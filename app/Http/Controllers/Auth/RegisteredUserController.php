<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

            // Since Supabase handles the user creation, we won't use the local User model
            // Optionally, you can still create a local User record if needed for Laravel's auth system
            /*
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password), // Store hashed password locally
            ]);
            */

            // Fire the Registered event (if you still want to use Laravel events)
            // Note: You might need to adjust this based on whether you keep a local User model
            // event(new Registered($user));

            // Log the user in using Supabase's session (or Laravel's auth if synced locally)
            // For Supabase-only auth, you’d typically use the access token
            $accessToken = $userData->access_token;
            // You could store this token in the session or use it with Laravel’s auth system
            // For simplicity, here we assume immediate login isn’t needed unless synced locally

            // If you want to use Laravel’s Auth system, you’d need to sync the user locally and log in:
            /*
            Auth::login($user);
            */

            return redirect(route('dashboard', absolute: false))
                ->with('success', 'Registration successful! Please check your email for confirmation.');
        } catch (\Exception $e) {
            // Handle any errors from Supabase
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => [$e->getMessage()],
            ]);
        }
    }
}