<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function index(Request $request)
    {
        $showAuthForm = $request->query('show_auth_form', false) === 'true';

        return view('about', [
            'pageTitle' => 'FarmMarket - About Us',
            'showAuthForm' => $showAuthForm,
            'availableThemes' => config('themes.available'),
        ]);
    }
}