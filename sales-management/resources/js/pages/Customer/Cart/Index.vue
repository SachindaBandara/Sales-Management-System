<template>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
        <h1 class="text-2xl font-bold mb-6">Your Shopping Cart</h1>

        <div v-if="cartItems.length === 0" class="text-center py-12">
            <ShoppingCart class="h-16 w-16 mx-auto text-gray-400" />
            <p class="mt-4 text-lg text-muted-foreground">Your cart is empty</p>
            <Button as-child class="mt-4">
                <Link :href="route('customer.home')">Continue Shopping</Link>
            </Button>
        </div>

        <div v-else class="space-y-6">
            <!-- Cart Items -->
            <div class="bg-card rounded-lg shadow-sm">
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-4">
                        <div v-for="item in cartItems" :key="item.id" class="flex items-center space-x-4 border-b py-4 last:border-b-0">
                            <img :src="item.product.image" :alt="item.product.name" class="w-20 h-20 object-cover rounded" />
                            <div class="flex-1">
                                <h3 class="font-medium">{{ item.product.name }}</h3>
                                <p class="text-sm text-muted-foreground">
                                    {{ item.product.brand.name }} | {{ item.product.category.name }}
                                </p>
                                <p class="text-sm">Quantity: {{ item.quantity }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-medium">${{ item.total.toFixed(2) }}</p>
                                <Button variant="ghost" size="sm">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="bg-card rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Order Summary</h2>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span>${{ subtotal.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tax (10%)</span>
                        <span>${{ tax.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Shipping</span>
                        <span>${{ shipping.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between font-semibold pt-2 border-t">
                        <span>Total</span>
                        <span>${{ total.toFixed(2) }}</span>
                    </div>
                </div>
                <Button as-child class="w-full mt-6">
                    <Link :href="route('customer.checkout')">Proceed to Checkout</Link>
                </Button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import { ShoppingCart, Trash2 } from 'lucide-vue-next';

defineProps<{
    cartItems: Array<{
        id: number;
        product: {
            id: number;
            name: string;
            image: string;
            brand: { name: string };
            category: { name: string };
        };
        quantity: number;
        total: number;
    }>;
    subtotal: number;
    tax: number;
    shipping: number;
    total: number;
}>();


</script>
