<?php

namespace Tests\Feature;

use App\Mail\OrderConfirmedBuyerMail;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SellerOrderConfirmationNotificationAndMailTest extends TestCase
{
    use DatabaseTransactions;

    public function test_when_seller_confirms_order_buyer_receives_in_app_notification_and_confirmation_email(): void
    {
        Mail::fake();

        // 1. Create Buyer
        $buyer = User::create([
            'name' => 'Sara Khan',
            'email' => 'sara_' . uniqid() . '@organicdairy.test',
            'phone' => '03451122334',
            'password' => Hash::make('Pass12345'),
            'role' => 'buyer',
            'status' => 'active',
        ]);

        // 2. Create Seller
        $seller = User::create([
            'name' => 'Ali Raza',
            'email' => 'ali_' . uniqid() . '@organicdairy.test',
            'phone' => '03459988776',
            'password' => Hash::make('Pass12345'),
            'role' => 'seller',
            'status' => 'active',
        ]);

        $sellerProfile = SellerProfile::create([
            'user_id' => $seller->id,
            'farm_name' => 'Hunza Pure Mountain Dairy',
            'location' => 'Karimabad, Hunza',
            'description' => 'Organic grass-fed butter & cheese.',
            'status' => 'approved',
        ]);

        // 3. Create Category & Product
        $category = \App\Models\Category::firstOrCreate(
            ['slug' => 'butter-test'],
            ['name' => 'Butter', 'description' => 'Butter products']
        );

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Raw Mountain Butter',
            'slug' => 'raw-mountain-butter-' . uniqid(),
            'description' => 'Fresh churning butter from Hunza cows.',
            'price' => 850,
            'stock' => 20,
            'unit' => '500g',
            'status' => 'approved',
            'is_active' => true,
        ]);

        // 4. Create Order Group & Order in 'placed' status
        $orderGroup = OrderGroup::create([
            'user_id' => $buyer->id,
            'group_no' => 'OD-GRP-' . strtoupper(uniqid()),
            'subtotal' => 1700,
            'delivery_total' => 150,
            'discount' => 0,
            'grand_total' => 1850,
            'payment_method' => 'cod',
            'shipping_full_name' => 'Sara Khan',
            'shipping_phone' => '03451122334',
            'shipping_city' => 'Gilgit',
            'shipping_area' => 'Jutial',
            'shipping_address_line' => 'House 14, Street 2',
        ]);

        $order = Order::create([
            'order_group_id' => $orderGroup->id,
            'order_no' => 'OD-' . strtoupper(uniqid()),
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'subtotal' => 1700,
            'delivery_fee' => 150,
            'commission' => 85,
            'total' => 1850,
            'status' => 'placed',
            'payment_status' => 'unpaid',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price' => 850,
            'line_total' => 1700,
            'commission_rate' => 5,
            'commission_amount' => 85,
        ]);

        // 5. Seller confirms the order
        $response = $this->actingAs($seller)->post(route('seller.orders.advance-status', $order->id), [
            'target_status' => 'confirmed',
            'note' => 'Fresh butter batch verified and queued for cooling box packing.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify order status updated to confirmed
        $order->refresh();
        $this->assertEquals('confirmed', $order->status);

        // 6. Verify In-App Notification was created for Buyer
        $notification = Notification::where('user_id', $buyer->id)
            ->where('type', 'order_confirmed')
            ->latest()
            ->first();

        $this->assertNotNull($notification, 'In-app notification should be sent to the buyer');
        $this->assertStringContainsString($order->order_no, $notification->title);
        $this->assertStringContainsString('Hunza Pure Mountain Dairy', $notification->title);
        $this->assertEquals('bi-check-circle-fill', $notification->icon);

        // 7. Verify Confirmation Email was sent to Buyer
        Mail::assertSent(OrderConfirmedBuyerMail::class, function ($mail) use ($buyer, $order) {
            return $mail->hasTo($buyer->email) &&
                   $mail->order->id === $order->id &&
                   str_contains($mail->envelope()->subject, $order->order_no);
        });
    }

    public function test_order_confirmed_email_renders_correctly(): void
    {
        $buyer = User::create([
            'name' => 'Hamza Ali',
            'email' => 'hamza_' . uniqid() . '@organicdairy.test',
            'phone' => '03001234567',
            'password' => Hash::make('Pass12345'),
            'role' => 'buyer',
            'status' => 'active',
        ]);

        $seller = User::create([
            'name' => 'Karim Farmer',
            'email' => 'karim_' . uniqid() . '@organicdairy.test',
            'phone' => '03007654321',
            'password' => Hash::make('Pass12345'),
            'role' => 'seller',
            'status' => 'active',
        ]);

        $sellerProfile = SellerProfile::create([
            'user_id' => $seller->id,
            'farm_name' => 'Skardu Valley Organic Dairy',
            'location' => 'Sadpara Road, Skardu',
            'status' => 'approved',
        ]);

        $orderGroup = OrderGroup::create([
            'user_id' => $buyer->id,
            'group_no' => 'OD-GRP-' . strtoupper(uniqid()),
            'subtotal' => 1200,
            'delivery_total' => 150,
            'discount' => 0,
            'grand_total' => 1350,
            'payment_method' => 'cod',
            'shipping_full_name' => 'Hamza Ali',
            'shipping_phone' => '03001234567',
            'shipping_city' => 'Skardu',
            'shipping_area' => 'Hussainabad',
            'shipping_address_line' => 'Near Central Hospital',
        ]);

        $order = Order::create([
            'order_group_id' => $orderGroup->id,
            'order_no' => 'OD-' . strtoupper(uniqid()),
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'subtotal' => 1200,
            'delivery_fee' => 150,
            'commission' => 60,
            'total' => 1350,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
        ]);

        $order->load(['buyer', 'seller.sellerProfile', 'orderGroup.payment', 'items']);

        $mailable = new OrderConfirmedBuyerMail($order, 'All organic milk packs are chilled.');
        $rendered = $mailable->render();

        $this->assertStringContainsString('Order Confirmed by Farm', $rendered);
        $this->assertStringContainsString($order->order_no, $rendered);
        $this->assertStringContainsString('Skardu Valley Organic Dairy', $rendered);
        $this->assertStringContainsString('Hamza Ali', $rendered);
        $this->assertStringContainsString('All organic milk packs are chilled', $rendered);
    }

    public function test_seller_can_confirm_order_via_dedicated_confirm_route(): void
    {
        Mail::fake();

        $buyer = User::create([
            'name' => 'Fatima Noor',
            'email' => 'fatima_' . uniqid() . '@organicdairy.test',
            'phone' => '03129876543',
            'password' => Hash::make('Pass12345'),
            'role' => 'buyer',
            'status' => 'active',
        ]);

        $seller = User::create([
            'name' => 'Tariq Hussain',
            'email' => 'tariq_' . uniqid() . '@organicdairy.test',
            'phone' => '03123456789',
            'password' => Hash::make('Pass12345'),
            'role' => 'seller',
            'status' => 'active',
        ]);

        $sellerProfile = SellerProfile::create([
            'user_id' => $seller->id,
            'farm_name' => 'Gilgit Alpine Dairy Farm',
            'location' => 'Danyore, Gilgit',
            'status' => 'approved',
        ]);

        $orderGroup = OrderGroup::create([
            'user_id' => $buyer->id,
            'group_no' => 'OD-GRP-' . strtoupper(uniqid()),
            'subtotal' => 900,
            'delivery_total' => 150,
            'discount' => 0,
            'grand_total' => 1050,
            'payment_method' => 'cod',
            'shipping_full_name' => 'Fatima Noor',
            'shipping_phone' => '03129876543',
            'shipping_city' => 'Gilgit',
            'shipping_area' => 'Danyore',
            'shipping_address_line' => 'Main Bazaar',
        ]);

        $order = Order::create([
            'order_group_id' => $orderGroup->id,
            'order_no' => 'OD-' . strtoupper(uniqid()),
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'subtotal' => 900,
            'delivery_fee' => 150,
            'commission' => 45,
            'total' => 1050,
            'status' => 'placed',
            'payment_status' => 'unpaid',
        ]);

        // Hit the dedicated confirm route
        $response = $this->actingAs($seller)->post(route('seller.orders.confirm', $order->id), [
            'note' => 'Fresh butter verified and ready for delivery.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('confirmed', $order->fresh()->status);

        // Buyer Notification verified
        $notification = Notification::where('user_id', $buyer->id)
            ->where('type', 'order_confirmed')
            ->first();
        $this->assertNotNull($notification);
        $this->assertStringContainsString($order->order_no, $notification->title);

        // Buyer Email verified
        Mail::assertSent(OrderConfirmedBuyerMail::class, function ($mail) use ($buyer, $order) {
            return $mail->hasTo($buyer->email) && $mail->order->id === $order->id;
        });
    }

    public function test_seller_can_confirm_pending_payment_order_and_inventory_decrements(): void
    {
        Mail::fake();

        $buyer = User::create([
            'name' => 'Bilal Ahmed',
            'email' => 'bilal_' . uniqid() . '@organicdairy.test',
            'phone' => '03331122334',
            'password' => Hash::make('Pass12345'),
            'role' => 'buyer',
            'status' => 'active',
        ]);

        $seller = User::create([
            'name' => 'Sultan Khan',
            'email' => 'sultan_' . uniqid() . '@organicdairy.test',
            'phone' => '03339988776',
            'password' => Hash::make('Pass12345'),
            'role' => 'seller',
            'status' => 'active',
        ]);

        $sellerProfile = SellerProfile::create([
            'user_id' => $seller->id,
            'farm_name' => 'Nagar Fresh Milk Point',
            'location' => 'Nagar Valley',
            'status' => 'approved',
        ]);

        $category = \App\Models\Category::firstOrCreate(
            ['slug' => 'fresh-milk'],
            ['name' => 'Fresh Milk', 'description' => 'Organic Fresh Milk']
        );

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Pure Mountain Milk 1L',
            'slug' => 'pure-mountain-milk-' . uniqid(),
            'description' => 'Fresh morning milk',
            'price' => 300,
            'stock' => 10,
            'unit' => '1L',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $orderGroup = OrderGroup::create([
            'user_id' => $buyer->id,
            'group_no' => 'OD-GRP-' . strtoupper(uniqid()),
            'subtotal' => 600,
            'delivery_total' => 150,
            'discount' => 0,
            'grand_total' => 750,
            'payment_method' => 'bank_transfer',
            'shipping_full_name' => 'Bilal Ahmed',
            'shipping_phone' => '03331122334',
            'shipping_city' => 'Gilgit',
            'shipping_area' => 'Jutial',
            'shipping_address_line' => 'Sector 2',
        ]);

        $order = Order::create([
            'order_group_id' => $orderGroup->id,
            'order_no' => 'OD-' . strtoupper(uniqid()),
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'subtotal' => 600,
            'delivery_fee' => 150,
            'commission' => 30,
            'total' => 750,
            'status' => 'pending_payment',
            'payment_status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price' => 300,
            'line_total' => 600,
            'commission_rate' => 5,
            'commission_amount' => 30,
        ]);

        // Seller confirms directly
        $response = $this->actingAs($seller)->post(route('seller.orders.confirm', $order->id), [
            'note' => 'Bank payment slip verified offline and milk prepared.',
        ]);

        $response->assertRedirect();
        $this->assertEquals('confirmed', $order->fresh()->status);

        // Product stock decremented from 10 to 8
        $this->assertEquals(8, $product->fresh()->stock);

        // Buyer Notification & Email verified
        $notification = Notification::where('user_id', $buyer->id)
            ->where('type', 'order_confirmed')
            ->first();
        $this->assertNotNull($notification);

        Mail::assertSent(OrderConfirmedBuyerMail::class);
    }
}
