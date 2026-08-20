<script setup lang="ts">
import { ref, computed } from 'vue';
import { Plus, Trash2, GripVertical } from '@lucide/vue';

export interface EventFormData {
    title: string;
    type: string;
    summary: string;
    description: string;
    location: string;
    pickup_location: string;
    start_date: string;
    end_date: string;
    price: string;
    capacity: string;
    status: string;
    liability_waiver_text: string;
    questions: QuestionRow[];
    // For new uploads
    images: File[];
    // For deleting existing
    delete_media_ids: number[];
    featured_media_id: number | null;
    delete_question_ids: number[];
}

export interface QuestionRow {
    id?: number;
    question_text: string;
    type: 'text' | 'textarea' | 'select';
    is_required: boolean;
    options: string[];
    _optionsText: string; // textarea helper for comma-separated options
}

export interface ExistingMedia {
    id: number;
    url: string;
    is_featured: boolean;
    sort_order: number;
}

const props = defineProps<{
    form: EventFormData;
    errors: Partial<Record<string, string>>;
    existingMedia?: ExistingMedia[];
    isEdit?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:form', v: EventFormData): void;
}>();

function patch(partial: Partial<EventFormData>) {
    emit('update:form', { ...props.form, ...partial });
}

// Image preview for newly selected files
const previewUrls = ref<string[]>([]);

function handleImageFiles(e: Event) {
    const input = e.target as HTMLInputElement;
    if (!input.files) { return; }
    const files = Array.from(input.files);
    patch({ images: [...props.form.images, ...files] });
    files.forEach((f) => {
        const reader = new FileReader();
        reader.onload = (ev) => previewUrls.value.push(ev.target?.result as string);
        reader.readAsDataURL(f);
    });
}

function removeNewImage(index: number) {
    const imgs = [...props.form.images];
    imgs.splice(index, 1);
    previewUrls.value.splice(index, 1);
    patch({ images: imgs });
}

function markDeleteMedia(id: number) {
    patch({ delete_media_ids: [...props.form.delete_media_ids, id] });
}

function setFeatured(id: number) {
    patch({ featured_media_id: id });
}

// Questions
function addQuestion() {
    patch({
        questions: [
            ...props.form.questions,
            { question_text: '', type: 'text', is_required: true, options: [], _optionsText: '' },
        ],
    });
}

function removeQuestion(index: number) {
    const q = props.form.questions[index];
    const updated = [...props.form.questions];
    updated.splice(index, 1);
    const deleteIds = q.id ? [...props.form.delete_question_ids, q.id] : props.form.delete_question_ids;
    patch({ questions: updated, delete_question_ids: deleteIds });
}

function updateQuestion(index: number, partial: Partial<QuestionRow>) {
    const updated = props.form.questions.map((q, i) => {
        if (i !== index) { return q; }
        const merged = { ...q, ...partial };
        // Sync options array from textarea text
        if ('_optionsText' in partial) {
            merged.options = merged._optionsText.split('\n').map((s) => s.trim()).filter(Boolean);
        }
        return merged;
    });
    patch({ questions: updated });
}

const remainingMedia = computed(() =>
    (props.existingMedia ?? []).filter((m) => !props.form.delete_media_ids.includes(m.id)),
);
</script>

<template>
    <div class="space-y-8">
        <!-- Basic details -->
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="form-label">Title *</label>
                <input
                    type="text"
                    class="form-input"
                    :class="{ 'border-red-500': errors.title }"
                    :value="form.title"
                    @input="patch({ title: ($event.target as HTMLInputElement).value })"
                />
                <p v-if="errors.title" class="form-error">{{ errors.title }}</p>
            </div>

            <div>
                <label class="form-label">Type *</label>
                <select class="form-input" :value="form.type" @change="patch({ type: ($event.target as HTMLSelectElement).value })">
                    <option value="event">Event</option>
                    <option value="road_trip">Road Trip</option>
                    <option value="vacation">Vacation</option>
                </select>
            </div>

            <div>
                <label class="form-label">Status *</label>
                <select class="form-input" :value="form.status" @change="patch({ status: ($event.target as HTMLSelectElement).value })">
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div>
                <label class="form-label">Location *</label>
                <input type="text" class="form-input" :value="form.location" @input="patch({ location: ($event.target as HTMLInputElement).value })" />
                <p v-if="errors.location" class="form-error">{{ errors.location }}</p>
            </div>

            <div>
                <label class="form-label">Pickup Location</label>
                <input type="text" class="form-input" :value="form.pickup_location" @input="patch({ pickup_location: ($event.target as HTMLInputElement).value })" placeholder="Optional" />
            </div>

            <div>
                <label class="form-label">Start Date *</label>
                <input type="datetime-local" class="form-input" :value="form.start_date" @input="patch({ start_date: ($event.target as HTMLInputElement).value })" />
                <p v-if="errors.start_date" class="form-error">{{ errors.start_date }}</p>
            </div>

            <div>
                <label class="form-label">End Date *</label>
                <input type="datetime-local" class="form-input" :value="form.end_date" @input="patch({ end_date: ($event.target as HTMLInputElement).value })" />
                <p v-if="errors.end_date" class="form-error">{{ errors.end_date }}</p>
            </div>

            <div>
                <label class="form-label">Price (KES) *</label>
                <input type="number" min="0" class="form-input" :value="form.price" @input="patch({ price: ($event.target as HTMLInputElement).value })" />
                <p v-if="errors.price" class="form-error">{{ errors.price }}</p>
            </div>

            <div>
                <label class="form-label">Capacity *</label>
                <input type="number" min="1" class="form-input" :value="form.capacity" @input="patch({ capacity: ($event.target as HTMLInputElement).value })" />
                <p v-if="errors.capacity" class="form-error">{{ errors.capacity }}</p>
            </div>
        </div>

        <div>
            <label class="form-label">Summary * <span class="text-xs text-muted-foreground">(max 500 chars)</span></label>
            <textarea rows="2" class="form-input resize-none" maxlength="500" :value="form.summary" @input="patch({ summary: ($event.target as HTMLTextAreaElement).value })" />
            <p v-if="errors.summary" class="form-error">{{ errors.summary }}</p>
        </div>

        <div>
            <label class="form-label">Description *</label>
            <textarea rows="6" class="form-input" :value="form.description" @input="patch({ description: ($event.target as HTMLTextAreaElement).value })" />
            <p v-if="errors.description" class="form-error">{{ errors.description }}</p>
        </div>

        <div>
            <label class="form-label">Liability Waiver Text</label>
            <textarea rows="4" class="form-input" placeholder="Optional legal waiver shown before booking..." :value="form.liability_waiver_text" @input="patch({ liability_waiver_text: ($event.target as HTMLTextAreaElement).value })" />
        </div>

        <!-- Media -->
        <div>
            <h3 class="mb-3 text-sm font-semibold text-foreground">Event Images <span class="text-xs text-muted-foreground">(converted to WebP automatically)</span></h3>

            <!-- Existing media (edit mode) -->
            <div v-if="remainingMedia.length > 0" class="mb-4 flex flex-wrap gap-3">
                <div
                    v-for="media in remainingMedia"
                    :key="media.id"
                    class="group relative h-24 w-32 overflow-hidden rounded-lg border border-border"
                >
                    <img :src="media.url" alt="Event image" class="h-full w-full object-cover" />
                    <div class="absolute inset-0 flex flex-col items-center justify-center gap-1 bg-black/60 opacity-0 transition-opacity group-hover:opacity-100">
                        <button
                            type="button"
                            class="rounded bg-amber-400 px-2 py-0.5 text-xs font-bold text-slate-900"
                            :class="{ 'ring-2 ring-amber-400': form.featured_media_id === media.id || (form.featured_media_id === null && media.is_featured) }"
                            @click="setFeatured(media.id)"
                        >
                            {{ (form.featured_media_id === media.id || (form.featured_media_id === null && media.is_featured)) ? 'Cover' : 'Set cover' }}
                        </button>
                        <button type="button" class="rounded bg-red-600 px-2 py-0.5 text-xs font-bold text-white" @click="markDeleteMedia(media.id)">
                            Remove
                        </button>
                    </div>
                    <span v-if="media.is_featured && form.featured_media_id === null" class="absolute left-1 top-1 rounded bg-amber-400 px-1 text-xs font-bold text-slate-900">Cover</span>
                </div>
            </div>

            <!-- New image previews -->
            <div v-if="previewUrls.length > 0" class="mb-4 flex flex-wrap gap-3">
                <div
                    v-for="(url, i) in previewUrls"
                    :key="i"
                    class="group relative h-24 w-32 overflow-hidden rounded-lg border-2 border-dashed border-amber-400"
                >
                    <img :src="url" alt="New upload preview" class="h-full w-full object-cover" />
                    <button
                        type="button"
                        class="absolute right-1 top-1 rounded-full bg-red-600 p-0.5 text-white opacity-0 transition-opacity group-hover:opacity-100"
                        @click="removeNewImage(i)"
                    >
                        <Trash2 class="h-3 w-3" />
                    </button>
                </div>
            </div>

            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-dashed border-border p-4 text-sm text-muted-foreground hover:border-amber-400 hover:text-amber-500">
                <Plus class="h-4 w-4" />
                Click to add images
                <input type="file" accept="image/*" multiple class="hidden" @change="handleImageFiles" />
            </label>
        </div>

        <!-- Questions -->
        <div>
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-foreground">Booking Questions</h3>
                <button type="button" class="flex items-center gap-1 rounded-lg bg-amber-400 px-3 py-1.5 text-xs font-bold text-slate-900 hover:bg-amber-300" @click="addQuestion">
                    <Plus class="h-3 w-3" /> Add Question
                </button>
            </div>

            <div class="space-y-4">
                <div
                    v-for="(q, i) in form.questions"
                    :key="i"
                    class="rounded-xl border border-border bg-card p-4"
                >
                    <div class="mb-3 flex items-start gap-2">
                        <GripVertical class="mt-1 h-4 w-4 shrink-0 text-muted-foreground" />
                        <div class="flex-1 space-y-3">
                            <input
                                type="text"
                                class="form-input"
                                placeholder="Question text"
                                :value="q.question_text"
                                @input="updateQuestion(i, { question_text: ($event.target as HTMLInputElement).value })"
                            />
                            <div class="flex flex-wrap gap-3">
                                <select class="form-input w-auto" :value="q.type" @change="updateQuestion(i, { type: ($event.target as HTMLSelectElement).value as QuestionRow['type'] })">
                                    <option value="text">Text input</option>
                                    <option value="textarea">Textarea</option>
                                    <option value="select">Dropdown select</option>
                                </select>
                                <label class="flex items-center gap-2 text-sm text-foreground">
                                    <input type="checkbox" :checked="q.is_required" class="accent-amber-400" @change="updateQuestion(i, { is_required: ($event.target as HTMLInputElement).checked })" />
                                    Required
                                </label>
                            </div>
                            <div v-if="q.type === 'select'">
                                <label class="mb-1 block text-xs text-muted-foreground">Options (one per line)</label>
                                <textarea
                                    rows="3"
                                    class="form-input resize-none text-xs"
                                    placeholder="Vegetarian&#10;Vegan&#10;No restriction"
                                    :value="q._optionsText"
                                    @input="updateQuestion(i, { _optionsText: ($event.target as HTMLTextAreaElement).value })"
                                />
                            </div>
                        </div>
                        <button type="button" class="mt-1 text-red-500 hover:text-red-700" @click="removeQuestion(i)">
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@reference "../../../css/app.css";
.form-label {
    @apply mb-1.5 block text-sm font-medium text-foreground;
}
.form-input {
    @apply w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground placeholder-muted-foreground focus:border-ring focus:outline-none;
}
.form-error {
    @apply mt-1 text-xs text-red-500;
}
</style>
