<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Menu, Moon, Sun } from '@lucide/vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sheet,
    SheetContent,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useAppearance } from '@/composables/useAppearance';
import { getInitials } from '@/composables/useInitials';
import type { User } from '@/types';

defineProps<{
    user?: User | null;
}>();

const mobileMenuOpen = ref(false);

const { resolvedAppearance, updateAppearance } = useAppearance();

function toggleTheme() {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
}

const navLinks = [
    { title: 'Home', href: '/' },
    { title: 'Events', href: '/events' },
    { title: 'Gallery', href: '/gallery' },
    { title: 'Contact', href: '/contact' },
];
</script>

<template>
    <div class="fixed top-4 left-0 right-0 z-50 flex justify-center px-4">
        <nav class="flex w-full max-w-4xl items-center justify-between rounded-full border border-slate-200/60 bg-white/80 px-6 py-3 shadow-lg backdrop-blur-md transition-colors dark:border-slate-700/50 dark:bg-slate-900/80 dark:shadow-2xl"
            aria-label="Main navigation">
            <!-- Logo -->
            <Link href="/" class="flex shrink-0 items-center gap-2">
                <img :src="resolvedAppearance === 'dark' ? '/assets/images/kivulini_logo.png' : '/assets/images/logo-dark.png'"
                    alt="Kivulini" class="h-12 w-auto object-contain" />
            </Link>

            <!-- Desktop nav links -->
            <div class="hidden items-center gap-6 md:flex">
                <Link v-for="link in navLinks" :key="link.title" :href="link.href"
                    class="text-sm font-medium text-slate-600 transition-colors hover:text-amber-500 dark:text-slate-300 dark:hover:text-amber-400">
                    {{ link.title }}
                </Link>
            </div>

            <!-- Right area -->
            <div class="flex items-center gap-3">
                <!-- Auth: authenticated -->
                <template v-if="user">
                    <DropdownMenu>
                        <DropdownMenuTrigger :as-child="true">
                            <Button variant="ghost" size="icon"
                                class="relative size-9 rounded-full p-0.5 focus-within:ring-2 focus-within:ring-amber-400">
                                <Avatar class="size-8 overflow-hidden rounded-full">
                                    <AvatarImage v-if="user.avatar" :src="user.avatar" :alt="user.name" />
                                    <AvatarFallback
                                        class="rounded-full bg-amber-400 text-xs font-semibold text-slate-900">
                                        {{ getInitials(user.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <UserMenuContent :user="user" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </template>

                <!-- Auth: guest (desktop) -->
                <template v-else>
                    <div class="hidden items-center gap-3 md:flex">
                        <Link href="/login"
                            class="rounded-full bg-amber-500 px-4 py-1.5 text-sm font-semibold text-white transition-colors hover:bg-amber-400 dark:bg-amber-400 dark:text-slate-900 dark:hover:bg-amber-300">
                            Log In
                        </Link>
                    </div>
                </template>

                <!-- Theme toggle -->
                <Button variant="ghost" size="icon"
                    class="h-9 w-9 text-slate-600 hover:text-amber-500 dark:text-slate-300 dark:hover:text-amber-400"
                    :aria-label="resolvedAppearance === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
                    @click="toggleTheme">
                    <Sun v-if="resolvedAppearance === 'dark'" class="h-4 w-4" />
                    <Moon v-else class="h-4 w-4" />
                </Button>

                <!-- Mobile hamburger -->
                <div class="md:hidden">
                    <Sheet v-model:open="mobileMenuOpen">
                        <SheetTrigger :as-child="true">
                            <Button variant="ghost" size="icon" class="h-9 w-9 text-slate-600 dark:text-slate-300">
                                <Menu class="h-5 w-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="right" class="w-72 border-slate-700 bg-slate-900 p-6 text-white">
                            <SheetTitle class="sr-only">Navigation menu</SheetTitle>
                            <nav class="mt-8 flex flex-col gap-4">
                                <Link v-for="link in navLinks" :key="link.title" :href="link.href"
                                    class="text-base font-medium text-slate-300 transition-colors hover:text-amber-400"
                                    @click="mobileMenuOpen = false">
                                    {{ link.title }}
                                </Link>
                                <hr class="border-slate-700" />
                                <template v-if="!user">
                                    <Link href="/login"
                                        class="rounded-full bg-amber-400 px-4 py-2 text-center text-sm font-semibold text-slate-900 transition-colors hover:bg-amber-300"
                                        @click="mobileMenuOpen = false">
                                        Log In
                                    </Link>
                                </template>
                            </nav>
                        </SheetContent>
                    </Sheet>
                </div>
            </div>
        </nav>
    </div>
</template>
