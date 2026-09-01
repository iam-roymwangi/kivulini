<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <img class="h-8 w-8" src="/assets/images/kivulini_logo.png" alt="Kivulini">
</template>
