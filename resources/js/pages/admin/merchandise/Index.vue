<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { PlusCircle, Pencil, Trash2, CheckCircle2, Clock } from '@lucide/vue';
import { index, create, edit, destroy } from '@/routes/admin/merchandise';
import type { MerchandiseItem } from '@/types/merchandise';

interface MerchandiseRow extends Omit<MerchandiseItem, 'images'> {
    deleted_at: string | null;
}

interface Paginator {
    data: MerchandiseRow[];
    current_page: number;
    last_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

defineProps<{
    merchandise: Paginator;
}>();

function typeLabel(type: string): string {
    return { t_shirt: 'T-Shirt', hoodie: 'Hoodie', hat: 'Hat', other: 'Other' }[type] ?? type;
}

function statusClass(status: string): string {
    return { published: 'text-green-500', draft: 'text-yellow-500' }[status] ?? 'text-muted-foreground';
}

function confirmDelete(item: MerchandiseRow) {
    if (!confirm(`Archive "${item.name}"? It will be soft-deleted and hidden from the site.`)) { return; }
    router.delete(destroy.url(item.id));
}
</script>

<template>
    <Head title="Admin — Merchandise" />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black text-foreground">Merchandise</h1>
                <p class="text-sm text-muted-foreground">{{ merchandise.total }} items</p>
            </div>
            <Link
                :href="create.url()"
                class="flex items-center gap-2 rounded-xl bg-amber-400 px-4 py-2 text-sm font-bold text-slate-900 hover:bg-amber-300"
            >
                <PlusCircle class="h-4 w-4" />
                New Item
            </Link>
        </div>

        <div class="overflow-hidden rounded-xl border border-border bg-card">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/40 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        <th class="px-4 py-3">Item</th>
                        <th class="hidden px-4 py-3 md:table-cell">Type</th>
                        <th class="hidden px-4 py-3 md:table-cell">Price</th>
                        <th class="hidden px-4 py-3 md:table-cell">Stock</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr
                        v-for="item in merchandise.data"
                        :key="item.id"
                        class="transition-colors hover:bg-muted/20"
                        :class="{ 'opacity-50': item.deleted_at }"
                    >
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="item.cover_image_url"
                                    :src="item.cover_image_url"
                                    :alt="item.name"
                                    class="h-10 w-10 rounded-lg object-cover"
                                />
                                <div v-else class="flex h-10 w-10 items-center justify-center rounded-lg bg-muted text-xs text-muted-foreground">
                                    IMG
                                </div>
                                <p class="font-semibold text-foreground">{{ item.name }}</p>
                            </div>
                        </td>
                        <td class="hidden px-4 py-3 text-muted-foreground md:table-cell">{{ typeLabel(item.type) }}</td>
                        <td class="hidden px-4 py-3 text-muted-foreground md:table-cell">
                            KES {{ Number(item.price).toLocaleString() }}
                        </td>
                        <td class="hidden px-4 py-3 text-muted-foreground md:table-cell">{{ item.stock }}</td>
                        <td class="px-4 py-3">
                            <span class="flex items-center gap-1 text-xs font-semibold" :class="statusClass(item.status)">
                                <component :is="item.status === 'published' ? CheckCircle2 : Clock" class="h-3.5 w-3.5" />
                                {{ item.status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <Link
                                    :href="edit.url(item.id)"
                                    class="rounded p-1 text-muted-foreground hover:text-amber-500"
                                    title="Edit"
                                >
                                    <Pencil class="h-4 w-4" />
                                </Link>
                                <button
                                    v-if="!item.deleted_at"
                                    type="button"
                                    class="rounded p-1 text-muted-foreground hover:text-red-500"
                                    title="Archive"
                                    @click="confirmDelete(item)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="merchandise.last_page > 1" class="flex items-center justify-center gap-1">
            <template v-for="link in merchandise.links" :key="link.label">
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
