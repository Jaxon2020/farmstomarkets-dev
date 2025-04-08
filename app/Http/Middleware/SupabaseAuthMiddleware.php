<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SupabaseAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            \Log::error("SupabaseAuthMiddleware: Token not provided.");
            return redirect()->route('login'); // Redirect to login if token is missing
        }

        $response = Http::withHeaders([
            'apikey' => env('SUPABASE_KEY'),
            'Authorization' => 'Bearer ' . $token,
        ])->get(env('SUPABASE_URL') . '/auth/v1/user');

        if ($response->failed()) {
            \Log::error("SupabaseAuthMiddleware: Failed to fetch user data.");
            return redirect()->route('login'); // Redirect to login if user data cannot be fetched
        }

        $supabaseUser = $response->json();

        if (!isset($supabaseUser['id'])) {
            \Log::error("SupabaseAuthMiddleware: Supabase user ID is missing.");
            return redirect()->route('login'); // Redirect to login if user ID is missing
        }

        session(['supabase_user_id' => $supabaseUser['id']]);
        \Log::info("SupabaseAuthMiddleware: Supabase UUID stored in session: {$supabaseUser['id']}");

        $request->merge(['user' => \App\Models\User::fromSupabase($supabaseUser)]);
        return $next($request);
    }
}
