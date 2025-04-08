<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use PHPSupabase\Service;

class AuthenticatedSessionController extends Controller
{
    protected $supabase;

    /**
     * Initialize the Supabase service.
     */
    public function __construct()
    {
        $this->supabase = new Service(
            config('services.supabase.anon_key'),
            config('services.supabase.url')
        );
    }

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        \Log::info('AuthenticatedSessionController@store called.');

        $request->authenticate();

        $supabaseUser = session('supabase_user');
        \Log::info('Supabase user:', (array) $supabaseUser);

        if (!isset($supabaseUser->id)) {
            \Log::info("AuthenticatedSessionController: Supabase user ID is missing.");
            return redirect()->route('login');
        }

        $user = \App\Models\User::fromSupabase($supabaseUser);

        session(['supabase_user_id' => $supabaseUser->id]);

        Auth::login($user);
        \Log::info('User logged in:', ['id' => $user->id]);

        $request->session()->regenerate();

        \Log::info('Session regenerated.');

        return redirect()->intended(route('marketplace'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $token = session('supabase_token');

        // Make a direct HTTP request to Supabase's logout endpoint
        if ($token) {
            try {
                $response = Http::withHeaders([
                    'apikey' => config('services.supabase.anon_key'),
                    'Authorization' => 'Bearer ' . $token,
                ])->post(config('services.supabase.url') . '/auth/v1/logout');

                if ($response->successful()) {
                    \Log::info('User signed out from Supabase.');
                } else {
                    \Log::error('Supabase logout failed: ' . $response->body());
                }
            } catch (\Exception $e) {
                \Log::error('Supabase logout failed: ' . $e->getMessage());
            }
        } else {
            \Log::warning('No Supabase token found in session during logout.');
        }

        $request->session()->forget(['supabase_token', 'supabase_user', 'supabase_user_id']);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        \Log::info('Session invalidated and token regenerated.');

        return redirect('/');
    }
}