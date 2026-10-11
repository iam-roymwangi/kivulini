<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ShoppingBag, ArrowRight } from '@lucide/vue';
import { index as merchandiseIndex, show as merchandiseShow } from '@/routes/merchandise';
import type { MerchandiseItem } from '@/types/merchandise';

defineProps<{
    items: MerchandiseItem[];
}>();

const typeLabel: Record<string, string> = {
    t_shirt: 'T-Shirt',
    hoodie: 'Hoodie',
    hat: 'Hat',
    other: 'Other',
};
</script>

<template>
    <section class="border-t border-border bg-background px-4 py-20 transition-colors md:px-8 lg:px-12">
        <div class="mx-auto max-w-7xl">
            <!-- Header -->
            <div class="mb-10 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-wider text-amber-500">Shop</p>
                    <h2 class="mt-2 text-3xl font-black text-foreground sm:text-4xl">
                        Wear the <span class="text-amber-500">Adventure</span>
                    </h2>
                    <p class="mt-3 max-w-lg text-sm text-muted-foreground">
                        Quality gear and apparel for explorers who live for the journey.
                    </p>
                </div>
                <Link
                    :href="merchandiseIndex.url()"
                    class="inline-flex shrink-0 items-center gap-2 rounded-full border border-border bg-card px-5 py-2.5 text-sm font-bold text-foreground transition hover:border-amber-400 hover:text-amber-500 self-start sm:self-auto"
                >
                    View all merch
                    <ArrowRight class="h-4 w-4" />
                </Link>
            </div>

            <!-- Grid -->
            <div v-if="items.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <Link
                    v-for="item in items"
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
                            loading="lazy"
                        />
                        <div v-else class="flex h-full w-full items-center justify-center">
                            <ShoppingBag class="h-16 w-16 text-muted-foreground/20" />
                        </div>

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

                        <!-- Type badge -->
                        <div class="absolute left-3 top-3">
                            <span class="rounded-full border border-border/60 bg-card/80 px-2.5 py-0.5 text-xs font-semibold text-foreground backdrop-blur-sm">
                                {{ typeLabel[item.type] ?? item.type }}
                            </span>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="flex flex-1 flex-col p-4">
                        <h3 class="font-bold text-foreground line-clamp-1">{{ item.name }}</h3>
                        <p v-if="item.description" class="mt-1 line-clamp-2 text-xs text-muted-foreground">
                            {{ item.description }}
                        </p>

                        <!-- Sizes -->
                        <div v-if="item.sizes.length > 0" class="mt-3 flex flex-wrap gap-1">
                            <span
                                v-for="size in item.sizes.slice(0, 5)"
                                :key="size"
                                class="rounded border border-border px-1.5 py-0.5 text-xs text-muted-foreground"
                            >
                                {{ size }}
                            </span>
                            <span v-if="item.sizes.length > 5" class="rounded border border-border px-1.5 py-0.5 text-xs text-muted-foreground">
                                +{{ item.sizes.length - 5 }}
                            </span>
                        </div>

                        <div class="mt-auto pt-4 flex items-center justify-between">
                            <p class="text-lg font-black text-foreground">
                                KES {{ Number(item.price).toLocaleString() }}
                            </p>
                            <span class="text-xs font-semibold text-amber-500 opacity-0 transition-opacity group-hover:opacity-100">
                                View details
                            </span>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Empty state -->
            <div v-else class="flex min-h-48 flex-col items-center justify-center rounded-2xl border border-dashed border-border bg-card text-center p-8">
                <ShoppingBag class="h-10 w-10 text-muted-foreground/30 mb-3" />
                <p class="font-semibold text-foreground">Merch coming soon</p>
                <p class="mt-1 text-sm text-muted-foreground">New apparel and gear is on the way.</p>
            </div>
        </div>
    </section>
</template>
