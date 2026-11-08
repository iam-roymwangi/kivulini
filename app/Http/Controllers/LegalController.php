<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class LegalController extends Controller
{
    /**
     * Display the Terms and Conditions page.
     */
    public function terms(): Response
    {
        return Inertia::render('legal/TermsConditions');
    }

    /**
     * Display the Privacy Policy page.
     */
    public function privacy(): Response
    {
        return Inertia::render('legal/PrivacyPolicy');
    }
}
