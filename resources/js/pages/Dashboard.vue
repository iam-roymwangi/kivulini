<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { index as adminEventsIndex, create as adminEventsCreate } from '@/routes/admin/events';
import { 
    Coins, 
    Ticket, 
    Calendar, 
    Users, 
    ArrowUpRight, 
    Activity, 
    CheckCircle2, 
    Clock, 
    XCircle,
    PlusCircle,
    Eye
} from '@lucide/vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

defineProps<{
    metrics: {
        total_revenue: number;
        total_bookings: number;
        tickets_sold: number;
        active_events: number;
        total_events: number;
    };
    recent_bookings: {
        id: number;
        reference: string;
        event_title: string;
        contact_name: string;
        contact_email: string;
        quantity: number;
        total_price: number;
        payment_status: string;
        created_at: string;
    }[];
}>();

function formatCurrency(value: number) {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
}

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('en-KE', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>

<template>
    <Head title="Dashboard" />

    <div class="space-y-8 p-6 max-w-7xl mx-auto">
        <!-- Welcome Section with Gradient Banner -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-amber-950 p-6 md:p-8 shadow-xl">
            <!-- Decorative backdrop glow -->
            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-amber-500/20 blur-3xl" />
            <div class="absolute -left-10 -bottom-10 h-40 w-40 rounded-full bg-blue-500/10 blur-3xl" />
            
            <div class="relative z-10 space-y-2">
                <h1 class="text-3xl font-black tracking-tight text-white sm:text-4xl">
                    Kivulini <span class="text-amber-400">Dashboard</span>
                </h1>
                <p class="max-w-2xl text-sm md:text-base text-slate-300">
                    Welcome back! Here is a real-time summary of your website metrics, bookings, and active trips.
                </p>
                <div class="pt-4 flex flex-wrap gap-3">
                    <Link
                        :href="adminEventsCreate.url()"
                        class="inline-flex items-center gap-2 rounded-xl bg-amber-400 px-4 py-2 text-xs md:text-sm font-bold text-slate-900 transition-all hover:bg-amber-300 hover:scale-[1.02] shadow-md shadow-amber-400/20"
                    >
                        <PlusCircle class="h-4 w-4" />
                        Create New Event
                    </Link>
                    <Link
                        :href="adminEventsIndex.url()"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-850 px-4 py-2 text-xs md:text-sm font-semibold text-white transition-all hover:bg-slate-800 hover:border-slate-600 hover:scale-[1.02]"
                    >
                        Manage Existing Events
                        <ArrowUpRight class="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Metrics Grid -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Metric 1: Revenue -->
            <div class="relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-amber-500/30 hover:shadow-md dark:border-slate-800">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-muted-foreground">Total Revenue</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500 dark:bg-emerald-500/20">
                        <Coins class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-4 space-y-1">
                    <h3 class="text-2xl font-black tracking-tight text-foreground sm:text-3xl">
                        {{ formatCurrency(metrics.total_revenue) }}
                    </h3>
                    <p class="text-xs text-muted-foreground">From paid event bookings</p>
                </div>
            </div>

            <!-- Metric 2: Bookings -->
            <div class="relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-amber-500/30 hover:shadow-md dark:border-slate-800">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-muted-foreground">Total Bookings</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500 dark:bg-amber-500/20">
                        <Ticket class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-4 space-y-1">
                    <h3 class="text-2xl font-black tracking-tight text-foreground sm:text-3xl">
                        {{ metrics.total_bookings }}
                    </h3>
                    <p class="text-xs text-muted-foreground">Reservations placed on site</p>
                </div>
            </div>

            <!-- Metric 3: Tickets Sold -->
            <div class="relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-amber-500/30 hover:shadow-md dark:border-slate-800">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-muted-foreground">Tickets Sold</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/10 text-blue-500 dark:bg-blue-500/20">
                        <Users class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-4 space-y-1">
                    <h3 class="text-2xl font-black tracking-tight text-foreground sm:text-3xl">
                        {{ metrics.tickets_sold }}
                    </h3>
                    <p class="text-xs text-muted-foreground">Total seats occupied</p>
                </div>
            </div>

            <!-- Metric 4: Active/Total Events -->
            <div class="relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-amber-500/30 hover:shadow-md dark:border-slate-800">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-muted-foreground">Active Trips</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-500/10 text-purple-500 dark:bg-purple-500/20">
                        <Calendar class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-4 space-y-1">
                    <h3 class="text-2xl font-black tracking-tight text-foreground sm:text-3xl">
                        {{ metrics.active_events }} <span class="text-sm font-normal text-muted-foreground">/ {{ metrics.total_events }}</span>
                    </h3>
                    <p class="text-xs text-muted-foreground">Published / Total trips configured</p>
                </div>
            </div>
        </div>

        <!-- Recent Bookings Table & Quick Stats -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Recent Bookings List -->
            <div class="lg:col-span-2 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <Activity class="h-5 w-5 text-amber-500" />
                        <h2 class="text-lg font-bold text-foreground">Recent Bookings</h2>
                    </div>
                    <p class="text-xs text-muted-foreground">Last 5 reservations</p>
                </div>

                <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm dark:border-slate-800">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-border bg-muted/40 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground dark:border-slate-800">
                                    <th class="px-4 py-3">Reference / Date</th>
                                    <th class="px-4 py-3">Event & Traveler</th>
                                    <th class="px-4 py-3 text-center">Tickets</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 text-right">Price</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border dark:divide-slate-850">
                                <tr
                                    v-for="booking in recent_bookings"
                                    :key="booking.id"
                                    class="hover:bg-muted/10 transition-colors"
                                >
                                    <td class="px-4 py-3.5">
                                        <p class="font-mono text-xs font-bold text-foreground">{{ booking.reference }}</p>
                                        <p class="text-[11px] text-muted-foreground mt-0.5">{{ formatDate(booking.created_at) }}</p>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <p class="font-semibold text-foreground line-clamp-1 max-w-[200px]">{{ booking.event_title }}</p>
                                        <p class="text-xs text-muted-foreground">{{ booking.contact_name }}</p>
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-bold text-foreground">
                                        {{ booking.quantity }}
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span 
                                            class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                            :class="booking.payment_status === 'paid' 
                                                ? 'bg-emerald-500/10 text-emerald-500 dark:bg-emerald-500/20' 
                                                : booking.payment_status === 'pending'
                                                ? 'bg-amber-500/10 text-amber-500 dark:bg-amber-500/20'
                                                : 'bg-red-500/10 text-red-500 dark:bg-red-500/20'"
                                        >
                                            <component 
                                                :is="booking.payment_status === 'paid' ? CheckCircle2 : booking.payment_status === 'pending' ? Clock : XCircle" 
                                                class="h-3 w-3" 
                                            />
                                            {{ booking.payment_status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-black text-foreground">
                                        {{ formatCurrency(Number(booking.total_price)) }}
                                    </td>
                                </tr>
                                <tr v-if="recent_bookings.length === 0">
                                    <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">
                                        No bookings received yet. Share event links to get started!
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quick Guidelines / Tips Panel -->
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <span class="inline-block h-2 w-2 rounded-full bg-amber-400" />
                    <h2 class="text-lg font-bold text-foreground">Quick Admin Operations</h2>
                </div>

                <div class="rounded-2xl border border-border bg-card p-5 space-y-4 shadow-sm dark:border-slate-800">
                    <div class="space-y-3">
                        <h3 class="text-sm font-bold text-foreground">Managing Event Images</h3>
                        <p class="text-xs text-muted-foreground leading-relaxed">
                            To add new photos or organize existing media for a trip, go to the <strong>Manage Events</strong> list, find your event, and click the Edit (pencil) icon. 
                        </p>
                    </div>

                    <div class="border-t border-border pt-4 dark:border-slate-800 space-y-3">
                        <h3 class="text-sm font-bold text-foreground">Publishing to Public Gallery</h3>
                        <p class="text-xs text-muted-foreground leading-relaxed">
                            Once an event is marked as <strong>completed</strong>, a button will appear in the Event Edit page allowing you to publish all of its photos directly to the main site's public gallery.
                        </p>
                    </div>

                    <div class="border-t border-border pt-4 dark:border-slate-800">
                        <Link 
                            :href="adminEventsIndex.url()"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-amber-400/10 py-2.5 text-xs font-bold text-amber-500 hover:bg-amber-400/20 transition-all"
                        >
                            <Eye class="h-4 w-4" />
                            Go to Event Images Upload
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
