<script setup lang="ts">
import { ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, X, Moon, Sun } from '@lucide/vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useAppearance } from '@/composables/useAppearance';
import { getInitials } from '@/composables/useInitials';
import type { User } from '@/types';

defineProps<{
    user?: User | null;
}>();

const page = usePage();
const mobileOpen = ref(false);

const { resolvedAppearance, updateAppearance } = useAppearance();

function toggleTheme() {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
}

// Close mobile menu on route change
watch(() => page.url, () => { mobileOpen.value = false; });

// Lock body scroll while menu is open
watch(mobileOpen, (val) => {
    document.body.style.overflow = val ? 'hidden' : '';
});

const navLinks = [
    { title: 'Home',    href: '/',            icon: '🏠' },
    { title: 'Events',  href: '/events',       icon: '🏕️' },
    { title: 'Merch',   href: '/merchandise',  icon: '👕' },
    { title: 'Gallery', href: '/gallery',      icon: '🖼️' },
    { title: 'About',   href: '/about',        icon: '✨' },
    { title: 'Contact', href: '/contact',      icon: '💬' },
];

function isActive(href: string): boolean {
    if (href === '/') { return page.url === '/'; }
    return page.url.startsWith(href);
}
</script>

<template>
    <div class="fixed top-4 left-0 right-0 z-50 flex justify-center px-4">
        <nav
            class="flex w-full max-w-4xl items-center justify-between rounded-full border border-slate-200/60 bg-white/80 px-6 py-3 shadow-lg backdrop-blur-md transition-colors dark:border-slate-700/50 dark:bg-slate-900/80"
            aria-label="Main navigation"
        >
            <!-- Logo -->
            <Link href="/" class="flex shrink-0 items-center gap-2">
                <img
                    :src="resolvedAppearance === 'dark' ? '/assets/images/kivulini_logo.png' : '/assets/images/logo-dark.png'"
                    alt="Kivulini Adventures"
                    class="h-12 w-auto object-contain"
                />
            </Link>

            <!-- Desktop links -->
            <div class="hidden items-center gap-6 md:flex">
                <Link
                    v-for="link in navLinks"
                    :key="link.title"
                    :href="link.href"
                    class="relative text-sm font-medium transition-colors"
                    :class="isActive(link.href)
                        ? 'text-amber-500 dark:text-amber-400'
                        : 'text-slate-600 hover:text-amber-500 dark:text-slate-300 dark:hover:text-amber-400'"
                >
                    {{ link.title }}
                    <span
                        v-if="isActive(link.href)"
                        class="absolute -bottom-1 left-0 h-0.5 w-full rounded-full bg-amber-400"
                    />
                </Link>
            </div>

            <!-- Right area -->
            <div class="flex items-center gap-2">
                <!-- Authenticated avatar -->
                <template v-if="user">
                    <DropdownMenu>
                        <DropdownMenuTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="relative size-9 rounded-full p-0.5 focus-within:ring-2 focus-within:ring-amber-400"
                            >
                                <Avatar class="size-8 overflow-hidden rounded-full">
                                    <AvatarImage v-if="user.avatar" :src="user.avatar" :alt="user.name" />
                                    <AvatarFallback class="rounded-full bg-amber-400 text-xs font-semibold text-slate-900">
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

                <!-- Theme toggle -->
                <Button
                    variant="ghost"
                    size="icon"
                    class="h-9 w-9 text-slate-600 hover:text-amber-500 dark:text-slate-300 dark:hover:text-amber-400"
                    :aria-label="resolvedAppearance === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
                    @click="toggleTheme"
                >
                    <Sun v-if="resolvedAppearance === 'dark'" class="h-4 w-4" />
                    <Moon v-else class="h-4 w-4" />
                </Button>

                <!-- Mobile hamburger -->
                <button
                    class="relative flex h-9 w-9 items-center justify-center rounded-full text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white md:hidden"
                    :aria-label="mobileOpen ? 'Close menu' : 'Open menu'"
                    :aria-expanded="mobileOpen"
                    @click="mobileOpen = !mobileOpen"
                >
                    <Transition
                        mode="out-in"
                        enter-active-class="transition-all duration-200"
                        enter-from-class="opacity-0 rotate-90 scale-75"
                        leave-active-class="transition-all duration-150"
                        leave-to-class="opacity-0 -rotate-90 scale-75"
                    >
                        <X v-if="mobileOpen" class="h-5 w-5" />
                        <Menu v-else class="h-5 w-5" />
                    </Transition>
                </button>
            </div>
        </nav>
    </div>

    <!-- ── Mobile full-screen overlay ── -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0"
            leave-active-class="transition-all duration-250 ease-in"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileOpen"
                class="fixed inset-0 z-40 md:hidden"
                aria-modal="true"
                role="dialog"
                aria-label="Mobile navigation"
            >
                <!-- Backdrop -->
                <div
                    class="absolute inset-0 bg-black/60 backdrop-blur-sm"
                    @click="mobileOpen = false"
                />

                <!-- Panel — slides up from bottom -->
                <Transition
                    appear
                    enter-active-class="transition-transform duration-300 ease-out"
                    enter-from-class="translate-y-full"
                    leave-active-class="transition-transform duration-250 ease-in"
                    leave-to-class="translate-y-full"
                >
                    <div
                        v-if="mobileOpen"
                        class="absolute bottom-0 left-0 right-0 rounded-t-3xl bg-white px-6 pb-10 pt-6 shadow-2xl dark:bg-slate-900"
                    >
                        <!-- Handle bar -->
                        <div class="mx-auto mb-6 h-1 w-10 rounded-full bg-slate-200 dark:bg-slate-700" />

                        <!-- Nav links with staggered fade-in -->
                        <nav class="space-y-1" aria-label="Mobile navigation links">
                            <TransitionGroup
                                appear
                                enter-active-class="transition-all duration-300"
                                enter-from-class="opacity-0 translate-x-4"
                            >
                                <Link
                                    v-for="(link, i) in navLinks"
                                    :key="link.href"
                                    :href="link.href"
                                    class="flex items-center gap-4 rounded-2xl px-4 py-3.5 text-base font-semibold transition-colors"
                                    :class="isActive(link.href)
                                        ? 'bg-amber-50 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400'
                                        : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white'"
                                    :style="{ transitionDelay: `${i * 40}ms` }"
                                >
                                    <span class="text-xl" aria-hidden="true">{{ link.icon }}</span>
                                    {{ link.title }}
                                    <span
                                        v-if="isActive(link.href)"
                                        class="ml-auto h-2 w-2 rounded-full bg-amber-400"
                                    />
                                </Link>
                            </TransitionGroup>
                        </nav>

                        <!-- Divider -->
                        <div class="my-5 border-t border-slate-100 dark:border-slate-800" />

                        <!-- Bottom row: theme toggle + social links + auth -->
                        <div class="flex items-center justify-between">
                            <button
                                class="flex items-center gap-2.5 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800"
                                @click="toggleTheme"
                            >
                                <Sun v-if="resolvedAppearance === 'dark'" class="h-4 w-4 text-amber-400" />
                                <Moon v-else class="h-4 w-4" />
                                {{ resolvedAppearance === 'dark' ? 'Light mode' : 'Dark mode' }}
                            </button>

                            <div class="flex items-center gap-2">
                                <!-- Social links -->
                                <a
                                    href="https://www.instagram.com/_kivulini.adventures/"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label="Instagram"
                                    class="rounded-full border border-slate-200 p-2 text-slate-500 transition-all hover:border-amber-400 hover:text-amber-500 dark:border-slate-700 dark:text-slate-400"
                                >
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051C.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                                </a>
                                <a
                                    href="https://www.tiktok.com/@kivuliniadventures"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label="TikTok"
                                    class="rounded-full border border-slate-200 p-2 text-slate-500 transition-all hover:border-amber-400 hover:text-amber-500 dark:border-slate-700 dark:text-slate-400"
                                >
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.52V6.75a4.85 4.85 0 01-1.01-.06z"/></svg>
                                </a>

                                <template v-if="!user">
                                    <Link
                                        href="/login"
                                        class="rounded-xl bg-amber-400 px-5 py-2.5 text-sm font-bold text-slate-900 shadow-sm transition-colors hover:bg-amber-500"
                                    >
                                        Sign in
                                    </Link>
                                </template>
                                <template v-else>
                                    <div class="flex items-center gap-3">
                                        <Avatar class="size-8 overflow-hidden rounded-full">
                                            <AvatarImage v-if="user.avatar" :src="user.avatar" :alt="user.name" />
                                            <AvatarFallback class="rounded-full bg-amber-400 text-xs font-semibold text-slate-900">
                                                {{ getInitials(user.name) }}
                                            </AvatarFallback>
                                        </Avatar>
                                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ user.name }}</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
