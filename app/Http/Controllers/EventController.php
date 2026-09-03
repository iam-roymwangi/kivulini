<?php

namespace App\Http\Controllers;

use App\Http\Resources\EventMediaResource;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\EventMedia;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    /**
     * Display the Discovery Feed with paginated published events.
     */
    public function index(): Response
    {
        $events = Event::published()
            ->with('media')
            ->paginate(3, ['*'], 'events_page');

        $pastEventsMedia = EventMedia::query()->whereRaw('is_featured = ?', [true], 'and')
            ->with('event')
            ->orderBy('sort_order')
            ->paginate(3, ['*'], 'gallery_page');

        return Inertia::render('events/Index', [
            'events' => EventResource::collection($events),
            'pastEventsMedia' => EventMediaResource::collection($pastEventsMedia),
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
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('id')
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
