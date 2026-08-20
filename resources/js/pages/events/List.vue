<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import CategoryFilter from '@/components/events/CategoryFilter.vue';
import EventCard from '@/components/events/EventCard.vue';
import type { PlatformEvent } from '@/types';

interface Paginator<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    events: Paginator<PlatformEvent>;
    filters: { type?: string };
}>();

const activeFilter = ref(props.filters.type || 'all');

watch(activeFilter, (newVal) => {
    router.get('/events', { type: newVal === 'all' ? undefined : newVal }, {
        preserveState: true,
        preserveScroll: true,
        only: ['events', 'filters'],
    });
});
</script>

<template>
    <Head title="Upcoming Trips & Events" />

    <div class="bg-background pt-32 pb-24 transition-colors">
        <div class="mx-auto max-w-7xl px-4 md:px-8 lg:px-12">
            <!-- Header section -->
            <div class="mb-12 border-b border-border pb-8">
                <p class="text-sm font-bold uppercase tracking-wider text-amber-500">Kivulini Tours</p>
                <h1 class="mt-2 text-4xl font-black text-foreground sm:text-5xl">
                    Explore Our <span class="text-amber-500">Adventures</span>
                </h1>
                <p class="mt-4 max-w-2xl text-base text-muted-foreground">
                    Discover hikes, road trips, and getaway vacations across East Africa. Filter below to find your next unforgettable journey.
                </p>
            </div>

            <!-- Filter Controls -->
            <div class="mb-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-muted-foreground font-medium">
                    Showing <span class="text-foreground font-semibold">{{ events.total }}</span> adventures
                </div>
                <CategoryFilter v-model="activeFilter" />
            </div>

            <!-- Grid -->
            <div
                v-if="events.data.length > 0"
                class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
            >
                <EventCard
                    v-for="event in events.data"
                    :key="event.id"
                    :event="event"
                />
            </div>

            <!-- Empty state -->
            <div
                v-else
                class="flex min-h-64 flex-col items-center justify-center rounded-2xl border border-border bg-card p-8 text-center"
            >
                <svg class="mx-auto h-12 w-12 text-muted-foreground/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h3 class="mt-4 text-lg font-bold text-foreground">No adventures found</h3>
                <p class="mt-2 text-sm text-muted-foreground">We couldn't find any trips in this category right now. Check back soon!</p>
            </div>

            <!-- Pagination -->
            <div v-if="events.last_page > 1" class="mt-16 flex items-center justify-center gap-1.5">
                <template v-for="link in events.links" :key="link.label">
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
    </div>
</template>
