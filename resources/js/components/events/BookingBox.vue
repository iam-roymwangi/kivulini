<script setup lang="ts">
import { ref, computed } from 'vue';
import type { PlatformEvent } from '@/types';

const props = defineProps<{
    event: PlatformEvent;
}>();

const emit = defineEmits<{
    (e: 'open-booking', quantity: number): void;
}>();

const quantity = ref(1);

const isPublished = computed(() => props.event.status === 'published');
const isSoldOut = computed(() => props.event.available_slots === 0);

const unitPrice = computed(() => parseFloat(props.event.price));
const totalPrice = computed(() => unitPrice.value * quantity.value);

const maxQuantity = computed(() => Math.min(props.event.available_slots, 10));

const formattedUnit = computed(() =>
    new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', maximumFractionDigits: 0 }).format(unitPrice.value),
);

const formattedTotal = computed(() =>
    new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', maximumFractionDigits: 0 }).format(totalPrice.value),
);

const formattedDate = computed(() =>
    new Date(props.event.start_date).toLocaleDateString('en-KE', {
        weekday: 'short',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }),
);

function proceedToBooking() {
    if (!isPublished.value || isSoldOut.value) { return; }
    emit('open-booking', quantity.value);
}
</script>

<template>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg transition-colors dark:border-slate-700 dark:bg-slate-900 dark:shadow-2xl">
        <h3 class="mb-4 text-lg font-bold text-slate-900 dark:text-white">Book Your Slot</h3>

        <!-- Date -->
        <div class="mb-4 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
            <svg class="h-4 w-4 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>{{ formattedDate }}</span>
        </div>

        <!-- Seat counter -->
        <div class="mb-5">
            <p v-if="!isSoldOut" class="text-sm text-slate-500 dark:text-slate-400">
                <span class="font-bold text-slate-900 dark:text-white">{{ event.available_slots }}</span>
                slot{{ event.available_slots === 1 ? '' : 's' }} remaining
            </p>
            <p v-else class="text-sm font-bold text-red-500">Sold Out</p>
        </div>

        <!-- Quantity selector -->
        <div v-if="!isSoldOut && isPublished" class="mb-5">
            <label for="booking-quantity" class="mb-2 block text-sm font-medium text-slate-600 dark:text-slate-300">
                Number of slots
            </label>
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-full border border-slate-300 text-slate-600 transition hover:border-amber-400 hover:text-amber-400 dark:border-slate-600 dark:text-slate-300 disabled:opacity-40"
                    :disabled="quantity <= 1"
                    aria-label="Decrease quantity"
                    @click="quantity = Math.max(1, quantity - 1)"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                </button>
                <span id="booking-quantity" class="w-8 text-center text-lg font-bold text-slate-900 dark:text-white">{{ quantity }}</span>
                <button
                    type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-full border border-slate-300 text-slate-600 transition hover:border-amber-400 hover:text-amber-400 dark:border-slate-600 dark:text-slate-300 disabled:opacity-40"
                    :disabled="quantity >= maxQuantity"
                    aria-label="Increase quantity"
                    @click="quantity = Math.min(maxQuantity, quantity + 1)"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Price breakdown -->
        <div class="mb-6 space-y-2 border-t border-slate-200 pt-4 dark:border-slate-700">
            <div class="flex justify-between text-sm text-slate-500 dark:text-slate-400">
                <span>{{ formattedUnit }} × {{ quantity }}</span>
                <span class="text-slate-900 dark:text-white">{{ formattedTotal }}</span>
            </div>
            <div class="flex justify-between font-bold text-slate-900 dark:text-white">
                <span>Total</span>
                <span class="text-lg text-amber-500 dark:text-amber-400">{{ formattedTotal }}</span>
            </div>
        </div>

        <!-- CTA -->
        <button
            v-if="isPublished"
            type="button"
            class="w-full rounded-xl py-3.5 text-sm font-bold transition-all active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
            :class="isSoldOut
                ? 'bg-slate-700 text-slate-500'
                : 'bg-amber-400 text-slate-900 hover:bg-amber-300'"
            :disabled="isSoldOut"
            @click="proceedToBooking"
        >
            {{ isSoldOut ? 'Sold Out' : 'Proceed to Booking' }}
        </button>
    </div>
</template>
