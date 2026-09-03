<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Search, SlidersHorizontal, X } from '@lucide/vue';
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

// ── Filters ──────────────────────────────────────────────────────────────────
const activeFilter = ref('all');   // category: all | event | road_trip | vacation
const searchQuery = ref('');
const priceRange = ref<'all' | 'low' | 'mid' | 'high'>('all');
const sortOrder = ref<'date-asc' | 'date-desc' | 'price-asc' | 'price-desc'>('date-asc');
const showMobileFilters = ref(false);

const hasActiveFilters = computed(
    () => searchQuery.value.trim() !== '' || priceRange.value !== 'all' || sortOrder.value !== 'date-asc',
);

function clearFilters() {
    searchQuery.value = '';
    priceRange.value = 'all';
    sortOrder.value = 'date-asc';
    activeFilter.value = 'all';
}

// Price buckets in KES
const priceBuckets = {
    all: [0, Infinity],
    low: [0, 3000],
    mid: [3001, 8000],
    high: [8001, Infinity],
} as const;

const filteredEvents = computed(() => {
    let data = [...(props.events?.data ?? [])];

    // Category
    if (activeFilter.value !== 'all') {
        data = data.filter((e) => e.type === activeFilter.value);
    }

    // Search (title + location)
    const q = searchQuery.value.trim().toLowerCase();
    if (q) {
        data = data.filter(
            (e) =>
                e.title.toLowerCase().includes(q) ||
                e.location.toLowerCase().includes(q) ||
                e.summary.toLowerCase().includes(q),
        );
    }

    // Price range
    if (priceRange.value !== 'all') {
        const [min, max] = priceBuckets[priceRange.value];
        data = data.filter((e) => {
            const p = parseFloat(e.price);
            return p >= min && p <= max;
        });
    }

    // Sort
    data.sort((a, b) => {
        switch (sortOrder.value) {
            case 'date-asc': return new Date(a.start_date).getTime() - new Date(b.start_date).getTime();
            case 'date-desc': return new Date(b.start_date).getTime() - new Date(a.start_date).getTime();
            case 'price-asc': return parseFloat(a.price) - parseFloat(b.price);
            case 'price-desc': return parseFloat(b.price) - parseFloat(a.price);
        }
    });

    return data;
});

const activeFilterLabel = computed(() => {
    const map: Record<string, string> = {
        all: 'Events',
        cultural_heritage: 'Cultural & Heritage Tours',
        wildlife_safari: 'Wildlife Safaris',
        food_music: 'Food & Music',
        road_trip: 'Road Trips',
        hiking: 'Hiking',
        vacation: 'Vacations',
    };
    return map[activeFilter.value] ?? 'Events';
});

const testimonials = [
    { name: 'Wanjiku N.', location: 'Nairobi, Kenya', initials: 'WN', text: 'The Mt. Longonot hike was perfectly organized! The guides were very professional and the bonfire at night was magical.' },
    { name: 'David K.', location: 'Kisumu, Kenya', initials: 'DK', text: 'Kivulini makes booking trips so simple. The digital consent and STK push payments are seamless!' },
    { name: 'Sarah M.', location: 'Mombasa, Kenya', initials: 'SM', text: 'An absolute 10/10 experience! I met amazing people and saw the most beautiful sunsets on the Rift Valley road trip.' },
    { name: 'John O.', location: 'Eldoret, Kenya', initials: 'JO', text: 'Highly recommend Kivulini for anyone looking to explore Kenya in groups. Great vibe, safe environment!' },
];
</script>

<template>

    <Head>
        <title>Explore Upcoming Trips & Adventures in Kenya</title>
        <meta name="description"
            content="Discover and book curated hikes, road trips, and getaway vacations across Kenya and East Africa with Kivulini Adventures." />
        <meta name="keywords"
            content="Kenya adventures, hiking Kenya, road trips East Africa, group travel Kenya, Kivulini" />
        <meta property="og:title" content="Kivulini Adventures – Explore Upcoming Trips" />
        <meta property="og:description"
            content="Discover and book curated hikes, road trips, and getaway vacations across Kenya and East Africa." />
        <meta property="og:type" content="website" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="Kivulini Adventures – Explore Upcoming Trips" />
        <meta name="twitter:description"
            content="Discover and book curated hikes, road trips, and getaway vacations across Kenya and East Africa." />
    </Head>

    <!-- Hero -->
    <HeroSection v-model="activeFilter" />

    <!-- Event grid -->
    <section id="events" class="bg-background px-4 py-16 transition-colors md:px-8 lg:px-12">
        <div class="mx-auto max-w-7xl">

            <!-- ── Search & Filter Toolbar ── -->
            <div class="mb-8 space-y-4">
                <!-- Row 1: heading + mobile filter toggle -->
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-3xl font-black text-foreground">
                        Upcoming
                        <span class="text-amber-500">{{ activeFilterLabel }}</span>
                    </h2>
                    <button type="button"
                        class="flex items-center gap-2 rounded-xl border border-border bg-card px-3 py-2 text-sm font-medium text-muted-foreground transition hover:border-amber-400 hover:text-amber-500 sm:hidden"
                        :class="showMobileFilters ? 'border-amber-400 text-amber-500' : ''"
                        @click="showMobileFilters = !showMobileFilters">
                        <SlidersHorizontal class="h-4 w-4" />
                        Filters
                        <span v-if="hasActiveFilters" class="flex h-2 w-2 rounded-full bg-amber-400" />
                    </button>
                </div>

                <!-- Row 2: search bar (always visible) -->
                <div class="relative">
                    <Search
                        class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <input v-model="searchQuery" type="search" placeholder="Search events, locations..."
                        class="w-full rounded-2xl border border-border bg-card py-3 pl-11 pr-4 text-sm text-foreground placeholder:text-muted-foreground transition focus:border-amber-400 focus:outline-none" />
                    <button v-if="searchQuery" type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full p-1 text-muted-foreground hover:text-foreground"
                        aria-label="Clear search" @click="searchQuery = ''">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <!-- Row 3: filter pills (desktop always, mobile collapsible) -->
                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center"
                    :class="showMobileFilters ? 'flex' : 'hidden sm:flex'">
                    <!-- Category -->
                    <CategoryFilter v-model="activeFilter" />

                    <div class="hidden h-5 w-px bg-border sm:block" />

                    <!-- Price range -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Price:</span>
                        <button v-for="opt in [
                            { value: 'all', label: 'Any' },
                            { value: 'low', label: 'Under KES 3k' },
                            { value: 'mid', label: 'KES 3–8k' },
                            { value: 'high', label: 'Over KES 8k' },
                        ]" :key="opt.value" type="button"
                            class="rounded-full border px-3 py-1.5 text-xs font-semibold transition-all" :class="priceRange === opt.value
                                ? 'border-amber-400 bg-amber-400/10 text-amber-500'
                                : 'border-border text-muted-foreground hover:border-amber-400/50 hover:text-amber-500'"
                            @click="priceRange = opt.value as typeof priceRange">
                            {{ opt.label }}
                        </button>
                    </div>

                    <div class="hidden h-5 w-px bg-border sm:block" />

                    <!-- Sort -->
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Sort:</span>
                        <select v-model="sortOrder"
                            class="rounded-xl border border-border bg-card px-3 py-1.5 text-xs font-semibold text-foreground focus:border-amber-400 focus:outline-none">
                            <option value="date-asc">Date: Soonest</option>
                            <option value="date-desc">Date: Latest</option>
                            <option value="price-asc">Price: Low to High</option>
                            <option value="price-desc">Price: High to Low</option>
                        </select>
                    </div>

                    <!-- Clear all (only when filters are active) -->
                    <button v-if="hasActiveFilters" type="button"
                        class="flex items-center gap-1.5 text-xs font-semibold text-red-500 hover:text-red-400"
                        @click="clearFilters">
                        <X class="h-3.5 w-3.5" />
                        Clear all
                    </button>
                </div>

                <!-- Results count -->
                <p class="text-sm text-muted-foreground">
                    <span class="font-semibold text-foreground">{{ filteredEvents.length }}</span>
                    {{ filteredEvents.length === 1 ? 'event' : 'events' }} found
                    <span v-if="searchQuery"> for "<span class="text-amber-500">{{ searchQuery }}</span>"</span>
                </p>
            </div>

            <!-- Grid -->
            <div v-if="filteredEvents.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <EventCard v-for="event in filteredEvents" :key="event.id" :event="event" />
            </div>

            <!-- Empty state -->
            <div v-else
                class="flex min-h-56 flex-col items-center justify-center gap-4 rounded-2xl border border-dashed border-border bg-card">
                <Search class="h-10 w-10 text-muted-foreground/40" />
                <div class="text-center">
                    <p class="font-semibold text-foreground">No events found</p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Try adjusting your search or clearing the filters.
                    </p>
                </div>
                <button type="button"
                    class="rounded-xl bg-amber-400 px-5 py-2 text-sm font-bold text-slate-900 hover:bg-amber-300"
                    @click="clearFilters">
                    Clear Filters
                </button>
            </div>

            <!-- Pagination (only when not filtering client-side) -->
            <div v-if="events.last_page > 1 && !hasActiveFilters && activeFilter === 'all'"
                class="mt-12 flex items-center justify-center gap-1.5">
                <template v-for="link in events.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url"
                        class="rounded-xl border border-border bg-card px-4 py-2 text-sm font-semibold transition-all hover:border-amber-400 hover:text-amber-500"
                        :class="link.active ? 'border-amber-400 bg-amber-400/10 text-amber-500 font-bold' : 'text-muted-foreground'"
                        preserve-scroll v-html="link.label" />
                    <span v-else
                        class="cursor-default rounded-xl border border-border/40 bg-card/40 px-4 py-2 text-sm text-muted-foreground/45"
                        v-html="link.label" />
                </template>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="bg-background px-4 py-20 transition-colors md:px-8 lg:px-12 border-t border-border">
        <div class="mx-auto max-w-7xl">
            <div class="mb-12 text-center">
                <p class="text-sm font-bold uppercase tracking-wider text-amber-500">Testimonials</p>
                <h2 class="mt-2 text-3xl font-black text-foreground sm:text-4xl">
                    What Our Clients <span class="text-amber-500">Say</span>
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-base text-muted-foreground">
                    Don't just take our word for it. Here is the feedback from our community of adventurers who have
                    toured
                    with us.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="(testimonial, idx) in testimonials" :key="idx"
                    class="group flex flex-col justify-between rounded-2xl border border-border bg-card p-6 shadow-xs transition-all duration-300 hover:-translate-y-1.5 hover:border-amber-400/30 hover:shadow-lg">
                    <div>
                        <!-- Rating Stars -->
                        <div class="mb-4 flex items-center gap-1">
                            <svg v-for="star in 5" :key="star" class="h-4 w-4 text-amber-400" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                        <!-- Quote text -->
                        <p class="text-sm leading-relaxed text-foreground/80 italic">
                            "{{ testimonial.text }}"
                        </p>
                    </div>

                    <!-- User info -->
                    <div class="mt-6 flex items-center gap-3 border-t border-border pt-4">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-400/10 text-sm font-bold text-amber-500 dark:bg-amber-400/20">
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
                    <Link v-if="link.url" :href="link.url"
                        class="rounded-xl border border-border bg-card px-4 py-2 text-sm font-semibold transition-all hover:border-amber-400 hover:text-amber-500"
                        :class="link.active ? 'border-amber-400 bg-amber-400/10 text-amber-500 font-bold' : 'text-muted-foreground'"
                        preserve-scroll v-html="link.label" />
                    <span v-else
                        class="cursor-default rounded-xl border border-border/40 bg-card/40 px-4 py-2 text-sm text-muted-foreground/45"
                        v-html="link.label" />
                </template>
            </div>
        </div>
    </section>
</template>
