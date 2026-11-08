<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { store as storeOrder } from '@/routes/merchandise/orders';
import { csrfHeaders } from '@/lib/utils';
import type { MerchandiseItem } from '@/types/merchandise';
import MpesaStkStatus from '@/components/payments/MpesaStkStatus.vue';

const props = defineProps<{
    merchandise: MerchandiseItem;
    size: string;
    color: string;
}>();

const form = reactive({
    contact_name: '',
    contact_email: '',
    contact_phone: '',
    mpesa_phone: '',
    quantity: 1,
});

const errors = reactive<Record<string, string>>({});
const serverErrors = ref<string[]>([]);
const submitting = ref(false);
const paymentId = ref('');
const orderReference = ref('');
const paid = ref(false);
const failedMessage = ref('');

const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const maxQuantity = computed(() => Math.max(1, props.merchandise.stock));
const unitPrice = computed(() => Number(props.merchandise.price));
const totalPrice = computed(() => unitPrice.value * form.quantity);

const formatKES = (n: number) =>
    new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', maximumFractionDigits: 0 }).format(n);

function validate(): boolean {
    Object.keys(errors).forEach((k) => delete errors[k]);

    if (!form.contact_name.trim()) {
        errors.contact_name = 'Full name is required.';
    }
    if (!form.contact_email.trim()) {
        errors.contact_email = 'Email address is required.';
    } else if (!emailRegex.test(form.contact_email)) {
        errors.contact_email = 'Enter a valid email address.';
    }
    if (!form.contact_phone.trim()) {
        errors.contact_phone = 'Phone number is required.';
    }
    if (!form.mpesa_phone.trim()) {
        errors.mpesa_phone = 'M-Pesa phone number is required.';
    }
    if (form.quantity < 1 || form.quantity > maxQuantity.value) {
        errors.quantity = `Choose between 1 and ${maxQuantity.value}.`;
    }

    return Object.keys(errors).length === 0;
}

async function submit(): Promise<void> {
    serverErrors.value = [];
    failedMessage.value = '';

    if (!validate()) {
        return;
    }

    submitting.value = true;

    try {
        const res = await fetch(storeOrder.url(props.merchandise.id), {
            method: 'POST',
            headers: csrfHeaders(),
            body: JSON.stringify({
                contact_name: form.contact_name.trim(),
                contact_email: form.contact_email.trim(),
                contact_phone: form.contact_phone.trim(),
                quantity: form.quantity,
                size: props.size || undefined,
                color: props.color || undefined,
                payment_method: 'mpesa',
                mpesa_phone: form.mpesa_phone.trim(),
            }),
        });

        const json = await res.json();

        if (res.ok) {
            orderReference.value = json.order_reference;
            paymentId.value = json.payment_id;
            return;
        }

        if (res.status === 422) {
            const fieldErrors = json.errors ?? {};
            const msgs = Object.values(fieldErrors).flat() as string[];
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

function handlePaid(payload: { reference: string | null }): void {
    paid.value = true;
    if (payload.reference) {
        orderReference.value = payload.reference;
    }
}

function handleFailed(message: string): void {
    paymentId.value = '';
    failedMessage.value = message;
}
</script>

<template>
    <div class="rounded-2xl border border-border bg-card p-5">
        <div v-if="paid" class="space-y-3 text-center">
            <h3 class="text-lg font-bold text-foreground">Payment received</h3>
            <p class="text-sm text-muted-foreground">
                Order <span class="font-semibold text-amber-500">{{ orderReference }}</span> is confirmed. We'll use your contact details for delivery.
            </p>
        </div>

        <MpesaStkStatus
            v-else-if="paymentId"
            :payment-id="paymentId"
            pending-title="Confirm on your phone"
            pending-description="Enter your M-Pesa PIN. This order is confirmed only after payment succeeds."
            @completed="handlePaid"
            @failed="handleFailed"
        />

        <form v-else class="space-y-4" novalidate @submit.prevent="submit">
            <div>
                <h3 class="text-lg font-bold text-foreground">Pay with M-Pesa</h3>
                <p class="mt-1 text-sm text-muted-foreground">We'll send a prompt to your Safaricom number.</p>
            </div>

            <div v-if="serverErrors.length > 0" class="rounded-xl border border-red-500/30 bg-red-500/10 p-4">
                <p v-for="(msg, i) in serverErrors" :key="i" class="text-sm text-red-400">{{ msg }}</p>
            </div>
            <p v-if="failedMessage" class="text-sm text-red-400" role="alert">{{ failedMessage }}</p>

            <div>
                <label for="merch_contact_name" class="mb-1.5 block text-sm font-medium text-foreground">Full name</label>
                <input id="merch_contact_name" v-model="form.contact_name" type="text" autocomplete="name" class="w-full rounded-xl border border-border bg-background px-4 py-3 text-sm" :class="errors.contact_name ? 'border-red-500' : ''" />
                <p v-if="errors.contact_name" class="mt-1 text-xs text-red-500">{{ errors.contact_name }}</p>
            </div>
            <div>
                <label for="merch_contact_email" class="mb-1.5 block text-sm font-medium text-foreground">Email</label>
                <input id="merch_contact_email" v-model="form.contact_email" type="email" autocomplete="email" class="w-full rounded-xl border border-border bg-background px-4 py-3 text-sm" :class="errors.contact_email ? 'border-red-500' : ''" />
                <p v-if="errors.contact_email" class="mt-1 text-xs text-red-500">{{ errors.contact_email }}</p>
            </div>
            <div>
                <label for="merch_contact_phone" class="mb-1.5 block text-sm font-medium text-foreground">Phone</label>
                <input id="merch_contact_phone" v-model="form.contact_phone" type="tel" autocomplete="tel" class="w-full rounded-xl border border-border bg-background px-4 py-3 text-sm" :class="errors.contact_phone ? 'border-red-500' : ''" />
                <p v-if="errors.contact_phone" class="mt-1 text-xs text-red-500">{{ errors.contact_phone }}</p>
            </div>
            <div>
                <label for="merch_mpesa_phone" class="mb-1.5 block text-sm font-medium text-foreground">M-Pesa number</label>
                <input id="merch_mpesa_phone" v-model="form.mpesa_phone" type="tel" class="w-full rounded-xl border border-border bg-background px-4 py-3 text-sm" :class="errors.mpesa_phone ? 'border-red-500' : ''" placeholder="07xx xxx xxx" />
                <p v-if="errors.mpesa_phone" class="mt-1 text-xs text-red-500">{{ errors.mpesa_phone }}</p>
            </div>
            <div>
                <label for="merch_quantity" class="mb-1.5 block text-sm font-medium text-foreground">Quantity</label>
                <input id="merch_quantity" v-model.number="form.quantity" type="number" min="1" :max="maxQuantity" class="w-full rounded-xl border border-border bg-background px-4 py-3 text-sm" :class="errors.quantity ? 'border-red-500' : ''" />
                <p v-if="errors.quantity" class="mt-1 text-xs text-red-500">{{ errors.quantity }}</p>
            </div>

            <div class="flex items-center justify-between border-t border-border pt-3 text-sm font-bold">
                <span>Total</span>
                <span class="text-amber-500">{{ formatKES(totalPrice) }}</span>
            </div>

            <button
                type="submit"
                :disabled="submitting"
                class="w-full rounded-xl bg-amber-400 py-3.5 text-sm font-bold text-slate-900 transition-colors hover:bg-amber-300 disabled:opacity-60"
            >
                {{ submitting ? 'Sending prompt...' : 'Pay with M-Pesa' }}
            </button>
        </form>
    </div>
</template>
