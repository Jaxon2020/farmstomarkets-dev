<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AgreementController extends Controller
{
    public function index(Request $request)
    {
        $showAuthForm = $request->query('show_auth_form', false) === 'true';

        return view('agreement', [
            'pageTitle' => 'FarmMarket - Service Agreement',
            'showAuthForm' => $showAuthForm,
            'availableThemes' => config('themes.available'),
        ]);
    }
}