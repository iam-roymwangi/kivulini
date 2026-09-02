<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from '@lucide/vue';
import type { PaginationLink, PaginationMeta } from '@/types/ui';

interface PaginatorProps {
    pagination: {
        data?: unknown[];
        links?: PaginationLink[];
        meta?: PaginationMeta;
        current_page?: number;
        last_page?: number;
        total?: number;
        from?: number | null;
        to?: number | null;
        per_page?: number;
    };
    itemName?: string;
}

const props = withDefaults(defineProps<PaginatorProps>(), {
    itemName: 'pictures',
});

const currentPage = computed(() => props.pagination.meta?.current_page ?? props.pagination.current_page ?? 1);
const lastPage = computed(() => props.pagination.meta?.last_page ?? props.pagination.last_page ?? 1);
const total = computed(() => props.pagination.meta?.total ?? props.pagination.total ?? 0);
const from = computed(() => props.pagination.meta?.from ?? props.pagination.from ?? (total.value > 0 ? 1 : 0));
const to = computed(() => props.pagination.meta?.to ?? props.pagination.to ?? 0);
const allLinks = computed(() => props.pagination.meta?.links ?? props.pagination.links ?? []);

const prevLink = computed(() => {
    return allLinks.value.find((l) =>
        l.label.includes('Previous') || l.label.includes('&laquo;') || l.label.includes('«')
    ) ?? null;
});

const nextLink = computed(() => {
    return allLinks.value.find((l) =>
        l.label.includes('Next') || l.label.includes('&raquo;') || l.label.includes('»')
    ) ?? null;
});

const firstPageLink = computed(() => {
    return allLinks.value.find((l) => l.label === '1' || l.label === '&laquo; 1') ?? null;
});

const lastPageLink = computed(() => {
    return allLinks.value.find((l) => l.label === String(lastPage.value)) ?? null;
});

const pageLinks = computed(() => {
    return allLinks.value.filter((l) => {
        const isPrev = l.label.includes('Previous') || l.label.includes('&laquo;') || l.label.includes('«');
        const isNext = l.label.includes('Next') || l.label.includes('&raquo;') || l.label.includes('»');
        return !isPrev && !isNext;
    });
});

function cleanLabel(label: string): string {
    return label
        .replace(/&laquo;?/g, '')
        .replace(/&raquo;?/g, '')
        .replace(/&hellip;?/g, '...')
        .trim();
}
</script>

<template>
    <nav
        v-if="lastPage > 1 || total > 0"
        aria-label="Gallery pagination navigation"
        class="mt-12 flex flex-col items-center justify-between gap-4 rounded-3xl border border-border/80 bg-card/80 p-4 shadow-xl backdrop-blur-xl sm:flex-row sm:px-6 sm:py-4"
    >
        <!-- Info text / stats -->
        <div class="flex items-center gap-3 text-xs sm:text-sm">
            <span class="rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 font-semibold text-amber-500">
                Page {{ currentPage }} of {{ lastPage }}
            </span>
            <span v-if="total > 0" class="text-muted-foreground">
                Showing <strong class="font-bold text-foreground">{{ from }}–{{ to }}</strong> of <strong class="font-bold text-foreground">{{ total }}</strong> {{ itemName }}
            </span>
        </div>

        <!-- Navigation Buttons -->
        <div v-if="lastPage > 1" class="flex items-center gap-1.5 sm:gap-2">
            <!-- First Page Button (Quick jump) -->
            <Link
                v-if="currentPage > 2 && firstPageLink?.url"
                :href="firstPageLink.url"
                aria-label="Go to first page"
                class="flex h-9 w-9 items-center justify-center rounded-xl border border-border bg-card text-muted-foreground transition-all duration-200 hover:border-amber-400 hover:bg-amber-400/10 hover:text-amber-500 hover:scale-105 active:scale-95"
            >
                <ChevronsLeft class="h-4 w-4" />
            </Link>

            <!-- Previous Page Button -->
            <Link
                v-if="prevLink?.url"
                :href="prevLink.url"
                aria-label="Go to previous page"
                class="flex h-9 items-center gap-1.5 rounded-xl border border-border bg-card px-3 py-1.5 text-xs font-semibold text-muted-foreground transition-all duration-200 hover:border-amber-400 hover:bg-amber-400/10 hover:text-amber-500 hover:scale-105 active:scale-95 sm:text-sm"
            >
                <ChevronLeft class="h-4 w-4" />
                <span class="hidden sm:inline">Prev</span>
            </Link>
            <button
                v-else
                disabled
                aria-disabled="true"
                aria-label="Previous page (disabled)"
                class="flex h-9 items-center gap-1.5 rounded-xl border border-border/40 bg-card/40 px-3 py-1.5 text-xs font-semibold text-muted-foreground/30 cursor-not-allowed sm:text-sm"
            >
                <ChevronLeft class="h-4 w-4 opacity-40" />
                <span class="hidden sm:inline">Prev</span>
            </button>

            <!-- Page Number Pills -->
            <div class="flex items-center gap-1 sm:gap-1.5">
                <template v-for="(link, idx) in pageLinks" :key="idx">
                    <!-- Numeric link -->
                    <Link
                        v-if="link.url && !link.active"
                        :href="link.url"
                        :aria-label="`Go to page ${cleanLabel(link.label)}`"
                        class="flex h-9 min-w-9 items-center justify-center rounded-xl border border-border bg-card px-2.5 text-xs font-semibold text-muted-foreground transition-all duration-200 hover:border-amber-400 hover:bg-amber-400/10 hover:text-amber-500 hover:scale-105 active:scale-95 sm:text-sm"
                    >
                        {{ cleanLabel(link.label) }}
                    </Link>

                    <!-- Active Page -->
                    <span
                        v-else-if="link.active"
                        aria-current="page"
                        class="flex h-9 min-w-9 items-center justify-center rounded-xl border border-amber-400 bg-gradient-to-r from-amber-400 to-amber-500 px-2.5 text-xs font-black text-slate-950 shadow-md shadow-amber-500/20 ring-2 ring-amber-400/30 sm:text-sm"
                    >
                        {{ cleanLabel(link.label) }}
                    </span>

                    <!-- Ellipsis (...) -->
                    <span
                        v-else
                        class="flex h-9 w-7 items-center justify-center text-xs font-bold text-muted-foreground/40 sm:text-sm"
                    >
                        ...
                    </span>
                </template>
            </div>

            <!-- Next Page Button -->
            <Link
                v-if="nextLink?.url"
                :href="nextLink.url"
                aria-label="Go to next page"
                class="flex h-9 items-center gap-1.5 rounded-xl border border-border bg-card px-3 py-1.5 text-xs font-semibold text-muted-foreground transition-all duration-200 hover:border-amber-400 hover:bg-amber-400/10 hover:text-amber-500 hover:scale-105 active:scale-95 sm:text-sm"
            >
                <span class="hidden sm:inline">Next</span>
                <ChevronRight class="h-4 w-4" />
            </Link>
            <button
                v-else
                disabled
                aria-disabled="true"
                aria-label="Next page (disabled)"
                class="flex h-9 items-center gap-1.5 rounded-xl border border-border/40 bg-card/40 px-3 py-1.5 text-xs font-semibold text-muted-foreground/30 cursor-not-allowed sm:text-sm"
            >
                <span class="hidden sm:inline">Next</span>
                <ChevronRight class="h-4 w-4 opacity-40" />
            </button>

            <!-- Last Page Button (Quick jump) -->
            <Link
                v-if="currentPage < lastPage - 1 && lastPageLink?.url"
                :href="lastPageLink.url"
                aria-label="Go to last page"
                class="flex h-9 w-9 items-center justify-center rounded-xl border border-border bg-card text-muted-foreground transition-all duration-200 hover:border-amber-400 hover:bg-amber-400/10 hover:text-amber-500 hover:scale-105 active:scale-95"
            >
                <ChevronsRight class="h-4 w-4" />
            </Link>
        </div>
    </nav>
</template>
