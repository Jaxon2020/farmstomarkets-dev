<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PHPSupabase\Service;

class LoginRequest extends FormRequest
{
    protected $supabase;

    /**
     * Initialize the Supabase service.
     */
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct();

        // Initialize the PHPSupabase Service with your Supabase credentials
        $this->supabase = new Service(
            config('services.supabase.anon_key'),
            config('services.supabase.url')
        );
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
{
    \Log::info('LoginRequest authenticate method called.');

    // Create an instance of the Auth class from PHPSupabase
    $auth = $this->supabase->createAuth();

    try {
        // Attempt to sign in with email and password
        $auth->signInWithEmailAndPassword(
            $this->input('email'),
            $this->input('password')
        );

        // Get the response data from Supabase
        $userData = $auth->data();

        // Store the access token, refresh token, expiration, and user data in the session
        session([
            'supabase_token' => $userData->access_token,
            'supabase_refresh_token' => $userData->refresh_token,
            'supabase_token_expires_at' => time() + $userData->expires_in,
            'supabase_user' => $userData->user,
        ]);

        \Log::info('Supabase login successful. Session data stored.', [
            'user_id' => $userData->user->id,
            'expires_at' => time() + $userData->expires_in,
        ]);

    } catch (\Exception $e) {
        \Log::error('Supabase login failed: ' . $e->getMessage());
        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }
}

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}