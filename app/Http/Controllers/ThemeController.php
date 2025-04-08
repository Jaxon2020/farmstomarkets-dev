<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ThemeController extends Controller
{
    protected $supabaseService;

    public function __construct(SupabaseService $supabaseService)
    {
        $this->supabaseService = $supabaseService;
    }

    public function switchTheme(Request $request)
    {
        // Validate the theme input
        $availableThemes = array_keys(config('themes.available', []));
        $request->validate([
            'theme' => ['required', 'string', 'in:' . implode(',', $availableThemes)],
        ]);

        $theme = $request->input('theme');

        // Check if the user is authenticated
        $userId = session('supabase_user_id'); // Supabase UUID from session
        $accessToken = session('supabase_token'); // Supabase access token from session
        $isAuthenticated = !empty($userId) && preg_match('/^[0-9a-fA-F-]{36}$/', $userId) && !empty($accessToken);

        // Always set the theme in the session
        Session::put('theme', $theme);

        if (!$isAuthenticated) {
            // Guest/not logged in: Only set the session theme, don't push to Supabase
            Session::flash('message', 'Theme updated for this session. Log in to save your preference.');
            return redirect()->back();
        }

        // Logged-in user: Push the theme to Supabase
        $result = $this->updateUserTheme($userId, $theme, $accessToken);

        if ($result) {
            Session::flash('message', 'Theme updated successfully and saved.');
        } else {
            Session::flash('message', 'Theme updated for this session, but failed to save to your profile. Please try again.');
        }

        return redirect()->back();
    }

    protected function updateUserTheme(string $userId, string $theme, string $accessToken): bool
    {
        if (empty($userId) || empty($theme)) {
            \Log::error("Update Theme Failed: User ID and theme are required.", [
                'user_id' => $userId,
                'theme' => $theme,
            ]);
            return false;
        }

        if (empty($accessToken)) {
            \Log::error("Update Theme Failed: Authentication required for user ID $userId");
            return false;
        }

        // Prepare the data to update
        $data = ['theme' => $theme];

        // Define the conditions for the update (match user ID)
        $conditions = ['id' => 'eq.' . $userId];

        // Use SupabaseService's patch method to update the user_information table
        $response = $this->supabaseService->patch('user_information', $data, $conditions, $accessToken);

        if (is_null($response)) {
            \Log::error("Update Theme Failed: Unable to update theme in Supabase for user ID $userId", [
                'response' => $response,
            ]);
            return false;
        }

        return true;
    }
}