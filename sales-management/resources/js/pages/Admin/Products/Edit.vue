<script setup lang="ts">
import AuthenticatedLayout from '../../../layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface Brand {
    id: number;
    name: string;
}

interface Category {
    id: number;
    name: string;
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
    brand_id: number | null;
    category_id: number;
    images: string[];
    image_urls: string[];
}

interface ImageInfo {
    path: string;
    url: string;
    index: number;
    is_primary: boolean;
    exists: boolean;
    size: number;
}

// Define the PageProps interface to fix Error 2
interface PageProps {
    errors: Record<string, string>;
    image_info?: ImageInfo[];
    [key: string]: any;
}

const props = defineProps<{
    product: Product;
    brands: Brand[];
    categories: Category[];
    image_info: ImageInfo[];
}>();

const form = ref({
    name: props.product.name,
    slug: props.product.slug,
    description: props.product.description || '',
    short_description: props.product.short_description || '',
    sku: props.product.sku,
    price: props.product.price.toString(),
    compare_price: props.product.compare_price?.toString() || '',
    stock_quantity: props.product.stock_quantity.toString(),
    track_quantity: props.product.track_quantity,
    weight: props.product.weight?.toString() || '',
    dimensions: {
        length: props.product.dimensions?.length?.toString() || '',
        width: props.product.dimensions?.width?.toString() || '',
        height: props.product.dimensions?.height?.toString() || '',
    },
    status: props.product.status,
    brand_id: props.product.brand_id?.toString() || '',
    category_id: props.product.category_id.toString(),
});

// Image management state
const currentImages = ref<ImageInfo[]>([...props.image_info]);
const isUploading = ref(false);
const isDeletingAll = ref(false);
const selectedFiles = ref<FileList | null>(null);
const dragCounter = ref(0);
const selectedImageIndex = ref<number | null>(null);
const showImageModal = ref(false);

// Computed properties (removed unused canAddMoreImages to fix Error 1)
const hasImages = computed(() => currentImages.value.length > 0);
const primaryImage = computed(() => currentImages.value.find(img => img.is_primary));
const secondaryImages = computed(() => currentImages.value.filter(img => !img.is_primary));
const totalImages = computed(() => currentImages.value.length);

// Utility functions
const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// Form submission for product data
const submit = () => {
    const formData = new FormData();
    formData.append('name', form.value.name);
    formData.append('slug', form.value.slug);
    formData.append('description', form.value.description || '');
    formData.append('short_description', form.value.short_description || '');
    formData.append('sku', form.value.sku);
    formData.append('price', form.value.price);
    formData.append('compare_price', form.value.compare_price || '');
    formData.append('stock_quantity', form.value.stock_quantity);
    formData.append('track_quantity', form.value.track_quantity ? '1' : '0');
    formData.append('weight', form.value.weight || '');
    formData.append('dimensions[length]', form.value.dimensions.length || '');
    formData.append('dimensions[width]', form.value.dimensions.width || '');
    formData.append('dimensions[height]', form.value.dimensions.height || '');
    formData.append('status', form.value.status);
    formData.append('brand_id', form.value.brand_id || '');
    formData.append('category_id', form.value.category_id);
    formData.append('_method', 'PATCH');

    router.post(route('admin.products.update', props.product.id), formData);
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

    const totalAfterUpload = currentImages.value.length + selectedFiles.value.length;
    if (totalAfterUpload > 10) {
        alert('Maximum 10 images allowed. Please select fewer images.');
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
            onSuccess: (page: { props: PageProps }) => {
                selectedFiles.value = null;
                const fileInput = document.getElementById('imageUpload') as HTMLInputElement;
                if (fileInput) fileInput.value = '';

                // Update current images from the response
                if (page.props.image_info) {
                    currentImages.value = [...page.props.image_info];
                }

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
            onSuccess: (page: { props: PageProps }) => {
                // Update current images from the response
                if (page.props.image_info) {
                    currentImages.value = [...page.props.image_info];
                }
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
            onSuccess: (page: { props: PageProps }) => {
                // Update current images from the response
                if (page.props.image_info) {
                    currentImages.value = [...page.props.image_info];
                }
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
            onSuccess: (page: { props: PageProps }) => {
                // Update current images from the response
                if (page.props.image_info) {
                    currentImages.value = [...page.props.image_info];
                }
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
    <Head title="Edit Product" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Product
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Name</label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Slug</label>
                                    <input
                                        v-model="form.slug"
                                        type="text"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea
                                        v-model="form.description"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        rows="4"
                                        required
                                    ></textarea>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Short Description</label>
                                    <textarea
                                        v-model="form.short_description"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        rows="2"
                                    ></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">SKU</label>
                                    <input
                                        v-model="form.sku"
                                        type="text"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Price</label>
                                    <input
                                        v-model="form.price"
                                        type="number"
                                        step="0.01"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Compare Price</label>
                                    <input
                                        v-model="form.compare_price"
                                        type="number"
                                        step="0.01"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Stock Quantity</label>
                                    <input
                                        v-model="form.stock_quantity"
                                        type="number"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                </div>
                                <div>
                                    <label class="flex items-center">
                                        <input
                                            v-model="form.track_quantity"
                                            type="checkbox"
                                            class="h-4 w-4 text-blue-600 border-gray-300 rounded"
                                        />
                                        <span class="ml-2 text-sm text-gray-700">Track Quantity</span>
                                    </label>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Weight (kg)</label>
                                    <input
                                        v-model="form.weight"
                                        type="number"
                                        step="0.01"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Dimensions (cm)</label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <input
                                            v-model="form.dimensions.length"
                                            type="number"
                                            step="0.01"
                                            placeholder="Length"
                                            class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        />
                                        <input
                                            v-model="form.dimensions.width"
                                            type="number"
                                            step="0.01"
                                            placeholder="Width"
                                            class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        />
                                        <input
                                            v-model="form.dimensions.height"
                                            type="number"
                                            step="0.01"
                                            placeholder="Height"
                                            class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status</label>
                                    <select
                                        v-model="form.status"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    >
                                        <option value="active">Active</option>
                                        <option value="draft">Draft</option>
                                        <option value="archived">Archived</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Brand</label>
                                    <select
                                        v-model="form.brand_id"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="">Select Brand</option>
                                        <option v-for="brand in props.brands" :key="brand.id" :value="brand.id">
                                            {{ brand.name }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Category</label>
                                    <select
                                        v-model="form.category_id"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    >
                                        <option value="">Select Category</option>
                                        <option v-for="category in props.categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Image Management Section -->
                                <div class="md:col-span-2">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="font-medium text-gray-900">
                                            Product Images ({{ totalImages }})
                                        </h4>
                                        <div class="flex gap-2">
                                            <button
                                                v-if="hasImages"
                                                type="button"
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
                                                    type="button"
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
                                                        type="button"
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
                                                            type="button"
                                                            @click="setPrimaryImage(image.path)"
                                                            class="px-2 py-1 text-xs bg-blue-500 text-white rounded hover:bg-blue-600"
                                                        >
                                                            Set Primary
                                                        </button>
                                                        <button
                                                            type="button"
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

                                <div class="md:col-span-2 flex gap-2">
                                    <button
                                        type="submit"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200"
                                        :disabled="isUploading"
                                    >
                                        <span v-if="isUploading">Updating...</span>
                                        <span v-else>Update Product</span>
                                    </button>
                                    <a
                                        :href="route('admin.products.index')"
                                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200"
                                    >
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
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
                    :src="currentImages[selectedImageIndex].url"
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
