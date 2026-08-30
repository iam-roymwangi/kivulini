<script setup lang="ts">
import { reactive } from 'vue';
import type { BookingPayload } from '@/types';

const props = defineProps<{
    initialData?: Partial<BookingPayload>;
}>();

const emit = defineEmits<{
    (e: 'next', data: Partial<BookingPayload>): void;
}>();

const form = reactive({
    contact_name: props.initialData?.contact_name ?? '',
    contact_email: props.initialData?.contact_email ?? '',
    contact_phone: props.initialData?.contact_phone ?? '',
});

const errors = reactive<Record<string, string>>({});
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

function validate(): boolean {
    Object.keys(errors).forEach((k) => delete errors[k]);
    if (!form.contact_name.trim()) { errors.contact_name = 'Full name is required.'; }
    if (!form.contact_phone.trim()) { errors.contact_phone = 'Phone number is required.'; }
    if (!form.contact_email.trim()) { errors.contact_email = 'Email address is required.'; }
    else if (!emailRegex.test(form.contact_email)) { errors.contact_email = 'Enter a valid email address.'; }
    return Object.keys(errors).length === 0;
}

function submit() {
    if (!validate()) { return; }
    emit('next', { ...form });
}
</script>

<template>
    <div class="space-y-5 pb-4">
        <div>
            <h2 class="text-lg font-bold text-foreground">Your Contact Details</h2>
            <p class="mt-1 text-sm text-muted-foreground">We'll use this to send your booking confirmation.</p>
        </div>

        <form class="space-y-4" novalidate @submit.prevent="submit">
            <div>
                <label for="contact_name" class="form-label">Full Name <span class="text-amber-500">*</span></label>
                <input id="contact_name" v-model="form.contact_name" type="text" autocomplete="name" class="form-input" :class="errors.contact_name ? 'border-red-500' : ''" placeholder="Jane Doe" />
                <p v-if="errors.contact_name" class="form-error" role="alert">{{ errors.contact_name }}</p>
            </div>

            <div>
                <label for="contact_phone" class="form-label">Phone Number <span class="text-amber-500">*</span></label>
                <input id="contact_phone" v-model="form.contact_phone" type="tel" autocomplete="tel" class="form-input" :class="errors.contact_phone ? 'border-red-500' : ''" placeholder="+254 700 000 000" />
                <p v-if="errors.contact_phone" class="form-error" role="alert">{{ errors.contact_phone }}</p>
            </div>

            <div>
                <label for="contact_email" class="form-label">Email Address <span class="text-amber-500">*</span></label>
                <input id="contact_email" v-model="form.contact_email" type="email" autocomplete="email" class="form-input" :class="errors.contact_email ? 'border-red-500' : ''" placeholder="jane@example.com" />
                <p v-if="errors.contact_email" class="form-error" role="alert">{{ errors.contact_email }}</p>
            </div>

            <button type="submit" class="btn-primary w-full">Continue</button>
        </form>
    </div>
</template>

<style scoped>
@reference "../../../css/app.css";
.form-label { @apply mb-1.5 block text-sm font-medium text-foreground; }
.form-input { @apply w-full rounded-xl border border-border bg-background px-4 py-3 text-sm text-foreground placeholder:text-muted-foreground focus:border-amber-400 focus:outline-none transition-colors; }
.form-error { @apply mt-1 text-xs text-red-500; }
.btn-primary { @apply rounded-xl bg-amber-400 py-3.5 text-sm font-bold text-slate-900 transition-colors hover:bg-amber-300 active:scale-95; }
</style>
