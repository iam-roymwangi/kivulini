<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Globe, EyeOff } from '@lucide/vue';
import EventForm from '@/components/admin/EventForm.vue';
import type { EventFormData, ExistingMedia } from '@/components/admin/EventForm.vue';
import { update, index, publishGallery, unpublishGallery } from '@/routes/admin/events';

interface EventMediaRecord {
    id: number;
    file_path: string;
    type: string;
    is_featured: boolean;
    sort_order: number;
}

interface QuestionRecord {
    id: number;
    question_text: string;
    type: 'text' | 'textarea' | 'select';
    options: string[] | null;
    is_required: boolean;
    sort_order: number;
}

interface EventRecord {
    id: number;
    title: string;
    type: string;
    summary: string;
    description: string;
    location: string;
    pickup_location: string | null;
    start_date: string;
    end_date: string;
    price: string;
    capacity: number;
    booked_slots: number;
    status: string;
    liability_waiver_text: string | null;
    slug: string;
    media: EventMediaRecord[];
    questions: QuestionRecord[];
}

const props = defineProps<{
    event: EventRecord;
}>();

// Build base URL for storage files
const storageUrl = (path: string) => `/storage/${path}`;

const existingMedia = computed<ExistingMedia[]>(() =>
    props.event.media.map((m) => ({
        id: m.id,
        url: storageUrl(m.file_path),
        is_featured: m.is_featured,
        sort_order: m.sort_order,
    })),
);

const isCompleted = computed(() => props.event.status === 'completed');
const isGalleryPublished = computed(() => props.event.media.some((m) => m.is_featured));

// Format datetime for input
function toDatetimeLocal(dt: string) {
    return dt ? dt.replace('T', 'T').slice(0, 16) : '';
}

const form = ref<EventFormData>({
    title: props.event.title,
    type: props.event.type,
    summary: props.event.summary,
    description: props.event.description,
    location: props.event.location,
    pickup_location: props.event.pickup_location ?? '',
    start_date: toDatetimeLocal(props.event.start_date),
    end_date: toDatetimeLocal(props.event.end_date),
    price: props.event.price,
    capacity: String(props.event.capacity),
    status: props.event.status,
    liability_waiver_text: props.event.liability_waiver_text ?? '',
    questions: props.event.questions
        .sort((a, b) => a.sort_order - b.sort_order)
        .map((q) => ({
            id: q.id,
            question_text: q.question_text,
            type: q.type,
            is_required: q.is_required,
            options: q.options ?? [],
            _optionsText: (q.options ?? []).join('\n'),
        })),
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
    data.append('_method', 'PUT');

    const scalar: (keyof EventFormData)[] = [
        'title', 'type', 'summary', 'description', 'location', 'pickup_location',
        'start_date', 'end_date', 'price', 'capacity', 'status', 'liability_waiver_text',
    ];
    scalar.forEach((k) => data.append(k, (form.value[k] as string) ?? ''));

    if (form.value.featured_media_id !== null) {
        data.append('featured_media_id', String(form.value.featured_media_id));
    }

    form.value.delete_media_ids.forEach((id) => data.append('delete_media_ids[]', String(id)));
    form.value.delete_question_ids.forEach((id) => data.append('delete_question_ids[]', String(id)));

    form.value.images.forEach((f) => data.append('images[]', f));

    form.value.questions.forEach((q, i) => {
        if (q.id) { data.append(`questions[${i}][id]`, String(q.id)); }
        data.append(`questions[${i}][question_text]`, q.question_text);
        data.append(`questions[${i}][type]`, q.type);
        data.append(`questions[${i}][is_required]`, q.is_required ? '1' : '0');
        q.options.forEach((opt) => data.append(`questions[${i}][options][]`, opt));
    });

    router.post(update.url(props.event.id), data, {
        forceFormData: true,
        onError: (e) => { errors.value = e; submitting.value = false; },
        onSuccess: () => { submitting.value = false; },
    });
}

function doPublishGallery() {
    if (!confirm('This will mark all event images as featured, making them visible in the public Past Trips gallery. Continue?')) { return; }
    router.post(publishGallery.url(props.event.id));
}

function doUnpublishGallery() {
    if (!confirm('This will remove all event images from the public gallery. Continue?')) { return; }
    router.post(unpublishGallery.url(props.event.id));
}
</script>

<template>
    <Head :title="`Edit — ${event.title}`" />

    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex items-center gap-3">
            <a :href="index.url()" class="rounded-lg border border-border p-2 text-muted-foreground hover:text-foreground">
                <ArrowLeft class="h-4 w-4" />
            </a>
            <div>
                <h1 class="text-2xl font-black text-foreground">Edit Event</h1>
                <p class="text-sm text-muted-foreground">{{ event.title }}</p>
            </div>
        </div>

        <!-- Gallery publish banner for completed events -->
        <div
            v-if="isCompleted"
            class="flex flex-col gap-3 rounded-2xl border p-5 sm:flex-row sm:items-center sm:justify-between"
            :class="isGalleryPublished ? 'border-blue-500/30 bg-blue-500/10' : 'border-amber-400/30 bg-amber-400/10'"
        >
            <div>
                <p class="font-bold" :class="isGalleryPublished ? 'text-blue-400' : 'text-amber-500'">
                    {{ isGalleryPublished ? 'Gallery Published' : 'Publish to Public Gallery' }}
                </p>
                <p class="text-sm text-muted-foreground">
                    {{ isGalleryPublished
                        ? 'This event\'s photos are visible in the Past Trips gallery on the homepage.'
                        : 'This event is completed. Publish its photos to the Past Trips gallery to showcase it on the homepage.' }}
                </p>
            </div>
            <div class="flex gap-2">
                <button
                    v-if="!isGalleryPublished"
                    type="button"
                    class="flex items-center gap-2 rounded-xl bg-amber-400 px-4 py-2 text-sm font-bold text-slate-900 hover:bg-amber-300"
                    @click="doPublishGallery"
                >
                    <Globe class="h-4 w-4" />
                    Publish Gallery
                </button>
                <button
                    v-if="isGalleryPublished"
                    type="button"
                    class="flex items-center gap-2 rounded-xl border border-slate-600 px-4 py-2 text-sm font-semibold text-muted-foreground hover:text-foreground"
                    @click="doUnpublishGallery"
                >
                    <EyeOff class="h-4 w-4" />
                    Unpublish
                </button>
            </div>
        </div>

        <!-- Errors -->
        <div v-if="Object.keys(errors).length > 0" class="rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-500">
            Please fix the errors below and try again.
        </div>

        <div class="rounded-2xl border border-border bg-card p-6">
            <EventForm
                :form="form"
                :errors="errors"
                :existing-media="existingMedia"
                :is-edit="true"
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
                {{ submitting ? 'Saving...' : 'Save Changes' }}
            </button>
        </div>
    </div>
</template>
