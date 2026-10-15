<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Notification;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ShelfLifeAndBatchExpiryTest extends TestCase
{
    use DatabaseTransactions;

    protected User $seller;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = User::factory()->create([
            'role' => 'seller',
            'status' => 'active',
            'email' => 'farmer@organicdairy.test',
        ]);

        SellerProfile::create([
            'user_id' => $this->seller->id,
            'farm_name' => 'Hunza Green Pastures',
            'location' => 'Karimabad, Hunza',
            'status' => 'approved',
        ]);

        $this->category = Category::create([
            'name' => 'Organic Milk',
            'slug' => 'organic-milk',
            'is_active' => true,
        ]);
    }

    public function test_product_auto_calculates_expiry_date_from_shelf_life(): void
    {
        $producedOn = Carbon::today();
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'name' => 'Raw Mountain Cow Milk',
            'slug' => 'raw-mountain-cow-milk',
            'price' => 220,
            'unit' => '1 Litre',
            'stock' => 30,
            'batch_no' => 'HNZ-MILK-2026',
            'produced_on' => $producedOn,
            'shelf_life_days' => 3, // 3 days shelf life
            'status' => 'approved',
        ]);

        $this->assertNotNull($product->expires_at);
        $this->assertEquals($producedOn->copy()->addDays(3)->format('Y-m-d'), $product->expires_at->format('Y-m-d'));
        $this->assertFalse($product->is_expired);
        $this->assertEquals(3, $product->days_until_expiry);
    }

    public function test_product_freshness_status_badges(): void
    {
        // Fresh product (expires in 10 days)
        $freshProduct = Product::create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'name' => 'Aged Alpine Gouda',
            'slug' => 'aged-alpine-gouda',
            'price' => 1200,
            'unit' => '300g',
            'stock' => 10,
            'produced_on' => Carbon::today(),
            'shelf_life_days' => 10,
            'status' => 'approved',
        ]);

        $this->assertEquals('fresh', $freshProduct->freshness_status['status']);
        $this->assertFalse($freshProduct->is_expired);
        $this->assertFalse($freshProduct->is_expiring_soon);

        // Expiring soon product (expires in 1 day)
        $expiringProduct = Product::create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'name' => 'Organic Fresh Paneer',
            'slug' => 'organic-fresh-paneer',
            'price' => 500,
            'unit' => '400g',
            'stock' => 15,
            'produced_on' => Carbon::today()->subDays(4),
            'shelf_life_days' => 5, // expires tomorrow
            'status' => 'approved',
        ]);

        $this->assertEquals('expiring_soon', $expiringProduct->freshness_status['status']);
        $this->assertTrue($expiringProduct->is_expiring_soon);
        $this->assertFalse($expiringProduct->is_expired);

        // Expired product (expired 2 days ago)
        $expiredProduct = Product::create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'name' => 'Pasture Yogurt',
            'slug' => 'pasture-yogurt',
            'price' => 200,
            'unit' => '500g',
            'stock' => 5,
            'produced_on' => Carbon::today()->subDays(10),
            'shelf_life_days' => 7,
            'status' => 'approved',
        ]);

        $this->assertEquals('expired', $expiredProduct->freshness_status['status']);
        $this->assertTrue($expiredProduct->is_expired);
    }

    public function test_dairy_check_expiries_command_dispatches_alerts_to_seller(): void
    {
        // Create an expiring product
        $product = Product::create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'name' => 'Fresh Yak Buttermilk',
            'slug' => 'fresh-yak-buttermilk',
            'price' => 180,
            'unit' => '1L',
            'stock' => 20,
            'batch_no' => 'YAK-BUTTERMILK-01',
            'produced_on' => Carbon::today()->subDays(2),
            'shelf_life_days' => 3, // expires tomorrow (1 day left)
            'status' => 'approved',
        ]);

        $exitCode = Artisan::call('dairy:check-expiries');
        $this->assertEquals(0, $exitCode);

        // Verify notification sent to seller
        $notification = Notification::where('user_id', $this->seller->id)->latest()->first();
        $this->assertNotNull($notification);
        $this->assertStringContainsString('Expiry Alert', $notification->title);
        $this->assertStringContainsString('Fresh Yak Buttermilk', $notification->body);
    }
}
