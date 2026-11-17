<script setup lang="ts">
import {
    Bold,
    Heading2,
    Heading3,
    Italic,
    Link,
    List,
    ListOrdered,
    Pilcrow,
    RemoveFormatting,
    Underline,
} from '@lucide/vue';
import { onMounted, ref, watch } from 'vue';

const props = defineProps<{
    modelValue: string;
    placeholder?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const editor = ref<HTMLDivElement | null>(null);

/**
 * Convert legacy plain-text descriptions (no tags) into paragraphs so they
 * keep their line breaks once loaded into the editor.
 */
function toEditorHtml(value: string): string {
    if (!value || /<[a-z][\s\S]*>/i.test(value)) {
        return value ?? '';
    }

    const escape = (text: string) =>
        text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

    return value
        .split(/\n{2,}/)
        .map((paragraph) => `<p>${escape(paragraph).replace(/\n/g, '<br>')}</p>`)
        .join('');
}

function emitValue(): void {
    if (!editor.value) {
        return;
    }

    const html = editor.value.innerHTML;
    const isEmpty = editor.value.textContent?.trim() === '' && !/<(ul|ol|li)/i.test(html);

    emit('update:modelValue', isEmpty ? '' : html);
}

function run(command: string, value?: string): void {
    editor.value?.focus();
    document.execCommand(command, false, value);
    emitValue();
}

function insertLink(): void {
    const url = window.prompt('Link URL (https://...)');

    if (url) {
        run('createLink', url);
    }
}

function onPaste(event: ClipboardEvent): void {
    const text = event.clipboardData?.getData('text/plain');

    if (text === undefined) {
        return;
    }

    event.preventDefault();
    document.execCommand('insertText', false, text);
}

onMounted(() => {
    document.execCommand('defaultParagraphSeparator', false, 'p');

    if (editor.value) {
        editor.value.innerHTML = toEditorHtml(props.modelValue);
    }
});

watch(
    () => props.modelValue,
    (value) => {
        if (editor.value && value !== editor.value.innerHTML) {
            editor.value.innerHTML = toEditorHtml(value);
        }
    },
);

const tools = [
    { label: 'Paragraph', icon: Pilcrow, action: () => run('formatBlock', '<p>') },
    { label: 'Heading', icon: Heading2, action: () => run('formatBlock', '<h2>') },
    { label: 'Subheading', icon: Heading3, action: () => run('formatBlock', '<h3>') },
    { label: 'Bold', icon: Bold, action: () => run('bold') },
    { label: 'Italic', icon: Italic, action: () => run('italic') },
    { label: 'Underline', icon: Underline, action: () => run('underline') },
    { label: 'Bullet list', icon: List, action: () => run('insertUnorderedList') },
    { label: 'Numbered list', icon: ListOrdered, action: () => run('insertOrderedList') },
    { label: 'Link', icon: Link, action: insertLink },
    { label: 'Clear formatting', icon: RemoveFormatting, action: () => run('removeFormat') },
];
</script>

<template>
    <div class="overflow-hidden rounded-md border border-input bg-background focus-within:ring-2 focus-within:ring-ring">
        <div class="flex flex-wrap gap-1 border-b border-input bg-muted/40 p-1">
            <button
                v-for="tool in tools"
                :key="tool.label"
                type="button"
                :title="tool.label"
                :aria-label="tool.label"
                class="rounded p-1.5 text-muted-foreground transition-colors hover:bg-accent hover:text-foreground"
                @mousedown.prevent
                @click="tool.action"
            >
                <component :is="tool.icon" class="h-4 w-4" />
            </button>
        </div>
        <div
            ref="editor"
            contenteditable="true"
            role="textbox"
            aria-multiline="true"
            :data-placeholder="placeholder"
            class="rich-text min-h-40 px-3 py-2 text-sm text-foreground outline-none empty:before:text-muted-foreground empty:before:content-[attr(data-placeholder)]"
            @input="emitValue"
            @blur="emitValue"
            @paste="onPaste"
        />
    </div>
</template>
