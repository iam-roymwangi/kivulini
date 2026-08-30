<script setup lang="ts">
/**
 * CategoryFilter — v-model compatible filter pill group.
 * modelValue: 'all' | 'event' | 'road_trip' | 'vacation'
 */
const props = withDefaults(
    defineProps<{
        modelValue: string;
    }>(),
    {
        modelValue: 'all',
    },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const pills = [
    { label: 'All Events', value: 'all', disabled: false },
    { label: 'Road Trips', value: 'road_trip', disabled: false },
];

function select(value: string, disabled: boolean) {
    if (disabled) { return; }
    emit('update:modelValue', value);
}
</script>

<template>
    <div class="flex flex-wrap items-center gap-3" role="group" aria-label="Filter events by category">
        <button
            v-for="pill in pills"
            :key="pill.value"
            type="button"
            :aria-pressed="!pill.disabled && props.modelValue === pill.value"
            :aria-disabled="pill.disabled"
            :disabled="pill.disabled"
            class="rounded-full border px-5 py-2 text-sm font-semibold transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400"
            :class="[
                pill.disabled
                    ? 'cursor-not-allowed border-slate-300 text-slate-400 opacity-50 dark:border-slate-700 dark:text-slate-600'
                    : props.modelValue === pill.value
                        ? 'border-amber-500 bg-amber-500 text-white dark:border-amber-400 dark:bg-amber-400 dark:text-slate-900'
                        : 'border-slate-300 text-slate-600 hover:border-amber-500 hover:text-amber-500 dark:border-slate-600 dark:text-slate-300 dark:hover:border-amber-400 dark:hover:text-amber-400',
            ]"
            @click="select(pill.value, pill.disabled)"
        >
            {{ pill.label }}
            <span v-if="pill.suffix" class="ml-1.5 text-xs font-normal opacity-70">[{{ pill.suffix }}]</span>
        </button>
    </div>
</template>
