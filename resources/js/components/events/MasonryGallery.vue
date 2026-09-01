<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { ChevronLeft, ChevronRight, X } from '@lucide/vue';
import type { EventMedia } from '@/types';

const props = defineProps<{
    items: EventMedia[];
}>();

const lightboxOpen = ref(false);
const currentIndex = ref(0);

const currentItem = computed(() => props.items[currentIndex.value] ?? null);

function openLightbox(index: number) {
    if (index >= 0 && index < props.items.length) {
        currentIndex.value = index;
        lightboxOpen.value = true;
        document.body.style.overflow = 'hidden';
    }
}

function closeLightbox() {
    lightboxOpen.value = false;
    document.body.style.overflow = '';
}

function prevPhoto() {
    if (props.items.length === 0) return;
    currentIndex.value = (currentIndex.value - 1 + props.items.length) % props.items.length;
}

function nextPhoto() {
    if (props.items.length === 0) return;
    currentIndex.value = (currentIndex.value + 1) % props.items.length;
}

// Touch swipe handling for mobile devices
let touchStartX = 0;
let touchEndX = 0;

function handleTouchStart(e: TouchEvent) {
    touchStartX = e.changedTouches[0].screenX;
}

function handleTouchEnd(e: TouchEvent) {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
}

function handleSwipe() {
    const swipeThreshold = 50;
    if (touchEndX < touchStartX - swipeThreshold) {
        nextPhoto();
    } else if (touchEndX > touchStartX + swipeThreshold) {
        prevPhoto();
    }
}

// Keyboard navigation
function handleKeydown(e: KeyboardEvent) {
    if (!lightboxOpen.value) return;

    if (e.key === 'Escape') {
        closeLightbox();
    } else if (e.key === 'ArrowLeft') {
        prevPhoto();
    } else if (e.key === 'ArrowRight') {
        nextPhoto();
    }
}

onMounted(() => window.addEventListener('keydown', handleKeydown));
onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
    <section v-if="items.length > 0" aria-label="Past trips photo gallery">
        <!-- Masonry grid via CSS columns -->
        <div class="columns-2 gap-3 space-y-3 sm:gap-4 sm:space-y-4 md:columns-3 lg:columns-4">
            <div
                v-for="(item, index) in items"
                :key="item.id"
                class="group relative break-inside-avoid cursor-pointer overflow-hidden rounded-2xl bg-slate-900 shadow-md transition-all duration-300 hover:shadow-xl hover:shadow-amber-500/10"
                role="button"
                tabindex="0"
                :aria-label="`View photo ${index + 1} of ${items.length}`"
                @click="openLightbox(index)"
                @keydown.enter="openLightbox(index)"
                @keydown.space.prevent="openLightbox(index)"
            >
                <img
                    :src="item.url"
                    :alt="item.event?.title ? `Photo from ${item.event.title}` : `Gallery photo ${index + 1}`"
                    loading="lazy"
                    class="w-full object-cover transition-transform duration-500 group-hover:scale-105"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100 flex flex-col justify-end p-4">
                    <p v-if="item.event?.title" class="text-xs font-bold text-amber-400 truncate">
                        {{ item.event.title }}
                    </p>
                    <p class="text-[11px] font-medium text-slate-300">
                        Click to expand
                    </p>
                </div>
            </div>
        </div>

        <!-- Lightbox Viewer Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="lightboxOpen && currentItem"
                    class="fixed inset-0 z-[100] flex flex-col items-center justify-between bg-slate-950/95 p-4 backdrop-blur-md sm:p-6"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Gallery Image Viewer"
                    @touchstart="handleTouchStart"
                    @touchend="handleTouchEnd"
                    @click.self="closeLightbox"
                >
                    <!-- Lightbox Header -->
                    <div class="flex w-full max-w-6xl items-center justify-between z-10 py-2 px-1">
                        <div class="flex items-center gap-3">
                            <span class="rounded-full border border-slate-700 bg-slate-900/80 px-3 py-1 text-xs font-bold text-amber-400">
                                {{ currentIndex + 1 }} / {{ items.length }}
                            </span>
                            <span v-if="currentItem.event?.title" class="hidden text-sm font-semibold text-slate-300 sm:inline truncate max-w-xs md:max-w-md">
                                {{ currentItem.event.title }}
                            </span>
                        </div>

                        <button
                            type="button"
                            class="rounded-full border border-slate-700 bg-slate-900/80 p-2.5 text-slate-300 transition-all hover:border-amber-400 hover:bg-slate-800 hover:text-amber-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 active:scale-95"
                            aria-label="Close viewer"
                            @click="closeLightbox"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Main Image Display & Navigation Arrows -->
                    <div class="relative flex w-full max-w-6xl flex-1 items-center justify-center overflow-hidden my-4">
                        <!-- Prev Arrow -->
                        <button
                            v-if="items.length > 1"
                            type="button"
                            class="absolute left-2 z-20 rounded-full border border-slate-700/80 bg-slate-900/80 p-3 text-slate-200 shadow-xl transition-all hover:scale-110 hover:border-amber-400 hover:bg-amber-400 hover:text-slate-950 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 sm:left-4"
                            aria-label="Previous photo"
                            @click="prevPhoto"
                        >
                            <ChevronLeft class="h-6 w-6" />
                        </button>

                        <!-- Main Image -->
                        <Transition
                            mode="out-in"
                            enter-active-class="transition duration-300 ease-out"
                            enter-from-class="opacity-0 scale-95"
                            enter-to-class="opacity-100 scale-100"
                            leave-active-class="transition duration-200 ease-in"
                            leave-from-class="opacity-100 scale-100"
                            leave-to-class="opacity-0 scale-95"
                        >
                            <img
                                :key="currentItem.id"
                                :src="currentItem.url"
                                :alt="currentItem.event?.title ? `Photo from ${currentItem.event.title}` : `Gallery photo ${currentIndex + 1}`"
                                class="max-h-[75vh] w-auto max-w-full rounded-2xl object-contain shadow-2xl transition-all"
                            />
                        </Transition>

                        <!-- Next Arrow -->
                        <button
                            v-if="items.length > 1"
                            type="button"
                            class="absolute right-2 z-20 rounded-full border border-slate-700/80 bg-slate-900/80 p-3 text-slate-200 shadow-xl transition-all hover:scale-110 hover:border-amber-400 hover:bg-amber-400 hover:text-slate-950 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 sm:right-4"
                            aria-label="Next photo"
                            @click="nextPhoto"
                        >
                            <ChevronRight class="h-6 w-6" />
                        </button>
                    </div>

                    <!-- Lightbox Footer & Thumbnail Strip -->
                    <div class="w-full max-w-4xl z-10 flex flex-col items-center gap-3">
                        <p v-if="currentItem.event?.title" class="text-center text-sm font-semibold text-slate-200 sm:hidden">
                            {{ currentItem.event.title }}
                        </p>

                        <!-- Thumbnail strip -->
                        <div v-if="items.length > 1" class="flex items-center gap-2 overflow-x-auto max-w-full p-2 scrollbar-none">
                            <button
                                v-for="(thumb, idx) in items"
                                :key="thumb.id"
                                type="button"
                                class="relative h-12 w-16 shrink-0 overflow-hidden rounded-lg border-2 transition-all"
                                :class="idx === currentIndex ? 'border-amber-400 scale-105 opacity-100' : 'border-transparent opacity-40 hover:opacity-80'"
                                :aria-label="`Jump to photo ${idx + 1}`"
                                @click="currentIndex = idx"
                            >
                                <img :src="thumb.url" alt="" class="h-full w-full object-cover" />
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </section>
</template>
