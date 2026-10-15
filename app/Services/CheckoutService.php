<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Mail\NewOrderSellerMail;
use App\Mail\OrderPlacedBuyerMail;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use App\Services\Payments\CodGateway;
use App\Services\Payments\MockGateway;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    /**
     * Resolve the Payment Gateway driver based on configuration or method.
     */
    public function resolveGateway(string $method): PaymentGateway
    {
        $gatewayConfigs = config('payment.gateways', []);

        if (isset($gatewayConfigs[$method]['driver'])) {
            $driverClass = $gatewayConfigs[$method]['driver'];
            return app($driverClass);
        }

        if ($method === 'cod') {
            return app(CodGateway::class);
        }

        return app(MockGateway::class);
    }

    /**
     * Determine the next available Order Number starting from OD-20500.
     */
    public function getNextOrderNumber(): int
    {
        // Query database to find max OD- number
        $latestOrder = Order::where('order_no', 'REGEXP', '^OD-[0-9]+$')
            ->orderByRaw("CAST(SUBSTRING(order_no, 4) AS UNSIGNED) DESC")
            ->lockForUpdate()
            ->first();

        if ($latestOrder && preg_match('/^OD-(\d+)$/', $latestOrder->order_no, $matches)) {
            $currentMax = (int) $matches[1];
            return max(20500, $currentMax + 1);
        }

        return 20500;
    }

    /**
     * Execute entire checkout in ONE database transaction with row-locking on stock.
     */
    public function createOrderGroup(User $buyer, array $deliveryData, string $paymentMethod, array $paymentDetails = []): array
    {
        return DB::transaction(function () use ($buyer, $deliveryData, $paymentMethod, $paymentDetails) {
            // 1. Fetch user's cart
            $cart = Cart::where('user_id', $buyer->id)->first();
            if (!$cart) {
                throw ValidationException::withMessages(['cart' => 'Your shopping basket is empty.']);
            }

            $cartItems = CartItem::where('cart_id', $cart->id)->get();
            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Your shopping basket is empty.']);
            }

            // 2. Validate and Row-Lock Stock for all items
            $lockedItems = [];
            $sellerGroupedItems = [];

            foreach ($cartItems as $item) {
                $product = Product::where('id', $item->product_id)
                    ->where('status', 'approved')
                    ->lockForUpdate()
                    ->first();

                if (!$product) {
                    throw ValidationException::withMessages([
                        'cart' => "A product in your basket is no longer active or approved."
                    ]);
                }

                $seller = User::where('id', $product->seller_id)
                    ->where('status', 'active')
                    ->first();

                if (!$seller || !$seller->sellerProfile || $seller->sellerProfile->status !== 'approved') {
                    throw ValidationException::withMessages([
                        'cart' => "Products from seller '{$product->seller->name}' are currently not accepting orders."
                    ]);
                }

                $variant = null;
                $unitPrice = (int) $product->price;
                $availableStock = (int) $product->stock;
                $productDisplayName = $product->name;

                if ($item->variant_id) {
                    $variant = ProductVariant::where('id', $item->variant_id)
                        ->where('product_id', $product->id)
                        ->lockForUpdate()
                        ->first();

                    if (!$variant) {
                        throw ValidationException::withMessages([
                            'cart' => "Selected variant for '{$product->name}' is invalid."
                        ]);
                    }

                    $unitPrice = (int) $variant->price;
                    $availableStock = (int) $variant->stock;
                    $productDisplayName .= ' (' . $variant->label . ')';
                }

                if ($availableStock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "Sorry, '{$productDisplayName}' has only {$availableStock} items in stock (requested: {$item->quantity})."
                    ]);
                }

                $lineTotal = $unitPrice * $item->quantity;

                $itemSnapshot = [
                    'cart_item_id' => $item->id,
                    'product' => $product,
                    'variant' => $variant,
                    'product_name' => $productDisplayName,
                    'unit_price' => $unitPrice,
                    'quantity' => $item->quantity,
                    'line_total' => $lineTotal,
                    'seller_id' => $product->seller_id,
                ];

                $lockedItems[] = $itemSnapshot;
                $sellerGroupedItems[$product->seller_id][] = $itemSnapshot;
            }

            // 3. Optional Address Saving
            if (!empty($deliveryData['save_address']) && $deliveryData['save_address']) {
                Address::updateOrCreate(
                    [
                        'user_id' => $buyer->id,
                        'city' => $deliveryData['shipping_city'],
                        'address_line' => $deliveryData['shipping_address_line'],
                    ],
                    [
                        'full_name' => $deliveryData['shipping_full_name'],
                        'phone' => $deliveryData['shipping_phone'],
                        'area' => $deliveryData['shipping_area'],
                    ]
                );
            }

            // 4. Calculations: Subtotal, Delivery Fee (per seller), Commission, City & Min Order Checks
            $defaultFlatDeliveryFee = (int) Setting::get('delivery_fee', 150);
            $commissionPercent = (float) Setting::get('commission_percent', 5);

            $groupSubtotal = 0;
            $groupDeliveryTotal = 0;
            $sellerFeeMap = [];

            foreach ($sellerGroupedItems as $sellerId => $items) {
                $sellerUser = User::with('sellerProfile')->find($sellerId);
                $profile = $sellerUser?->sellerProfile;
                $farmTitle = $profile?->farm_name ?? ($sellerUser?->name ?? "Seller #{$sellerId}");

                // Check Serviceable City
                if ($profile && !$profile->isCityServiceable($deliveryData['shipping_city'])) {
                    $citiesList = !empty($profile->serviceable_cities) ? implode(', ', $profile->serviceable_cities) : 'their local region';
                    throw ValidationException::withMessages([
                        'shipping_city' => "Farm '{$farmTitle}' only provides direct delivery to: {$citiesList}. Please select a supported city or remove their items."
                    ]);
                }

                $sellerSubtotal = array_sum(array_column($items, 'line_total'));

                // Check Minimum Order Amount
                if ($profile && $profile->min_order_amount && $sellerSubtotal < $profile->min_order_amount) {
                    throw ValidationException::withMessages([
                        'cart' => "Minimum basket order amount for '{$farmTitle}' is Rs " . number_format($profile->min_order_amount) . " (Current: Rs " . number_format($sellerSubtotal) . ")."
                    ]);
                }

                // Dynamic Delivery Fee based on seller settings and free delivery threshold
                $sellerFee = $profile ? $profile->calculateDeliveryFee($sellerSubtotal) : $defaultFlatDeliveryFee;
                $sellerFeeMap[$sellerId] = $sellerFee;

                $groupSubtotal += $sellerSubtotal;
                $groupDeliveryTotal += $sellerFee;
            }

            $discount = 0;
            $grandTotal = $groupSubtotal + $groupDeliveryTotal - $discount;

            // 5. Create OrderGroup
            $groupNo = 'GRP-' . date('Ymd') . '-' . strtoupper(Str::random(4));
            $orderGroup = OrderGroup::create([
                'user_id' => $buyer->id,
                'group_no' => $groupNo,
                'subtotal' => $groupSubtotal,
                'delivery_total' => $groupDeliveryTotal,
                'discount' => $discount,
                'grand_total' => $grandTotal,
                'payment_method' => $paymentMethod,
                'delivery_slot' => $deliveryData['delivery_slot'] ?? 'morning',
                'shipping_full_name' => $deliveryData['shipping_full_name'],
                'shipping_phone' => $deliveryData['shipping_phone'],
                'shipping_city' => $deliveryData['shipping_city'],
                'shipping_area' => $deliveryData['shipping_area'],
                'shipping_address_line' => $deliveryData['shipping_address_line'],
            ]);

            // 6. Generate sequential OD- numbers for seller sub-orders
            $nextOrderNum = $this->getNextOrderNumber();
            $isCod = ($paymentMethod === 'cod');
            $initialStatus = $isCod ? 'placed' : 'pending_payment';
            $initialPaymentStatus = $isCod ? 'unpaid' : 'pending';

            $createdOrders = [];

            foreach ($sellerGroupedItems as $sellerId => $items) {
                $sellerSubtotal = array_sum(array_column($items, 'line_total'));
                $sellerDeliveryFee = $sellerFeeMap[$sellerId] ?? $defaultFlatDeliveryFee;
                
                // Calculate item-based commission according to each product's commission rate
                $itemCommissionTotals = 0;
                $processedItems = [];
                foreach ($items as $itemData) {
                    $itemCommissionRate = $itemData['product']->effective_commission_rate;
                    $itemCommissionAmount = (int) round(($itemData['line_total'] * $itemCommissionRate) / 100);
                    $itemCommissionTotals += $itemCommissionAmount;

                    $processedItems[] = array_merge($itemData, [
                        'commission_rate' => $itemCommissionRate,
                        'commission_amount' => $itemCommissionAmount,
                    ]);
                }

                $sellerCommission = $itemCommissionTotals;
                $sellerTotal = $sellerSubtotal + $sellerDeliveryFee;
                $orderNo = 'OD-' . $nextOrderNum++;

                $order = Order::create([
                    'order_group_id' => $orderGroup->id,
                    'order_no' => $orderNo,
                    'buyer_id' => $buyer->id,
                    'seller_id' => $sellerId,
                    'subtotal' => $sellerSubtotal,
                    'delivery_fee' => $sellerDeliveryFee,
                    'commission' => $sellerCommission,
                    'total' => $sellerTotal,
                    'status' => $initialStatus,
                    'payment_status' => $initialPaymentStatus,
                    'delivery_slot' => $deliveryData['delivery_slot'] ?? 'morning',
                ]);

                // Create Order Items and snap details
                foreach ($processedItems as $itemData) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $itemData['product']->id,
                        'variant_id' => $itemData['variant'] ? $itemData['variant']->id : null,
                        'product_name' => $itemData['product_name'],
                        'unit_price' => $itemData['unit_price'],
                        'quantity' => $itemData['quantity'],
                        'line_total' => $itemData['line_total'],
                        'commission_rate' => $itemData['commission_rate'],
                        'commission_amount' => $itemData['commission_amount'],
                    ]);

                    // If COD: reduce stock immediately with locked models
                    if ($isCod) {
                        if ($itemData['variant']) {
                            $itemData['variant']->decrement('stock', $itemData['quantity']);
                        }
                        $itemData['product']->decrement('stock', $itemData['quantity']);
                        $itemData['product']->fresh()->checkAndDispatchLowStockAlert();
                    }
                }

                // Write initial order status history
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => $initialStatus,
                    'note' => $isCod ? 'Order placed successfully with Cash on Delivery' : 'Awaiting online payment authorization',
                    'changed_by' => $buyer->id,
                ]);

                $createdOrders[] = $order;
            }

            // 7. Initialize Payment Record (store only gateway_ref and last4, NEVER card numbers)
            $last4 = null;
            if (!empty($paymentDetails['card_number'])) {
                $digits = preg_replace('/\D/', '', $paymentDetails['card_number']);
                $last4 = strlen($digits) >= 4 ? substr($digits, -4) : '4242';
            } elseif (!empty($paymentDetails['wallet_account_no'])) {
                $digits = preg_replace('/\D/', '', $paymentDetails['wallet_account_no']);
                $last4 = strlen($digits) >= 4 ? substr($digits, -4) : '9988';
            }

            $payment = Payment::create([
                'order_group_id' => $orderGroup->id,
                'method' => $paymentMethod,
                'gateway_ref' => $isCod ? ('COD-' . $orderGroup->id . '-' . strtoupper(substr(uniqid(), -5))) : null,
                'last4' => $last4,
                'amount' => $grandTotal,
                'status' => $initialPaymentStatus,
                'paid_at' => null,
            ]);

            // 8. Clear Cart
            CartItem::where('cart_id', $cart->id)->delete();

            // 9. Process Gateway
            $gateway = $this->resolveGateway($paymentMethod);
            $gatewayResult = $gateway->process($orderGroup, array_merge($paymentDetails, ['last4' => $last4]));

            // 10. Send Real-Time Notifications
            try {
                // To Buyer
                \App\Models\Notification::sendTo(
                    $buyer,
                    "Order #{$orderGroup->group_no} Placed!",
                    $isCod ? "Your order totaling Rs " . number_format($grandTotal) . " has been placed via Cash on Delivery." : "Order placed! Please submit your payment proof to complete verification.",
                    route('orders.index'),
                    'order_placed',
                    'bi-bag-check-fill'
                );

                // To Each Seller
                foreach ($createdOrders as $createdOrder) {
                    \App\Models\Notification::sendTo(
                        $createdOrder->seller_id,
                        "New Order #{$createdOrder->order_no} Received!",
                        "Buyer {$buyer->name} placed an order totaling Rs " . number_format($createdOrder->total) . " (" . ($isCod ? 'Cash on Delivery' : 'Online Payment') . ").",
                        route('seller.orders.show', $createdOrder->id),
                        'new_order',
                        'bi-bell-fill'
                    );
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to create order notifications: ' . $e->getMessage());
            }

            // 11. If COD: Send emails immediately
            if ($isCod) {
                $this->sendOrderConfirmationEmails($orderGroup);
            }

            return [
                'order_group' => $orderGroup,
                'gateway_result' => $gatewayResult,
            ];
        });
    }

    /**
     * Process mock / gateway payment success.
     * Decrements stock with row-locking, updates status to 'placed', payment to 'paid', logs history, and sends emails.
     */
    public function processPaymentSuccess(OrderGroup $orderGroup, array $gatewayData = []): void
    {
        DB::transaction(function () use ($orderGroup, $gatewayData) {
            $orderGroup->load(['orders.items.product', 'orders.items.variant', 'orders.seller.sellerProfile', 'user']);

            foreach ($orderGroup->orders as $order) {
                // If already placed, skip stock decrementing to prevent double deduction
                if ($order->status === 'pending_payment') {
                    // Lock and decrement stock for all items
                    foreach ($order->items as $item) {
                        if ($item->product_id) {
                            $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                            if ($product) {
                                $product->decrement('stock', $item->quantity);
                            }
                        }
                        if ($item->variant_id) {
                            $variant = ProductVariant::where('id', $item->variant_id)->lockForUpdate()->first();
                            if ($variant) {
                                $variant->decrement('stock', $item->quantity);
                            }
                        }
                    }

                    $order->update([
                        'status' => 'placed',
                        'payment_status' => 'paid',
                    ]);

                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'status' => 'placed',
                        'note' => 'Payment authorized & captured (' . ($gatewayData['gateway_ref'] ?? 'ONLINE') . ')',
                        'changed_by' => $orderGroup->user_id,
                    ]);
                }
            }

            // Update Payment record
            $payment = Payment::where('order_group_id', $orderGroup->id)->latest()->first();
            if ($payment) {
                $payment->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'gateway_ref' => $gatewayData['gateway_ref'] ?? ('SIM-' . strtoupper(Str::random(10))),
                    'last4' => $gatewayData['last4'] ?? $payment->last4,
                ]);
            }

            // Send real-time notifications
            try {
                \App\Models\Notification::sendTo(
                    $orderGroup->user_id,
                    "Payment Authorized & Confirmed",
                    "Your online payment for Order Group #{$orderGroup->group_no} was authorized and confirmed successfully.",
                    route('orders.index'),
                    'payment_success',
                    'bi-check-circle-fill'
                );

                foreach ($orderGroup->orders as $order) {
                    \App\Models\Notification::sendTo(
                        $order->seller_id,
                        "Payment Captured for Order #{$order->order_no}",
                        "Digital payment has been captured for order #{$order->order_no}. You can now start packing.",
                        route('seller.orders.show', $order->id),
                        'payment_received',
                        'bi-credit-card-fill'
                    );
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Notification error on payment success: ' . $e->getMessage());
            }

            // Send confirmation emails
            $this->sendOrderConfirmationEmails($orderGroup);
        });
    }

    /**
     * Process payment failure: stays pending_payment, marks payment as failed, logs history.
     */
    public function processPaymentFailure(OrderGroup $orderGroup, string $reason = 'Payment declined by simulator'): void
    {
        DB::transaction(function () use ($orderGroup, $reason) {
            $orderGroup->load('orders');

            foreach ($orderGroup->orders as $order) {
                $order->update([
                    'payment_status' => 'failed',
                ]);

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'pending_payment',
                    'note' => 'Payment attempt failed: ' . $reason,
                    'changed_by' => $orderGroup->user_id,
                ]);
            }

            $payment = Payment::where('order_group_id', $orderGroup->id)->latest()->first();
            if ($payment) {
                $payment->update([
                    'status' => 'failed',
                ]);
            }
        });
    }

    /**
     * Switch an unpaid pending_payment order to Cash on Delivery.
     */
    public function switchPaymentToCod(OrderGroup $orderGroup): void
    {
        DB::transaction(function () use ($orderGroup) {
            $orderGroup->load(['orders.items', 'user']);

            foreach ($orderGroup->orders as $order) {
                if ($order->status === 'pending_payment') {
                    // Lock and decrement stock
                    foreach ($order->items as $item) {
                        if ($item->product_id) {
                            $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                            if ($product) {
                                $product->decrement('stock', $item->quantity);
                            }
                        }
                        if ($item->variant_id) {
                            $variant = ProductVariant::where('id', $item->variant_id)->lockForUpdate()->first();
                            if ($variant) {
                                $variant->decrement('stock', $item->quantity);
                            }
                        }
                    }

                    $order->update([
                        'status' => 'placed',
                        'payment_status' => 'unpaid',
                    ]);

                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'status' => 'placed',
                        'note' => 'Payment method switched to Cash on Delivery by customer',
                        'changed_by' => $orderGroup->user_id,
                    ]);
                }
            }

            $orderGroup->update([
                'payment_method' => 'cod',
            ]);

            $payment = Payment::where('order_group_id', $orderGroup->id)->latest()->first();
            if ($payment) {
                $payment->update([
                    'method' => 'cod',
                    'status' => 'unpaid',
                    'gateway_ref' => 'COD-' . $orderGroup->id . '-' . strtoupper(substr(uniqid(), -5)),
                ]);
            }

            $this->sendOrderConfirmationEmails($orderGroup);
        });
    }

    /**
     * Send email notifications to the buyer and all distinct sellers involved.
     */
    public function sendOrderConfirmationEmails(OrderGroup $orderGroup): void
    {
        try {
            $orderGroup->load(['orders.items', 'orders.seller.sellerProfile', 'user']);

            // 1. Email Buyer
            if ($orderGroup->user && $orderGroup->user->email) {
                Mail::to($orderGroup->user->email)->send(new OrderPlacedBuyerMail($orderGroup));
            }

            // 2. Email Each Seller
            foreach ($orderGroup->orders as $order) {
                if ($order->seller && $order->seller->email) {
                    Mail::to($order->seller->email)->send(new NewOrderSellerMail($order));
                }
            }
        } catch (Exception $e) {
            Log::error('Failed to send checkout confirmation emails: ' . $e->getMessage(), [
                'order_group_id' => $orderGroup->id,
            ]);
        }
    }
}
