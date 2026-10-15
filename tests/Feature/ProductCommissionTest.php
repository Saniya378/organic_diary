<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\Setting;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductCommissionTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $seller;
    protected User $buyer;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('commission_percent', '5');
        Setting::set('delivery_fee', '150');

        $this->admin = User::firstOrCreate(
            ['email' => 'admin@organicdairy.test'],
            ['name' => 'Admin User', 'password' => bcrypt('Admin@12345'), 'role' => 'admin', 'status' => 'active']
        );
        $this->admin->update(['role' => 'admin', 'status' => 'active']);

        $this->seller = User::firstOrCreate(
            ['email' => 'farmer_comm@organicdairy.test'],
            ['name' => 'Farmer User', 'password' => bcrypt('Seller@12345'), 'role' => 'seller', 'status' => 'active']
        );
        $this->seller->update(['role' => 'seller', 'status' => 'active']);

        SellerProfile::firstOrCreate(
            ['user_id' => $this->seller->id],
            [
                'farm_name' => 'Karakoram Organic Farm',
                'location' => 'Hunza Valley',
                'description' => 'Organic Himalayan Farm',
                'status' => 'approved',
            ]
        );

        $this->buyer = User::firstOrCreate(
            ['email' => 'buyer_comm@organicdairy.test'],
            ['name' => 'Buyer User', 'password' => bcrypt('Buyer@12345'), 'role' => 'buyer', 'status' => 'active']
        );
        $this->buyer->update(['role' => 'buyer', 'status' => 'active']);

        $this->category = Category::firstOrCreate(
            ['slug' => 'organic-milk-dairy'],
            ['name' => 'Organic Milk & Dairy', 'is_active' => true, 'commission_rate' => null]
        );
    }

    public function test_admin_can_update_product_commission_rate(): void
    {
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'name' => 'Grass-fed Butter',
            'slug' => 'grass-fed-butter-' . uniqid(),
            'price' => 500,
            'unit' => '500g',
            'stock' => 50,
            'status' => 'approved',
            'commission_rate' => null,
        ]);

        $this->assertEquals(5.0, $product->effective_commission_rate);

        $response = $this->actingAs($this->admin)->post(route('admin.products.commission', $product), [
            'commission_rate' => 12.5,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $product->refresh();
        $this->assertEquals(12.5, $product->commission_rate);
        $this->assertEquals(12.5, $product->effective_commission_rate);
    }

    public function test_checkout_calculates_product_specific_commission(): void
    {
        // Product A with custom 10% commission
        $productA = Product::create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'name' => 'Artisanal Yak Cheese',
            'slug' => 'artisanal-yak-cheese-' . uniqid(),
            'price' => 1000,
            'unit' => '500g',
            'stock' => 20,
            'status' => 'approved',
            'commission_rate' => 10.0,
        ]);

        // Product B with default (5%) commission
        $productB = Product::create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'name' => 'Fresh Cow Milk',
            'slug' => 'fresh-cow-milk-' . uniqid(),
            'price' => 200,
            'unit' => '1L',
            'stock' => 50,
            'status' => 'approved',
            'commission_rate' => null, // falls back to global 5%
        ]);

        $cart = Cart::create(['user_id' => $this->buyer->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $productA->id,
            'quantity' => 2, // Total = 2000 => 10% commission = 200
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $productB->id,
            'quantity' => 3, // Total = 600 => 5% commission = 30
        ]);

        $deliveryData = [
            'shipping_full_name' => 'Ali Khan',
            'shipping_phone' => '03001234567',
            'shipping_city' => 'Gilgit',
            'shipping_area' => 'Jutial',
            'shipping_address_line' => 'House 45, Street 2',
        ];

        $checkoutService = app(CheckoutService::class);
        $result = $checkoutService->createOrderGroup($this->buyer, $deliveryData, 'cod');

        $orderGroup = $result['order_group'];
        $order = Order::where('order_group_id', $orderGroup->id)->first();

        $this->assertNotNull($order);
        $this->assertEquals(2600, $order->subtotal);
        // Expected commission: 200 (product A) + 30 (product B) = 230
        $this->assertEquals(230, $order->commission);

        // Verify Order Items recorded correct commission rates and amounts
        $itemA = OrderItem::where('order_id', $order->id)->where('product_id', $productA->id)->first();
        $this->assertNotNull($itemA);
        $this->assertEquals(10.0, $itemA->commission_rate);
        $this->assertEquals(200, $itemA->commission_amount);

        $itemB = OrderItem::where('order_id', $order->id)->where('product_id', $productB->id)->first();
        $this->assertNotNull($itemB);
        $this->assertEquals(5.0, $itemB->commission_rate);
        $this->assertEquals(30, $itemB->commission_amount);
    }

    public function test_category_commission_fallback(): void
    {
        $cat = Category::firstOrCreate(
            ['slug' => 'premium-cheese'],
            ['name' => 'Premium Cheese', 'is_active' => true, 'commission_rate' => 8.0]
        );
        $cat->update(['commission_rate' => 8.0]);

        $product = Product::create([
            'seller_id' => $this->seller->id,
            'category_id' => $cat->id,
            'name' => 'Aged Cheddar',
            'slug' => 'aged-cheddar-' . uniqid(),
            'price' => 1500,
            'unit' => '1kg',
            'stock' => 10,
            'status' => 'approved',
            'commission_rate' => null, // will fall back to category rate (8%)
        ]);

        $this->assertEquals(8.0, $product->effective_commission_rate);
        $this->assertEquals(120, $product->calculateCommission(1500)); // 8% of 1500 = 120
    }
}
