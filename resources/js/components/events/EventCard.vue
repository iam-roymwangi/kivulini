<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { CalendarDays, MapPin } from '@lucide/vue';
import { show as showEvent } from '@/routes/events';
import type { PlatformEvent } from '@/types';

const props = defineProps<{
    event: PlatformEvent;
}>();

const isSoldOut = computed(() => props.event.available_slots === 0);

const formattedPrice = computed(() =>
    new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', maximumFractionDigits: 0 }).format(
        parseFloat(props.event.price),
    ),
);

const formattedDate = computed(() =>
    new Date(props.event.start_date).toLocaleDateString('en-KE', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }),
);

const eventUrl = computed(() => showEvent.url(props.event.slug));
</script>

<template>
    <article class="group relative flex flex-col overflow-hidden rounded-2xl bg-card shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
        <!-- Main clickable area -->
        <Link :href="eventUrl" class="flex flex-1 flex-col">
            <!-- Cover image -->
            <div class="relative aspect-[4/3] overflow-hidden bg-slate-800">
                <img
                    v-if="event.cover_image_url"
                    :src="event.cover_image_url"
                    :alt="event.title"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    loading="lazy"
                />
                <div
                    v-else
                    class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-800 to-slate-700"
                >
                    <span class="text-4xl font-black text-slate-600">{{ event.title[0] }}</span>
                </div>

                <!-- Price badge -->
                <div class="absolute left-3 top-3">
                    <span class="rounded-full bg-amber-400 px-3 py-1 text-xs font-bold text-slate-900 shadow">
                        {{ formattedPrice }}
                    </span>
                </div>

                <!-- Sold out overlay -->
                <div
                    v-if="isSoldOut"
                    class="absolute inset-0 flex items-center justify-center bg-slate-950/70"
                >
                    <span class="rounded-full border-2 border-red-500 px-4 py-1.5 text-sm font-bold uppercase tracking-widest text-red-500">
                        Sold Out
                    </span>
                </div>
            </div>

            <!-- Card body -->
            <div class="flex flex-1 flex-col gap-3 p-4">
                <h3 class="line-clamp-2 text-base font-bold leading-snug text-card-foreground">
                    {{ event.title }}
                </h3>

                <!-- Meta row -->
                <div class="flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                    <span class="flex items-center gap-1">
                        <MapPin class="h-3.5 w-3.5 text-amber-400" aria-hidden="true" />
                        {{ event.location }}
                    </span>
                    <span class="flex items-center gap-1">
                        <CalendarDays class="h-3.5 w-3.5 text-amber-400" aria-hidden="true" />
                        {{ formattedDate }}
                    </span>
                </div>

                <!-- Seat counter -->
                <p v-if="!isSoldOut" class="text-xs text-muted-foreground">
                    {{ event.available_slots }} seat{{ event.available_slots === 1 ? '' : 's' }} left
                </p>
            </div>
        </Link>

        <!-- CTA (outside the Link to avoid nested interactive elements) -->
        <div class="p-4 pt-0">
            <Link
                v-if="!isSoldOut"
                :href="eventUrl"
                class="block w-full rounded-xl bg-amber-400 py-2.5 text-center text-sm font-bold text-slate-900 transition-colors hover:bg-amber-300 active:scale-95"
            >
                Book Seat
            </Link>
            <button
                v-else
                type="button"
                disabled
                class="block w-full cursor-not-allowed rounded-xl bg-slate-200 py-2.5 text-center text-sm font-bold text-slate-400 dark:bg-slate-700 dark:text-slate-500"
            >
                Book Seat
            </button>
        </div>
    </article>
</template>
