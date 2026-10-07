<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Authenticate the request's credentials with 5 attempts max before a 5-minute account lock.
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $user = User::where('email', $this->email)->first();

        if ($user && $user->status === 'suspended') {
            RateLimiter::hit($this->throttleKey(), 300);
            throw ValidationException::withMessages([
                'email' => __('Your account has been suspended. Please contact administrator support.'),
            ]);
        }

        if (!Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey(), 300);

            $attemptsRemaining = RateLimiter::retriesLeft($this->throttleKey(), 5);
            $message = __('These credentials do not match our records.');
            if ($attemptsRemaining > 0) {
                $message .= ' ' . __('You have :attempts attempt(s) remaining before your account is locked for 5 minutes.', ['attempts' => $attemptsRemaining]);
            }

            throw ValidationException::withMessages([
                'email' => $message,
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());
        $minutes = ceil($seconds / 60);

        throw ValidationException::withMessages([
            'email' => __('Too many login attempts. Your account is temporarily locked for 5 minutes. Please try again in :minutes minute(s) (:seconds seconds).', [
                'seconds' => $seconds,
                'minutes' => $minutes,
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}

