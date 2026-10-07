<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Cart;
use App\Models\SellerProfile;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class AuthController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $guestSessionId = Session::getId();
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        // Merge guest session cart into user DB cart if present
        $guestCart = Cart::where('session_id', $guestSessionId)->whereNull('user_id')->first();
        if ($guestCart) {
            $userCart = Cart::firstOrCreate(['user_id' => $user->id]);
            $this->cartService->mergeCart($guestCart, $userCart);
        }

        return $this->redirectByRole($user);
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('seller_photos', 'public');
        }

        $pendingData = array_merge($validated, [
            'photo_path' => $photoPath,
            'guest_session_id' => Session::getId(),
        ]);

        // Save pending registration payload in session
        Session::put('pending_registration', $pendingData);

        // Generate 6-digit OTP code
        $otp = \App\Models\EmailOtp::generateFor($validated['email'], 'registration', 10);

        // Send OTP email
        try {
            \Illuminate\Support\Facades\Mail::to($validated['email'])->send(
                new \App\Mail\RegistrationOtpMail($otp, $validated['name'], 10)
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send registration OTP email: ' . $e->getMessage());
        }

        return redirect()->route('register.verify.form')
            ->with('status', 'We have sent a 6-digit verification code to ' . $validated['email'] . '. Please enter it below to complete your registration.');
    }

    /**
     * Show the Registration OTP Verification screen.
     */
    public function showVerifyOtp(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        $pending = Session::get('pending_registration');
        if (!$pending || empty($pending['email'])) {
            return redirect()->route('register')->with('warning', 'Please fill in the registration form first.');
        }

        return view('auth.verify-otp', [
            'email' => $pending['email'],
            'name' => $pending['name'],
            'role' => $pending['role'] ?? 'buyer',
        ]);
    }

    /**
     * Verify the 6-digit OTP and create user account.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'digits:6'],
        ], [
            'otp.required' => 'Please enter the 6-digit verification code sent to your email.',
            'otp.digits' => 'The verification code must be exactly 6 digits.',
        ]);

        $pending = Session::get('pending_registration');
        if (!$pending || empty($pending['email'])) {
            return redirect()->route('register')->with('error', 'Registration session expired. Please register again.');
        }

        $isValid = \App\Models\EmailOtp::verifyOtp($pending['email'], $request->input('otp'), 'registration');

        if (!$isValid) {
            return back()->withErrors([
                'otp' => 'Invalid or expired verification code. Please check your email or click Resend Code.',
            ])->withInput();
        }

        // OTP is verified! Create user account
        $user = User::create([
            'name' => $pending['name'],
            'email' => $pending['email'],
            'phone' => $pending['phone'],
            'password' => Hash::make($pending['password']),
            'role' => $pending['role'],
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        if ($pending['role'] === 'seller') {
            SellerProfile::create([
                'user_id' => $user->id,
                'farm_name' => $pending['farm_name'],
                'location' => $pending['location'],
                'description' => $pending['description'] ?? null,
                'photo_path' => $pending['photo_path'] ?? null,
                'status' => 'pending', // Pending approval by admin
            ]);
        }

        $guestSessionId = $pending['guest_session_id'] ?? Session::getId();
        Session::forget('pending_registration');

        Auth::login($user);
        $request->session()->regenerate();

        // Merge guest session cart into user DB cart
        $guestCart = Cart::where('session_id', $guestSessionId)->whereNull('user_id')->first();
        if ($guestCart) {
            $userCart = Cart::firstOrCreate(['user_id' => $user->id]);
            $this->cartService->mergeCart($guestCart, $userCart);
        }

        if ($user->role === 'seller') {
            return redirect()->route('seller.dashboard')->with('status', 'Welcome! Your email has been verified. Your seller account is pending admin approval before your products can be made live.');
        }

        return redirect()->intended('/')->with('success', 'Email verified successfully! Welcome to Organic Dairy Gilgit-Baltistan.');
    }

    /**
     * Resend registration verification OTP code.
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        $pending = Session::get('pending_registration');
        if (!$pending || empty($pending['email'])) {
            return redirect()->route('register')->with('error', 'Registration session expired. Please register again.');
        }

        $otp = \App\Models\EmailOtp::generateFor($pending['email'], 'registration', 10);

        try {
            \Illuminate\Support\Facades\Mail::to($pending['email'])->send(
                new \App\Mail\RegistrationOtpMail($otp, $pending['name'], 10)
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to resend registration OTP email: ' . $e->getMessage());
        }

        return back()->with('status', 'A fresh 6-digit verification code has been sent to ' . $pending['email'] . '. Please check your inbox.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'You have been logged out successfully.');
    }

    protected function redirectByRole(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($user->isSeller()) {
            return redirect()->intended(route('seller.dashboard'));
        }

        return redirect()->intended('/');
    }
}
