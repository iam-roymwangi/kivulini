<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { EventReview } from '@/types';

interface PaginatorLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface ReviewsPaginator {
    data: EventReview[];
    current_page: number;
    last_page: number;
    total: number;
    links: PaginatorLink[];
}

defineProps<{
    reviews: ReviewsPaginator;
}>();
</script>

<template>
    <section class="border-t border-border bg-muted/40 px-4 py-20 transition-colors md:px-8 lg:px-12">
        <div class="mx-auto max-w-7xl">
            <!-- Header -->
            <div class="mb-12 text-center">
                <p class="text-sm font-bold uppercase tracking-wider text-amber-500">Reviews</p>
                <h2 class="mt-2 text-3xl font-black text-foreground sm:text-4xl">
                    What Our Clients <span class="text-amber-500">Say</span>
                </h2>
                <p class="mx-auto mt-4 max-w-xl text-sm text-muted-foreground">
                    Honest feedback from adventurers who have travelled with us across Kenya and East Africa.
                </p>
            </div>

            <!-- Reviews grid -->
            <div v-if="reviews.data.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="review in reviews.data"
                    :key="review.id"
                    class="flex flex-col justify-between rounded-2xl border border-border bg-card p-6 shadow-xs transition-all duration-300 hover:-translate-y-1 hover:border-amber-400/30 hover:shadow-md"
                >
                    <div>
                        <!-- Stars -->
                        <div class="mb-4 flex items-center gap-0.5" :aria-label="`${review.rating} out of 5 stars`">
                            <svg
                                v-for="star in 5"
                                :key="star"
                                class="h-4 w-4"
                                :class="star <= review.rating ? 'text-amber-400' : 'text-border'"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                                aria-hidden="true"
                            >
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>

                        <blockquote class="text-sm leading-relaxed text-foreground/80">
                            "{{ review.body }}"
                        </blockquote>
                    </div>

                    <!-- Author -->
                    <footer class="mt-6 flex items-center gap-3 border-t border-border pt-4">
                        <div
                            class="h-10 w-10 shrink-0 overflow-hidden rounded-full border border-border bg-muted"
                            aria-hidden="true"
                        >
                            <img
                                v-if="review.image_url"
                                :src="review.image_url"
                                :alt="review.reviewer_name"
                                class="h-full w-full object-cover"
                            />
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center text-sm font-bold text-foreground"
                            >
                                {{ review.reviewer_name[0].toUpperCase() }}
                            </div>
                        </div>
                        <p class="text-sm font-bold text-foreground">{{ review.reviewer_name }}</p>
                    </footer>
                </article>
            </div>

            <!-- Empty state -->
            <div v-else class="rounded-2xl border border-border bg-card p-8 text-center">
                <p class="text-muted-foreground text-sm">No reviews yet. Be the first to share your experience after a trip.</p>
            </div>

            <!-- Pagination -->
            <div v-if="reviews.last_page > 1" class="mt-10 flex items-center justify-center gap-1.5">
                <template v-for="link in reviews.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="rounded-xl border border-border bg-card px-4 py-2 text-sm font-semibold transition-all hover:border-amber-400 hover:text-amber-500"
                        :class="link.active ? 'border-amber-400 bg-amber-400/10 text-amber-500 font-bold' : 'text-muted-foreground'"
                        preserve-scroll
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="cursor-default rounded-xl border border-border/40 bg-card/40 px-4 py-2 text-sm text-muted-foreground/45"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </section>
</template>
