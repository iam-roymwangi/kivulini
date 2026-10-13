<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ProcessEventMedia;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminGalleryController extends Controller
{
    /**
     * Display a listing of the gallery images for management.
     */
    public function index(): Response
    {
        $media = EventMedia::with('event')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->through(fn (EventMedia $item) => [
                'id' => $item->id,
                'file_path' => $item->file_path,
                'type' => $item->type,
                'is_featured' => $item->is_featured,
                'sort_order' => $item->sort_order,
                'url' => Storage::url($item->file_path),
                'event' => $item->event ? [
                    'id' => $item->event->id,
                    'title' => $item->event->title,
                    'status' => $item->event->status,
                ] : null,
            ]);

        $events = Event::orderByDesc('status')
            ->orderBy('title')
            ->get(['id', 'title', 'status', 'type']);

        return Inertia::render('admin/gallery/Index', [
            'media' => $media,
            'events' => $events,
        ]);
    }

    /**
     * Store newly uploaded gallery images.
     */
    public function store(Request $request, ProcessEventMedia $processor): RedirectResponse
    {
        $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['image', 'max:10240', 'mimes:jpeg,jpg,png,gif,webp'],
        ]);

        $event = Event::findOrFail($request->input('event_id'));

        $nextOrder = EventMedia::where('event_id', $event->id)->max('sort_order') + 1;

        foreach ($request->file('images') as $index => $file) {
            // Quality 90 ensures excellent clarity while keeping it compressed and converted to WebP
            $processor->handle($event, $file, [
                'quality' => 90,
                'is_featured' => false,
                'sort_order' => $nextOrder + $index,
            ]);
        }

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Images uploaded and converted to WebP successfully.');
    }

    /**
     * Update the gallery image — reassign event and/or replace with a cropped version.
     */
    public function update(Request $request, EventMedia $media, ProcessEventMedia $processor): RedirectResponse
    {
        $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'image' => ['nullable', 'image', 'max:10240', 'mimes:jpeg,jpg,png,gif,webp'],
        ]);

        $media->update(['event_id' => $request->input('event_id')]);

        if ($request->hasFile('image')) {
            // Delete the old file
            if ($media->file_path) {
                Storage::disk('public')->delete($media->file_path);
            }

            $event = Event::findOrFail($request->input('event_id'));

            $processor->handle($event, $request->file('image'), [
                'quality' => 90,
                'is_featured' => $media->is_featured,
                'sort_order' => $media->sort_order,
            ]);

            // The processor creates a new record; delete the old one
            $media->delete();
        }

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Gallery image updated successfully.');
    }

    /**
     * Remove the specified gallery image.
     */
    public function destroy(EventMedia $media): RedirectResponse
    {
        if ($media->file_path) {
            Storage::disk('public')->delete($media->file_path);
        }

        $media->delete();

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Image deleted successfully.');
    }

    /**
     * Toggle public gallery publication status (is_featured).
     */
    public function toggleFeatured(EventMedia $media): RedirectResponse
    {
        $media->update([
            'is_featured' => ! $media->is_featured,
        ]);

        $status = $media->is_featured ? 'published to the public gallery' : 'removed from the public gallery';

        return redirect()->route('admin.gallery.index')
            ->with('success', "Image has been {$status}.");
    }
}
