<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import SEOHead from '@/components/SEOHead.vue';
import { MapPin, Phone, Mail, Mountain, Users, Heart, Compass, Star, Sparkles } from '@lucide/vue';

const coreValues = [
    {
        icon: Star,
        title: 'Authenticity',
        description: 'Real experiences, honest communication and a clear point of view.',
    },
    {
        icon: Users,
        title: 'Togetherness',
        description: 'Making room for good company and shared memories.',
    },
    {
        icon: Compass,
        title: 'Discovery',
        description: 'Encouraging curiosity and a willingness to explore.',
    },
    {
        icon: Sparkles,
        title: 'Thoughtfulness',
        description: 'Giving care to the choice and presentation of every experience.',
    },
    {
        icon: Heart,
        title: 'Enjoyment',
        description: 'Keeping the spirit of adventure welcoming, positive and fun.',
    },
];

const offerings = [
    {
        title: 'Group Road Trips',
        description: 'Shared trips built around the journey, the destination and the people along for the ride.',
    },
    {
        title: 'Nature Trails & Outdoor Outings',
        description: 'Guided-style outdoor experiences such as the Oloolua Nature Trail Hike.',
    },
    {
        title: 'Weekend Getaways',
        description: 'Short escapes delivered under the Your Weekend, Reimagined message.',
    },
    {
        title: 'Private Group & Custom Itineraries',
        description: 'Tailored trips for friends, teams and organisations on enquiry.',
    },
    {
        title: 'Adventure Apparel',
        description: "Kivulini-branded clothing under the Wear the Adventure line.",
    },
];

const whyKivulini = [
    { title: 'A clear point of view', description: 'Real adventures, not tourist traps.' },
    { title: 'Good company', description: 'The crew and the shared experience come first.' },
    { title: 'Variety', description: 'Road trips, nature trails and weekend escapes.' },
    { title: 'Flexibility', description: 'Private group bookings and custom itineraries on request.' },
    { title: 'A brand you can wear', description: 'Kivulini apparel carries the adventure beyond the trip.' },
];

const completedAdventures = [
    { name: 'The Oloolua Nature Trail Hike', location: 'Karen, Kenya', date: '2 June 2026' },
    { name: 'Suswa Trip', location: 'Suswa, Kenya', date: '5 July 2026' },
    { name: 'Suswa Part II: The Road Trip', location: 'Kenya', date: '10 October 2026' },
];
</script>

<template>
    <SEOHead
        title="About Us"
        description="Kivulini Adventures is a Kenyan adventure and travel experiences brand built around shared outings, carefully chosen destinations and good company. No Tourist Traps. Real Adventures."
        keywords="about Kivulini Adventures, Kenya adventure brand, group travel Kenya, road trips Kenya, Kivulini story"
        canonical="https://kivuliniadventures.com/about"
        twitter-card="summary"
    />

    <div class="bg-background transition-colors">

        <!-- ─── Hero ─────────────────────────────────────────────── -->
        <section class="relative overflow-hidden bg-slate-900 pt-40 pb-24">
            <!-- Decorative amber orbs -->
            <div class="pointer-events-none absolute -top-32 -left-32 h-96 w-96 rounded-full bg-amber-400/10 blur-3xl" aria-hidden="true" />
            <div class="pointer-events-none absolute -bottom-16 right-0 h-80 w-80 rounded-full bg-amber-400/8 blur-3xl" aria-hidden="true" />

            <div class="relative mx-auto max-w-7xl px-4 md:px-8 lg:px-12">
                <div class="max-w-3xl">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-400">
                        2026 Company Profile &nbsp;·&nbsp; Kivulini Adventures
                    </p>
                    <h1 class="mt-4 text-5xl font-black leading-tight tracking-tight text-white sm:text-6xl lg:text-7xl">
                        Adventure Is Better<br />
                        <span class="text-amber-400">Shared.</span>
                    </h1>
                    <p class="mt-2 font-medium italic text-amber-400/80">Mchumia Juani, Hulia Kivulini.</p>
                    <p class="mt-6 max-w-xl text-base leading-relaxed text-slate-300">
                        A Kenyan adventure and travel experiences brand built around shared outings, carefully chosen
                        destinations and good company. We put the experience first.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <Link
                            href="/events"
                            class="inline-flex items-center gap-2 rounded-full bg-amber-400 px-6 py-3 text-sm font-bold text-slate-900 transition-all hover:bg-amber-300 hover:shadow-lg hover:shadow-amber-400/30"
                        >
                            <Mountain class="h-4 w-4" />
                            View Our Trips
                        </Link>
                        <Link
                            href="/contact"
                            class="inline-flex items-center gap-2 rounded-full border border-slate-600 px-6 py-3 text-sm font-bold text-slate-200 transition-all hover:border-amber-400 hover:text-amber-400"
                        >
                            Get In Touch
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- ─── Overview Band ─────────────────────────────────────── -->
        <section class="border-b border-border bg-muted/30 py-12">
            <div class="mx-auto max-w-7xl px-4 md:px-8 lg:px-12">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-400/10 text-amber-500 dark:bg-amber-400/20">
                            <MapPin class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Based In</p>
                            <p class="font-semibold text-foreground">Nairobi, Kenya</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-400/10 text-amber-500 dark:bg-amber-400/20">
                            <Phone class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Call Us</p>
                            <a href="tel:+254725038004" class="font-semibold text-foreground transition-colors hover:text-amber-500">+254 725 038 004</a>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-400/10 text-amber-500 dark:bg-amber-400/20">
                            <Mail class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Email Us</p>
                            <a href="mailto:info@kivuliniadventures.com" class="font-semibold text-foreground transition-colors hover:text-amber-500">info@kivuliniadventures.com</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ─── About Story ───────────────────────────────────────── -->
        <section class="py-24">
            <div class="mx-auto max-w-7xl px-4 md:px-8 lg:px-12">
                <div class="grid grid-cols-1 gap-16 lg:grid-cols-2 lg:items-start">
                    <!-- Story text -->
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wider text-amber-500">About Us</p>
                        <h2 class="mt-3 text-4xl font-black text-foreground sm:text-5xl">
                            The Journey Matters<br />
                            <span class="text-amber-500">As Much As</span> the Destination.
                        </h2>
                        <div class="mt-6 space-y-4 text-base leading-relaxed text-muted-foreground">
                            <p>
                                At Kivulini Adventures the journey matters as much as the destination. Every trip is shaped around the things
                                that make an outing memorable: a thoughtfully selected place, a welcoming crew, time outdoors and the freedom
                                to enjoy the moment.
                            </p>
                            <p>
                                Whether it is a road trip, a nature hike or a weekend away, we want every adventure to feel like a story
                                worth telling.
                            </p>
                        </div>

                        <!-- Vision & Mission -->
                        <div class="mt-10 space-y-6">
                            <div class="rounded-2xl border border-border bg-card p-6 shadow-xs">
                                <p class="text-xs font-bold uppercase tracking-wider text-amber-500">Our Vision</p>
                                <p class="mt-2 text-base leading-relaxed text-foreground">
                                    To inspire people to explore more, connect with others and create meaningful memories through shared adventures.
                                </p>
                            </div>
                            <div class="rounded-2xl border border-border bg-card p-6 shadow-xs">
                                <p class="text-xs font-bold uppercase tracking-wider text-amber-500">Our Mission</p>
                                <p class="mt-2 text-base leading-relaxed text-foreground">
                                    To bring people together through thoughtfully presented trips that make discovering new places enjoyable,
                                    welcoming and memorable.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Core Values -->
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wider text-amber-500">Core Values</p>
                        <h3 class="mt-3 text-2xl font-black text-foreground">What We Stand For</h3>
                        <ul class="mt-8 space-y-5">
                            <li
                                v-for="value in coreValues"
                                :key="value.title"
                                class="flex items-start gap-4 rounded-2xl border border-border bg-card p-5 shadow-xs transition-all hover:shadow-md"
                            >
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-400/10 text-amber-500 dark:bg-amber-400/20">
                                    <component :is="value.icon" class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="font-bold text-foreground">{{ value.title }}</p>
                                    <p class="mt-1 text-sm leading-relaxed text-muted-foreground">{{ value.description }}</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- ─── What We Offer ─────────────────────────────────────── -->
        <section class="border-t border-border bg-muted/30 py-24">
            <div class="mx-auto max-w-7xl px-4 md:px-8 lg:px-12">
                <div class="text-center">
                    <p class="text-sm font-bold uppercase tracking-wider text-amber-500">What We Offer</p>
                    <h2 class="mt-3 text-4xl font-black text-foreground sm:text-5xl">
                        Every Kind of <span class="text-amber-500">Adventure</span>
                    </h2>
                    <p class="mx-auto mt-4 max-w-xl text-base text-muted-foreground">
                        From guided hikes to weekend road trips and everything in between.
                    </p>
                </div>

                <div class="mt-14 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(item, index) in offerings"
                        :key="item.title"
                        class="group rounded-2xl border border-border bg-card p-6 shadow-xs transition-all hover:border-amber-400/40 hover:shadow-md"
                    >
                        <div class="mb-4 inline-flex h-9 w-9 items-center justify-center rounded-xl bg-amber-400/10 text-amber-500 text-sm font-black dark:bg-amber-400/20">
                            {{ String(index + 1).padStart(2, '0') }}
                        </div>
                        <h3 class="font-bold text-foreground">{{ item.title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-muted-foreground">{{ item.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ─── Track Record ──────────────────────────────────────── -->
        <section class="py-24">
            <div class="mx-auto max-w-7xl px-4 md:px-8 lg:px-12">
                <div class="grid grid-cols-1 gap-16 lg:grid-cols-2 lg:items-center">
                    <!-- The Kivulini Experience copy -->
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wider text-amber-500">The Kivulini Experience</p>
                        <h2 class="mt-3 text-4xl font-black text-foreground sm:text-5xl">
                            Handpicked Locations.<br />
                            <span class="text-amber-500">Unforgettable Crew.</span>
                        </h2>
                        <p class="mt-6 text-base leading-relaxed text-muted-foreground">
                            A Kivulini adventure is a chance to leave the everyday behind, explore somewhere different and share the day with
                            people who make the journey worthwhile. Group photographs, open landscapes and moments shared on the road are at
                            the heart of everything we do.
                        </p>
                        <Link
                            href="/gallery"
                            class="mt-8 inline-flex items-center gap-2 rounded-full bg-amber-400/10 px-5 py-2.5 text-sm font-bold text-amber-600 transition-all hover:bg-amber-400/20 dark:text-amber-400"
                        >
                            View Our Gallery →
                        </Link>
                    </div>

                    <!-- Completed Adventures -->
                    <div>
                        <p class="mb-6 text-sm font-bold uppercase tracking-wider text-muted-foreground">Track Record: Completed Adventures</p>
                        <ul class="space-y-4">
                            <li
                                v-for="adventure in completedAdventures"
                                :key="adventure.name"
                                class="flex items-start gap-4 rounded-2xl border border-border bg-card p-5 shadow-xs"
                            >
                                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-400/10 text-amber-500 dark:bg-amber-400/20">
                                    <Mountain class="h-4 w-4" />
                                </div>
                                <div>
                                    <p class="font-bold text-foreground">{{ adventure.name }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                        <span class="flex items-center gap-1">
                                            <MapPin class="h-3 w-3" />{{ adventure.location }}
                                        </span>
                                        <span>·</span>
                                        <span>{{ adventure.date }}</span>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- ─── Why Choose Kivulini ────────────────────────────────── -->
        <section class="border-t border-border bg-muted/30 py-24">
            <div class="mx-auto max-w-7xl px-4 md:px-8 lg:px-12">
                <div class="text-center">
                    <p class="text-sm font-bold uppercase tracking-wider text-amber-500">Why Choose Kivulini</p>
                    <h2 class="mt-3 text-4xl font-black text-foreground sm:text-5xl">
                        Five Reasons to <span class="text-amber-500">Adventure With Us</span>
                    </h2>
                </div>

                <div class="mx-auto mt-14 max-w-4xl">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="reason in whyKivulini"
                            :key="reason.title"
                            class="rounded-2xl border border-border bg-card p-6 shadow-xs transition-all hover:border-amber-400/40 hover:shadow-md"
                        >
                            <div class="mb-3 h-1.5 w-8 rounded-full bg-amber-400" />
                            <h3 class="font-bold text-foreground">{{ reason.title }}</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-muted-foreground">{{ reason.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ─── CTA Banner ────────────────────────────────────────── -->
        <section class="bg-slate-900 py-24">
            <div class="mx-auto max-w-7xl px-4 text-center md:px-8 lg:px-12">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-400">Nairobi, Kenya</p>
                <h2 class="mt-4 text-4xl font-black text-white sm:text-5xl">
                    Ready to Write Your<br />
                    <span class="text-amber-400">Next Story?</span>
                </h2>
                <p class="mx-auto mt-4 max-w-lg text-base text-slate-300">
                    Browse upcoming adventures and book your spot, or reach out to us for a private group trip or custom itinerary.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <Link
                        href="/events"
                        class="inline-flex items-center gap-2 rounded-full bg-amber-400 px-7 py-3 text-sm font-bold text-slate-900 transition-all hover:bg-amber-300 hover:shadow-lg hover:shadow-amber-400/30"
                    >
                        <Mountain class="h-4 w-4" />
                        Browse Trips
                    </Link>
                    <Link
                        href="/contact"
                        class="inline-flex items-center gap-2 rounded-full border border-slate-600 px-7 py-3 text-sm font-bold text-slate-200 transition-all hover:border-amber-400 hover:text-amber-400"
                    >
                        Contact Us
                    </Link>
                </div>
                <p class="mt-12 font-medium italic text-slate-500">Mchumia Juani, Hulia Kivulini.</p>
            </div>
        </section>

    </div>
</template>
