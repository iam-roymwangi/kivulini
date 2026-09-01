<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, SlidersHorizontal, X } from '@lucide/vue';
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

// Category filter drives a server-side request
const activeFilter = ref(props.filters.type ?? 'all');

watch(activeFilter, (val) => {
    router.get('/events', { type: val === 'all' ? undefined : val }, {
        preserveState: true,
        preserveScroll: true,
        only: ['events', 'filters'],
    });
});

// Client-side search / sort / price
const searchQuery = ref('');
const priceRange = ref<'all' | 'low' | 'mid' | 'high'>('all');
const sortOrder = ref<'date-asc' | 'date-desc' | 'price-asc' | 'price-desc'>('date-asc');
const showMobileFilters = ref(false);

const hasLocalFilters = computed(
    () => searchQuery.value.trim() !== '' || priceRange.value !== 'all' || sortOrder.value !== 'date-asc',
);

function clearFilters() {
    searchQuery.value = '';
    priceRange.value = 'all';
    sortOrder.value = 'date-asc';
}

const priceBuckets = { all: [0, Infinity], low: [0, 3000], mid: [3001, 8000], high: [8001, Infinity] } as const;

const filteredEvents = computed(() => {
    let data = [...props.events.data];

    const q = searchQuery.value.trim().toLowerCase();
    if (q) {
        data = data.filter((e) =>
            e.title.toLowerCase().includes(q) ||
            e.location.toLowerCase().includes(q) ||
            e.summary.toLowerCase().includes(q),
        );
    }

    if (priceRange.value !== 'all') {
        const [min, max] = priceBuckets[priceRange.value];
        data = data.filter((e) => { const p = parseFloat(e.price); return p >= min && p <= max; });
    }

    data.sort((a, b) => {
        switch (sortOrder.value) {
            case 'date-asc':   return new Date(a.start_date).getTime() - new Date(b.start_date).getTime();
            case 'date-desc':  return new Date(b.start_date).getTime() - new Date(a.start_date).getTime();
            case 'price-asc':  return parseFloat(a.price) - parseFloat(b.price);
            case 'price-desc': return parseFloat(b.price) - parseFloat(a.price);
        }
    });

    return data;
});

const typeLabel = computed(() => ({
    all: 'All Adventures',
    cultural_heritage: 'Cultural & Heritage Tours',
    wildlife_safari: 'Wildlife Safaris',
    food_music: 'Food & Music',
    road_trip: 'Road Trips',
    hiking: 'Hiking',
    vacation: 'Vacations',
}[activeFilter.value] ?? 'Adventures'));
</script>

<template>
    <Head>
        <title>Upcoming Trips & Events</title>
        <meta name="description" content="Browse all upcoming hikes, road trips, and vacations in Kenya. Filter by category, price, and date to find your perfect adventure." />
        <meta name="keywords" content="Kenya trips, upcoming events Kenya, hiking, road trips, group travel" />
        <meta property="og:title" content="Upcoming Trips & Events – Kivulini Adventures" />
        <meta property="og:description" content="Browse all upcoming hikes, road trips, and vacations in Kenya." />
        <meta property="og:type" content="website" />
        <link rel="canonical" :href="$page.url" />
    </Head>

    <div class="bg-background pb-24 pt-32 transition-colors">
        <div class="mx-auto max-w-7xl px-4 md:px-8 lg:px-12">

            <!-- Page header -->
            <div class="mb-10 border-b border-border pb-8">
                <h1 class="mt-2 text-4xl font-black text-foreground sm:text-5xl">
                    {{ typeLabel }}
                </h1>
                <p class="mt-3 max-w-2xl text-base text-muted-foreground">
                    Discover hikes, road trips, and getaway vacations across East Africa. Filter below to find your next unforgettable journey.
                </p>
            </div>

            <!-- ── Search & Filter Toolbar ── -->
            <div class="mb-8 space-y-4">
                <!-- Mobile filter toggle -->
                <div class="flex items-center justify-between sm:hidden">
                    <p class="text-sm text-muted-foreground">
                        <span class="font-semibold text-foreground">{{ filteredEvents.length }}</span> of {{ events.total }} trips
                    </p>
                    <button
                        type="button"
                        class="flex items-center gap-2 rounded-xl border border-border bg-card px-3 py-2 text-sm font-medium text-muted-foreground transition hover:border-amber-400 hover:text-amber-500"
                        :class="showMobileFilters || hasLocalFilters ? 'border-amber-400 text-amber-500' : ''"
                        @click="showMobileFilters = !showMobileFilters"
                    >
                        <SlidersHorizontal class="h-4 w-4" />
                        Filters
                        <span v-if="hasLocalFilters" class="h-2 w-2 rounded-full bg-amber-400" />
                    </button>
                </div>

                <!-- Search -->
                <div class="relative">
                    <Search class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <input
                        v-model="searchQuery"
                        type="search"
                        placeholder="Search trips, locations..."
                        class="w-full rounded-2xl border border-border bg-card py-3 pl-11 pr-10 text-sm text-foreground placeholder:text-muted-foreground transition focus:border-amber-400 focus:outline-none"
                    />
                    <button
                        v-if="searchQuery"
                        type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full p-1 text-muted-foreground hover:text-foreground"
                        @click="searchQuery = ''"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <!-- Filter pills (desktop always, mobile toggle) -->
                <div
                    class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center"
                    :class="showMobileFilters ? 'flex' : 'hidden sm:flex'"
                >
                    <CategoryFilter v-model="activeFilter" />

                    <div class="hidden h-5 w-px bg-border sm:block" />

                    <!-- Price -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Price:</span>
                        <button
                            v-for="opt in [
                                { value: 'all', label: 'Any' },
                                { value: 'low', label: 'Under KES 3k' },
                                { value: 'mid', label: 'KES 3–8k' },
                                { value: 'high', label: 'Over KES 8k' },
                            ]"
                            :key="opt.value"
                            type="button"
                            class="rounded-full border px-3 py-1.5 text-xs font-semibold transition-all"
                            :class="priceRange === opt.value
                                ? 'border-amber-400 bg-amber-400/10 text-amber-500'
                                : 'border-border text-muted-foreground hover:border-amber-400/50 hover:text-amber-500'"
                            @click="priceRange = opt.value as typeof priceRange"
                        >
                            {{ opt.label }}
                        </button>
                    </div>

                    <div class="hidden h-5 w-px bg-border sm:block" />

                    <!-- Sort -->
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Sort:</span>
                        <select
                            v-model="sortOrder"
                            class="rounded-xl border border-border bg-card px-3 py-1.5 text-xs font-semibold text-foreground focus:border-amber-400 focus:outline-none"
                        >
                            <option value="date-asc">Date: Soonest</option>
                            <option value="date-desc">Date: Latest</option>
                            <option value="price-asc">Price: Low to High</option>
                            <option value="price-desc">Price: High to Low</option>
                        </select>
                    </div>

                    <button
                        v-if="hasLocalFilters"
                        type="button"
                        class="flex items-center gap-1.5 text-xs font-semibold text-red-500 hover:text-red-400"
                        @click="clearFilters"
                    >
                        <X class="h-3.5 w-3.5" />
                        Clear filters
                    </button>
                </div>

                <!-- Results count (desktop) -->
                <p class="hidden text-sm text-muted-foreground sm:block">
                    <span class="font-semibold text-foreground">{{ filteredEvents.length }}</span>
                    {{ filteredEvents.length === 1 ? 'trip' : 'trips' }} found
                    <template v-if="searchQuery"> for "<span class="text-amber-500">{{ searchQuery }}</span>"</template>
                </p>
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
                class="flex min-h-56 flex-col items-center justify-center gap-4 rounded-2xl border border-dashed border-border bg-card"
            >
                <Search class="h-10 w-10 text-muted-foreground/40" />
                <div class="text-center">
                    <p class="font-semibold text-foreground">No trips found</p>
                    <p class="mt-1 text-sm text-muted-foreground">Try adjusting your search or clearing filters.</p>
                </div>
                <button
                    type="button"
                    class="rounded-xl bg-amber-400 px-5 py-2 text-sm font-bold text-slate-900 hover:bg-amber-300"
                    @click="clearFilters"
                >
                    Clear Filters
                </button>
            </div>

            <!-- Pagination (server-side, hidden when local filtering) -->
            <div v-if="events.last_page > 1 && !hasLocalFilters" class="mt-16 flex items-center justify-center gap-1.5">
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
