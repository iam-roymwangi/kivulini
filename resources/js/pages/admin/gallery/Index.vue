<script setup lang="ts">
import { ref, computed } from 'vue';
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
    FolderPlus
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

function triggerFileInput() {
    fileInput.value?.click();
}

function handleFileChange(e: Event) {
    const files = (e.target as HTMLInputElement).files;
    if (files) {
        addFiles(Array.from(files));
    }
}

function handleDragOver(e: DragEvent) {
    e.preventDefault();
    dragActive.value = true;
}

function handleDragLeave(e: DragEvent) {
    e.preventDefault();
    dragActive.value = false;
}

function handleDrop(e: DragEvent) {
    e.preventDefault();
    dragActive.value = false;
    const files = e.dataTransfer?.files;
    if (files) {
        addFiles(Array.from(files));
    }
}

function addFiles(files: File[]) {
    const validImages = files.filter(f => f.type.startsWith('image/'));
    uploadForm.images.push(...validImages);
    
    validImages.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
            if (e.target?.result) {
                filePreviews.value.push(e.target.result as string);
            }
        };
        reader.readAsDataURL(file);
    });
}

function removePreview(index: number) {
    uploadForm.images.splice(index, 1);
    filePreviews.value.splice(index, 1);
}

function uploadImages() {
    if (!uploadForm.event_id) {
        alert('Please select an event first.');
        return;
    }
    if (uploadForm.images.length === 0) {
        alert('Please select at least one image to upload.');
        return;
    }

    uploadForm.post('/admin/gallery', {
        forceFormData: true,
        onSuccess: () => {
            uploadForm.reset();
            filePreviews.value = [];
        },
    });
}

function deleteImage(id: number) {
    if (confirm('Are you sure you want to permanently delete this gallery image?')) {
        router.delete(`/admin/gallery/${id}`);
    }
}

function toggleFeatured(id: number) {
    router.post(`/admin/gallery/${id}/toggle-featured`);
}

const sortedEvents = computed(() => {
    return [...props.events].sort((a, b) => {
        if (a.status === 'completed' && b.status !== 'completed') return -1;
        if (a.status !== 'completed' && b.status === 'completed') return 1;
        return a.title.localeCompare(b.title);
    });
});
</script>

<template>
    <Head title="Admin — Gallery Management" />

    <div class="space-y-8 p-6 max-w-7xl mx-auto">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-black text-foreground">Gallery Management</h1>
                <p class="text-sm text-muted-foreground">{{ media.total }} total images in gallery</p>
            </div>
            <div class="flex items-center gap-2 rounded-xl bg-amber-500/10 px-4 py-2 border border-amber-500/20 text-xs font-semibold text-amber-500">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                </span>
                WebP Compression & Clarity Auto-Optimization Active
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
                    <!-- Event Selection -->
                    <div class="md:col-span-1 space-y-2">
                        <label class="block text-sm font-medium text-foreground">
                            Assign to Event / Trip *
                        </label>
                        <select 
                            v-model="uploadForm.event_id"
                            class="w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm text-foreground focus:border-ring focus:outline-none"
                            required
                        >
                            <option value="" disabled>Select an Event...</option>
                            <option 
                                v-for="event in sortedEvents" 
                                :key="event.id" 
                                :value="event.id"
                            >
                                {{ event.title }} ({{ event.status }})
                            </option>
                        </select>
                        <p class="text-xs text-muted-foreground">
                            Assigning images to completed events makes them eligible to appear in the public gallery.
                        </p>
                    </div>

                    <!-- Drag & Drop Zone -->
                    <div class="md:col-span-2">
                        <div 
                            class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-border p-8 text-center transition-colors cursor-pointer"
                            :class="dragActive ? 'border-amber-400 bg-amber-500/5' : 'hover:border-amber-400/50'"
                            @click="triggerFileInput"
                            @dragover="handleDragOver"
                            @dragleave="handleDragLeave"
                            @drop="handleDrop"
                        >
                            <input 
                                ref="fileInput"
                                type="file"
                                multiple
                                accept="image/*"
                                class="hidden"
                                @change="handleFileChange"
                            />
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-muted text-muted-foreground mb-3">
                                <Upload class="h-6 w-6" />
                            </div>
                            <p class="text-sm font-semibold text-foreground">
                                Drag & drop images here, or <span class="text-amber-500 underline">browse</span>
                            </p>
                            <p class="text-xs text-muted-foreground mt-1">
                                Supports PNG, JPG, GIF, WebP up to 10MB per file.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Previews -->
                <div v-if="filePreviews.length > 0" class="space-y-3 border-t border-border pt-4 dark:border-slate-800">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-foreground">Selected Images ({{ uploadForm.images.length }})</h3>
                        <button 
                            type="button" 
                            @click="filePreviews = []; uploadForm.images = []" 
                            class="text-xs text-red-500 hover:underline"
                        >
                            Clear All
                        </button>
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8">
                        <div 
                            v-for="(url, idx) in filePreviews" 
                            :key="idx" 
                            class="group relative aspect-square overflow-hidden rounded-lg border border-border"
                        >
                            <img :src="url" class="h-full w-full object-cover" />
                            <button 
                                type="button" 
                                @click="removePreview(idx)"
                                class="absolute right-1 top-1 rounded-full bg-red-600 p-1 text-white opacity-0 transition-opacity group-hover:opacity-100 shadow"
                            >
                                <X class="h-3 w-3" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end gap-3">
                    <button 
                        v-if="uploadForm.images.length > 0"
                        type="submit"
                        :disabled="uploadForm.processing"
                        class="rounded-xl bg-amber-400 px-6 py-2.5 text-sm font-bold text-slate-900 hover:bg-amber-300 disabled:opacity-60 transition-all shadow-md shadow-amber-400/10"
                    >
                        {{ uploadForm.processing ? `Uploading & Compiling (${uploadForm.progress ? uploadForm.progress.percentage + '%' : 'Processing'})...` : 'Upload Images' }}
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
                    <!-- Image Wrapper -->
                    <div class="relative aspect-video overflow-hidden bg-muted">
                        <img :src="img.url" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                        
                        <!-- Hover Action Bar overlay -->
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

                        <!-- Published status indicator -->
                        <span 
                            v-if="img.is_featured"
                            class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full bg-emerald-500 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-white shadow-md shadow-emerald-500/20"
                        >
                            <Globe class="h-2.5 w-2.5" />
                            Published
                        </span>
                    </div>

                    <!-- Meta Details -->
                    <div class="p-4 space-y-1 bg-card">
                        <p class="font-bold text-sm text-foreground line-clamp-1">
                            {{ img.event?.title ?? 'Unassigned Trip' }}
                        </p>
                        <div class="flex items-center justify-between text-xs text-muted-foreground">
                            <span class="flex items-center gap-1">
                                <Calendar class="h-3 w-3" />
                                {{ img.event?.status ?? 'draft' }}
                            </span>
                            <span class="font-mono text-[10px]">
                                ID: {{ img.id }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="flex min-h-60 flex-col items-center justify-center rounded-2xl border border-border bg-card text-center p-6 dark:border-slate-800">
                <ImageIcon class="h-10 w-10 text-muted-foreground/50 mb-3" />
                <p class="font-semibold text-foreground">No gallery images found</p>
                <p class="text-sm text-muted-foreground mt-1 max-w-sm">
                    Upload new images above and associate them with your events to build the collection.
                </p>
            </div>

            <!-- Pagination -->
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
</template>
