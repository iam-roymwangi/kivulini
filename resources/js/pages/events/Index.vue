<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import CategoryFilter from '@/components/events/CategoryFilter.vue';
import EventCard from '@/components/events/EventCard.vue';
import HeroSection from '@/components/events/HeroSection.vue';
import MasonryGallery from '@/components/events/MasonryGallery.vue';
import type { EventMedia, PlatformEvent } from '@/types';

const props = withDefaults(defineProps<{
    events: PlatformEvent[];
    featuredMedia: EventMedia[];
}>(), {
    events: () => [],
    featuredMedia: () => [],
});

const activeFilter = ref('all');

const filteredEvents = computed(() => {
    if (activeFilter.value === 'all') { return props.events; }
    return props.events.filter((e) => e.type === activeFilter.value);
});
</script>

<template>
    <Head title="Explore Upcoming Trips" />

    <!-- Hero -->
    <HeroSection v-model="activeFilter" />

    <!-- Event grid -->
    <section id="events" class="bg-background px-4 py-16 transition-colors md:px-8 lg:px-12">
        <div class="mx-auto max-w-7xl">
            <!-- Section header -->
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-3xl font-black text-foreground">
                    Upcoming
                    <span class="text-amber-500">
                        {{ activeFilter === 'all' ? 'Events' : activeFilter === 'road_trip' ? 'Road Trips' : 'Vacations' }}
                    </span>
                </h2>
                <!-- Inline filter for the grid section -->
                <CategoryFilter v-model="activeFilter" />
            </div>

            <!-- Grid -->
            <div
                v-if="filteredEvents.length > 0"
                class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
            >
                <EventCard
                    v-for="event in filteredEvents"
                    :key="event.id"
                    :event="event"
                />
            </div>

            <!-- Empty state -->
            <div
                v-else
                class="flex min-h-48 items-center justify-center rounded-2xl border border-border bg-card"
            >
                <p class="text-muted-foreground">No events found for this category yet. Check back soon.</p>
            </div>
        </div>
    </section>

    <!-- Past trips gallery -->
    <section v-if="featuredMedia.length > 0" class="bg-muted px-4 pb-24 transition-colors md:px-8 lg:px-12">
        <div class="mx-auto max-w-7xl pt-16">
            <h2 class="mb-8 text-3xl font-black text-foreground">
                Past <span class="text-amber-500">Trips</span>
            </h2>
            <MasonryGallery :items="featuredMedia" />
        </div>
    </section>
</template>
