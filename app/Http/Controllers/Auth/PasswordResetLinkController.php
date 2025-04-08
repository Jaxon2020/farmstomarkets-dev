<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $request->input('email');

        try {
            // Make a direct HTTP request to Supabase Auth API to send the password reset email
            $response = Http::withHeaders([
                'apikey' => config('services.supabase.anon_key'),
                'Content-Type' => 'application/json',
            ])->post(config('services.supabase.url') . '/auth/v1/recover', [
                'email' => $email,
                'redirect_to' => url('/reset-password'), // URL where the user will be redirected after clicking the reset link
            ]);

            if ($response->failed()) {
                throw new \Exception('Supabase API error: ' . $response->body());
            }

            Log::info('Password reset email sent via Supabase', ['email' => $email]);

            return back()->with('status', 'We have emailed your password reset link!');
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email via Supabase: ' . $e->getMessage(), ['email' => $email]);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'We were unable to send a password reset link. Please try again.']);
        }
    }
}