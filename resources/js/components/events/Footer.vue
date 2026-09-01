<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useAppearance } from '@/composables/useAppearance';

const { resolvedAppearance } = useAppearance();

const currentYear = new Date().getFullYear();

const exploreLinks = [
    { label: 'All Trips & Events', href: '/events' },
    { label: 'Road Trips', href: '/events?type=road_trip' },
    { label: 'Vacations & Getaways', href: '/events?type=vacation' },
];

const categoryLinks = [
    { label: 'Past Trips Gallery', href: '/gallery' },
    { label: 'Contact Us', href: '/contact' },
];

const contactInfo = [
    { label: 'info@kivuliniadventures.com', value: 'mailto:info@kivuliniadventures.com' },
    { label: '+254 725 038 004', value: 'tel:+254725038004' },
    { label: 'Nairobi, Kenya', value: '#' },
];
</script>

<template>
    <footer class="w-full border-t border-border bg-slate-50 py-16 transition-colors dark:bg-slate-950/60">
        <div class="mx-auto max-w-7xl px-4 md:px-8 lg:px-12">
            <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 md:grid-cols-4">
                <!-- Column 1: Brand & Logo -->
                <div class="space-y-4">
                    <Link href="/" class="flex items-center gap-2">
                        <img
                            :src="resolvedAppearance === 'dark' ? '/assets/images/kivulini_logo.png' : '/assets/images/logo-dark.png'"
                            alt="Kivulini"
                            class="h-10 w-auto object-contain transition-opacity"
                        />
                    </Link>
                    <p class="text-sm leading-relaxed text-muted-foreground">
                        Curating unforgettable road trips, scenic hikes, and outdoor experiences across Kenya. Explore nature, build community.
                    </p>
                    <!-- Social Icons -->
                    <div class="flex items-center gap-4 pt-2">
                        <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="rounded-full bg-background p-2 text-muted-foreground border border-border transition-all hover:border-amber-400 hover:text-amber-500 hover:scale-105" aria-label="Instagram">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051C.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </a>
                        <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="rounded-full bg-background p-2 text-muted-foreground border border-border transition-all hover:border-amber-400 hover:text-amber-500 hover:scale-105" aria-label="Facebook">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>
                        </a>
                        <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" class="rounded-full bg-background p-2 text-muted-foreground border border-border transition-all hover:border-amber-400 hover:text-amber-500 hover:scale-105" aria-label="Twitter">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Column 2: Explore -->
                <div class="space-y-4">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-foreground">Explore</h3>
                    <ul class="space-y-2.5">
                        <li v-for="link in exploreLinks" :key="link.label">
                            <Link :href="link.href" class="text-sm text-muted-foreground transition-all duration-200 hover:text-amber-500 hover:translate-x-0.5 inline-block">
                                {{ link.label }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Column 3: Trip Categories -->
                <div class="space-y-4">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-foreground">Categories</h3>
                    <ul class="space-y-2.5">
                        <li v-for="link in categoryLinks" :key="link.label">
                            <Link :href="link.href" class="text-sm text-muted-foreground transition-all duration-200 hover:text-amber-500 hover:translate-x-0.5 inline-block">
                                {{ link.label }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Column 4: Contact -->
                <div class="space-y-4">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-foreground">Contact Us</h3>
                    <ul class="space-y-2.5">
                        <li v-for="item in contactInfo" :key="item.label">
                            <a v-if="item.value !== '#'" :href="item.value" class="text-sm text-muted-foreground transition-colors hover:text-amber-500">
                                {{ item.label }}
                            </a>
                            <span v-else class="text-sm text-muted-foreground">
                                {{ item.label }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Section -->
            <div class="mt-12 border-t border-border pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-xs text-muted-foreground">
                    &copy; {{ currentYear }} Kivulini. All rights reserved.
                </p>
                <div class="flex items-center gap-6">
                    <Link href="#" class="text-xs text-muted-foreground hover:text-amber-500">Privacy Policy</Link>
                    <Link href="#" class="text-xs text-muted-foreground hover:text-amber-500">Terms of Service</Link>
                    <Link href="#" class="text-xs text-muted-foreground hover:text-amber-500">Booking Policy</Link>
                </div>
            </div>
        </div>
    </footer>
</template>
