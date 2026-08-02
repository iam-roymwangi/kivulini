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
    emergency_contact_name: props.initialData?.emergency_contact_name ?? '',
    emergency_contact_phone: props.initialData?.emergency_contact_phone ?? '',
});

const errors = reactive<Record<string, string>>({});

const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

function validate(): boolean {
    // Clear previous errors
    Object.keys(errors).forEach((k) => delete errors[k]);

    if (!form.contact_name.trim()) { errors.contact_name = 'Full name is required.'; }
    if (!form.contact_phone.trim()) { errors.contact_phone = 'Phone number is required.'; }
    if (!form.contact_email.trim()) {
        errors.contact_email = 'Email address is required.';
    } else if (!emailRegex.test(form.contact_email)) {
        errors.contact_email = 'Enter a valid email address.';
    }
    if (!form.emergency_contact_name.trim()) { errors.emergency_contact_name = 'Emergency contact name is required.'; }
    if (!form.emergency_contact_phone.trim()) { errors.emergency_contact_phone = 'Emergency contact phone is required.'; }

    return Object.keys(errors).length === 0;
}

function submit() {
    if (!validate()) { return; }
    emit('next', { ...form });
}
</script>

<template>
    <div class="space-y-6 px-1 pb-4">
        <div>
            <h2 class="text-xl font-bold text-white">Contact Details</h2>
            <p class="mt-1 text-sm text-slate-400">We need a few details to confirm your booking.</p>
        </div>

        <form class="space-y-4" novalidate @submit.prevent="submit">
            <!-- Full Name -->
            <div>
                <label for="contact_name" class="mb-1.5 block text-sm font-medium text-slate-300">
                    Full Name <span class="text-amber-400" aria-hidden="true">*</span>
                </label>
                <input
                    id="contact_name"
                    v-model="form.contact_name"
                    type="text"
                    autocomplete="name"
                    class="w-full rounded-xl border bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 transition-colors focus:border-amber-400 focus:outline-none"
                    :class="errors.contact_name ? 'border-red-500' : 'border-slate-700'"
                    placeholder="Jane Doe"
                    :aria-invalid="!!errors.contact_name"
                    :aria-describedby="errors.contact_name ? 'err-contact_name' : undefined"
                />
                <p v-if="errors.contact_name" id="err-contact_name" class="mt-1 text-xs text-red-400" role="alert">
                    {{ errors.contact_name }}
                </p>
            </div>

            <!-- Phone -->
            <div>
                <label for="contact_phone" class="mb-1.5 block text-sm font-medium text-slate-300">
                    Phone Number <span class="text-amber-400" aria-hidden="true">*</span>
                </label>
                <input
                    id="contact_phone"
                    v-model="form.contact_phone"
                    type="tel"
                    autocomplete="tel"
                    class="w-full rounded-xl border bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 transition-colors focus:border-amber-400 focus:outline-none"
                    :class="errors.contact_phone ? 'border-red-500' : 'border-slate-700'"
                    placeholder="+254 700 000 000"
                    :aria-invalid="!!errors.contact_phone"
                    :aria-describedby="errors.contact_phone ? 'err-contact_phone' : undefined"
                />
                <p v-if="errors.contact_phone" id="err-contact_phone" class="mt-1 text-xs text-red-400" role="alert">
                    {{ errors.contact_phone }}
                </p>
            </div>

            <!-- Email -->
            <div>
                <label for="contact_email" class="mb-1.5 block text-sm font-medium text-slate-300">
                    Email Address <span class="text-amber-400" aria-hidden="true">*</span>
                </label>
                <input
                    id="contact_email"
                    v-model="form.contact_email"
                    type="email"
                    autocomplete="email"
                    class="w-full rounded-xl border bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 transition-colors focus:border-amber-400 focus:outline-none"
                    :class="errors.contact_email ? 'border-red-500' : 'border-slate-700'"
                    placeholder="jane@example.com"
                    :aria-invalid="!!errors.contact_email"
                    :aria-describedby="errors.contact_email ? 'err-contact_email' : undefined"
                />
                <p v-if="errors.contact_email" id="err-contact_email" class="mt-1 text-xs text-red-400" role="alert">
                    {{ errors.contact_email }}
                </p>
            </div>

            <!-- Emergency contact section -->
            <div class="rounded-xl border border-slate-700 p-4 space-y-4">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Emergency Contact</p>

                <div>
                    <label for="emergency_contact_name" class="mb-1.5 block text-sm font-medium text-slate-300">
                        Name <span class="text-amber-400" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="emergency_contact_name"
                        v-model="form.emergency_contact_name"
                        type="text"
                        autocomplete="off"
                        class="w-full rounded-xl border bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 transition-colors focus:border-amber-400 focus:outline-none"
                        :class="errors.emergency_contact_name ? 'border-red-500' : 'border-slate-700'"
                        placeholder="John Doe"
                        :aria-invalid="!!errors.emergency_contact_name"
                        :aria-describedby="errors.emergency_contact_name ? 'err-ecname' : undefined"
                    />
                    <p v-if="errors.emergency_contact_name" id="err-ecname" class="mt-1 text-xs text-red-400" role="alert">
                        {{ errors.emergency_contact_name }}
                    </p>
                </div>

                <div>
                    <label for="emergency_contact_phone" class="mb-1.5 block text-sm font-medium text-slate-300">
                        Phone <span class="text-amber-400" aria-hidden="true">*</span>
                    </label>
                    <input
                        id="emergency_contact_phone"
                        v-model="form.emergency_contact_phone"
                        type="tel"
                        autocomplete="off"
                        class="w-full rounded-xl border bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 transition-colors focus:border-amber-400 focus:outline-none"
                        :class="errors.emergency_contact_phone ? 'border-red-500' : 'border-slate-700'"
                        placeholder="+254 700 000 001"
                        :aria-invalid="!!errors.emergency_contact_phone"
                        :aria-describedby="errors.emergency_contact_phone ? 'err-ecphone' : undefined"
                    />
                    <p v-if="errors.emergency_contact_phone" id="err-ecphone" class="mt-1 text-xs text-red-400" role="alert">
                        {{ errors.emergency_contact_phone }}
                    </p>
                </div>
            </div>

            <button
                type="submit"
                class="w-full rounded-xl bg-amber-400 py-3.5 text-sm font-bold text-slate-900 transition-colors hover:bg-amber-300 active:scale-95"
            >
                Continue
            </button>
        </form>
    </div>
</template>
