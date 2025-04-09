<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use PHPSupabase\Service;

class RegisteredUserController extends Controller
{
    protected $supabase;

    /**
     * Initialize the Supabase service.
     */
    public function __construct()
    {
        // Initialize the PHPSupabase Service with your Supabase credentials
        $this->supabase = new Service(
            config('services.supabase.anon_key'),
            config('services.supabase.url')
        );
    }

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate the incoming request
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Create an instance of the Auth class from PHPSupabase
        $auth = $this->supabase->createAuth();

        try {
            // Register the user in Supabase
            $auth->createUserWithEmailAndPassword(
                $request->email,
                $request->password,
                ['name' => $request->name] // Optional user metadata
            );

            // Get the response data from Supabase
            $userData = $auth->data();
            
            // Log the structure to debug
            Log::info('Supabase registration response', ['userData' => $userData]);
            
            // Check if access_token exists before trying to access it
            $accessToken = null;
            if (isset($userData->access_token)) {
                $accessToken = $userData->access_token;
            }
            
            // Store user data in session if needed
            if (isset($userData->user) && isset($userData->user->id)) {
                session([
                    'supabase_user_id' => $userData->user->id,
                    'supabase_user' => $userData->user
                ]);
                
                if ($accessToken) {
                    session(['supabase_access_token' => $accessToken]);
                }
            }

            return redirect()->route('login')
                ->with('registration_success', true);
        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());
            // Handle any errors from Supabase
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => [$e->getMessage()],
            ]);
        }
    }
}
