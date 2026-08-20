<script setup lang="ts">
import { ref } from 'vue';
import type { BookingPayload } from '@/types';

const props = defineProps<{
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
    if (!signerName.value.trim()) { errors.value.signerName = 'Please type your full name to sign.'; }
    return Object.keys(errors.value).length === 0;
}

function submit() {
    if (!validate()) { return; }
    emit('next', { consent: { agreed: true, signer_name: signerName.value.trim() } });
}
</script>

<template>
    <div class="space-y-5 pb-4">
        <div>
            <h2 class="text-lg font-bold text-foreground">Consent & Liability</h2>
            <p class="mt-1 text-sm text-muted-foreground">Read carefully and sign to proceed with your booking.</p>
        </div>

        <form class="space-y-5" novalidate @submit.prevent="submit">
            <!-- Scrollable waiver -->
            <div
                class="max-h-56 overflow-y-auto rounded-2xl border border-border bg-muted/20 p-4"
                tabindex="0"
                aria-label="Liability waiver"
            >
                <pre class="whitespace-pre-wrap font-sans text-sm leading-relaxed text-foreground/80">{{ waiverText }}</pre>
            </div>

            <!-- Acceptance checkbox -->
            <div>
                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-border p-4 transition-colors hover:bg-muted/20" :class="agreed ? 'border-amber-400/50 bg-amber-400/5' : ''">
                    <div class="relative mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center">
                        <input v-model="agreed" type="checkbox" class="h-5 w-5 cursor-pointer accent-amber-400" />
                    </div>
                    <span class="text-sm leading-relaxed text-foreground">
                        I have read, understood, and agree to the <span class="font-semibold">terms and liability release</span> outlined above.
                    </span>
                </label>
                <p v-if="errors.agreed" class="mt-1 text-xs text-red-500" role="alert">{{ errors.agreed }}</p>
            </div>

            <!-- Digital signature -->
            <div>
                <label for="signer_name" class="mb-1.5 block text-sm font-medium text-foreground">
                    Type your full name as a digital signature <span class="text-amber-500">*</span>
                </label>
                <input
                    id="signer_name"
                    v-model="signerName"
                    type="text"
                    autocomplete="name"
                    placeholder="Your full legal name"
                    class="w-full rounded-xl border bg-background px-4 py-3 text-sm text-foreground placeholder:text-muted-foreground transition-colors focus:border-amber-400 focus:outline-none"
                    :class="errors.signerName ? 'border-red-500' : 'border-border'"
                />
                <p v-if="errors.signerName" class="mt-1 text-xs text-red-500" role="alert">{{ errors.signerName }}</p>
                <p class="mt-1.5 text-xs text-muted-foreground">By typing your name, you are confirming your agreement electronically.</p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" class="flex-1 rounded-xl border border-border py-3.5 text-sm font-bold text-muted-foreground transition-colors hover:text-foreground" @click="emit('back')">Back</button>
                <button type="submit" class="flex-1 rounded-xl bg-amber-400 py-3.5 text-sm font-bold text-slate-900 transition-colors hover:bg-amber-300 active:scale-95">I Agree & Continue</button>
            </div>
        </form>
    </div>
</template>
