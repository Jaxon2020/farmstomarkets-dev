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
    public function create(Request $request): View
    {
        // Remove the server-side check for access_token and email
        // Let the client-side JavaScript handle the fragment parsing
        return view('auth.reset-password');
    }

    public function store(Request $request): RedirectResponse
    {
        Log::info('Password reset form submitted', $request->all());
    
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    
        Log::info('Validation passed', $request->all());
    
        $token = $request->input('token');
        $email = $request->input('email');
        $password = $request->input('password');
    
        try {
            Log::info('Attempting to verify token with Supabase', ['token' => $token, 'email' => $email]);
    
            // Step 1: Verify the reset token by setting the session with Supabase
            $response = Http::withHeaders([
                'apikey' => config('services.supabase.anon_key'),
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->get(config('services.supabase.url') . '/auth/v1/user');
    
            Log::info('Supabase /auth/v1/user response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
    
            if ($response->failed()) {
                throw new \Exception('Invalid or expired reset token: ' . $response->body());
            }
    
            $userData = $response->json();
            if ($userData['email'] !== $email) {
                throw new \Exception('Email does not match the reset token.');
            }
    
            Log::info('Token verified, updating password', ['email' => $email]);
    
            // Step 2: Update the password using the authenticated session
            $updateResponse = Http::withHeaders([
                'apikey' => config('services.supabase.anon_key'),
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->put(config('services.supabase.url') . '/auth/v1/user', [
                'password' => $password,
            ]);
    
            Log::info('Supabase /auth/v1/user update response', [
                'status' => $updateResponse->status(),
                'body' => $updateResponse->body(),
            ]);
    
            if ($updateResponse->failed()) {
                throw new \Exception('Failed to update password: ' . $updateResponse->body());
            }
    
            Log::info('Password reset successful', ['email' => $email]);
            return redirect()->route('login')->with('status', 'Password reset successful! Please log in.');
        } catch (\Exception $e) {
            Log::error('Password reset failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors(['password' => 'Unable to reset password. The link may be invalid or expired.']);
        }
    }
}