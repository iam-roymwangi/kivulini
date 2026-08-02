<script setup lang="ts">
import { ref, computed } from 'vue';
import { Sheet, SheetContent, SheetTitle } from '@/components/ui/sheet';
import BookingConfirmation from '@/components/events/BookingConfirmation.vue';
import BookingStep1 from '@/components/events/BookingStep1.vue';
import BookingStep2 from '@/components/events/BookingStep2.vue';
import BookingStep3 from '@/components/events/BookingStep3.vue';
import BookingStep4 from '@/components/events/BookingStep4.vue';
import type { BookingPayload, PlatformEvent } from '@/types';

const props = defineProps<{
    event: PlatformEvent;
    initialQuantity?: number;
}>();

const open = defineModel<boolean>('open', { default: false });

// Determine which steps are active
const hasQuestions = computed(() => props.event.questions.length > 0);
const hasWaiver = computed(() => !!props.event.liability_waiver_text);

// Steps: 1 = contact, 2 = questions (optional), 3 = consent (optional), 4 = payment
const steps = computed(() => {
    const s = [1];
    if (hasQuestions.value) { s.push(2); }
    if (hasWaiver.value) { s.push(3); }
    s.push(4);
    return s;
});

const currentStepIndex = ref(0);
const currentStep = computed(() => steps.value[currentStepIndex.value]);
const totalSteps = computed(() => steps.value.length);
const isConfirmed = ref(false);
const confirmedReference = ref('');

// Accumulated booking payload
const bookingPayload = ref<Partial<BookingPayload>>({
    quantity: props.initialQuantity ?? 1,
    responses: [],
    consent: { agreed: false, signer_name: '' },
});

function goNext(data: Partial<BookingPayload>) {
    bookingPayload.value = { ...bookingPayload.value, ...data };
    if (currentStepIndex.value < steps.value.length - 1) {
        currentStepIndex.value++;
    }
}

function goBack() {
    if (currentStepIndex.value > 0) {
        currentStepIndex.value--;
    }
}

function handleConfirmed(reference: string) {
    confirmedReference.value = reference;
    isConfirmed.value = true;
}

function handleClose() {
    // Reset on close
    currentStepIndex.value = 0;
    isConfirmed.value = false;
    confirmedReference.value = '';
    bookingPayload.value = {
        quantity: props.initialQuantity ?? 1,
        responses: [],
        consent: { agreed: false, signer_name: '' },
    };
    open.value = false;
}
</script>

<template>
    <Sheet :open="open" @update:open="(v) => { if (!v) { handleClose(); } }">
        <SheetContent
            side="bottom"
            class="h-[92dvh] overflow-y-auto border-slate-700 bg-slate-950 text-white md:h-auto md:max-h-[90vh] sm:rounded-t-2xl"
        >
            <SheetTitle class="sr-only">Book {{ event.title }}</SheetTitle>

            <!-- Confirmed state -->
            <BookingConfirmation
                v-if="isConfirmed"
                :booking-reference="confirmedReference"
                :event="event"
                @close="handleClose"
            />

            <template v-else>
                <!-- Progress indicator -->
                <div class="mb-6 px-1">
                    <div class="mb-2 flex items-center justify-between text-xs text-slate-400">
                        <span>Step {{ currentStepIndex + 1 }} of {{ totalSteps }}</span>
                        <button
                            type="button"
                            class="text-slate-500 hover:text-white"
                            aria-label="Close booking"
                            @click="handleClose"
                        >
                            Cancel
                        </button>
                    </div>
                    <div class="flex gap-1.5">
                        <div
                            v-for="(_, i) in steps"
                            :key="i"
                            class="h-1 flex-1 rounded-full transition-colors duration-300"
                            :class="i <= currentStepIndex ? 'bg-amber-400' : 'bg-slate-700'"
                        />
                    </div>
                </div>

                <!-- Step 1: Contact details -->
                <BookingStep1
                    v-if="currentStep === 1"
                    :initial-data="bookingPayload"
                    @next="goNext"
                />

                <!-- Step 2: Questions -->
                <BookingStep2
                    v-else-if="currentStep === 2"
                    :questions="event.questions"
                    :initial-responses="bookingPayload.responses ?? []"
                    @next="goNext"
                    @back="goBack"
                />

                <!-- Step 3: Consent -->
                <BookingStep3
                    v-else-if="currentStep === 3"
                    :waiver-text="event.liability_waiver_text!"
                    @next="goNext"
                    @back="goBack"
                />

                <!-- Step 4: Payment -->
                <BookingStep4
                    v-else-if="currentStep === 4"
                    :event="event"
                    :payload="bookingPayload as BookingPayload"
                    @confirmed="handleConfirmed"
                    @back="goBack"
                />
            </template>
        </SheetContent>
    </Sheet>
</template>
