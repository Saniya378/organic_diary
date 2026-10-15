<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\OrderGroup;
use App\Models\Setting;
use App\Services\CheckoutService;
use App\Services\Payments\MockGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(protected CheckoutService $checkoutService)
    {
    }

    /**
     * Show the 3-Step Checkout Page.
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return redirect()->route('cart.index')->with('warning', 'Your shopping basket is empty.');
        }

        $cartItems = CartItem::where('cart_id', $cart->id)
            ->with(['product.seller.sellerProfile', 'product.images', 'variant'])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('warning', 'Your shopping basket is empty.');
        }

        // Group items by seller
        // Group items by seller and compute dynamic delivery fee per seller
        $sellerGroups = [];
        $subtotal = 0;
        $totalDeliveryFee = 0;
        $defaultFlatDeliveryFee = (int) Setting::get('delivery_fee', 150);

        foreach ($cartItems as $item) {
            if (!$item->product || $item->product->status !== 'approved') {
                continue;
            }
            $sellerId = $item->product->seller_id;
            if (!isset($sellerGroups[$sellerId])) {
                $sellerGroups[$sellerId] = [
                    'seller' => $item->product->seller,
                    'items' => [],
                    'subtotal' => 0,
                    'delivery_fee' => 0,
                ];
            }
            $sellerGroups[$sellerId]['items'][] = $item;
            $sellerGroups[$sellerId]['subtotal'] += $item->line_total;
            $subtotal += $item->line_total;
        }

        foreach ($sellerGroups as $sellerId => &$group) {
            $profile = $group['seller']?->sellerProfile;
            $group['delivery_fee'] = $profile ? $profile->calculateDeliveryFee($group['subtotal']) : $defaultFlatDeliveryFee;
            $totalDeliveryFee += $group['delivery_fee'];
        }
        unset($group);

        $distinctSellersCount = count($sellerGroups);
        $discount = 0;
        $grandTotal = $subtotal + $totalDeliveryFee - $discount;

        // Allowed GB service cities
        $allowedCitiesSetting = Setting::get('allowed_cities', '[]');
        $allowedCities = is_string($allowedCitiesSetting) ? json_decode($allowedCitiesSetting, true) : $allowedCitiesSetting;
        if (!is_array($allowedCities) || empty($allowedCities)) {
            $allowedCities = ['Gilgit', 'Skardu', 'Hunza', 'Ghizer', 'Astore', 'Nagar', 'Diamer'];
        }

        // Buyer saved addresses
        $savedAddresses = Address::where('user_id', $user->id)->latest()->get();
        $defaultAddress = $savedAddresses->firstWhere('is_default', true) ?? $savedAddresses->first();

        return view('checkout.index', [
            'cartItems' => $cartItems,
            'sellerGroups' => $sellerGroups,
            'subtotal' => $subtotal,
            'flatDeliveryFee' => $defaultFlatDeliveryFee,
            'distinctSellersCount' => $distinctSellersCount,
            'totalDeliveryFee' => $totalDeliveryFee,
            'discount' => $discount,
            'grandTotal' => $grandTotal,
            'allowedCities' => $allowedCities,
            'savedAddresses' => $savedAddresses,
            'defaultAddress' => $defaultAddress,
        ]);
    }

    /**
     * Handle checkout order placement in a single transaction with row locking.
     */
    public function store(CheckoutRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $paymentMethod = $request->input('payment_method');
        $deliveryData = [
            'shipping_full_name' => $request->input('shipping_full_name'),
            'shipping_phone' => $request->input('shipping_phone'),
            'shipping_city' => $request->input('shipping_city'),
            'shipping_area' => $request->input('shipping_area'),
            'shipping_address_line' => $request->input('shipping_address_line'),
            'delivery_slot' => $request->input('delivery_slot', 'morning'),
            'save_address' => $request->boolean('save_address'),
        ];

        $paymentDetails = $request->only([
            'card_name',
            'card_number',
            'card_expiry',
            'card_cvc',
            'wallet_provider',
            'wallet_account_no',
        ]);

        $result = $this->checkoutService->createOrderGroup($user, $deliveryData, $paymentMethod, $paymentDetails);
        $orderGroup = $result['order_group'];
        $gatewayResult = $result['gateway_result'];

        if ($paymentMethod === 'cod') {
            return redirect()->route('checkout.success', $orderGroup->group_no)
                ->with('success', 'Thank you! Your order has been placed successfully via Cash on Delivery.');
        }

        if ($paymentMethod === 'card') {
            return redirect()->route('checkout.payment.mock', $orderGroup->id);
        }

        // Real-Time Bank / Mobile Wallet Transfer Payment
        return redirect()->route('checkout.payment', $orderGroup->id)
            ->with('info', 'Please transfer the order amount and submit your Transaction ID for verification.');
    }

    /**
     * Render the Real-Time Bank / Mobile Wallet Payment Transfer & Proof Submission UI.
     */
    public function showPayment(OrderGroup $orderGroup): View|RedirectResponse
    {
        if (Auth::id() !== $orderGroup->user_id && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access to order payment session.');
        }

        $orderGroup->load(['orders.items.product.images', 'orders.seller.sellerProfile', 'payment']);

        // If already paid, redirect straight to success
        if ($orderGroup->payment && $orderGroup->payment->status === 'paid') {
            return redirect()->route('checkout.success', $orderGroup->group_no);
        }

        $primaryOrder = $orderGroup->orders->first();
        $primarySeller = $primaryOrder?->seller;
        $sellerProfile = $primarySeller?->sellerProfile;
        $sellerFarmName = $sellerProfile?->farm_name ?? ($primarySeller?->name ?? 'Farm Producer');

        // Direct payment to seller accounts, falling back to platform settings if seller has not configured them
        $hasSellerAccounts = (bool) ($sellerProfile && ($sellerProfile->easypaisa_number || $sellerProfile->jazzcash_number || $sellerProfile->bank_account_number));

        $bankDetails = [
            'seller_name' => $sellerFarmName,
            'is_seller_direct' => $hasSellerAccounts,
            'has_bank' => (bool) ($sellerProfile?->bank_account_number ?: Setting::get('bank_account_number')),
            'has_easypaisa' => (bool) ($sellerProfile?->easypaisa_number ?: Setting::get('easypaisa_number')),
            'has_jazzcash' => (bool) ($sellerProfile?->jazzcash_number ?: Setting::get('jazzcash_number')),
            'bank_name' => $sellerProfile?->bank_name ?: Setting::get('bank_name', 'Meezan Bank Ltd'),
            'bank_account_title' => $sellerProfile?->bank_account_title ?: Setting::get('bank_account_title', 'Organic Dairy Gilgit-Baltistan'),
            'bank_account_number' => $sellerProfile?->bank_account_number ?: Setting::get('bank_account_number', '01020304050607'),
            'bank_iban' => $sellerProfile?->bank_iban ?: Setting::get('bank_iban', 'PK36MEZN0001020304050607'),
            'bank_branch' => $sellerProfile?->bank_branch ?: Setting::get('bank_branch', 'Gilgit City Branch'),
            'easypaisa_title' => $sellerProfile?->easypaisa_title ?: Setting::get('easypaisa_title', 'Organic Dairy Gilgit'),
            'easypaisa_number' => $sellerProfile?->easypaisa_number ?: Setting::get('easypaisa_number', '0345-1234567'),
            'jazzcash_title' => $sellerProfile?->jazzcash_title ?: Setting::get('jazzcash_title', 'Organic Dairy Gilgit'),
            'jazzcash_number' => $sellerProfile?->jazzcash_number ?: Setting::get('jazzcash_number', '0300-7654321'),
            'payment_instructions' => $sellerProfile?->payment_instructions ?: Setting::get('payment_instructions', 'Please transfer the exact order amount directly to the seller accounts listed below. Once transferred, enter your Transaction ID (TRX ID) and submit proof. The seller will verify and dispatch your fresh dairy.'),
        ];

        return view('checkout.payment', [
            'orderGroup' => $orderGroup,
            'payment' => $orderGroup->payment,
            'bankDetails' => $bankDetails,
            'primarySeller' => $primarySeller,
            'sellerProfile' => $sellerProfile,
        ]);
    }

    /**
     * Submit buyer payment transaction ID and screenshot proof for admin verification.
     */
    public function submitPaymentProof(Request $request, OrderGroup $orderGroup): RedirectResponse
    {
        if (Auth::id() !== $orderGroup->user_id && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'payment_channel' => ['required', 'string', 'max:50'],
            'sender_name' => ['required', 'string', 'max:100'],
            'sender_account' => ['nullable', 'string', 'max:50'],
            'transaction_id' => ['required', 'string', 'max:100'],
            'proof_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $proofImagePath = null;
        if ($request->hasFile('proof_image')) {
            $proofImagePath = $request->file('proof_image')->store('payment_proofs', 'public');
        }

        $payment = $orderGroup->payment;
        if (!$payment) {
            $payment = \App\Models\Payment::create([
                'order_group_id' => $orderGroup->id,
                'method' => 'bank_transfer',
                'amount' => $orderGroup->grand_total,
                'status' => 'pending',
            ]);
        }

        $payment->update([
            'payment_channel' => $request->input('payment_channel'),
            'sender_name' => $request->input('sender_name'),
            'sender_account' => $request->input('sender_account'),
            'transaction_id' => $request->input('transaction_id'),
            'gateway_ref' => $request->input('transaction_id'),
            'proof_image' => $proofImagePath ?? $payment->proof_image,
            'status' => 'under_review',
            'admin_notes' => null, // Reset previous rejection note on resubmit
        ]);

        // Update all orders in group & notify sellers
        foreach ($orderGroup->orders as $order) {
            $order->update([
                'payment_status' => 'under_review',
            ]);

            \App\Models\OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'note' => 'Buyer submitted payment proof via ' . ucfirst(str_replace('_', ' ', $request->input('payment_channel'))) . ' (TRX ID: ' . $request->input('transaction_id') . '). Awaiting seller verification.',
                'changed_by' => Auth::id(),
            ]);

            try {
                \App\Models\Notification::sendTo(
                    $order->seller_id,
                    "Payment Receipt Submitted for Order #{$order->order_no}",
                    "Buyer submitted payment proof (TRX: {$request->input('transaction_id')}). Please verify the receipt and confirm the order.",
                    route('seller.orders.show', $order->id),
                    'payment_under_review',
                    'bi-credit-card-2-front-fill'
                );
            } catch (\Exception $e) {}
        }

        return redirect()->route('checkout.success', $orderGroup->group_no)
            ->with('success', 'Thank you! Your payment details (TRX ID: ' . $request->input('transaction_id') . ') have been sent to the farm. The seller will verify your receipt and prepare your order.');
    }

    /**
     * Render the Mock Payment Simulator Gateway UI.
     */
    public function mockPayment(OrderGroup $orderGroup): View|RedirectResponse
    {
        if (Auth::id() !== $orderGroup->user_id && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access to order payment session.');
        }

        $orderGroup->load(['orders.items', 'payment']);

        // If already paid, redirect straight to success
        if ($orderGroup->payment && $orderGroup->payment->status === 'paid') {
            return redirect()->route('checkout.success', $orderGroup->group_no);
        }

        return view('checkout.mock_payment', [
            'orderGroup' => $orderGroup,
            'payment' => $orderGroup->payment,
        ]);
    }

    /**
     * Process Mock Payment Outcome (Success or Fail).
     */
    public function processMockPayment(Request $request, OrderGroup $orderGroup): RedirectResponse
    {
        if (Auth::id() !== $orderGroup->user_id && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $action = $request->input('action');
        $gateway = app(MockGateway::class);

        if ($action === 'success') {
            $simResult = $gateway->simulate('success', [
                'last4' => $orderGroup->payment?->last4 ?? '4242',
            ]);

            $this->checkoutService->processPaymentSuccess($orderGroup, $simResult);

            return redirect()->route('checkout.success', $orderGroup->group_no)
                ->with('success', 'Digital payment verified and authorized successfully!');
        }

        // Action is 'fail'
        $simResult = $gateway->simulate('fail');
        $this->checkoutService->processPaymentFailure($orderGroup, $simResult['message']);

        return redirect()->route('checkout.payment.mock', $orderGroup->id)
            ->with('error', 'Payment simulation was declined. You can retry with another card or switch directly to Cash on Delivery.');
    }

    /**
     * Switch an unpaid pending_payment order directly to Cash on Delivery.
     */
    public function switchToCod(OrderGroup $orderGroup): RedirectResponse
    {
        if (Auth::id() !== $orderGroup->user_id && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $this->checkoutService->switchPaymentToCod($orderGroup);

        return redirect()->route('checkout.success', $orderGroup->group_no)
            ->with('success', 'Your order was successfully switched to Cash on Delivery!');
    }

    /**
     * Render the Order Success Confirmation Page.
     */
    public function success(string $groupNo): View
    {
        $orderGroup = OrderGroup::where('group_no', $groupNo)
            ->with([
                'orders.items.product.images',
                'orders.items.variant',
                'orders.seller.sellerProfile',
                'payment',
            ])
            ->firstOrFail();

        if (Auth::id() !== $orderGroup->user_id && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access to order summary.');
        }

        return view('checkout.success', [
            'orderGroup' => $orderGroup,
        ]);
    }
}
