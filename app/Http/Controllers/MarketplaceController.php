<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Services\SupabaseService;

class MarketplaceController extends Controller
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

        // Get user profile information if authenticated
        $userProfile = null;
        if ($isAuthenticated) {
            $userProfile = $this->supabaseService->getUserProfile($userId);
            Log::info("Marketplace accessed by authenticated user: {$userId}");
        } else {
            Log::info("Marketplace accessed by guest");
        }

        // Fetch featured listings (e.g., the most recent 3 listings)
        $featuredListings = $this->supabaseService->fetchFeaturedListings();

        // Fetch all listings (or search results)
        $listings = [];
        if ($request->isMethod('post') && $request->has('search')) {
            $listings = $this->supabaseService->searchListings($request->all());
        } elseif ($request->isMethod('post') && $request->has('reset_search')) {
            $listings = $this->supabaseService->fetchListings();
        } else {
            $listings = $this->supabaseService->fetchListings();
        }

        // Handle like/unlike actions (only for authenticated users)
        if ($request->isMethod('post') && $request->has('like_listing') && $isAuthenticated) {
            $listingId = $request->input('listing_id');
            $formToken = $request->input('form_token');

            if (!$listingId || !$this->validateFormToken($formToken, 'marketplace_form')) {
                Session::flash('message', 'Invalid request.');
                return redirect()->route('marketplace');
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
            return redirect()->route('marketplace');
        }

        // Handle delete listing action (only for authenticated users)
        if ($request->isMethod('post') && $request->has('delete_listing') && $isAuthenticated) {
            $listingId = $request->input('listing_id');
            $formToken = $request->input('form_token');

            if (!$listingId || !$this->validateFormToken($formToken, 'delete_listing_form_' . $listingId)) {
                Log::error("Delete Failed: Invalid request. Listing ID: $listingId, Token: $formToken");
                Session::flash('message', 'Invalid request.');
                return redirect()->route('marketplace');
            }

            if ($this->supabaseService->deleteListing($listingId, $userId)) {
                Log::info("Listing deleted successfully", ['listing_id' => $listingId]);
                Session::flash('message', 'Listing deleted successfully.');
            } else {
                Log::warning("Failed to delete listing", ['listing_id' => $listingId]);
                Session::flash('message', 'Failed to delete listing.');
            }

            return redirect()->route('marketplace');
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
            $selectedListing = $this->supabaseService->fetchListingById($viewDetails);
            if ($selectedListing && $isAuthenticated && ($selectedListing['user_id'] ?? null) !== $userId) {
                $selectedListingHasLiked = $this->supabaseService->hasUserLikedListing($userId, $selectedListing['id']);
            }
        }

        // Add hasLiked to each listing (only for authenticated users)
        $listingsWithLikes = [];
        foreach ($listings as $listing) {
            $listing['hasLiked'] = $isAuthenticated && isset($listing['user_id'])
                ? $this->supabaseService->hasUserLikedListing($userId, $listing['id'])
                : false;
            // Generate a unique form token for the delete form
            $listing['delete_form_token'] = $this->generateFormToken('delete_listing_form_' . $listing['id']);
            $listingsWithLikes[] = $listing;
        }

        // Generate form token for the like/unlike form
        $formToken = $this->generateFormToken('marketplace_form');

        // Search criteria for the filter bar
        $searchCriteria = [
            'animal-type' => $request->input('animal-type', ''),
            'location' => $request->input('location', ''),
            'min-price' => $request->input('min-price', ''),
            'max-price' => $request->input('max-price', ''),
        ];

        // Define $showAuthForm
        $showAuthForm = $request->query('show_auth_form', false) === 'true';

        return view('marketplace', [
            'pageTitle' => 'FarmstoMarkets - Marketplace',
            'theme' => session('theme', 'original'),
            'availableThemes' => config('themes.available'),
            'featuredListings' => $featuredListings,
            'listings' => $listingsWithLikes,
            'selectedListing' => $selectedListing,
            'selectedListingHasLiked' => $selectedListingHasLiked,
            'formToken' => $formToken,
            'searchCriteria' => $searchCriteria,
            'message' => Session::get('message'),
            'showAuthForm' => $showAuthForm,
            'isAuthenticated' => $isAuthenticated,
            'userProfile' => $userProfile, // Add user profile to the view
        ]);
    }

    public function createListing(Request $request)
    {
        $logger = Log::channel('supabase');
        $logger->info('Create listing request received');

        // Check if the user is authenticated
        $userId = session('supabase_user_id');
        if (!$userId) {
            $logger->error('User not authenticated');
            return redirect()->back()->withErrors(['listing' => 'User not authenticated.']);
        }

        // Log the user_id for debugging
        $logger->info('Creating listing with user_id', ['user_id' => $userId]);

        // Get the authenticated user's token
        $accessToken = session('supabase_token');
        if (!$accessToken) {
            $logger->error('No access token found in session');
            return redirect()->back()->withErrors(['listing' => 'No access token found. Please log in again.']);
        }

        // Validate the request data
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'type' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'preferred_contact' => 'required|in:email,phone',
            'image' => 'required|image|mimes:jpeg,png,gif|max:5120', // 5MB
        ]);

        // Upload the image using SupabaseService with the auth token
        $image = $request->file('image');
        if (!$image) {
            $logger->error('No image file provided');
            return redirect()->back()->withErrors(['image' => 'No image file provided.']);
        }
        
        $imageUrl = $this->supabaseService->uploadImage($image, 'public/', $accessToken); // Pass the access token
        if (!$imageUrl) {
            $logger->error('Failed to upload image for listing');
            return redirect()->back()->withErrors(['image' => 'Failed to upload image. Please try again.']);
        }

        $logger->info('Image uploaded successfully', ['imageUrl' => $imageUrl]);

        // Prepare the listing data
        $data = [
            'title' => $validated['title'],
            'price' => $validated['price'],
            'type' => $validated['type'],
            'location' => $validated['location'],
            'description' => $validated['description'],
            'contact_email' => $validated['contact_email'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'preferred_contact' => $validated['preferred_contact'],
            'image_url' => $imageUrl,
            'user_id' => $userId,
        ];

        // Create the listing using SupabaseService with the auth token
        $result = $this->supabaseService->createListing($data, $accessToken); // Pass the access token
        if ($result) {
            $logger->info('Listing created successfully');
            return redirect()->route('marketplace')->with('message', 'Listing created successfully!');
        }

        $logger->warning('Failed to create listing');
        return redirect()->back()->withErrors(['listing' => 'Failed to create listing.']);
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
