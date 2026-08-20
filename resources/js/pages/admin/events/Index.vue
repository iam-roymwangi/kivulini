<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { PlusCircle, Pencil, Trash2, Eye, CheckCircle2, XCircle, Clock, Archive } from '@lucide/vue';
import { index, create, edit, destroy } from '@/routes/admin/events';

interface EventRow {
    id: number;
    title: string;
    type: string;
    status: string;
    location: string;
    start_date: string;
    price: string;
    capacity: number;
    booked_slots: number;
    bookings_count: number;
    deleted_at: string | null;
    slug: string;
}

interface Paginator {
    data: EventRow[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
    links: { url: string | null; label: string; active: boolean }[];
}

defineProps<{
    events: Paginator;
}>();

function statusIcon(status: string) {
    return {
        published: CheckCircle2,
        draft: Clock,
        completed: Archive,
        cancelled: XCircle,
    }[status] ?? Clock;
}

function statusClass(status: string) {
    return {
        published: 'text-green-500',
        draft: 'text-yellow-500',
        completed: 'text-blue-500',
        cancelled: 'text-red-500',
    }[status] ?? 'text-muted-foreground';
}

function typeLabel(type: string) {
    return { event: 'Event', road_trip: 'Road Trip', vacation: 'Vacation' }[type] ?? type;
}

function confirmDelete(event: EventRow) {
    if (!confirm(`Archive "${event.title}"? It will be soft-deleted and hidden from the site.`)) { return; }
    router.delete(destroy.url(event.id));
}
</script>

<template>
    <Head title="Admin — Events" />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black text-foreground">Events</h1>
                <p class="text-sm text-muted-foreground">{{ events.total }} total events</p>
            </div>
            <Link
                :href="create.url()"
                class="flex items-center gap-2 rounded-xl bg-amber-400 px-4 py-2 text-sm font-bold text-slate-900 hover:bg-amber-300"
            >
                <PlusCircle class="h-4 w-4" />
                New Event
            </Link>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-xl border border-border bg-card">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/40 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        <th class="px-4 py-3">Title</th>
                        <th class="hidden px-4 py-3 md:table-cell">Type</th>
                        <th class="hidden px-4 py-3 md:table-cell">Date</th>
                        <th class="hidden px-4 py-3 md:table-cell">Bookings</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr
                        v-for="event in events.data"
                        :key="event.id"
                        class="hover:bg-muted/20 transition-colors"
                        :class="{ 'opacity-50': event.deleted_at }"
                    >
                        <td class="px-4 py-3">
                            <p class="font-semibold text-foreground">{{ event.title }}</p>
                            <p class="text-xs text-muted-foreground">{{ event.location }}</p>
                        </td>
                        <td class="hidden px-4 py-3 text-muted-foreground md:table-cell">{{ typeLabel(event.type) }}</td>
                        <td class="hidden px-4 py-3 text-muted-foreground md:table-cell">
                            {{ new Date(event.start_date).toLocaleDateString('en-KE', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                        </td>
                        <td class="hidden px-4 py-3 text-muted-foreground md:table-cell">
                            {{ event.booked_slots }} / {{ event.capacity }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="flex items-center gap-1 text-xs font-semibold" :class="statusClass(event.status)">
                                <component :is="statusIcon(event.status)" class="h-3.5 w-3.5" />
                                {{ event.status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a
                                    :href="`/events/${event.slug}`"
                                    target="_blank"
                                    class="rounded p-1 text-muted-foreground hover:text-foreground"
                                    title="View on site"
                                >
                                    <Eye class="h-4 w-4" />
                                </a>
                                <Link
                                    :href="edit.url(event.id)"
                                    class="rounded p-1 text-muted-foreground hover:text-amber-500"
                                    title="Edit"
                                >
                                    <Pencil class="h-4 w-4" />
                                </Link>
                                <button
                                    v-if="!event.deleted_at"
                                    type="button"
                                    class="rounded p-1 text-muted-foreground hover:text-red-500"
                                    title="Archive"
                                    @click="confirmDelete(event)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="events.last_page > 1" class="flex items-center justify-center gap-1">
            <template v-for="link in events.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    class="rounded px-3 py-1.5 text-sm"
                    :class="link.active ? 'bg-amber-400 font-bold text-slate-900' : 'text-muted-foreground hover:text-foreground'"
                    v-html="link.label"
                />
                <span v-else class="cursor-default rounded px-3 py-1.5 text-sm text-muted-foreground/40" v-html="link.label" />
            </template>
        </div>
    </div>
</template>
