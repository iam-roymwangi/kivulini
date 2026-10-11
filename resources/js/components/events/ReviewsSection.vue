<script setup lang="ts">
interface Review {
    name: string;
    location: string;
    initials: string;
    text: string;
    rating: number;
}

const reviews: Review[] = [
    {
        name: 'Wanjiku N.',
        location: 'Nairobi, Kenya',
        initials: 'WN',
        rating: 5,
        text: 'The Mt. Longonot hike was perfectly organized. The guides were professional and the bonfire at night was magical.',
    },
    {
        name: 'David K.',
        location: 'Kisumu, Kenya',
        initials: 'DK',
        rating: 5,
        text: 'Kivulini makes booking trips genuinely simple. The digital consent forms and M-Pesa STK push payments are seamless.',
    },
    {
        name: 'Sarah M.',
        location: 'Mombasa, Kenya',
        initials: 'SM',
        rating: 5,
        text: 'An absolute 10/10 experience. I met amazing people and caught the most beautiful sunsets on the Rift Valley road trip.',
    },
    {
        name: 'John O.',
        location: 'Eldoret, Kenya',
        initials: 'JO',
        rating: 5,
        text: 'Highly recommend for anyone looking to explore Kenya in a group. Great vibe, safe environment, and well-planned routes.',
    },
    {
        name: 'Amina H.',
        location: 'Nakuru, Kenya',
        initials: 'AH',
        rating: 5,
        text: 'From the first message to the final campfire, every detail was taken care of. Already booked my next trip.',
    },
    {
        name: 'Brian T.',
        location: 'Thika, Kenya',
        initials: 'BT',
        rating: 5,
        text: 'The Aberdare hike was challenging in the best way. The crew was supportive and the scenery made every step worth it.',
    },
];
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
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="(review, idx) in reviews"
                    :key="idx"
                    class="flex flex-col justify-between rounded-2xl border border-border bg-card p-6 shadow-xs transition-all duration-300 hover:-translate-y-1 hover:border-amber-400/30 hover:shadow-md"
                >
                    <!-- Stars -->
                    <div>
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
                            "{{ review.text }}"
                        </blockquote>
                    </div>

                    <!-- Author -->
                    <footer class="mt-6 flex items-center gap-3 border-t border-border pt-4">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-border bg-muted text-sm font-bold text-foreground"
                            aria-hidden="true"
                        >
                            {{ review.initials }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-foreground">{{ review.name }}</p>
                            <p class="text-xs text-muted-foreground">{{ review.location }}</p>
                        </div>
                    </footer>
                </article>
            </div>
        </div>
    </section>
</template>
