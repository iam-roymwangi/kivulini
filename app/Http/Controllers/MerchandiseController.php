<?php

namespace App\Http\Controllers;

use App\Http\Resources\MerchandiseResource;
use App\Models\Merchandise;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MerchandiseController extends Controller
{
    public function index(Request $request): Response
    {
        $merchandise = Merchandise::published()
            ->with('images')
            ->orderByDesc('created_at')
            ->paginate(12);

        return Inertia::render('merchandise/Index', [
            'head' => Seo::make(
                'Merchandise – Wear the Adventure',
                'Shop Kivulini Adventures merchandise: t-shirts, hoodies, hats and more. Pay with M-Pesa and wear your adventures.',
            )
                ->keywords('Kivulini Adventures merch, Kenya adventure apparel, hiking t-shirts Kenya, adventure hoodies')
                ->canonical('/merchandise')
                ->image('/assets/images/merch-hero.webp')
                ->toHead(),
            'merchandise' => MerchandiseResource::collection($merchandise),
        ]);
    }

    public function show(Merchandise $merchandise): Response
    {
        abort_unless($merchandise->status === 'published', 404);

        $merchandise->load('images');

        $cover = $merchandise->images->firstWhere('is_cover', true) ?? $merchandise->images->first();

        return Inertia::render('merchandise/Show', [
            'head' => Seo::make(
                $merchandise->name,
                $merchandise->description ?: "Shop {$merchandise->name} from Kivulini Adventures.",
            )
                ->type('product')
                ->canonical('/merchandise/'.$merchandise->getRouteKey())
                ->image($cover ? Storage::url($cover->file_path) : null)
                ->toHead(),
            'merchandise' => (new MerchandiseResource($merchandise))->resolve(),
        ]);
    }
}
