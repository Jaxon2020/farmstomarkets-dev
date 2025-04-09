<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;
class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        Log::info('Rendering profile.edit view with user data:', ['user' => $request->user()]);
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Validate the request
        $request->validateWithBag('userDeletion', [
            'password' => ['required'],
        ]);

        $user = $request->user();
        $email = $user->email;
        $password = $request->input('password');
        
        // Get Supabase user ID from session instead of user model
        $supabaseUserId = session('supabase_user_id');
        $supabaseToken = session('supabase_access_token');

        try {
            // Step 1: Verify the password with Supabase
            $authResponse = Http::withHeaders([
                'apikey' => config('services.supabase.anon_key'),
                'Content-Type' => 'application/json',
            ])->post(config('services.supabase.url') . '/auth/v1/token?grant_type=password', [
                'email' => $email,
                'password' => $password,
            ]);

            if ($authResponse->failed()) {
                Log::warning('Password verification failed for account deletion', [
                    'email' => $email,
                    'status' => $authResponse->status(),
                    'body' => $authResponse->body(),
                ]);
                return back()->withErrors(['userDeletion.password' => 'Incorrect password.']);
            }

            // Get the Supabase user ID if not available in session
            if (!$supabaseUserId) {
                $authData = $authResponse->json();
                $supabaseUserId = $authData['user']['id'] ?? null;
                
                if (!$supabaseUserId) {
                    throw new \Exception('Supabase user ID not found in authentication response.');
                }
            }

            // Step 2: Delete the user from Supabase using the service_role key
            $deleteUrl = config('services.supabase.url') . '/auth/v1/admin/users/' . $supabaseUserId;
            $deleteResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.supabase.service_key'),
                'apikey' => config('services.supabase.anon_key'),
                'Content-Type' => 'application/json',
            ])->delete($deleteUrl);

            if ($deleteResponse->failed()) {
                throw new \Exception('Failed to delete user from Supabase: ' . $deleteResponse->body());
            }

            // Step 3: Clear Supabase session data (same as in SupabaseAuthController)
            session()->forget(['supabase_access_token', 'supabase_user_id', 'supabase_user']);

            // Step 4: Log the user out and delete from Laravel
            Auth::logout();
            $user->delete();

            // Step 5: Invalidate session
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return Redirect::to('/')->with('status', 'Your account has been deleted.');
        } catch (\Exception $e) {
            Log::error('Account deletion failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors(['userDeletion' => 'Unable to delete your account. Please try again.']);
        }
    }
}
