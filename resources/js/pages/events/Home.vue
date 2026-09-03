<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CalendarDays, Compass, HeartHandshake, MapPinned, Sparkles, Ticket } from '@lucide/vue';
import EventCard from '@/components/events/EventCard.vue';
import MasonryGallery from '@/components/events/MasonryGallery.vue';
import { contact } from '@/routes';
import type { EventMedia, PlatformEvent } from '@/types';

interface PreviewCollection<T> {
    data: T[];
}

defineProps<{
    featuredEvents: PreviewCollection<PlatformEvent>;
    featuredGallery: PreviewCollection<EventMedia>;
}>();

const highlights = [
    {
        icon: Sparkles,
        title: 'Curated adventures',
        description: 'Handpicked road trips, hikes, and weekend escapes designed for memorable group travel.',
    },
    {
        icon: MapPinned,
        title: 'Kenya-first itineraries',
        description: 'Trips centered on scenic routes, local culture, and destinations people actually want to revisit.',
    },
    {
        icon: HeartHandshake,
        title: 'Community-focused',
        description: 'Travel with a friendly crew, thoughtful coordination, and a booking flow built for comfort.',
    },
];
</script>

<template>
    <Head>
        <title>Kivulini Adventures | Explore Kenya with Curated Trips</title>
        <meta
            name="description"
            content="Kivulini Adventures curates road trips, hikes, and getaway vacations across Kenya. Discover upcoming experiences, browse past trips, and book your next adventure."
        />
        <meta
            name="keywords"
            content="Kivulini Adventures, Kenya trips, hiking Kenya, road trips Kenya, group travel Kenya, vacations Kenya"
        />
        <meta property="og:title" content="Kivulini Adventures | Explore Kenya with Curated Trips" />
        <meta
            property="og:description"
            content="Discover upcoming adventures, browse past trips, and book curated road trips, hikes, and vacations across Kenya."
        />
        <meta property="og:type" content="website" />
    </Head>

    <main class="bg-background text-foreground transition-colors">
        <section class="relative overflow-hidden border-b border-border bg-slate-950 text-white">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(251,191,36,0.20),_transparent_30%),linear-gradient(180deg,rgba(2,6,23,0.55),rgba(2,6,23,0.92))]" />
            <div class="relative mx-auto max-w-7xl px-4 py-24 md:px-8 lg:px-12 lg:py-32">
                <div class="max-w-3xl space-y-6">
                    <p class="inline-flex items-center gap-2 rounded-full border border-amber-400/30 bg-amber-400/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-amber-300">
                        <Compass class="h-3.5 w-3.5" />
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
                        <Link
                            href="/events"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-amber-400 px-6 py-3 text-sm font-bold text-slate-950 transition hover:bg-amber-300"
                        >
                            <Ticket class="h-4 w-4" />
                            View upcoming trips
                        </Link>
                        <Link
                            :href="contact.url()"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-white/15 bg-white/5 px-6 py-3 text-sm font-semibold text-white transition hover:border-amber-400/40 hover:bg-white/10"
                        >
                            <CalendarDays class="h-4 w-4" />
                            Contact the team
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-16 md:px-8 lg:px-12 lg:py-20">
            <div class="grid gap-6 md:grid-cols-3">
                <article
                    v-for="item in highlights"
                    :key="item.title"
                    class="rounded-2xl border border-border bg-card p-6 shadow-xs"
                >
                    <component :is="item.icon" class="h-8 w-8 text-amber-500" />
                    <h2 class="mt-4 text-xl font-bold">{{ item.title }}</h2>
                    <p class="mt-2 text-sm leading-7 text-muted-foreground">{{ item.description }}</p>
                </article>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 pb-16 md:px-8 lg:px-12">
            <div class="mb-8 flex items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-bold uppercase tracking-wider text-amber-500">Featured trips</p>
                    <h2 class="mt-2 text-3xl font-black text-foreground sm:text-4xl">Upcoming experiences worth booking</h2>
                </div>
                <Link href="/events" class="hidden text-sm font-semibold text-amber-500 hover:text-amber-400 sm:inline-flex">
                    Browse all trips
                </Link>
            </div>

            <div v-if="featuredEvents.data.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <EventCard v-for="event in featuredEvents.data" :key="event.id" :event="event" />
            </div>

            <div v-else class="rounded-2xl border border-dashed border-border bg-card p-8 text-center">
                <p class="text-lg font-semibold">New trips are on the way.</p>
                <p class="mt-2 text-sm text-muted-foreground">
                    Check back soon or contact us for private group bookings and custom itineraries.
                </p>
            </div>
        </section>

        <section class="bg-muted/40 px-4 py-16 md:px-8 lg:px-12">
            <div class="mx-auto max-w-7xl">
                <div class="mb-8">
                    <p class="text-sm font-bold uppercase tracking-wider text-amber-500">Past trips</p>
                    <h2 class="mt-2 text-3xl font-black text-foreground sm:text-4xl">A glimpse of the journey</h2>
                </div>

                <div v-if="featuredGallery.data.length > 0">
                    <MasonryGallery :items="featuredGallery.data" />
                </div>

                <div v-else class="rounded-2xl border border-border bg-card p-8 text-center">
                    <p class="text-lg font-semibold">Gallery coming soon.</p>
                    <p class="mt-2 text-sm text-muted-foreground">
                        We are collecting highlights from past hikes, road trips, and vacations.
                    </p>
                </div>
            </div>
        </section>
    </main>
</template>