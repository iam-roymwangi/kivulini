<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { X, ChevronLeft, ChevronRight, ZoomIn, Play } from '@lucide/vue';
import type { EventMedia } from '@/types';

const props = defineProps<{
    media: EventMedia[];
}>();

// --- normalise the media list regardless of resource wrapper shape ---
const mediaList = computed<EventMedia[]>(() => {
    if (!props.media) { return []; }
    if (Array.isArray(props.media)) { return props.media; }
    if (typeof props.media === 'object' && 'data' in (props.media as any)) {
        return (props.media as any).data ?? [];
    }
    return [];
});

const sorted = computed(() =>
    [...mediaList.value].sort((a, b) => a.sort_order - b.sort_order),
);

// ---- lightbox state ----
const lightboxOpen = ref(false);
const lightboxIndex = ref(0);

function openLightbox(index: number) {
    lightboxIndex.value = index;
    lightboxOpen.value = true;
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    lightboxOpen.value = false;
    document.body.style.overflow = '';
}

function prev() {
    lightboxIndex.value = (lightboxIndex.value - 1 + sorted.value.length) % sorted.value.length;
}

function next() {
    lightboxIndex.value = (lightboxIndex.value + 1) % sorted.value.length;
}

function onKeydown(e: KeyboardEvent) {
    if (!lightboxOpen.value) { return; }
    if (e.key === 'ArrowLeft') { prev(); }
    else if (e.key === 'ArrowRight') { next(); }
    else if (e.key === 'Escape') { closeLightbox(); }
}

onMounted(() => window.addEventListener('keydown', onKeydown));
onUnmounted(() => {
    window.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = '';
});

// ---- layout helpers ----
// First item is the hero (big). Rest fills a grid.
const hero = computed(() => sorted.value[0] ?? null);
const extras = computed(() => sorted.value.slice(1, 5)); // max 4 extra slots
const remaining = computed(() => sorted.value.length - 5); // items beyond slot 4
</script>

<template>
    <div class="w-full">

        <!-- ── Empty placeholder ── -->
        <div
            v-if="!hero"
            class="flex aspect-video w-full flex-col items-center justify-center gap-4 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800"
        >
            <svg class="h-16 w-16 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="text-xs font-semibold uppercase tracking-widest text-slate-600">No photos yet</span>
        </div>

        <!-- ── Main grid layout ── -->
        <div
            v-else
            class="grid gap-2"
            :class="{
                'grid-cols-1':                        extras.length === 0,
                'grid-cols-1 md:grid-cols-2':         extras.length === 1,
                'grid-cols-2 md:grid-cols-3':         extras.length >= 2,
            }"
        >
            <!-- Hero cell — always spans full height on left -->
            <div
                class="group relative cursor-pointer overflow-hidden rounded-2xl bg-slate-900"
                :class="{
                    'aspect-video':                   extras.length === 0,
                    'md:row-span-2':                  extras.length >= 2,
                    'aspect-video md:aspect-auto':    extras.length >= 2,
                }"
                @click="openLightbox(0)"
            >
                <video
                    v-if="hero.type === 'video'"
                    :src="hero.url"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    muted
                    playsinline
                />
                <img
                    v-else
                    :src="hero.url"
                    alt="Event photo 1"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                />
                <!-- hover overlay -->
                <div class="absolute inset-0 flex items-center justify-center bg-black/0 transition-all duration-300 group-hover:bg-black/30">
                    <div class="scale-75 rounded-full bg-white/20 p-3 opacity-0 backdrop-blur-sm transition-all duration-300 group-hover:scale-100 group-hover:opacity-100">
                        <Play v-if="hero.type === 'video'" class="h-6 w-6 text-white" />
                        <ZoomIn v-else class="h-6 w-6 text-white" />
                    </div>
                </div>
            </div>

            <!-- Extra cells — right column(s) -->
            <template v-for="(item, i) in extras" :key="item.id">
                <div
                    class="group relative cursor-pointer overflow-hidden rounded-2xl bg-slate-900"
                    :class="extras.length >= 2 ? 'aspect-square md:aspect-video' : 'aspect-video'"
                    @click="openLightbox(i + 1)"
                >
                    <img
                        :src="item.url"
                        :alt="`Event photo ${i + 2}`"
                        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                        loading="lazy"
                    />
                    <!-- "see all" overlay on last visible extra when more exist -->
                    <div
                        v-if="i === 3 && remaining > 0"
                        class="absolute inset-0 flex flex-col items-center justify-center gap-1 bg-black/55 backdrop-blur-[2px]"
                    >
                        <span class="text-2xl font-black text-white">+{{ remaining }}</span>
                        <span class="text-xs font-semibold uppercase tracking-widest text-white/80">more photos</span>
                    </div>
                    <!-- hover overlay for non-"see all" cells -->
                    <div
                        v-else
                        class="absolute inset-0 flex items-center justify-center bg-black/0 transition-all duration-300 group-hover:bg-black/30"
                    >
                        <div class="scale-75 rounded-full bg-white/20 p-2.5 opacity-0 backdrop-blur-sm transition-all duration-300 group-hover:scale-100 group-hover:opacity-100">
                            <ZoomIn class="h-5 w-5 text-white" />
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- ── Lightbox ── -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                leave-active-class="transition-opacity duration-200"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="lightboxOpen"
                    class="fixed inset-0 z-[9998] flex items-center justify-center bg-black/95 p-4"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Photo viewer"
                    @click.self="closeLightbox"
                >
                    <!-- Close -->
                    <button
                        class="absolute top-4 right-4 z-10 rounded-full bg-white/10 p-2 text-white transition-colors hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
                        aria-label="Close"
                        @click="closeLightbox"
                    >
                        <X class="h-5 w-5" />
                    </button>

                    <!-- Counter -->
                    <div class="absolute top-4 left-1/2 -translate-x-1/2 rounded-full bg-black/40 px-3 py-1 text-xs font-semibold text-white/80 backdrop-blur-sm">
                        {{ lightboxIndex + 1 }} / {{ sorted.length }}
                    </div>

                    <!-- Prev -->
                    <button
                        v-if="sorted.length > 1"
                        class="absolute left-4 rounded-full bg-white/10 p-3 text-white transition-colors hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
                        aria-label="Previous photo"
                        @click="prev"
                    >
                        <ChevronLeft class="h-6 w-6" />
                    </button>

                    <!-- Media -->
                    <Transition
                        mode="out-in"
                        enter-active-class="transition-all duration-200"
                        enter-from-class="opacity-0 scale-95"
                        leave-active-class="transition-all duration-150"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div :key="lightboxIndex" class="flex max-h-[85vh] max-w-5xl items-center justify-center">
                            <video
                                v-if="sorted[lightboxIndex]?.type === 'video'"
                                :src="sorted[lightboxIndex]?.url"
                                controls
                                autoplay
                                class="max-h-[85vh] max-w-full rounded-xl object-contain shadow-2xl"
                            />
                            <img
                                v-else
                                :src="sorted[lightboxIndex]?.url"
                                :alt="`Photo ${lightboxIndex + 1}`"
                                class="max-h-[85vh] max-w-full rounded-xl object-contain shadow-2xl"
                                draggable="false"
                            />
                        </div>
                    </Transition>

                    <!-- Next -->
                    <button
                        v-if="sorted.length > 1"
                        class="absolute right-4 rounded-full bg-white/10 p-3 text-white transition-colors hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
                        aria-label="Next photo"
                        @click="next"
                    >
                        <ChevronRight class="h-6 w-6" />
                    </button>

                    <!-- Thumbnail strip -->
                    <div
                        v-if="sorted.length > 1"
                        class="absolute bottom-4 left-1/2 flex -translate-x-1/2 gap-1.5 overflow-x-auto rounded-xl bg-black/40 p-2 backdrop-blur-sm"
                        style="max-width: min(100vw - 2rem, 600px)"
                    >
                        <button
                            v-for="(item, i) in sorted"
                            :key="item.id"
                            class="h-12 w-16 shrink-0 overflow-hidden rounded-lg border-2 transition-all duration-200 focus:outline-none"
                            :class="i === lightboxIndex ? 'border-amber-400 opacity-100' : 'border-transparent opacity-50 hover:opacity-80'"
                            :aria-label="`Go to photo ${i + 1}`"
                            @click="lightboxIndex = i"
                        >
                            <img :src="item.url" :alt="`Thumb ${i + 1}`" class="h-full w-full object-cover" loading="lazy" />
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
