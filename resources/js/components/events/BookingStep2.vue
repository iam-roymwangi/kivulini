<script setup lang="ts">
import { computed, reactive } from 'vue';
import type { BookingPayload, EventQuestion } from '@/types';

const props = defineProps<{
    questions: EventQuestion[];
    initialResponses?: BookingPayload['responses'];
}>();

const emit = defineEmits<{
    (e: 'next', data: Partial<BookingPayload>): void;
    (e: 'back'): void;
}>();

// Sort questions by sort_order ascending
const sortedQuestions = computed(() =>
    [...props.questions].sort((a, b) => a.sort_order - b.sort_order),
);

// Map question id → answer string
const answers = reactive<Record<number, string>>(
    Object.fromEntries(
        (props.initialResponses ?? []).map((r) => [r.event_question_id, r.answer]),
    ),
);

const errors = reactive<Record<number, string>>({});

function validate(): boolean {
    sortedQuestions.value.forEach((q) => {
        delete errors[q.id];
        if (q.is_required && !answers[q.id]?.trim()) {
            errors[q.id] = `This field is required.`;
        }
    });
    return Object.keys(errors).length === 0;
}

function submit() {
    if (!validate()) { return; }
    const responses = sortedQuestions.value.map((q) => ({
        event_question_id: q.id,
        answer: answers[q.id] ?? '',
    }));
    emit('next', { responses });
}
</script>

<template>
    <div class="space-y-6 px-1 pb-4">
        <div>
            <h2 class="text-xl font-bold text-white">Participant Details</h2>
            <p class="mt-1 text-sm text-slate-400">Help us tailor the experience for you.</p>
        </div>

        <form class="space-y-5" novalidate @submit.prevent="submit">
            <div
                v-for="question in sortedQuestions"
                :key="question.id"
                :data-question-id="question.id"
            >
                <label
                    :for="`q-${question.id}`"
                    class="mb-1.5 block text-sm font-medium text-slate-300"
                >
                    {{ question.question_text }}
                    <span v-if="question.is_required" class="text-amber-400" aria-hidden="true"> *</span>
                </label>

                <!-- Text input -->
                <input
                    v-if="question.type === 'text'"
                    :id="`q-${question.id}`"
                    v-model="answers[question.id]"
                    type="text"
                    class="w-full rounded-xl border bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 transition-colors focus:border-amber-400 focus:outline-none"
                    :class="errors[question.id] ? 'border-red-500' : 'border-slate-700'"
                    :aria-invalid="!!errors[question.id]"
                    :aria-describedby="errors[question.id] ? `err-q-${question.id}` : undefined"
                />

                <!-- Textarea -->
                <textarea
                    v-else-if="question.type === 'textarea'"
                    :id="`q-${question.id}`"
                    v-model="answers[question.id]"
                    rows="3"
                    class="w-full rounded-xl border bg-slate-900 px-4 py-3 text-sm text-white placeholder-slate-500 transition-colors focus:border-amber-400 focus:outline-none resize-none"
                    :class="errors[question.id] ? 'border-red-500' : 'border-slate-700'"
                    :aria-invalid="!!errors[question.id]"
                    :aria-describedby="errors[question.id] ? `err-q-${question.id}` : undefined"
                />

                <!-- Select -->
                <select
                    v-else-if="question.type === 'select'"
                    :id="`q-${question.id}`"
                    v-model="answers[question.id]"
                    class="w-full rounded-xl border bg-slate-900 px-4 py-3 text-sm text-white transition-colors focus:border-amber-400 focus:outline-none"
                    :class="errors[question.id] ? 'border-red-500' : 'border-slate-700'"
                    :aria-invalid="!!errors[question.id]"
                    :aria-describedby="errors[question.id] ? `err-q-${question.id}` : undefined"
                >
                    <option value="" disabled selected>Select an option</option>
                    <option
                        v-for="opt in question.options ?? []"
                        :key="opt"
                        :value="opt"
                    >
                        {{ opt }}
                    </option>
                </select>

                <p
                    v-if="errors[question.id]"
                    :id="`err-q-${question.id}`"
                    class="mt-1 text-xs text-red-400"
                    role="alert"
                >
                    {{ errors[question.id] }}
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
