<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Clock,
    CheckCircle2,
    XCircle,
    Phone,
    Mail,
    User,
    Package,
    Receipt,
    Calendar,
    Search,
    ShoppingBag,
} from '@lucide/vue';
import type { Component } from 'vue';

interface OrderMerchandise {
    id: number;
    name: string;
    slug: string;
    type: string;
    status: string;
    deleted_at: string | null;
}

interface MpesaTransaction {
    id: number;
    uuid: string;
    phone: string;
    amount: number;
    account_reference: string;
    description: string | null;
    status: 'pending' | 'completed' | 'failed';
    result_code: string | null;
    result_desc: string | null;
    mpesa_receipt_number: string | null;
    queried_at: string | null;
    created_at: string;
}

interface Order {
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
    mpesa_transactions: MpesaTransaction[];
}

const props = defineProps<{
    order: Order;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Merch Orders', href: '/admin/orders' },
            { title: 'Order Detail', href: '/admin/orders' },
        ],
    },
});

const typeLabel = (type: string): string =>
    ({ t_shirt: 'T-Shirt', hoodie: 'Hoodie', hat: 'Hat', other: 'Other' })[
        type
    ] ?? type;

interface StatusMeta {
    label: string;
    icon: Component;
    class: string;
    dot: string;
}

function orderStatusMeta(status: Order['payment_status']): StatusMeta {
    switch (status) {
        case 'paid':
            return {
                label: 'Paid',
                icon: CheckCircle2,
                class: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
                dot: 'bg-emerald-500',
            };
        case 'failed':
            return {
                label: 'Failed',
                icon: XCircle,
                class: 'bg-red-500/10 text-red-500',
                dot: 'bg-red-500',
            };
        default:
            return {
                label: 'Pending',
                icon: Clock,
                class: 'bg-amber-400/10 text-amber-600 dark:text-amber-400',
                dot: 'bg-amber-400',
            };
    }
}

function txnStatusMeta(status: MpesaTransaction['status']): StatusMeta {
    switch (status) {
        case 'completed':
            return {
                label: 'Completed',
                icon: CheckCircle2,
                class: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
                dot: 'bg-emerald-500',
            };
        case 'failed':
            return {
                label: 'Failed',
                icon: XCircle,
                class: 'bg-red-500/10 text-red-500',
                dot: 'bg-red-500',
            };
        default:
            return {
                label: 'Pending',
                icon: Clock,
                class: 'bg-amber-400/10 text-amber-600 dark:text-amber-400',
                dot: 'bg-amber-400',
            };
    }
}

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

const orderMeta = orderStatusMeta(props.order.payment_status);
</script>

<template>
    <Head :title="`Admin — Order ${order.order_reference}`" />

    <div class="space-y-6 p-6">
        <!-- Back link + header -->
        <div class="flex items-center gap-3">
            <Link
                href="/admin/orders"
                class="rounded-lg border border-border p-2 text-muted-foreground hover:text-foreground"
            >
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="font-mono text-xl font-black text-foreground">
                        {{ order.order_reference }}
                    </h1>
                    <span
                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold"
                        :class="orderMeta.class"
                    >
                        <component :is="orderMeta.icon" class="h-3 w-3" />
                        {{ orderMeta.label }}
                    </span>
                </div>
                <p class="text-sm text-muted-foreground">
                    Placed {{ formatDateTime(order.created_at) }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Left: details -->
            <div class="space-y-6 lg:col-span-2">
                <!-- Customer -->
                <div class="rounded-2xl border border-border bg-card p-5">
                    <h2
                        class="mb-4 flex items-center gap-2 text-sm font-semibold text-foreground"
                    >
                        <User class="h-4 w-4 text-muted-foreground" /> Customer
                    </h2>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <p
                                class="text-xs tracking-wide text-muted-foreground uppercase"
                            >
                                Name
                            </p>
                            <p class="text-sm font-semibold text-foreground">
                                {{ order.contact_name }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="text-xs tracking-wide text-muted-foreground uppercase"
                            >
                                Phone
                            </p>
                            <a
                                :href="`tel:${order.contact_phone}`"
                                class="flex items-center gap-1.5 text-sm font-semibold text-amber-500 hover:underline"
                            >
                                <Phone class="h-3.5 w-3.5" />
                                {{ order.contact_phone }}
                            </a>
                        </div>
                        <div class="sm:col-span-2">
                            <p
                                class="text-xs tracking-wide text-muted-foreground uppercase"
                            >
                                Email
                            </p>
                            <a
                                :href="`mailto:${order.contact_email}`"
                                class="flex items-center gap-1.5 text-sm font-semibold text-amber-500 hover:underline"
                            >
                                <Mail class="h-3.5 w-3.5" />
                                {{ order.contact_email }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Item -->
                <div class="rounded-2xl border border-border bg-card p-5">
                    <h2
                        class="mb-4 flex items-center gap-2 text-sm font-semibold text-foreground"
                    >
                        <ShoppingBag class="h-4 w-4 text-muted-foreground" />
                        Item
                    </h2>
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <p
                                    class="text-sm font-semibold text-foreground"
                                >
                                    {{
                                        order.merchandise?.name ??
                                        'Archived item'
                                    }}
                                </p>
                                <span
                                    v-if="order.merchandise?.deleted_at"
                                    class="rounded-full bg-muted px-2 py-0.5 text-xs font-semibold text-muted-foreground"
                                >
                                    Archived
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{
                                    order.merchandise
                                        ? typeLabel(order.merchandise.type)
                                        : '—'
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
                        </div>
                        <Link
                            v-if="
                                order.merchandise &&
                                !order.merchandise.deleted_at
                            "
                            :href="`/admin/merchandise/${order.merchandise.id}/edit`"
                            class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-border px-3 py-1.5 text-xs font-semibold text-muted-foreground hover:border-amber-400 hover:text-amber-500"
                        >
                            <Package class="h-3.5 w-3.5" /> Edit item
                        </Link>
                    </div>

                    <dl
                        class="mt-4 grid grid-cols-2 gap-3 border-t border-border pt-4 text-sm sm:grid-cols-4"
                    >
                        <div>
                            <dt
                                class="text-xs tracking-wide text-muted-foreground uppercase"
                            >
                                Quantity
                            </dt>
                            <dd class="font-semibold text-foreground">
                                {{ order.quantity }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs tracking-wide text-muted-foreground uppercase"
                            >
                                Unit price
                            </dt>
                            <dd class="font-semibold text-foreground">
                                {{ formatMoney(order.unit_price) }}
                            </dd>
                        </div>
                        <div class="col-span-2">
                            <dt
                                class="text-xs tracking-wide text-muted-foreground uppercase"
                            >
                                Total
                            </dt>
                            <dd class="text-lg font-black text-amber-500">
                                {{ formatMoney(order.total_price) }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Right: payment summary -->
            <div class="space-y-6">
                <div class="rounded-2xl border border-border bg-card p-5">
                    <h2
                        class="mb-4 flex items-center gap-2 text-sm font-semibold text-foreground"
                    >
                        <Receipt class="h-4 w-4 text-muted-foreground" />
                        Payment
                    </h2>
                    <dl class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-muted-foreground">Status</dt>
                            <dd>
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold"
                                    :class="orderMeta.class"
                                >
                                    <component
                                        :is="orderMeta.icon"
                                        class="h-3 w-3"
                                    />
                                    {{ orderMeta.label }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-muted-foreground">Amount</dt>
                            <dd class="font-semibold text-foreground">
                                {{ formatMoney(order.total_price) }}
                            </dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-muted-foreground">M-Pesa ref</dt>
                            <dd
                                class="text-right font-mono text-xs text-foreground"
                            >
                                {{ order.payment_reference ?? '—' }}
                            </dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt
                                class="flex items-center gap-1 text-muted-foreground"
                            >
                                <Calendar class="h-3.5 w-3.5" /> Created
                            </dt>
                            <dd class="text-right text-xs text-foreground">
                                {{ formatDateTime(order.created_at) }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Payment tracking timeline -->
        <div class="rounded-2xl border border-border bg-card p-5">
            <h2
                class="mb-5 flex items-center gap-2 text-sm font-semibold text-foreground"
            >
                <Search class="h-4 w-4 text-muted-foreground" /> Payment
                Tracking
                <span class="text-xs font-normal text-muted-foreground"
                    >· M-Pesa transaction history</span
                >
            </h2>

            <div
                v-if="order.mpesa_transactions.length > 0"
                class="relative space-y-6 pl-6"
            >
                <!-- vertical line -->
                <div
                    class="absolute top-2 bottom-2 left-[7px] w-px bg-border"
                />

                <div
                    v-for="txn in order.mpesa_transactions"
                    :key="txn.id"
                    class="relative"
                >
                    <!-- dot -->
                    <span
                        class="absolute top-1 -left-6 h-3.5 w-3.5 rounded-full border-2 border-card"
                        :class="txnStatusMeta(txn.status).dot"
                    />

                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold"
                            :class="txnStatusMeta(txn.status).class"
                        >
                            <component
                                :is="txnStatusMeta(txn.status).icon"
                                class="h-3 w-3"
                            />
                            {{ txnStatusMeta(txn.status).label }}
                        </span>
                        <span class="font-mono text-xs text-muted-foreground">{{
                            txn.uuid
                        }}</span>
                        <span class="text-xs text-muted-foreground">{{
                            formatDateTime(txn.created_at)
                        }}</span>
                    </div>

                    <div
                        class="mt-2 grid grid-cols-1 gap-x-6 gap-y-1.5 text-sm sm:grid-cols-2"
                    >
                        <p class="text-muted-foreground">
                            Amount:
                            <span class="font-semibold text-foreground">{{
                                formatMoney(txn.amount)
                            }}</span>
                        </p>
                        <p class="text-muted-foreground">
                            Phone:
                            <span class="font-semibold text-foreground">{{
                                txn.phone
                            }}</span>
                        </p>
                        <p class="text-muted-foreground">
                            Account ref:
                            <span class="font-mono text-xs text-foreground">{{
                                txn.account_reference
                            }}</span>
                        </p>
                        <p
                            v-if="txn.mpesa_receipt_number"
                            class="text-muted-foreground"
                        >
                            Receipt:
                            <span
                                class="font-mono text-xs font-semibold text-emerald-600 dark:text-emerald-400"
                                >{{ txn.mpesa_receipt_number }}</span
                            >
                        </p>
                        <p v-if="txn.result_code" class="text-muted-foreground">
                            Result code:
                            <span class="font-mono text-xs text-foreground">{{
                                txn.result_code
                            }}</span>
                        </p>
                        <p v-if="txn.queried_at" class="text-muted-foreground">
                            Queried:
                            <span class="text-xs text-foreground">{{
                                formatDateTime(txn.queried_at)
                            }}</span>
                        </p>
                        <p
                            v-if="txn.result_desc"
                            class="text-muted-foreground sm:col-span-2"
                        >
                            <span class="text-xs text-foreground">{{
                                txn.result_desc
                            }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- No transactions -->
            <div
                v-else
                class="flex flex-col items-center justify-center py-8 text-center"
            >
                <Receipt class="mb-2 h-8 w-8 text-muted-foreground/30" />
                <p class="font-semibold text-foreground">
                    No M-Pesa transactions yet
                </p>
                <p class="mt-1 text-sm text-muted-foreground">
                    Payment attempts will appear here once the customer
                    initiates checkout.
                </p>
            </div>
        </div>
    </div>
</template>
