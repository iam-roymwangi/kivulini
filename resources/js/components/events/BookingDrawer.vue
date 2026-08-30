<script setup lang="ts">
import { ref, computed } from 'vue';
import { X } from '@lucide/vue';
import { Sheet, SheetContent, SheetTitle } from '@/components/ui/sheet';
import BookingConfirmation from '@/components/events/BookingConfirmation.vue';
import BookingStep1 from '@/components/events/BookingStep1.vue';
import BookingStep2 from '@/components/events/BookingStep2.vue';
import BookingStep3 from '@/components/events/BookingStep3.vue';
import BookingStep4 from '@/components/events/BookingStep4.vue';
import type { BookingPayload, PlatformEvent } from '@/types';

const DEFAULT_WAIVER = `By completing this booking, you acknowledge and agree to the following:

1. PARTICIPATION RISKS — You understand that participation in outdoor activities and travel events involves inherent physical risks, including but not limited to personal injury, illness, or loss of personal property.

2. VOLUNTARY PARTICIPATION — Your participation is entirely voluntary. You confirm you are in good physical health and have no medical conditions that would prevent safe participation.

3. RELEASE OF LIABILITY — You agree to release Kivulini Adventures, its organizers, staff, and agents from any liability arising from your participation in this event.

4. EMERGENCY CONSENT — In the event of a medical emergency, you authorize Kivulini Adventures to seek medical assistance on your behalf.

5. PHOTOGRAPHY & MEDIA — You consent to photographs and videos taken during the event being used for promotional purposes.

By proceeding, you confirm that you have read, understood, and agree to these terms.`;

const props = defineProps<{
    event: PlatformEvent;
    initialQuantity?: number;
}>();

const open = defineModel<boolean>('open', { default: false });

const waiverText = computed(() => props.event.liability_waiver_text ?? DEFAULT_WAIVER);
const hasQuestions = computed(() => props.event.questions.length > 0);

// Steps: 1 = contact, 2 = questions (if any), 3 = consent (always), 4 = payment
const steps = computed(() => {
    const s = [1];
    if (hasQuestions.value) { s.push(2); }
    s.push(3); // consent always required
    s.push(4);
    return s;
});

const stepLabels: Record<number, string> = {
    1: 'Contact',
    2: 'Details',
    3: 'Consent',
    4: 'Payment',
};

const currentStepIndex = ref(0);
const currentStep = computed(() => steps.value[currentStepIndex.value]);
const totalSteps = computed(() => steps.value.length);

const isConfirmed = ref(false);
const confirmedReference = ref('');
const confirmedBookingId = ref(0);

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

function handleConfirmed(reference: string, bookingId: number) {
    confirmedReference.value = reference;
    confirmedBookingId.value = bookingId;
    isConfirmed.value = true;
}

function handleClose() {
    currentStepIndex.value = 0;
    isConfirmed.value = false;
    confirmedReference.value = '';
    confirmedBookingId.value = 0;
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
            class="flex h-[92dvh] flex-col overflow-hidden rounded-t-3xl border-t border-border bg-background p-0 text-foreground shadow-2xl md:mx-auto md:max-h-[85vh] md:max-w-lg md:rounded-2xl"
        >
            <SheetTitle class="sr-only">Book {{ event.title }}</SheetTitle>

            <!-- Header bar -->
            <div class="flex shrink-0 items-center justify-between border-b border-border px-5 py-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">
                        {{ isConfirmed ? 'Booking Confirmed' : `Step ${currentStepIndex + 1} of ${totalSteps}` }}
                    </p>
                    <p class="mt-0.5 text-sm font-bold text-foreground line-clamp-1">{{ event.title }}</p>
                </div>
                
            </div>

            <!-- Progress bar (not shown on confirmation) -->
            <div v-if="!isConfirmed" class="shrink-0 px-5 pt-4">
                <div class="flex gap-1.5">
                    <div
                        v-for="(step, i) in steps"
                        :key="step"
                        class="h-1 flex-1 rounded-full transition-all duration-300"
                        :class="i <= currentStepIndex ? 'bg-amber-400' : 'bg-muted'"
                    />
                </div>
                <div class="mt-2 flex justify-between">
                    <span
                        v-for="(step, i) in steps"
                        :key="step"
                        class="text-xs transition-colors"
                        :class="i === currentStepIndex ? 'font-bold text-amber-500' : i < currentStepIndex ? 'text-muted-foreground' : 'text-muted-foreground/40'"
                    >
                        {{ stepLabels[step] }}
                    </span>
                </div>
            </div>

            <!-- Scrollable content -->
            <div class="min-h-0 flex-1 overflow-y-auto px-5 py-4">
                <!-- Confirmation -->
                <BookingConfirmation
                    v-if="isConfirmed"
                    :booking-reference="confirmedReference"
                    :booking-id="confirmedBookingId"
                    :event="event"
                    @close="handleClose"
                />

                <template v-else>
                    <BookingStep1
                        v-if="currentStep === 1"
                        :initial-data="bookingPayload"
                        @next="goNext"
                    />

                    <BookingStep2
                        v-else-if="currentStep === 2"
                        :questions="event.questions"
                        :initial-responses="bookingPayload.responses ?? []"
                        @next="goNext"
                        @back="goBack"
                    />

                    <BookingStep3
                        v-else-if="currentStep === 3"
                        :waiver-text="waiverText"
                        @next="goNext"
                        @back="goBack"
                    />

                    <BookingStep4
                        v-else-if="currentStep === 4"
                        :event="event"
                        :payload="bookingPayload as BookingPayload"
                        @confirmed="handleConfirmed"
                        @back="goBack"
                    />
                </template>
            </div>
        </SheetContent>
    </Sheet>
</template>
