<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Package,
    Search,
    Eye,
    X,
    Clock,
    CheckCircle2,
    XCircle,
} from '@lucide/vue';
import { ref } from 'vue';
import type { Component } from 'vue';

interface OrderMerchandise {
    id: number;
    name: string;
    slug: string;
    type: string;
}

interface OrderRow {
    id: number;
    order_reference: string;
    contact_name: string;
    contact_email: string;
    contact_phone: string;
    size: string | null;
    color: string | null;
    quantity: number;
    unit_price: string;
    total_price: string;
    payment_status: 'pending' | 'paid' | 'failed';
    payment_reference: string | null;
    created_at: string;
    merchandise: OrderMerchandise | null;
}

interface PaginatorLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Paginator {
    data: OrderRow[];
    current_page: number;
    last_page: number;
    total: number;
    links: PaginatorLink[];
}

interface Counts {
    all: number;
    pending: number;
    paid: number;
    failed: number;
}

const props = defineProps<{
    orders: Paginator;
    counts: Counts;
    filters: { status: string | null; search: string | null };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Merch Orders', href: '/admin/orders' }],
    },
});

const activeFilter = props.filters.status ?? 'all';
const searchTerm = ref(props.filters.search ?? '');

const typeLabel = (type: string): string =>
    ({ t_shirt: 'T-Shirt', hoodie: 'Hoodie', hat: 'Hat', other: 'Other' })[
        type
    ] ?? type;

function statusMeta(status: OrderRow['payment_status']): {
    label: string;
    icon: Component;
    class: string;
} {
    switch (status) {
        case 'paid':
            return {
                label: 'Paid',
                icon: CheckCircle2,
                class: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
            };
        case 'failed':
            return {
                label: 'Failed',
                icon: XCircle,
                class: 'bg-red-500/10 text-red-500',
            };
        default:
            return {
                label: 'Pending',
                icon: Clock,
                class: 'bg-amber-400/10 text-amber-600 dark:text-amber-400',
            };
    }
}

function applyFilters(status: string | null) {
    const statusParam = status && status !== 'all' ? status : undefined;
    router.get(
        '/admin/orders',
        { status: statusParam, search: searchTerm.value || undefined },
        { preserveScroll: true, replace: true },
    );
}

function submitSearch() {
    applyFilters(activeFilter === 'all' ? null : activeFilter);
}

function clearSearch() {
    searchTerm.value = '';
    applyFilters(activeFilter === 'all' ? null : activeFilter);
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

const tabs = [
    { key: 'all', label: 'All', count: props.counts.all },
    { key: 'pending', label: 'Pending', count: props.counts.pending },
    { key: 'paid', label: 'Paid', count: props.counts.paid },
    { key: 'failed', label: 'Failed', count: props.counts.failed },
];
</script>

<template>
    <Head title="Admin — Merch Orders" />

    <div class="space-y-6 p-6">
        <!-- Header -->
        <div
            class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-black text-foreground">
                    Merch Orders
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{ orders.total }} orders · track M-Pesa payment status
                </p>
            </div>
        </div>

        <!-- Filter pills -->
        <div class="flex flex-wrap items-center gap-3">
            <button
                v-for="tab in tabs"
                :key="tab.key"
                type="button"
                class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 text-sm font-semibold transition-all"
                :class="
                    activeFilter === tab.key
                        ? 'border-amber-400 bg-amber-400/10 text-amber-600 dark:text-amber-400'
                        : 'border-border text-muted-foreground hover:border-amber-400/50 hover:text-foreground'
                "
                @click="applyFilters(tab.key === 'all' ? null : tab.key)"
            >
                <span>{{ tab.label }}</span>
                <span
                    class="rounded-full px-2 py-0.5 text-xs font-bold"
                    :class="
                        activeFilter === tab.key
                            ? 'bg-amber-400 text-slate-900'
                            : 'bg-muted text-muted-foreground'
                    "
                >
                    {{ tab.count }}
                </span>
            </button>

            <!-- Search -->
            <form class="relative ml-auto" @submit.prevent="submitSearch">
                <Search
                    class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />
                <input
                    v-model="searchTerm"
                    type="search"
                    placeholder="Search reference, name, email, phone…"
                    class="w-full rounded-xl border border-input bg-background py-2 pr-9 pl-9 text-sm text-foreground placeholder-muted-foreground focus:border-ring focus:outline-none sm:w-72"
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

        <!-- Orders table -->
        <div
            v-if="orders.data.length > 0"
            class="overflow-hidden rounded-xl border border-border bg-card"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-border bg-muted/40 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            <th class="px-4 py-3">Reference</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="hidden px-4 py-3 md:table-cell">Item</th>
                            <th class="hidden px-4 py-3 md:table-cell">Qty</th>
                            <th class="hidden px-4 py-3 md:table-cell">
                                Total
                            </th>
                            <th class="px-4 py-3">Status</th>
                            <th class="hidden px-4 py-3 md:table-cell">Date</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="order in orders.data"
                            :key="order.id"
                            class="transition-colors hover:bg-muted/20"
                        >
                            <td
                                class="px-4 py-3 font-mono text-xs font-semibold text-foreground"
                            >
                                {{ order.order_reference }}
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-foreground">
                                    {{ order.contact_name }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ order.contact_phone }}
                                </p>
                            </td>
                            <td class="hidden px-4 py-3 md:table-cell">
                                <p class="text-foreground">
                                    {{
                                        order.merchandise?.name ??
                                        'Archived item'
                                    }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{
                                        typeLabel(
                                            order.merchandise?.type ?? 'other',
                                        )
                                    }}
                                    <template v-if="order.size || order.color">
                                        ·
                                        {{
                                            [order.size, order.color]
                                                .filter(Boolean)
                                                .join(' / ')
                                        }}
                                    </template>
                                </p>
                            </td>
                            <td
                                class="hidden px-4 py-3 text-muted-foreground md:table-cell"
                            >
                                {{ order.quantity }}
                            </td>
                            <td
                                class="hidden px-4 py-3 font-semibold text-foreground md:table-cell"
                            >
                                {{ formatMoney(order.total_price) }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold"
                                    :class="
                                        statusMeta(order.payment_status).class
                                    "
                                >
                                    <component
                                        :is="
                                            statusMeta(order.payment_status)
                                                .icon
                                        "
                                        class="h-3 w-3"
                                    />
                                    {{ statusMeta(order.payment_status).label }}
                                </span>
                            </td>
                            <td
                                class="hidden px-4 py-3 text-xs text-muted-foreground md:table-cell"
                            >
                                {{ formatDate(order.created_at) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end">
                                    <Link
                                        :href="`/admin/orders/${order.id}`"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-border px-3 py-1.5 text-xs font-semibold text-muted-foreground transition hover:border-amber-400 hover:text-amber-500"
                                        title="View / track"
                                    >
                                        <Eye class="h-3.5 w-3.5" />
                                        Track
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
            <Package class="mb-3 h-10 w-10 text-muted-foreground/30" />
            <p class="font-semibold text-foreground">No orders found</p>
            <p class="mt-1 text-sm text-muted-foreground">
                {{
                    searchTerm || activeFilter !== 'all'
                        ? 'Try adjusting your filters or search.'
                        : 'Merchandise orders will appear here once customers check out.'
                }}
            </p>
        </div>

        <!-- Pagination -->
        <div
            v-if="orders.last_page > 1"
            class="flex items-center justify-center gap-1.5"
        >
            <template v-for="link in orders.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    class="rounded-xl border border-border bg-card px-4 py-2 text-sm font-semibold transition-all hover:border-amber-400 hover:text-amber-500"
                    :class="
                        link.active
                            ? 'border-amber-400 bg-amber-400/10 font-bold text-amber-500'
                            : 'text-muted-foreground'
                    "
                    preserve-scroll
                    v-html="link.label"
                />
                <span
                    v-else
                    class="cursor-default rounded-xl border border-border/40 bg-card/40 px-4 py-2 text-sm text-muted-foreground/45"
                    v-html="link.label"
                />
            </template>
        </div>
    </div>
</template>
