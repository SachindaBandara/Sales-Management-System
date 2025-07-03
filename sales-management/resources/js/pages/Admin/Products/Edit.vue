<script setup lang="ts">
import AuthenticatedLayout from '../../../layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

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
}

const props = defineProps<{
    product: Product;
    brands: Brand[];
    categories: Category[];
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
    images: [] as File[],
});

const imagePreviews = ref<string[]>(props.product.images);
const removedImages = ref<string[]>([]);

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
    
    form.value.images.forEach((image, index) => {
        formData.append(`images[${index}]`, image);
    });

    // Optionally, you could send removed images to the backend if needed
    // formData.append('removed_images', JSON.stringify(removedImages.value));
    
    formData.append('_method', 'PATCH');

    router.post(route('admin.products.update', props.product.id), formData, {
        onSuccess: () => {
            form.value.images = [];
            imagePreviews.value = [];
            removedImages.value = [];
        },
    });
};

const handleFileChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const files = Array.from(input.files || []);
    
    form.value.images = [...form.value.images, ...files];
    imagePreviews.value = [
        ...imagePreviews.value,
        ...files.map(file => URL.createObjectURL(file))
    ];
};

const removeImage = (index: number) => {
    const removedImage = imagePreviews.value[index];
    if (props.product.images.includes(removedImage)) {
        removedImages.value.push(removedImage);
    }
    form.value.images = form.value.images.filter((_, i) => 
        !imagePreviews.value[i].startsWith('blob:') || i !== index
    );
    imagePreviews.value = imagePreviews.value.filter((_, i) => i !== index);
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
                        <form @submit.prevent="submit" enctype="multipart/form-data">
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
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Images</label>
                                    <div class="mt-2 flex flex-wrap gap-4">
                                        <div
                                            v-for="(preview, index) in imagePreviews"
                                            :key="index"
                                            class="relative"
                                        >
                                            <img
                                                :src="preview"
                                                class="h-24 w-24 rounded-md object-cover"
                                                alt="Image preview"
                                            />
                                            <button
                                                type="button"
                                                @click="removeImage(index)"
                                                class="absolute top-0 right-0 -mt-2 -mr-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center"
                                            >
                                                ×
                                            </button>
                                        </div>
                                    </div>
                                    <input
                                        type="file"
                                        accept="image/*"
                                        multiple
                                        class="mt-2 w-full border-gray-300 rounded-md shadow-sm"
                                        @change="handleFileChange"
                                    />
                                    <p class="mt-1 text-sm text-gray-500">
                                        Select multiple images (JPEG, PNG, JPG, GIF, max 2MB each)
                                    </p>
                                </div>
                                <div class="md:col-span-2 flex gap-2">
                                    <button
                                        type="submit"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200"
                                    >
                                        Update
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
    </AuthenticatedLayout>
</template>