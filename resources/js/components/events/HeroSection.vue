<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import CategoryFilter from '@/components/events/CategoryFilter.vue';

const activeFilter = defineModel<string>({ default: 'all' });

const slides = [
    { src: '/assets/images/sgr_terminal.webp', caption: 'Life is Short.', accent: 'Make it Epic.', sub: 'Curated road trips and live events for the bold.' },
    { src: '/assets/images/sgr_terminal1.webp', caption: 'No Tourist Traps.', accent: 'Real Adventures.', sub: 'Handpicked locations, unforgettable crew.' },
    { src: '/assets/images/jg_hike.webp', caption: 'Your Weekend,', accent: 'Reimagined.', sub: 'From Naivasha to the coast — we handle everything.' },
    { src: '/assets/images/event-4.jpg', caption: 'The Crew.', accent: 'The Experience.', sub: 'People who get it. Places that never disappoint.' },
    { src: '/assets/images/event-5.jpg', caption: 'Stories Worth', accent: 'Telling.', sub: 'Book your seat and become part of the story.' },
];

const currentIndex = ref(0);
let intervalId: ReturnType<typeof setInterval> | null = null;

function goTo(index: number) {
    currentIndex.value = (index + slides.length) % slides.length;
}

function startAutoplay() {
    intervalId = setInterval(() => goTo(currentIndex.value + 1), 5500);
}

function stopAutoplay() {
    if (intervalId) { clearInterval(intervalId); intervalId = null; }
}

onMounted(startAutoplay);
onBeforeUnmount(stopAutoplay);

const current = computed(() => slides[currentIndex.value]);
</script>

<template>
    <section
        class="relative w-full items-center justify-center overflow-hidden bg-slate-950"
        style="min-height: 100dvh; display: flex;"
        @mouseenter="stopAutoplay"
        @mouseleave="startAutoplay"
    >
        <!-- Slide backgrounds -->
        <div class="absolute inset-0" style="position: absolute; inset: 0;">
            <div
                v-for="(slide, i) in slides"
                :key="slide.src"
                style="position: absolute; inset: 0; transition: opacity 1s;"
                :style="{ opacity: i === currentIndex ? '1' : '0' }"
            >
                <img :src="slide.src" :alt="slide.caption" style="width: 100%; height: 100%; object-fit: cover; display: block;" :loading="i === 0 ? 'eager' : 'lazy'" />
            </div>
            <!-- Gradient -->
            <div style="position: absolute; inset: 0; background: linear-gradient(to top, #020617 0%, rgba(2,6,23,0.55) 50%, rgba(2,6,23,0.15) 100%);" />
        </div>

        <!-- Content -->
        <div class="relative z-10 flex flex-col items-center gap-6 px-4 text-center">
            <div class="flex min-h-[9rem] flex-col items-center justify-center gap-3">
                <Transition name="hero-text" mode="out-in">
                    <div :key="currentIndex">
                        <h1 class="max-w-4xl text-5xl font-black tracking-tight text-white sm:text-6xl lg:text-7xl">
                            {{ current.caption }}<br />
                            <span class="text-amber-400">{{ current.accent }}</span>
                        </h1>
                        <p class="mx-auto mt-3 max-w-xl text-lg text-slate-300">{{ current.sub }}</p>
                    </div>
                </Transition>
            </div>

            <a
                href="#events"
                class="rounded-full bg-amber-400 px-8 py-4 text-base font-bold text-slate-900 shadow-lg transition-all hover:bg-amber-300 hover:shadow-amber-400/25 hover:shadow-xl active:scale-95"
            >
                Explore Upcoming Trips
            </a>

        </div>

        <!-- Prev / Next arrows -->
        <button
            type="button"
            class="absolute left-4 top-1/2 z-20 -translate-y-1/2 rounded-full bg-slate-900/60 p-2.5 text-white backdrop-blur-sm transition hover:bg-slate-900/80 md:left-8"
            aria-label="Previous slide"
            @click="goTo(currentIndex - 1)"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <button
            type="button"
            class="absolute right-4 top-1/2 z-20 -translate-y-1/2 rounded-full bg-slate-900/60 p-2.5 text-white backdrop-blur-sm transition hover:bg-slate-900/80 md:right-8"
            aria-label="Next slide"
            @click="goTo(currentIndex + 1)"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        <!-- Dot indicators -->
        <div class="absolute bottom-20 left-1/2 z-20 flex -translate-x-1/2 gap-2">
            <button
                v-for="(_, i) in slides"
                :key="i"
                type="button"
                class="h-1.5 rounded-full transition-all duration-300"
                :class="i === currentIndex ? 'w-8 bg-amber-400' : 'w-2 bg-white/40 hover:bg-white/70'"
                :aria-label="`Go to slide ${i + 1}`"
                @click="goTo(i)"
            />
        </div>

        <!-- Scroll hint -->
        <div class="absolute bottom-8 left-1/2 z-10 -translate-x-1/2 animate-bounce text-slate-400">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </section>
</template>

<style scoped>
.hero-text-enter-active,
.hero-text-leave-active {
    transition: opacity 0.4s ease, transform 0.4s ease;
}
.hero-text-enter-from {
    opacity: 0;
    transform: translateY(16px);
}
.hero-text-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
