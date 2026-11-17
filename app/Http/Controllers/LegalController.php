<?php

namespace App\Http\Controllers;

use App\Support\Seo;
use Inertia\Inertia;
use Inertia\Response;

class LegalController extends Controller
{
    /**
     * Display the Terms and Conditions page.
     */
    public function terms(): Response
    {
        return Inertia::render('legal/TermsConditions', [
            'head' => Seo::make(
                'Terms and Conditions',
                'Read the Terms and Conditions for all bookings, trips, events and shop purchases made with Kivulini Adventures.',
            )->canonical('/terms')->toHead(),
        ]);
    }

    /**
     * Display the Privacy Policy page.
     */
    public function privacy(): Response
    {
        return Inertia::render('legal/PrivacyPolicy', [
            'head' => Seo::make(
                'Privacy Policy',
                'Learn how Kivulini Adventures collects, uses and protects your personal data under the Kenya Data Protection Act, 2019.',
            )->canonical('/privacy')->toHead(),
        ]);
    }
}
