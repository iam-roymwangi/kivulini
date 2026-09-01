<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $events = Event::published()
            ->select('slug', 'updated_at')
            ->orderByDesc('updated_at')
            ->get();

        $staticPages = [
            ['url' => route('home'),          'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => route('events.list'),   'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => route('events.gallery'), 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['url' => route('contact'),       'priority' => '0.6', 'changefreq' => 'monthly'],
        ];

        $content = view('sitemap', compact('staticPages', 'events'))->render();

        return response($content, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
