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
        $userId = session('supabase_user_id'); // Use Supabase UUID from session
        $accessToken = session('supabase_token'); // Use Supabase access token from session
        $isAuthenticated = !empty($userId) && preg_match('/^[0-9a-fA-F-]{36}$/', $userId) && !empty($accessToken);
    
        // Log access for debugging
        if ($isAuthenticated) {
            Log::info("Dashboard accessed by authenticated user: {$userId}");
        } else {
            Log::info("Dashboard accessed by guest");
        }
    
        // Fetch featured listings (e.g., the most recent 3 listings)
        try {
            $featuredListings = $this->supabaseService->fetchFeaturedListings($accessToken) ?? [];
        } catch (\Exception $e) {
            Log::error("Failed to fetch featured listings: " . $e->getMessage());
            $featuredListings = []; // Default to an empty array if the fetch fails
        }
    
        // Fetch only the authenticated user's published listings
        $listings = [];
        if ($isAuthenticated) {
            if ($request->isMethod('post') && $request->has('search')) {
                $listings = $this->supabaseService->searchListings(array_merge($request->all(), ['user_id' => $userId]), $accessToken);
            } elseif ($request->isMethod('post') && $request->has('reset_search')) {
                $listings = $this->supabaseService->fetchUserListings($userId, $accessToken);
            } else {
                $listings = $this->supabaseService->fetchUserListings($userId, $accessToken);
            }
        } else {
            $listings = [];
        }
    
        // Fetch liked listings for the authenticated user
        $likedListings = [];
        if ($isAuthenticated) {
            try {
                $likedListings = $this->supabaseService->fetchLikedListings($userId, $accessToken);
            } catch (\Exception $e) {
                Log::error("Failed to fetch liked listings: " . $e->getMessage());
                $likedListings = [];
            }
        }

        // Handle like/unlike actions (only for authenticated users)
        if ($request->isMethod('post') && $request->has('like_listing') && $isAuthenticated) {
            // ... (keep this section as is)
        }
    
        // Handle delete listing action (only for authenticated users)
        if ($request->isMethod('post') && $request->has('delete_listing') && $isAuthenticated) {
            // ... (keep this section as is)
        }
    
        // Handle create listing (only for authenticated users)
        if ($request->isMethod('post') && $request->has('create_listing')) {
            if (!$isAuthenticated) {
                Session::flash('message', 'Please log in to create a listing.');
                return redirect()->route('login');
            }
            return $this->createListing($request);
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
    
        // Add hasLiked to each published listing
        $listingsWithLikes = [];
        foreach ($listings as $listing) {
            $listing['hasLiked'] = $isAuthenticated && isset($listing['user_id'])
                ? $this->supabaseService->hasUserLikedListing($userId, $listing['id'])
                : false;
            $listing['delete_form_token'] = $this->generateFormToken('delete_listing_form_' . $listing['id']);
            $listingsWithLikes[] = $listing;
        }

        // Add hasLiked to each liked listing (all should be true since they’re liked)
        $likedListingsWithLikes = [];
        foreach ($likedListings as $listing) {
            $listing['hasLiked'] = true; // Liked listings are inherently liked
            $listing['delete_form_token'] = $this->generateFormToken('delete_listing_form_' . $listing['id']);
            $likedListingsWithLikes[] = $listing;
        }
    
        // Generate form token for the like/unlike form
        $formToken = $this->generateFormToken('dashboard_form');
    
        // Search criteria for the filter bar
        $searchCriteria = [
            'animal-type' => $request->input('animal-type', ''),
            'location' => $request->input('location', ''),
            'min-price' => $request->input('min-price', ''),
            'max-price' => $request->input('max-price', ''),
        ];
    
        $showAuthForm = $request->query('show_auth_form', false) === 'true';
    
        return view('dashboard', [
            'pageTitle' => 'FarmstoMarkets - Dashboard',
            'theme' => session('theme', 'original'),
            'availableThemes' => config('themes.available'),
            'featuredListings' => $featuredListings,
            'listings' => $listingsWithLikes,
            'likedListings' => $likedListingsWithLikes, // Pass liked listings
            'selectedListing' => $selectedListing,
            'selectedListingHasLiked' => $selectedListingHasLiked,
            'formToken' => $formToken,
            'searchCriteria' => $searchCriteria,
            'message' => Session::get('message'),
            'showAuthForm' => $showAuthForm,
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