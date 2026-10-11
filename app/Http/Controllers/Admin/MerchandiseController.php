<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ProcessMerchandiseImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMerchandiseRequest;
use App\Http\Requests\Admin\UpdateMerchandiseRequest;
use App\Http\Resources\MerchandiseImageResource;
use App\Models\Merchandise;
use App\Models\MerchandiseImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class MerchandiseController extends Controller
{
    public function index(Request $request): Response
    {
        $merchandise = Merchandise::withTrashed()
            ->with(['images' => fn ($q) => $q->where('is_cover', true)->orWhere('sort_order', 0)->limit(1)])
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('admin/merchandise/Index', [
            'merchandise' => $merchandise,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/merchandise/Create');
    }

    public function store(StoreMerchandiseRequest $request, ProcessMerchandiseImage $processor): RedirectResponse
    {
        $item = DB::transaction(function () use ($request, $processor): Merchandise {
            $item = Merchandise::create([
                ...$request->safe()->except(['images']),
                'slug' => Str::slug($request->name).'-'.Str::lower(Str::random(6)),
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $processor->handle($item, $file, [
                        'is_cover' => $index === 0,
                        'sort_order' => $index,
                    ]);
                }
            }

            return $item;
        });

        return redirect()->route('admin.merchandise.index')
            ->with('success', "Merchandise \"{$item->name}\" created.");
    }

    public function edit(Merchandise $merchandise): Response
    {
        $merchandise->load('images');

        return Inertia::render('admin/merchandise/Edit', [
            'merchandise' => $merchandise,
            'existingImages' => MerchandiseImageResource::collection($merchandise->images),
        ]);
    }

    public function update(UpdateMerchandiseRequest $request, Merchandise $merchandise, ProcessMerchandiseImage $processor): RedirectResponse
    {
        DB::transaction(function () use ($request, $merchandise, $processor): void {
            $merchandise->update($request->safe()->except(['images', 'delete_image_ids', 'cover_image_id', 'image_order']));

            // Delete removed images
            foreach ($request->input('delete_image_ids', []) as $imageId) {
                $image = MerchandiseImage::find($imageId);
                if ($image && $image->merchandise_id === $merchandise->id) {
                    Storage::disk('public')->delete($image->file_path);
                    $image->delete();
                }
            }

            // Set cover image
            if ($request->filled('cover_image_id')) {
                MerchandiseImage::where('merchandise_id', $merchandise->id)->update(['is_cover' => false]);
                MerchandiseImage::where('id', $request->input('cover_image_id'))
                    ->where('merchandise_id', $merchandise->id)
                    ->update(['is_cover' => true]);
            }

            // Reorder existing images
            foreach ($request->input('image_order', []) as $position => $imageId) {
                MerchandiseImage::where('id', $imageId)
                    ->where('merchandise_id', $merchandise->id)
                    ->update(['sort_order' => $position]);
            }

            // Upload new images
            if ($request->hasFile('images')) {
                $nextOrder = MerchandiseImage::where('merchandise_id', $merchandise->id)->max('sort_order') + 1;
                $hasCover = MerchandiseImage::where('merchandise_id', $merchandise->id)->where('is_cover', true)->exists();
                foreach ($request->file('images') as $index => $file) {
                    $processor->handle($merchandise, $file, [
                        'is_cover' => ! $hasCover && $index === 0,
                        'sort_order' => $nextOrder + $index,
                    ]);
                }
            }
        });

        return redirect()->route('admin.merchandise.edit', $merchandise)
            ->with('success', 'Merchandise updated successfully.');
    }

    public function destroy(Merchandise $merchandise): RedirectResponse
    {
        $merchandise->delete();

        return redirect()->route('admin.merchandise.index')
            ->with('success', "Merchandise \"{$merchandise->name}\" archived.");
    }
}
