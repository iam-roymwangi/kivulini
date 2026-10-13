<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Search, SlidersHorizontal, X, ShoppingBag, Tag } from '@lucide/vue';
import { show as merchandiseShow } from '@/routes/merchandise';
import type { MerchandiseItem, MerchandiseType } from '@/types/merchandise';

interface Paginator {
    data: MerchandiseItem[];
    current_page: number;
    last_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    merchandise: Paginator;
}>();

// ── Filters ──────────────────────────────────────────────────────────────────
const searchQuery = ref('');
const activeType = ref<'all' | MerchandiseType>('all');
const activeColor = ref('all');
const activeSize = ref('all');
const sortOrder = ref<'newest' | 'price-asc' | 'price-desc'>('newest');
const showMobileFilters = ref(false);

const hasActiveFilters = computed(
    () =>
        searchQuery.value.trim() !== '' ||
        activeType.value !== 'all' ||
        activeColor.value !== 'all' ||
        activeSize.value !== 'all' ||
        sortOrder.value !== 'newest',
);

function clearFilters() {
    searchQuery.value = '';
    activeType.value = 'all';
    activeColor.value = 'all';
    activeSize.value = 'all';
    sortOrder.value = 'newest';
}

// Derive unique colors and sizes from all items
const allColors = computed(() => {
    const set = new Set<string>();
    props.merchandise.data.forEach((item) => item.colors.forEach((c) => set.add(c)));
    return Array.from(set).sort();
});

const allSizes = computed(() => {
    const sizeOrder = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'One Size'];
    const set = new Set<string>();
    props.merchandise.data.forEach((item) => item.sizes.forEach((s) => set.add(s)));
    const sorted = Array.from(set).sort((a, b) => {
        const ai = sizeOrder.indexOf(a);
        const bi = sizeOrder.indexOf(b);
        if (ai === -1 && bi === -1) { return a.localeCompare(b); }
        if (ai === -1) { return 1; }
        if (bi === -1) { return -1; }
        return ai - bi;
    });
    return sorted;
});

const typeOptions: { value: 'all' | MerchandiseType; label: string }[] = [
    { value: 'all', label: 'All' },
    { value: 't_shirt', label: 'T-Shirts' },
    { value: 'hoodie', label: 'Hoodies' },
    { value: 'hat', label: 'Hats' },
    { value: 'other', label: 'Other' },
];

const filteredItems = computed(() => {
    let data = [...(props.merchandise.data ?? [])];

    if (activeType.value !== 'all') {
        data = data.filter((item) => item.type === activeType.value);
    }

    const q = searchQuery.value.trim().toLowerCase();
    if (q) {
        data = data.filter(
            (item) =>
                item.name.toLowerCase().includes(q) ||
                (item.description ?? '').toLowerCase().includes(q),
        );
    }

    if (activeColor.value !== 'all') {
        data = data.filter((item) => item.colors.includes(activeColor.value));
    }

    if (activeSize.value !== 'all') {
        data = data.filter((item) => item.sizes.includes(activeSize.value));
    }

    data.sort((a, b) => {
        switch (sortOrder.value) {
            case 'price-asc': return parseFloat(a.price) - parseFloat(b.price);
            case 'price-desc': return parseFloat(b.price) - parseFloat(a.price);
            default: return b.id - a.id; // newest first by ID
        }
    });

    return data;
});
</script>

<template>
    <Head>
        <title>Merchandise – Kivulini Adventures</title>
        <meta name="description" content="Shop Kivulini Adventures merchandise — t-shirts, hoodies, hats and more. Wear your adventures." />
        <meta name="keywords" content="Kivulini Adventures merch, Kenya adventure apparel, hiking t-shirts Kenya, adventure hoodies" />
        <meta property="og:title" content="Merchandise – Kivulini Adventures" />
        <meta property="og:description" content="Shop Kivulini Adventures merchandise — t-shirts, hoodies, hats and more. Wear your adventures." />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="https://kivuliniadventures.com/merchandise" />
        <meta property="og:image" content="https://kivuliniadventures.com/assets/images/merch-hero.webp" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="Merchandise – Kivulini Adventures" />
        <meta name="twitter:description" content="Shop Kivulini Adventures merchandise — t-shirts, hoodies, hats and more." />
        <meta name="twitter:image" content="https://kivuliniadventures.com/assets/images/merch-hero.webp" />
        <link rel="canonical" href="https://kivuliniadventures.com/merchandise" />
    </Head>

    <!-- Hero Banner -->
    <section class="relative overflow-hidden px-4 pb-60 lg:pt-142 pt-82 text-white md:px-8 lg:px-12">
        <!-- Background image -->
        <div class="absolute inset-0">
            <img
                src="/assets/images/merch-hero.webp"
                alt=""
                aria-hidden="true"
                class="h-full w-full object-cover object-center"
            />
            <div class="absolute inset-0 bg-slate-900/70" />
            <div class="absolute inset-0 opacity-20">
                <div class="absolute -left-20 -top-20 h-96 w-96 rounded-full bg-amber-400 blur-3xl" />
                <div class="absolute -bottom-20 -right-20 h-96 w-96 rounded-full bg-amber-500 blur-3xl" />
            </div>
        </div>
        <div class="relative mx-auto max-w-7xl text-center">
            <p class="mb-3 text-sm font-bold uppercase tracking-widest text-amber-400">Shop</p>
            <h1 class="text-4xl font-black sm:text-5xl lg:text-6xl">
                Kivulini <span class="text-amber-400">Merch</span>
            </h1>
            <p class="mx-auto mt-4 max-w-xl text-base text-slate-300">
                Wear your adventures. Quality merch for explorers who live for the journey.
            </p>
        </div>
    </section>

    <!-- Main content -->
    <section class="bg-background px-4 py-14 transition-colors md:px-8 lg:px-12">
        <div class="mx-auto max-w-7xl">

            <!-- Toolbar -->
            <div class="mb-8 space-y-4">
                <!-- Heading + mobile toggle -->
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-2xl font-black text-foreground">
                        <span class="text-amber-500">{{ filteredItems.length }}</span>
                        {{ filteredItems.length === 1 ? 'item' : 'items' }} found
                    </h2>
                    <button
                        type="button"
                        class="flex items-center gap-2 rounded-xl border border-border bg-card px-3 py-2 text-sm font-medium text-muted-foreground transition hover:border-amber-400 hover:text-amber-500 sm:hidden"
                        :class="showMobileFilters ? 'border-amber-400 text-amber-500' : ''"
                        @click="showMobileFilters = !showMobileFilters"
                    >
                        <SlidersHorizontal class="h-4 w-4" />
                        Filters
                        <span v-if="hasActiveFilters" class="flex h-2 w-2 rounded-full bg-amber-400" />
                    </button>
                </div>

                <!-- Search -->
                <div class="relative">
                    <Search class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <input
                        v-model="searchQuery"
                        type="search"
                        placeholder="Search merch..."
                        class="w-full rounded-2xl border border-border bg-card py-3 pl-11 pr-4 text-sm text-foreground placeholder:text-muted-foreground transition focus:border-amber-400 focus:outline-none"
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

                <!-- Filter pills -->
                <div
                    class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center"
                    :class="showMobileFilters ? 'flex' : 'hidden sm:flex'"
                >
                    <!-- Type -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Type:</span>
                        <button
                            v-for="opt in typeOptions"
                            :key="opt.value"
                            type="button"
                            class="rounded-full border px-3 py-1.5 text-xs font-semibold transition-all"
                            :class="activeType === opt.value
                                ? 'border-amber-400 bg-amber-400/10 text-amber-500'
                                : 'border-border text-muted-foreground hover:border-amber-400/50 hover:text-amber-500'"
                            @click="activeType = opt.value"
                        >
                            {{ opt.label }}
                        </button>
                    </div>

                    <div v-if="allSizes.length > 0" class="hidden h-5 w-px bg-border sm:block" />

                    <!-- Size -->
                    <div v-if="allSizes.length > 0" class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Size:</span>
                        <button
                            type="button"
                            class="rounded-full border px-3 py-1.5 text-xs font-semibold transition-all"
                            :class="activeSize === 'all'
                                ? 'border-amber-400 bg-amber-400/10 text-amber-500'
                                : 'border-border text-muted-foreground hover:border-amber-400/50'"
                            @click="activeSize = 'all'"
                        >
                            Any
                        </button>
                        <button
                            v-for="size in allSizes"
                            :key="size"
                            type="button"
                            class="rounded-full border px-3 py-1.5 text-xs font-semibold transition-all"
                            :class="activeSize === size
                                ? 'border-amber-400 bg-amber-400/10 text-amber-500'
                                : 'border-border text-muted-foreground hover:border-amber-400/50'"
                            @click="activeSize = size"
                        >
                            {{ size }}
                        </button>
                    </div>

                    <div v-if="allColors.length > 0" class="hidden h-5 w-px bg-border sm:block" />

                    <!-- Color -->
                    <div v-if="allColors.length > 0" class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Color:</span>
                        <button
                            type="button"
                            class="rounded-full border px-3 py-1.5 text-xs font-semibold transition-all"
                            :class="activeColor === 'all'
                                ? 'border-amber-400 bg-amber-400/10 text-amber-500'
                                : 'border-border text-muted-foreground hover:border-amber-400/50'"
                            @click="activeColor = 'all'"
                        >
                            Any
                        </button>
                        <button
                            v-for="color in allColors"
                            :key="color"
                            type="button"
                            class="rounded-full border px-3 py-1.5 text-xs font-semibold transition-all"
                            :class="activeColor === color
                                ? 'border-amber-400 bg-amber-400/10 text-amber-500'
                                : 'border-border text-muted-foreground hover:border-amber-400/50'"
                            @click="activeColor = color"
                        >
                            {{ color }}
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
                            <option value="newest">Newest</option>
                            <option value="price-asc">Price: Low to High</option>
                            <option value="price-desc">Price: High to Low</option>
                        </select>
                    </div>

                    <!-- Clear -->
                    <button
                        v-if="hasActiveFilters"
                        type="button"
                        class="flex items-center gap-1.5 text-xs font-semibold text-red-500 hover:text-red-400"
                        @click="clearFilters"
                    >
                        <X class="h-3.5 w-3.5" />
                        Clear all
                    </button>
                </div>
            </div>

            <!-- Grid -->
            <div v-if="filteredItems.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <Link
                    v-for="item in filteredItems"
                    :key="item.id"
                    :href="merchandiseShow.url(item.id)"
                    class="group flex flex-col overflow-hidden rounded-2xl border border-border bg-card transition-all duration-300 hover:-translate-y-1 hover:border-amber-400/40 hover:shadow-lg"
                >
                    <!-- Image -->
                    <div class="relative aspect-square overflow-hidden bg-muted">
                        <img
                            v-if="item.cover_image_url"
                            :src="item.cover_image_url"
                            :alt="item.name"
                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                        />
                        <div v-else class="flex h-full w-full items-center justify-center">
                            <ShoppingBag class="h-16 w-16 text-muted-foreground/30" />
                        </div>
                        <!-- Stock badge -->
                        <div
                            v-if="item.stock === 0"
                            class="absolute inset-0 flex items-center justify-center bg-black/50"
                        >
                            <span class="rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white">Sold Out</span>
                        </div>
                        <div v-else-if="item.stock <= 5" class="absolute right-3 top-3">
                            <span class="rounded-full bg-amber-400 px-2 py-0.5 text-xs font-bold text-slate-900">
                                Only {{ item.stock }} left
                            </span>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="flex flex-1 flex-col p-4">
                        <div class="mb-1 flex items-center gap-2">
                            <span class="flex items-center gap-1 rounded-full bg-muted px-2 py-0.5 text-xs font-semibold text-muted-foreground">
                                <Tag class="h-3 w-3" />
                                {{ { t_shirt: 'T-Shirt', hoodie: 'Hoodie', hat: 'Hat', other: 'Other' }[item.type] }}
                            </span>
                        </div>
                        <h3 class="font-bold text-foreground">{{ item.name }}</h3>
                        <p v-if="item.description" class="mt-1 line-clamp-2 text-xs text-muted-foreground">
                            {{ item.description }}
                        </p>

                        <!-- Sizes -->
                        <div v-if="item.sizes.length > 0" class="mt-3 flex flex-wrap gap-1">
                            <span
                                v-for="size in item.sizes"
                                :key="size"
                                class="rounded border border-border px-1.5 py-0.5 text-xs text-muted-foreground"
                            >
                                {{ size }}
                            </span>
                        </div>

                        <!-- Colors -->
                        <div v-if="item.colors.length > 0" class="mt-2 flex flex-wrap gap-1">
                            <span
                                v-for="color in item.colors"
                                :key="color"
                                class="rounded-full border border-border px-2 py-0.5 text-xs text-muted-foreground"
                            >
                                {{ color }}
                            </span>
                        </div>

                        <!-- Price -->
                        <div class="mt-auto pt-4">
                            <p class="text-lg font-black text-foreground">
                                KES {{ Number(item.price).toLocaleString() }}
                            </p>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Empty state -->
            <div
                v-else
                class="flex min-h-56 flex-col items-center justify-center gap-4 rounded-2xl border border-dashed border-border bg-card"
            >
                <ShoppingBag class="h-10 w-10 text-muted-foreground/40" />
                <div class="text-center">
                    <p class="font-semibold text-foreground">No merchandise found</p>
                    <p class="mt-1 text-sm text-muted-foreground">Try adjusting your filters.</p>
                </div>
                <button
                    type="button"
                    class="rounded-xl bg-amber-400 px-5 py-2 text-sm font-bold text-slate-900 hover:bg-amber-300"
                    @click="clearFilters"
                >
                    Clear Filters
                </button>
            </div>

            <!-- Pagination -->
            <div v-if="merchandise.last_page > 1 && !hasActiveFilters" class="mt-12 flex items-center justify-center gap-1.5">
                <template v-for="link in merchandise.links" :key="link.label">
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
