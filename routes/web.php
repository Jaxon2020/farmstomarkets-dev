<?php

use App\Http\Controllers\Auth\SupabaseAuthController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\DashboardController;

// Share available themes with all views
View::share('availableThemes', config('themes.available'));

// Public routes (accessible to all users)
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [\App\Http\Controllers\AboutController::class, 'index'])->name('about');
Route::get('/agreement', [\App\Http\Controllers\AgreementController::class, 'index'])->name('agreement');

// Dashboard route
Route::match(['get', 'post'], '/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

// Theme switching route
Route::post('/theme/switch', [ThemeController::class, 'switchTheme'])->name('theme.switch');

// Supabase authentication routes
Route::get('/login', [SupabaseAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [SupabaseAuthController::class, 'login']);
Route::get('/register', [SupabaseAuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [SupabaseAuthController::class, 'register']);

// Authenticated Supabase routes
Route::get('/me', [SupabaseAuthController::class, 'me'])->middleware('auth:supabase')->name('me');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Marketplace routes
Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace');
Route::post('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.store');

// Include Laravel's default auth routes
require __DIR__.'/auth.php';
