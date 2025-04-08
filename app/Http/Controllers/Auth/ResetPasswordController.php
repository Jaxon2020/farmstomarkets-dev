<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $token = $request->query('access_token');
        $email = $request->query('email');

        if (!$token || !$email) {
            return redirect()->route('password.request')->withErrors(['email' => 'Invalid or expired reset link.']);
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $token = $request->input('token');
        $email = $request->input('email');
        $password = $request->input('password');

        try {
            // Step 1: Verify the reset token by setting the session with Supabase
            $response = Http::withHeaders([
                'apikey' => config('services.supabase.anon_key'),
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->get(config('services.supabase.url') . '/auth/v1/user');

            if ($response->failed()) {
                throw new \Exception('Invalid or expired reset token: ' . $response->body());
            }

            $userData = $response->json();
            if ($userData['email'] !== $email) {
                throw new \Exception('Email does not match the reset token.');
            }

            // Step 2: Update the password using the authenticated session
            $updateResponse = Http::withHeaders([
                'apikey' => config('services.supabase.anon_key'),
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->put(config('services.supabase.url') . '/auth/v1/user', [
                'password' => $password,
            ]);

            if ($updateResponse->failed()) {
                throw new \Exception('Failed to update password: ' . $updateResponse->body());
            }

            Log::info('Password reset successful', ['email' => $email]);
            return redirect()->route('login')->with('status', 'Password reset successful! Please log in.');
        } catch (\Exception $e) {
            Log::error('Password reset failed: ' . $e->getMessage());
            return back()->withErrors(['password' => 'Unable to reset password. The link may be invalid or expired.']);
        }
    }
}