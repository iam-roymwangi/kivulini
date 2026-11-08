<script setup lang="ts">
import { ref, computed } from 'vue';
import { store as storeBooking } from '@/routes/bookings';
import { csrfHeaders } from '@/lib/utils';
import type { BookingPayload, PlatformEvent } from '@/types';

const props = defineProps<{
    event: PlatformEvent;
    payload: BookingPayload;
}>();

const emit = defineEmits<{
    (e: 'initiated', payload: { reference: string; bookingId: number; paymentId: string }): void;
    (e: 'back'): void;
}>();

const mpesaPhone = ref(props.payload.contact_phone ?? '');
const submitting = ref(false);
const serverErrors = ref<string[]>([]);
const phoneError = ref('');

const unitPrice = computed(() => parseFloat(props.event.price));
const totalPrice = computed(() => unitPrice.value * props.payload.quantity);

const formatKES = (n: number) =>
    new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', maximumFractionDigits: 0 }).format(n);

const formattedUnit = computed(() => formatKES(unitPrice.value));
const formattedTotal = computed(() => formatKES(totalPrice.value));

const formattedDate = computed(() =>
    new Date(props.event.start_date).toLocaleDateString('en-KE', {
        day: 'numeric', month: 'long', year: 'numeric',
    }),
);

async function submit() {
    phoneError.value = '';
    serverErrors.value = [];

    if (!mpesaPhone.value.trim()) {
        phoneError.value = 'M-Pesa phone number is required.';
        return;
    }

    submitting.value = true;

    try {
        const res = await fetch(storeBooking.url(props.event.id), {
            method: 'POST',
            headers: csrfHeaders(),
            body: JSON.stringify({
                ...props.payload,
                payment_method: 'mpesa',
                mpesa_phone: mpesaPhone.value.trim(),
            }),
        });

        const json = await res.json();

        if (res.ok) {
            emit('initiated', {
                reference: json.booking_reference,
                bookingId: json.booking_id,
                paymentId: json.payment_id,
            });
        } else if (res.status === 422) {
            const errs = json.errors ?? {};
            const msgs = Object.values(errs).flat() as string[];
            serverErrors.value = msgs.length ? msgs : [json.message ?? 'Validation failed.'];
        } else {
            serverErrors.value = [json.message ?? 'Something went wrong. Please try again.'];
        }
    } catch {
        serverErrors.value = ['Network error. Please check your connection.'];
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <div class="space-y-6 px-1 pb-6">
        <div>
            <h2 class="text-xl font-bold text-foreground">Payment</h2>
            <p class="mt-1 text-sm text-muted-foreground">Review your order and pay with M-Pesa.</p>
        </div>

        <div class="space-y-3 rounded-2xl border border-border bg-muted/30 p-4">
            <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">Order Summary</p>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="font-semibold text-foreground">{{ event.title }}</span>
                </div>
                <div class="flex justify-between text-muted-foreground">
                    <span>Date</span>
                    <span>{{ formattedDate }}</span>
                </div>
                <div class="flex justify-between text-muted-foreground">
                    <span>Location</span>
                    <span>{{ event.location }}</span>
                </div>
                <div class="flex justify-between text-muted-foreground">
                    <span>Slots</span>
                    <span>{{ payload.quantity }}</span>
                </div>
                <div class="flex justify-between text-muted-foreground">
                    <span>Unit price</span>
                    <span>{{ formattedUnit }}</span>
                </div>
            </div>
            <div class="flex justify-between border-t border-border pt-3 font-bold">
                <span class="text-foreground">Total</span>
                <span class="text-lg text-amber-500">{{ formattedTotal }}</span>
            </div>
        </div>

        <div v-if="serverErrors.length > 0" class="rounded-xl border border-red-500/30 bg-red-500/10 p-4">
            <p v-for="(msg, i) in serverErrors" :key="i" class="text-sm text-red-400">{{ msg }}</p>
        </div>

        <div>
            <label for="mpesa_phone" class="mb-1.5 block text-sm font-medium text-foreground">
                M-Pesa phone number <span class="text-amber-500" aria-hidden="true">*</span>
            </label>
            <input
                id="mpesa_phone"
                v-model="mpesaPhone"
                type="tel"
                placeholder="+254 700 000 000"
                class="w-full rounded-xl border bg-background px-4 py-3 text-sm text-foreground placeholder-muted-foreground transition-colors focus:border-amber-400 focus:outline-none"
                :class="phoneError ? 'border-red-500' : 'border-border'"
            />
            <p v-if="phoneError" class="mt-1 text-xs text-red-400" role="alert">{{ phoneError }}</p>
            <p class="mt-2 text-xs text-muted-foreground">A prompt will be sent to this number. Use the Safaricom line registered to M-Pesa.</p>
        </div>

        <div class="flex gap-3 pt-2">
            <button
                type="button"
                :disabled="submitting"
                class="flex-1 rounded-xl border border-border py-3.5 text-sm font-bold text-muted-foreground transition-colors hover:text-foreground active:scale-95 disabled:opacity-50"
                @click="emit('back')"
            >
                Back
            </button>
            <button
                type="button"
                :disabled="submitting"
                class="flex-1 rounded-xl bg-amber-400 py-3.5 text-sm font-bold text-slate-900 transition-colors hover:bg-amber-300 active:scale-95 disabled:opacity-60"
                @click="submit"
            >
                {{ submitting ? 'Sending STK push...' : 'Pay with M-Pesa' }}
            </button>
        </div>
    </div>
</template>
