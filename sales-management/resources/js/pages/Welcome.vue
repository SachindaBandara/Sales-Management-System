<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Header from '@/components/header/Header.vue';
import ProductList from '@/components/product/ProductList.vue';
import ProductDetail from '@/components/product/ProductDetail.vue';

// Define interfaces
interface Product {
  id: number;
  name: string;
  description: string;
  price: number;
  rating: number;
  reviews: number;
  image: string;
}

// Reactive state
const currentView = ref('products');
const searchQuery = ref('');
const sortBy = ref('name');
const selectedProduct = ref<Product | null>(null);

// Sample products data
const products = ref<Product[]>([
  {
    id: 1,
    name: 'Wireless Headphones',
    description: 'High-quality wireless headphones with noise cancellation',
    price: 199.99,
    rating: 4.5,
    reviews: 128,
    image: '/placeholder.svg?height=300&width=300',
  },
  {
    id: 2,
    name: 'Smart Watch',
    description: 'Feature-rich smartwatch with health tracking',
    price: 299.99,
    rating: 4.3,
    reviews: 89,
    image: '/placeholder.svg?height=300&width=300',
  },
  {
    id: 3,
    name: 'Laptop Stand',
    description: 'Ergonomic aluminum laptop stand for better posture',
    price: 79.99,
    rating: 4.7,
    reviews: 156,
    image: '/placeholder.svg?height=300&width=300',
  },
  {
    id: 4,
    name: 'Bluetooth Speaker',
    description: 'Portable Bluetooth speaker with premium sound quality',
    price: 129.99,
    rating: 4.4,
    reviews: 203,
    image: '/placeholder.svg?height=300&width=300',
  },
  {
    id: 5,
    name: 'USB-C Hub',
    description: 'Multi-port USB-C hub with HDMI and charging support',
    price: 59.99,
    rating: 4.2,
    reviews: 94,
    image: '/placeholder.svg?height=300&width=300',
  },
  {
    id: 6,
    name: 'Wireless Mouse',
    description: 'Precision wireless mouse with ergonomic design',
    price: 49.99,
    rating: 4.6,
    reviews: 167,
    image: '/placeholder.svg?height=300&width=300',
  },
]);

// Computed properties
const filteredProducts = computed(() => {
  let filtered = products.value;
  if (searchQuery.value) {
    filtered = filtered.filter(
      (product) =>
        product.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        product.description.toLowerCase().includes(searchQuery.value.toLowerCase()),
    );
  }
  return filtered.sort((a, b) => {
    switch (sortBy.value) {
      case 'price':
        return a.price - b.price;
      case 'rating':
        return b.rating - a.rating;
      default:
        return a.name.localeCompare(b.name);
    }
  });
});

// Methods
const selectProduct = (product: Product) => {
  selectedProduct.value = product;
  currentView.value = 'product-detail';
};

const showProducts = () => {
  currentView.value = 'products';
  selectedProduct.value = null; // Clear selected product for safety
};
</script>

<template>
  <Head title="Welcome" />
  <div
    class="flex min-h-screen flex-col bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]"
  >
    <Header v-model:searchQuery="searchQuery" v-model:currentView="currentView" />
    <main class="container mx-auto px-4 py-8">
      <ProductList
        v-if="currentView === 'products'"
        :products="filteredProducts"
        v-model:sortBy="sortBy"
        @selectProduct="selectProduct"
      />
      <ProductDetail
        v-if="currentView === 'product-detail' && selectedProduct"
        :product="selectedProduct"
        @back="showProducts"
      />
    </main>
  </div>
</template>

<style>
@import url('https://rsms.me/inter/inter.css');
</style>