<?php

namespace Tests\Feature;

use App\Mail\SellerApprovedMail;
use App\Mail\SellerRejectedMail;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SellerProfile;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $seller;
    protected User $buyer;
    protected SellerProfile $sellerProfile;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('delivery_fee', '150');
        Setting::set('commission_percent', '5');
        Setting::set('low_stock_threshold', '10');
        Setting::set('allowed_cities', json_encode(['Gilgit', 'Skardu', 'Hunza']));

        $this->admin = User::firstOrCreate(
            ['email' => 'admin_test@organicdairy.test'],
            [
                'name' => 'Super Admin',
                'phone' => '03551119999',
                'password' => Hash::make('Admin@12345'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        $this->seller = User::firstOrCreate(
            ['email' => 'owner_test@organicdairy.test'],
            [
                'name' => 'Farm Owner',
                'phone' => '03552229999',
                'password' => Hash::make('Seller@12345'),
                'role' => 'seller',
                'status' => 'active',
            ]
        );

        $this->sellerProfile = SellerProfile::firstOrCreate(
            ['user_id' => $this->seller->id],
            [
                'farm_name' => 'Alpine Meadows Farm',
                'location' => 'Hunza Valley',
                'description' => 'Organic dairy from mountain pastures.',
                'status' => 'pending',
            ]
        );

        $this->buyer = User::firstOrCreate(
            ['email' => 'buyer_test@organicdairy.test'],
            [
                'name' => 'Test Buyer',
                'phone' => '03553339999',
                'password' => Hash::make('Buyer@12345'),
                'role' => 'buyer',
                'status' => 'active',
            ]
        );
    }

    public function test_non_admin_cannot_access_admin_panel(): void
    {
        // Guest
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));

        // Buyer
        $response = $this->actingAs($this->buyer)->get(route('admin.dashboard'));
        $response->assertStatus(403);

        // Seller
        $response = $this->actingAs($this->seller)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_admin_dashboard_renders_with_kpis_and_pending_queues(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Marketplace Administration Center');
        $response->assertSee('Alpine Meadows Farm');
        $response->assertSee('Total Sellers');
        $response->assertSee('Total Buyers');
    }

    public function test_admin_can_approve_seller_and_triggers_email(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->admin)->post(route('admin.sellers.approve', $this->sellerProfile));

        $response->assertRedirect();
        $this->assertDatabaseHas('seller_profiles', [
            'id' => $this->sellerProfile->id,
            'status' => 'approved',
        ]);

        Mail::assertSent(SellerApprovedMail::class, function ($mail) {
            return $mail->hasTo($this->seller->email);
        });
    }

    public function test_admin_can_reject_seller_with_required_reason(): void
    {
        Mail::fake();

        // Validation failure without reason
        $response = $this->actingAs($this->admin)->post(route('admin.sellers.reject', $this->sellerProfile), []);
        $response->assertSessionHasErrors('rejection_reason');

        // Valid rejection
        $response = $this->actingAs($this->admin)->post(route('admin.sellers.reject', $this->sellerProfile), [
            'rejection_reason' => 'Organic certification documents are incomplete.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('seller_profiles', [
            'id' => $this->sellerProfile->id,
            'status' => 'rejected',
            'rejection_reason' => 'Organic certification documents are incomplete.',
        ]);

        Mail::assertSent(SellerRejectedMail::class, function ($mail) {
            return $mail->hasTo($this->seller->email) && $mail->reason === 'Organic certification documents are incomplete.';
        });
    }

    public function test_admin_can_suspend_and_reactivate_seller(): void
    {
        // Suspend
        $response = $this->actingAs($this->admin)->post(route('admin.sellers.suspend', $this->sellerProfile));
        $response->assertRedirect();

        $this->assertEquals('suspended', $this->sellerProfile->fresh()->status);
        $this->assertEquals('suspended', $this->seller->fresh()->status);

        // Reactivate
        $response = $this->actingAs($this->admin)->post(route('admin.sellers.reactivate', $this->sellerProfile));
        $response->assertRedirect();

        $this->assertEquals('approved', $this->sellerProfile->fresh()->status);
        $this->assertEquals('active', $this->seller->fresh()->status);
    }

    public function test_admin_can_approve_and_reject_products(): void
    {
        $category = Category::firstOrCreate(['slug' => 'milk-admin-test'], ['name' => 'Milk Admin Test', 'is_active' => true]);

        $product = Product::create([
            'seller_id' => $this->seller->id,
            'category_id' => $category->id,
            'name' => 'Raw Goat Milk Admin Test',
            'slug' => 'raw-goat-milk-admin-test',
            'description' => 'Fresh raw milk',
            'price' => 300,
            'unit' => '1 Litre',
            'stock' => 20,
            'is_organic' => true,
            'status' => 'pending',
        ]);

        // Approve
        $response = $this->actingAs($this->admin)->post(route('admin.products.approve', $product));
        $response->assertRedirect();
        $this->assertEquals('approved', $product->fresh()->status);

        // Reject with reason
        $response = $this->actingAs($this->admin)->post(route('admin.products.reject', $product), [
            'rejection_reason' => 'Missing batch number and expiration date.',
        ]);
        $response->assertRedirect();
        $this->assertEquals('rejected', $product->fresh()->status);
        $this->assertEquals('Missing batch number and expiration date.', $product->fresh()->rejection_reason);
    }

    public function test_admin_can_view_orders_and_cancel_with_stock_restoration(): void
    {
        $category = Category::firstOrCreate(['slug' => 'butter-admin-test'], ['name' => 'Butter Admin Test', 'is_active' => true]);

        $product = Product::create([
            'seller_id' => $this->seller->id,
            'category_id' => $category->id,
            'name' => 'Desi Butter Admin Test',
            'slug' => 'desi-butter-admin-test',
            'description' => 'Pure butter',
            'price' => 600,
            'unit' => '250g',
            'stock' => 10,
            'is_organic' => true,
            'status' => 'approved',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'label' => '500g Pack',
            'price' => 1200,
            'stock' => 5,
        ]);

        $group = OrderGroup::create([
            'user_id' => $this->buyer->id,
            'group_no' => 'GRP-2026-TEST',
            'subtotal' => 2400,
            'delivery_total' => 150,
            'discount' => 0,
            'grand_total' => 2550,
            'payment_method' => 'cod',
            'shipping_full_name' => 'Buyer Name',
            'shipping_phone' => '03553333333',
            'shipping_city' => 'Gilgit',
            'shipping_area' => 'Jutial',
            'shipping_address_line' => 'House 1',
        ]);

        $order = Order::create([
            'order_group_id' => $group->id,
            'order_no' => 'OD-TEST-999',
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'subtotal' => 2400,
            'delivery_fee' => 150,
            'commission' => 120,
            'total' => 2550,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'product_name' => 'Desi Butter (500g Pack)',
            'unit_price' => 1200,
            'quantity' => 2,
            'line_total' => 2400,
        ]);

        // Verify show page
        $response = $this->actingAs($this->admin)->get(route('admin.orders.show', $order));
        $response->assertStatus(200);
        $response->assertSee('OD-TEST-999');
        $response->assertSee('Desi Butter');

        // Cancel order
        $response = $this->actingAs($this->admin)->post(route('admin.orders.cancel', $order), [
            'cancel_reason' => 'Customer requested cancellation due to travel.',
        ]);

        $response->assertRedirect();
        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals('Customer requested cancellation due to travel.', $order->fresh()->cancel_reason);

        // Verify stock restoration
        $this->assertEquals(12, $product->fresh()->stock); // 10 + 2
        $this->assertEquals(7, $variant->fresh()->stock);   // 5 + 2

        // Verify audit timeline row created
        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $order->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_admin_users_management_and_suspension(): void
    {
        // List users
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $response->assertStatus(200);
        $response->assertSee($this->buyer->name);

        // Suspend buyer
        $response = $this->actingAs($this->admin)->post(route('admin.users.suspend', $this->buyer));
        $response->assertRedirect();
        $this->assertEquals('suspended', $this->buyer->fresh()->status);

        // Reactivate buyer
        $response = $this->actingAs($this->admin)->post(route('admin.users.reactivate', $this->buyer));
        $response->assertRedirect();
        $this->assertEquals('active', $this->buyer->fresh()->status);

        // Admin cannot suspend self
        $response = $this->actingAs($this->admin)->post(route('admin.users.suspend', $this->admin));
        $response->assertRedirect();
        $this->assertEquals('active', $this->admin->fresh()->status);
    }

    public function test_admin_categories_crud(): void
    {
        // 1. Create
        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'Artisanal Cheese',
            'slug' => 'artisanal-cheese',
            'is_active' => '1',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['name' => 'Artisanal Cheese', 'slug' => 'artisanal-cheese']);

        $category = Category::where('slug', 'artisanal-cheese')->first();

        // 2. Update
        $response = $this->actingAs($this->admin)->put(route('admin.categories.update', $category), [
            'name' => 'Gourmet Artisanal Cheese',
            'slug' => 'gourmet-artisanal-cheese',
            'is_active' => '1',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['name' => 'Gourmet Artisanal Cheese', 'slug' => 'gourmet-artisanal-cheese']);

        // 3. Delete
        $response = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category));
        $response->assertRedirect();
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_admin_settings_update(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.settings.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->admin)->put(route('admin.settings.update'), [
            'delivery_fee' => 200,
            'commission_percent' => 7.5,
            'low_stock_threshold' => 15,
            'allowed_cities' => 'Gilgit, Skardu, Hunza, Ghizer, Nagar',
        ]);

        $response->assertRedirect();
        $this->assertEquals('200', Setting::get('delivery_fee'));
        $this->assertEquals('7.5', Setting::get('commission_percent'));
        $this->assertEquals('15', Setting::get('low_stock_threshold'));
        $this->assertStringContainsString('Nagar', Setting::get('allowed_cities'));
    }

    public function test_admin_payments_and_reports_with_csv_export(): void
    {
        $group = OrderGroup::create([
            'user_id' => $this->buyer->id,
            'group_no' => 'GRP-2026-PAY1',
            'subtotal' => 1000,
            'delivery_total' => 150,
            'discount' => 0,
            'grand_total' => 1150,
            'payment_method' => 'card',
            'shipping_full_name' => 'Buyer',
            'shipping_phone' => '03553333333',
            'shipping_city' => 'Gilgit',
            'shipping_area' => 'Jutial',
            'shipping_address_line' => 'House 1',
        ]);

        Payment::create([
            'order_group_id' => $group->id,
            'method' => 'card',
            'gateway_ref' => 'TXN-998877',
            'last4' => '4242',
            'amount' => 1150,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Payments list
        $response = $this->actingAs($this->admin)->get(route('admin.payments.index'));
        $response->assertStatus(200);
        $response->assertSee('TXN-998877');

        // Reports page
        $response = $this->actingAs($this->admin)->get(route('admin.reports.index'));
        $response->assertStatus(200);
        $response->assertSee('Marketplace Reports & Financial Analytics');

        // CSV export
        $response = $this->actingAs($this->admin)->get(route('admin.reports.export'));
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));
    }
}
