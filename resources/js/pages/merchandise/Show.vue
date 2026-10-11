<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ShoppingBag, Tag, Ruler, Palette, Package, ChevronLeft, ChevronRight, X } from '@lucide/vue';
import { index as merchandiseIndex } from '@/routes/merchandise';
import type { MerchandiseItem, MerchandiseImage } from '@/types/merchandise';

const props = defineProps<{
    merchandise: MerchandiseItem;
}>();

const TYPE_LABELS: Record<string, string> = {
    t_shirt: 'T-Shirt',
    hoodie: 'Hoodie',
    hat: 'Hat',
    other: 'Other',
};

// ── Gallery state ─────────────────────────────────────────────────────────────
const images = computed<MerchandiseImage[]>(() => {
    const imgs = props.merchandise.images ?? [];
    return [...imgs].sort((a, b) => a.sort_order - b.sort_order);
});

const selectedIndex = ref(0);
const selectedImage = computed(() => images.value[selectedIndex.value] ?? null);
const lightboxOpen = ref(false);
const lightboxIndex = ref(0);

function selectImage(index: number) {
    selectedIndex.value = index;
}

function openLightbox(index: number) {
    lightboxIndex.value = index;
    lightboxOpen.value = true;
}

function prevLightbox() {
    lightboxIndex.value = (lightboxIndex.value - 1 + images.value.length) % images.value.length;
}

function nextLightbox() {
    lightboxIndex.value = (lightboxIndex.value + 1) % images.value.length;
}

function onKeydown(e: KeyboardEvent) {
    if (!lightboxOpen.value) { return; }
    if (e.key === 'ArrowLeft') { prevLightbox(); }
    if (e.key === 'ArrowRight') { nextLightbox(); }
    if (e.key === 'Escape') { lightboxOpen.value = false; }
}

// ── Selection state ───────────────────────────────────────────────────────────
const selectedSize = ref('');
const selectedColor = ref('');

const isInStock = computed(() => props.merchandise.stock > 0);
const canOrder = computed(() => {
    const needsSize = (props.merchandise.sizes ?? []).length > 0;
    const needsColor = (props.merchandise.colors ?? []).length > 0;
    return (!needsSize || selectedSize.value !== '') && (!needsColor || selectedColor.value !== '');
});

// WhatsApp order — simple intent link
const whatsappMessage = computed(() => {
    const parts = [
        `Hi, I'd like to order the *${props.merchandise.name}*`,
        selectedSize.value ? `Size: ${selectedSize.value}` : '',
        selectedColor.value ? `Color: ${selectedColor.value}` : '',
        `Price: KES ${Number(props.merchandise.price).toLocaleString()}`,
    ].filter(Boolean);
    return encodeURIComponent(parts.join('\n'));
});

const whatsappUrl = computed(() => `https://wa.me/?text=${whatsappMessage.value}`);
</script>

<template>
    <Head>
        <title>{{ merchandise.name }} – Kivulini Adventures</title>
        <meta name="description" :content="merchandise.description ?? `Shop ${merchandise.name} from Kivulini Adventures.`" />
        <meta property="og:title" :content="`${merchandise.name} – Kivulini Adventures`" />
        <meta property="og:description" :content="merchandise.description ?? `Shop ${merchandise.name} from Kivulini Adventures.`" />
        <meta v-if="merchandise.cover_image_url" property="og:image" :content="merchandise.cover_image_url" />
    </Head>

    <!-- Keyboard handler for lightbox -->
    <div tabindex="-1" class="outline-none" @keydown="onKeydown">

    <div class="mx-auto max-w-7xl px-4 pb-24 pt-28 md:px-8 lg:px-12">

        <!-- Back link -->
        <div class="mb-8">
            <Link :href="merchandiseIndex.url()" class="inline-flex items-center gap-2 text-sm font-medium text-muted-foreground transition hover:text-amber-500">
                <ArrowLeft class="h-4 w-4" />
                Back to Merch
            </Link>
        </div>

        <!-- Main grid -->
        <div class="grid grid-cols-1 gap-10 lg:grid-cols-2 lg:gap-16">

            <!-- ── Left: Gallery ──────────────────────────────────────────────── -->
            <div class="space-y-3">
                <!-- Main image -->
                <div
                    class="relative aspect-square w-full cursor-zoom-in overflow-hidden rounded-2xl bg-muted"
                    @click="openLightbox(selectedIndex)"
                >
                    <img
                        v-if="selectedImage"
                        :src="selectedImage.url"
                        :alt="merchandise.name"
                        class="h-full w-full object-cover transition-transform duration-500 hover:scale-105"
                    />
                    <div v-else class="flex h-full w-full items-center justify-center">
                        <ShoppingBag class="h-20 w-20 text-muted-foreground/30" />
                    </div>

                    <!-- Stock overlay -->
                    <div v-if="!isInStock" class="absolute inset-0 flex items-center justify-center bg-black/50">
                        <span class="rounded-full bg-red-600 px-4 py-1.5 text-sm font-bold text-white">Sold Out</span>
                    </div>
                </div>

                <!-- Thumbnail reel -->
                <div v-if="images.length > 1" class="flex gap-2 overflow-x-auto pb-1" role="list">
                    <button
                        v-for="(img, i) in images"
                        :key="img.id"
                        type="button"
                        role="listitem"
                        :aria-label="`View image ${i + 1}`"
                        :aria-pressed="selectedIndex === i"
                        class="relative h-16 w-16 shrink-0 overflow-hidden rounded-xl border-2 transition-all duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400"
                        :class="selectedIndex === i ? 'border-amber-400' : 'border-border opacity-60 hover:opacity-100'"
                        @click="selectImage(i)"
                    >
                        <img :src="img.url" :alt="`Thumbnail ${i + 1}`" class="h-full w-full object-cover" loading="lazy" />
                    </button>
                </div>
            </div>

            <!-- ── Right: Details ─────────────────────────────────────────────── -->
            <div class="flex flex-col space-y-6">

                <!-- Badge + name -->
                <div>
                    <span class="mb-3 inline-flex items-center gap-1 rounded-full bg-amber-400/10 px-3 py-1 text-xs font-bold text-amber-600 dark:text-amber-400">
                        <Tag class="h-3 w-3" />
                        {{ TYPE_LABELS[merchandise.type] ?? merchandise.type }}
                    </span>
                    <h1 class="text-3xl font-black text-foreground sm:text-4xl">{{ merchandise.name }}</h1>
                    <p class="mt-3 text-3xl font-black text-amber-500">
                        KES {{ Number(merchandise.price).toLocaleString() }}
                    </p>
                </div>

                <!-- Description -->
                <p v-if="merchandise.description" class="leading-relaxed text-muted-foreground">
                    {{ merchandise.description }}
                </p>

                <!-- Size selector -->
                <div v-if="(merchandise.sizes ?? []).length > 0">
                    <div class="mb-2 flex items-center justify-between">
                        <label class="flex items-center gap-1.5 text-sm font-semibold text-foreground">
                            <Ruler class="h-4 w-4 text-muted-foreground" /> Size
                        </label>
                        <span v-if="!selectedSize" class="text-xs text-muted-foreground">Select a size</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="size in merchandise.sizes"
                            :key="size"
                            type="button"
                            class="rounded-xl border px-4 py-2 text-sm font-semibold transition-all"
                            :class="selectedSize === size
                                ? 'border-amber-400 bg-amber-400/10 text-amber-600 dark:text-amber-400'
                                : 'border-border text-foreground hover:border-amber-400/60'"
                            @click="selectedSize = size"
                        >
                            {{ size }}
                        </button>
                    </div>
                </div>

                <!-- Color selector -->
                <div v-if="(merchandise.colors ?? []).length > 0">
                    <div class="mb-2 flex items-center justify-between">
                        <label class="flex items-center gap-1.5 text-sm font-semibold text-foreground">
                            <Palette class="h-4 w-4 text-muted-foreground" /> Color
                            <span v-if="selectedColor" class="ml-1 font-normal text-muted-foreground">— {{ selectedColor }}</span>
                        </label>
                        <span v-if="!selectedColor" class="text-xs text-muted-foreground">Select a color</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="color in merchandise.colors"
                            :key="color"
                            type="button"
                            class="rounded-xl border px-4 py-2 text-sm font-semibold transition-all"
                            :class="selectedColor === color
                                ? 'border-amber-400 bg-amber-400/10 text-amber-600 dark:text-amber-400'
                                : 'border-border text-foreground hover:border-amber-400/60'"
                            @click="selectedColor = color"
                        >
                            {{ color }}
                        </button>
                    </div>
                </div>

                <!-- Stock indicator -->
                <div class="flex items-center gap-2">
                    <Package class="h-4 w-4 text-muted-foreground" />
                    <span v-if="isInStock" class="text-sm text-muted-foreground">
                        <span class="font-semibold text-green-500">In stock</span>
                        <span v-if="merchandise.stock <= 10"> · only {{ merchandise.stock }} left</span>
                    </span>
                    <span v-else class="text-sm font-semibold text-red-500">Out of stock</span>
                </div>

                <!-- CTA -->
                <div class="pt-2">
                    <a
                        v-if="isInStock"
                        :href="canOrder ? whatsappUrl : '#'"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex w-full items-center justify-center gap-3 rounded-2xl py-4 text-base font-bold transition-all duration-200"
                        :class="canOrder
                            ? 'bg-amber-400 text-slate-900 hover:bg-amber-300 shadow-lg shadow-amber-400/20'
                            : 'cursor-not-allowed bg-muted text-muted-foreground'"
                        :aria-disabled="!canOrder"
                        @click.prevent="canOrder ? $event.currentTarget?.dispatchEvent(new MouseEvent('click')) : null"
                    >
                        <!-- WhatsApp icon -->
                        <svg class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                        </svg>
                        Order via WhatsApp
                    </a>
                    <div v-else class="flex w-full items-center justify-center gap-2 rounded-2xl bg-muted py-4 text-base font-bold text-muted-foreground">
                        <ShoppingBag class="h-5 w-5" />
                        Currently Unavailable
                    </div>
                    <p v-if="!canOrder && isInStock" class="mt-2 text-center text-xs text-muted-foreground">
                        Please select
                        {{ !selectedSize && (merchandise.sizes ?? []).length > 0 ? 'a size' : '' }}{{ !selectedSize && (merchandise.sizes ?? []).length > 0 && !selectedColor && (merchandise.colors ?? []).length > 0 ? ' and ' : '' }}{{ !selectedColor && (merchandise.colors ?? []).length > 0 ? 'a color' : '' }}
                        to continue.
                    </p>
                </div>

                <!-- Specs card -->
                <div class="rounded-2xl border border-border bg-card p-5">
                    <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-muted-foreground">Product Details</h3>
                    <dl class="space-y-3">
                        <div class="flex items-center justify-between gap-4 text-sm">
                            <dt class="text-muted-foreground">Type</dt>
                            <dd class="font-semibold text-foreground">{{ TYPE_LABELS[merchandise.type] ?? merchandise.type }}</dd>
                        </div>
                        <div v-if="(merchandise.sizes ?? []).length > 0" class="flex items-start justify-between gap-4 text-sm">
                            <dt class="text-muted-foreground">Available Sizes</dt>
                            <dd class="text-right font-semibold text-foreground">{{ (merchandise.sizes ?? []).join(', ') }}</dd>
                        </div>
                        <div v-if="(merchandise.colors ?? []).length > 0" class="flex items-start justify-between gap-4 text-sm">
                            <dt class="text-muted-foreground">Available Colors</dt>
                            <dd class="text-right font-semibold text-foreground">{{ (merchandise.colors ?? []).join(', ') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 text-sm">
                            <dt class="text-muted-foreground">Stock</dt>
                            <dd class="font-semibold" :class="isInStock ? 'text-green-500' : 'text-red-500'">
                                {{ isInStock ? `${merchandise.stock} available` : 'Out of stock' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Lightbox -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="lightboxOpen"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
                role="dialog"
                aria-modal="true"
                :aria-label="`Image ${lightboxIndex + 1} of ${images.length}`"
                @click.self="lightboxOpen = false"
            >
                <!-- Close -->
                <button
                    type="button"
                    class="absolute right-4 top-4 rounded-full bg-white/10 p-2 text-white hover:bg-white/20"
                    aria-label="Close lightbox"
                    @click="lightboxOpen = false"
                >
                    <X class="h-5 w-5" />
                </button>

                <!-- Prev -->
                <button
                    v-if="images.length > 1"
                    type="button"
                    class="absolute left-4 top-1/2 -translate-y-1/2 rounded-full bg-white/10 p-3 text-white hover:bg-white/20"
                    aria-label="Previous image"
                    @click="prevLightbox"
                >
                    <ChevronLeft class="h-6 w-6" />
                </button>

                <!-- Image -->
                <img
                    v-if="images[lightboxIndex]"
                    :src="images[lightboxIndex].url"
                    :alt="`${merchandise.name} – image ${lightboxIndex + 1}`"
                    class="max-h-[90vh] max-w-[90vw] rounded-xl object-contain shadow-2xl"
                />

                <!-- Next -->
                <button
                    v-if="images.length > 1"
                    type="button"
                    class="absolute right-4 top-1/2 -translate-y-1/2 rounded-full bg-white/10 p-3 text-white hover:bg-white/20"
                    aria-label="Next image"
                    @click="nextLightbox"
                >
                    <ChevronRight class="h-6 w-6" />
                </button>

                <!-- Counter -->
                <span class="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-white/10 px-4 py-1.5 text-sm font-semibold text-white">
                    {{ lightboxIndex + 1 }} / {{ images.length }}
                </span>
            </div>
        </Transition>
    </Teleport>

    </div>
</template>
