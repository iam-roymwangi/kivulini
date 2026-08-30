<script setup lang="ts">
import { ref, computed } from 'vue';
import type { BookingPayload, PlatformEvent } from '@/types';

const props = defineProps<{
    event: PlatformEvent;
    payload: BookingPayload;
}>();

const emit = defineEmits<{
    (e: 'confirmed', reference: string, bookingId: number): void;
    (e: 'back'): void;
}>();

type PaymentMethod = 'mpesa' | 'card';
const paymentMethod = ref<PaymentMethod>('mpesa');
const mpesaPhone = ref('');
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

    if (paymentMethod.value === 'mpesa' && !mpesaPhone.value.trim()) {
        phoneError.value = 'M-Pesa phone number is required.';
        return;
    }

    submitting.value = true;

    try {
        const csrfMeta = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]');
        const csrf = csrfMeta?.content ?? '';

        const body = JSON.stringify({
            ...props.payload,
            payment_method: paymentMethod.value,
            mpesa_phone: paymentMethod.value === 'mpesa' ? mpesaPhone.value.trim() : undefined,
        });

        const res = await fetch(`/events/${props.event.id}/bookings`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Inertia': 'false',
            },
            body,
        });

        const json = await res.json();

        if (res.ok) {
            emit('confirmed', json.booking_reference, json.booking_id);
        } else if (res.status === 422) {
            // Validation errors
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
            <p class="mt-1 text-sm text-muted-foreground">Review your order and complete payment.</p>
        </div>

        <!-- Order summary -->
        <div class="rounded-2xl border border-border bg-muted/30 p-4 space-y-3">
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
            <div class="border-t border-border pt-3 flex justify-between font-bold">
                <span class="text-foreground">Total</span>
                <span class="text-lg text-amber-500">{{ formattedTotal }}</span>
            </div>
        </div>

        <!-- Server errors -->
        <div v-if="serverErrors.length > 0" class="rounded-xl border border-red-500/30 bg-red-500/10 p-4">
            <p v-for="(msg, i) in serverErrors" :key="i" class="text-sm text-red-400">{{ msg }}</p>
        </div>

        <!-- Payment method toggle -->
        <div>
            <p class="mb-3 text-sm font-semibold text-foreground">Payment method</p>
            <div class="grid grid-cols-2 gap-3">
                <button
                    v-for="method in [{ id: 'mpesa', label: 'M-Pesa' }, { id: 'card', label: 'Card' }]"
                    :key="method.id"
                    type="button"
                    class="rounded-xl border py-3 text-sm font-bold transition-all"
                    :class="paymentMethod === method.id
                        ? 'border-amber-400 bg-amber-400/10 text-amber-500'
                        : 'border-border text-muted-foreground hover:border-muted-foreground hover:text-foreground'"
                    @click="paymentMethod = method.id as PaymentMethod"
                >
                    {{ method.label }}
                </button>
            </div>
        </div>

        <!-- M-Pesa phone -->
        <div v-if="paymentMethod === 'mpesa'">
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
            <p class="mt-2 text-xs text-muted-foreground">An STK push will be sent to this number.</p>
        </div>

        <!-- Card placeholder -->
        <div v-else class="rounded-xl border border-border bg-muted/20 p-4 text-center text-sm text-muted-foreground">
            Card payments coming soon. Please use M-Pesa for now.
        </div>

        <!-- Actions -->
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
                {{ submitting ? 'Processing...' : 'Confirm & Pay' }}
            </button>
        </div>
    </div>
</template>
