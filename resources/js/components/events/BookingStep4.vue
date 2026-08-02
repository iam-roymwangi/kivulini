<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type { BookingPayload, PlatformEvent } from '@/types';

const props = defineProps<{
    event: PlatformEvent;
    payload: BookingPayload;
}>();

const emit = defineEmits<{
    (e: 'confirmed', reference: string): void;
    (e: 'back'): void;
}>();

type PaymentMethod = 'mpesa' | 'card';
const paymentMethod = ref<PaymentMethod>('mpesa');
const mpesaPhone = ref('');

const unitPrice = computed(() => parseFloat(props.event.price));
const totalPrice = computed(() => unitPrice.value * props.payload.quantity);

const formatKES = (amount: number) =>
    new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', maximumFractionDigits: 0 }).format(amount);

const formattedUnit = computed(() => formatKES(unitPrice.value));
const formattedTotal = computed(() => formatKES(totalPrice.value));
const formattedDate = computed(() =>
    new Date(props.event.start_date).toLocaleDateString('en-KE', {
        day: 'numeric', month: 'long', year: 'numeric',
    }),
);

const phoneError = ref('');

// Use Inertia useForm for submission — endpoint built from event id
const form = useForm({});

function submit() {
    phoneError.value = '';

    if (paymentMethod.value === 'mpesa' && !mpesaPhone.value.trim()) {
        phoneError.value = 'M-Pesa phone number is required.';
        return;
    }

    const data = {
        ...props.payload,
        payment_method: paymentMethod.value,
        mpesa_phone: paymentMethod.value === 'mpesa' ? mpesaPhone.value.trim() : undefined,
    };

    form.transform(() => data).post(`/events/${props.event.id}/bookings`, {
        preserveScroll: true,
        onSuccess: (page) => {
            const flash = page.props.flash as Record<string, unknown> | undefined;
            const ref = (flash?.booking_reference as string) ?? '';
            emit('confirmed', ref);
        },
    });
}
</script>

<template>
    <div class="space-y-6 px-1 pb-4">
        <div>
            <h2 class="text-xl font-bold text-white">Payment</h2>
            <p class="mt-1 text-sm text-slate-400">Review your order and complete payment.</p>
        </div>

        <!-- Order summary -->
        <div class="rounded-xl border border-slate-700 bg-slate-900 p-4 space-y-3">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Order Summary</p>
            <div class="space-y-1.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-300">{{ event.title }}</span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Date</span>
                    <span>{{ formattedDate }}</span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Quantity</span>
                    <span>{{ payload.quantity }} seat{{ payload.quantity === 1 ? '' : 's' }}</span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Unit price</span>
                    <span>{{ formattedUnit }}</span>
                </div>
            </div>
            <div class="border-t border-slate-700 pt-3 flex justify-between font-bold">
                <span class="text-white">Total</span>
                <span class="text-amber-400 text-lg">{{ formattedTotal }}</span>
            </div>
        </div>

        <!-- Server errors -->
        <div v-if="Object.keys(form.errors).length > 0" class="rounded-xl border border-red-500/30 bg-red-500/10 p-4">
            <p v-for="(msg, field) in form.errors" :key="field" class="text-sm text-red-400">{{ msg }}</p>
        </div>

        <!-- Payment method toggle -->
        <div>
            <p class="mb-3 text-sm font-medium text-slate-300">Payment method</p>
            <div class="flex gap-3">
                <button
                    v-for="method in [{ id: 'mpesa', label: 'M-Pesa' }, { id: 'card', label: 'Card' }]"
                    :key="method.id"
                    type="button"
                    class="flex-1 rounded-xl border py-3 text-sm font-bold transition-all"
                    :class="paymentMethod === method.id
                        ? 'border-amber-400 bg-amber-400/10 text-amber-400'
                        : 'border-slate-700 text-slate-400 hover:border-slate-500 hover:text-slate-200'"
                    @click="paymentMethod = method.id as PaymentMethod"
                >
                    {{ method.label }}
                </button>
            </div>
        </div>

        <!-- M-Pesa phone input -->
        <div v-if="paymentMethod === 'mpesa'">
            <label for="mpesa_phone" class="mb-1.5 block text-sm font-medium text-slate-300">
                M-Pesa phone number <span class="text-amber-400" aria-hidden="true">*</span>
            </label>
            <input
                id="mpesa_phone"
                v-model="mpesaPhone"
                type="tel"
                placeholder="+254 700 000 000"
                class="w-full rounded-xl border bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 transition-colors focus:border-amber-400 focus:outline-none"
                :class="phoneError ? 'border-red-500' : 'border-slate-700'"
                :aria-invalid="!!phoneError"
                aria-describedby="err-mpesa"
            />
            <p v-if="phoneError" id="err-mpesa" class="mt-1 text-xs text-red-400" role="alert">
                {{ phoneError }}
            </p>
            <p class="mt-2 text-xs text-slate-500">
                You will receive an STK push prompt on this number.
            </p>
        </div>

        <!-- Card placeholder -->
        <div v-else class="rounded-xl border border-slate-700 bg-slate-900 p-4 text-center text-sm text-slate-500">
            Card payment coming soon. Please use M-Pesa for now.
        </div>

        <div class="flex gap-3 pt-2">
            <button
                type="button"
                :disabled="form.processing"
                class="flex-1 rounded-xl border border-slate-700 py-3.5 text-sm font-bold text-slate-300 transition-colors hover:border-slate-500 hover:text-white active:scale-95 disabled:opacity-50"
                @click="emit('back')"
            >
                Back
            </button>
            <button
                type="button"
                :disabled="form.processing"
                class="flex-1 rounded-xl bg-amber-400 py-3.5 text-sm font-bold text-slate-900 transition-colors hover:bg-amber-300 active:scale-95 disabled:opacity-60"
                @click="submit"
            >
                <span v-if="form.processing">Processing...</span>
                <span v-else>Confirm &amp; Pay</span>
            </button>
        </div>
    </div>
</template>
