<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import NavIsland from '@/components/events/NavIsland.vue';
import Footer from '@/components/events/Footer.vue';
import type { User } from '@/types';

const page = usePage();
const user = computed(() => (page.props.auth as { user: User | null } | null)?.user ?? null);
</script>

<template>
    <div class="min-h-screen bg-background text-foreground transition-colors flex flex-col justify-between">
        <div>
            <NavIsland :user="user" />
            <slot />
        </div>
        <Footer />
    </div>
</template>
