<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    /**
     * Update the user's password using Supabase.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();
        $email = $user->email;
        $currentPassword = $validated['current_password'];
        $newPassword = $validated['password'];

        try {
            // Step 1: Verify the current password with Supabase
            $authResponse = Http::withHeaders([
                'apikey' => config('services.supabase.anon_key'),
                'Content-Type' => 'application/json',
            ])->post(config('services.supabase.url') . '/auth/v1/token?grant_type=password', [
                'email' => $email,
                'password' => $currentPassword,
            ]);

            if ($authResponse->failed()) {
                Log::warning('Current password verification failed', [
                    'email' => $email,
                    'status' => $authResponse->status(),
                    'body' => $authResponse->body(),
                ]);
                
                throw ValidationException::withMessages([
                    'current_password' => ['The provided password is incorrect.'],
                ]);
            }

            // Get the access token from the response
            $authData = $authResponse->json();
            $accessToken = $authData['access_token'] ?? null;

            if (!$accessToken) {
                throw new \Exception('Access token not found in authentication response.');
            }

            // Step 2: Update the password using the authenticated session
            $updateResponse = Http::withHeaders([
                'apikey' => config('services.supabase.anon_key'),
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->put(config('services.supabase.url') . '/auth/v1/user', [
                'password' => $newPassword,
            ]);

            if ($updateResponse->failed()) {
                throw new \Exception('Failed to update password: ' . $updateResponse->body());
            }

            // Update the local user password if you're storing it
            $user->update([
                'password' => Hash::make($newPassword),
            ]);

            Log::info('Password updated successfully', ['email' => $email]);
            return back()->with('status', 'password-updated');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Password update failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors(['updatePassword' => 'Unable to update password. ' . $e->getMessage()]);
        }
    }
}
