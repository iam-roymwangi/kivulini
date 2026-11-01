<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import { status as paymentStatus } from '@/routes/payments';
import { csrfHeaders } from '@/lib/utils';

const props = defineProps<{
    paymentId: string;
    pendingTitle?: string;
    pendingDescription?: string;
}>();

const emit = defineEmits<{
    (e: 'completed', payload: { reference: string | null; paymentStatus: string | null }): void;
    (e: 'failed', message: string): void;
}>();

const resultMessage = ref('');
let timer: ReturnType<typeof setInterval> | null = null;
let attempts = 0;

async function poll(): Promise<void> {
    attempts += 1;

    try {
        const res = await fetch(paymentStatus.url(props.paymentId), {
            headers: csrfHeaders(),
        });

        const json = await res.json();

        if (! res.ok) {
            return;
        }

        if (json.status === 'completed') {
            stop();
            emit('completed', {
                reference: json.payable?.reference ?? null,
                paymentStatus: json.payable?.payment_status ?? null,
            });
            return;
        }

        if (json.status === 'failed') {
            stop();
            emit('failed', json.result_desc || 'Payment was not completed. Please try again.');
        }
    } catch {
        resultMessage.value = 'Checking payment status…';
    }

    if (attempts >= 60) {
        resultMessage.value = 'Still waiting for M-Pesa. Keep this page open, or check your phone.';
    }
}

function stop(): void {
    if (timer) {
        clearInterval(timer);
        timer = null;
    }
}

onMounted(() => {
    void poll();
    timer = setInterval(() => {
        void poll();
    }, 3000);
});

onUnmounted(() => {
    stop();
});
</script>

<template>
    <div class="flex flex-col items-center gap-5 px-1 pb-6 text-center">
        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-amber-400/10 ring-4 ring-amber-400/30">
            <svg class="h-10 w-10 animate-pulse text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <div class="space-y-2">
            <h2 class="text-2xl font-black text-foreground">
                {{ pendingTitle ?? 'Confirm on your phone' }}
            </h2>
            <p class="text-sm text-muted-foreground">
                {{ pendingDescription ?? 'Enter your M-Pesa PIN when prompted. This page updates automatically once payment is confirmed.' }}
            </p>
            <p v-if="resultMessage" class="text-xs text-muted-foreground">{{ resultMessage }}</p>
        </div>
    </div>
</template>
