<script setup lang="ts">
import NavIsland from '@/components/events/NavIsland.vue';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { User } from '@/types';

defineProps<{
    title?: string;
    description?: string;
}>();

const page = usePage();
const user = computed(() => (page.props.auth as { user: User | null } | null)?.user ?? null);

const heroImages = [
    '/assets/images/hero.jpg',
    '/assets/images/event-3.jpg',
    '/assets/images/event-4.jpg',
];

const heroImage = heroImages[Math.floor(Math.random() * heroImages.length)];
</script>

<template>
    <div class="min-h-svh bg-background">
        <NavIsland :user="user" />

        <div class="grid min-h-svh pt-20 lg:grid-cols-2 lg:pt-0">
            <!-- Left panel — hero image + brand copy (desktop only) -->
            <div class="relative hidden overflow-hidden lg:flex">
                <img :src="heroImage" alt="Adventure" class="absolute inset-0 h-full w-full object-cover" />
                <div class="absolute inset-0 bg-gradient-to-br from-slate-950/90 via-slate-950/60 to-transparent" />
                <div class="relative z-10 flex flex-col justify-end p-10 pb-16">
                    <blockquote class="text-white">
                        <p class="mb-4 text-3xl font-black leading-tight">
                            The adventure<br />starts with<br />
                            <span class="text-amber-400">one booking.</span>
                        </p>
                        <footer class="text-sm text-slate-400">
                            Join thousands of explorers who trust Kivulini Adventures for their best weekends.
                        </footer>
                    </blockquote>
                </div>
            </div>

            <!-- Right panel — form -->
            <div class="flex flex-col items-center justify-center bg-background px-6 py-12 md:px-12">
                <div class="w-full max-w-sm space-y-8">
                    <!-- Heading -->
                    <div class="space-y-2 text-center lg:text-left">
                        <h1 class="text-2xl font-black text-foreground">{{ title }}</h1>
                        <p class="text-sm text-muted-foreground">{{ description }}</p>
                    </div>

                    <!-- Form slot -->
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
