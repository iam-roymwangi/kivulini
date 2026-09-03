<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MasonryGallery from '@/components/events/MasonryGallery.vue';
import ModernPagination from '@/components/events/ModernPagination.vue';
import type { EventMedia } from '@/types/events';
import type { Paginator } from '@/types/ui';

defineProps<{
    pastEventsMedia: Paginator<EventMedia>;
}>();
</script>

<template>
    <Head>
        <title>Past Trips Photo Gallery</title>
        <meta name="description" content="Browse photos from Kivulini Adventures' past hikes, road trips, and vacations across Kenya and East Africa." />
        <meta property="og:title" content="Past Trips Photo Gallery – Kivulini Adventures" />
        <meta property="og:description" content="Browse photos from our past hikes, road trips, and vacations across Kenya." />
        <meta property="og:type" content="website" />
    </Head>

    <div class="bg-background pt-32 pb-24 transition-colors">
        <div class="mx-auto max-w-7xl px-4 md:px-8 lg:px-12">
            <!-- Header section -->
            <div class="mb-12 border-b border-border pb-8">
                <p class="text-sm font-bold uppercase tracking-wider text-amber-500">Visual Journey</p>
                <h1 class="mt-2 text-4xl font-black text-foreground sm:text-5xl">
                    Moments from <span class="text-amber-500">Kivulini</span>
                </h1>
                <p class="mt-4 max-w-2xl text-base text-muted-foreground">
                    Step into the memories of our previous adventures. Highlighting beautiful landscapes, group laughs, and hikes from completed journeys.
                </p>
            </div>

            <!-- Masonry Gallery & Pagination -->
            <div v-if="pastEventsMedia.data && pastEventsMedia.data.length > 0">
                <MasonryGallery :items="pastEventsMedia.data" />

                <!-- Modern Pagination -->
                <ModernPagination :pagination="pastEventsMedia" item-name="pictures" />
            </div>

            <!-- Empty state -->
            <div
                v-else
                class="flex min-h-64 flex-col items-center justify-center rounded-2xl border border-border bg-card p-8 text-center"
            >
                <svg class="mx-auto h-12 w-12 text-muted-foreground/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-4 text-lg font-bold text-foreground">No photos yet</h3>
                <p class="mt-2 text-sm text-muted-foreground">We haven't uploaded gallery images for any completed trips yet. Check back soon!</p>
            </div>
        </div>
    </div>
</template>
