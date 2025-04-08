<?php

namespace App\Http\Controllers;

use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    protected $supabaseService;

    public function __construct(SupabaseService $supabaseService)
    {
        $this->supabaseService = $supabaseService;
    }

    public function index(Request $request)
    {
        $showAuthForm = $request->query('show_auth_form', false) === 'true';

        if (!Auth::check()) {
            \Log::info("User is not signed in. Showing login form on index.");
            session()->flash('message', 'Please log in to continue.');
        }

        $accessToken = session('access_token'); // Use Breeze's session token
        $userId = Auth::id() ? (int) Auth::id() : null; // Ensure $userId is an integer or null

        $featuredListings = $this->supabaseService->fetchListings($accessToken, $userId);
        $featuredListings = array_slice($featuredListings, 0, 3);

        return view('home', [
            'pageTitle' => 'FarmstoMarkets.com - Buy & Sell Animals Online',
            'theme' => session('theme', 'original'),
            'availableThemes' => config('themes.available'), // Ensure this is correct
            'featuredListings' => $featuredListings, // Pass the variable here
            'showAuthForm' => $showAuthForm,
        ]);
    }
}