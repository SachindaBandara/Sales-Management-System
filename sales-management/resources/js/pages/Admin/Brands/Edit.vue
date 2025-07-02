<script setup lang="ts">
import AuthenticatedLayout from '../../../layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Brand {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    logo: string | null;
    is_active: boolean;
}

const props = defineProps<{
    brand: Brand;
}>();

const form = ref({
    name: props.brand.name,
    slug: props.brand.slug,
    description: props.brand.description || '',
    logo: null as File | null,
    is_active: props.brand.is_active,
});

const submit = () => {
    const formData = new FormData();
    formData.append('name', form.value.name);
    formData.append('slug', form.value.slug);
    formData.append('description', form.value.description || '');
    formData.append('is_active', form.value.is_active ? '1' : '0');
    if (form.value.logo) {
        formData.append('logo', form.value.logo);
    }
    formData.append('_method', 'PATCH');

    router.post(route('admin.brands.update', props.brand.id), formData);
};

const handleFileChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    form.value.logo = input.files?.[0] || null;
};
</script>

<template>
    <Head title="Edit Brand" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Brand
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
                                    <label class="block text-sm font-medium text-gray-700">Slug</label>
                                    <input
                                        v-model="form.slug"
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
                                    <label class="block text-sm font-medium text-gray-700">Logo</label>
                                    <img
                                        v-if="props.brand.logo"
                                        :src="props.brand.logo"
                                        class="h-10 w-10 rounded-full object-cover mb-2"
                                        alt="Current brand logo"
                                    />
                                    <input
                                        type="file"
                                        accept="image/*"
                                        class="mt-1 w-full border-gray-300 rounded-md shadow-sm"
                                        @change="handleFileChange"
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
                                        :href="route('admin.brands.index')"
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