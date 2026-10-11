<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import {
    Upload,
    Trash2,
    Eye,
    Globe,
    EyeOff,
    Plus,
    X,
    Image as ImageIcon,
    Calendar,
    FolderPlus,
    Loader2,
    Pencil,
    CropIcon,
    RotateCcw,
    Check,
    ZoomIn,
    ZoomOut,
    Move,
} from '@lucide/vue';

interface EventOption {
    id: number;
    title: string;
    status: string;
    type: string;
}

interface MediaRow {
    id: number;
    url: string;
    file_path: string;
    type: string;
    is_featured: boolean;
    sort_order: number;
    event?: {
        id: number;
        title: string;
        status: string;
    };
}

interface Paginator {
    data: MediaRow[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
    links: { url: string | null; label: string; active: boolean }[];
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Gallery Management',
                href: '/admin/gallery',
            },
        ],
    },
});

const props = defineProps<{
    media: Paginator;
    events: EventOption[];
}>();

const uploadForm = useForm({
    event_id: '',
    images: [] as File[],
});

const fileInput = ref<HTMLInputElement | null>(null);
const filePreviews = ref<string[]>([]);
const dragActive = ref(false);
const converting = ref(false);

// ── Edit / Crop Modal ─────────────────────────────────────────────────────────

const editingMedia = ref<MediaRow | null>(null);
const editForm = useForm({
    event_id: '' as string | number,
    image: null as File | null,
});
const cropImageSrc = ref<string>('');
const cropCanvas = ref<HTMLCanvasElement | null>(null);
const cropContainer = ref<HTMLDivElement | null>(null);
const isCropping = ref(false);
const croppedPreview = ref<string>('');

// Crop state
const cropStart = ref<{ x: number; y: number } | null>(null);
const cropRect = ref<{ x: number; y: number; w: number; h: number } | null>(null);
const isDraggingCrop = ref(false);

// Pan/zoom state for the preview image
const imgScale = ref(1);
const imgOffset = ref({ x: 0, y: 0 });
const isPanning = ref(false);
const panStart = ref({ x: 0, y: 0 });
const panStartOffset = ref({ x: 0, y: 0 });

// The natural image loaded for cropping
const cropImage = ref<HTMLImageElement | null>(null);

function openEdit(img: MediaRow) {
    editingMedia.value = img;
    editForm.event_id = img.event?.id ?? '';
    editForm.image = null;
    cropImageSrc.value = img.url;
    croppedPreview.value = '';
    cropRect.value = null;
    isCropping.value = false;
    imgScale.value = 1;
    imgOffset.value = { x: 0, y: 0 };
    loadCropImage(img.url);
}

function closeEdit() {
    editingMedia.value = null;
    cropImage.value = null;
    cropImageSrc.value = '';
    croppedPreview.value = '';
    cropRect.value = null;
    isCropping.value = false;
}

function loadCropImage(url: string) {
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = () => { cropImage.value = img; };
    img.src = url;
}

// ── Canvas drawing ────────────────────────────────────────────────────────────

function drawCropCanvas() {
    const canvas = cropCanvas.value;
    const img = cropImage.value;
    if (!canvas || !img) { return; }

    const ctx = canvas.getContext('2d')!;
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Draw the image with pan/zoom
    const drawW = img.naturalWidth * imgScale.value;
    const drawH = img.naturalHeight * imgScale.value;
    const drawX = (canvas.width - drawW) / 2 + imgOffset.value.x;
    const drawY = (canvas.height - drawH) / 2 + imgOffset.value.y;

    ctx.drawImage(img, drawX, drawY, drawW, drawH);

    if (cropRect.value) {
        const { x, y, w, h } = cropRect.value;
        // Darken outside selection
        ctx.fillStyle = 'rgba(0,0,0,0.5)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.clearRect(x, y, w, h);
        ctx.drawImage(img,
            (x - drawX) / imgScale.value, (y - drawY) / imgScale.value,
            w / imgScale.value, h / imgScale.value,
            x, y, w, h,
        );

        // Border
        ctx.strokeStyle = '#f59e0b';
        ctx.lineWidth = 2;
        ctx.setLineDash([6, 3]);
        ctx.strokeRect(x, y, w, h);
        ctx.setLineDash([]);

        // Corner handles
        drawHandles(ctx, x, y, w, h);
    }
}

function drawHandles(ctx: CanvasRenderingContext2D, x: number, y: number, w: number, h: number) {
    const corners = [
        [x, y], [x + w, y], [x, y + h], [x + w, y + h],
        [x + w / 2, y], [x + w / 2, y + h],
        [x, y + h / 2], [x + w, y + h / 2],
    ];
    ctx.fillStyle = '#f59e0b';
    corners.forEach(([cx, cy]) => {
        ctx.beginPath();
        ctx.arc(cx, cy, 5, 0, Math.PI * 2);
        ctx.fill();
    });
}

watch([cropRect, imgScale, imgOffset, cropImage], drawCropCanvas, { deep: true });

// ── Crop interaction ──────────────────────────────────────────────────────────

function onCanvasMouseDown(e: MouseEvent) {
    const canvas = cropCanvas.value;
    if (!canvas) { return; }

    const rect = canvas.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    if (isCropping.value) {
        cropStart.value = { x, y };
        cropRect.value = null;
        isDraggingCrop.value = true;
    } else {
        // Pan
        isPanning.value = true;
        panStart.value = { x: e.clientX, y: e.clientY };
        panStartOffset.value = { ...imgOffset.value };
    }
}

function onCanvasMouseMove(e: MouseEvent) {
    const canvas = cropCanvas.value;
    if (!canvas) { return; }

    const rect = canvas.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    if (isDraggingCrop.value && cropStart.value) {
        cropRect.value = {
            x: Math.min(x, cropStart.value.x),
            y: Math.min(y, cropStart.value.y),
            w: Math.abs(x - cropStart.value.x),
            h: Math.abs(y - cropStart.value.y),
        };
    } else if (isPanning.value) {
        imgOffset.value = {
            x: panStartOffset.value.x + (e.clientX - panStart.value.x),
            y: panStartOffset.value.y + (e.clientY - panStart.value.y),
        };
    }
}

function onCanvasMouseUp() {
    isDraggingCrop.value = false;
    isPanning.value = false;
}

function onCanvasWheel(e: WheelEvent) {
    e.preventDefault();
    const delta = e.deltaY > 0 ? -0.1 : 0.1;
    imgScale.value = Math.max(0.2, Math.min(5, imgScale.value + delta));
}

function zoomIn() { imgScale.value = Math.min(5, imgScale.value + 0.2); }
function zoomOut() { imgScale.value = Math.max(0.2, imgScale.value - 0.2); }
function resetView() {
    imgScale.value = 1;
    imgOffset.value = { x: 0, y: 0 };
    cropRect.value = null;
}

function toggleCropMode() {
    isCropping.value = !isCropping.value;
    if (!isCropping.value) {
        cropRect.value = null;
    }
}

// ── Apply crop ────────────────────────────────────────────────────────────────

function applyCrop() {
    const canvas = cropCanvas.value;
    const img = cropImage.value;
    if (!canvas || !img || !cropRect.value) { return; }

    const { x, y, w, h } = cropRect.value;
    if (w < 10 || h < 10) { return; }

    // Map canvas coords back to natural image coords
    const drawW = img.naturalWidth * imgScale.value;
    const drawH = img.naturalHeight * imgScale.value;
    const drawX = (canvas.width - drawW) / 2 + imgOffset.value.x;
    const drawY = (canvas.height - drawH) / 2 + imgOffset.value.y;

    const srcX = (x - drawX) / imgScale.value;
    const srcY = (y - drawY) / imgScale.value;
    const srcW = w / imgScale.value;
    const srcH = h / imgScale.value;

    const output = document.createElement('canvas');
    output.width = srcW;
    output.height = srcH;
    const ctx = output.getContext('2d')!;
    ctx.drawImage(img, srcX, srcY, srcW, srcH, 0, 0, srcW, srcH);

    output.toBlob(
        (blob) => {
            if (!blob) { return; }
            const fileName = (editingMedia.value?.file_path.split('/').pop() ?? 'cropped') + '';
            const file = new File([blob], fileName.replace(/\.[^.]+$/, '.webp'), { type: 'image/webp' });
            editForm.image = file;
            croppedPreview.value = output.toDataURL('image/webp', 0.9);
            cropRect.value = null;
            isCropping.value = false;
        },
        'image/webp',
        0.9,
    );
}

function clearCrop() {
    editForm.image = null;
    croppedPreview.value = '';
}

function submitEdit() {
    if (!editingMedia.value) { return; }
    editForm.post(`/admin/gallery/${editingMedia.value.id}`, {
        forceFormData: true,
        onSuccess: () => { closeEdit(); },
    });
}

// ── WebP compression ─────────────────────────────────────────────────────────

function compressToWebp(file: File): Promise<File> {
    return new Promise((resolve) => {
        const img = new Image();
        const objectUrl = URL.createObjectURL(file);

        img.onload = () => {
            URL.revokeObjectURL(objectUrl);
            const MAX = 1920;
            let { width, height } = img;
            if (width > MAX || height > MAX) {
                if (width > height) { height = Math.round((height * MAX) / width); width = MAX; }
                else { width = Math.round((width * MAX) / height); height = MAX; }
            }
            const canvas = document.createElement('canvas');
            canvas.width = width; canvas.height = height;
            canvas.getContext('2d')!.drawImage(img, 0, 0, width, height);
            canvas.toBlob(
                (blob) => {
                    if (!blob) { resolve(file); return; }
                    resolve(new File([blob], file.name.replace(/\.[^.]+$/, '') + '.webp', { type: 'image/webp' }));
                },
                'image/webp', 0.82,
            );
        };
        img.onerror = () => { URL.revokeObjectURL(objectUrl); resolve(file); };
        img.src = objectUrl;
    });
}

// ── File handling ─────────────────────────────────────────────────────────────

function triggerFileInput() { fileInput.value?.click(); }

function handleFileChange(e: Event) {
    const files = (e.target as HTMLInputElement).files;
    if (files) { addFiles(Array.from(files)); }
    (e.target as HTMLInputElement).value = '';
}

function handleDragOver(e: DragEvent) { e.preventDefault(); dragActive.value = true; }
function handleDragLeave(e: DragEvent) { e.preventDefault(); dragActive.value = false; }
function handleDrop(e: DragEvent) {
    e.preventDefault(); dragActive.value = false;
    const files = e.dataTransfer?.files;
    if (files) { addFiles(Array.from(files)); }
}

async function addFiles(files: File[]) {
    const validImages = files.filter((f) => f.type.startsWith('image/'));
    if (validImages.length === 0) { return; }
    converting.value = true;
    const converted = await Promise.all(validImages.map(compressToWebp));
    converted.forEach((file) => {
        uploadForm.images.push(file);
        const reader = new FileReader();
        reader.onload = (e) => { if (e.target?.result) { filePreviews.value.push(e.target.result as string); } };
        reader.readAsDataURL(file);
    });
    converting.value = false;
}

function removePreview(index: number) {
    uploadForm.images.splice(index, 1);
    filePreviews.value.splice(index, 1);
}

function clearAll() { uploadForm.images = []; filePreviews.value = []; }

function uploadImages() {
    if (!uploadForm.event_id) { alert('Please select an event first.'); return; }
    if (uploadForm.images.length === 0) { alert('Please select at least one image to upload.'); return; }
    uploadForm.post('/admin/gallery', {
        forceFormData: true,
        onSuccess: () => { uploadForm.reset(); filePreviews.value = []; },
    });
}

function deleteImage(id: number) {
    if (confirm('Are you sure you want to permanently delete this gallery image?')) {
        router.delete(`/admin/gallery/${id}`);
    }
}

function toggleFeatured(id: number) { router.post(`/admin/gallery/${id}/toggle-featured`); }

const sortedEvents = computed(() => {
    return [...props.events].sort((a, b) => {
        if (a.status === 'completed' && b.status !== 'completed') { return -1; }
        if (a.status !== 'completed' && b.status === 'completed') { return 1; }
        return a.title.localeCompare(b.title);
    });
});
</script>

<template>
    <Head title="Admin — Gallery Management" />

    <div class="space-y-8 p-6 mx-auto">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-black text-foreground">Gallery Management</h1>
                <p class="text-sm text-muted-foreground">{{ media.total }} total images in gallery</p>
            </div>
        </div>

        <!-- Image Upload Section -->
        <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-slate-800">
            <h2 class="text-lg font-bold text-foreground mb-4 flex items-center gap-2">
                <FolderPlus class="h-5 w-5 text-amber-500" />
                Upload Multiple Images
            </h2>

            <form @submit.prevent="uploadImages" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1 space-y-2">
                        <label class="block text-sm font-medium text-foreground">Assign to Event / Trip *</label>
                        <select
                            v-model="uploadForm.event_id"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm text-foreground focus:border-ring focus:outline-none"
                            required
                        >
                            <option value="" disabled>Select an Event...</option>
                            <option v-for="event in sortedEvents" :key="event.id" :value="event.id">
                                {{ event.title }} ({{ event.status }})
                            </option>
                        </select>
                        <p class="text-xs text-muted-foreground">
                            Assigning images to completed events makes them eligible to appear in the public gallery.
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <div
                            class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-border p-8 text-center transition-colors"
                            :class="[
                                dragActive ? 'border-amber-400 bg-amber-500/5' : 'hover:border-amber-400/50',
                                converting ? 'cursor-wait' : 'cursor-pointer',
                            ]"
                            @click="!converting && triggerFileInput()"
                            @dragover="handleDragOver"
                            @dragleave="handleDragLeave"
                            @drop="handleDrop"
                        >
                            <input ref="fileInput" type="file" multiple accept="image/*" class="hidden" @change="handleFileChange" />
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-muted text-muted-foreground mb-3">
                                <Loader2 v-if="converting" class="h-6 w-6 animate-spin text-amber-500" />
                                <Upload v-else class="h-6 w-6" />
                            </div>
                            <p class="text-sm font-semibold text-foreground">
                                <span v-if="converting">Converting to WebP…</span>
                                <template v-else>Drag & drop images here, or <span class="text-amber-500 underline">browse</span></template>
                            </p>
                        </div>
                    </div>
                </div>

                <div v-if="filePreviews.length > 0" class="space-y-3 border-t border-border pt-4 dark:border-slate-800">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-foreground">Selected Images ({{ uploadForm.images.length }})</h3>
                        <button type="button" @click="clearAll()" class="text-xs text-red-500 hover:underline">Clear All</button>
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8">
                        <div v-for="(url, idx) in filePreviews" :key="idx" class="group relative aspect-square overflow-hidden rounded-lg border border-border">
                            <img :src="url" class="h-full w-full object-cover" />
                            <button type="button" @click="removePreview(idx)" class="absolute right-1 top-1 rounded-full bg-red-600 p-1 text-white opacity-0 transition-opacity group-hover:opacity-100 shadow">
                                <X class="h-3 w-3" />
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button
                        v-if="uploadForm.images.length > 0"
                        type="submit"
                        :disabled="uploadForm.processing || converting"
                        class="rounded-xl bg-amber-400 px-6 py-2.5 text-sm font-bold text-slate-900 hover:bg-amber-300 disabled:opacity-60 transition-all shadow-md shadow-amber-400/10"
                    >
                        <span v-if="converting">Converting images…</span>
                        <span v-else-if="uploadForm.processing">Uploading{{ uploadForm.progress ? ` ${uploadForm.progress.percentage}%` : '…' }}</span>
                        <span v-else>Upload {{ uploadForm.images.length }} Image{{ uploadForm.images.length !== 1 ? 's' : '' }}</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Gallery Grid -->
        <div class="space-y-4">
            <h2 class="text-lg font-bold text-foreground flex items-center gap-2">
                <ImageIcon class="h-5 w-5 text-amber-500" />
                Gallery Archive
            </h2>

            <div v-if="media.data.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                <div
                    v-for="img in media.data"
                    :key="img.id"
                    class="group relative overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all duration-300 hover:border-amber-500/20 dark:border-slate-800"
                >
                    <div class="relative aspect-video overflow-hidden bg-muted">
                        <img :src="img.url" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />

                        <div class="absolute inset-0 bg-black/60 opacity-0 transition-opacity duration-300 group-hover:opacity-100 flex items-center justify-center gap-3">
                            <a
                                :href="img.url"
                                target="_blank"
                                class="rounded-full bg-white/20 p-2.5 text-white hover:bg-white/30 backdrop-blur-xs transition-all"
                                title="Open Original Image"
                            >
                                <Eye class="h-4 w-4" />
                            </a>
                            <button
                                type="button"
                                @click="openEdit(img)"
                                class="rounded-full bg-blue-500/30 border border-blue-400/50 p-2.5 text-blue-200 hover:bg-blue-500/50 backdrop-blur-xs transition-all"
                                title="Edit / Crop image"
                            >
                                <Pencil class="h-4 w-4" />
                            </button>
                            <button
                                type="button"
                                @click="toggleFeatured(img.id)"
                                class="rounded-full p-2.5 text-white backdrop-blur-xs transition-all"
                                :class="img.is_featured
                                    ? 'bg-amber-400/30 border border-amber-400 hover:bg-amber-400/40 text-amber-300'
                                    : 'bg-white/20 hover:bg-white/30'"
                                :title="img.is_featured ? 'Remove from public gallery' : 'Publish to public gallery'"
                            >
                                <component :is="img.is_featured ? EyeOff : Globe" class="h-4 w-4" />
                            </button>
                            <button
                                type="button"
                                @click="deleteImage(img.id)"
                                class="rounded-full bg-red-600/35 border border-red-500/40 p-2.5 text-red-200 hover:bg-red-600/50 backdrop-blur-xs transition-all"
                                title="Delete permanently"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>

                        <span
                            v-if="img.is_featured"
                            class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full bg-emerald-500 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-white shadow-md shadow-emerald-500/20"
                        >
                            <Globe class="h-2.5 w-2.5" />
                            Published
                        </span>
                    </div>

                    <div class="p-4 space-y-1 bg-card">
                        <p class="font-bold text-sm text-foreground line-clamp-1">
                            {{ img.event?.title ?? 'Unassigned Trip' }}
                        </p>
                        <div class="flex items-center justify-between text-xs text-muted-foreground">
                            <span class="flex items-center gap-1">
                                <Calendar class="h-3 w-3" />
                                {{ img.event?.status ?? 'draft' }}
                            </span>
                            <span class="font-mono text-[10px]">ID: {{ img.id }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="flex min-h-60 flex-col items-center justify-center rounded-2xl border border-border bg-card text-center p-6 dark:border-slate-800">
                <ImageIcon class="h-10 w-10 text-muted-foreground/50 mb-3" />
                <p class="font-semibold text-foreground">No gallery images found</p>
                <p class="text-sm text-muted-foreground mt-1 max-w-sm">
                    Upload new images above and associate them with your events to build the collection.
                </p>
            </div>

            <div v-if="media.last_page > 1" class="mt-8 flex items-center justify-center gap-1.5">
                <template v-for="link in media.links" :key="link.label">
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
                        class="cursor-default rounded-xl border border-border/40 bg-card/40 px-4 py-2 text-sm text-muted-foreground/45 dark:border-slate-800"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </div>

    <!-- ── Edit / Crop Modal ───────────────────────────────────────────────── -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="editingMedia" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" @click.self="closeEdit">
                <div class="relative w-full max-w-4xl rounded-2xl border border-border bg-card shadow-2xl flex flex-col max-h-[90vh]">
                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-border shrink-0">
                        <div>
                            <h2 class="text-lg font-bold text-foreground flex items-center gap-2">
                                <Pencil class="h-4 w-4 text-amber-500" />
                                Edit Gallery Image
                            </h2>
                            <p class="text-xs text-muted-foreground mt-0.5">ID: {{ editingMedia.id }}</p>
                        </div>
                        <button type="button" @click="closeEdit" class="rounded-lg p-2 text-muted-foreground hover:bg-muted transition-colors">
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="overflow-y-auto flex-1 p-6 space-y-6">
                        <!-- Event assignment -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-foreground">Assigned Event</label>
                            <select
                                v-model="editForm.event_id"
                                class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm text-foreground focus:border-ring focus:outline-none"
                            >
                                <option v-for="event in sortedEvents" :key="event.id" :value="event.id">
                                    {{ event.title }} ({{ event.status }})
                                </option>
                            </select>
                        </div>

                        <!-- Crop area -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-foreground">Crop Image</p>
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        @click="zoomOut"
                                        class="rounded-lg p-1.5 text-muted-foreground hover:bg-muted transition-colors"
                                        title="Zoom out"
                                    >
                                        <ZoomOut class="h-4 w-4" />
                                    </button>
                                    <span class="text-xs text-muted-foreground w-12 text-center">{{ Math.round(imgScale * 100) }}%</span>
                                    <button
                                        type="button"
                                        @click="zoomIn"
                                        class="rounded-lg p-1.5 text-muted-foreground hover:bg-muted transition-colors"
                                        title="Zoom in"
                                    >
                                        <ZoomIn class="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        @click="resetView"
                                        class="rounded-lg p-1.5 text-muted-foreground hover:bg-muted transition-colors"
                                        title="Reset view"
                                    >
                                        <RotateCcw class="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        @click="toggleCropMode"
                                        class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors flex items-center gap-1.5"
                                        :class="isCropping
                                            ? 'bg-amber-400/20 border border-amber-400 text-amber-400'
                                            : 'bg-muted text-foreground hover:bg-muted/80'"
                                    >
                                        <CropIcon class="h-3.5 w-3.5" />
                                        {{ isCropping ? 'Drawing crop…' : 'Crop' }}
                                    </button>
                                </div>
                            </div>

                            <p class="text-xs text-muted-foreground">
                                <span v-if="isCropping">Click and drag on the image to draw a crop selection, then click "Apply Crop".</span>
                                <span v-else>Click "Crop" to draw a selection. Scroll or use buttons to zoom. Drag to pan.</span>
                            </p>

                            <div
                                ref="cropContainer"
                                class="relative rounded-xl overflow-hidden bg-slate-950 border border-border"
                                style="height: 400px;"
                            >
                                <canvas
                                    ref="cropCanvas"
                                    width="800"
                                    height="400"
                                    class="w-full h-full"
                                    :class="isCropping ? 'cursor-crosshair' : 'cursor-grab active:cursor-grabbing'"
                                    @mousedown="onCanvasMouseDown"
                                    @mousemove="onCanvasMouseMove"
                                    @mouseup="onCanvasMouseUp"
                                    @mouseleave="onCanvasMouseUp"
                                    @wheel.prevent="onCanvasWheel"
                                />
                                <div v-if="!cropImage" class="absolute inset-0 flex items-center justify-center">
                                    <Loader2 class="h-8 w-8 animate-spin text-amber-400" />
                                </div>
                            </div>

                            <div v-if="cropRect && cropRect.w > 10 && cropRect.h > 10" class="flex justify-end">
                                <button
                                    type="button"
                                    @click="applyCrop"
                                    class="rounded-xl bg-amber-400 px-4 py-2 text-sm font-bold text-slate-900 hover:bg-amber-300 transition-all flex items-center gap-2"
                                >
                                    <Check class="h-4 w-4" />
                                    Apply Crop
                                </button>
                            </div>
                        </div>

                        <!-- Crop preview -->
                        <div v-if="croppedPreview" class="space-y-2">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-foreground flex items-center gap-2">
                                    <Check class="h-4 w-4 text-emerald-500" />
                                    Cropped Preview
                                </p>
                                <button type="button" @click="clearCrop" class="text-xs text-red-500 hover:underline">Remove crop</button>
                            </div>
                            <img :src="croppedPreview" class="max-h-48 rounded-xl border border-border object-contain" />
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-border shrink-0">
                        <button type="button" @click="closeEdit" class="rounded-xl border border-border px-5 py-2.5 text-sm font-semibold text-muted-foreground hover:bg-muted transition-colors">
                            Cancel
                        </button>
                        <button
                            type="button"
                            @click="submitEdit"
                            :disabled="editForm.processing"
                            class="rounded-xl bg-amber-400 px-6 py-2.5 text-sm font-bold text-slate-900 hover:bg-amber-300 disabled:opacity-60 transition-all shadow-md shadow-amber-400/10"
                        >
                            <span v-if="editForm.processing">Saving…</span>
                            <span v-else>Save Changes</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
