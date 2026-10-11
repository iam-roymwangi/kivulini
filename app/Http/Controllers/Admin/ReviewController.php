<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    /**
     * Paginated list of all reviews for admin management.
     */
    public function index(Request $request): Response
    {
        $reviews = Review::with('event:id,title,slug')
            ->when($request->filled('status'), function ($query) use ($request) {
                match ($request->status) {
                    'approved' => $query->where('is_approved', true),
                    'pending' => $query->where('is_approved', false),
                    default => null,
                };
            })
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Review $review) => [
                'id' => $review->id,
                'reviewer_name' => $review->reviewer_name,
                'reviewer_email' => $review->reviewer_email,
                'rating' => $review->rating,
                'body' => $review->body,
                'image_url' => $review->image_path ? Storage::url($review->image_path) : null,
                'is_approved' => $review->is_approved,
                'created_at' => $review->created_at?->toIso8601String(),
                'event' => $review->event ? [
                    'id' => $review->event->id,
                    'title' => $review->event->title,
                    'slug' => $review->event->slug,
                ] : null,
            ]);

        $counts = [
            'all' => Review::count(),
            'pending' => Review::where('is_approved', false)->count(),
            'approved' => Review::where('is_approved', true)->count(),
        ];

        return Inertia::render('admin/reviews/Index', [
            'reviews' => $reviews,
            'counts' => $counts,
            'filters' => $request->only('status'),
        ]);
    }

    /**
     * Approve a review.
     */
    public function approve(Review $review): RedirectResponse
    {
        $review->update(['is_approved' => true]);

        return back()->with('success', 'Review approved and published.');
    }

    /**
     * Revoke approval (unpublish) a review.
     */
    public function unapprove(Review $review): RedirectResponse
    {
        $review->update(['is_approved' => false]);

        return back()->with('success', 'Review removed from public listing.');
    }

    /**
     * Permanently delete a review and its associated image.
     */
    public function destroy(Review $review): RedirectResponse
    {
        if ($review->image_path) {
            Storage::disk('public')->delete($review->image_path);
        }

        $review->delete();

        return back()->with('success', 'Review deleted permanently.');
    }
}
