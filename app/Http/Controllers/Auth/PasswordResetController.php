<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /**
     * Display the forgot password request view.
     */
    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a 6-digit OTP to the user's email for password recovery.
     */
    public function sendResetOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'We could not find an account associated with that email address.',
        ]);

        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        // Generate 6-digit OTP
        $otp = \App\Models\EmailOtp::generateFor($email, 'forgot_password', 10);

        try {
            Mail::to($user->email)->send(new \App\Mail\PasswordResetOtpMail($user, $otp, 10));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send password reset OTP: ' . $e->getMessage());
        }

        return redirect()->route('password.reset.otp', ['email' => $email])
            ->with('status', 'We have sent a 6-digit password reset OTP code to ' . $email . '. Please check your inbox.');
    }

    /**
     * Display the password reset form with OTP verification.
     */
    public function showOtpResetForm(Request $request): View
    {
        return view('auth.reset-password', [
            'email' => $request->query('email'),
        ]);
    }

    /**
     * Reset the user's password using the verified 6-digit OTP code.
     */
    public function resetPasswordWithOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'otp' => ['required', 'string', 'digits:6'],
            'password' => [
                'required',
                'string',
                'confirmed',
                PasswordRule::min(8)->letters()->numbers(),
            ],
        ], [
            'otp.required' => 'Please enter the 6-digit verification code.',
            'otp.digits' => 'The verification code must be exactly 6 digits.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.letters' => 'Password must contain at least one letter.',
            'password.numbers' => 'Password must contain at least one number.',
        ]);

        $email = $request->input('email');
        $otp = $request->input('otp');

        $isValid = \App\Models\EmailOtp::verifyOtp($email, $otp, 'forgot_password');

        if (!$isValid) {
            return back()->withErrors([
                'otp' => 'Invalid or expired OTP verification code. Please check your email or request a new code.',
            ])->withInput();
        }

        // Update User Password
        $user = User::where('email', $email)->first();
        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return redirect()->route('login')->with('success', 'Your password has been successfully reset! You can now log in with your new password.');
    }

    /**
     * Resend password reset OTP code.
     */
    public function resendResetOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'We could not find an account associated with that email address.',
        ]);

        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        $otp = \App\Models\EmailOtp::generateFor($email, 'forgot_password', 10);

        try {
            Mail::to($user->email)->send(new \App\Mail\PasswordResetOtpMail($user, $otp, 10));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to resend password reset OTP: ' . $e->getMessage());
        }

        return back()->with('status', 'A fresh 6-digit reset code has been sent to ' . $email . '. Please check your inbox.');
    }

    /**
     * Legacy token reset fallback.
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        return $this->sendResetOtp($request);
    }

    public function showResetForm(Request $request, ?string $token = null): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        if ($request->has('otp')) {
            return $this->resetPasswordWithOtp($request);
        }

        return $this->resetPasswordWithOtp($request);
    }
}

