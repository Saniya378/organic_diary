<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Deal;
use App\Models\DealDelivery;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DealsTest extends TestCase
{
    use DatabaseTransactions;

    protected User $buyer;
    protected User $seller;
    protected User $unrelatedBuyer;
    protected Product $product;
    protected Address $address;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('delivery_fee', '150');
        Setting::set('commission_percent', '5');

        $this->seller = User::factory()->create([
            'role' => 'seller',
            'status' => 'active',
        ]);
        SellerProfile::create([
            'user_id' => $this->seller->id,
            'farm_name' => 'Hunza Pure Pasture',
            'location' => 'Karimabad, Hunza',
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
            'name' => 'Organic Fresh Cow Milk 1L',
            'slug' => 'organic-fresh-cow-milk-1l-' . uniqid(),
            'description' => 'Pure unpasteurized Himalayan pasture milk',
            'price' => 200,
            'unit' => '1 Litre',
            'stock' => 50,
            'status' => 'approved',
            'is_organic' => true,
            'deals_enabled' => true,
        ]);

        $this->buyer = User::factory()->create([
            'role' => 'buyer',
            'status' => 'active',
        ]);

        $this->unrelatedBuyer = User::factory()->create([
            'role' => 'buyer',
            'status' => 'active',
        ]);

        $this->address = Address::create([
            'user_id' => $this->buyer->id,
            'full_name' => $this->buyer->name,
            'phone' => '03551234567',
            'city' => 'Gilgit',
            'area' => 'Jutial',
            'address_line' => 'House 12, River View Road',
            'is_default' => true,
        ]);
    }

    public function test_public_deals_showcase_page_loads_with_plans(): void
    {
        $response = $this->get(route('deals.index'));
        $response->assertStatus(200);
        $response->assertSee('Daily Farm Deals');
        $response->assertSee('3 Days Trial Deal');
        $response->assertSee('1 Week Routine Deal');
        $response->assertSee('1 Month Family Supply Deal');
    }

    public function test_guest_is_redirected_when_accessing_deal_configuration(): void
    {
        $response = $this->get(route('deals.create', $this->product->id));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_buyer_can_view_deal_configuration_form(): void
    {
        $response = $this->actingAs($this->buyer)->get(route('deals.create', $this->product->id));
        $response->assertStatus(200);
        $response->assertSee('Select Your Deal Duration');
        $response->assertSee('3 Days Deal');
        $response->assertSee('1 Week Deal');
        $response->assertSee('1 Month Deal');
    }

    public function test_buyer_can_subscribe_to_1_week_deal(): void
    {
        $startDate = Carbon::tomorrow()->toDateString();

        $response = $this->actingAs($this->buyer)->post(route('deals.store'), [
            'product_id' => $this->product->id,
            'duration_type' => '1_week',
            'quantity_per_day' => 2,
            'delivery_slot' => 'morning',
            'start_date' => $startDate,
            'payment_method' => 'cod',
            'address_id' => $this->address->id,
            'special_instructions' => 'Drop at doorstep container',
        ]);

        $deal = Deal::where('buyer_id', $this->buyer->id)
            ->where('duration_type', '1_week')
            ->first();

        $this->assertNotNull($deal);
        $this->assertEquals(7, $deal->total_days);
        $this->assertEquals(0, $deal->delivered_days);
        $this->assertEquals('active', $deal->status);
        $this->assertEquals(7, $deal->deliveries()->count());

        // Check pricing: 200 * 2 qty * 7 days = 2800 - 10% (280) = 2520
        $this->assertEquals(2520, $deal->total_amount);

        $response->assertRedirect(route('buyer.deals.show', $deal->id));
    }

    public function test_buyer_can_subscribe_to_3_days_and_1_month_deals(): void
    {
        // 3 Days Deal (5% off)
        $this->actingAs($this->buyer)->post(route('deals.store'), [
            'product_id' => $this->product->id,
            'duration_type' => '3_days',
            'quantity_per_day' => 1,
            'delivery_slot' => 'morning',
            'start_date' => Carbon::tomorrow()->toDateString(),
            'payment_method' => 'cod',
            'address_id' => $this->address->id,
        ]);

        $deal3Days = Deal::where('buyer_id', $this->buyer->id)->where('duration_type', '3_days')->first();
        $this->assertNotNull($deal3Days);
        $this->assertEquals(3, $deal3Days->total_days);
        $this->assertEquals(3, $deal3Days->deliveries()->count());
        // 200 * 1 * 3 = 600 - 5% (30) = 570
        $this->assertEquals(570, $deal3Days->total_amount);

        // 1 Month Deal (15% off)
        $this->actingAs($this->buyer)->post(route('deals.store'), [
            'product_id' => $this->product->id,
            'duration_type' => '1_month',
            'quantity_per_day' => 1,
            'delivery_slot' => 'morning',
            'start_date' => Carbon::tomorrow()->toDateString(),
            'payment_method' => 'card',
            'address_id' => $this->address->id,
        ]);

        $deal1Month = Deal::where('buyer_id', $this->buyer->id)->where('duration_type', '1_month')->first();
        $this->assertNotNull($deal1Month);
        $this->assertEquals(30, $deal1Month->total_days);
        $this->assertEquals(30, $deal1Month->deliveries()->count());
        // 200 * 1 * 30 = 6000 - 15% (900) = 5100
        $this->assertEquals(5100, $deal1Month->total_amount);
    }

    public function test_buyer_can_view_my_deals_dashboard(): void
    {
        $this->test_buyer_can_subscribe_to_1_week_deal();

        $response = $this->actingAs($this->buyer)->get(route('buyer.deals.index'));
        $response->assertStatus(200);
        $response->assertSee('My Deals');
        $response->assertSee('1 Week Deal');
    }

    public function test_buyer_cannot_view_another_buyers_deal(): void
    {
        $deal = Deal::create([
            'deal_no' => 'DEAL-TEST-001',
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'product_id' => $this->product->id,
            'title' => 'Test Deal',
            'duration_type' => '1_week',
            'total_days' => 7,
            'delivered_days' => 0,
            'quantity_per_day' => 1,
            'unit_price' => 200,
            'discount_percent' => 10,
            'total_amount' => 1260,
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(6),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->unrelatedBuyer)->get(route('buyer.deals.show', $deal->id));
        $response->assertStatus(403);
    }

    public function test_buyer_can_pause_and_resume_deal(): void
    {
        $deal = Deal::create([
            'deal_no' => 'DEAL-TEST-002',
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'product_id' => $this->product->id,
            'title' => 'Test Deal',
            'duration_type' => '1_week',
            'total_days' => 7,
            'delivered_days' => 0,
            'quantity_per_day' => 1,
            'unit_price' => 200,
            'discount_percent' => 10,
            'total_amount' => 1260,
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(6),
            'status' => 'active',
        ]);

        // Pause
        $this->actingAs($this->buyer)->post(route('buyer.deals.pause', $deal->id));
        $this->assertEquals('paused', $deal->fresh()->status);

        // Resume
        $this->actingAs($this->buyer)->post(route('buyer.deals.resume', $deal->id));
        $this->assertEquals('active', $deal->fresh()->status);
    }

    public function test_seller_can_view_farm_deals_and_today_deliveries(): void
    {
        $response = $this->actingAs($this->seller)->get(route('seller.deals.index'));
        $response->assertStatus(200);
        $response->assertSee('Farm Deals & Subscriptions');
        $response->assertSee('Active Subscriptions');
        $response->assertSee('Today\'s Doorstep Delivery Run', false);
    }

    public function test_seller_can_mark_daily_delivery_as_delivered(): void
    {
        $deal = Deal::create([
            'deal_no' => 'DEAL-TEST-003',
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'product_id' => $this->product->id,
            'title' => 'Test Deal',
            'duration_type' => '3_days',
            'total_days' => 3,
            'delivered_days' => 0,
            'quantity_per_day' => 1,
            'unit_price' => 200,
            'discount_percent' => 5,
            'total_amount' => 570,
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(2),
            'status' => 'active',
        ]);

        $delivery1 = DealDelivery::create([
            'deal_id' => $deal->id,
            'day_number' => 1,
            'scheduled_date' => Carbon::today(),
            'status' => 'scheduled',
        ]);

        $delivery2 = DealDelivery::create([
            'deal_id' => $deal->id,
            'day_number' => 2,
            'scheduled_date' => Carbon::tomorrow(),
            'status' => 'scheduled',
        ]);

        $delivery3 = DealDelivery::create([
            'deal_id' => $deal->id,
            'day_number' => 3,
            'scheduled_date' => Carbon::today()->addDays(2),
            'status' => 'scheduled',
        ]);

        // Seller marks day 1 as delivered
        $response = $this->actingAs($this->seller)->post(route('seller.deals.deliveries.mark', $delivery1->id), [
            'status' => 'delivered',
        ]);

        $this->assertEquals('delivered', $delivery1->fresh()->status);
        $this->assertEquals(1, $deal->fresh()->delivered_days);
        $this->assertEquals('active', $deal->fresh()->status);

        // Mark day 2 and day 3 -> should auto complete
        $this->actingAs($this->seller)->post(route('seller.deals.deliveries.mark', $delivery2->id), ['status' => 'delivered']);
        $this->actingAs($this->seller)->post(route('seller.deals.deliveries.mark', $delivery3->id), ['status' => 'delivered']);

        $this->assertEquals(3, $deal->fresh()->delivered_days);
        $this->assertEquals('completed', $deal->fresh()->status);
    }
}
