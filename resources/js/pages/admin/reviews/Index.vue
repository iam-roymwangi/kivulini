<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Star, Trash2, CheckCircle, XCircle, ExternalLink, MessageSquare, Clock, Filter } from '@lucide/vue';

interface ReviewEvent {
    id: number;
    title: string;
    slug: string;
}

interface ReviewRow {
    id: number;
    reviewer_name: string;
    reviewer_email: string | null;
    rating: number;
    body: string;
    image_url: string | null;
    is_approved: boolean;
    created_at: string;
    event: ReviewEvent | null;
}

interface PaginatorLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Paginator {
    data: ReviewRow[];
    current_page: number;
    last_page: number;
    total: number;
    links: PaginatorLink[];
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Reviews', href: '/admin/reviews' },
        ],
    },
});

const props = defineProps<{
    reviews: Paginator;
    counts: { all: number; pending: number; approved: number };
    filters: { status?: string };
}>();

function formatDate(iso: string) {
    return new Date(iso).toLocaleDateString('en-KE', { day: 'numeric', month: 'short', year: 'numeric' });
}

function setFilter(status: string | null) {
    router.get('/admin/reviews', status ? { status } : {}, { preserveScroll: true, replace: true });
}

function approve(id: number) {
    router.post(`/admin/reviews/${id}/approve`, {}, { preserveScroll: true });
}

function unapprove(id: number) {
    router.post(`/admin/reviews/${id}/unapprove`, {}, { preserveScroll: true });
}

function destroy(id: number) {
    if (confirm('Permanently delete this review? This cannot be undone.')) {
        router.delete(`/admin/reviews/${id}`, { preserveScroll: true });
    }
}

const activeFilter = props.filters.status ?? 'all';
</script>

<template>
    <Head title="Admin — Reviews" />

    <div class="space-y-6 p-6 mx-auto">
        <!-- Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-black text-foreground">Reviews</h1>
                <p class="text-sm text-muted-foreground">
                    {{ counts.pending }} pending approval · {{ counts.approved }} published
                </p>
            </div>
        </div>

        <!-- Stat pills -->
        <div class="flex flex-wrap gap-3">
            <button
                v-for="tab in [
                    { key: 'all', label: 'All', count: counts.all },
                    { key: 'pending', label: 'Pending', count: counts.pending },
                    { key: 'approved', label: 'Approved', count: counts.approved },
                ]"
                :key="tab.key"
                type="button"
                class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 text-sm font-semibold transition-all"
                :class="activeFilter === tab.key
                    ? 'border-amber-400 bg-amber-400/10 text-amber-600 dark:text-amber-400'
                    : 'border-border text-muted-foreground hover:border-amber-400/50 hover:text-foreground'"
                @click="setFilter(tab.key === 'all' ? null : tab.key)"
            >
                <span>{{ tab.label }}</span>
                <span
                    class="rounded-full px-2 py-0.5 text-xs font-bold"
                    :class="activeFilter === tab.key ? 'bg-amber-400 text-slate-900' : 'bg-muted text-muted-foreground'"
                >
                    {{ tab.count }}
                </span>
            </button>
        </div>

        <!-- Reviews list -->
        <div v-if="reviews.data.length > 0" class="space-y-4">
            <div
                v-for="review in reviews.data"
                :key="review.id"
                class="rounded-2xl border border-border bg-card p-5 shadow-xs transition-all"
                :class="!review.is_approved ? 'border-amber-400/30 bg-amber-400/[0.03]' : ''"
            >
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <!-- Left: reviewer + meta -->
                    <div class="min-w-0 flex-1 space-y-3">
                        <!-- Top row -->
                        <div class="flex flex-wrap items-center gap-3">
                            <!-- Avatar -->
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-border bg-muted text-sm font-bold text-foreground">
                                {{ review.reviewer_name[0].toUpperCase() }}
                            </div>

                            <div class="min-w-0">
                                <p class="text-sm font-bold text-foreground">{{ review.reviewer_name }}</p>
                                <p v-if="review.reviewer_email" class="text-xs text-muted-foreground truncate">
                                    {{ review.reviewer_email }}
                                </p>
                            </div>

                            <!-- Stars -->
                            <div class="flex items-center gap-0.5" :aria-label="`${review.rating} out of 5`">
                                <svg
                                    v-for="s in 5"
                                    :key="s"
                                    class="h-3.5 w-3.5"
                                    :class="s <= review.rating ? 'text-amber-400' : 'text-border'"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                    aria-hidden="true"
                                >
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </div>

                            <!-- Status badge -->
                            <span
                                class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold"
                                :class="review.is_approved
                                    ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                    : 'bg-amber-400/10 text-amber-600 dark:text-amber-400'"
                            >
                                <CheckCircle v-if="review.is_approved" class="h-3 w-3" />
                                <Clock v-else class="h-3 w-3" />
                                {{ review.is_approved ? 'Approved' : 'Pending' }}
                            </span>
                        </div>

                        <!-- Review body -->
                        <p class="text-sm leading-relaxed text-foreground/80">{{ review.body }}</p>

                        <!-- Attached photo -->
                        <img
                            v-if="review.image_url"
                            :src="review.image_url"
                            :alt="`Photo from ${review.reviewer_name}`"
                            class="h-28 w-auto rounded-xl border border-border object-cover"
                            loading="lazy"
                        />

                        <!-- Meta footer -->
                        <div class="flex flex-wrap items-center gap-4 text-xs text-muted-foreground">
                            <span class="flex items-center gap-1">
                                <MessageSquare class="h-3.5 w-3.5" />
                                ID: {{ review.id }}
                            </span>
                            <span>{{ formatDate(review.created_at) }}</span>
                            <a
                                v-if="review.event"
                                :href="`/events/${review.event.slug}`"
                                target="_blank"
                                class="inline-flex items-center gap-1 font-medium text-amber-500 hover:text-amber-400 hover:underline"
                            >
                                {{ review.event.title }}
                                <ExternalLink class="h-3 w-3" />
                            </a>
                        </div>
                    </div>

                    <!-- Right: actions -->
                    <div class="flex shrink-0 items-center gap-2 sm:flex-col sm:items-end">
                        <button
                            v-if="!review.is_approved"
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-emerald-400"
                            @click="approve(review.id)"
                        >
                            <CheckCircle class="h-3.5 w-3.5" />
                            Approve
                        </button>
                        <button
                            v-else
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-border px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-amber-400 hover:text-amber-500"
                            @click="unapprove(review.id)"
                        >
                            <XCircle class="h-3.5 w-3.5" />
                            Unpublish
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-red-500/30 bg-red-500/5 px-4 py-2 text-xs font-semibold text-red-500 transition hover:bg-red-500/10"
                            @click="destroy(review.id)"
                        >
                            <Trash2 class="h-3.5 w-3.5" />
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div v-else class="flex min-h-64 flex-col items-center justify-center rounded-2xl border border-border bg-card text-center p-8">
            <Star class="h-10 w-10 text-muted-foreground/30 mb-3" />
            <p class="font-semibold text-foreground">No reviews found</p>
            <p class="mt-1 text-sm text-muted-foreground">
                {{ activeFilter === 'pending' ? 'No reviews are pending approval.' : 'Reviews will appear here once submitted.' }}
            </p>
        </div>

        <!-- Pagination -->
        <div v-if="reviews.last_page > 1" class="flex items-center justify-center gap-1.5">
            <template v-for="link in reviews.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    class="rounded-xl border border-border bg-card px-4 py-2 text-sm font-semibold transition-all hover:border-amber-400 hover:text-amber-500 dark:border-slate-800"
                    :class="link.active ? 'border-amber-400 bg-amber-400/10 text-amber-500 font-bold' : 'text-muted-foreground'"
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
