<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthAndProfileTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Welcome Back');
    }

    public function test_register_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('Create an Account');
        $response->assertSee('Seller accounts need admin approval before products go live');
    }

    public function test_buyer_registration_and_redirect(): void
    {
        $email = 'buyer_' . uniqid() . '@organicdairy.test';
        $response = $this->post('/register', [
            'name' => 'Test New Buyer',
            'email' => $email,
            'phone' => '03450001122',
            'role' => 'buyer',
            'password' => 'Pass12345',
            'password_confirmation' => 'Pass12345',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => $email,
            'role' => 'buyer',
        ]);
    }

    public function test_seller_registration_creates_pending_profile_and_redirects_to_seller_dashboard(): void
    {
        $email = 'seller_' . uniqid() . '@organicdairy.test';
        $response = $this->post('/register', [
            'name' => 'Ghizer New Farm',
            'email' => $email,
            'phone' => '03450003344',
            'role' => 'seller',
            'farm_name' => 'Ghizer Pastures Dairy',
            'location' => 'Gahkuch, Ghizer',
            'description' => 'Organic family dairy farm in Ghizer.',
            'password' => 'Pass12345',
            'password_confirmation' => 'Pass12345',
        ]);

        $response->assertRedirect(route('seller.dashboard'));
        $this->assertAuthenticated();
        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertEquals('seller', $user->role);
        $this->assertDatabaseHas('seller_profiles', [
            'user_id' => $user->id,
            'farm_name' => 'Ghizer Pastures Dairy',
            'status' => 'pending',
        ]);
    }

    public function test_admin_redirects_to_admin_dashboard_on_login(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@organicdairy.test',
            'password' => 'Admin@12345',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_seller_redirects_to_seller_dashboard_on_login(): void
    {
        $response = $this->post('/login', [
            'email' => 'hunza@organicdairy.test',
            'password' => 'Seller@12345',
        ]);

        $response->assertRedirect(route('seller.dashboard'));
    }

    public function test_suspended_user_cannot_login(): void
    {
        $suspendedUser = User::create([
            'name' => 'Suspended User',
            'email' => 'suspended_' . uniqid() . '@organicdairy.test',
            'phone' => '03459990000',
            'password' => Hash::make('Pass12345'),
            'role' => 'buyer',
            'status' => 'suspended',
        ]);

        $response = $this->post('/login', [
            'email' => $suspendedUser->email,
            'password' => 'Pass12345',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_buyer_cannot_access_admin_or_seller_routes(): void
    {
        $buyer = User::where('email', 'buyer@organicdairy.test')->first();

        $responseAdmin = $this->actingAs($buyer)->get('/admin/dashboard');
        $responseAdmin->assertStatus(403);

        $responseSeller = $this->actingAs($buyer)->get('/seller/dashboard');
        $responseSeller->assertStatus(403);
    }

    public function test_address_book_crud_for_buyer(): void
    {
        $buyer = User::where('email', 'buyer@organicdairy.test')->first();

        // 1. View addresses
        $response = $this->actingAs($buyer)->get('/addresses');
        $response->assertStatus(200);

        // 2. Create address
        $createResponse = $this->actingAs($buyer)->post('/addresses', [
            'full_name' => 'Ali Alternate Delivery',
            'phone' => '03451112233',
            'city' => 'Hunza',
            'area' => 'Karimabad',
            'address_line' => 'House 9, Upper Bazaar',
            'is_default' => 0,
        ]);
        $createResponse->assertRedirect(route('addresses.index'));

        $newAddress = Address::where('full_name', 'Ali Alternate Delivery')->first();
        $this->assertNotNull($newAddress);

        // 3. Make Default
        $defaultResponse = $this->actingAs($buyer)->patch("/addresses/{$newAddress->id}/default");
        $defaultResponse->assertRedirect(route('addresses.index'));
        $this->assertTrue($newAddress->fresh()->is_default);

        // 4. Update address
        $updateResponse = $this->actingAs($buyer)->put("/addresses/{$newAddress->id}", [
            'full_name' => 'Ali Updated Delivery',
            'phone' => '03451112233',
            'city' => 'Hunza',
            'area' => 'Karimabad',
            'address_line' => 'House 10, Upper Bazaar',
            'is_default' => 1,
        ]);
        $updateResponse->assertRedirect(route('addresses.index'));
        $this->assertEquals('Ali Updated Delivery', $newAddress->fresh()->full_name);

        // 5. Delete address
        $deleteResponse = $this->actingAs($buyer)->delete("/addresses/{$newAddress->id}");
        $deleteResponse->assertRedirect(route('addresses.index'));
        $this->assertNull(Address::find($newAddress->id));
    }
}
