<?php

namespace Tests\Feature;

use App\Mail\ResetPasswordMail;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\SellerProfile;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReviewsAndPolishTest extends TestCase
{
    use DatabaseTransactions;

    protected User $seller;
    protected User $buyer;
    protected Category $category;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('delivery_fee', '150');
        Setting::set('commission_percent', '5');

        $this->seller = User::firstOrCreate(
            ['email' => 'polish_seller@organicdairy.test'],
            [
                'name' => 'Polish Seller Farm',
                'phone' => '03554440001',
                'password' => Hash::make('Seller@12345'),
                'role' => 'seller',
                'status' => 'active',
            ]
        );

        SellerProfile::firstOrCreate(
            ['user_id' => $this->seller->id],
            [
                'farm_name' => 'Polish Organic Farm',
                'location' => 'Hunza',
                'status' => 'approved',
            ]
        );

        $this->buyer = User::firstOrCreate(
            ['email' => 'polish_buyer@organicdairy.test'],
            [
                'name' => 'Polish Buyer',
                'phone' => '03554440002',
                'password' => Hash::make('Buyer@12345'),
                'role' => 'buyer',
                'status' => 'active',
            ]
        );

        $this->category = Category::firstOrCreate(
            ['slug' => 'butter-polish-test'],
            ['name' => 'Butter Polish Test', 'is_active' => true]
        );

        $this->product = Product::firstOrCreate(
            ['slug' => 'organic-churned-butter-test'],
            [
                'seller_id' => $this->seller->id,
                'category_id' => $this->category->id,
                'name' => 'Organic Churned Butter Test',
                'description' => 'Pure churned butter from grass-fed cows.',
                'price' => 700,
                'unit' => '250g',
                'stock' => 25,
                'is_organic' => true,
                'status' => 'approved',
            ]
        );
    }

    public function test_buyer_cannot_review_product_without_delivered_order(): void
    {
        $response = $this->actingAs($this->buyer)->post(route('products.reviews.store', $this->product), [
            'rating' => 5,
            'comment' => 'Great product!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('reviews', [
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_buyer_can_review_product_after_delivery(): void
    {
        // Create delivered order for buyer containing this product
        $group = OrderGroup::create([
            'user_id' => $this->buyer->id,
            'group_no' => 'GRP-REV-001',
            'subtotal' => 700,
            'delivery_total' => 150,
            'discount' => 0,
            'grand_total' => 850,
            'payment_method' => 'cod',
            'shipping_full_name' => 'Buyer',
            'shipping_phone' => '03554440002',
            'shipping_city' => 'Gilgit',
            'shipping_area' => 'Jutial',
            'shipping_address_line' => 'House 1',
        ]);

        $order = Order::create([
            'order_group_id' => $group->id,
            'order_no' => 'OD-REV-101',
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'subtotal' => 700,
            'delivery_fee' => 150,
            'commission' => 35,
            'total' => 850,
            'status' => 'delivered',
            'payment_status' => 'paid',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'unit_price' => 700,
            'quantity' => 1,
            'line_total' => 700,
        ]);

        // Submit review
        $response = $this->actingAs($this->buyer)->post(route('products.reviews.store', $this->product), [
            'rating' => 5,
            'comment' => 'Exceptional aroma and rich mountain taste!',
            'order_id' => $order->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Exceptional aroma and rich mountain taste!',
        ]);

        // Duplicate review for same order should be rejected
        $dupResponse = $this->actingAs($this->buyer)->post(route('products.reviews.store', $this->product), [
            'rating' => 4,
            'comment' => 'Trying to submit again',
            'order_id' => $order->id,
        ]);
        $dupResponse->assertSessionHas('error');
    }

    public function test_forgot_password_and_reset_flow(): void
    {
        Mail::fake();

        // 1. Request forgot password link
        $response = $this->post(route('password.email'), [
            'email' => $this->buyer->email,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        Mail::assertSent(ResetPasswordMail::class, function ($mail) {
            return $mail->hasTo($this->buyer->email);
        });

        // Verify token in DB
        $tokenRow = DB::table('password_reset_tokens')->where('email', $this->buyer->email)->first();
        $this->assertNotNull($tokenRow);

        // 2. Perform reset
        // Since token in DB is hashed, create a known raw token
        $rawToken = 'sample_secure_token_1234567890';
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $this->buyer->email],
            ['token' => Hash::make($rawToken), 'created_at' => now()]
        );

        $resetResponse = $this->post(route('password.update'), [
            'token' => $rawToken,
            'email' => $this->buyer->email,
            'password' => 'NewPassword@2026',
            'password_confirmation' => 'NewPassword@2026',
        ]);

        $resetResponse->assertRedirect(route('login'));
        $resetResponse->assertSessionHas('success');

        // Check user can login with new password
        $this->assertTrue(Hash::check('NewPassword@2026', $this->buyer->fresh()->password));
    }

    public function test_custom_404_error_page_renders_with_brand(): void
    {
        $response = $this->get('/non-existent-mountain-route-404');
        $response->assertStatus(404);
        $response->assertSee('Pasture Not Found');
        $response->assertSee('Browse Fresh Shop');
    }
}
