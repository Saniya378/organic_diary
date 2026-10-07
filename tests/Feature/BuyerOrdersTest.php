<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SellerProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BuyerOrdersTest extends TestCase
{
    use DatabaseTransactions;

    protected User $buyer1;
    protected User $buyer2;
    protected User $seller;
    protected Product $product;
    protected ProductVariant $variant;
    protected OrderGroup $orderGroup;
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer1 = User::factory()->create([
            'name' => 'Buyer Ali',
            'email' => 'ali.test@organicdairy.test',
            'role' => 'buyer',
            'status' => 'active',
        ]);

        $this->buyer2 = User::factory()->create([
            'name' => 'Buyer Fatima',
            'email' => 'fatima.test@organicdairy.test',
            'role' => 'buyer',
            'status' => 'active',
        ]);

        $this->seller = User::factory()->create([
            'name' => 'Hunza Seller',
            'email' => 'hunza.seller@organicdairy.test',
            'role' => 'seller',
            'status' => 'active',
        ]);
        SellerProfile::create([
            'user_id' => $this->seller->id,
            'farm_name' => 'Hunza Valley Farm',
            'location' => 'Hunza',
            'status' => 'approved',
        ]);

        $category = Category::create([
            'name' => 'Raw Milk',
            'slug' => 'raw-milk-' . uniqid(),
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'seller_id' => $this->seller->id,
            'category_id' => $category->id,
            'name' => 'Fresh Cow Milk',
            'slug' => 'fresh-cow-milk-' . uniqid(),
            'description' => 'Pure milk',
            'price' => 220,
            'unit' => '1 Litre',
            'stock' => 50,
            'status' => 'approved',
            'is_organic' => true,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $this->product->id,
            'label' => '1 Litre Glass Bottle',
            'price' => 220,
            'stock' => 30,
        ]);

        $this->orderGroup = OrderGroup::create([
            'user_id' => $this->buyer1->id,
            'group_no' => 'GRP-2026-TEST1',
            'subtotal' => 440,
            'delivery_total' => 150,
            'discount' => 0,
            'grand_total' => 590,
            'payment_method' => 'cod',
            'shipping_full_name' => 'Buyer Ali',
            'shipping_phone' => '03551234567',
            'shipping_city' => 'Gilgit',
            'shipping_area' => 'Jutial',
            'shipping_address_line' => 'House 10, River View',
        ]);

        $this->order = Order::create([
            'order_group_id' => $this->orderGroup->id,
            'order_no' => 'OD-20999',
            'buyer_id' => $this->buyer1->id,
            'seller_id' => $this->seller->id,
            'subtotal' => 440,
            'delivery_fee' => 150,
            'commission' => 22,
            'total' => 590,
            'status' => 'placed',
            'payment_status' => 'unpaid',
            'rider_name' => 'Shamsuddin Rider',
            'rider_phone' => '03419998877',
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'variant_id' => $this->variant->id,
            'product_name' => 'Fresh Cow Milk (1 Litre Glass Bottle)',
            'unit_price' => 220,
            'quantity' => 2,
            'line_total' => 440,
        ]);

        OrderStatusHistory::create([
            'order_id' => $this->order->id,
            'status' => 'placed',
            'note' => 'Order placed successfully by buyer',
            'changed_by' => $this->buyer1->id,
            'created_at' => Carbon::now()->subHours(2),
        ]);
    }

    public function test_buyer_can_view_my_orders_page(): void
    {
        $response = $this->actingAs($this->buyer1)->get(route('orders.index'));
        $response->assertStatus(200);
        $response->assertSee('My Orders');
        $response->assertSee('#GRP-2026-TEST1');
        $response->assertSee('OD-20999');
        $response->assertSee('Hunza Valley Farm');
        $response->assertSee('Rs 590');
    }

    public function test_buyer_cannot_view_other_buyers_order(): void
    {
        $response = $this->actingAs($this->buyer2)->get(route('orders.show', $this->order->id));
        $response->assertStatus(403);
    }

    public function test_buyer_can_view_own_order_details(): void
    {
        $response = $this->actingAs($this->buyer1)->get(route('orders.show', $this->order->id));
        $response->assertStatus(200);
        $response->assertSee('#OD-20999');
        $response->assertSee('Fresh Cow Milk');
        $response->assertSee('Rs 440');
        $response->assertSee('River View');
    }

    public function test_track_order_lookup_renders_form(): void
    {
        $response = $this->get(route('track-order.form'));
        $response->assertStatus(200);
        $response->assertSee('Live Order Tracker');
        $response->assertSee('Enter Order #');
    }

    public function test_track_order_displays_timeline_and_rider_details(): void
    {
        $response = $this->actingAs($this->buyer1)->get(route('orders.track', 'OD-20999'));
        $response->assertStatus(200);
        $response->assertSee('#OD-20999');
        $response->assertSee('Order Placed');
        $response->assertSee('Shamsuddin Rider');
        $response->assertSee('tel:03419998877');
        $response->assertSee('Gilgit');
        $response->assertSee('Cancel Order & Restore Stock', false);
    }

    public function test_track_status_json_polling_endpoint(): void
    {
        $response = $this->actingAs($this->buyer1)->get(route('orders.track.status', $this->order->id));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'order_no' => 'OD-20999',
            'status' => 'placed',
            'rider_name' => 'Shamsuddin Rider',
            'rider_phone' => '03419998877',
            'can_cancel' => true,
        ]);
    }

    public function test_buyer_can_cancel_placed_order_restoring_stock_and_saving_history(): void
    {
        $initialVariantStock = $this->variant->fresh()->stock;
        $initialProductStock = $this->product->fresh()->stock;

        $response = $this->actingAs($this->buyer1)->post(route('orders.cancel', $this->order->id), [
            'cancel_reason' => 'Changed my mind',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Order marked as cancelled
        $this->assertEquals('cancelled', $this->order->fresh()->status);
        $this->assertEquals('Changed my mind', $this->order->fresh()->cancel_reason);

        // Stock restored (2 units)
        $this->assertEquals($initialVariantStock + 2, $this->variant->fresh()->stock);
        $this->assertEquals($initialProductStock + 2, $this->product->fresh()->stock);

        // History recorded
        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $this->order->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_buyer_cannot_cancel_order_in_packing_or_delivered_status(): void
    {
        $this->order->update(['status' => 'packing']);

        $response = $this->actingAs($this->buyer1)->post(route('orders.cancel', $this->order->id));
        $response->assertStatus(403);

        $this->order->update(['status' => 'delivered']);

        $response = $this->actingAs($this->buyer1)->post(route('orders.cancel', $this->order->id));
        $response->assertStatus(403);
    }
}
