<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\FarmerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Seller\DashboardController as SellerDashboardController;
use App\Http\Controllers\ShopController;
use App\Models\Product;
use App\Models\SellerProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Public Storefront Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/product/{slug}', [ShopController::class, 'show'])->name('shop.show');
Route::get('/api/search/autocomplete', [ShopController::class, 'autocomplete'])->name('api.search.autocomplete');

// Deals & Subscriptions Showcase (3 Days, 1 Week, 1 Month Plans)
Route::get('/deals', [\App\Http\Controllers\DealController::class, 'index'])->name('deals.index');
Route::get('/deals/create/{product}', [\App\Http\Controllers\DealController::class, 'create'])->name('deals.create')->middleware('auth');
Route::post('/deals/subscribe', [\App\Http\Controllers\DealController::class, 'store'])->name('deals.store')->middleware('auth');

Route::get('/farmers', [FarmerController::class, 'index'])->name('farmers.index');
Route::get('/farmers/{id}', [FarmerController::class, 'show'])->name('farmers.show');

Route::get('/track-order/{order_no?}', [\App\Http\Controllers\OrderController::class, 'track'])->name('track-order.form');
Route::get('/orders/track/{order_no?}', [\App\Http\Controllers\OrderController::class, 'track'])->name('orders.track');
Route::get('/orders/{order}/track-status', [\App\Http\Controllers\OrderController::class, 'trackStatus'])->name('orders.track.status');

Route::get('/about', function () {
    return view('pages.about');
})->name('about');

// Live AI Customer Support Chatbot
Route::post('/chatbot/message', [\App\Http\Controllers\ChatbotController::class, 'message'])->name('chatbot.message');

/*
|--------------------------------------------------------------------------
| Shopping Cart Routes
|--------------------------------------------------------------------------
*/
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::put('/cart/{id}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{id}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::post('/cart/merge', [CartController::class, 'merge'])->name('cart.merge');
Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');

/*
|--------------------------------------------------------------------------
| Authentication Routes (Guest Only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');

    // Registration OTP Verification
    Route::get('/register/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('register.verify.form');
    Route::post('/register/verify-otp', [AuthController::class, 'verifyOtp'])->name('register.verify.post');
    Route::post('/register/resend-otp', [AuthController::class, 'resendOtp'])->name('register.resend-otp');

    // Password Reset OTP Flow
    Route::get('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'sendResetOtp'])->name('password.email');
    Route::get('/reset-password/otp', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showOtpResetForm'])->name('password.reset.otp');
    Route::post('/reset-password/otp', [\App\Http\Controllers\Auth\PasswordResetController::class, 'resetPasswordWithOtp'])->name('password.update.otp');
    Route::post('/reset-password/resend-otp', [\App\Http\Controllers\Auth\PasswordResetController::class, 'resendResetOtp'])->name('password.resend.otp');

    // Password Reset Legacy / URL Token Fallback
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Verified Customer Reviews
Route::post('/product/{product}/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->middleware('auth')->name('products.reviews.store');

/*
|--------------------------------------------------------------------------
| Authenticated User Routes (Profile, Address Book & Buyer Orders)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Address Book Management
    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::patch('/addresses/{address}/default', [AddressController::class, 'setDefault'])->name('addresses.set-default');

    // Buyer Orders
    Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/invoice', [\App\Http\Controllers\OrderController::class, 'invoice'])->name('orders.invoice');
    Route::get('/orders/group/{orderGroup}/invoice', [\App\Http\Controllers\OrderController::class, 'groupInvoice'])->name('orders.group.invoice');
    Route::post('/orders/{order}/cancel', [\App\Http\Controllers\OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/resubmit-payment', [\App\Http\Controllers\OrderController::class, 'resubmitPayment'])->name('orders.resubmit-payment');

    // Checkout Flow
    Route::get('/checkout', [\App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [\App\Http\Controllers\CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/payment/{orderGroup}', [\App\Http\Controllers\CheckoutController::class, 'showPayment'])->name('checkout.payment');
    Route::post('/checkout/payment/{orderGroup}', [\App\Http\Controllers\CheckoutController::class, 'submitPaymentProof'])->name('checkout.payment.submit');
    Route::get('/checkout/payment/mock/{orderGroup}', [\App\Http\Controllers\CheckoutController::class, 'mockPayment'])->name('checkout.payment.mock');
    Route::post('/checkout/payment/mock/{orderGroup}', [\App\Http\Controllers\CheckoutController::class, 'processMockPayment'])->name('checkout.payment.mock.process');
    Route::post('/checkout/payment/switch-cod/{orderGroup}', [\App\Http\Controllers\CheckoutController::class, 'switchToCod'])->name('checkout.payment.switch-cod');
    // Buyer Deals & Subscriptions
    Route::get('/my-deals', [\App\Http\Controllers\DealController::class, 'buyerIndex'])->name('buyer.deals.index');
    Route::get('/my-deals/{deal}', [\App\Http\Controllers\DealController::class, 'buyerShow'])->name('buyer.deals.show');
    Route::post('/my-deals/{deal}/pause', [\App\Http\Controllers\DealController::class, 'pause'])->name('buyer.deals.pause');
    Route::post('/my-deals/{deal}/resume', [\App\Http\Controllers\DealController::class, 'resume'])->name('buyer.deals.resume');
    Route::post('/my-deals/{deal}/cancel', [\App\Http\Controllers\DealController::class, 'cancel'])->name('buyer.deals.cancel');

    // Real-Time Notifications (for Buyer & Seller)
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/open', [\App\Http\Controllers\NotificationController::class, 'openAndRedirect'])->name('notifications.open');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    Route::get('/checkout/success/{groupNo}', [\App\Http\Controllers\CheckoutController::class, 'success'])->name('checkout.success');
});

/*
|--------------------------------------------------------------------------
| Seller Panel Routes (role:seller)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:seller'])->prefix('seller')->name('seller.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Seller\DashboardController::class, 'index'])->name('dashboard');

    // Products Management
    Route::get('/products', [\App\Http\Controllers\Seller\ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [\App\Http\Controllers\Seller\ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [\App\Http\Controllers\Seller\ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [\App\Http\Controllers\Seller\ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [\App\Http\Controllers\Seller\ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [\App\Http\Controllers\Seller\ProductController::class, 'destroy'])->name('products.destroy');
    Route::delete('/products/{product}/images/{image}', [\App\Http\Controllers\Seller\ProductController::class, 'deleteImage'])->name('products.images.destroy');
    Route::patch('/products/{product}/toggle-active', [\App\Http\Controllers\Seller\ProductController::class, 'toggleActive'])->name('products.toggle-active');
    Route::patch('/products/{product}/toggle-deals', [\App\Http\Controllers\Seller\ProductController::class, 'toggleDeals'])->name('products.toggle-deals');

    // Inventory Quick Stock
    Route::get('/inventory', [\App\Http\Controllers\Seller\InventoryController::class, 'index'])->name('inventory.index');
    Route::patch('/inventory/{product}', [\App\Http\Controllers\Seller\InventoryController::class, 'updateStock'])->name('inventory.update-stock');
    Route::patch('/inventory/variant/{variant}', [\App\Http\Controllers\Seller\InventoryController::class, 'updateVariantStock'])->name('inventory.update-variant-stock');

    // Order Fulfillment & Progression
    Route::get('/orders', [\App\Http\Controllers\Seller\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Seller\OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/invoice', [\App\Http\Controllers\Seller\OrderController::class, 'invoice'])->name('orders.invoice');
    Route::post('/orders/{order}/confirm', [\App\Http\Controllers\Seller\OrderController::class, 'confirm'])->name('orders.confirm');
    Route::post('/orders/{order}/advance-status', [\App\Http\Controllers\Seller\OrderController::class, 'advanceStatus'])->name('orders.advance-status');
    Route::post('/orders/{order}/cancel', [\App\Http\Controllers\Seller\OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/approve-payment', [\App\Http\Controllers\Seller\OrderController::class, 'approvePayment'])->name('orders.approve-payment');
    Route::post('/orders/{order}/reject-payment', [\App\Http\Controllers\Seller\OrderController::class, 'rejectPayment'])->name('orders.reject-payment');

    // Buyer Payments Direct Verification for Seller
    Route::get('/payments', [\App\Http\Controllers\Seller\PaymentController::class, 'index'])->name('payments.index');

    // Earnings & Analytics
    Route::get('/earnings', [\App\Http\Controllers\Seller\EarningsController::class, 'index'])->name('earnings.index');

    // Payouts & Wallet Withdrawals
    Route::get('/payouts', [\App\Http\Controllers\Seller\PayoutController::class, 'index'])->name('payouts.index');
    Route::post('/payouts', [\App\Http\Controllers\Seller\PayoutController::class, 'store'])->name('payouts.store');
    Route::delete('/payouts/{payout}', [\App\Http\Controllers\Seller\PayoutController::class, 'cancel'])->name('payouts.cancel');

    // Platform Commission Ledger & Dues
    Route::get('/commissions', [\App\Http\Controllers\Seller\CommissionController::class, 'index'])->name('commissions.index');
    Route::post('/commissions', [\App\Http\Controllers\Seller\CommissionController::class, 'store'])->name('commissions.store');

    // Farm Profile
    Route::get('/profile', [\App\Http\Controllers\Seller\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\Seller\ProfileController::class, 'update'])->name('profile.update');

    // Farm Deals & Subscriptions (3 Days, 1 Week, 1 Month)
    Route::get('/deals', [\App\Http\Controllers\Seller\DealController::class, 'index'])->name('deals.index');
    Route::get('/deals/{deal}', [\App\Http\Controllers\Seller\DealController::class, 'show'])->name('deals.show');
    Route::post('/deals/deliveries/{delivery}/mark', [\App\Http\Controllers\Seller\DealController::class, 'markDelivery'])->name('deals.deliveries.mark');
});

/*
|--------------------------------------------------------------------------
| Admin Panel Routes (role:admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Sellers Approval & Management
    Route::get('/sellers', [\App\Http\Controllers\Admin\SellerController::class, 'index'])->name('sellers.index');
    Route::post('/sellers/{sellerProfile}/approve', [\App\Http\Controllers\Admin\SellerController::class, 'approve'])->name('sellers.approve');
    Route::post('/sellers/{sellerProfile}/reject', [\App\Http\Controllers\Admin\SellerController::class, 'reject'])->name('sellers.reject');
    Route::post('/sellers/{sellerProfile}/suspend', [\App\Http\Controllers\Admin\SellerController::class, 'suspend'])->name('sellers.suspend');
    Route::post('/sellers/{sellerProfile}/reactivate', [\App\Http\Controllers\Admin\SellerController::class, 'reactivate'])->name('sellers.reactivate');

    // Products Approval, Review & Commission Settings
    Route::get('/products', [\App\Http\Controllers\Admin\ProductController::class, 'index'])->name('products.index');
    Route::post('/products/{product}/approve', [\App\Http\Controllers\Admin\ProductController::class, 'approve'])->name('products.approve');
    Route::post('/products/{product}/reject', [\App\Http\Controllers\Admin\ProductController::class, 'reject'])->name('products.reject');
    Route::post('/products/{product}/commission', [\App\Http\Controllers\Admin\ProductController::class, 'updateCommission'])->name('products.commission');

    // Orders Management & Cancellation
    Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/invoice', [\App\Http\Controllers\Admin\OrderController::class, 'invoice'])->name('orders.invoice');
    Route::post('/orders/{order}/cancel', [\App\Http\Controllers\Admin\OrderController::class, 'cancel'])->name('orders.cancel');

    // Users Management
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/suspend', [\App\Http\Controllers\Admin\UserController::class, 'suspend'])->name('users.suspend');
    Route::post('/users/{user}/reactivate', [\App\Http\Controllers\Admin\UserController::class, 'reactivate'])->name('users.reactivate');

    // Categories CRUD
    Route::get('/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('categories.destroy');

    // Platform Settings
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

    // Payments & Transactions
    Route::get('/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments/{payment}/approve', [\App\Http\Controllers\Admin\PaymentController::class, 'approve'])->name('payments.approve');
    Route::post('/payments/{payment}/reject', [\App\Http\Controllers\Admin\PaymentController::class, 'reject'])->name('payments.reject');

    // Seller Payouts & Disbursements
    Route::get('/payouts', [\App\Http\Controllers\Admin\PayoutController::class, 'index'])->name('payouts.index');
    Route::post('/payouts/{payout}/approve', [\App\Http\Controllers\Admin\PayoutController::class, 'approve'])->name('payouts.approve');
    Route::post('/payouts/{payout}/reject', [\App\Http\Controllers\Admin\PayoutController::class, 'reject'])->name('payouts.reject');

    // Seller Commission Collections
    Route::get('/commissions', [\App\Http\Controllers\Admin\CommissionController::class, 'index'])->name('commissions.index');
    Route::post('/commissions/{settlement}/approve', [\App\Http\Controllers\Admin\CommissionController::class, 'approve'])->name('commissions.approve');
    Route::post('/commissions/{settlement}/reject', [\App\Http\Controllers\Admin\CommissionController::class, 'reject'])->name('commissions.reject');

    // Reports & Analytics + CSV Export
    Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [\App\Http\Controllers\Admin\ReportController::class, 'exportCsv'])->name('reports.export');
});


