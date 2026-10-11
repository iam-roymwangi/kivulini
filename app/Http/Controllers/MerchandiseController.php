<?php

namespace App\Http\Controllers;

use App\Http\Resources\MerchandiseResource;
use App\Models\Merchandise;
use Illuminate\Http\Request;
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
            'merchandise' => MerchandiseResource::collection($merchandise),
        ]);
    }

    public function show(Merchandise $merchandise): Response
    {
        abort_unless($merchandise->status === 'published', 404);

        $merchandise->load('images');

        return Inertia::render('merchandise/Show', [
            'merchandise' => (new MerchandiseResource($merchandise))->resolve(),
        ]);
    }
}
