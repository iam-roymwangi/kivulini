<?php

namespace App\Http\Controllers;

use App\Http\Resources\EventMediaResource;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\EventMedia;
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
            ->paginate(12);

        $featuredMedia = EventMedia::where('is_featured', true)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('events/Index', [
            'events' => EventResource::collection($events),
            'featuredMedia' => EventMediaResource::collection($featuredMedia),
        ]);
    }

    /**
     * Display a single event by slug.
     */
    public function show(string $slug): Response
    {
        $event = Event::where('slug', $slug)
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
}
