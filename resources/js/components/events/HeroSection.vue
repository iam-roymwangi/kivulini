<script setup lang="ts">
import CategoryFilter from '@/components/events/CategoryFilter.vue';

withDefaults(defineProps<{
    mediaUrl?: string | null;
    mediaType?: 'image' | 'video';
}>(), {
    mediaUrl: '/assets/images/hero.jpg',
    mediaType: 'image',
});

const activeFilter = defineModel<string>({ default: 'all' });
</script>

<template>
    <section class="relative flex min-h-screen w-full items-center justify-center overflow-hidden">
        <!-- Background media -->
        <div class="absolute inset-0 z-0">
            <video
                v-if="mediaType === 'video' && mediaUrl"
                :src="mediaUrl"
                autoplay
                muted
                loop
                playsinline
                class="h-full w-full object-cover"
            />
            <img
                v-else-if="mediaUrl"
                :src="mediaUrl"
                alt="Hero background"
                class="h-full w-full object-cover"
            />
            <!-- Fallback gradient when no media provided -->
            <div
                v-else
                class="h-full w-full bg-gradient-to-br from-slate-950 via-slate-900 to-amber-950"
            />
            <!-- Dark overlay -->
            <div class="absolute inset-0 bg-slate-950/60" />
        </div>

        <!-- Content -->
        <div class="relative z-10 flex flex-col items-center gap-8 px-4 text-center">
            <slot name="heading">
                <h1 class="max-w-4xl text-5xl font-black tracking-tight text-white sm:text-6xl lg:text-7xl">
                    Life is Short.<br />
                    <span class="text-amber-400">Make it Epic.</span>
                </h1>
            </slot>

            <p class="max-w-xl text-lg text-slate-300">
                Curated road trips and live events for the bold. No boring weekends.
            </p>

            <a
                href="#events"
                class="rounded-full bg-amber-400 px-8 py-4 text-base font-bold text-slate-900 shadow-lg transition-all hover:bg-amber-300 hover:shadow-amber-400/30 hover:shadow-xl active:scale-95"
            >
                Explore Upcoming Trips
            </a>

            <CategoryFilter v-model="activeFilter" />
        </div>

        <!-- Scroll hint -->
        <div class="absolute bottom-8 left-1/2 z-10 -translate-x-1/2 animate-bounce text-slate-400">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </section>
</template>
