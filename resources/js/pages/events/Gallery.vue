<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MasonryGallery from '@/components/events/MasonryGallery.vue';
import type { EventMedia } from '@/types';

interface Paginator<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
    links: { url: string | null; label: string; active: boolean }[];
}

defineProps<{
    pastEventsMedia: Paginator<EventMedia>;
}>();
</script>

<template>
    <Head title="Past Trips Photo Gallery" />

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

            <!-- Masonry Gallery -->
            <div v-if="pastEventsMedia.data.length > 0">
                <MasonryGallery :items="pastEventsMedia.data" />

                <!-- Pagination -->
                <div v-if="pastEventsMedia.last_page > 1" class="mt-16 flex items-center justify-center gap-1.5">
                    <template v-for="link in pastEventsMedia.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="rounded-xl border border-border bg-card px-4 py-2 text-sm font-semibold transition-all hover:border-amber-400 hover:text-amber-500"
                            :class="link.active ? 'border-amber-400 bg-amber-400/10 text-amber-500 font-bold' : 'text-muted-foreground'"
                            preserve-scroll
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="cursor-default rounded-xl border border-border/40 bg-card/40 px-4 py-2 text-sm text-muted-foreground/45"
                            v-html="link.label"
                        />
                    </template>
                </div>
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
