<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ShoppingBag, Star, ArrowRight, Truck, Shield, RefreshCw } from 'lucide-vue-next';

// Mock data for featured products
const featuredProducts = [
  {
    id: 1,
    name: 'Premium Wireless Headphones',
    price: 299.99,
    originalPrice: 399.99,
    image: '/placeholder.svg?height=300&width=300',
    rating: 4.8,
    reviews: 124
  },
  {
    id: 2,
    name: 'Smart Fitness Watch',
    price: 199.99,
    originalPrice: 249.99,
    image: '/placeholder.svg?height=300&width=300',
    rating: 4.6,
    reviews: 89
  },
  {
    id: 3,
    name: 'Minimalist Backpack',
    price: 79.99,
    originalPrice: 99.99,
    image: '/placeholder.svg?height=300&width=300',
    rating: 4.9,
    reviews: 156
  }
];

const features = [
  {
    icon: Truck,
    title: 'Free Shipping',
    description: 'Free shipping on orders over $50'
  },
  {
    icon: Shield,
    title: 'Secure Payment',
    description: '100% secure payment processing'
  },
  {
    icon: RefreshCw,
    title: 'Easy Returns',
    description: '30-day hassle-free returns'
  }
];
</script>

<template>
    <Head title="Welcome - Premium E-commerce">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]">
        <!-- Header -->
        <header class="border-b border-[#19140035] dark:border-[#3E3E3A]">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo -->
                    <div class="flex items-center gap-2">
                        <ShoppingBag class="h-8 w-8 text-[#1b1b18] dark:text-[#EDEDEC]" />
                        <span class="text-xl font-semibold">ShopVue</span>
                    </div>

                    <!-- Navigation -->
                    <nav class="hidden md:flex items-center gap-8">
                        <Link href="/home" class="text-sm hover:text-gray-600 dark:hover:text-gray-300">
                            Home
                        </Link>
                        <Link href="/products" class="text-sm hover:text-gray-600 dark:hover:text-gray-300">
                            Products
                        </Link>
                        <Link href="/categories" class="text-sm hover:text-gray-600 dark:hover:text-gray-300">
                            Categories
                        </Link>
                        <Link href="/brands" class="text-sm hover:text-gray-600 dark:hover:text-gray-300">
                            Brands
                        </Link>
                        <Link href="/about" class="text-sm hover:text-gray-600 dark:hover:text-gray-300">
                            About
                        </Link>
                        <Link href="/contact" class="text-sm hover:text-gray-600 dark:hover:text-gray-300">
                            Contact
                        </Link>
                    </nav>

                    <!-- Auth Links -->
                    <div class="flex items-center gap-4">
                        <Link
                            v-if="$page.props.auth?.user"
                            :href="route('dashboard')"
                            class="inline-flex items-center gap-2 rounded-sm border border-[#19140035] px-4 py-2 text-sm leading-normal hover:border-[#1915014a] dark:border-[#3E3E3A] dark:hover:border-[#62605b]"
                        >
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="inline-block rounded-sm border border-transparent px-4 py-2 text-sm leading-normal hover:border-[#19140035] dark:hover:border-[#3E3E3A]"
                            >
                                Log in
                            </Link>
                            <Link
                                :href="route('register')"
                                class="inline-block rounded-sm border border-[#19140035] px-4 py-2 text-sm leading-normal hover:border-[#1915014a] dark:border-[#3E3E3A] dark:hover:border-[#62605b]"
                            >
                                Register
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative overflow-hidden py-20 lg:py-32">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <h1 class="text-4xl font-bold tracking-tight sm:text-6xl lg:text-7xl">
                        Discover Premium
                        <span class="text-gray-600 dark:text-gray-400">Products</span>
                    </h1>
                    <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-400">
                        Curated collection of high-quality products for modern living.
                        From tech gadgets to lifestyle essentials, find everything you need.
                    </p>
                    <div class="mt-10 flex items-center justify-center gap-x-6">
                        <Link
                            href="/products"
                            class="inline-flex items-center gap-2 rounded-sm bg-[#1b1b18] px-6 py-3 text-sm font-semibold text-white hover:bg-[#2a2a27] dark:bg-[#EDEDEC] dark:text-[#1b1b18] dark:hover:bg-[#d4d4d1]"
                        >
                            Shop Now
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                        <Link
                            href="/categories"
                            class="inline-flex items-center gap-2 text-sm font-semibold leading-6 hover:text-gray-600 dark:hover:text-gray-300"
                        >
                            Browse Categories
                            <ArrowRight class="h-4 w-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Products -->
        <section class="py-16 lg:py-24">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Featured Products</h2>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                        Hand-picked items that our customers love most
                    </p>
                </div>

                <div class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-8 sm:mt-20 lg:mx-0 lg:max-w-none lg:grid-cols-3">
                    <div
                        v-for="product in featuredProducts"
                        :key="product.id"
                        class="group relative overflow-hidden rounded-lg border border-[#19140035] bg-white p-6 hover:border-[#1915014a] dark:border-[#3E3E3A] dark:bg-[#1a1a1a] dark:hover:border-[#62605b]"
                    >
                        <div class="aspect-square overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800">
                            <img
                                :src="product.image"
                                :alt="product.name"
                                class="h-full w-full object-cover object-center group-hover:scale-105 transition-transform duration-300"
                            />
                        </div>

                        <div class="mt-4">
                            <h3 class="text-lg font-semibold">{{ product.name }}</h3>

                            <div class="mt-2 flex items-center gap-2">
                                <div class="flex items-center">
                                    <Star
                                        v-for="i in 5"
                                        :key="i"
                                        class="h-4 w-4"
                                        :class="i <= Math.floor(product.rating) ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300'"
                                    />
                                </div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    ({{ product.reviews }})
                                </span>
                            </div>

                            <div class="mt-3 flex items-center gap-2">
                                <span class="text-xl font-bold">${{ product.price }}</span>
                                <span class="text-sm text-gray-500 line-through">${{ product.originalPrice }}</span>
                            </div>

                            <button class="mt-4 w-full rounded-sm bg-[#1b1b18] py-2 text-sm font-semibold text-white hover:bg-[#2a2a27] dark:bg-[#EDEDEC] dark:text-[#1b1b18] dark:hover:bg-[#d4d4d1]">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section class="border-t border-[#19140035] py-16 dark:border-[#3E3E3A] lg:py-24">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Why Choose Us</h2>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                        We're committed to providing the best shopping experience
                    </p>
                </div>

                <div class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-8 sm:mt-20 lg:mx-0 lg:max-w-none lg:grid-cols-3">
                    <div
                        v-for="feature in features"
                        :key="feature.title"
                        class="text-center"
                    >
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                            <component :is="feature.icon" class="h-8 w-8 text-[#1b1b18] dark:text-[#EDEDEC]" />
                        </div>
                        <h3 class="mt-6 text-lg font-semibold">{{ feature.title }}</h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Newsletter -->
        <section class="border-t border-[#19140035] py-16 dark:border-[#3E3E3A]">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Stay Updated</h2>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                        Get the latest updates on new products and exclusive offers
                    </p>

                    <form class="mt-8 flex max-w-md mx-auto gap-4">
                        <input
                            type="email"
                            placeholder="Enter your email"
                            class="flex-1 rounded-sm border border-[#19140035] px-4 py-2 text-sm focus:border-[#1915014a] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#1a1a1a] dark:focus:border-[#62605b]"
                        />
                        <button
                            type="submit"
                            class="rounded-sm bg-[#1b1b18] px-6 py-2 text-sm font-semibold text-white hover:bg-[#2a2a27] dark:bg-[#EDEDEC] dark:text-[#1b1b18] dark:hover:bg-[#d4d4d1]"
                        >
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-[#19140035] py-12 dark:border-[#3E3E3A]">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <ShoppingBag class="h-6 w-6" />
                            <span class="text-lg font-semibold">ShopVue</span>
                        </div>
                        <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                            Your trusted partner for premium products and exceptional shopping experience.
                        </p>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold">Shop</h3>
                        <ul class="mt-4 space-y-2">
                            <li><Link href="/products" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">All Products</Link></li>
                            <li><Link href="/categories" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">Categories</Link></li>
                            <li><Link href="/deals" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">Deals</Link></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold">Support</h3>
                        <ul class="mt-4 space-y-2">
                            <li><Link href="/help" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">Help Center</Link></li>
                            <li><Link href="/contact" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">Contact Us</Link></li>
                            <li><Link href="/returns" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">Returns</Link></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold">Company</h3>
                        <ul class="mt-4 space-y-2">
                            <li><Link href="/about" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">About</Link></li>
                            <li><Link href="/careers" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">Careers</Link></li>
                            <li><Link href="/privacy" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">Privacy</Link></li>
                        </ul>
                    </div>
                </div>

                <div class="mt-8 border-t border-[#19140035] pt-8 dark:border-[#3E3E3A]">
                    <p class="text-center text-sm text-gray-600 dark:text-gray-400">
                        © 2024 ShopVue. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Custom styles for smooth transitions */
.group:hover img {
    transform: scale(1.05);
}
</style>
