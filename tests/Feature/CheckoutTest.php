<?php

namespace Tests\Feature;

use App\Mail\NewOrderSellerMail;
use App\Mail\OrderPlacedBuyerMail;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SellerProfile;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use DatabaseTransactions;

    protected User $buyer;
    protected User $seller1;
    protected User $seller2;
    protected Category $category;
    protected Product $product1;
    protected Product $product2;
    protected ProductVariant $variant1;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('delivery_fee', '150');
        Setting::set('commission_percent', '5');
        Setting::set('allowed_cities', json_encode(['Gilgit', 'Skardu', 'Hunza', 'Ghizer', 'Astore', 'Nagar', 'Diamer']));

        $this->buyer = User::factory()->create([
            'name' => 'Test Buyer Gilgit',
            'email' => 'testbuyer@organicdairy.test',
            'phone' => '03551112233',
            'role' => 'buyer',
            'status' => 'active',
        ]);

        Address::create([
            'user_id' => $this->buyer->id,
            'full_name' => 'Test Buyer Gilgit',
            'phone' => '03551112233',
            'city' => 'Gilgit',
            'area' => 'Jutial',
            'address_line' => 'House 12, River View Road',
            'is_default' => true,
        ]);

        $this->seller1 = User::factory()->create([
            'name' => 'Hunza Valley Seller',
            'email' => 'seller1@organicdairy.test',
            'role' => 'seller',
            'status' => 'active',
        ]);
        SellerProfile::create([
            'user_id' => $this->seller1->id,
            'farm_name' => 'Hunza Valley Farm',
            'location' => 'Hunza',
            'status' => 'approved',
        ]);

        $this->seller2 = User::factory()->create([
            'name' => 'Skardu Co-op Seller',
            'email' => 'seller2@organicdairy.test',
            'role' => 'seller',
            'status' => 'active',
        ]);
        SellerProfile::create([
            'user_id' => $this->seller2->id,
            'farm_name' => 'Skardu Co-op Farm',
            'location' => 'Skardu',
            'status' => 'approved',
        ]);

        $this->category = Category::create([
            'name' => 'Fresh Milk & Ghee',
            'slug' => 'fresh-milk-ghee-' . uniqid(),
            'is_active' => true,
        ]);

        $this->product1 = Product::create([
            'seller_id' => $this->seller1->id,
            'category_id' => $this->category->id,
            'name' => 'Hunza Raw Milk',
            'slug' => 'hunza-raw-milk-' . uniqid(),
            'description' => 'Pure milk',
            'price' => 200,
            'unit' => '1 Litre',
            'stock' => 50,
            'status' => 'approved',
            'is_organic' => true,
        ]);

        $this->variant1 = ProductVariant::create([
            'product_id' => $this->product1->id,
            'label' => '1 Litre Bottle',
            'price' => 200,
            'stock' => 30,
        ]);

        $this->product2 = Product::create([
            'seller_id' => $this->seller2->id,
            'category_id' => $this->category->id,
            'name' => 'Skardu Desi Ghee',
            'slug' => 'skardu-desi-ghee-' . uniqid(),
            'description' => 'Pure ghee',
            'price' => 1500,
            'unit' => '500 g',
            'stock' => 20,
            'status' => 'approved',
            'is_organic' => true,
        ]);
    }

    public function test_guest_is_redirected_to_login_from_checkout(): void
    {
        $response = $this->get(route('checkout.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_buyer_with_empty_cart_is_redirected_to_cart(): void
    {
        $response = $this->actingAs($this->buyer)->get(route('checkout.index'));
        $response->assertRedirect(route('cart.index'));
    }

    public function test_buyer_with_items_can_view_checkout_page(): void
    {
        $cart = Cart::create(['user_id' => $this->buyer->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product1->id,
            'variant_id' => $this->variant1->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->buyer)->get(route('checkout.index'));
        $response->assertStatus(200);
        $response->assertSee('Delivery Information');
        $response->assertSee('Cash on Delivery');
        $response->assertSee('Gilgit');
    }

    public function test_cod_checkout_splits_multi_vendor_reduces_stock_immediately_and_sends_emails(): void
    {
        Mail::fake();

        $cart = Cart::create(['user_id' => $this->buyer->id]);
        // Seller 1 item
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product1->id,
            'variant_id' => $this->variant1->id,
            'quantity' => 3, // 3 * 200 = 600
        ]);
        // Seller 2 item
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product2->id,
            'variant_id' => null,
            'quantity' => 1, // 1 * 1500 = 1500
        ]);

        $initialP1Stock = $this->product1->fresh()->stock;
        $initialV1Stock = $this->variant1->fresh()->stock;
        $initialP2Stock = $this->product2->fresh()->stock;

        $response = $this->actingAs($this->buyer)->post(route('checkout.store'), [
            'shipping_full_name' => 'Ali Khan',
            'shipping_phone' => '03551234567',
            'shipping_city' => 'Hunza',
            'shipping_area' => 'Aliabad',
            'shipping_address_line' => 'Main Bazaar near Bank',
            'payment_method' => 'cod',
        ]);

        $orderGroup = OrderGroup::where('user_id', $this->buyer->id)->latest()->first();
        $this->assertNotNull($orderGroup);
        $response->assertRedirect(route('checkout.success', $orderGroup->group_no));

        // Total calculation: Subtotal = 600 + 1500 = 2100. Delivery = 150 * 2 sellers = 300. Grand total = 2400.
        $this->assertEquals(2100, $orderGroup->subtotal);
        $this->assertEquals(300, $orderGroup->delivery_total);
        $this->assertEquals(2400, $orderGroup->grand_total);
        $this->assertEquals('cod', $orderGroup->payment_method);

        // 2 sub-orders created for 2 sellers
        $orders = $orderGroup->orders()->get();
        $this->assertCount(2, $orders);

        foreach ($orders as $order) {
            $this->assertEquals('placed', $order->status);
            $this->assertEquals('unpaid', $order->payment_status);
            $this->assertStringStartsWith('OD-', $order->order_no);
            $numPart = (int) substr($order->order_no, 3);
            $this->assertGreaterThanOrEqual(20500, $numPart);

            // Verify order_status_history
            $this->assertDatabaseHas('order_status_history', [
                'order_id' => $order->id,
                'status' => 'placed',
            ]);
        }

        // Verify stock was reduced immediately for COD
        $this->assertEquals($initialV1Stock - 3, $this->variant1->fresh()->stock);
        $this->assertEquals($initialP1Stock - 3, $this->product1->fresh()->stock);
        $this->assertEquals($initialP2Stock - 1, $this->product2->fresh()->stock);

        // Verify cart is cleared
        $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);

        // Verify emails sent
        Mail::assertSent(OrderPlacedBuyerMail::class, function ($mail) use ($orderGroup) {
            return $mail->orderGroup->id === $orderGroup->id;
        });

        Mail::assertSent(NewOrderSellerMail::class, 2);
    }

    public function test_card_payment_creates_pending_order_and_handles_simulator_success(): void
    {
        Mail::fake();

        $cart = Cart::create(['user_id' => $this->buyer->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product1->id,
            'variant_id' => $this->variant1->id,
            'quantity' => 2,
        ]);

        $initialStock = $this->variant1->fresh()->stock;

        $response = $this->actingAs($this->buyer)->post(route('checkout.store'), [
            'shipping_full_name' => 'Fatima Zahra',
            'shipping_phone' => '03129876543',
            'shipping_city' => 'Skardu',
            'shipping_area' => 'Hameed Garh',
            'shipping_address_line' => 'House 4, Hospital Road',
            'payment_method' => 'card',
            'card_name' => 'Fatima Zahra',
            'card_number' => '4242 4242 4242 1234',
            'card_expiry' => '12/28',
            'card_cvc' => '123',
        ]);

        $orderGroup = OrderGroup::where('user_id', $this->buyer->id)->latest()->first();
        $this->assertNotNull($orderGroup);
        $response->assertRedirect(route('checkout.payment.mock', $orderGroup->id));

        // Check order is pending_payment and stock NOT reduced yet
        $order = $orderGroup->orders->first();
        $this->assertEquals('pending_payment', $order->status);
        $this->assertEquals('pending', $order->payment_status);
        $this->assertEquals($initialStock, $this->variant1->fresh()->stock);

        // Payment record stores last4 (1234) and NEVER card number
        $payment = Payment::where('order_group_id', $orderGroup->id)->first();
        $this->assertEquals('1234', $payment->last4);

        // View Mock Payment Simulator Page
        $mockView = $this->actingAs($this->buyer)->get(route('checkout.payment.mock', $orderGroup->id));
        $mockView->assertStatus(200);
        $mockView->assertSee('Digital Payment Gateway');
        $mockView->assertSee('Authorize & Pay (Success)', false);

        // Simulate Success
        $simResponse = $this->actingAs($this->buyer)->post(route('checkout.payment.mock.process', $orderGroup->id), [
            'action' => 'success',
            'last4' => '1234',
        ]);

        $simResponse->assertRedirect(route('checkout.success', $orderGroup->group_no));

        // Now order should be placed, payment paid, and stock reduced
        $this->assertEquals('placed', $order->fresh()->status);
        $this->assertEquals('paid', $order->fresh()->payment_status);
        $this->assertEquals($initialStock - 2, $this->variant1->fresh()->stock);

        // Status history recorded
        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $order->id,
            'status' => 'placed',
        ]);

        // Confirmation emails sent upon successful payment
        Mail::assertSent(OrderPlacedBuyerMail::class);
        Mail::assertSent(NewOrderSellerMail::class);
    }

    public function test_card_payment_failure_and_switch_to_cod(): void
    {
        Mail::fake();

        $cart = Cart::create(['user_id' => $this->buyer->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product1->id,
            'variant_id' => $this->variant1->id,
            'quantity' => 1,
        ]);

        $initialStock = $this->variant1->fresh()->stock;

        $this->actingAs($this->buyer)->post(route('checkout.store'), [
            'shipping_full_name' => 'Ali Khan',
            'shipping_phone' => '03551234567',
            'shipping_city' => 'Gilgit',
            'shipping_area' => 'Jutial',
            'shipping_address_line' => 'House 14',
            'payment_method' => 'card',
            'card_name' => 'Ali Khan',
            'card_number' => '4000 0000 0000 0002',
            'card_expiry' => '11/27',
            'card_cvc' => '456',
        ]);

        $orderGroup = OrderGroup::where('user_id', $this->buyer->id)->latest()->first();

        // Simulate Fail
        $failResponse = $this->actingAs($this->buyer)->post(route('checkout.payment.mock.process', $orderGroup->id), [
            'action' => 'fail',
        ]);

        $failResponse->assertRedirect(route('checkout.payment.mock', $orderGroup->id));
        $this->assertEquals('pending_payment', $orderGroup->orders->first()->fresh()->status);
        $this->assertEquals('failed', $orderGroup->orders->first()->fresh()->payment_status);
        $this->assertEquals($initialStock, $this->variant1->fresh()->stock);

        // Switch to COD
        $switchResponse = $this->actingAs($this->buyer)->post(route('checkout.payment.switch-cod', $orderGroup->id));
        $switchResponse->assertRedirect(route('checkout.success', $orderGroup->group_no));

        // Now order is placed, unpaid COD, and stock reduced
        $this->assertEquals('placed', $orderGroup->orders->first()->fresh()->status);
        $this->assertEquals('unpaid', $orderGroup->orders->first()->fresh()->payment_status);
        $this->assertEquals($initialStock - 1, $this->variant1->fresh()->stock);

        // Emails sent
        Mail::assertSent(OrderPlacedBuyerMail::class);
    }

    public function test_scheduled_command_cancels_unpaid_orders_older_than_30_minutes(): void
    {
        $group = OrderGroup::create([
            'user_id' => $this->buyer->id,
            'group_no' => 'GRP-OLD-TEST',
            'subtotal' => 500,
            'delivery_total' => 150,
            'discount' => 0,
            'grand_total' => 650,
            'payment_method' => 'card',
            'shipping_full_name' => 'Old Order Customer',
            'shipping_phone' => '03551111111',
            'shipping_city' => 'Gilgit',
            'shipping_area' => 'Jutial',
            'shipping_address_line' => 'Old Address',
            'created_at' => Carbon::now()->subMinutes(35),
            'updated_at' => Carbon::now()->subMinutes(35),
        ]);

        $order = Order::create([
            'order_group_id' => $group->id,
            'order_no' => 'OD-99991',
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller1->id,
            'subtotal' => 500,
            'delivery_fee' => 150,
            'commission' => 25,
            'total' => 650,
            'status' => 'pending_payment',
            'payment_status' => 'pending',
        ]);

        // Manually backdate created_at
        Order::where('id', $order->id)->update([
            'created_at' => Carbon::now()->subMinutes(35),
            'updated_at' => Carbon::now()->subMinutes(35),
        ]);

        Artisan::call('orders:cancel-unpaid');

        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals('failed', $order->fresh()->payment_status);
        $this->assertStringContainsString('expired after 30 minutes', $order->fresh()->cancel_reason);

        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $order->id,
            'status' => 'cancelled',
        ]);
    }
}
