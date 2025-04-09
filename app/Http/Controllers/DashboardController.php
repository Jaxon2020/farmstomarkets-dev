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
    
        // Log the user_id for debugging
        Log::info('Session user_id in DashboardController', ['user_id' => $userId]);
    
        // Define search criteria for the form
        $searchCriteria = [
            'animal-type' => $request->input('animal-type', ''),
            'location' => $request->input('location', ''),
            'min-price' => $request->input('min-price', ''),
            'max-price' => $request->input('max-price', ''),
        ];
    
        // Handle delete listing action (only for authenticated users)
        if ($request->isMethod('post') && $request->has('delete_listing') && $isAuthenticated) {
            $listingId = $request->input('listing_id');
            $formToken = $request->input('form_token');
    
            if (!$listingId || !$this->validateFormToken($formToken, 'delete_listing_form_' . $listingId)) {
                Log::error("Delete Failed: Invalid request. Listing ID: $listingId, Token: $formToken");
                Session::flash('message', 'Invalid request.');
                return redirect()->route('dashboard');
            }
    
            if ($this->supabaseService->deleteListing($listingId, $userId, $accessToken)) {
                Log::info("Listing deleted successfully", ['listing_id' => $listingId]);
                Session::flash('message', 'Listing deleted successfully.');
            } else {
                Log::warning("Failed to delete listing", ['listing_id' => $listingId]);
                Session::flash('message', 'Failed to delete listing.');
            }
    
            return redirect()->route('dashboard');
        }
    
        // Fetch user's listings with search criteria
        $userListings = [];
        if ($request->isMethod('post') && $request->has('search')) {
            $userListings = $this->supabaseService->fetchUserListings($userId, $accessToken, $searchCriteria);
        } elseif ($request->isMethod('post') && $request->has('reset_search')) {
            $userListings = $this->supabaseService->fetchUserListings($userId, $accessToken);
        } else {
            $userListings = $this->supabaseService->fetchUserListings($userId, $accessToken);
        }
    
        // Add hasLiked and delete_form_token to each user listing
        $userListingsWithExtras = [];
        foreach ($userListings as $listing) {
            $listing['hasLiked'] = $isAuthenticated && isset($listing['user_id'])
                ? $this->supabaseService->hasUserLikedListing($userId, $listing['id'])
                : false;
            $listing['delete_form_token'] = $this->generateFormToken('delete_listing_form_' . $listing['id']);
            $userListingsWithExtras[] = $listing;
        }
    
        // Log the fetched listings for debugging
        Log::info('User listings fetched', ['userListings' => $userListingsWithExtras]);
    
        // Fetch liked listings with search criteria
        $likedListings = [];
        if ($request->isMethod('post') && $request->has('search')) {
            $likedListings = $this->supabaseService->fetchLikedListings($userId, $accessToken, $searchCriteria);
        } elseif ($request->isMethod('post') && $request->has('reset_search')) {
            $likedListings = $this->supabaseService->fetchLikedListings($userId, $accessToken);
        } else {
            $likedListings = $this->supabaseService->fetchLikedListings($userId, $accessToken);
        }
    
        // Add hasLiked and delete_form_token to each liked listing
        $likedListingsWithExtras = [];
        foreach ($likedListings as $listing) {
            $listing['hasLiked'] = $isAuthenticated && isset($listing['user_id'])
                ? $this->supabaseService->hasUserLikedListing($userId, $listing['id'])
                : false;
            $listing['delete_form_token'] = $this->generateFormToken('delete_listing_form_' . $listing['id']);
            $likedListingsWithExtras[] = $listing;
        }
    
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
    
        // Check for a specific listing to view
        $viewDetails = $request->query('view_details');
        $selectedListing = null;
        $selectedListingHasLiked = false;
        if ($viewDetails) {
            $selectedListing = $this->supabaseService->fetchListingById($viewDetails, $accessToken);
            if ($selectedListing && $isAuthenticated && ($selectedListing['user_id'] ?? null) !== $userId) {
                $selectedListingHasLiked = $this->supabaseService->hasUserLikedListing($userId, $selectedListing['id']);
            }
        }
        
        // Generate form token for the like/unlike form
        $formToken = $this->generateFormToken('dashboard_form');
        
        return view('dashboard', [
            'pageTitle' => 'FarmstoMarkets - Dashboard',
            'theme' => session('theme', 'original'),
            'availableThemes' => config('themes.available'),
            'userListings' => $userListingsWithExtras,
            'likedListings' => $likedListingsWithExtras,
            'formToken' => $formToken,
            'searchCriteria' => $searchCriteria,
            'message' => Session::get('message'),
            'selectedListing' => $selectedListing,
            'selectedListingHasLiked' => $selectedListingHasLiked,
            'isAuthenticated' => $isAuthenticated,
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