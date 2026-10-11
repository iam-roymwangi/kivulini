<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Event;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReviewController extends Controller
{
    /**
     * Store a new review for a completed event.
     * Image is already compressed/converted to WebP on the client; we just store it.
     */
    public function store(StoreReviewRequest $request, Event $event): RedirectResponse
    {
        abort_unless($event->status === 'completed', 403, 'Reviews can only be submitted for completed events.');

        $imagePath = null;

        if ($request->hasFile('image')) {
            $filename = Str::ulid().'.webp';
            $storagePath = 'reviews/'.$event->id.'/'.$filename;
            Storage::disk('public')->put($storagePath, $request->file('image')->get());
            $imagePath = $storagePath;
        }

        Review::create([
            'event_id' => $event->id,
            'reviewer_name' => $request->input('reviewer_name'),
            'reviewer_email' => $request->input('reviewer_email'),
            'rating' => $request->input('rating'),
            'body' => $request->input('body'),
            'image_path' => $imagePath,
            'is_approved' => false,
        ]);

        return back()->with('success', 'Your review has been submitted and is pending approval. Thank you!');
    }
}
