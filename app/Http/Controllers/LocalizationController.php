<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocalizationController extends Controller
{
    public function setLocale(Request $request)
    {
        $locale = $request->input('locale');

        if (!in_array($locale, ['en', 'bn'])) {
            $locale = 'en';
        }

        // Update user preference
        auth()->user()->update(['locale' => $locale]);

        // Set session
        session(['locale' => $locale]);
        app()->setLocale($locale);

        return response()->json([
            'success' => true,
            'locale' => $locale,
        ]);
    }
}
