<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ProcessEventMedia;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEventRequest;
use App\Http\Requests\Admin\UpdateEventRequest;
use App\Models\Event;
use App\Models\EventMedia;
use App\Models\EventQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    /**
     * List all events for admin management.
     */
    public function index(Request $request): Response
    {
        $events = Event::withTrashed()
            ->withCount('bookings')
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('admin/events/Index', [
            'events' => $events,
        ]);
    }

    /**
     * Show the form for creating a new event.
     */
    public function create(): Response
    {
        return Inertia::render('admin/events/Create');
    }

    /**
     * Store a newly created event.
     */
    public function store(StoreEventRequest $request, ProcessEventMedia $processor): RedirectResponse
    {
        $event = DB::transaction(function () use ($request, $processor): Event {
            $event = Event::create([
                ...$request->safe()->except(['images', 'questions']),
                'slug' => Str::slug($request->title).'-'.Str::lower(Str::random(6)),
            ]);

            // Process & store uploaded images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $processor->handle($event, $file, [
                        'is_featured' => $index === 0,
                        'sort_order' => $index,
                    ]);
                }
            }

            // Create questions
            foreach ($request->input('questions', []) as $i => $q) {
                EventQuestion::create([
                    'event_id' => $event->id,
                    'question_text' => $q['question_text'],
                    'type' => $q['type'],
                    'is_required' => $q['is_required'] ?? true,
                    'options' => ! empty($q['options']) ? $q['options'] : null,
                    'sort_order' => $i,
                ]);
            }

            return $event;
        });

        return redirect()->route('admin.events.index')
            ->with('success', "Event \"{$event->title}\" created.");
    }

    /**
     * Show the form for editing an event.
     */
    public function edit(Event $event): Response
    {
        $event->load(['media', 'questions']);

        return Inertia::render('admin/events/Edit', [
            'event' => $event,
        ]);
    }

    /**
     * Update the event.
     */
    public function update(UpdateEventRequest $request, Event $event, ProcessEventMedia $processor): RedirectResponse
    {
        DB::transaction(function () use ($request, $event, $processor): void {
            $event->update($request->safe()->except(['images', 'delete_media_ids', 'featured_media_id', 'questions', 'delete_question_ids']));

            // Delete removed media
            foreach ($request->input('delete_media_ids', []) as $mediaId) {
                $media = EventMedia::find($mediaId);
                if ($media && $media->event_id === $event->id) {
                    Storage::disk('public')->delete($media->file_path);
                    $media->delete();
                }
            }

            // Set featured media
            if ($request->filled('featured_media_id')) {
                EventMedia::where('event_id', $event->id)->update(['is_featured' => false]);
                EventMedia::where('id', $request->input('featured_media_id'))
                    ->where('event_id', $event->id)
                    ->update(['is_featured' => true]);
            }

            // Upload new images
            if ($request->hasFile('images')) {
                $nextOrder = EventMedia::where('event_id', $event->id)->max('sort_order') + 1;
                foreach ($request->file('images') as $index => $file) {
                    $processor->handle($event, $file, [
                        'is_featured' => false,
                        'sort_order' => $nextOrder + $index,
                    ]);
                }
            }

            // Sync questions: delete removed, upsert existing/new
            $deleteIds = $request->input('delete_question_ids', []);
            if (! empty($deleteIds)) {
                EventQuestion::where('event_id', $event->id)->whereIn('id', $deleteIds)->delete();
            }

            foreach ($request->input('questions', []) as $i => $q) {
                EventQuestion::updateOrCreate(
                    ['id' => $q['id'] ?? null, 'event_id' => $event->id],
                    [
                        'event_id' => $event->id,
                        'question_text' => $q['question_text'],
                        'type' => $q['type'],
                        'is_required' => $q['is_required'] ?? true,
                        'options' => ! empty($q['options']) ? $q['options'] : null,
                        'sort_order' => $i,
                    ],
                );
            }
        });

        return redirect()->route('admin.events.edit', $event)
            ->with('success', 'Event updated successfully.');
    }

    /**
     * Soft-delete the event.
     */
    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', "Event \"{$event->title}\" archived.");
    }

    /**
     * Publish completed-event media to the public gallery (is_featured = true).
     * Only allowed when event status is 'completed'.
     */
    public function publishGallery(Event $event): RedirectResponse
    {
        abort_unless($event->status === 'completed', 422, 'Only completed events can have gallery media published.');

        EventMedia::where('event_id', $event->id)->update(['is_featured' => true]);

        return redirect()->route('admin.events.edit', $event)
            ->with('success', 'All event media has been published to the public gallery.');
    }

    /**
     * Unpublish media from the public gallery.
     */
    public function unpublishGallery(Event $event): RedirectResponse
    {
        EventMedia::where('event_id', $event->id)->update(['is_featured' => false]);

        return redirect()->route('admin.events.edit', $event)
            ->with('success', 'Gallery media has been unpublished.');
    }
}
