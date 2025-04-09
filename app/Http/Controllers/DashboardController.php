<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Services\SupabaseService;

class DashboardController extends Controller
{
    protected $supabaseService;

    public function __construct(SupabaseService $supabaseService)
    {
        $this->supabaseService = $supabaseService;
    }

    public function index(Request $request)
    {
        $userId = session('supabase_user_id');
        $accessToken = session('supabase_token');
        $isAuthenticated = !empty($userId) && !empty($accessToken);

        // Redirect to login if not authenticated
        if (!$isAuthenticated) {
            return redirect()->route('login');
        }

        // Fetch user's listings
        $userListings = $this->supabaseService->fetchUserListings($userId, $accessToken);
        
        // Fetch liked listings for the authenticated user
        $likedListings = $this->supabaseService->fetchLikedListings($userId, $accessToken);
        
        // Handle like/unlike actions
        if ($request->isMethod('post') && $request->has('like_listing')) {
            $listingId = $request->input('listing_id');
            $formToken = $request->input('form_token');

            if (!$listingId || !$this->validateFormToken($formToken, 'dashboard_form')) {
                Session::flash('message', 'Invalid request.');
                return redirect()->route('dashboard');
            }

            $hasLiked = $this->supabaseService->hasUserLikedListing($userId, $listingId);
            if ($hasLiked) {
                // Unlike the listing
                if ($this->supabaseService->removeLike($userId, $listingId)) {
                    Session::flash('message', 'Listing unliked successfully.');
                } else {
                    Session::flash('message', 'Failed to unlike listing.');
                }
            } else {
                // Like the listing
                if ($this->supabaseService->addLike($userId, $listingId)) {
                    Session::flash('message', 'Listing liked successfully.');
                } else {
                    Session::flash('message', 'Failed to like listing.');
                }
            }
            return redirect()->route('dashboard');
        }
        
        // Generate form token for the like/unlike form
        $formToken = $this->generateFormToken('dashboard_form');
        
        return view('dashboard', [
            'pageTitle' => 'FarmstoMarkets - Dashboard',
            'theme' => session('theme', 'original'),
            'availableThemes' => config('themes.available'),
            'userListings' => $userListings,
            'likedListings' => $likedListings,
            'formToken' => $formToken,
            'message' => Session::get('message'),
        ]);
    }
    
    protected function generateFormToken($formName)
    {
        $token = bin2hex(random_bytes(16));
        Session::put('form_tokens.' . $formName, $token);
        return $token;
    }

    protected function validateFormToken($token, $formName)
    {
        $storedToken = Session::get('form_tokens.' . $formName);
        if ($storedToken && hash_equals($storedToken, $token)) {
            Session::forget('form_tokens.' . $formName);
            return true;
        }
        return false;
    }
}
