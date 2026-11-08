<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const visible = ref(false);
const animating = ref(false);

let showTimer: ReturnType<typeof setTimeout> | null = null;

function show(): void {
    // Small delay so instant navigations don't flash the loader
    showTimer = setTimeout(() => {
        visible.value = true;
        animating.value = true;
    }, 60);
}

function hide(): void {
    if (showTimer) {
        clearTimeout(showTimer);
        showTimer = null;
    }
    animating.value = false;
    // Wait for fade-out transition, then remove from layout
    setTimeout(() => {
        visible.value = false;
    }, 400);
}

const stopStart = router.on('start', show);
const stopFinish = router.on('finish', hide);

onUnmounted(() => {
    stopStart();
    stopFinish();
    if (showTimer) clearTimeout(showTimer);
});
</script>

<template>
    <Teleport to="body">
        <div
            v-if="visible"
            id="page-preloader"
            aria-hidden="true"
            :class="[
                'fixed inset-0 z-[9999] flex items-center justify-center',
                'bg-white dark:bg-[oklch(0.145_0_0)]',
                'transition-opacity duration-400',
                animating ? 'opacity-100' : 'opacity-0',
            ]"
        >
            <div class="flex flex-col items-center gap-6">
                <!-- Light logo -->
                <img
                    class="block dark:hidden h-20 w-20 object-contain"
                    src="/assets/images/kivulini_logo.png"
                    alt="Kivulini Adventures"
                >
                <!-- Dark logo -->
                <img
                    class="hidden dark:block h-20 w-20 object-contain"
                    src="/assets/images/logo-dark.png"
                    alt="Kivulini Adventures"
                >

                <!-- Bouncing dots (runs 3×, then stops) -->
                <div class="flex gap-1.5">
                    <span class="preloader-dot" style="animation-delay: 0s" />
                    <span class="preloader-dot" style="animation-delay: 0.2s" />
                    <span class="preloader-dot" style="animation-delay: 0.4s" />
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.preloader-dot {
    width: 6px;
    height: 6px;
    border-radius: 9999px;
    background-color: #4b5563;
    animation: dot-bounce 1.2s ease-in-out 3;
}

:global(.dark) .preloader-dot {
    background-color: #9ca3af;
}

@keyframes dot-bounce {
    0%, 80%, 100% { transform: translateY(0);    opacity: 0.4; }
    40%            { transform: translateY(-6px); opacity: 1; }
}
</style>
