<script setup lang="ts">
import AuthenticatedLayout from '../../../layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface Brand {
    id: number;
    name: string;
}

interface Category {
    id: number;
    name: string;
}

const props = defineProps<{
    brands: Brand[];
    categories: Category[];
}>();

const form = useForm({
    name: '',
    slug: '',
    description: '',
    short_description: '',
    sku: '',
    price: '',
    compare_price: '',
    stock_quantity: '',
    track_quantity: true,
    weight: '',
    dimensions: {
        length: '',
        width: '',
        height: '',
    },
    status: 'draft',
    brand_id: '',
    category_id: '',
});

const selectedImages = ref<File[]>([]);
const imagePreviews = ref<string[]>([]);
const isUploading = ref(false);
const uploadProgress = ref(0);

// Auto-generate slug from name
const generateSlug = () => {
    if (form.name && !form.slug) {
        form.slug = form.name
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
    }
};

const submit = () => {
    form.post(route('admin.products.store'), {
        onSuccess: (page) => {
            // If we have images selected, upload them after product creation
            if (selectedImages.value.length > 0) {
                const productId = extractProductIdFromResponse(page);
                if (productId) {
                    uploadImages(productId);
                } else {
                    // Redirect to products index if no images to upload
                    router.visit(route('admin.products.index'));
                }
            } else {
                // No images, just redirect
                router.visit(route('admin.products.index'));
            }
        },
        onError: (errors) => {
            console.error('Product creation failed:', errors);
        }
    });
};

const extractProductIdFromResponse = (page: any): number | null => {
    // Try to extract product ID from the response
    // This might need adjustment based on your actual response structure
    if (page.props?.product?.id) {
        return page.props.product.id;
    }

    // Alternative: parse from URL if redirected to product show/edit page
    const url = page.url || window.location.href;
    const match = url.match(/\/products\/(\d+)/);
    return match ? parseInt(match[1]) : null;
};

const uploadImages = async (productId: number) => {
    if (selectedImages.value.length === 0) return;

    isUploading.value = true;
    uploadProgress.value = 0;

    try {
        const formData = new FormData();
        selectedImages.value.forEach((image) => {
            formData.append('images[]', image);
        });

        const response = await fetch(route('admin.products.images.upload', { product: productId }), {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
            },
        });

        const result = await response.json();

        if (result.success) {
            // Success - redirect to products index with success message
            router.visit(route('admin.products.index'), {
                data: {
                    success: `Product created successfully with ${result.uploaded_count} images uploaded.`
                }
            });
        } else {
            // Upload failed, but product was created
            console.error('Image upload failed:', result.message);
            router.visit(route('admin.products.index'), {
                data: {
                    warning: 'Product created successfully, but image upload failed. You can upload images later.'
                }
            });
        }
    } catch (error) {
        console.error('Image upload error:', error);
        router.visit(route('admin.products.index'), {
            data: {
                warning: 'Product created successfully, but image upload failed. You can upload images later.'
            }
        });
    } finally {
        isUploading.value = false;
        uploadProgress.value = 0;
    }
};

const handleFileChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const files = Array.from(input.files || []);

    // Validate file types and sizes
    const validFiles = files.filter(file => {
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (!validTypes.includes(file.type)) {
            alert(`Invalid file type: ${file.name}. Only JPEG, PNG, JPG, GIF, and WebP are allowed.`);
            return false;
        }

        if (file.size > maxSize) {
            alert(`File too large: ${file.name}. Maximum size is 5MB.`);
            return false;
        }

        return true;
    });

    // Limit to 10 images
    if (validFiles.length > 10) {
        alert('Maximum 10 images allowed.');
        validFiles.splice(10);
    }

    selectedImages.value = validFiles;

    // Create previews
    imagePreviews.value = validFiles.map(file => URL.createObjectURL(file));
};

const removeImage = (index: number) => {
    // Revoke the object URL to free memory
    if (imagePreviews.value[index]) {
        URL.revokeObjectURL(imagePreviews.value[index]);
    }

    selectedImages.value = selectedImages.value.filter((_, i) => i !== index);
    imagePreviews.value = imagePreviews.value.filter((_, i) => i !== index);
};

const moveImage = (fromIndex: number, toIndex: number) => {
    if (toIndex < 0 || toIndex >= selectedImages.value.length) return;

    // Move in selectedImages array
    const imageItem = selectedImages.value.splice(fromIndex, 1)[0];
    selectedImages.value.splice(toIndex, 0, imageItem);

    // Move in previews array
    const previewItem = imagePreviews.value.splice(fromIndex, 1)[0];
    imagePreviews.value.splice(toIndex, 0, previewItem);
};

const isFormValid = computed(() => {
    return form.name && form.sku && form.price && form.stock_quantity && form.category_id;
});

// Cleanup object URLs when component unmounts
const cleanup = () => {
    imagePreviews.value.forEach(url => URL.revokeObjectURL(url));
};

// Watch for route changes to cleanup
router.on('navigate', cleanup);
</script>

<template>
    <Head title="Create Product" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create New Product
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Loading overlay -->
                        <div v-if="form.processing || isUploading" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
                            <div class="bg-white p-6 rounded-lg shadow-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                                    <span class="text-gray-700">
                                        {{ form.processing ? 'Creating product...' : 'Uploading images...' }}
                                    </span>
                                </div>
                                <div v-if="isUploading" class="mt-3">
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" :style="{ width: uploadProgress + '%' }"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Basic Information -->
                                <div class="md:col-span-2">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Name *</label>
                                    <input
                                        v-model="form.name"
                                        @blur="generateSlug"
                                        type="text"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.name }"
                                        required
                                    />
                                    <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Slug *</label>
                                    <input
                                        v-model="form.slug"
                                        type="text"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.slug }"
                                        required
                                    />
                                    <div v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</div>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Description *</label>
                                    <textarea
                                        v-model="form.description"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.description }"
                                        rows="4"
                                        required
                                    ></textarea>
                                    <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Short Description</label>
                                    <textarea
                                        v-model="form.short_description"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.short_description }"
                                        rows="2"
                                        placeholder="Optional brief description for product listings"
                                    ></textarea>
                                    <div v-if="form.errors.short_description" class="mt-1 text-sm text-red-600">{{ form.errors.short_description }}</div>
                                </div>

                                <!-- Pricing & Inventory -->
                                <div class="md:col-span-2">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4 mt-6">Pricing & Inventory</h3>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">SKU *</label>
                                    <input
                                        v-model="form.sku"
                                        type="text"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.sku }"
                                        required
                                    />
                                    <div v-if="form.errors.sku" class="mt-1 text-sm text-red-600">{{ form.errors.sku }}</div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Price *</label>
                                    <input
                                        v-model="form.price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.price }"
                                        required
                                    />
                                    <div v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Compare Price</label>
                                    <input
                                        v-model="form.compare_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.compare_price }"
                                        placeholder="Optional original price for showing discounts"
                                    />
                                    <div v-if="form.errors.compare_price" class="mt-1 text-sm text-red-600">{{ form.errors.compare_price }}</div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Stock Quantity *</label>
                                    <input
                                        v-model="form.stock_quantity"
                                        type="number"
                                        min="0"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.stock_quantity }"
                                        required
                                    />
                                    <div v-if="form.errors.stock_quantity" class="mt-1 text-sm text-red-600">{{ form.errors.stock_quantity }}</div>
                                </div>

                                <div class="flex items-center">
                                    <label class="flex items-center">
                                        <input
                                            v-model="form.track_quantity"
                                            type="checkbox"
                                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        />
                                        <span class="ml-2 text-sm text-gray-700">Track inventory quantity</span>
                                    </label>
                                </div>

                                <!-- Physical Properties -->
                                <div class="md:col-span-2">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4 mt-6">Physical Properties</h3>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Weight (kg)</label>
                                    <input
                                        v-model="form.weight"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.weight }"
                                        placeholder="Product weight in kilograms"
                                    />
                                    <div v-if="form.errors.weight" class="mt-1 text-sm text-red-600">{{ form.errors.weight }}</div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Dimensions (cm)</label>
                                    <div class="grid grid-cols-3 gap-2 mt-1">
                                        <input
                                            v-model="form.dimensions.length"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            placeholder="Length"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            :class="{ 'border-red-500': form.errors['dimensions.length'] }"
                                        />
                                        <input
                                            v-model="form.dimensions.width"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            placeholder="Width"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            :class="{ 'border-red-500': form.errors['dimensions.width'] }"
                                        />
                                        <input
                                            v-model="form.dimensions.height"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            placeholder="Height"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            :class="{ 'border-red-500': form.errors['dimensions.height'] }"
                                        />
                                    </div>
                                    <div v-if="form.errors['dimensions.length'] || form.errors['dimensions.width'] || form.errors['dimensions.height']" class="mt-1 text-sm text-red-600">
                                        Invalid dimensions
                                    </div>
                                </div>

                                <!-- Organization -->
                                <div class="md:col-span-2">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4 mt-6">Organization</h3>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status *</label>
                                    <select
                                        v-model="form.status"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.status }"
                                        required
                                    >
                                        <option value="active">Active</option>
                                        <option value="draft">Draft</option>
                                        <option value="archived">Archived</option>
                                    </select>
                                    <div v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Brand</label>
                                    <select
                                        v-model="form.brand_id"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.brand_id }"
                                    >
                                        <option value="">Select Brand</option>
                                        <option v-for="brand in props.brands" :key="brand.id" :value="brand.id">
                                            {{ brand.name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.brand_id" class="mt-1 text-sm text-red-600">{{ form.errors.brand_id }}</div>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Category *</label>
                                    <select
                                        v-model="form.category_id"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.category_id }"
                                        required
                                    >
                                        <option value="">Select Category</option>
                                        <option v-for="category in props.categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.category_id" class="mt-1 text-sm text-red-600">{{ form.errors.category_id }}</div>
                                </div>

                                <!-- Images -->
                                <div class="md:col-span-2">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4 mt-6">Images</h3>
                                    <p class="text-sm text-gray-600 mb-4">
                                        Images will be uploaded after the product is created. You can select up to 10 images.
                                    </p>

                                    <div class="space-y-4">
                                        <!-- Image previews -->
                                        <div v-if="imagePreviews.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                                            <div
                                                v-for="(preview, index) in imagePreviews"
                                                :key="index"
                                                class="relative group"
                                            >
                                                <img
                                                    :src="preview"
                                                    class="h-24 w-24 rounded-lg object-cover border-2 border-gray-200 group-hover:border-blue-500 transition-colors"
                                                    alt="Image preview"
                                                />
                                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 rounded-lg transition-opacity"></div>

                                                <!-- Controls -->
                                                <div class="absolute top-1 right-1 flex space-x-1">
                                                    <button
                                                        type="button"
                                                        @click="removeImage(index)"
                                                        class="bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold transition-colors"
                                                        title="Remove image"
                                                    >
                                                        ×
                                                    </button>
                                                </div>

                                                <!-- Move buttons -->
                                                <div class="absolute bottom-1 left-1 flex space-x-1">
                                                    <button
                                                        v-if="index > 0"
                                                        type="button"
                                                        @click="moveImage(index, index - 1)"
                                                        class="bg-blue-500 hover:bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs transition-colors"
                                                        title="Move left"
                                                    >
                                                        ←
                                                    </button>
                                                    <button
                                                        v-if="index < imagePreviews.length - 1"
                                                        type="button"
                                                        @click="moveImage(index, index + 1)"
                                                        class="bg-blue-500 hover:bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs transition-colors"
                                                        title="Move right"
                                                    >
                                                        →
                                                    </button>
                                                </div>

                                                <!-- Primary indicator -->
                                                <div v-if="index === 0" class="absolute top-1 left-1 bg-green-500 text-white text-xs px-2 py-1 rounded">
                                                    Primary
                                                </div>
                                            </div>
                                        </div>

                                        <!-- File input -->
                                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-500 transition-colors">
                                            <input
                                                type="file"
                                                accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                                multiple
                                                class="hidden"
                                                id="image-upload"
                                                @change="handleFileChange"
                                            />
                                            <label for="image-upload" class="cursor-pointer">
                                                <div class="text-gray-600">
                                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                    <p class="text-sm font-medium">Click to select images</p>
                                                    <p class="text-xs text-gray-500">or drag and drop</p>
                                                </div>
                                            </label>
                                        </div>

                                        <div class="text-sm text-gray-500">
                                            <p>• Supported formats: JPEG, PNG, JPG, GIF, WebP</p>
                                            <p>• Maximum file size: 5MB per image</p>
                                            <p>• Maximum 10 images per product</p>
                                            <p>• First image will be used as the primary image</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="md:col-span-2 flex justify-end space-x-3 pt-6 border-t">
                                    <button
                                        type="button"
                                        @click="router.visit(route('admin.products.index'))"
                                        class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md transition-colors duration-200"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="!isFormValid || form.processing"
                                        class="bg-blue-500 hover:bg-blue-600 disabled:bg-gray-400 text-white px-6 py-2 rounded-md transition-colors duration-200"
                                    >
                                        <span v-if="form.processing">Creating...</span>
                                        <span v-else>Create Product</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Add any additional styles here */
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
