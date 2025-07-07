<script setup lang="ts">
import AuthenticatedLayout from '../../../layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface Brand {
    id: number;
    name: string;
}

interface Category {
    id: number;
    name: string;
}

interface ImageInfo {
    path: string;
    url: string;
    index: number;
    is_primary: boolean;
    exists: boolean;
    size: number;
}

interface Product {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    short_description: string | null;
    sku: string;
    price: number;
    compare_price: number | null;
    stock_quantity: number;
    track_quantity: boolean;
    weight: number | null;
    dimensions: {
        length?: number;
        width?: number;
        height?: number;
    } | null;
    status: string;
    brand: Brand | null;
    category: Category | null;
    images: string[];
    image_urls: string[];
    first_image_url: string;
    discount_percentage: number;
    is_in_stock: boolean;
    image_count: number;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    product: Product;
    image_info: ImageInfo[];
}>();

// Reactive state for image management
const isUploading = ref(false);
const isDeletingAll = ref(false);
const selectedFiles = ref<FileList | null>(null);
const dragCounter = ref(0);
const selectedImageIndex = ref<number | null>(null);
const showImageModal = ref(false);

// Computed properties
const hasImages = computed(() => props.product.image_count > 0);
const primaryImage = computed(() => props.image_info.find(img => img.is_primary));
const secondaryImages = computed(() => props.image_info.filter(img => !img.is_primary));

// Utility functions
const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatPrice = (price: number): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'LKR'
    }).format(price);
};

const formatDimensions = (dimensions: Product['dimensions']): string => {
    if (!dimensions || (!dimensions.length && !dimensions.width && !dimensions.height)) {
        return 'Not specified';
    }
    return `${dimensions.length || 0} x ${dimensions.width || 0} x ${dimensions.height || 0} cm`;
};

const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// Image management functions
const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        selectedFiles.value = target.files;
    }
};

const uploadImages = async () => {
    if (!selectedFiles.value || selectedFiles.value.length === 0) {
        alert('Please select images to upload');
        return;
    }

    isUploading.value = true;
    const formData = new FormData();

    Array.from(selectedFiles.value).forEach(file => {
        formData.append('images[]', file);
    });

    try {
        await router.post(route('admin.products.images.upload', props.product.id), formData, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                selectedFiles.value = null;
                const fileInput = document.getElementById('imageUpload') as HTMLInputElement;
                if (fileInput) fileInput.value = '';
                alert('Images uploaded successfully!');
            },
            onError: (errors) => {
                console.error('Upload failed:', errors);
                alert('Failed to upload images. Please try again.');
            }
        });
    } catch (error) {
        console.error('Upload error:', error);
        alert('An error occurred while uploading images.');
    } finally {
        isUploading.value = false;
    }
};

const deleteImage = async (imagePath: string) => {
    if (!confirm('Are you sure you want to delete this image?')) return;

    try {
        await router.delete(route('admin.products.images.delete', props.product.id), {
            data: { image_path: imagePath },
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                alert('Image deleted successfully!');
            },
            onError: (errors) => {
                console.error('Delete failed:', errors);
                alert('Failed to delete image. Please try again.');
            }
        });
    } catch (error) {
        console.error('Delete error:', error);
        alert('An error occurred while deleting the image.');
    }
};

const deleteAllImages = async () => {
    if (!confirm('Are you sure you want to delete ALL images? This action cannot be undone.')) return;

    isDeletingAll.value = true;
    try {
        await router.delete(route('admin.products.images.delete-all', props.product.id), {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                alert('All images deleted successfully!');
            },
            onError: (errors) => {
                console.error('Delete all failed:', errors);
                alert('Failed to delete all images. Please try again.');
            }
        });
    } catch (error) {
        console.error('Delete all error:', error);
        alert('An error occurred while deleting all images.');
    } finally {
        isDeletingAll.value = false;
    }
};

const setPrimaryImage = async (imagePath: string) => {
    try {
        await router.patch(route('admin.products.images.set-primary', props.product.id), {
            image_path: imagePath
        }, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                alert('Primary image updated successfully!');
            },
            onError: (errors) => {
                console.error('Set primary failed:', errors);
                alert('Failed to set primary image. Please try again.');
            }
        });
    } catch (error) {
        console.error('Set primary error:', error);
        alert('An error occurred while setting the primary image.');
    }
};

const openImageModal = (index: number) => {
    selectedImageIndex.value = index;
    showImageModal.value = true;
};

const closeImageModal = () => {
    showImageModal.value = false;
    selectedImageIndex.value = null;
};

// Drag and drop functionality
const handleDragEnter = (e: DragEvent) => {
    e.preventDefault();
    dragCounter.value++;
};

const handleDragLeave = (e: DragEvent) => {
    e.preventDefault();
    dragCounter.value--;
};

const handleDragOver = (e: DragEvent) => {
    e.preventDefault();
};

const handleDrop = (e: DragEvent) => {
    e.preventDefault();
    dragCounter.value = 0;

    const files = e.dataTransfer?.files;
    if (files) {
        selectedFiles.value = files;
    }
};
</script>

<template>
    <Head title="View Product" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                View Product: {{ product.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Product Header -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-20 w-20">
                                    <img
                                        v-if="product.first_image_url"
                                        :src="product.first_image_url"
                                        class="h-20 w-20 rounded-lg object-cover border-2 border-gray-200"
                                        alt="Product image"
                                    />
                                    <div
                                        v-else
                                        class="h-20 w-20 rounded-lg flex items-center justify-center bg-gray-100 text-gray-600 font-semibold text-2xl border-2 border-gray-200"
                                    >
                                        {{ product.name.charAt(0).toUpperCase() }}
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-2xl font-bold text-gray-900">{{ product.name }}</h3>
                                    <p class="text-sm text-gray-500">{{ product.slug }}</p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <span
                                            :class="{
                                                'bg-green-100 text-green-800': product.status === 'active',
                                                'bg-gray-100 text-gray-800': product.status === 'draft',
                                                'bg-red-100 text-red-800': product.status === 'archived'
                                            }"
                                            class="px-2 py-1 text-xs font-semibold rounded-full"
                                        >
                                            {{ product.status.charAt(0).toUpperCase() + product.status.slice(1) }}
                                        </span>
                                        <span
                                            v-if="product.discount_percentage > 0"
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800"
                                        >
                                            {{ product.discount_percentage }}% OFF
                                        </span>
                                        <span
                                            :class="{
                                                'bg-green-100 text-green-800': product.is_in_stock,
                                                'bg-red-100 text-red-800': !product.is_in_stock
                                            }"
                                            class="px-2 py-1 text-xs font-semibold rounded-full"
                                        >
                                            {{ product.is_in_stock ? 'In Stock' : 'Out of Stock' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-bold text-gray-900">
                                    {{ formatPrice(product.price) }}
                                </div>
                                <div v-if="product.compare_price" class="text-lg line-through text-gray-500">
                                    {{ formatPrice(product.compare_price) }}
                                </div>
                                <div class="text-sm text-gray-500 mt-2">
                                    SKU: {{ product.sku }}
                                </div>
                            </div>
                        </div>

                        <!-- Product Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-900 mb-2">Basic Information</h4>
                                <div class="space-y-2 text-sm">
                                    <div><span class="font-medium">Brand:</span> {{ product.brand?.name || 'No brand' }}</div>
                                    <div><span class="font-medium">Category:</span> {{ product.category?.name || 'No category' }}</div>
                                    <div><span class="font-medium">Weight:</span> {{ product.weight ? `${product.weight} kg` : 'Not specified' }}</div>
                                    <div><span class="font-medium">Dimensions:</span> {{ formatDimensions(product.dimensions) }}</div>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-900 mb-2">Stock Information</h4>
                                <div class="space-y-2 text-sm">
                                    <div><span class="font-medium">Track Quantity:</span> {{ product.track_quantity ? 'Yes' : 'No' }}</div>
                                    <div><span class="font-medium">Stock Quantity:</span> {{ product.track_quantity ? product.stock_quantity : 'N/A' }}</div>
                                    <div><span class="font-medium">Status:</span> {{ product.is_in_stock ? 'In Stock' : 'Out of Stock' }}</div>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-900 mb-2">Timestamps</h4>
                                <div class="space-y-2 text-sm">
                                    <div><span class="font-medium">Created:</span> {{ formatDate(product.created_at) }}</div>
                                    <div><span class="font-medium">Updated:</span> {{ formatDate(product.updated_at) }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Descriptions -->
                        <div class="mb-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Description</h4>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <p class="text-sm text-gray-700">{{ product.description || 'No description provided.' }}</p>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Short Description</h4>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <p class="text-sm text-gray-700">{{ product.short_description || 'No short description provided.' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Image Management Section -->
                        <div class="mb-8">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="font-medium text-gray-900">
                                    Product Images ({{ product.image_count }})
                                </h4>
                                <div class="flex gap-2">
                                    <button
                                        v-if="hasImages"
                                        @click="deleteAllImages"
                                        :disabled="isDeletingAll"
                                        class="px-3 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600 disabled:opacity-50"
                                    >
                                        {{ isDeletingAll ? 'Deleting...' : 'Delete All' }}
                                    </button>
                                </div>
                            </div>

                            <!-- Image Upload Section -->
                            <div class="mb-6">
                                <div
                                    class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors"
                                    :class="{ 'border-blue-400 bg-blue-50': dragCounter > 0 }"
                                    @dragenter="handleDragEnter"
                                    @dragleave="handleDragLeave"
                                    @dragover="handleDragOver"
                                    @drop="handleDrop"
                                >
                                    <div class="space-y-2">
                                        <div class="text-gray-600">
                                            <span class="font-medium">Drag and drop images here</span> or
                                            <label for="imageUpload" class="text-blue-600 hover:text-blue-500 cursor-pointer font-medium">
                                                browse to upload
                                            </label>
                                        </div>
                                        <input
                                            id="imageUpload"
                                            type="file"
                                            multiple
                                            accept="image/*"
                                            class="hidden"
                                            @change="handleFileSelect"
                                        />
                                        <div class="text-xs text-gray-500">
                                            Supported formats: JPEG, PNG, JPG, GIF, WebP (Max 5MB each, up to 10 images)
                                        </div>
                                        <div v-if="selectedFiles" class="text-sm text-gray-700">
                                            {{ selectedFiles.length }} file(s) selected
                                        </div>
                                        <button
                                            v-if="selectedFiles"
                                            @click="uploadImages"
                                            :disabled="isUploading"
                                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50"
                                        >
                                            {{ isUploading ? 'Uploading...' : 'Upload Images' }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Image Gallery -->
                            <div v-if="hasImages" class="space-y-4">
                                <!-- Primary Image -->
                                <div v-if="primaryImage" class="bg-gray-50 p-4 rounded-lg">
                                    <h5 class="font-medium text-gray-900 mb-3">Primary Image</h5>
                                    <div class="flex items-center gap-4">
                                        <img
                                            :src="primaryImage.url"
                                            :alt="product.name"
                                            class="h-24 w-24 rounded-lg object-cover border-2 border-blue-300 cursor-pointer"
                                            @click="openImageModal(primaryImage.index)"
                                        />
                                        <div class="flex-1">
                                            <div class="text-sm text-gray-600">
                                                <div>Size: {{ formatFileSize(primaryImage.size) }}</div>
                                                <div>Path: {{ primaryImage.path }}</div>
                                                <div>Status: {{ primaryImage.exists ? 'Exists' : 'Missing' }}</div>
                                            </div>
                                        </div>
                                        <div class="flex gap-2">
                                            <button
                                                @click="deleteImage(primaryImage.path)"
                                                class="px-3 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Secondary Images -->
                                <div v-if="secondaryImages.length > 0" class="bg-gray-50 p-4 rounded-lg">
                                    <h5 class="font-medium text-gray-900 mb-3">Other Images</h5>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <div
                                            v-for="image in secondaryImages"
                                            :key="image.path"
                                            class="flex items-center gap-3 bg-white p-3 rounded-lg border"
                                        >
                                            <img
                                                :src="image.url"
                                                :alt="product.name"
                                                class="h-16 w-16 rounded-lg object-cover cursor-pointer"
                                                @click="openImageModal(image.index)"
                                            />
                                            <div class="flex-1 min-w-0">
                                                <div class="text-xs text-gray-600">
                                                    <div class="truncate">{{ image.path }}</div>
                                                    <div>{{ formatFileSize(image.size) }}</div>
                                                </div>
                                            </div>
                                            <div class="flex flex-col gap-1">
                                                <button
                                                    @click="setPrimaryImage(image.path)"
                                                    class="px-2 py-1 text-xs bg-blue-500 text-white rounded hover:bg-blue-600"
                                                >
                                                    Set Primary
                                                </button>
                                                <button
                                                    @click="deleteImage(image.path)"
                                                    class="px-2 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- No Images State -->
                            <div v-else class="text-center py-8 text-gray-500">
                                <div class="text-lg mb-2">No images uploaded yet</div>
                                <div class="text-sm">Upload some images to showcase your product</div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3 pt-6 border-t">
                            <Link
                                :href="route('admin.products.edit', product.id)"
                                class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200"
                            >
                                Edit Product
                            </Link>
                            <Link
                                :href="route('admin.products.index')"
                                class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200"
                            >
                                Back to Products
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Image Modal -->
        <div
            v-if="showImageModal && selectedImageIndex !== null"
            class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50"
            @click="closeImageModal"
        >
            <div class="max-w-4xl max-h-full p-4">
                <img
                    :src="image_info[selectedImageIndex].url"
                    :alt="product.name"
                    class="max-w-full max-h-full object-contain"
                    @click.stop
                />
                <div class="text-center mt-4">
                    <button
                        @click="closeImageModal"
                        class="bg-white text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-100"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
