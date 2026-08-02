<script setup lang="ts">
import { ref } from 'vue';
import type { BookingPayload } from '@/types';

defineProps<{
    waiverText: string;
}>();

const emit = defineEmits<{
    (e: 'next', data: Partial<BookingPayload>): void;
    (e: 'back'): void;
}>();

const agreed = ref(false);
const signerName = ref('');
const errors = ref<{ agreed?: string; signerName?: string }>({});

function validate(): boolean {
    errors.value = {};
    if (!agreed.value) { errors.value.agreed = 'You must accept the terms to continue.'; }
    if (!signerName.value.trim()) { errors.value.signerName = 'Please type your full name to confirm.'; }
    return Object.keys(errors.value).length === 0;
}

function submit() {
    if (!validate()) { return; }
    emit('next', {
        consent: { agreed: agreed.value, signer_name: signerName.value.trim() },
    });
}
</script>

<template>
    <div class="space-y-6 px-1 pb-4">
        <div>
            <h2 class="text-xl font-bold text-white">Consent & Liability</h2>
            <p class="mt-1 text-sm text-slate-400">Please read and accept the waiver to proceed.</p>
        </div>

        <form class="space-y-5" novalidate @submit.prevent="submit">
            <!-- Scrollable waiver -->
            <div
                class="max-h-64 overflow-y-auto rounded-xl border border-slate-700 bg-slate-900 p-4 text-sm leading-relaxed text-slate-300"
                tabindex="0"
                aria-label="Liability waiver text"
            >
                <pre class="whitespace-pre-wrap font-sans">{{ waiverText }}</pre>
            </div>

            <!-- Checkbox -->
            <div>
                <label class="flex cursor-pointer items-start gap-3">
                    <input
                        v-model="agreed"
                        type="checkbox"
                        class="mt-0.5 h-4 w-4 shrink-0 accent-amber-400"
                        :aria-invalid="!!errors.agreed"
                        aria-describedby="err-agreed"
                    />
                    <span class="text-sm text-slate-300">
                        I accept the terms and liability release
                    </span>
                </label>
                <p v-if="errors.agreed" id="err-agreed" class="mt-1 text-xs text-red-400" role="alert">
                    {{ errors.agreed }}
                </p>
            </div>

            <!-- Digital signature field -->
            <div>
                <label for="signer_name" class="mb-1.5 block text-sm font-medium text-slate-300">
                    Type your full name to confirm
                    <span class="text-amber-400" aria-hidden="true"> *</span>
                </label>
                <input
                    id="signer_name"
                    v-model="signerName"
                    type="text"
                    autocomplete="name"
                    placeholder="Your full name"
                    class="w-full rounded-xl border bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 transition-colors focus:border-amber-400 focus:outline-none"
                    :class="errors.signerName ? 'border-red-500' : 'border-slate-700'"
                    :aria-invalid="!!errors.signerName"
                    aria-describedby="err-signer"
                />
                <p v-if="errors.signerName" id="err-signer" class="mt-1 text-xs text-red-400" role="alert">
                    {{ errors.signerName }}
                </p>
            </div>

            <div class="flex gap-3 pt-2">
                <button
                    type="button"
                    class="flex-1 rounded-xl border border-slate-700 py-3.5 text-sm font-bold text-slate-300 transition-colors hover:border-slate-500 hover:text-white active:scale-95"
                    @click="emit('back')"
                >
                    Back
                </button>
                <button
                    type="submit"
                    class="flex-1 rounded-xl bg-amber-400 py-3.5 text-sm font-bold text-slate-900 transition-colors hover:bg-amber-300 active:scale-95"
                >
                    Continue
                </button>
            </div>
        </form>
    </div>
</template>
