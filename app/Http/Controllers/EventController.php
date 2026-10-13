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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $query = Event::published()->with('media');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $events = $query->paginate(6, ['*'], 'page')->withQueryString();

        return Inertia::render('events/List', [
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
                'questions' => fn ($query) => $query->orderBy('sort_order'),
                'reviews' => fn ($query) => $query->where('is_approved', true)->latest()->limit(20),
            ])
            ->firstOrFail();

        return Inertia::render('events/Show', [
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
            'pastEventsMedia' => EventMediaResource::collection($pastEventsMedia),
        ]);
    }

    /**
     * Display the dedicated contact page.
     */
    public function contactPage(): Response
    {
        return Inertia::render('events/Contact');
    }
}
