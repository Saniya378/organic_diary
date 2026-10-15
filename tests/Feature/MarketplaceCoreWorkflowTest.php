<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\SellerProfile;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketplaceCoreWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected User $approvedSellerA;
    protected User $approvedSellerB;
    protected User $pendingSeller;
    protected User $buyerUser;
    protected Category $category;
    protected Product $productA;
    protected Product $productB;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        Setting::set('delivery_fee', '150');
        Setting::set('commission_percent', '5');
        Setting::set('low_stock_threshold', '10');
        Setting::set('allowed_cities', json_encode(['Gilgit', 'Skardu', 'Hunza']));

        // Admin User
        $this->adminUser = User::firstOrCreate(
            ['email' => 'core_admin@organicdairy.test'],
            [
                'name' => 'Core Admin',
                'phone' => '03550000001',
                'password' => Hash::make('Admin@12345'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        // Seller A
        $this->approvedSellerA = User::firstOrCreate(
            ['email' => 'seller_a@organicdairy.test'],
            [
                'name' => 'Hunza Farm A',
                'phone' => '03550000002',
                'password' => Hash::make('Seller@12345'),
                'role' => 'seller',
                'status' => 'active',
            ]
        );
        SellerProfile::firstOrCreate(
            ['user_id' => $this->approvedSellerA->id],
            [
                'farm_name' => 'Hunza Mountain Dairy A',
                'location' => 'Karimabad, Hunza',
                'status' => 'approved',
            ]
        );

        // Seller B
        $this->approvedSellerB = User::firstOrCreate(
            ['email' => 'seller_b@organicdairy.test'],
            [
                'name' => 'Skardu Farm B',
                'phone' => '03550000003',
                'password' => Hash::make('Seller@12345'),
                'role' => 'seller',
                'status' => 'active',
            ]
        );
        SellerProfile::firstOrCreate(
            ['user_id' => $this->approvedSellerB->id],
            [
                'farm_name' => 'Skardu Pasture Farm B',
                'location' => 'Sadpara, Skardu',
                'status' => 'approved',
            ]
        );

        // Pending Seller
        $this->pendingSeller = User::firstOrCreate(
            ['email' => 'pending_seller@organicdairy.test'],
            [
                'name' => 'Unapproved Seller',
                'phone' => '03550000004',
                'password' => Hash::make('Seller@12345'),
                'role' => 'seller',
                'status' => 'active',
            ]
        );
        SellerProfile::firstOrCreate(
            ['user_id' => $this->pendingSeller->id],
            [
                'farm_name' => 'Astore Valley Pending',
                'location' => 'Astore',
                'status' => 'pending',
            ]
        );

        // Buyer
        $this->buyerUser = User::firstOrCreate(
            ['email' => 'core_buyer@organicdairy.test'],
            [
                'name' => 'Core Buyer User',
                'phone' => '03550000005',
                'password' => Hash::make('Buyer@12345'),
                'role' => 'buyer',
                'status' => 'active',
            ]
        );

        // Category
        $this->category = Category::firstOrCreate(
            ['slug' => 'dairy-core-category'],
            ['name' => 'Dairy Core Category', 'is_active' => true]
        );

        // Products
        $this->productA = Product::create([
            'seller_id' => $this->approvedSellerA->id,
            'category_id' => $this->category->id,
            'name' => 'Product Farm A Cow Milk',
            'slug' => 'product-farm-a-cow-milk-' . uniqid(),
            'description' => 'Fresh milk from Farm A',
            'price' => 200,
            'unit' => '1 Litre',
            'stock' => 50,
            'is_organic' => true,
            'status' => 'approved',
        ]);

        $this->productB = Product::create([
            'seller_id' => $this->approvedSellerB->id,
            'category_id' => $this->category->id,
            'name' => 'Product Farm B Desi Ghee',
            'slug' => 'product-farm-b-desi-ghee-' . uniqid(),
            'description' => 'Pure ghee from Farm B',
            'price' => 1500,
            'unit' => '500g',
            'stock' => 20,
            'is_organic' => true,
            'status' => 'approved',
        ]);
    }

    /**
     * 1. Registration and Role Redirects
     */
    public function test_registration_and_role_redirects(): void
    {
        // Buyer Registration -> Redirects to OTP verification then intended / home
        $buyerEmail = 'reg_buyer_' . uniqid() . '@organicdairy.test';
        $buyerResponse = $this->post(route('register.post'), [
            'name' => 'New Buyer',
            'email' => $buyerEmail,
            'phone' => '03559998877',
            'role' => 'buyer',
            'password' => 'Buyer@12345',
            'password_confirmation' => 'Buyer@12345',
        ]);
        $buyerResponse->assertRedirect('/register/verify-otp');
        $buyerOtp = \App\Models\EmailOtp::where('email', $buyerEmail)->where('type', 'registration')->latest()->first();
        $this->assertNotNull($buyerOtp);
        $buyerVerify = $this->post('/register/verify-otp', ['otp' => $buyerOtp->otp]);
        $buyerVerify->assertRedirect(url('/'));
        $this->assertDatabaseHas('users', ['email' => $buyerEmail, 'role' => 'buyer']);

        // Log out before testing next registration
        $this->post(route('logout'));

        // Seller Registration -> Creates pending seller profile and redirects to seller dashboard with banner
        Storage::fake('public');
        $sellerEmail = 'reg_seller_' . uniqid() . '@organicdairy.test';
        $sellerResponse = $this->post(route('register.post'), [
            'name' => 'New Seller',
            'email' => $sellerEmail,
            'phone' => '03559998866',
            'role' => 'seller',
            'farm_name' => 'Highland Valley Farm',
            'location' => 'Ghizer',
            'description' => 'Organic family farm',
            'cnic_no' => '71101-1234567-1',
            'cnic_front' => UploadedFile::fake()->create('cnic_front.jpg', 200, 'image/jpeg'),
            'cnic_back' => UploadedFile::fake()->create('cnic_back.jpg', 200, 'image/jpeg'),
            'password' => 'Seller@12345',
            'password_confirmation' => 'Seller@12345',
        ]);
        $sellerResponse->assertRedirect('/register/verify-otp');
        $sellerOtp = \App\Models\EmailOtp::where('email', $sellerEmail)->where('type', 'registration')->latest()->first();
        $this->assertNotNull($sellerOtp);
        $sellerVerify = $this->post('/register/verify-otp', ['otp' => $sellerOtp->otp]);
        $sellerVerify->assertRedirect(route('seller.dashboard'));
        $this->assertDatabaseHas('seller_profiles', ['farm_name' => 'Highland Valley Farm', 'status' => 'pending']);

        // Log out before login tests
        $this->post(route('logout'));

        // Admin Login -> Redirects to Admin Dashboard
        $adminLoginResponse = $this->post(route('login.post'), [
            'email' => $this->adminUser->email,
            'password' => 'Admin@12345',
        ]);
        $adminLoginResponse->assertRedirect(route('admin.dashboard'));

        $this->post(route('logout'));

        // Seller Login -> Redirects to Seller Dashboard
        $sellerLoginResponse = $this->post(route('login.post'), [
            'email' => $this->approvedSellerA->email,
            'password' => 'Seller@12345',
        ]);
        $sellerLoginResponse->assertRedirect(route('seller.dashboard'));
    }

    /**
     * 2. Seller Pending Cannot Publish
     */
    public function test_seller_pending_cannot_publish_products(): void
    {
        // Pending seller tries to view product creation page -> Blocked/Redirected with notice
        $createResponse = $this->actingAs($this->pendingSeller)->get(route('seller.products.create'));
        $createResponse->assertRedirect(route('seller.products.index'));
        $createResponse->assertSessionHas('error');

        // Pending seller tries to post new product -> Blocked with 403 authorization refusal
        $storeResponse = $this->actingAs($this->pendingSeller)->post(route('seller.products.store'), [
            'name' => 'Pending Cheese Block',
            'category_id' => $this->category->id,
            'price' => 800,
            'unit' => '500g',
            'stock' => 10,
        ]);
        $storeResponse->assertStatus(403);
        $this->assertDatabaseMissing('products', ['name' => 'Pending Cheese Block']);
    }

    /**
     * 3. Cart Stock Limit
     */
    public function test_cart_stock_limit_enforcement(): void
    {
        // Add item with quantity greater than stock (product A has stock 50)
        $response = $this->actingAs($this->buyerUser)->postJson(route('cart.store'), [
            'product_id' => $this->productA->id,
            'quantity' => 100, // exceeds 50
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // Cart quantity must be capped at 50
        $cart = Cart::where('user_id', $this->buyerUser->id)->first();
        $this->assertNotNull($cart);
        $this->assertEquals(50, $cart->items->first()->quantity);

        // Attempting to step beyond stock via PUT /cart/{id}
        $updateResponse = $this->actingAs($this->buyerUser)->putJson(route('cart.update', $cart->items->first()->id), [
            'quantity' => 75,
        ]);
        $updateResponse->assertStatus(200);
        $this->assertEquals(50, $cart->items->first()->fresh()->quantity);
        $this->assertTrue($updateResponse->json('is_capped'));
    }

    /**
     * 4. Checkout Split per Seller with Stock Reduction
     */
    public function test_checkout_splits_per_seller_with_stock_reduction(): void
    {
        $cart = Cart::firstOrCreate(['user_id' => $this->buyerUser->id]);
        $cart->items()->delete();

        // Add 2 units of Product A (Seller A) and 3 units of Product B (Seller B)
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->productA->id,
            'quantity' => 2,
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->productB->id,
            'quantity' => 3,
        ]);

        $initialStockA = $this->productA->stock; // 50
        $initialStockB = $this->productB->stock; // 20

        $response = $this->actingAs($this->buyerUser)->post(route('checkout.store'), [
            'shipping_full_name' => 'Ali Khan',
            'shipping_phone' => '03551234567',
            'shipping_city' => 'Gilgit',
            'shipping_area' => 'Jutial',
            'shipping_address_line' => 'House 10, River View',
            'payment_method' => 'cod',
        ]);

        $response->assertRedirect();

        // 1 OrderGroup created
        $orderGroup = OrderGroup::where('user_id', $this->buyerUser->id)->latest()->first();
        $this->assertNotNull($orderGroup);

        // Exactly 2 sub-orders created under the group (one for Seller A, one for Seller B)
        $orders = Order::where('order_group_id', $orderGroup->id)->get();
        $this->assertCount(2, $orders);

        $orderA = $orders->where('seller_id', $this->approvedSellerA->id)->first();
        $orderB = $orders->where('seller_id', $this->approvedSellerB->id)->first();
        $this->assertNotNull($orderA);
        $this->assertNotNull($orderB);

        // Stock immediately reduced by atomic checkout transaction
        $this->assertEquals($initialStockA - 2, $this->productA->fresh()->stock);
        $this->assertEquals($initialStockB - 3, $this->productB->fresh()->stock);
    }

    /**
     * 5. Failed Payment Keeps Pending Payment Status
     */
    public function test_failed_payment_keeps_pending_payment_status(): void
    {
        $cart = Cart::firstOrCreate(['user_id' => $this->buyerUser->id]);
        $cart->items()->delete();

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->productA->id,
            'quantity' => 1,
        ]);

        // Place card order with all required card validation fields
        $this->actingAs($this->buyerUser)->post(route('checkout.store'), [
            'shipping_full_name' => 'Ali Khan',
            'shipping_phone' => '03551234567',
            'shipping_city' => 'Gilgit',
            'shipping_area' => 'Jutial',
            'shipping_address_line' => 'House 10',
            'payment_method' => 'card',
            'card_name' => 'Ali Khan',
            'card_number' => '4242424242424242',
            'card_expiry' => '12/28',
            'card_cvc' => '123',
        ]);

        $orderGroup = OrderGroup::where('user_id', $this->buyerUser->id)->latest()->first();
        $this->assertNotNull($orderGroup);
        $order = Order::where('order_group_id', $orderGroup->id)->first();
        $this->assertNotNull($order);

        // Initial status is pending_payment
        $this->assertEquals('pending_payment', $order->status);
        $this->assertContains($order->payment_status, ['pending', 'unpaid']);

        // Simulate failed payment gateway response
        $failResponse = $this->actingAs($this->buyerUser)->post(route('checkout.payment.mock.process', $orderGroup), [
            'action' => 'fail',
        ]);

        $failResponse->assertRedirect();
        $this->assertEquals('pending_payment', $order->fresh()->status);
        $this->assertContains($order->fresh()->payment_status, ['pending', 'unpaid', 'failed']);
    }

    /**
     * 6. Cancel Allowed Only at Placed / Confirmed Status
     */
    public function test_cancel_allowed_only_at_placed_or_confirmed(): void
    {
        $group = OrderGroup::create([
            'user_id' => $this->buyerUser->id,
            'group_no' => 'GRP-CANCEL-TEST',
            'subtotal' => 400,
            'delivery_total' => 150,
            'discount' => 0,
            'grand_total' => 550,
            'payment_method' => 'cod',
            'shipping_full_name' => 'Buyer',
            'shipping_phone' => '03551234567',
            'shipping_city' => 'Gilgit',
            'shipping_area' => 'Jutial',
            'shipping_address_line' => 'House 1',
        ]);

        $order = Order::create([
            'order_group_id' => $group->id,
            'order_no' => 'OD-CAN-01',
            'buyer_id' => $this->buyerUser->id,
            'seller_id' => $this->approvedSellerA->id,
            'subtotal' => 400,
            'delivery_fee' => 150,
            'commission' => 20,
            'total' => 550,
            'status' => 'placed',
            'payment_status' => 'unpaid',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->productA->id,
            'product_name' => $this->productA->name,
            'unit_price' => 200,
            'quantity' => 2,
            'line_total' => 400,
        ]);

        $stockBefore = $this->productA->fresh()->stock;

        // Cancellation at 'placed' succeeds and restores stock
        $cancelResponse = $this->actingAs($this->buyerUser)->post(route('orders.cancel', $order), [
            'cancel_reason' => 'Changed my mind',
        ]);
        $cancelResponse->assertRedirect();
        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals($stockBefore + 2, $this->productA->fresh()->stock);

        // Attempting to cancel an order already at 'packing' is refused with 403
        $packedOrder = Order::create([
            'order_group_id' => $group->id,
            'order_no' => 'OD-CAN-02',
            'buyer_id' => $this->buyerUser->id,
            'seller_id' => $this->approvedSellerA->id,
            'subtotal' => 400,
            'delivery_fee' => 150,
            'commission' => 20,
            'total' => 550,
            'status' => 'packing',
            'payment_status' => 'unpaid',
        ]);

        $failCancel = $this->actingAs($this->buyerUser)->post(route('orders.cancel', $packedOrder), [
            'cancel_reason' => 'Too late',
        ]);
        $failCancel->assertStatus(403);
        $this->assertEquals('packing', $packedOrder->fresh()->status);
    }

    /**
     * 7. Status Workflow Order (Step-by-Step Single Progression)
     */
    public function test_status_workflow_progression(): void
    {
        $group = OrderGroup::create([
            'user_id' => $this->buyerUser->id,
            'group_no' => 'GRP-WF-TEST',
            'subtotal' => 200,
            'delivery_total' => 150,
            'discount' => 0,
            'grand_total' => 350,
            'payment_method' => 'cod',
            'shipping_full_name' => 'Buyer',
            'shipping_phone' => '03551234567',
            'shipping_city' => 'Gilgit',
            'shipping_area' => 'Jutial',
            'shipping_address_line' => 'House 1',
        ]);

        $order = Order::create([
            'order_group_id' => $group->id,
            'order_no' => 'OD-WF-01',
            'buyer_id' => $this->buyerUser->id,
            'seller_id' => $this->approvedSellerA->id,
            'subtotal' => 200,
            'delivery_fee' => 150,
            'commission' => 10,
            'total' => 350,
            'status' => 'placed',
            'payment_status' => 'unpaid',
        ]);

        // Step 1: placed -> confirmed
        $this->actingAs($this->approvedSellerA)->post(route('seller.orders.advance-status', $order), [
            'target_status' => 'confirmed',
        ]);
        $this->assertEquals('confirmed', $order->fresh()->status);

        // Step 2: confirmed -> packing
        $this->actingAs($this->approvedSellerA)->post(route('seller.orders.advance-status', $order), [
            'target_status' => 'packing',
        ]);
        $this->assertEquals('packing', $order->fresh()->status);

        // Invalid jump: packing -> delivered (skipping in_transit) is blocked
        $invalidJump = $this->actingAs($this->approvedSellerA)->post(route('seller.orders.advance-status', $order), [
            'target_status' => 'delivered',
        ]);
        $invalidJump->assertRedirect();
        $invalidJump->assertSessionHas('error');
        $this->assertEquals('packing', $order->fresh()->status);

        // Step 3: packing -> in_transit (with rider details)
        $this->actingAs($this->approvedSellerA)->post(route('seller.orders.advance-status', $order), [
            'target_status' => 'in_transit',
            'rider_name' => 'Karim Shah',
            'rider_phone' => '03551122334',
        ]);
        $this->assertEquals('in_transit', $order->fresh()->status);
        $this->assertEquals('Karim Shah', $order->fresh()->rider_name);

        // Step 4: in_transit -> delivered
        $this->actingAs($this->approvedSellerA)->post(route('seller.orders.advance-status', $order), [
            'target_status' => 'delivered',
        ]);
        $this->assertEquals('delivered', $order->fresh()->status);
    }

    /**
     * 8. Seller A Cannot Access Seller B's Order (403 Forbidden)
     */
    public function test_seller_a_cannot_access_seller_b_order(): void
    {
        $group = OrderGroup::create([
            'user_id' => $this->buyerUser->id,
            'group_no' => 'GRP-ISO-TEST',
            'subtotal' => 1500,
            'delivery_total' => 150,
            'discount' => 0,
            'grand_total' => 1650,
            'payment_method' => 'cod',
            'shipping_full_name' => 'Buyer',
            'shipping_phone' => '03551234567',
            'shipping_city' => 'Gilgit',
            'shipping_area' => 'Jutial',
            'shipping_address_line' => 'House 1',
        ]);

        // Order belongs to Seller B
        $orderB = Order::create([
            'order_group_id' => $group->id,
            'order_no' => 'OD-SEC-B',
            'buyer_id' => $this->buyerUser->id,
            'seller_id' => $this->approvedSellerB->id,
            'subtotal' => 1500,
            'delivery_fee' => 150,
            'commission' => 75,
            'total' => 1650,
            'status' => 'placed',
            'payment_status' => 'unpaid',
        ]);

        // Seller A tries to inspect Seller B's order -> 403
        $showResponse = $this->actingAs($this->approvedSellerA)->get(route('seller.orders.show', $orderB));
        $showResponse->assertStatus(403);

        // Seller A tries to advance status on Seller B's order -> 403
        $advanceResponse = $this->actingAs($this->approvedSellerA)->post(route('seller.orders.advance-status', $orderB), [
            'target_status' => 'confirmed',
        ]);
        $advanceResponse->assertStatus(403);
    }

    /**
     * 9. Review Only After Delivery
     */
    public function test_review_allowed_only_after_successful_delivery(): void
    {
        $group = OrderGroup::create([
            'user_id' => $this->buyerUser->id,
            'group_no' => 'GRP-REV-GATE',
            'subtotal' => 200,
            'delivery_total' => 150,
            'discount' => 0,
            'grand_total' => 350,
            'payment_method' => 'cod',
            'shipping_full_name' => 'Buyer',
            'shipping_phone' => '03551234567',
            'shipping_city' => 'Gilgit',
            'shipping_area' => 'Jutial',
            'shipping_address_line' => 'House 1',
        ]);

        // Order in 'confirmed' (not delivered yet)
        $order = Order::create([
            'order_group_id' => $group->id,
            'order_no' => 'OD-GATE-01',
            'buyer_id' => $this->buyerUser->id,
            'seller_id' => $this->approvedSellerA->id,
            'subtotal' => 200,
            'delivery_fee' => 150,
            'commission' => 10,
            'total' => 350,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->productA->id,
            'product_name' => $this->productA->name,
            'unit_price' => 200,
            'quantity' => 1,
            'line_total' => 200,
        ]);

        // Attempting review while status is 'confirmed' is rejected
        $reviewBeforeDelivery = $this->actingAs($this->buyerUser)->post(route('products.reviews.store', $this->productA), [
            'rating' => 5,
            'comment' => 'Tastes good but order not delivered yet',
            'order_id' => $order->id,
        ]);
        $reviewBeforeDelivery->assertRedirect();
        $reviewBeforeDelivery->assertSessionHas('error');
        $this->assertDatabaseMissing('reviews', ['order_id' => $order->id]);

        // Update status to 'delivered'
        $order->update(['status' => 'delivered']);

        // Now review succeeds
        $reviewAfterDelivery = $this->actingAs($this->buyerUser)->post(route('products.reviews.store', $this->productA), [
            'rating' => 5,
            'comment' => 'Arrived fresh and delicious!',
            'order_id' => $order->id,
        ]);
        $reviewAfterDelivery->assertRedirect();
        $reviewAfterDelivery->assertSessionHas('success');
        $this->assertDatabaseHas('reviews', ['order_id' => $order->id, 'rating' => 5]);
    }
}
