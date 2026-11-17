<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Clock,
    Eye,
    Mail,
    MessageCircle,
    Phone,
    Search,
    Ticket,
    X,
    XCircle,
} from '@lucide/vue';
import { ref } from 'vue';
import type { Component } from 'vue';
import {
    callLink,
    emailLink,
    followUpClasses,
    followUpLabels,
    whatsAppLink,
} from '@/lib/bookingFollowUp';
import type { BookingPaymentStatus, FollowUpStatus } from '@/lib/bookingFollowUp';
import { index as bookingsIndex, show as bookingsShow } from '@/routes/admin/bookings';

interface BookingRow {
    id: number;
    booking_reference: string;
    contact_name: string;
    contact_email: string;
    contact_phone: string;
    quantity: number;
    tier_name: string | null;
    total_price: string;
    payment_status: BookingPaymentStatus;
    follow_up_status: FollowUpStatus;
    followed_up_at: string | null;
    created_at: string;
    event: { id: number; title: string; slug: string; start_date: string } | null;
}

interface PaginatorLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Paginator {
    data: BookingRow[];
    current_page: number;
    last_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginatorLink[];
}

interface Filters {
    status: string | null;
    follow_up: FollowUpStatus | null;
    event: number | null;
    search: string | null;
}

const props = defineProps<{
    bookings: Paginator;
    counts: Record<'all' | 'pending' | 'paid' | 'failed' | 'cancelled', number>;
    events: { id: number; title: string; start_date: string }[];
    followUpStatuses: FollowUpStatus[];
    filters: Filters;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Event Bookings', href: '/admin/bookings' }],
    },
});

const searchTerm = ref(props.filters.search ?? '');
const activeStatus = props.filters.status ?? 'all';

const tabs = [
    { key: 'all', label: 'All', count: props.counts.all },
    { key: 'pending', label: 'Pending', count: props.counts.pending },
    { key: 'paid', label: 'Paid', count: props.counts.paid },
    { key: 'failed', label: 'Failed', count: props.counts.failed },
    { key: 'cancelled', label: 'Cancelled', count: props.counts.cancelled },
];

function visit(overrides: Partial<Filters>): void {
    const next = { ...props.filters, search: searchTerm.value || null, ...overrides };

    router.get(
        bookingsIndex.url(),
        {
            status: next.status || undefined,
            follow_up: next.follow_up || undefined,
            event: next.event || undefined,
            search: next.search || undefined,
        },
        { preserveScroll: true, replace: true },
    );
}

function clearSearch(): void {
    searchTerm.value = '';
    visit({ search: null });
}

function statusMeta(status: BookingPaymentStatus): { label: string; icon: Component; class: string } {
    switch (status) {
        case 'paid':
            return { label: 'Paid', icon: CheckCircle2, class: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' };
        case 'failed':
            return { label: 'Failed', icon: XCircle, class: 'bg-red-500/10 text-red-500' };
        case 'cancelled':
        case 'refunded':
            return { label: status === 'cancelled' ? 'Cancelled' : 'Refunded', icon: XCircle, class: 'bg-muted text-muted-foreground' };
        default:
            return { label: 'Pending', icon: Clock, class: 'bg-amber-400/10 text-amber-600 dark:text-amber-400' };
    }
}

function formatDate(iso: string): string {
    return new Date(iso).toLocaleString('en-KE', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function formatMoney(value: string): string {
    return 'KES ' + Number(value).toLocaleString('en-KE');
}
</script>

<template>
    <Head title="Admin — Event Bookings" />

    <div class="space-y-6 p-6">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-black text-foreground">Event Bookings</h1>
            <p class="text-sm text-muted-foreground">
                {{ bookings.total }} bookings · track payments and follow up with customers
            </p>
        </div>

        <!-- Payment status pills -->
        <div class="flex flex-wrap items-center gap-3">
            <button
                v-for="tab in tabs"
                :key="tab.key"
                type="button"
                class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 text-sm font-semibold transition-all"
                :class="
                    activeStatus === tab.key
                        ? 'border-amber-400 bg-amber-400/10 text-amber-600 dark:text-amber-400'
                        : 'border-border text-muted-foreground hover:border-amber-400/50 hover:text-foreground'
                "
                @click="visit({ status: tab.key === 'all' ? null : tab.key })"
            >
                <span>{{ tab.label }}</span>
                <span
                    class="rounded-full px-2 py-0.5 text-xs font-bold"
                    :class="activeStatus === tab.key ? 'bg-amber-400 text-slate-900' : 'bg-muted text-muted-foreground'"
                >
                    {{ tab.count }}
                </span>
            </button>
        </div>

        <!-- Secondary filters -->
        <div class="flex flex-col gap-3 md:flex-row md:items-center">
            <select
                class="rounded-xl border border-input bg-background px-3 py-2 text-sm text-foreground focus:border-ring focus:outline-none md:w-64"
                aria-label="Filter by event"
                :value="filters.event ?? ''"
                @change="visit({ event: Number(($event.target as HTMLSelectElement).value) || null })"
            >
                <option value="">All events</option>
                <option v-for="event in events" :key="event.id" :value="event.id">
                    {{ event.title }}
                </option>
            </select>

            <select
                class="rounded-xl border border-input bg-background px-3 py-2 text-sm text-foreground focus:border-ring focus:outline-none md:w-48"
                aria-label="Filter by follow-up status"
                :value="filters.follow_up ?? ''"
                @change="visit({ follow_up: (($event.target as HTMLSelectElement).value || null) as FollowUpStatus | null })"
            >
                <option value="">Any follow-up</option>
                <option v-for="status in followUpStatuses" :key="status" :value="status">
                    {{ followUpLabels[status] }}
                </option>
            </select>

            <form class="relative md:ml-auto" @submit.prevent="visit({})">
                <Search class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <input
                    v-model="searchTerm"
                    type="search"
                    placeholder="Search reference, name, email, phone…"
                    class="w-full rounded-xl border border-input bg-background py-2 pr-9 pl-9 text-sm text-foreground placeholder-muted-foreground focus:border-ring focus:outline-none md:w-72"
                />
                <button
                    v-if="searchTerm"
                    type="button"
                    class="absolute top-1/2 right-2 -translate-y-1/2 rounded p-1 text-muted-foreground hover:text-foreground"
                    title="Clear"
                    @click="clearSearch"
                >
                    <X class="h-4 w-4" />
                </button>
            </form>
        </div>

        <!-- Bookings table -->
        <div v-if="bookings.data.length > 0" class="overflow-hidden rounded-xl border border-border bg-card">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-muted/40 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                            <th class="px-4 py-3">Reference</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="hidden px-4 py-3 lg:table-cell">Event</th>
                            <th class="hidden px-4 py-3 md:table-cell">Tickets</th>
                            <th class="hidden px-4 py-3 md:table-cell">Total</th>
                            <th class="px-4 py-3">Payment</th>
                            <th class="hidden px-4 py-3 md:table-cell">Follow-up</th>
                            <th class="hidden px-4 py-3 xl:table-cell">Booked</th>
                            <th class="px-4 py-3 text-right">Follow up</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="booking in bookings.data" :key="booking.id" class="transition-colors hover:bg-muted/20">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-foreground">
                                {{ booking.booking_reference }}
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-foreground">{{ booking.contact_name }}</p>
                                <p class="text-xs text-muted-foreground">{{ booking.contact_phone }}</p>
                            </td>
                            <td class="hidden max-w-56 px-4 py-3 lg:table-cell">
                                <p class="truncate text-foreground">{{ booking.event?.title ?? 'Deleted event' }}</p>
                                <p v-if="booking.event" class="text-xs text-muted-foreground">
                                    {{ new Date(booking.event.start_date).toLocaleDateString('en-KE', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                                </p>
                            </td>
                            <td class="hidden px-4 py-3 text-muted-foreground md:table-cell">
                                {{ booking.quantity }}<span v-if="booking.tier_name"> × {{ booking.tier_name }}</span>
                            </td>
                            <td class="hidden px-4 py-3 font-semibold text-foreground md:table-cell">
                                {{ formatMoney(booking.total_price) }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold"
                                    :class="statusMeta(booking.payment_status).class"
                                >
                                    <component :is="statusMeta(booking.payment_status).icon" class="h-3 w-3" />
                                    {{ statusMeta(booking.payment_status).label }}
                                </span>
                            </td>
                            <td class="hidden px-4 py-3 md:table-cell">
                                <span
                                    class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold whitespace-nowrap"
                                    :class="followUpClasses[booking.follow_up_status]"
                                >
                                    {{ followUpLabels[booking.follow_up_status] }}
                                </span>
                            </td>
                            <td class="hidden px-4 py-3 text-xs text-muted-foreground xl:table-cell">
                                {{ formatDate(booking.created_at) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a
                                        :href="callLink(booking)"
                                        class="rounded-lg border border-border p-1.5 text-muted-foreground transition hover:border-amber-400 hover:text-amber-500"
                                        :title="`Call ${booking.contact_name}`"
                                        :aria-label="`Call ${booking.contact_name}`"
                                    >
                                        <Phone class="h-3.5 w-3.5" />
                                    </a>
                                    <a
                                        v-if="whatsAppLink(booking)"
                                        :href="whatsAppLink(booking)!"
                                        target="_blank"
                                        rel="noopener"
                                        class="rounded-lg border border-border p-1.5 text-muted-foreground transition hover:border-emerald-500 hover:text-emerald-500"
                                        :title="`WhatsApp ${booking.contact_name}`"
                                        :aria-label="`WhatsApp ${booking.contact_name}`"
                                    >
                                        <MessageCircle class="h-3.5 w-3.5" />
                                    </a>
                                    <a
                                        :href="emailLink(booking)"
                                        class="rounded-lg border border-border p-1.5 text-muted-foreground transition hover:border-amber-400 hover:text-amber-500"
                                        :title="`Email ${booking.contact_name}`"
                                        :aria-label="`Email ${booking.contact_name}`"
                                    >
                                        <Mail class="h-3.5 w-3.5" />
                                    </a>
                                    <Link
                                        :href="bookingsShow.url(booking.id)"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-border px-3 py-1.5 text-xs font-semibold text-muted-foreground transition hover:border-amber-400 hover:text-amber-500"
                                    >
                                        <Eye class="h-3.5 w-3.5" />
                                        View
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Empty state -->
        <div
            v-else
            class="flex min-h-64 flex-col items-center justify-center rounded-2xl border border-border bg-card p-8 text-center"
        >
            <Ticket class="mb-3 h-10 w-10 text-muted-foreground/30" />
            <p class="font-semibold text-foreground">No bookings found</p>
            <p class="mt-1 text-sm text-muted-foreground">
                {{
                    filters.search || filters.status || filters.follow_up || filters.event
                        ? 'Try adjusting your filters or search.'
                        : 'Event bookings will appear here once customers book a trip.'
                }}
            </p>
        </div>

        <!-- Pagination -->
        <div v-if="bookings.last_page > 1" class="flex flex-col items-center gap-3">
            <p class="text-xs text-muted-foreground">
                Showing {{ bookings.from }}–{{ bookings.to }} of {{ bookings.total }}
            </p>
            <div class="flex flex-wrap items-center justify-center gap-1.5">
                <template v-for="link in bookings.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="rounded-xl border border-border bg-card px-4 py-2 text-sm font-semibold transition-all hover:border-amber-400 hover:text-amber-500"
                        :class="link.active ? 'border-amber-400 bg-amber-400/10 font-bold text-amber-500' : 'text-muted-foreground'"
                        preserve-scroll
                    >
                        <span v-html="link.label" />
                    </Link>
                    <span
                        v-else
                        class="cursor-default rounded-xl border border-border/40 bg-card/40 px-4 py-2 text-sm text-muted-foreground/45"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </div>
</template>
