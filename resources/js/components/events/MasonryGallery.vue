<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import type { EventMedia } from '@/types';

defineProps<{
    items: EventMedia[];
}>();

const lightboxOpen = ref(false);
const lightboxSrc = ref('');
const lightboxAlt = ref('');

function openLightbox(url: string, alt: string) {
    lightboxSrc.value = url;
    lightboxAlt.value = alt;
    lightboxOpen.value = true;
}

function closeLightbox() {
    lightboxOpen.value = false;
}

function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') { closeLightbox(); }
}

onMounted(() => window.addEventListener('keydown', handleKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', handleKeydown));
</script>

<template>
    <section v-if="items.length > 0" aria-label="Past trips photo wall">
        <!-- Masonry grid via CSS columns -->
        <div class="columns-2 gap-3 space-y-3 md:columns-3">
            <div
                v-for="item in items"
                :key="item.id"
                class="group relative break-inside-avoid cursor-pointer overflow-hidden rounded-xl bg-slate-800"
                role="button"
                tabindex="0"
                :aria-label="`View photo`"
                @click="openLightbox(item.url, 'Past trip photo')"
                @keydown.enter="openLightbox(item.url, 'Past trip photo')"
                @keydown.space.prevent="openLightbox(item.url, 'Past trip photo')"
            >
                <img
                    :src="item.url"
                    alt="Past trip photo"
                    loading="lazy"
                    class="w-full object-cover transition-transform duration-500 group-hover:scale-105"
                />
                <div class="absolute inset-0 bg-slate-950/0 transition-colors duration-300 group-hover:bg-slate-950/20" />
            </div>
        </div>

        <!-- Lightbox -->
        <Teleport to="body">
            <div
                v-if="lightboxOpen"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/90 p-4"
                role="dialog"
                aria-modal="true"
                :aria-label="lightboxAlt"
                @click.self="closeLightbox"
            >
                <button
                    type="button"
                    class="absolute right-6 top-6 rounded-full bg-slate-800 p-2 text-white transition-colors hover:bg-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400"
                    aria-label="Close lightbox"
                    @click="closeLightbox"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <img
                    :src="lightboxSrc"
                    :alt="lightboxAlt"
                    class="max-h-[90vh] max-w-full rounded-xl object-contain shadow-2xl"
                />
            </div>
        </Teleport>
    </section>
</template>
