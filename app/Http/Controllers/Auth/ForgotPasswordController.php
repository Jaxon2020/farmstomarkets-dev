<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    /**
     * Display the forgot password view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle the forgot password request using Supabase Auth API.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $request->input('email');

        try {
            // Log the email being processed
            Log::info('Processing password reset request', ['email' => $email]);

            // Construct the redirect URL
            $redirectTo = 'http://localhost:8000/reset-password';
            Log::info('Password reset redirectTo URL', ['redirectTo' => $redirectTo]);

            // Log the Supabase URL and anon key being used
            $supabaseUrl = config('services.supabase.url');
            $anonKey = config('services.supabase.anon_key');
            Log::info('Supabase API configuration', [
                'supabase_url' => $supabaseUrl,
                'anon_key' => $anonKey,
            ]);

            // Construct the full payload with redirect_to (snake_case)
            $payload = [
                'email' => $email,
                'options' => [
                    'redirect_to' => $redirectTo, // Changed to snake_case
                ],
            ];
            Log::info('Supabase /recover request payload', ['payload' => $payload]);

            // Make the request to Supabase
            $response = Http::withHeaders([
                'apikey' => $anonKey,
                'Content-Type' => 'application/json',
            ])->post($supabaseUrl . '/auth/v1/recover', $payload);

            // Log the response status and body
            Log::info('Supabase /recover response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers(),
            ]);

            if ($response->failed()) {
                throw new \Exception('Supabase API error: ' . $response->body());
            }

            Log::info('Password reset email sent via Supabase', ['email' => $email]);

            return redirect()->route('password.request')
                ->with('status', 'We have emailed your password reset link!');
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email via Supabase', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['email' => 'Unable to send password reset email. Please try again later.']);
        }
    }
}