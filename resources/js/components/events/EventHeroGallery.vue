<script setup lang="ts">
import { ref, computed } from 'vue';
import type { EventMedia } from '@/types';

const props = defineProps<{
    media: EventMedia[];
}>();

const mediaList = computed(() => {
    if (!props.media) { return []; }
    if (Array.isArray(props.media)) { return props.media; }
    if (typeof props.media === 'object' && props.media !== null && 'data' in props.media) {
        return (props.media as any).data || [];
    }
    return [];
});

const sortedMedia = computed(() =>
    [...mediaList.value].sort((a, b) => a.sort_order - b.sort_order),
);

const selectedIndex = ref(0);

const selectedMedia = computed(() => sortedMedia.value[selectedIndex.value] ?? null);

const hasThumbnails = computed(() => sortedMedia.value.length > 1);

function selectMedia(index: number) {
    selectedIndex.value = index;
}
</script>

<template>
    <div class="w-full">
        <!-- Main cover -->
        <div class="relative aspect-video w-full overflow-hidden rounded-2xl bg-slate-800">
            <!-- Media present -->
            <template v-if="selectedMedia">
                <video
                    v-if="selectedMedia.type === 'video'"
                    :src="selectedMedia.url"
                    controls
                    class="h-full w-full object-cover"
                />
                <img
                    v-else
                    :src="selectedMedia.url"
                    :alt="`Event photo ${selectedIndex + 1}`"
                    class="h-full w-full object-cover transition-opacity duration-300"
                />
            </template>

            <!-- Branded placeholder when no media -->
            <div
                v-else
                class="flex h-full w-full flex-col items-center justify-center gap-4 bg-gradient-to-br from-slate-900 to-slate-800"
            >
                <svg
                    class="h-20 w-20 text-slate-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                </svg>
                <span class="text-sm font-semibold tracking-widest text-slate-600 uppercase">Kivulini</span>
            </div>
        </div>

        <!-- Thumbnail reel — only shown when more than 1 media item -->
        <div
            v-if="hasThumbnails"
            class="mt-3 flex gap-2 overflow-x-auto pb-1"
            role="list"
            aria-label="Event photos"
        >
            <button
                v-for="(item, index) in sortedMedia"
                :key="item.id"
                type="button"
                role="listitem"
                :aria-label="`View photo ${index + 1}`"
                :aria-pressed="selectedIndex === index"
                class="relative h-16 w-24 shrink-0 overflow-hidden rounded-lg border-2 transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400"
                :class="selectedIndex === index ? 'border-amber-400' : 'border-transparent opacity-60 hover:opacity-100'"
                @click="selectMedia(index)"
            >
                <img
                    :src="item.url"
                    :alt="`Thumbnail ${index + 1}`"
                    class="h-full w-full object-cover"
                    loading="lazy"
                />
            </button>
        </div>
    </div>
</template>
