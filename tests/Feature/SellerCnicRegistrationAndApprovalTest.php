<?php

namespace Tests\Feature;

use App\Models\EmailOtp;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SellerCnicRegistrationAndApprovalTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_seller_registration_requires_cnic_fields(): void
    {
        $response = $this->post('/register', [
            'name' => 'Kareem Dairy Farm',
            'email' => 'kareem_' . uniqid() . '@organicdairy.test',
            'phone' => '03451234567',
            'role' => 'seller',
            'farm_name' => 'Hunza Pure Milk',
            'location' => 'Karimabad, Hunza',
            'description' => 'Organic grass-fed cow milk in Hunza.',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            // Missing cnic_no, cnic_front, cnic_back
        ]);

        $response->assertSessionHasErrors(['cnic_no', 'cnic_front', 'cnic_back']);
    }

    public function test_seller_registration_with_cnic_stores_files_and_redirects_to_otp_verification(): void
    {
        $email = 'seller_cnic_' . uniqid() . '@organicdairy.test';

        $frontFile = UploadedFile::fake()->create('cnic_front.jpg', 200, 'image/jpeg');
        $backFile = UploadedFile::fake()->create('cnic_back.jpg', 200, 'image/jpeg');

        $response = $this->post('/register', [
            'name' => 'Muhammad Ali',
            'email' => $email,
            'phone' => '03459876543',
            'role' => 'seller',
            'farm_name' => 'Karakoram Organic Farm',
            'location' => 'Sadpara Road, Skardu',
            'description' => 'Pure butter and raw yak milk.',
            'cnic_no' => '71101-1234567-1',
            'cnic_front' => $frontFile,
            'cnic_back' => $backFile,
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertRedirect(route('register.verify.form'));
        $response->assertSessionHas('pending_registration');

        $pending = session('pending_registration');
        $this->assertEquals('71101-1234567-1', $pending['cnic_no']);
        $this->assertNotNull($pending['cnic_front']);
        $this->assertNotNull($pending['cnic_back']);

        // Verify stored files in public disk
        Storage::disk('public')->assertExists($pending['cnic_front']);
        Storage::disk('public')->assertExists($pending['cnic_back']);

        // Verify OTP and complete registration
        $otp = EmailOtp::where('email', $email)->where('type', 'registration')->latest()->first();
        $this->assertNotNull($otp);

        $verifyResponse = $this->post('/register/verify-otp', [
            'otp' => $otp->otp,
        ]);

        $verifyResponse->assertRedirect(route('seller.dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertEquals('seller', $user->role);

        $profile = $user->sellerProfile;
        $this->assertNotNull($profile);
        $this->assertEquals('pending', $profile->status);
        $this->assertEquals('71101-1234567-1', $profile->cnic_no);
        $this->assertEquals($pending['cnic_front'], $profile->cnic_front);
        $this->assertEquals($pending['cnic_back'], $profile->cnic_back);
    }

    public function test_admin_can_view_seller_cnic_and_approve(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin_cnic_test@organicdairy.test'],
            [
                'name' => 'Admin Tester',
                'phone' => '03001234567',
                'password' => Hash::make('AdminPass123'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        $sellerUser = User::create([
            'name' => 'Javed Khan',
            'email' => 'javed_' . uniqid() . '@organicdairy.test',
            'phone' => '03451122334',
            'password' => Hash::make('Password123'),
            'role' => 'seller',
            'status' => 'active',
        ]);

        $sellerProfile = SellerProfile::create([
            'user_id' => $sellerUser->id,
            'farm_name' => 'Nagar Valley Dairy',
            'location' => 'Chalt, Nagar',
            'description' => 'Fresh goat and cow milk.',
            'cnic_no' => '71102-7654321-3',
            'cnic_front' => 'seller_cnic/test_front.jpg',
            'cnic_back' => 'seller_cnic/test_back.jpg',
            'status' => 'pending',
        ]);

        // 1. Admin accesses sellers page and sees CNIC details
        $indexResponse = $this->actingAs($admin)->get(route('admin.sellers.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('71102-7654321-3');
        $indexResponse->assertSee('Farmer CNIC Verification');
        $indexResponse->assertSee('Inspect CNIC');

        // 2. Admin approves seller
        $approveResponse = $this->actingAs($admin)->post(route('admin.sellers.approve', $sellerProfile->id));
        $approveResponse->assertRedirect();

        $sellerProfile->refresh();
        $this->assertEquals('approved', $sellerProfile->status);
    }
}
