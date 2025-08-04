<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    product: {
        id: number;
        name: string;
        description: string;
        price: number;
        first_image_url?: string;
        category?: { name: string };
        brand?: { name: string };
    };
}>();

const addingToCart = ref(false);

const addToCart = async () => {
    if (addingToCart.value) return;

    addingToCart.value = true;

    try {
        router.post(route('customer.cart.add', props.product.id), {
            quantity: 1,
        }, {
            onSuccess: (page) => {
                const data = page.props.flash as any;
                if (data?.success) {
                    toast.success('Success', {
                        description: data.message,
                    });
                }
            },
            onError: (errors) => {
                console.error('Cart add error:', errors);
                toast.error('Error', {
                    description: 'Failed to add product to cart',
                });
            },
            onFinish: () => {
                addingToCart.value = false;
            },
        });
    } catch (error) {
        console.error('Network error:', error);
        toast.error('Error', {
            description: 'Network error. Please try again.',
        });
        addingToCart.value = false;
    }
};
</script>

<template>
    <Head :title="product.name" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ product.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <img
                            :src="product.first_image_url || '/images/placeholder.png'"
                            :alt="product.name || 'Product Image'"
                            class="w-full h-64 object-cover rounded-md mb-4"
                        />
                        <h3 class="text-lg font-semibold mb-2">{{ product.name }}</h3>
                        <p class="text-gray-600 mb-2">{{ product.description }}</p>
                        <p class="text-gray-600">Category: {{ product.category?.name }}</p>
                        <p class="text-gray-600">Brand: {{ product.brand?.name }}</p>
                        <p class="text-indigo-600 font-bold mt-2">LKR {{ product.price.toLocaleString() }}</p>
                        <Button
                            :disabled="addingToCart"
                            class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                            @click="addToCart"
                        >
                            {{ addingToCart ? 'Adding...' : 'Add to Cart' }}
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

