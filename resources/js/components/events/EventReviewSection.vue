<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { Star, ImagePlus, X, Loader2, CheckCircle } from '@lucide/vue';
import type { EventReview } from '@/types';

const props = defineProps<{
    eventId: number;
    reviews: EventReview[];
    isCompleted: boolean;
}>();

// ── Review form ───────────────────────────────────────────────────────────────

const form = useForm({
    reviewer_name: '',
    reviewer_email: '',
    rating: 0,
    body: '',
    image: null as File | null,
});

const hoverRating = ref(0);
const imagePreview = ref<string>('');
const fileInput = ref<HTMLInputElement | null>(null);
const converting = ref(false);

const page = usePage();
const justSubmitted = computed(() => (page.props.flash as Record<string, string> | undefined)?.success?.includes('review'));

// ── WebP compression ──────────────────────────────────────────────────────────

function compressToWebp(file: File): Promise<File> {
    return new Promise((resolve) => {
        const img = new Image();
        const url = URL.createObjectURL(file);

        img.onload = () => {
            URL.revokeObjectURL(url);
            const MAX = 1600;
            let { width, height } = img;
            if (width > MAX || height > MAX) {
                if (width > height) { height = Math.round((height * MAX) / width); width = MAX; }
                else { width = Math.round((width * MAX) / height); height = MAX; }
            }
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            canvas.getContext('2d')!.drawImage(img, 0, 0, width, height);
            canvas.toBlob(
                (blob) => {
                    if (!blob) { resolve(file); return; }
                    resolve(new File([blob], file.name.replace(/\.[^.]+$/, '.webp'), { type: 'image/webp' }));
                },
                'image/webp',
                0.85,
            );
        };
        img.onerror = () => { URL.revokeObjectURL(url); resolve(file); };
        img.src = url;
    });
}

async function handleImageChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    (e.target as HTMLInputElement).value = '';
    if (!file) { return; }

    converting.value = true;
    const webp = await compressToWebp(file);
    form.image = webp;

    const reader = new FileReader();
    reader.onload = (ev) => { imagePreview.value = ev.target?.result as string; };
    reader.readAsDataURL(webp);

    converting.value = false;
}

function removeImage() {
    form.image = null;
    imagePreview.value = '';
}

function setRating(value: number) {
    form.rating = value;
}

function submitReview() {
    form.post(`/events/${props.eventId}/reviews`, {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            imagePreview.value = '';
            hoverRating.value = 0;
        },
    });
}

// ── Display helpers ───────────────────────────────────────────────────────────

function formatDate(iso: string) {
    return new Date(iso).toLocaleDateString('en-KE', { day: 'numeric', month: 'short', year: 'numeric' });
}

const averageRating = computed(() => {
    if (props.reviews.length === 0) { return 0; }
    return props.reviews.reduce((sum, r) => sum + r.rating, 0) / props.reviews.length;
});
</script>

<template>
    <div>
        <h2 class="mb-6 text-xl font-bold text-foreground">Reviews</h2>

        <!-- Approved reviews list -->
        <div v-if="reviews.length > 0" class="mb-10 space-y-5">
            <!-- Summary bar -->
            <div class="flex items-center gap-4 rounded-xl border border-border bg-card p-4">
                <div class="text-center">
                    <p class="text-3xl font-black text-foreground">{{ averageRating.toFixed(1) }}</p>
                    <div class="mt-1 flex items-center gap-0.5">
                        <svg
                            v-for="s in 5"
                            :key="s"
                            class="h-3.5 w-3.5"
                            :class="s <= Math.round(averageRating) ? 'text-amber-400' : 'text-border'"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                            aria-hidden="true"
                        >
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>
                </div>
                <div class="h-10 w-px bg-border" />
                <p class="text-sm text-muted-foreground">
                    Based on
                    <span class="font-semibold text-foreground">{{ reviews.length }}</span>
                    {{ reviews.length === 1 ? 'review' : 'reviews' }}
                </p>
            </div>

            <!-- Individual reviews -->
            <article
                v-for="review in reviews"
                :key="review.id"
                class="rounded-xl border border-border bg-card p-5"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-border bg-muted text-sm font-bold text-foreground">
                            {{ review.reviewer_name[0].toUpperCase() }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-foreground">{{ review.reviewer_name }}</p>
                            <p class="text-xs text-muted-foreground">{{ formatDate(review.created_at) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-0.5 shrink-0" :aria-label="`${review.rating} out of 5`">
                        <svg
                            v-for="s in 5"
                            :key="s"
                            class="h-3.5 w-3.5"
                            :class="s <= review.rating ? 'text-amber-400' : 'text-border'"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                            aria-hidden="true"
                        >
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>
                </div>

                <p class="mt-3 text-sm leading-relaxed text-foreground/80">{{ review.body }}</p>

                <img
                    v-if="review.image_url"
                    :src="review.image_url"
                    :alt="`Photo from ${review.reviewer_name}`"
                    class="mt-4 max-h-64 w-auto rounded-lg border border-border object-cover"
                    loading="lazy"
                />
            </article>
        </div>

        <!-- Empty state (no reviews yet) -->
        <div
            v-else
            class="mb-10 rounded-xl border border-border bg-card p-6 text-center"
        >
            <p class="text-muted-foreground text-sm">No reviews yet. Be the first to share your experience.</p>
        </div>

        <!-- Submit form — only for completed events -->
        <template v-if="isCompleted">
            <!-- Success flash -->
            <div
                v-if="justSubmitted"
                class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-600 dark:text-emerald-400"
            >
                <CheckCircle class="h-5 w-5 shrink-0" />
                Your review has been submitted and is pending approval. Thank you!
            </div>

            <div v-else class="rounded-xl border border-border bg-card p-6">
                <h3 class="mb-5 text-base font-bold text-foreground">Leave a Review</h3>

                <form class="space-y-5" @submit.prevent="submitReview">
                    <!-- Star rating -->
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-foreground">
                            Rating <span class="text-red-500">*</span>
                        </label>
                        <div
                            class="flex items-center gap-1"
                            role="radiogroup"
                            aria-label="Rating"
                        >
                            <button
                                v-for="star in 5"
                                :key="star"
                                type="button"
                                class="rounded p-0.5 transition-transform hover:scale-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400"
                                :aria-label="`${star} star${star > 1 ? 's' : ''}`"
                                :aria-pressed="form.rating === star"
                                @click="setRating(star)"
                                @mouseenter="hoverRating = star"
                                @mouseleave="hoverRating = 0"
                            >
                                <Star
                                    class="h-7 w-7 transition-colors"
                                    :class="star <= (hoverRating || form.rating) ? 'text-amber-400 fill-amber-400' : 'text-border'"
                                />
                            </button>
                        </div>
                        <p v-if="form.errors.rating" class="text-xs text-red-500">{{ form.errors.rating }}</p>
                    </div>

                    <!-- Name + email -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <label for="reviewer_name" class="block text-sm font-medium text-foreground">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="reviewer_name"
                                v-model="form.reviewer_name"
                                type="text"
                                autocomplete="name"
                                placeholder="Your name"
                                class="w-full rounded-xl border border-input bg-background px-4 py-2.5 text-sm text-foreground placeholder:text-muted-foreground transition focus:border-amber-400 focus:outline-none"
                            />
                            <p v-if="form.errors.reviewer_name" class="text-xs text-red-500">{{ form.errors.reviewer_name }}</p>
                        </div>
                        <div class="space-y-1.5">
                            <label for="reviewer_email" class="block text-sm font-medium text-foreground">
                                Email <span class="text-xs font-normal text-muted-foreground">(optional)</span>
                            </label>
                            <input
                                id="reviewer_email"
                                v-model="form.reviewer_email"
                                type="email"
                                autocomplete="email"
                                placeholder="your@email.com"
                                class="w-full rounded-xl border border-input bg-background px-4 py-2.5 text-sm text-foreground placeholder:text-muted-foreground transition focus:border-amber-400 focus:outline-none"
                            />
                            <p v-if="form.errors.reviewer_email" class="text-xs text-red-500">{{ form.errors.reviewer_email }}</p>
                        </div>
                    </div>

                    <!-- Review body -->
                    <div class="space-y-1.5">
                        <label for="review_body" class="block text-sm font-medium text-foreground">
                            Your Review <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="review_body"
                            v-model="form.body"
                            rows="4"
                            placeholder="Tell us about your experience..."
                            class="w-full resize-none rounded-xl border border-input bg-background px-4 py-2.5 text-sm text-foreground placeholder:text-muted-foreground transition focus:border-amber-400 focus:outline-none"
                        />
                        <div class="flex items-center justify-between">
                            <p v-if="form.errors.body" class="text-xs text-red-500">{{ form.errors.body }}</p>
                            <p class="ml-auto text-xs text-muted-foreground">{{ form.body.length }} / 2000</p>
                        </div>
                    </div>

                    <!-- Image upload -->
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-foreground">
                            Photo <span class="text-xs font-normal text-muted-foreground">(optional)</span>
                        </label>

                        <div v-if="imagePreview" class="relative inline-block">
                            <img
                                :src="imagePreview"
                                alt="Preview"
                                class="h-32 w-auto rounded-xl border border-border object-cover"
                            />
                            <button
                                type="button"
                                class="absolute -right-2 -top-2 rounded-full bg-red-600 p-1 text-white shadow transition hover:bg-red-500"
                                aria-label="Remove photo"
                                @click="removeImage"
                            >
                                <X class="h-3.5 w-3.5" />
                            </button>
                        </div>

                        <button
                            v-else
                            type="button"
                            class="flex items-center gap-2 rounded-xl border border-dashed border-border bg-muted/30 px-5 py-3 text-sm text-muted-foreground transition hover:border-amber-400/60 hover:text-amber-500"
                            :disabled="converting"
                            @click="fileInput?.click()"
                        >
                            <Loader2 v-if="converting" class="h-4 w-4 animate-spin" />
                            <ImagePlus v-else class="h-4 w-4" />
                            <span v-if="converting">Converting…</span>
                            <span v-else>Add a photo</span>
                        </button>

                        <input
                            ref="fileInput"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="handleImageChange"
                        />
                        <p v-if="form.errors.image" class="text-xs text-red-500">{{ form.errors.image }}</p>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="form.processing || converting || form.rating === 0"
                            class="rounded-xl bg-amber-400 px-7 py-2.5 text-sm font-bold text-slate-900 transition hover:bg-amber-300 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <span v-if="form.processing">Submitting…</span>
                            <span v-else>Submit Review</span>
                        </button>
                    </div>
                </form>
            </div>
        </template>
    </div>
</template>
