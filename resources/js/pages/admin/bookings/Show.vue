<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calendar,
    CheckCircle2,
    ClipboardList,
    Clock,
    Download,
    FileSignature,
    Mail,
    MapPin,
    MessageCircle,
    Phone,
    Receipt,
    Search,
    Ticket,
    User,
    XCircle,
} from '@lucide/vue';
import { computed } from 'vue';
import type { Component } from 'vue';
import {
    callLink,
    emailLink,
    followUpClasses,
    followUpLabels,
    followUpMessage,
    whatsAppLink,
} from '@/lib/bookingFollowUp';
import type { BookingPaymentStatus, FollowUpStatus } from '@/lib/bookingFollowUp';
import { followUp, index as bookingsIndex } from '@/routes/admin/bookings';

interface MpesaTransaction {
    id: number;
    uuid: string;
    phone: string;
    amount: number;
    status: 'pending' | 'completed' | 'failed';
    result_desc: string | null;
    mpesa_receipt_number: string | null;
    created_at: string;
}

interface Booking {
    id: number;
    booking_reference: string;
    contact_name: string;
    contact_email: string;
    contact_phone: string;
    quantity: number;
    tier_name: string | null;
    unit_price: string | null;
    total_price: string;
    payment_status: BookingPaymentStatus;
    payment_reference: string | null;
    terms_accepted_at: string | null;
    follow_up_status: FollowUpStatus;
    follow_up_notes: string | null;
    followed_up_at: string | null;
    created_at: string;
    event: {
        id: number;
        title: string;
        slug: string;
        start_date: string;
        end_date: string;
        location: string;
        pickup_location: string | null;
        deleted_at: string | null;
    } | null;
    responses: { id: number; answer: string; question: { id: number; question_text: string } | null }[];
    consent: { signer_name: string; signed_at: string } | null;
    mpesa_transactions: MpesaTransaction[];
}

const props = defineProps<{
    booking: Booking;
    followUpStatuses: FollowUpStatus[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Event Bookings', href: '/admin/bookings' },
            { title: 'Booking Detail', href: '/admin/bookings' },
        ],
    },
});

const form = useForm({
    follow_up_status: props.booking.follow_up_status,
    follow_up_notes: props.booking.follow_up_notes ?? '',
});

function saveFollowUp(): void {
    form.patch(followUp.url(props.booking.id), {
        preserveScroll: true,
        onSuccess: () => form.defaults(),
    });
}

const whatsApp = computed(() => whatsAppLink(props.booking));
const suggestedMessage = computed(() => followUpMessage(props.booking));

function paymentMeta(status: BookingPaymentStatus | MpesaTransaction['status']): { label: string; icon: Component; class: string; dot: string } {
    switch (status) {
        case 'paid':
        case 'completed':
            return { label: status === 'paid' ? 'Paid' : 'Completed', icon: CheckCircle2, class: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400', dot: 'bg-emerald-500' };
        case 'failed':
            return { label: 'Failed', icon: XCircle, class: 'bg-red-500/10 text-red-500', dot: 'bg-red-500' };
        case 'cancelled':
        case 'refunded':
            return { label: status === 'cancelled' ? 'Cancelled' : 'Refunded', icon: XCircle, class: 'bg-muted text-muted-foreground', dot: 'bg-slate-400' };
        default:
            return { label: 'Pending', icon: Clock, class: 'bg-amber-400/10 text-amber-600 dark:text-amber-400', dot: 'bg-amber-400' };
    }
}

const bookingMeta = computed(() => paymentMeta(props.booking.payment_status));

function formatMoney(value: string | number): string {
    return 'KES ' + Number(value).toLocaleString('en-KE');
}

function formatDateTime(iso: string): string {
    return new Date(iso).toLocaleString('en-KE', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <Head :title="`Admin — Booking ${booking.booking_reference}`" />

    <div class="space-y-6 p-6">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <Link :href="bookingsIndex.url()" class="rounded-lg border border-border p-2 text-muted-foreground hover:text-foreground">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="font-mono text-xl font-black text-foreground">{{ booking.booking_reference }}</h1>
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold" :class="bookingMeta.class">
                            <component :is="bookingMeta.icon" class="h-3 w-3" />
                            {{ bookingMeta.label }}
                        </span>
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold" :class="followUpClasses[booking.follow_up_status]">
                            {{ followUpLabels[booking.follow_up_status] }}
                        </span>
                    </div>
                    <p class="text-sm text-muted-foreground">Booked {{ formatDateTime(booking.created_at) }}</p>
                </div>
            </div>
            <a
                v-if="booking.payment_status === 'paid'"
                :href="`/bookings/${booking.id}/pass`"
                class="inline-flex items-center gap-2 self-start rounded-xl border border-border px-4 py-2 text-sm font-semibold text-foreground hover:border-amber-400 hover:text-amber-500 sm:self-auto"
            >
                <Download class="h-4 w-4" /> Download pass
            </a>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Left: details -->
            <div class="space-y-6 lg:col-span-2">
                <!-- Customer -->
                <div class="rounded-2xl border border-border bg-card p-5">
                    <h2 class="mb-4 flex items-center gap-2 text-sm font-semibold text-foreground">
                        <User class="h-4 w-4 text-muted-foreground" /> Customer
                    </h2>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <p class="text-xs tracking-wide text-muted-foreground uppercase">Name</p>
                            <p class="text-sm font-semibold text-foreground">{{ booking.contact_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs tracking-wide text-muted-foreground uppercase">Phone</p>
                            <a :href="callLink(booking)" class="flex items-center gap-1.5 text-sm font-semibold text-amber-500 hover:underline">
                                <Phone class="h-3.5 w-3.5" /> {{ booking.contact_phone }}
                            </a>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-xs tracking-wide text-muted-foreground uppercase">Email</p>
                            <a :href="`mailto:${booking.contact_email}`" class="flex items-center gap-1.5 text-sm font-semibold break-all text-amber-500 hover:underline">
                                <Mail class="h-3.5 w-3.5 shrink-0" /> {{ booking.contact_email }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Trip -->
                <div class="rounded-2xl border border-border bg-card p-5">
                    <h2 class="mb-4 flex items-center gap-2 text-sm font-semibold text-foreground">
                        <Ticket class="h-4 w-4 text-muted-foreground" /> Trip
                    </h2>
                    <div v-if="booking.event" class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="space-y-1">
                            <p class="text-sm font-semibold text-foreground">
                                {{ booking.event.title }}
                                <span v-if="booking.event.deleted_at" class="ml-1 rounded-full bg-muted px-2 py-0.5 text-xs font-semibold text-muted-foreground">Archived</span>
                            </p>
                            <p class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                <Calendar class="h-3.5 w-3.5" /> {{ formatDateTime(booking.event.start_date) }}
                            </p>
                            <p class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                <MapPin class="h-3.5 w-3.5" />
                                {{ booking.event.pickup_location ? `${booking.event.pickup_location} → ` : '' }}{{ booking.event.location }}
                            </p>
                        </div>
                        <Link
                            v-if="!booking.event.deleted_at"
                            :href="`/admin/bookings?event=${booking.event.id}`"
                            class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-border px-3 py-1.5 text-xs font-semibold text-muted-foreground hover:border-amber-400 hover:text-amber-500"
                        >
                            All bookings for this trip
                        </Link>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">This event has been deleted.</p>

                    <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-border pt-4 text-sm sm:grid-cols-4">
                        <div>
                            <dt class="text-xs tracking-wide text-muted-foreground uppercase">Ticket</dt>
                            <dd class="font-semibold text-foreground">{{ booking.tier_name ?? 'Standard' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs tracking-wide text-muted-foreground uppercase">Quantity</dt>
                            <dd class="font-semibold text-foreground">{{ booking.quantity }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-xs tracking-wide text-muted-foreground uppercase">Total</dt>
                            <dd class="text-lg font-black text-amber-500">{{ formatMoney(booking.total_price) }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Answers -->
                <div v-if="booking.responses.length > 0" class="rounded-2xl border border-border bg-card p-5">
                    <h2 class="mb-4 flex items-center gap-2 text-sm font-semibold text-foreground">
                        <ClipboardList class="h-4 w-4 text-muted-foreground" /> Participant answers
                    </h2>
                    <dl class="space-y-3 text-sm">
                        <div v-for="response in booking.responses" :key="response.id">
                            <dt class="text-xs text-muted-foreground">{{ response.question?.question_text ?? 'Removed question' }}</dt>
                            <dd class="font-semibold text-foreground">{{ response.answer || '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Payment timeline -->
                <div class="rounded-2xl border border-border bg-card p-5">
                    <h2 class="mb-5 flex items-center gap-2 text-sm font-semibold text-foreground">
                        <Search class="h-4 w-4 text-muted-foreground" /> Payment attempts
                    </h2>
                    <div v-if="booking.mpesa_transactions.length > 0" class="relative space-y-5 pl-6">
                        <div class="absolute top-2 bottom-2 left-[7px] w-px bg-border" />
                        <div v-for="txn in booking.mpesa_transactions" :key="txn.id" class="relative">
                            <span class="absolute top-1 -left-6 h-3.5 w-3.5 rounded-full border-2 border-card" :class="paymentMeta(txn.status).dot" />
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold" :class="paymentMeta(txn.status).class">
                                    <component :is="paymentMeta(txn.status).icon" class="h-3 w-3" />
                                    {{ paymentMeta(txn.status).label }}
                                </span>
                                <span class="text-xs text-muted-foreground">{{ formatDateTime(txn.created_at) }}</span>
                            </div>
                            <p class="mt-1.5 text-sm text-muted-foreground">
                                {{ formatMoney(txn.amount) }} from <span class="font-semibold text-foreground">{{ txn.phone }}</span>
                                <span v-if="txn.mpesa_receipt_number" class="ml-1 font-mono text-xs text-emerald-600 dark:text-emerald-400">{{ txn.mpesa_receipt_number }}</span>
                            </p>
                            <p v-if="txn.result_desc" class="mt-0.5 text-xs text-foreground">{{ txn.result_desc }}</p>
                        </div>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">No M-Pesa payment attempts recorded.</p>
                </div>
            </div>

            <!-- Right: follow-up -->
            <div class="space-y-6">
                <div class="rounded-2xl border border-amber-400/40 bg-card p-5">
                    <h2 class="mb-1 flex items-center gap-2 text-sm font-semibold text-foreground">
                        <MessageCircle class="h-4 w-4 text-amber-500" /> Follow up
                    </h2>
                    <p class="mb-4 text-xs text-muted-foreground">
                        Reach out with a message matched to this booking's payment status.
                    </p>

                    <div class="grid grid-cols-3 gap-2">
                        <a :href="callLink(booking)" class="flex flex-col items-center gap-1 rounded-xl border border-border py-3 text-xs font-semibold text-foreground transition hover:border-amber-400 hover:text-amber-500">
                            <Phone class="h-4 w-4" /> Call
                        </a>
                        <a
                            v-if="whatsApp"
                            :href="whatsApp"
                            target="_blank"
                            rel="noopener"
                            class="flex flex-col items-center gap-1 rounded-xl border border-border py-3 text-xs font-semibold text-foreground transition hover:border-emerald-500 hover:text-emerald-500"
                        >
                            <MessageCircle class="h-4 w-4" /> WhatsApp
                        </a>
                        <span v-else class="flex flex-col items-center gap-1 rounded-xl border border-dashed border-border py-3 text-xs text-muted-foreground" title="Phone number can't be used for WhatsApp">
                            <MessageCircle class="h-4 w-4" /> No WhatsApp
                        </span>
                        <a :href="emailLink(booking)" class="flex flex-col items-center gap-1 rounded-xl border border-border py-3 text-xs font-semibold text-foreground transition hover:border-amber-400 hover:text-amber-500">
                            <Mail class="h-4 w-4" /> Email
                        </a>
                    </div>

                    <details class="mt-3 rounded-xl bg-muted/40 px-3 py-2 text-xs text-muted-foreground">
                        <summary class="cursor-pointer font-semibold text-foreground">Suggested message</summary>
                        <p class="mt-2 leading-relaxed">{{ suggestedMessage }}</p>
                    </details>

                    <form class="mt-5 space-y-3 border-t border-border pt-4" @submit.prevent="saveFollowUp">
                        <div>
                            <label for="follow_up_status" class="mb-1.5 block text-xs font-semibold text-foreground">Follow-up status</label>
                            <select
                                id="follow_up_status"
                                v-model="form.follow_up_status"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground focus:border-ring focus:outline-none"
                            >
                                <option v-for="status in followUpStatuses" :key="status" :value="status">{{ followUpLabels[status] }}</option>
                            </select>
                            <p v-if="form.errors.follow_up_status" class="mt-1 text-xs text-red-500">{{ form.errors.follow_up_status }}</p>
                        </div>
                        <div>
                            <label for="follow_up_notes" class="mb-1.5 block text-xs font-semibold text-foreground">Notes</label>
                            <textarea
                                id="follow_up_notes"
                                v-model="form.follow_up_notes"
                                rows="4"
                                placeholder="e.g. Called 14 Nov, will pay on Friday"
                                class="w-full resize-y rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground placeholder-muted-foreground focus:border-ring focus:outline-none"
                            />
                            <p v-if="form.errors.follow_up_notes" class="mt-1 text-xs text-red-500">{{ form.errors.follow_up_notes }}</p>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs text-muted-foreground">
                                {{ booking.followed_up_at ? `Last updated ${formatDateTime(booking.followed_up_at)}` : 'Not followed up yet' }}
                            </p>
                            <button
                                type="submit"
                                :disabled="form.processing || !form.isDirty"
                                class="rounded-xl bg-amber-400 px-4 py-2 text-sm font-bold text-slate-900 hover:bg-amber-300 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Saving…' : form.recentlySuccessful ? 'Saved' : 'Save' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Payment summary -->
                <div class="rounded-2xl border border-border bg-card p-5">
                    <h2 class="mb-4 flex items-center gap-2 text-sm font-semibold text-foreground">
                        <Receipt class="h-4 w-4 text-muted-foreground" /> Payment
                    </h2>
                    <dl class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-muted-foreground">Status</dt>
                            <dd>
                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold" :class="bookingMeta.class">
                                    <component :is="bookingMeta.icon" class="h-3 w-3" /> {{ bookingMeta.label }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-muted-foreground">M-Pesa ref</dt>
                            <dd class="text-right font-mono text-xs text-foreground">{{ booking.payment_reference ?? '—' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-muted-foreground">Terms accepted</dt>
                            <dd class="text-right text-xs text-foreground">{{ booking.terms_accepted_at ? formatDateTime(booking.terms_accepted_at) : '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Waiver -->
                <div v-if="booking.consent" class="rounded-2xl border border-border bg-card p-5">
                    <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold text-foreground">
                        <FileSignature class="h-4 w-4 text-muted-foreground" /> Waiver signed
                    </h2>
                    <p class="text-sm font-semibold text-foreground">{{ booking.consent.signer_name }}</p>
                    <p class="text-xs text-muted-foreground">{{ formatDateTime(booking.consent.signed_at) }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
