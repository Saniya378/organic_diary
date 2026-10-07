<?php

namespace Tests\Feature;

use App\Mail\OrderStatusUpdatedBuyerMail;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SellerProfile;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SellerPanelTest extends TestCase
{
    use DatabaseTransactions;

    protected User $approvedSeller;
    protected User $pendingSeller;
    protected User $otherSeller;
    protected User $buyer;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        Setting::set('delivery_fee', '150');
        Setting::set('commission_percent', '5');
        Setting::set('low_stock_threshold', '10');

        $this->approvedSeller = User::factory()->create([
            'name' => 'Hunza Approved Seller',
            'email' => 'hunza.test@organicdairy.test',
            'role' => 'seller',
            'status' => 'active',
        ]);
        SellerProfile::create([
            'user_id' => $this->approvedSeller->id,
            'farm_name' => 'Hunza Valley Pure Farm',
            'location' => 'Karimabad, Hunza',
            'description' => 'Mountain pasture farm',
            'status' => 'approved',
        ]);

        $this->pendingSeller = User::factory()->create([
            'name' => 'Astore Pending Seller',
            'email' => 'astore.test@organicdairy.test',
            'role' => 'seller',
            'status' => 'active',
        ]);
        SellerProfile::create([
            'user_id' => $this->pendingSeller->id,
            'farm_name' => 'Astore Highland Dairy',
            'location' => 'Astore',
            'description' => 'Pending farm',
            'status' => 'pending',
        ]);

        $this->otherSeller = User::factory()->create([
            'name' => 'Skardu Seller',
            'email' => 'skardu.test@organicdairy.test',
            'role' => 'seller',
            'status' => 'active',
        ]);
        SellerProfile::create([
            'user_id' => $this->otherSeller->id,
            'farm_name' => 'Skardu Co-op',
            'location' => 'Skardu',
            'status' => 'approved',
        ]);

        $this->buyer = User::factory()->create([
            'name' => 'Buyer Test',
            'email' => 'buyer.test@organicdairy.test',
            'role' => 'buyer',
            'status' => 'active',
        ]);

        $this->category = Category::create([
            'name' => 'Dairy Ghee & Butter',
            'slug' => 'dairy-ghee-butter-' . uniqid(),
            'is_active' => true,
        ]);
    }

    public function test_buyer_cannot_access_seller_panel_routes(): void
    {
        $response = $this->actingAs($this->buyer)->get(route('seller.dashboard'));
        $response->assertStatus(403);
    }

    public function test_seller_can_view_dashboard_with_kpis_and_chart(): void
    {
        $response = $this->actingAs($this->approvedSeller)->get(route('seller.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Farmer Control Center');
        $response->assertSee('Today\'s Sales', false);
        $response->assertSee('New Orders');
        $response->assertSee('Active Products');
        $response->assertSee('Low Stock Alerts');
        $response->assertSee('weeklySalesChart');
    }

    public function test_seller_can_view_products_index_page(): void
    {
        $response = $this->actingAs($this->approvedSeller)->get(route('seller.products.index'));
        $response->assertStatus(200);
        $response->assertSee('My Dairy Products');
        $response->assertSee('All (');
    }

    public function test_unapproved_seller_cannot_create_products(): void
    {
        $response = $this->actingAs($this->pendingSeller)->get(route('seller.products.create'));
        $response->assertRedirect(route('seller.products.index'));
        $response->assertSessionHas('error');

        $postResponse = $this->actingAs($this->pendingSeller)->post(route('seller.products.store'), [
            'name' => 'Pending Product',
            'category_id' => $this->category->id,
            'description' => 'Test description',
            'price' => 500,
            'unit' => '1 kg',
            'stock' => 10,
        ]);

        $postResponse->assertStatus(403);
    }

    public function test_approved_seller_can_create_product_with_variants_and_images(): void
    {
        $image1 = UploadedFile::fake()->create('ghee1.jpg', 100, 'image/jpeg');
        $image2 = UploadedFile::fake()->create('ghee2.png', 100, 'image/png');

        $response = $this->actingAs($this->approvedSeller)->post(route('seller.products.store'), [
            'name' => 'Himalayan Churned Butter',
            'category_id' => $this->category->id,
            'description' => 'Traditionally churned fresh butter',
            'price' => 700,
            'unit' => '250 g',
            'stock' => 30,
            'batch_no' => 'HNZ-2026-01',
            'produced_on' => '2026-10-01',
            'is_organic' => '1',
            'images' => [$image1, $image2],
            'variants' => [
                ['label' => '250 g Tub', 'price' => 700, 'stock' => 20],
                ['label' => '500 g Tub', 'price' => 1350, 'stock' => 10],
            ],
        ]);

        $response->assertRedirect(route('seller.products.index'));
        $response->assertSessionHas('success');

        $product = Product::where('seller_id', $this->approvedSeller->id)->latest()->first();
        $this->assertNotNull($product);
        $this->assertEquals('Himalayan Churned Butter', $product->name);
        $this->assertEquals('pending', $product->status); // Status must be pending
        $this->assertEquals(2, $product->variants()->count());
        $this->assertEquals(2, $product->images()->count());
    }

    public function test_seller_can_edit_product_and_status_becomes_pending(): void
    {
        $product = Product::create([
            'seller_id' => $this->approvedSeller->id,
            'category_id' => $this->category->id,
            'name' => 'Original Cow Milk',
            'slug' => 'original-cow-milk-' . uniqid(),
            'description' => 'Original milk',
            'price' => 200,
            'unit' => '1 Litre',
            'stock' => 20,
            'status' => 'approved',
            'is_organic' => true,
        ]);

        $response = $this->actingAs($this->approvedSeller)->put(route('seller.products.update', $product->id), [
            'name' => 'Updated Organic Cow Milk',
            'category_id' => $this->category->id,
            'description' => 'Updated organic grass fed cow milk',
            'price' => 240,
            'unit' => '1 Litre',
            'stock' => 25,
            'batch_no' => 'HNZ-MLK-02',
            'is_organic' => '1',
        ]);

        $response->assertRedirect(route('seller.products.index'));

        $product->refresh();
        $this->assertEquals('Updated Organic Cow Milk', $product->name);
        $this->assertEquals(240, $product->price);
        $this->assertEquals('pending', $product->status); // Status reset to pending
    }

    public function test_seller_cannot_edit_or_delete_other_sellers_product(): void
    {
        $otherProduct = Product::create([
            'seller_id' => $this->otherSeller->id,
            'category_id' => $this->category->id,
            'name' => 'Skardu Cheese',
            'slug' => 'skardu-cheese-' . uniqid(),
            'description' => 'Other cheese',
            'price' => 900,
            'unit' => '300 g',
            'stock' => 15,
            'status' => 'approved',
            'is_organic' => true,
        ]);

        $editResponse = $this->actingAs($this->approvedSeller)->get(route('seller.products.edit', $otherProduct->id));
        $editResponse->assertStatus(403);

        $deleteResponse = $this->actingAs($this->approvedSeller)->delete(route('seller.products.destroy', $otherProduct->id));
        $deleteResponse->assertStatus(403);
    }

    public function test_seller_can_update_inventory_stock_inline_via_fetch(): void
    {
        $product = Product::create([
            'seller_id' => $this->approvedSeller->id,
            'category_id' => $this->category->id,
            'name' => 'Inventory Test Milk',
            'slug' => 'inventory-test-milk-' . uniqid(),
            'description' => 'Desc',
            'price' => 200,
            'unit' => '1 Litre',
            'stock' => 5,
            'status' => 'approved',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'label' => '500 ml',
            'price' => 110,
            'stock' => 8,
        ]);

        // Update product stock
        $response1 = $this->actingAs($this->approvedSeller)->patchJson(route('seller.inventory.update-stock', $product->id), [
            'stock' => 45,
        ]);
        $response1->assertStatus(200);
        $response1->assertJson(['success' => true, 'stock' => 45, 'is_low_stock' => false]);
        $this->assertEquals(45, $product->fresh()->stock);

        // Update variant stock
        $response2 = $this->actingAs($this->approvedSeller)->patchJson(route('seller.inventory.update-variant-stock', $variant->id), [
            'stock' => 4,
        ]);
        $response2->assertStatus(200);
        $response2->assertJson(['success' => true, 'stock' => 4, 'is_low_stock' => true]);
        $this->assertEquals(4, $variant->fresh()->stock);
    }

    public function test_seller_can_advance_order_status_one_step_at_a_time_and_emails_buyer(): void
    {
        Mail::fake();

        $group = OrderGroup::create([
            'user_id' => $this->buyer->id,
            'group_no' => 'GRP-ORDER-FLOW',
            'subtotal' => 600,
            'delivery_total' => 150,
            'discount' => 0,
            'grand_total' => 750,
            'payment_method' => 'cod',
            'shipping_full_name' => 'Buyer Test',
            'shipping_phone' => '03551234567',
            'shipping_city' => 'Gilgit',
            'shipping_area' => 'Jutial',
            'shipping_address_line' => 'House 1',
        ]);

        $order = Order::create([
            'order_group_id' => $group->id,
            'order_no' => 'OD-20888',
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->approvedSeller->id,
            'subtotal' => 600,
            'delivery_fee' => 150,
            'commission' => 30,
            'total' => 750,
            'status' => 'placed',
            'payment_status' => 'unpaid',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_name' => 'Test Cheese',
            'unit_price' => 600,
            'quantity' => 1,
            'line_total' => 600,
        ]);

        // Step 1: placed -> confirmed
        $res1 = $this->actingAs($this->approvedSeller)->post(route('seller.orders.advance-status', $order->id), [
            'target_status' => 'confirmed',
            'note' => 'Fresh batch confirmed',
        ]);
        $res1->assertRedirect();
        $this->assertEquals('confirmed', $order->fresh()->status);
        Mail::assertSent(OrderStatusUpdatedBuyerMail::class);

        // Step 2: confirmed -> packing
        $res2 = $this->actingAs($this->approvedSeller)->post(route('seller.orders.advance-status', $order->id), [
            'target_status' => 'packing',
        ]);
        $this->assertEquals('packing', $order->fresh()->status);

        // Step 3: packing -> in_transit (with rider details)
        $res3 = $this->actingAs($this->approvedSeller)->post(route('seller.orders.advance-status', $order->id), [
            'target_status' => 'in_transit',
            'rider_name' => 'Ali Rider',
            'rider_phone' => '03419998877',
        ]);
        $this->assertEquals('in_transit', $order->fresh()->status);
        $this->assertEquals('Ali Rider', $order->fresh()->rider_name);

        // Step 4: in_transit -> delivered (COD becomes paid)
        $res4 = $this->actingAs($this->approvedSeller)->post(route('seller.orders.advance-status', $order->id), [
            'target_status' => 'delivered',
        ]);
        $this->assertEquals('delivered', $order->fresh()->status);
        $this->assertEquals('paid', $order->fresh()->payment_status); // COD auto settled to paid

        // Verify status history count
        $this->assertEquals(4, $order->statusHistories()->count());
    }

    public function test_seller_cannot_make_invalid_status_jumps(): void
    {
        $group = OrderGroup::create([
            'user_id' => $this->buyer->id,
            'group_no' => 'GRP-JUMP-TEST',
            'subtotal' => 500,
            'delivery_total' => 150,
            'discount' => 0,
            'grand_total' => 650,
            'payment_method' => 'cod',
            'shipping_full_name' => 'Buyer Test',
            'shipping_phone' => '03551234567',
            'shipping_city' => 'Gilgit',
            'shipping_area' => 'Jutial',
            'shipping_address_line' => 'House 1',
        ]);

        $order = Order::create([
            'order_group_id' => $group->id,
            'order_no' => 'OD-20777',
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->approvedSeller->id,
            'subtotal' => 500,
            'delivery_fee' => 150,
            'commission' => 25,
            'total' => 650,
            'status' => 'placed',
            'payment_status' => 'unpaid',
        ]);

        // Attempting to jump placed -> delivered directly
        $response = $this->actingAs($this->approvedSeller)->post(route('seller.orders.advance-status', $order->id), [
            'target_status' => 'delivered',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals('placed', $order->fresh()->status);
    }

    public function test_seller_can_view_earnings_with_date_range(): void
    {
        $response = $this->actingAs($this->approvedSeller)->get(route('seller.earnings.index', [
            'preset' => 'this_month',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Gross Farm Sales');
        $response->assertSee('Platform Commission (5%)');
        $response->assertSee('Net Farmer Payable');
    }

    public function test_seller_can_update_farm_profile(): void
    {
        $fakePhoto = UploadedFile::fake()->create('farm.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->approvedSeller)->put(route('seller.profile.update'), [
            'farm_name' => 'Hunza Mountain Organics',
            'location' => 'Altit, Hunza',
            'description' => 'Updated high pasture farm story',
            'phone' => '03559998877',
            'photo' => $fakePhoto,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $profile = $this->approvedSeller->fresh()->sellerProfile;
        $this->assertEquals('Hunza Mountain Organics', $profile->farm_name);
        $this->assertEquals('Altit, Hunza', $profile->location);
        $this->assertNotNull($profile->photo_path);
        $this->assertEquals('03559998877', $this->approvedSeller->fresh()->phone);
    }
}
