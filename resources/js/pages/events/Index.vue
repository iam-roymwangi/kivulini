<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import CategoryFilter from '@/components/events/CategoryFilter.vue';
import EventCard from '@/components/events/EventCard.vue';
import HeroSection from '@/components/events/HeroSection.vue';
import MasonryGallery from '@/components/events/MasonryGallery.vue';
import type { EventMedia, PlatformEvent } from '@/types';

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
    pastEventsMedia: Paginator<EventMedia>;
}>();

const activeFilter = ref('all');

const filteredEvents = computed(() => {
    const data = props.events?.data || [];
    if (activeFilter.value === 'all') { return data; }
    return data.filter((e) => e.type === activeFilter.value);
});

const testimonials = [
    {
        name: 'Wanjiku N.',
        location: 'Nairobi, Kenya',
        initials: 'WN',
        text: 'The Mt. Longonot hike was perfectly organized! The guides were very professional and the bonfire at night was magical.',
    },
    {
        name: 'David K.',
        location: 'Kisumu, Kenya',
        initials: 'DK',
        text: 'Kivulini makes booking trips so simple. The digital consent and STK push payments are seamless!',
    },
    {
        name: 'Sarah M.',
        location: 'Mombasa, Kenya',
        initials: 'SM',
        text: 'An absolute 10/10 experience! I met amazing people and saw the most beautiful sunsets on the Rift Valley road trip.',
    },
    {
        name: 'John O.',
        location: 'Eldoret, Kenya',
        initials: 'JO',
        text: 'Highly recommend Kivulini for anyone looking to explore Kenya in groups. Great vibe, safe environment!',
    },
];
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

            <!-- Events Pagination -->
            <div v-if="events.last_page > 1" class="mt-12 flex items-center justify-center gap-1.5">
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
    </section>

    <!-- Testimonials Section -->
    <section class="bg-background px-4 py-20 transition-colors md:px-8 lg:px-12 border-t border-border">
        <div class="mx-auto max-w-7xl">
            <div class="mb-12 text-center">
                <p class="text-sm font-bold uppercase tracking-wider text-amber-500">Traveler Reviews</p>
                <h2 class="mt-2 text-3xl font-black text-foreground sm:text-4xl">
                    What Our Travelers <span class="text-amber-500">Say</span>
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-base text-muted-foreground">
                    Don't just take our word for it. Here is the feedback from our community of adventurers who have toured with us.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div 
                    v-for="(testimonial, idx) in testimonials" 
                    :key="idx"
                    class="group flex flex-col justify-between rounded-2xl border border-border bg-card p-6 shadow-xs transition-all duration-300 hover:-translate-y-1.5 hover:border-amber-400/30 hover:shadow-lg"
                >
                    <div>
                        <!-- Rating Stars -->
                        <div class="mb-4 flex items-center gap-1">
                            <svg 
                                v-for="star in 5" 
                                :key="star"
                                class="h-4 w-4 text-amber-400" 
                                fill="currentColor" 
                                viewBox="0 0 20 20"
                            >
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                        <!-- Quote text -->
                        <p class="text-sm leading-relaxed text-foreground/80 italic">
                            "{{ testimonial.text }}"
                        </p>
                    </div>
                    
                    <!-- User info -->
                    <div class="mt-6 flex items-center gap-3 border-t border-border pt-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-400/10 text-sm font-bold text-amber-500 dark:bg-amber-400/20">
                            {{ testimonial.initials }}
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-foreground">{{ testimonial.name }}</h4>
                            <p class="text-xs text-muted-foreground">{{ testimonial.location }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Past trips gallery -->
    <section v-if="pastEventsMedia.data.length > 0" class="bg-muted px-4 pb-24 transition-colors md:px-8 lg:px-12">
        <div class="mx-auto max-w-7xl pt-16">
            <h2 class="mb-8 text-3xl font-black text-foreground">
                Past <span class="text-amber-500">Trips</span>
            </h2>
            <MasonryGallery :items="pastEventsMedia.data" />

            <!-- Gallery Pagination -->
            <div v-if="pastEventsMedia.last_page > 1" class="mt-12 flex items-center justify-center gap-1.5">
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
    </section>
</template>
