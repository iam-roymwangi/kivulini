<script setup lang="ts">
import type { PlatformEvent } from '@/types';

const props = defineProps<{
    bookingReference: string;
    event: PlatformEvent;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const passUrl = `/bookings/${props.bookingReference}/pass`;

const formattedDate = new Date(props.event.start_date).toLocaleDateString('en-KE', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});
</script>

<template>
    <div class="flex flex-col items-center gap-6 px-1 pb-4 text-center">
        <!-- Success icon -->
        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-amber-400/10 ring-4 ring-amber-400/30">
            <svg class="h-10 w-10 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <div class="space-y-1">
            <h2 class="text-2xl font-black text-white">You're In.</h2>
            <p class="text-slate-400">Your seat is confirmed. Get ready.</p>
        </div>

        <!-- Booking reference -->
        <div class="w-full rounded-2xl border border-slate-700 bg-slate-900 p-5">
            <p class="mb-1 text-xs font-semibold uppercase tracking-widest text-slate-500">Booking Reference</p>
            <p class="text-2xl font-black tracking-widest text-amber-400">
                {{ bookingReference }}
            </p>
        </div>

        <!-- Event summary -->
        <div class="w-full space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-slate-500">Event</span>
                <span class="font-semibold text-white text-right max-w-[60%]">{{ event.title }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Date</span>
                <span class="text-white">{{ formattedDate }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Location</span>
                <span class="text-white">{{ event.location }}</span>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex w-full flex-col gap-3">
            <a
                :href="passUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-amber-400 py-3.5 text-sm font-bold text-slate-900 transition-colors hover:bg-amber-300 active:scale-95"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Download Your Pass
            </a>

            <button
                type="button"
                class="w-full rounded-xl border border-slate-700 py-3 text-sm font-bold text-slate-300 transition-colors hover:border-slate-500 hover:text-white"
                @click="emit('close')"
            >
                Done
            </button>
        </div>
    </div>
</template>
