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
        $sellerGroups = [];
        $subtotal = 0;
        foreach ($cartItems as $item) {
            if (!$item->product || $item->product->status !== 'approved') {
                continue;
            }
            $sellerId = $item->product->seller_id;
            $sellerGroups[$sellerId]['seller'] = $item->product->seller;
            $sellerGroups[$sellerId]['items'][] = $item;
            $subtotal += $item->line_total;
        }

        $flatDeliveryFee = (int) Setting::get('delivery_fee', 150);
        $distinctSellersCount = count($sellerGroups);
        $totalDeliveryFee = $flatDeliveryFee * max(1, $distinctSellersCount);
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
            'flatDeliveryFee' => $flatDeliveryFee,
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

        // Bank and Mobile Wallet Account Settings
        $bankDetails = [
            'bank_name' => Setting::get('bank_name', 'Meezan Bank Ltd'),
            'bank_account_title' => Setting::get('bank_account_title', 'Organic Dairy Gilgit-Baltistan'),
            'bank_account_number' => Setting::get('bank_account_number', '01020304050607'),
            'bank_iban' => Setting::get('bank_iban', 'PK36MEZN0001020304050607'),
            'bank_branch' => Setting::get('bank_branch', 'Gilgit City Branch'),
            'easypaisa_title' => Setting::get('easypaisa_title', 'Organic Dairy Gilgit'),
            'easypaisa_number' => Setting::get('easypaisa_number', '0345-1234567'),
            'jazzcash_title' => Setting::get('jazzcash_title', 'Organic Dairy Gilgit'),
            'jazzcash_number' => Setting::get('jazzcash_number', '0300-7654321'),
            'payment_instructions' => Setting::get('payment_instructions', 'Please transfer the exact order amount to any of our official accounts listed below. Once transferred, enter your Transaction ID (TRX ID) and submit proof. The admin will verify and approve your order immediately.'),
        ];

        return view('checkout.payment', [
            'orderGroup' => $orderGroup,
            'payment' => $orderGroup->payment,
            'bankDetails' => $bankDetails,
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

        // Update all orders in group
        foreach ($orderGroup->orders as $order) {
            $order->update([
                'payment_status' => 'under_review',
            ]);

            \App\Models\OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'note' => 'Buyer submitted payment proof via ' . ucfirst(str_replace('_', ' ', $request->input('payment_channel'))) . ' (TRX ID: ' . $request->input('transaction_id') . '). Status: Under Admin Review.',
                'changed_by' => Auth::id(),
            ]);
        }

        return redirect()->route('checkout.success', $orderGroup->group_no)
            ->with('success', 'Thank you! Your payment details (TRX ID: ' . $request->input('transaction_id') . ') have been submitted. Our team is reviewing your transaction.');
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
