<?php

namespace App\Http\Controllers;

use App\Http\Resources\EventMediaResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\MerchandiseResource;
use App\Models\Booking;
use App\Models\Event;
use App\Models\EventMedia;
use App\Models\Merchandise;
use App\Models\Review;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    /**
     * Display the Discovery Feed with paginated published events.
     */
    public function index(Request $request): Response
    {
        $featuredEvents = Event::published()
            ->with('media')
            ->withCount('priceTiers')
            ->orderBy('start_date')
            ->limit(3)
            ->get();

        $featuredGallery = EventMedia::query()
            ->with('event')
            ->where('type', 'image')
            ->whereHas('event', fn ($query) => $query->where('status', 'completed'))
            ->latest()
            ->limit(8)
            ->get();

        $featuredMerchandise = Merchandise::published()
            ->with('images')
            ->latest()
            ->limit(4)
            ->get();

        $pastEvents = Event::where('status', 'completed')
            ->with('media')
            ->orderByDesc('end_date')
            ->paginate(6, ['*'], 'past_page')
            ->withQueryString();

        $reviews = Review::where('is_approved', true)
            ->latest()
            ->paginate(6, ['*'], 'reviews_page')
            ->withQueryString();

        $stats = [
            'trips_completed' => Event::where('status', 'completed')->count(),
            'adventurers' => Booking::sum('quantity'),
            'destinations' => Event::distinct()->count('location'),
            'reviews' => Review::where('is_approved', true)->count(),
        ];

        return Inertia::render('events/Home', [
            'head' => Seo::make(
                'Kivulini Adventures | Road Trips, Hikes & Getaways in Kenya',
                'Kivulini Adventures curates road trips, hikes and getaway vacations across Kenya. Discover upcoming trips, browse past adventures and book your next one with M-Pesa.',
            )
                ->keywords('Kivulini Adventures, Kenya trips, hiking Kenya, road trips Kenya, group travel Kenya, vacations Kenya')
                ->canonical('/')
                ->withSiteIdentity()
                ->toHead(),
            'featuredEvents' => EventResource::collection($featuredEvents),
            'featuredGallery' => EventMediaResource::collection($featuredGallery),
            'featuredMerchandise' => MerchandiseResource::collection($featuredMerchandise),
            'pastEvents' => EventResource::collection($pastEvents),
            'stats' => $stats,
            'reviews' => $reviews->through(fn (Review $review) => [
                'id' => $review->id,
                'reviewer_name' => $review->reviewer_name,
                'rating' => $review->rating,
                'body' => $review->body,
                'image_url' => $review->image_path ? Storage::url($review->image_path) : null,
                'created_at' => $review->created_at?->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Display the dedicated paginated events list.
     */
    public function list(Request $request): Response
    {
        $query = Event::published()->with('media')->withCount('priceTiers');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $events = $query->paginate(6, ['*'], 'page')->withQueryString();

        return Inertia::render('events/List', [
            'head' => Seo::make(
                'Upcoming Trips & Events',
                'Browse all upcoming hikes, road trips and vacations in Kenya. Filter by category to find your next adventure and book online with M-Pesa.',
            )
                ->keywords('Kenya trips, upcoming events Kenya, hiking, road trips, group travel, Kivulini Adventures')
                ->canonical('/events')
                ->toHead(),
            'events' => EventResource::collection($events),
            'filters' => $request->only(['type']),
        ]);
    }

    /**
     * Display a single event by slug.
     */
    public function show(string $slug): Response
    {
        $event = Event::query()->whereRaw('slug = ?', [$slug], 'and')
            ->with([
                'media',
                'priceTiers',
                'questions' => fn ($query) => $query->orderBy('sort_order'),
                'reviews' => fn ($query) => $query->where('is_approved', true)->latest()->limit(20),
            ])
            ->firstOrFail();

        return Inertia::render('events/Show', [
            'head' => $this->eventSeo($event)->toHead(),
            'event' => new EventResource($event),
            'availableSlots' => $event->available_slots,
        ]);
    }

    /**
     * Display the dedicated paginated past trips gallery.
     */
    public function galleryPage(Request $request): Response
    {
        $pastEventsMedia = EventMedia::with('event')
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'page')->withQueryString();

        return Inertia::render('events/Gallery', [
            'head' => Seo::make(
                'Past Trips Photo Gallery',
                "Browse photos from Kivulini Adventures' past hikes, road trips and vacations across Kenya and East Africa.",
            )
                ->keywords('Kivulini Adventures gallery, Kenya trip photos, hiking photos Kenya, adventure gallery East Africa')
                ->canonical('/gallery')
                ->toHead(),
            'pastEventsMedia' => EventMediaResource::collection($pastEventsMedia),
        ]);
    }

    /**
     * Display the dedicated contact page.
     */
    public function contactPage(): Response
    {
        return Inertia::render('events/Contact', [
            'head' => Seo::make(
                'Contact Us',
                'Get in touch with Kivulini Adventures. Ask about upcoming trips, group bookings, safety or partnerships. Call +254 725 038 004 or email info@kivuliniadventures.com.',
            )
                ->keywords('contact Kivulini Adventures, Kenya trips inquiry, group bookings Kenya, adventure travel contact')
                ->canonical('/contact')
                ->toHead(),
        ]);
    }

    /**
     * Display the About Us page.
     */
    public function aboutPage(): Response
    {
        return Inertia::render('events/About', [
            'head' => Seo::make(
                'About Us',
                'Kivulini Adventures is a Kenyan adventure and travel experiences brand built around shared outings, carefully chosen destinations and good company. No Tourist Traps. Real Adventures.',
            )
                ->keywords('about Kivulini Adventures, Kenya adventure brand, group travel Kenya, road trips Kenya, Kivulini story')
                ->canonical('/about')
                ->toHead(),
        ]);
    }

    /**
     * SEO tags and Event structured data for a single trip page.
     */
    private function eventSeo(Event $event): Seo
    {
        $cover = $event->media->firstWhere('is_featured', true) ?? $event->media->first();
        $coverUrl = $cover ? Storage::url($cover->file_path) : null;
        $url = Seo::SITE_URL.'/events/'.$event->slug;

        $offers = $event->priceTiers->isNotEmpty()
            ? $event->priceTiers->map(fn ($tier): array => ['name' => $tier->name, 'price' => $tier->price])
            : collect([['name' => 'Standard', 'price' => $event->price]]);

        $seo = Seo::make(
            $event->title,
            $event->summary ?: Str::limit(strip_tags((string) $event->description), 160),
        )
            ->type('article')
            ->keywords(collect([$event->title, $event->location, Str::headline((string) $event->type), 'Kenya adventures', 'Kivulini Adventures'])->filter()->implode(', '))
            ->canonical($url)
            ->image($coverUrl)
            ->schema(array_filter([
                '@type' => 'Event',
                'name' => $event->title,
                'description' => $event->summary,
                'startDate' => $event->start_date?->toIso8601String(),
                'endDate' => $event->end_date?->toIso8601String(),
                'eventStatus' => $event->status === 'cancelled' ? 'https://schema.org/EventCancelled' : 'https://schema.org/EventScheduled',
                'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
                'location' => [
                    '@type' => 'Place',
                    'name' => $event->location,
                    'address' => ['@type' => 'PostalAddress', 'addressLocality' => $event->location, 'addressCountry' => 'KE'],
                ],
                'image' => $coverUrl ? [Seo::SITE_URL.$coverUrl] : [Seo::DEFAULT_IMAGE],
                'organizer' => ['@type' => 'Organization', 'name' => Seo::SITE_NAME, 'url' => Seo::SITE_URL.'/'],
                'offers' => $offers->map(fn (array $offer): array => [
                    '@type' => 'Offer',
                    'name' => $offer['name'],
                    'price' => (string) $offer['price'],
                    'priceCurrency' => 'KES',
                    'availability' => $event->available_slots > 0 ? 'https://schema.org/InStock' : 'https://schema.org/SoldOut',
                    'url' => $url,
                ])->values()->all(),
            ]));

        return in_array($event->status, ['published', 'completed'], true) ? $seo : $seo->noIndex();
    }
}
