<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import MerchandiseForm from '@/components/admin/MerchandiseForm.vue';
import type { MerchandiseFormData } from '@/components/admin/MerchandiseForm.vue';
import { store, index } from '@/routes/admin/merchandise';

const form = ref<MerchandiseFormData>({
    name: '',
    type: 't_shirt',
    description: '',
    price: '',
    sizes: [],
    colors: [],
    stock: '0',
    status: 'draft',
    newImages: [],
    delete_image_ids: [],
    cover_image_id: null,
    image_order: [],
});

const errors = ref<Partial<Record<string, string>>>({});
const submitting = ref(false);

function submit() {
    submitting.value = true;
    errors.value = {};

    const data = new FormData();
    data.append('name', form.value.name);
    data.append('type', form.value.type);
    data.append('description', form.value.description);
    data.append('price', form.value.price);
    data.append('stock', form.value.stock);
    data.append('status', form.value.status);
    form.value.sizes.forEach((s) => data.append('sizes[]', s));
    form.value.colors.forEach((c) => data.append('colors[]', c));
    form.value.newImages.forEach((item) => data.append('images[]', item.file));

    router.post(store.url(), data, {
        forceFormData: true,
        onError: (e) => { errors.value = e; submitting.value = false; },
        onSuccess: () => { submitting.value = false; },
    });
}
</script>

<template>
    <Head title="New Merchandise" />

    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex items-center gap-3">
            <a :href="index.url()" class="rounded-lg border border-border p-2 text-muted-foreground hover:text-foreground">
                <ArrowLeft class="h-4 w-4" />
            </a>
            <h1 class="text-2xl font-black text-foreground">Create Merchandise</h1>
        </div>

        <div v-if="Object.keys(errors).length > 0" class="rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-500">
            Please fix the errors below and try again.
        </div>

        <div class="rounded-2xl border border-border bg-card p-6">
            <MerchandiseForm :form="form" :errors="errors" @update:form="form = $event" />
        </div>

        <div class="flex justify-end gap-3 pb-8">
            <a :href="index.url()" class="rounded-xl border border-border px-5 py-2.5 text-sm font-semibold text-foreground hover:bg-muted">
                Cancel
            </a>
            <button
                type="button"
                :disabled="submitting"
                class="rounded-xl bg-amber-400 px-6 py-2.5 text-sm font-bold text-slate-900 hover:bg-amber-300 disabled:opacity-60"
                @click="submit"
            >
                {{ submitting ? 'Creating...' : 'Create Item' }}
            </button>
        </div>
    </div>
</template>
