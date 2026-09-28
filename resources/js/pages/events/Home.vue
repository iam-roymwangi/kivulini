<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { GalleryHorizontal, HeartHandshake, MapPinned, Sparkles } from '@lucide/vue';
import EventCard from '@/components/events/EventCard.vue';
import HeroSection from '@/components/events/HeroSection.vue';
import MasonryGallery from '@/components/events/MasonryGallery.vue';
import { gallery, list as eventsList } from '@/routes/events';
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
        <HeroSection />

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

        <section id="events" class="mx-auto max-w-7xl px-4 pb-16 md:px-8 lg:px-12">
            <div class="mb-8 flex items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-bold uppercase tracking-wider text-amber-500">Featured trips</p>
                    <h2 class="mt-2 text-3xl font-black text-foreground sm:text-4xl">Upcoming experiences worth booking</h2>
                </div>
                <Link :href="eventsList.url()" class="hidden text-sm font-semibold text-amber-500 hover:text-amber-400 sm:inline-flex">
                    Browse all trips
                </Link>
            </div>

            <div v-if="featuredEvents.data.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <EventCard v-for="event in featuredEvents.data" :key="event.id" :event="event" variant="overlay" />
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
                <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wider text-amber-500">Past trips</p>
                        <h2 class="mt-2 text-3xl font-black text-foreground sm:text-4xl">A glimpse of the journey</h2>
                    </div>
                    <Link
                        :href="gallery.url()"
                        class="inline-flex items-center justify-center gap-2 rounded-full border border-border bg-card px-5 py-2.5 text-sm font-bold text-foreground transition hover:border-amber-400 hover:text-amber-500 sm:self-center"
                    >
                        <GalleryHorizontal class="h-4 w-4" />
                        View full gallery
                    </Link>
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
