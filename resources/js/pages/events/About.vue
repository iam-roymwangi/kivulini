<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import SEOHead from '@/components/SEOHead.vue';
import { MapPin, Phone, Mail, Mountain, Users, Heart, Compass, Star, Sparkles, ArrowRight } from '@lucide/vue';

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
        image: '/assets/images/event-4.jpg',
    },
    {
        title: 'Nature Trails & Outdoor Outings',
        description: 'Guided-style outdoor experiences such as the Oloolua Nature Trail Hike.',
        image: '/assets/images/jg_hike.webp',
    },
    {
        title: 'Weekend Getaways',
        description: 'Short escapes delivered under the Your Weekend, Reimagined message.',
        image: '/assets/images/event-2.jpg',
    },
    {
        title: 'Private Group & Custom Itineraries',
        description: 'Tailored trips for friends, teams and organisations on enquiry.',
        image: '/assets/images/lineup.webp',
    },
    {
        title: 'Adventure Apparel',
        description: "Kivulini-branded clothing under the Wear the Adventure line.",
        image: '/assets/images/merch-hero.webp',
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
    {
        name: 'The Oloolua Nature Trail Hike',
        location: 'Karen, Kenya',
        date: '2 June 2026',
        image: '/assets/images/event-2.jpg',
    },
    {
        name: 'Suswa Trip',
        location: 'Suswa, Kenya',
        date: '5 July 2026',
        image: '/assets/images/event-4.jpg',
    },
    {
        name: 'Suswa Part II: The Road Trip',
        location: 'Kenya',
        date: '10 October 2026',
        image: '/assets/images/event-3.jpg',
    },
];


</script>

<template>
    <SEOHead title="About Us"
        description="Kivulini Adventures is a Kenyan adventure and travel experiences brand built around shared outings, carefully chosen destinations and good company. No Tourist Traps. Real Adventures."
        keywords="about Kivulini Adventures, Kenya adventure brand, group travel Kenya, road trips Kenya, Kivulini story"
        canonical="https://kivuliniadventures.com/about" twitter-card="summary" />

    <div class="bg-background transition-colors">

        <!-- ─── Hero ──────────────────────────────────────────────────────── -->
        <section class="relative min-h-[90vh] overflow-hidden">
            <!-- Full-bleed background image -->
            <img src="/assets/images/hero.jpg" alt="Four adventurers standing on a van roof at sunset"
                class="absolute inset-0 h-full w-full object-cover object-center" loading="eager" />
            <!-- Gradient overlay: dark at bottom for text legibility, lighter at top -->
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/70 to-slate-900/30" />

            <div class="relative flex min-h-[90vh] flex-col justify-end pb-20 pt-40">
                <div class="mx-auto w-full max-w-7xl px-4 md:px-8 lg:px-12">
                    <h1
                        class="mt-4 max-w-3xl text-5xl font-black leading-tight tracking-tight text-white sm:text-6xl lg:text-7xl">
                        Adventure Is Better<br />
                        <span class="text-amber-400">Shared.</span>
                    </h1>
                    <p class="mt-2 font-medium italic text-amber-400/80">Mchumia Juani, Hulia Kivulini.</p>
                    <p class="mt-6 max-w-xl text-base leading-relaxed text-slate-300">
                        A Kenyan adventure and travel experiences brand built around shared outings, carefully chosen
                        destinations and good company. We put the experience first.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <Link href="/events"
                            class="inline-flex items-center gap-2 rounded-full bg-amber-400 px-6 py-3 text-sm font-bold text-slate-900 transition-all hover:bg-amber-300 hover:shadow-lg hover:shadow-amber-400/30">
                            <Mountain class="h-4 w-4" />
                            View Our Trips
                        </Link>
                        <Link href="/contact"
                            class="inline-flex items-center gap-2 rounded-full border border-white/30 px-6 py-3 text-sm font-bold text-slate-200 backdrop-blur-sm transition-all hover:border-amber-400 hover:text-amber-400">
                            Get In Touch
                        </Link>
                    </div>

                </div>
            </div>
        </section>

        <!-- ─── Contact band ──────────────────────────────────────────────── -->
        <section class="border-b border-border bg-muted/30 py-10">
            <div class="mx-auto max-w-7xl px-4 md:px-8 lg:px-12">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-400/10 text-amber-500 dark:bg-amber-400/20">
                            <MapPin class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Based In</p>
                            <p class="font-semibold text-foreground">Nairobi, Kenya</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-400/10 text-amber-500 dark:bg-amber-400/20">
                            <Phone class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Call Us</p>
                            <a href="tel:+254725038004"
                                class="font-semibold text-foreground transition-colors hover:text-amber-500">+254 725
                                038 004</a>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-400/10 text-amber-500 dark:bg-amber-400/20">
                            <Mail class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Email Us</p>
                            <a href="mailto:info@kivuliniadventures.com"
                                class="font-semibold text-foreground transition-colors hover:text-amber-500">info@kivuliniadventures.com</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ─── About Story ───────────────────────────────────────────────── -->
        <section class="py-24">
            <div class="mx-auto max-w-7xl px-4 md:px-8 lg:px-12">
                <div class="grid grid-cols-1 gap-16 lg:grid-cols-2 lg:items-start">

                    <!-- Story text + vision/mission -->
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wider text-amber-500">About Us</p>
                        <h2 class="mt-3 text-4xl font-black text-foreground sm:text-5xl">
                            The Journey Matters<br />
                            <span class="text-amber-500">As Much As</span> the Destination.
                        </h2>
                        <div class="mt-6 space-y-4 text-base leading-relaxed text-muted-foreground">
                            <p>
                                At Kivulini Adventures the journey matters as much as the destination. Every trip is
                                shaped around the things
                                that make an outing memorable: a thoughtfully selected place, a welcoming crew, time
                                outdoors and the freedom
                                to enjoy the moment.
                            </p>
                            <p>
                                Whether it is a road trip, a nature hike or a weekend away, we want every adventure to
                                feel like a story
                                worth telling.
                            </p>
                        </div>

                        <div class="mt-10 space-y-6">
                            <div class="rounded-2xl border border-border bg-card p-6 shadow-xs">
                                <p class="text-xs font-bold uppercase tracking-wider text-amber-500">Our Vision</p>
                                <p class="mt-2 text-base leading-relaxed text-foreground">
                                    To inspire people to explore more, connect with others and create meaningful
                                    memories through shared adventures.
                                </p>
                            </div>
                            <div class="rounded-2xl border border-border bg-card p-6 shadow-xs">
                                <p class="text-xs font-bold uppercase tracking-wider text-amber-500">Our Mission</p>
                                <p class="mt-2 text-base leading-relaxed text-foreground">
                                    To bring people together through thoughtfully presented trips that make discovering
                                    new places enjoyable,
                                    welcoming and memorable.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Stacked image collage -->
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Tall left column -->
                        <div class="overflow-hidden rounded-2xl">
                            <img src="/assets/images/jg_hike.webp" alt="Group on the Oloolua nature trail bridge"
                                class="h-full w-full object-cover transition-transform duration-500 hover:scale-105"
                                loading="lazy" />
                        </div>
                        <!-- Right column: two stacked -->
                        <div class="flex flex-col gap-3">
                            <div class="overflow-hidden rounded-2xl">
                                <img src="/assets/images/event-2.jpg" alt="Happy group selfie on a Kivulini hike"
                                    class="aspect-[4/3] w-full object-cover transition-transform duration-500 hover:scale-105"
                                    loading="lazy" />
                            </div>
                            <div class="overflow-hidden rounded-2xl">
                                <img src="/assets/images/meal.webp" alt="The crew sharing a meal together"
                                    class="aspect-[4/3] w-full object-cover object-top transition-transform duration-500 hover:scale-105"
                                    loading="lazy" />
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- ─── Core Values ───────────────────────────────────────────────── -->
        <section class="border-t border-border bg-muted/30 py-24">
            <div class="mx-auto max-w-7xl px-4 md:px-8 lg:px-12">
                <div class="grid grid-cols-1 gap-16 lg:grid-cols-2 lg:items-center">

                    <!-- Values list -->
                    <div class="order-2 lg:order-1">
                        <p class="text-sm font-bold uppercase tracking-wider text-amber-500">Core Values</p>
                        <h2 class="mt-3 text-4xl font-black text-foreground sm:text-5xl">What We<br /><span
                                class="text-amber-500">Stand For</span></h2>
                        <ul class="mt-10 space-y-4">
                            <li v-for="value in coreValues" :key="value.title"
                                class="flex items-start gap-4 rounded-2xl border border-border bg-card p-5 shadow-xs transition-all hover:shadow-md">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-400/10 text-amber-500 dark:bg-amber-400/20">
                                    <component :is="value.icon" class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="font-bold text-foreground">{{ value.title }}</p>
                                    <p class="mt-1 text-sm leading-relaxed text-muted-foreground">{{ value.description
                                        }}</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Full-height image -->
                    <div class="order-1 lg:order-2">
                        <div class="relative overflow-hidden rounded-3xl shadow-2xl">
                            <img src="/assets/images/lineup.webp" alt="The Kivulini crew lined up on a trail"
                                class="aspect-[3/4] w-full object-cover transition-transform duration-700 hover:scale-105"
                                loading="lazy" />
                            <!-- Overlay caption -->
                            <div
                                class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-slate-950/90 to-transparent p-6">
                                <p class="text-xs font-bold uppercase tracking-wider text-amber-400">The Kivulini Crew
                                </p>
                                <p class="mt-1 text-sm text-slate-300">Every trip, a new story.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- ─── What We Offer ─────────────────────────────────────────────── -->
        <section class="py-24">
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
                    <div v-for="(item, index) in offerings" :key="item.title"
                        class="group overflow-hidden rounded-2xl border border-border bg-card shadow-xs transition-all hover:border-amber-400/40 hover:shadow-md">
                        <!-- Image thumbnail -->
                        <div class="overflow-hidden">
                            <img :src="item.image" :alt="item.title"
                                class="aspect-video w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                loading="lazy" />
                        </div>
                        <div class="p-6">
                            <div
                                class="mb-3 inline-flex h-8 w-8 items-center justify-center rounded-xl bg-amber-400/10 text-amber-500 text-sm font-black dark:bg-amber-400/20">
                                {{ String(index + 1).padStart(2, '0') }}
                            </div>
                            <h3 class="font-bold text-foreground">{{ item.title }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">{{ item.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ─── The Kivulini Experience (mosaic + copy) ───────────────────── -->
        <section class="border-t border-border bg-muted/30 py-24">
            <div class="mx-auto max-w-7xl px-4 md:px-8 lg:px-12">
                <div class="grid grid-cols-1 gap-16 lg:grid-cols-2 lg:items-center">

                    <!-- Mosaic grid -->
                    <div class="grid grid-cols-3 grid-rows-2 gap-3">
                        <!-- Tall left image (spans 2 rows) -->
                        <div class="row-span-2 overflow-hidden rounded-2xl">
                            <img src="/assets/images/jg_hike.webp" alt="Crew on the Oloolua trail bridge"
                                class="h-full w-full object-cover transition-transform duration-500 hover:scale-105"
                                loading="lazy" />
                        </div>
                        <!-- Top-right spanning 2 columns -->
                        <div class="col-span-2 overflow-hidden rounded-2xl">
                            <img src="/assets/images/meal.webp" alt="Group sharing a meal together"
                                class="aspect-video w-full object-cover object-top transition-transform duration-500 hover:scale-105"
                                loading="lazy" />
                        </div>
                        <!-- Bottom-right 2 cells -->
                        <div class="overflow-hidden rounded-2xl">
                            <img src="/assets/images/event-2.jpg" alt="Trail selfie on a group hike"
                                class="aspect-square w-full object-cover transition-transform duration-500 hover:scale-105"
                                loading="lazy" />
                        </div>
                        <div class="overflow-hidden rounded-2xl">
                            <img src="/assets/images/event-5.jpg" alt="Adventurers on the road"
                                class="aspect-square w-full object-cover transition-transform duration-500 hover:scale-105"
                                loading="lazy" />
                        </div>
                    </div>

                    <!-- Copy -->
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wider text-amber-500">The Kivulini Experience</p>
                        <h2 class="mt-3 text-4xl font-black text-foreground sm:text-5xl">
                            Handpicked Locations.<br />
                            <span class="text-amber-500">Unforgettable Crew.</span>
                        </h2>
                        <p class="mt-6 text-base leading-relaxed text-muted-foreground">
                            A Kivulini adventure is a chance to leave the everyday behind, explore somewhere different
                            and share the day with
                            people who make the journey worthwhile. Group photographs, open landscapes and moments
                            shared on the road are at
                            the heart of everything we do.
                        </p>

                        <!-- Track record -->
                        <p class="mt-10 mb-4 text-xs font-bold uppercase tracking-wider text-muted-foreground">Completed
                            Adventures</p>
                        <ul class="space-y-3">
                            <li v-for="adventure in completedAdventures" :key="adventure.name"
                                class="flex items-center gap-4 overflow-hidden rounded-2xl border border-border bg-card shadow-xs transition-all hover:shadow-md">
                                <div class="h-16 w-16 shrink-0 overflow-hidden">
                                    <img :src="adventure.image" :alt="adventure.name" class="h-full w-full object-cover"
                                        loading="lazy" />
                                </div>
                                <div class="min-w-0 flex-1 py-3 pr-4">
                                    <p class="truncate font-bold text-foreground text-sm">{{ adventure.name }}</p>
                                    <div
                                        class="mt-0.5 flex flex-wrap items-center gap-1.5 text-xs text-muted-foreground">
                                        <span class="flex items-center gap-1">
                                            <MapPin class="h-3 w-3" />{{ adventure.location }}
                                        </span>
                                        <span>·</span>
                                        <span>{{ adventure.date }}</span>
                                    </div>
                                </div>
                            </li>
                        </ul>

                        <Link href="/gallery"
                            class="mt-8 inline-flex items-center gap-2 rounded-full bg-amber-400/10 px-5 py-2.5 text-sm font-bold text-amber-600 transition-all hover:bg-amber-400/20 dark:text-amber-400">
                            View Our Gallery
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                    </div>

                </div>
            </div>
        </section>

        <!-- ─── Why Choose Kivulini ────────────────────────────────────────── -->
        <section class="py-24">
            <div class="mx-auto max-w-7xl px-4 md:px-8 lg:px-12">
                <div class="text-center">
                    <p class="text-sm font-bold uppercase tracking-wider text-amber-500">Why Choose Kivulini</p>
                    <h2 class="mt-3 text-4xl font-black text-foreground sm:text-5xl">
                        Five Reasons to <span class="text-amber-500">Adventure With Us</span>
                    </h2>
                </div>

                <div class="mx-auto mt-14 max-w-4xl">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="reason in whyKivulini" :key="reason.title"
                            class="rounded-2xl border border-border bg-card p-6 shadow-xs transition-all hover:border-amber-400/40 hover:shadow-md">
                            <div class="mb-3 h-1.5 w-8 rounded-full bg-amber-400" />
                            <h3 class="font-bold text-foreground">{{ reason.title }}</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-muted-foreground">{{ reason.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ─── CTA banner ─────────────────────────────────────────────────── -->
        <section class="relative overflow-hidden">
            <!-- Background image with overlay -->
            <img src="/assets/images/event-4.jpg" alt="Kivulini adventure road trip"
                class="absolute inset-0 h-full w-full object-cover" loading="lazy" />
            <div class="absolute inset-0 bg-slate-950/80" />

            <div class="relative py-28">
                <div class="mx-auto max-w-7xl px-4 text-center md:px-8 lg:px-12">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-400">Nairobi, Kenya</p>
                    <h2 class="mt-4 text-4xl font-black text-white sm:text-5xl">
                        Ready to Write Your<br />
                        <span class="text-amber-400">Next Story?</span>
                    </h2>
                    <p class="mx-auto mt-4 max-w-lg text-base text-slate-300">
                        Browse upcoming adventures and book your spot, or reach out to us for a private group trip or
                        custom itinerary.
                    </p>
                    <div class="mt-8 flex flex-wrap justify-center gap-4">
                        <Link href="/events"
                            class="inline-flex items-center gap-2 rounded-full bg-amber-400 px-7 py-3 text-sm font-bold text-slate-900 transition-all hover:bg-amber-300 hover:shadow-lg hover:shadow-amber-400/30">
                            <Mountain class="h-4 w-4" />
                            Browse Trips
                        </Link>
                        <Link href="/contact"
                            class="inline-flex items-center gap-2 rounded-full border border-white/30 px-7 py-3 text-sm font-bold text-slate-200 backdrop-blur-sm transition-all hover:border-amber-400 hover:text-amber-400">
                            Contact Us
                        </Link>
                    </div>
                    <p class="mt-12 font-medium italic text-slate-500">Mchumia Juani, Hulia Kivulini.</p>
                </div>
            </div>
        </section>

    </div>
</template>
