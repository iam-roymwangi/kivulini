<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="index,follow">
        <meta name="description" content="Kivulini Adventures curates road trips, hikes, and getaway vacations across Kenya. Discover upcoming experiences, browse past trips, and book your next adventure.">
        <meta name="keywords" content="Kivulini Adventures, Kenya trips, hiking Kenya, road trips Kenya, group travel Kenya, vacations Kenya">
        <meta property="og:title" content="Kivulini Adventures | Explore Kenya with Curated Trips">
        <meta property="og:description" content="Discover upcoming adventures, browse past trips, and book curated road trips, hikes, and vacations across Kenya.">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url('/') }}">
        <link rel="canonical" href="{{ url('/') }}">
        <title>Kivulini Adventures | Explore Kenya with Curated Trips</title>
        @vite(['resources/css/app.css'])
    </head>
    <body class="bg-background text-foreground antialiased">
        <main>
            <section class="overflow-hidden border-b border-border bg-slate-950 text-white">
                <div class="mx-auto max-w-7xl px-4 py-24 md:px-8 lg:px-12 lg:py-32">
                    <div class="max-w-3xl space-y-6">
                        <p class="inline-flex items-center gap-2 rounded-full border border-amber-400/30 bg-amber-400/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-amber-300">
                            Adventure travel in Kenya
                        </p>
                        <h1 class="text-4xl font-black leading-tight sm:text-5xl lg:text-7xl">
                            Explore Kenya with <span class="text-amber-400">Kivulini Adventures</span>
                        </h1>
                        <p class="max-w-2xl text-base leading-8 text-slate-300 sm:text-lg">
                            We curate road trips, scenic hikes, and weekend getaways for travelers who want safe logistics,
                            memorable destinations, and a community-driven experience from booking to checkout.
                        </p>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('events.list') }}" class="inline-flex items-center justify-center rounded-full bg-amber-400 px-6 py-3 text-sm font-bold text-slate-950 transition hover:bg-amber-300">
                                View upcoming trips
                            </a>
                            <a href="{{ route('contact') }}" class="inline-flex items-center justify-center rounded-full border border-white/15 bg-white/5 px-6 py-3 text-sm font-semibold text-white transition hover:border-amber-400/40 hover:bg-white/10">
                                Contact the team
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mx-auto max-w-7xl px-4 py-16 md:px-8 lg:px-12 lg:py-20">
                <div class="grid gap-6 md:grid-cols-3">
                    <article class="rounded-2xl border border-border bg-card p-6 shadow-xs">
                        <h2 class="text-xl font-bold">Curated adventures</h2>
                        <p class="mt-2 text-sm leading-7 text-muted-foreground">Handpicked road trips, hikes, and weekend escapes designed for memorable group travel.</p>
                    </article>
                    <article class="rounded-2xl border border-border bg-card p-6 shadow-xs">
                        <h2 class="text-xl font-bold">Kenya-first itineraries</h2>
                        <p class="mt-2 text-sm leading-7 text-muted-foreground">Trips centered on scenic routes, local culture, and destinations people actually want to revisit.</p>
                    </article>
                    <article class="rounded-2xl border border-border bg-card p-6 shadow-xs">
                        <h2 class="text-xl font-bold">Community-focused</h2>
                        <p class="mt-2 text-sm leading-7 text-muted-foreground">Travel with a friendly crew, thoughtful coordination, and a booking flow built for comfort.</p>
                    </article>
                </div>
            </section>

            <section class="mx-auto max-w-7xl px-4 pb-16 md:px-8 lg:px-12">
                <div class="mb-8 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wider text-amber-500">Featured trips</p>
                        <h2 class="mt-2 text-3xl font-black text-foreground sm:text-4xl">Upcoming experiences worth booking</h2>
                    </div>
                    <a href="{{ route('events.list') }}" class="hidden text-sm font-semibold text-amber-500 hover:text-amber-400 sm:inline-flex">Browse all trips</a>
                </div>

                @if ($featuredEvents->isNotEmpty())
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($featuredEvents as $event)
                            <article class="rounded-2xl border border-border bg-card p-6 shadow-xs">
                                <p class="text-xs font-semibold uppercase tracking-wider text-amber-500">{{ $event->type }}</p>
                                <h3 class="mt-2 text-xl font-bold">{{ $event->title }}</h3>
                                <p class="mt-2 text-sm text-muted-foreground">{{ $event->location }}</p>
                                <p class="mt-4 text-sm text-foreground">
                                    {{ \Illuminate\Support\Str::limit($event->summary, 120) }}
                                </p>
                                <p class="mt-4 text-sm font-semibold text-amber-500">
                                    {{ $event->start_date->format('M d, Y') }} · KES {{ number_format((float) $event->price) }}
                                </p>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-border bg-card p-8 text-center">
                        <p class="text-lg font-semibold">New trips are on the way.</p>
                        <p class="mt-2 text-sm text-muted-foreground">Check back soon or contact us for private group bookings and custom itineraries.</p>
                    </div>
                @endif
            </section>

            <section class="bg-muted/40 px-4 py-16 md:px-8 lg:px-12">
                <div class="mx-auto max-w-7xl">
                    <div class="mb-8">
                        <p class="text-sm font-bold uppercase tracking-wider text-amber-500">Past trips</p>
                        <h2 class="mt-2 text-3xl font-black text-foreground sm:text-4xl">A glimpse of the journey</h2>
                    </div>

                    @if ($featuredGallery->isNotEmpty())
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($featuredGallery as $media)
                                <article class="rounded-2xl border border-border bg-card p-6 shadow-xs">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-amber-500">Featured memory</p>
                                    <h3 class="mt-2 text-xl font-bold">{{ $media->event?->title ?? 'Kivulini Adventures' }}</h3>
                                    <p class="mt-2 text-sm text-muted-foreground">{{ $media->event?->location ?? 'Kenya' }}</p>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-2xl border border-border bg-card p-8 text-center">
                            <p class="text-lg font-semibold">Gallery coming soon.</p>
                            <p class="mt-2 text-sm text-muted-foreground">We are collecting highlights from past hikes, road trips, and vacations.</p>
                        </div>
                    @endif
                </div>
            </section>
        </main>
    </body>
</html>