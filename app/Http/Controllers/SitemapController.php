<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Merchandise;
use App\Support\Seo;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Static public pages: path => [change frequency, priority].
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const STATIC_PAGES = [
        '/' => ['daily', '1.0'],
        '/events' => ['daily', '0.9'],
        '/about' => ['monthly', '0.8'],
        '/merchandise' => ['weekly', '0.8'],
        '/gallery' => ['weekly', '0.7'],
        '/contact' => ['monthly', '0.6'],
        '/terms' => ['yearly', '0.3'],
        '/privacy' => ['yearly', '0.3'],
    ];

    /**
     * Build sitemap.xml from the static pages plus every public trip and product.
     */
    public function __invoke(): Response
    {
        $lastUpdated = collect([
            Event::max('updated_at'),
            Merchandise::max('updated_at'),
        ])->filter()->max();

        $urls = collect(self::STATIC_PAGES)->map(fn (array $meta, string $path): array => [
            'loc' => Seo::SITE_URL.($path === '/' ? '/' : $path),
            'changefreq' => $meta[0],
            'priority' => $meta[1],
            'lastmod' => $lastUpdated,
        ])->values();

        $events = Event::query()
            ->whereIn('status', ['published', 'completed'])
            ->orderByDesc('start_date')
            ->get(['slug', 'status', 'updated_at'])
            ->map(fn (Event $event): array => [
                'loc' => Seo::SITE_URL.'/events/'.$event->slug,
                'changefreq' => $event->status === 'published' ? 'weekly' : 'yearly',
                'priority' => $event->status === 'published' ? '0.8' : '0.4',
                'lastmod' => $event->updated_at,
            ]);

        $merchandise = Merchandise::published()
            ->get(['id', 'updated_at'])
            ->map(fn (Merchandise $item): array => [
                'loc' => Seo::SITE_URL.'/merchandise/'.$item->getRouteKey(),
                'changefreq' => 'weekly',
                'priority' => '0.5',
                'lastmod' => $item->updated_at,
            ]);

        return response()
            ->view('sitemap', ['urls' => $urls->concat($events)->concat($merchandise)])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
