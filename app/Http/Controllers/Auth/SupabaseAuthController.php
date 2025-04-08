<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupabaseAuthController extends Controller
{
    /**
     * Display the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Display the registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle a login request to Supabase.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            $response = Http::withHeaders([
                'apikey' => config('services.supabase.service_key'),
                'Content-Type' => 'application/json',
            ])->post(config('services.supabase.url') . '/auth/v1/token?grant_type=password', [
                'email' => $request->email,
                'password' => $request->password,
            ]);

            if ($response->failed()) {
                throw new \Exception('Supabase API error: ' . $response->body());
            }

            $data = $response->json();
            $accessToken = $data['access_token'] ?? null;
            $userId = $data['user']['id'] ?? null;
            $supabaseUser = $data['user'] ?? null;

            if (!$accessToken || !$userId || !$supabaseUser) {
                throw new \Exception('Invalid response from Supabase: Missing access token, user ID, or user data.');
            }

            // Store the access token and user data in the session
            Session::put('supabase_access_token', $accessToken);
            Session::put('supabase_user_id', $userId);
            Session::put('supabase_user', $supabaseUser);

            // Convert Supabase user to Laravel User and log in (optional, if using Laravel Auth)
            $user = \App\Models\User::fromSupabase((object) $supabaseUser);
            Auth::login($user);

            Log::info('User logged in via Supabase', ['user_id' => $userId]);

            $request->session()->regenerate();

            return redirect()->intended(route('marketplace'))->with('status', 'You are logged in!');
        } catch (\Exception $e) {
            Log::error('Failed to log in via Supabase: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Invalid credentials or an error occurred.']);
        }
    }

    /**
     * Handle a registration request to Supabase.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $response = Http::withHeaders([
                'apikey' => config('services.supabase.service_key'),
                'Content-Type' => 'application/json',
            ])->post(config('services.supabase.url') . '/auth/v1/signup', [
                'email' => $request->email,
                'password' => $request->password,
            ]);

            if ($response->failed()) {
                throw new \Exception('Supabase API error: ' . $response->body());
            }

            $data = $response->json();
            $accessToken = $data['access_token'] ?? null;
            $userId = $data['user']['id'] ?? null;
            $supabaseUser = $data['user'] ?? null;

            if (!$accessToken || !$userId || !$supabaseUser) {
                throw new \Exception('Invalid response from Supabase: Missing access token, user ID, or user data.');
            }

            // Store the access token and user data in the session
            Session::put('supabase_access_token', $accessToken);
            Session::put('supabase_user_id', $userId);
            Session::put('supabase_user', $supabaseUser);

            // Convert Supabase user to Laravel User and log in (optional, if using Laravel Auth)
            $user = \App\Models\User::fromSupabase((object) $supabaseUser);
            Auth::login($user);

            Log::info('User registered via Supabase', ['user_id' => $userId]);

            $request->session()->regenerate();

            return redirect()->route('dashboard')->with('status', 'Registration successful! You are logged in.');
        } catch (\Exception $e) {
            Log::error('Failed to register via Supabase: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Unable to register. Please try again.']);
        }
    }

    /**
     * Handle a logout request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        $token = Session::get('supabase_access_token');

        // Make a direct HTTP request to Supabase's logout endpoint
        if ($token) {
            try {
                $response = Http::withHeaders([
                    'apikey' => config('services.supabase.service_key'),
                    'Authorization' => 'Bearer ' . $token,
                ])->post(config('services.supabase.url') . '/auth/v1/logout');

                if ($response->successful()) {
                    Log::info('User signed out from Supabase.');
                } else {
                    Log::error('Supabase logout failed: ' . $response->body());
                }
            } catch (\Exception $e) {
                Log::error('Supabase logout failed: ' . $e->getMessage());
            }
        } else {
            Log::warning('No Supabase token found in session during logout.');
        }

        // Clear Supabase session data
        Session::forget(['supabase_access_token', 'supabase_user_id', 'supabase_user']);

        // Log out of Laravel Auth (if using)
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('Session invalidated and token regenerated.');

        return redirect('/')->with('status', 'You have been logged out.');
    }

    /**
     * Get the authenticated user's details.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        $token = Session::get('supabase_access_token');

        try {
            $response = Http::withHeaders([
                'apikey' => config('services.supabase.service_key'),
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->get(config('services.supabase.url') . '/auth/v1/user');

            if ($response->failed()) {
                throw new \Exception('Supabase API error: ' . $response->body());
            }

            return response()->json($response->json());
        } catch (\Exception $e) {
            Log::error('Failed to fetch user details via Supabase: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch user details.'], 500);
        }
    }
}