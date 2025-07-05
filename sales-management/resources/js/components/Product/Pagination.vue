<template>
    <div class="mt-6">
        <div class="flex justify-between items-center">
            <button
                :disabled="!products.prev_page_url"
                class="px-4 py-2 bg-gray-200 rounded-md disabled:opacity-50"
                @click="$emit('change-page', products.prev_page_url)"
            >
                Previous
            </button>
            <span>Page {{ products.current_page }} of {{ products.last_page }}</span>
            <button
                :disabled="!products.next_page_url"
                class="px-4 py-2 bg-gray-200 rounded-md disabled:opacity-50"
                @click="$emit('change-page', products.next_page_url)"
            >
                Next
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
interface Product {
    id: number;
    name: string;
    price: number;
    image?: string;
    is_in_stock: boolean;
}

interface PaginatedProducts {
    data: Product[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

defineProps<{
    products: PaginatedProducts;
}>();

defineEmits(['change-page']);
</script>
