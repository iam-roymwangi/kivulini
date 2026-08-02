<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import BookingBox from '@/components/events/BookingBox.vue';
import BookingDrawer from '@/components/events/BookingDrawer.vue';
import EventHeroGallery from '@/components/events/EventHeroGallery.vue';
import type { PlatformEvent } from '@/types';

const props = defineProps<{
    event: PlatformEvent;
    availableSlots: number;
}>();

const showBooking = ref(false);
const bookingQuantity = ref(1);

function openBooking(qty: number) {
    bookingQuantity.value = qty;
    showBooking.value = true;
}
</script>

<template>
    <Head>
        <title>{{ event.title }}</title>
        <meta name="description" :content="event.summary" />
    </Head>

    <div class="pt-24 pb-16">
        <!-- Hero gallery -->
        <div class="mx-auto mb-10 max-w-7xl px-4 md:px-8 lg:px-12">
            <EventHeroGallery :media="event.media" />
        </div>

        <!-- Content area -->
        <div class="mx-auto max-w-7xl px-4 md:px-8 lg:px-12">
            <div class="flex flex-col gap-10 lg:flex-row lg:items-start lg:gap-12">

                <!-- Left column: content -->
                <div class="min-w-0 flex-1 space-y-10">

                    <!-- Title & summary -->
                    <div>
                        <h1 class="mb-3 text-4xl font-black text-white md:text-5xl">
                            {{ event.title }}
                        </h1>
                        <p class="text-lg leading-relaxed text-slate-300">{{ event.summary }}</p>
                    </div>

                    <!-- Pickup location -->
                    <div v-if="event.pickup_location" class="flex items-start gap-3 rounded-xl border border-amber-400/20 bg-amber-400/5 p-4">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-amber-400">Pickup Location</p>
                            <p class="mt-1 text-slate-200">{{ event.pickup_location }}</p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <h2 class="mb-4 text-xl font-bold text-white">About this Trip</h2>
                        <!-- eslint-disable-next-line vue/no-v-html -->
                        <div class="prose prose-invert prose-slate max-w-none text-slate-300" v-html="event.description" />
                    </div>

                    <!-- Trip timeline placeholder (itinerary) -->
                    <div>
                        <h2 class="mb-6 text-xl font-bold text-white">Itinerary</h2>
                        <ol class="relative border-l border-slate-700 pl-6 space-y-8">
                            <li>
                                <div class="absolute -left-2 mt-1.5 h-4 w-4 rounded-full border-2 border-amber-400 bg-slate-950" />
                                <p class="text-xs font-semibold uppercase tracking-widest text-amber-400">
                                    {{ new Date(event.start_date).toLocaleDateString('en-KE', { weekday: 'long', day: 'numeric', month: 'long' }) }}
                                </p>
                                <p class="mt-1 text-slate-300">Departure from {{ event.pickup_location ?? event.location }}</p>
                            </li>
                            <li>
                                <div class="absolute -left-2 mt-1.5 h-4 w-4 rounded-full border-2 border-slate-600 bg-slate-950" />
                                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">
                                    {{ new Date(event.end_date).toLocaleDateString('en-KE', { weekday: 'long', day: 'numeric', month: 'long' }) }}
                                </p>
                                <p class="mt-1 text-slate-300">Return to {{ event.location }}</p>
                            </li>
                        </ol>
                    </div>

                    <!-- Organizer -->
                    <div class="flex items-center gap-4 rounded-xl border border-slate-700 bg-slate-900 p-5">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-amber-400 text-lg font-black text-slate-900">
                            K
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Organizer</p>
                            <p class="font-bold text-white">Kivulini Adventures</p>
                        </div>
                    </div>

                    <!-- Reviews placeholder -->
                    <div>
                        <h2 class="mb-4 text-xl font-bold text-white">Traveller Reviews</h2>
                        <div class="rounded-xl border border-slate-800 bg-slate-900 p-6 text-center">
                            <p class="text-slate-500">Reviews coming soon. Be the first to book and share your experience.</p>
                        </div>
                    </div>
                </div>

                <!-- Right column: sticky booking box -->
                <div class="w-full lg:sticky lg:top-28 lg:w-80 xl:w-96">
                    <BookingBox
                        :event="{ ...event, available_slots: availableSlots }"
                        @open-booking="openBooking"
                    />
                </div>
            </div>
        </div>
    </div>

    <!-- Booking drawer -->
    <BookingDrawer
        v-model:open="showBooking"
        :event="event"
        :initial-quantity="bookingQuantity"
    />
</template>
