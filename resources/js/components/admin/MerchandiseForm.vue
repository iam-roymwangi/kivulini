<script setup lang="ts">
import { ref, computed } from 'vue';
import { Trash2, Plus, X, GripVertical, Star } from '@lucide/vue';
import type { MerchandiseImage } from '@/types/merchandise';

export interface MerchandiseFormData {
    name: string;
    type: string;
    description: string;
    price: string;
    sizes: string[];
    colors: string[];
    stock: string;
    status: string;
    // New images to upload (already compressed to webp on client)
    newImages: { file: File; previewUrl: string }[];
    // IDs of existing images to delete
    delete_image_ids: number[];
    // Which existing image ID is the cover (null = no change)
    cover_image_id: number | null;
    // Ordered list of existing image IDs (for sort_order)
    image_order: number[];
}

const props = defineProps<{
    form: MerchandiseFormData;
    errors: Partial<Record<string, string>>;
    existingImages?: MerchandiseImage[];
    isEdit?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:form', v: MerchandiseFormData): void;
}>();

function patch(partial: Partial<MerchandiseFormData>) {
    emit('update:form', { ...props.form, ...partial });
}

// ── Client-side WebP compression ──────────────────────────────────────────────

/**
 * Compress + convert an image File to WebP using a canvas, returning both
 * a new File and a data-URL for preview. Falls back to original if the
 * browser doesn't support WebP output.
 */
async function compressToWebp(file: File, quality = 0.82): Promise<{ file: File; previewUrl: string }> {
    return new Promise((resolve) => {
        const img = new Image();
        const objectUrl = URL.createObjectURL(file);

        img.onload = () => {
            URL.revokeObjectURL(objectUrl);

            // Scale down if wider than 1600px
            const maxWidth = 1600;
            let { width, height } = img;
            if (width > maxWidth) {
                height = Math.round((height * maxWidth) / width);
                width = maxWidth;
            }

            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d')!;
            ctx.drawImage(img, 0, 0, width, height);

            canvas.toBlob(
                (blob) => {
                    if (!blob) {
                        // Fallback: use original
                        const reader = new FileReader();
                        reader.onload = (ev) => resolve({ file, previewUrl: ev.target?.result as string });
                        reader.readAsDataURL(file);
                        return;
                    }
                    const webpFile = new File([blob], file.name.replace(/\.[^.]+$/, '') + '.webp', { type: 'image/webp' });
                    const previewUrl = URL.createObjectURL(blob);
                    resolve({ file: webpFile, previewUrl });
                },
                'image/webp',
                quality,
            );
        };

        img.onerror = () => {
            URL.revokeObjectURL(objectUrl);
            const reader = new FileReader();
            reader.onload = (ev) => resolve({ file, previewUrl: ev.target?.result as string });
            reader.readAsDataURL(file);
        };

        img.src = objectUrl;
    });
}

const isCompressing = ref(false);

async function handleImageFiles(e: Event) {
    const input = e.target as HTMLInputElement;
    if (!input.files?.length) { return; }
    const files = Array.from(input.files);
    input.value = ''; // reset so same file can be re-added

    isCompressing.value = true;
    const compressed = await Promise.all(files.map((f) => compressToWebp(f)));
    isCompressing.value = false;

    patch({ newImages: [...props.form.newImages, ...compressed] });
}

function removeNewImage(index: number) {
    const updated = [...props.form.newImages];
    URL.revokeObjectURL(updated[index].previewUrl);
    updated.splice(index, 1);
    patch({ newImages: updated });
}

// ── Existing images ───────────────────────────────────────────────────────────

const remainingExisting = computed(() =>
    (props.existingImages ?? []).filter((img) => !props.form.delete_image_ids.includes(img.id)),
);

function markDeleteExisting(id: number) {
    patch({ delete_image_ids: [...props.form.delete_image_ids, id] });
}

function setCover(id: number) {
    patch({ cover_image_id: id });
}

function isCover(img: MerchandiseImage): boolean {
    if (props.form.cover_image_id !== null) {
        return props.form.cover_image_id === img.id;
    }
    return img.is_cover;
}

// ── Drag-to-reorder (existing images) ────────────────────────────────────────

const dragFromIndex = ref<number | null>(null);

function onDragStart(index: number) {
    dragFromIndex.value = index;
}

function onDrop(toIndex: number) {
    if (dragFromIndex.value === null || dragFromIndex.value === toIndex) { return; }
    const order = [...props.form.image_order];
    const [moved] = order.splice(dragFromIndex.value, 1);
    order.splice(toIndex, 0, moved);
    dragFromIndex.value = null;
    patch({ image_order: order });
}

// Keep image_order in sync when existingImages change
function syncOrder() {
    const currentOrder = props.form.image_order;
    const ids = (props.existingImages ?? []).map((img) => img.id);
    if (currentOrder.length === 0 || currentOrder.some((id) => !ids.includes(id))) {
        patch({ image_order: ids });
    }
}
syncOrder();

// ── Sizes ─────────────────────────────────────────────────────────────────────
const PRESET_SIZES = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'One Size'];

function toggleSize(size: string) {
    const sizes = props.form.sizes.includes(size)
        ? props.form.sizes.filter((s) => s !== size)
        : [...props.form.sizes, size];
    patch({ sizes });
}

const customSizeInput = ref('');

function addCustomSize() {
    const val = customSizeInput.value.trim().toUpperCase();
    if (!val || props.form.sizes.includes(val)) { return; }
    patch({ sizes: [...props.form.sizes, val] });
    customSizeInput.value = '';
}

// ── Colors ────────────────────────────────────────────────────────────────────
const PRESET_COLORS = ['Black', 'White', 'Amber', 'Navy', 'Forest Green', 'Red', 'Grey', 'Beige', 'Brown'];

function toggleColor(color: string) {
    const colors = props.form.colors.includes(color)
        ? props.form.colors.filter((c) => c !== color)
        : [...props.form.colors, color];
    patch({ colors });
}

const customColorInput = ref('');

function addCustomColor() {
    const val = customColorInput.value.trim();
    if (!val || props.form.colors.includes(val)) { return; }
    patch({ colors: [...props.form.colors, val] });
    customColorInput.value = '';
}
</script>

<template>
    <div class="space-y-8">
        <!-- Basic details -->
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="form-label">Name *</label>
                <input
                    type="text"
                    class="form-input"
                    :class="{ 'border-red-500': errors.name }"
                    :value="form.name"
                    @input="patch({ name: ($event.target as HTMLInputElement).value })"
                />
                <p v-if="errors.name" class="form-error">{{ errors.name }}</p>
            </div>

            <div>
                <label class="form-label">Type *</label>
                <select class="form-input" :value="form.type" @change="patch({ type: ($event.target as HTMLSelectElement).value })">
                    <option value="t_shirt">T-Shirt</option>
                    <option value="hoodie">Hoodie</option>
                    <option value="hat">Hat</option>
                    <option value="other">Other</option>
                </select>
                <p v-if="errors.type" class="form-error">{{ errors.type }}</p>
            </div>

            <div>
                <label class="form-label">Status *</label>
                <select class="form-input" :value="form.status" @change="patch({ status: ($event.target as HTMLSelectElement).value })">
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                </select>
            </div>

            <div>
                <label class="form-label">Price (KES) *</label>
                <input
                    type="number"
                    min="0"
                    class="form-input"
                    :class="{ 'border-red-500': errors.price }"
                    :value="form.price"
                    @input="patch({ price: ($event.target as HTMLInputElement).value })"
                />
                <p v-if="errors.price" class="form-error">{{ errors.price }}</p>
            </div>

            <div>
                <label class="form-label">Stock *</label>
                <input
                    type="number"
                    min="0"
                    class="form-input"
                    :class="{ 'border-red-500': errors.stock }"
                    :value="form.stock"
                    @input="patch({ stock: ($event.target as HTMLInputElement).value })"
                />
                <p v-if="errors.stock" class="form-error">{{ errors.stock }}</p>
            </div>
        </div>

        <div>
            <label class="form-label">Description</label>
            <textarea
                rows="4"
                class="form-input"
                :value="form.description"
                @input="patch({ description: ($event.target as HTMLTextAreaElement).value })"
            />
        </div>

        <!-- Sizes -->
        <div>
            <h3 class="mb-3 text-sm font-semibold text-foreground">
                Sizes * <span class="text-xs font-normal text-muted-foreground">(select all that apply)</span>
            </h3>
            <p v-if="errors.sizes" class="mb-2 text-xs text-red-500">{{ errors.sizes }}</p>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="size in PRESET_SIZES"
                    :key="size"
                    type="button"
                    class="rounded-full border px-3 py-1.5 text-xs font-semibold transition-all"
                    :class="form.sizes.includes(size)
                        ? 'border-amber-400 bg-amber-400/10 text-amber-600 dark:text-amber-400'
                        : 'border-border text-muted-foreground hover:border-amber-400/60'"
                    @click="toggleSize(size)"
                >{{ size }}</button>
            </div>
            <div v-if="form.sizes.some((s) => !PRESET_SIZES.includes(s))" class="mt-2 flex flex-wrap gap-2">
                <span
                    v-for="size in form.sizes.filter((s) => !PRESET_SIZES.includes(s))"
                    :key="size"
                    class="flex items-center gap-1 rounded-full border border-amber-400 bg-amber-400/10 px-3 py-1 text-xs font-semibold text-amber-600 dark:text-amber-400"
                >
                    {{ size }}
                    <button type="button" class="hover:text-red-500" @click="patch({ sizes: form.sizes.filter((s) => s !== size) })">
                        <X class="h-3 w-3" />
                    </button>
                </span>
            </div>
            <div class="mt-2 flex gap-2">
                <input v-model="customSizeInput" type="text" class="form-input w-32 text-xs" placeholder="Custom size" @keydown.enter.prevent="addCustomSize" />
                <button type="button" class="flex items-center gap-1 rounded-lg bg-muted px-3 py-1.5 text-xs font-semibold text-foreground hover:bg-muted/70" @click="addCustomSize">
                    <Plus class="h-3 w-3" /> Add
                </button>
            </div>
        </div>

        <!-- Colors -->
        <div>
            <h3 class="mb-3 text-sm font-semibold text-foreground">
                Colors * <span class="text-xs font-normal text-muted-foreground">(select all that apply)</span>
            </h3>
            <p v-if="errors.colors" class="mb-2 text-xs text-red-500">{{ errors.colors }}</p>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="color in PRESET_COLORS"
                    :key="color"
                    type="button"
                    class="rounded-full border px-3 py-1.5 text-xs font-semibold transition-all"
                    :class="form.colors.includes(color)
                        ? 'border-amber-400 bg-amber-400/10 text-amber-600 dark:text-amber-400'
                        : 'border-border text-muted-foreground hover:border-amber-400/60'"
                    @click="toggleColor(color)"
                >{{ color }}</button>
            </div>
            <div v-if="form.colors.some((c) => !PRESET_COLORS.includes(c))" class="mt-2 flex flex-wrap gap-2">
                <span
                    v-for="color in form.colors.filter((c) => !PRESET_COLORS.includes(c))"
                    :key="color"
                    class="flex items-center gap-1 rounded-full border border-amber-400 bg-amber-400/10 px-3 py-1 text-xs font-semibold text-amber-600 dark:text-amber-400"
                >
                    {{ color }}
                    <button type="button" class="hover:text-red-500" @click="patch({ colors: form.colors.filter((c) => c !== color) })">
                        <X class="h-3 w-3" />
                    </button>
                </span>
            </div>
            <div class="mt-2 flex gap-2">
                <input v-model="customColorInput" type="text" class="form-input w-40 text-xs" placeholder="Custom color" @keydown.enter.prevent="addCustomColor" />
                <button type="button" class="flex items-center gap-1 rounded-lg bg-muted px-3 py-1.5 text-xs font-semibold text-foreground hover:bg-muted/70" @click="addCustomColor">
                    <Plus class="h-3 w-3" /> Add
                </button>
            </div>
        </div>

        <!-- Images -->
        <div>
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-foreground">
                    Product Images
                    <span class="ml-1 text-xs font-normal text-muted-foreground">(compressed &amp; converted to WebP in browser)</span>
                </h3>
            </div>

            <!-- Existing images (drag to reorder) -->
            <div v-if="remainingExisting.length > 0" class="mb-4">
                <p class="mb-2 text-xs text-muted-foreground">Drag to reorder · click star to set cover</p>
                <div class="flex flex-wrap gap-3">
                    <div
                        v-for="(img, idx) in remainingExisting"
                        :key="img.id"
                        draggable="true"
                        class="group relative h-28 w-28 shrink-0 cursor-grab overflow-hidden rounded-xl border-2 transition-all duration-150 active:cursor-grabbing"
                        :class="isCover(img) ? 'border-amber-400' : 'border-border'"
                        @dragstart="onDragStart(idx)"
                        @dragover.prevent
                        @drop.prevent="onDrop(idx)"
                    >
                        <img :src="img.url" :alt="`Image ${idx + 1}`" class="h-full w-full object-cover" />
                        <!-- Drag handle -->
                        <div class="absolute left-1 top-1 rounded bg-black/40 p-0.5 text-white opacity-0 transition-opacity group-hover:opacity-100">
                            <GripVertical class="h-3 w-3" />
                        </div>
                        <!-- Cover badge -->
                        <span v-if="isCover(img)" class="absolute bottom-1 left-1 rounded bg-amber-400 px-1.5 py-0.5 text-xs font-bold text-slate-900">Cover</span>
                        <!-- Actions overlay -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center gap-1 bg-black/50 opacity-0 transition-opacity group-hover:opacity-100">
                            <button type="button" class="rounded bg-amber-400 px-2 py-0.5 text-xs font-bold text-slate-900" @click="setCover(img.id)">
                                <Star class="inline h-3 w-3" /> Cover
                            </button>
                            <button type="button" class="rounded bg-red-600 px-2 py-0.5 text-xs font-bold text-white" @click="markDeleteExisting(img.id)">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- New image previews -->
            <div v-if="form.newImages.length > 0" class="mb-4 flex flex-wrap gap-3">
                <div
                    v-for="(item, i) in form.newImages"
                    :key="i"
                    class="group relative h-28 w-28 shrink-0 overflow-hidden rounded-xl border-2 border-dashed border-amber-400"
                >
                    <img :src="item.previewUrl" alt="New upload" class="h-full w-full object-cover" />
                    <span v-if="i === 0 && remainingExisting.length === 0" class="absolute bottom-1 left-1 rounded bg-amber-400 px-1.5 py-0.5 text-xs font-bold text-slate-900">Cover</span>
                    <button
                        type="button"
                        class="absolute right-1 top-1 rounded-full bg-red-600 p-0.5 text-white opacity-0 transition-opacity group-hover:opacity-100"
                        @click="removeNewImage(i)"
                    >
                        <X class="h-3 w-3" />
                    </button>
                </div>
            </div>

            <!-- Compressing indicator -->
            <div v-if="isCompressing" class="mb-3 flex items-center gap-2 text-sm text-amber-500">
                <span class="h-4 w-4 animate-spin rounded-full border-2 border-amber-400 border-t-transparent" />
                Compressing images…
            </div>

            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-dashed border-border p-4 text-sm text-muted-foreground hover:border-amber-400 hover:text-amber-500">
                <Plus class="h-4 w-4" />
                Add images (multiple allowed)
                <input type="file" accept="image/*" multiple class="hidden" @change="handleImageFiles" />
            </label>
            <p v-if="errors['images']" class="form-error">{{ errors['images'] }}</p>
        </div>
    </div>
</template>

<style scoped>
@reference "../../../css/app.css";
.form-label {
    @apply mb-1.5 block text-sm font-medium text-foreground;
}
.form-input {
    @apply w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground placeholder-muted-foreground focus:border-ring focus:outline-none;
}
.form-error {
    @apply mt-1 text-xs text-red-500;
}
</style>
