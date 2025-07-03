<script setup lang="ts">
import AuthenticatedLayout from '../../../layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    image: string | null;
    parent_id: number | null;
    sort_order: number;
    is_active: boolean;
}

interface ParentCategory {
    id: number;
    name: string;
}

const props = defineProps<{
    category: Category;
    parentCategories: ParentCategory[];
}>();

const form = ref({
    name: props.category.name,
    description: props.category.description || '',
    image: null as File | null,
    parent_id: props.category.parent_id,
    sort_order: props.category.sort_order,
    is_active: props.category.is_active,
});

const submit = () => {
    const formData = new FormData();
    formData.append('name', form.value.name);
    formData.append('description', form.value.description || '');
    formData.append('sort_order', form.value.sort_order.toString());
    formData.append('is_active', form.value.is_active ? '1' : '0');
    if (form.value.image) {
        formData.append('image', form.value.image);
    }
    if (form.value.parent_id) {
        formData.append('parent_id', form.value.parent_id.toString());
    }
    formData.append('_method', 'PATCH');

    router.post(route('admin.categories.update', props.category.id), formData);
};

const handleFileChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    form.value.image = input.files?.[0] || null;
};
</script>

<template>
    <Head title="Edit Category" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Category
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" enctype="multipart/form-data">
                            <div class="grid grid-cols-1 gap-4">
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
                                    <label class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea
                                        v-model="form.description"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    ></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Image</label>
                                    <img
                                        v-if="props.category.image"
                                        :src="props.category.image"
                                        class="h-10 w-10 rounded-full object-cover mb-2"
                                        alt="Current category image"
                                    />
                                    <input
                                        type="file"
                                        accept="image/*"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm"
                                        @change="handleFileChange"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Parent Category</label>
                                    <select
                                        v-model="form.parent_id"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option :value="null">No Parent</option>
                                        <option v-for="category in parentCategories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Sort Order</label>
                                    <input
                                        v-model.number="form.sort_order"
                                        type="number"
                                        min="0"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <label class="flex items-center">
                                        <input
                                            v-model="form.is_active"
                                            type="checkbox"
                                            class="h-4 w-4 text-blue-600 border-gray-300 rounded"
                                        />
                                        <span class="ml-2 text-sm text-gray-700">Active</span>
                                    </label>
                                </div>
                                <div class="flex gap-2">
                                    <button
                                        type="submit"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200"
                                    >
                                        Update
                                    </button>
                                    <a
                                        :href="route('admin.categories.index')"
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