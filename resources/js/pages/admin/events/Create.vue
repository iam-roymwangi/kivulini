<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import EventForm from '@/components/admin/EventForm.vue';
import type { EventFormData } from '@/components/admin/EventForm.vue';
import { store, index } from '@/routes/admin/events';

const form = ref<EventFormData>({
    title: '',
    type: 'event',
    summary: '',
    description: '',
    location: '',
    pickup_location: '',
    start_date: '',
    end_date: '',
    price: '',
    capacity: '',
    status: 'draft',
    liability_waiver_text: '',
    questions: [],
    images: [],
    delete_media_ids: [],
    featured_media_id: null,
    delete_question_ids: [],
});

const errors = ref<Partial<Record<string, string>>>({});
const submitting = ref(false);

function submit() {
    submitting.value = true;
    errors.value = {};

    const data = new FormData();

    // Scalar fields
    const scalar: (keyof EventFormData)[] = [
        'title', 'type', 'summary', 'description', 'location', 'pickup_location',
        'start_date', 'end_date', 'price', 'capacity', 'status', 'liability_waiver_text',
    ];
    scalar.forEach((k) => data.append(k, (form.value[k] as string) ?? ''));

    // Images
    form.value.images.forEach((f) => data.append('images[]', f));

    // Questions
    form.value.questions.forEach((q, i) => {
        data.append(`questions[${i}][question_text]`, q.question_text);
        data.append(`questions[${i}][type]`, q.type);
        data.append(`questions[${i}][is_required]`, q.is_required ? '1' : '0');
        q.options.forEach((opt) => data.append(`questions[${i}][options][]`, opt));
    });

    router.post(store.url(), data, {
        forceFormData: true,
        onError: (e) => { errors.value = e; submitting.value = false; },
        onSuccess: () => { submitting.value = false; },
    });
}
</script>

<template>
    <Head title="New Event" />

    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex items-center gap-3">
            <a :href="index.url()" class="rounded-lg border border-border p-2 text-muted-foreground hover:text-foreground">
                <ArrowLeft class="h-4 w-4" />
            </a>
            <h1 class="text-2xl font-black text-foreground">Create Event</h1>
        </div>

        <!-- Global error -->
        <div v-if="Object.keys(errors).length > 0" class="rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-500">
            Please fix the errors below and try again.
        </div>

        <div class="rounded-2xl border border-border bg-card p-6">
            <EventForm
                :form="form"
                :errors="errors"
                @update:form="form = $event"
            />
        </div>

        <div class="flex justify-end gap-3 pb-8">
            <a :href="index.url()" class="rounded-xl border border-border px-5 py-2.5 text-sm font-semibold text-foreground hover:bg-muted">
                Cancel
            </a>
            <button
                type="button"
                :disabled="submitting"
                class="rounded-xl bg-amber-400 px-6 py-2.5 text-sm font-bold text-slate-900 hover:bg-amber-300 disabled:opacity-60"
                @click="submit"
            >
                {{ submitting ? 'Creating...' : 'Create Event' }}
            </button>
        </div>
    </div>
</template>
