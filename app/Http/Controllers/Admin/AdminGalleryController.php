<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ProcessEventMedia;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventMediaResource;
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
            ->paginate(12);

        $events = Event::orderByDesc('status')
            ->orderBy('title')
            ->get(['id', 'title', 'status', 'type']);

        return Inertia::render('admin/gallery/Index', [
            'media' => EventMediaResource::collection($media),
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
