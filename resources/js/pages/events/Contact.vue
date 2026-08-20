<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Mail, Phone, MapPin, Send, CheckCircle2 } from '@lucide/vue';

const form = ref({
    name: '',
    email: '',
    subject: '',
    message: '',
});

const submitting = ref(false);
const submitted = ref(false);
const errors = ref<Record<string, string>>({});

function submitForm() {
    errors.value = {};
    if (!form.value.name.trim()) { errors.value.name = 'Name is required'; }
    if (!form.value.email.trim()) {
        errors.value.email = 'Email is required';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
        errors.value.email = 'Please enter a valid email address';
    }
    if (!form.value.message.trim()) { errors.value.message = 'Message is required'; }

    if (Object.keys(errors.value).length > 0) { return; }

    submitting.value = true;
    
    // Simulate API request
    setTimeout(() => {
        submitting.value = false;
        submitted.value = true;
        form.value = { name: '', email: '', subject: '', message: '' };
    }, 1200);
}
</script>

<template>
    <Head title="Contact Us" />

    <div class="bg-background pt-32 pb-24 transition-colors">
        <div class="mx-auto max-w-7xl px-4 md:px-8 lg:px-12">
            <!-- Header section -->
            <div class="mb-12 border-b border-border pb-8 text-center sm:text-left">
                <p class="text-sm font-bold uppercase tracking-wider text-amber-500">Get in touch</p>
                <h1 class="mt-2 text-4xl font-black text-foreground sm:text-5xl">
                    We'd Love to Hear <span class="text-amber-500">From You</span>
                </h1>
                <p class="mt-4 max-w-2xl text-base text-muted-foreground">
                    Have questions about our upcoming hikes, safety guidelines, custom group bookings, or partnerships? Message us directly below.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-12 lg:grid-cols-12">
                <!-- Left side: Contact Cards -->
                <div class="space-y-6 lg:col-span-5">
                    <div class="rounded-2xl border border-border bg-card p-6 shadow-xs transition-all hover:shadow-md">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-400/10 text-amber-500 dark:bg-amber-400/20">
                                <Mail class="h-6 w-6" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold uppercase tracking-wider text-muted-foreground">Email Us</h3>
                                <p class="mt-1 font-bold text-foreground">info@kivulini.co.ke</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-border bg-card p-6 shadow-xs transition-all hover:shadow-md">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-400/10 text-amber-500 dark:bg-amber-400/20">
                                <Phone class="h-6 w-6" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold uppercase tracking-wider text-muted-foreground">Call Us</h3>
                                <p class="mt-1 font-bold text-foreground">+254 700 000 000</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-border bg-card p-6 shadow-xs transition-all hover:shadow-md">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-400/10 text-amber-500 dark:bg-amber-400/20">
                                <MapPin class="h-6 w-6" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold uppercase tracking-wider text-muted-foreground">Location</h3>
                                <p class="mt-1 font-bold text-foreground">Nairobi, Kenya</p>
                            </div>
                        </div>
                    </div>

                    <!-- Visual map card -->
                    <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-xs transition-all">
                        <div class="relative h-48 bg-slate-200 dark:bg-slate-800">
                            <!-- Placeholder styled like a map -->
                            <div class="absolute inset-0 flex flex-col items-center justify-center bg-radial-gradient p-4 text-center">
                                <MapPin class="h-8 w-8 animate-bounce text-amber-500" />
                                <h4 class="mt-2 font-bold text-foreground">Kivulini HQ</h4>
                                <p class="text-xs text-muted-foreground">Westlands, Nairobi, Kenya</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right side: Contact Form -->
                <div class="lg:col-span-7">
                    <div class="rounded-2xl border border-border bg-card p-8 shadow-xs">
                        <div v-if="submitted" class="flex flex-col items-center justify-center py-12 text-center">
                            <CheckCircle2 class="h-16 w-16 text-green-500" />
                            <h2 class="mt-4 text-2xl font-black text-foreground">Message Sent!</h2>
                            <p class="mt-2 max-w-sm text-sm text-muted-foreground">
                                Thank you for contacting Kivulini. We have received your message and will respond to you within 24 hours.
                            </p>
                            <button
                                type="button"
                                class="mt-6 rounded-xl bg-amber-500 px-6 py-2 text-sm font-semibold text-white transition-all hover:bg-amber-400 dark:bg-amber-400 dark:text-slate-900 dark:hover:bg-amber-300"
                                @click="submitted = false"
                            >
                                Send Another Message
                            </button>
                        </div>

                        <form v-else @submit.prevent="submitForm" class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="name" class="block text-sm font-bold text-foreground">Your Name</label>
                                    <input
                                        id="name"
                                        type="text"
                                        v-model="form.name"
                                        class="mt-2 w-full rounded-xl border border-border bg-background px-4 py-2.5 text-sm text-foreground transition-all focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400"
                                        placeholder="Jane Doe"
                                        :class="{ 'border-red-500': errors.name }"
                                    />
                                    <span v-if="errors.name" class="mt-1 block text-xs text-red-500">{{ errors.name }}</span>
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-bold text-foreground">Email Address</label>
                                    <input
                                        id="email"
                                        type="email"
                                        v-model="form.email"
                                        class="mt-2 w-full rounded-xl border border-border bg-background px-4 py-2.5 text-sm text-foreground transition-all focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400"
                                        placeholder="jane@example.com"
                                        :class="{ 'border-red-500': errors.email }"
                                    />
                                    <span v-if="errors.email" class="mt-1 block text-xs text-red-500">{{ errors.email }}</span>
                                </div>
                            </div>

                            <div>
                                <label for="subject" class="block text-sm font-bold text-foreground">Subject</label>
                                <input
                                    id="subject"
                                    type="text"
                                    v-model="form.subject"
                                    class="mt-2 w-full rounded-xl border border-border bg-background px-4 py-2.5 text-sm text-foreground transition-all focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400"
                                    placeholder="Trip inquiry, booking issue, etc."
                                />
                            </div>

                            <div>
                                <label for="message" class="block text-sm font-bold text-foreground">Message</label>
                                <textarea
                                    id="message"
                                    rows="5"
                                    v-model="form.message"
                                    class="mt-2 w-full rounded-xl border border-border bg-background px-4 py-2.5 text-sm text-foreground transition-all focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400"
                                    placeholder="Write your message here..."
                                    :class="{ 'border-red-500': errors.message }"
                                />
                                <span v-if="errors.message" class="mt-1 block text-xs text-red-500">{{ errors.message }}</span>
                            </div>

                            <button
                                type="submit"
                                :disabled="submitting"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 py-3 text-sm font-bold text-white transition-all hover:bg-amber-400 disabled:opacity-60 dark:bg-amber-400 dark:text-slate-900 dark:hover:bg-amber-300"
                            >
                                <Send class="h-4 w-4" />
                                {{ submitting ? 'Sending...' : 'Send Message' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
