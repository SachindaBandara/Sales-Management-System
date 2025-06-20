<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import Header from '@/components/header/Header.vue'
import ProductList from '@/components/product/ProductList.vue'
import ProductDetail from '@/components/product/ProductDetail.vue'
import Cart from '@/components/product/Cart.vue'

// Define interfaces
interface Product {
  id: number
  name: string
  description: string
  price: number
  rating: number
  reviews: number
  image: string
}

interface CartItem extends Product {
  quantity: number
}

// Access Inertia page props
const page = usePage()
const user = computed(() => page.props.auth.user)

// Reactive state
const currentView = ref('products')
const searchQuery = ref('')
const sortBy = ref('name')
const selectedProduct = ref<Product | null>(null)
const cart = ref<CartItem[]>([])
const toast = ref({
  show: false,
  message: '',
  type: 'success'
})

// Sample products data
const products = ref<Product[]>([
  {
    id: 1,
    name: 'Wireless Headphones',
    description: 'High-quality wireless headphones with noise cancellation',
    price: 199.99,
    rating: 4.5,
    reviews: 128,
    image: '/placeholder.svg?height=300&width=300'
  },
  {
    id: 2,
    name: 'Smart Watch',
    description: 'Feature-rich smartwatch with health tracking',
    price: 299.99,
    rating: 4.3,
    reviews: 89,
    image: '/placeholder.svg?height=300&width=300'
  },
  {
    id: 3,
    name: 'Laptop Stand',
    description: 'Ergonomic aluminum laptop stand for better posture',
    price: 79.99,
    rating: 4.7,
    reviews: 156,
    image: '/placeholder.svg?height=300&width=300'
  },
  {
    id: 4,
    name: 'Bluetooth Speaker',
    description: 'Portable Bluetooth speaker with premium sound quality',
    price: 129.99,
    rating: 4.4,
    reviews: 203,
    image: '/placeholder.svg?height=300&width=300'
  },
  {
    id: 5,
    name: 'USB-C Hub',
    description: 'Multi-port USB-C hub with HDMI and charging support',
    price: 59.99,
    rating: 4.2,
    reviews: 94,
    image: '/placeholder.svg?height=300&width=300'
  },
  {
    id: 6,
    name: 'Wireless Mouse',
    description: 'Precision wireless mouse with ergonomic design',
    price: 49.99,
    rating: 4.6,
    reviews: 167,
    image: '/placeholder.svg?height=300&width=300'
  }
])

// Computed properties
const filteredProducts = computed(() => {
  let filtered = products.value
  if (searchQuery.value) {
    filtered = filtered.filter(product =>
      product.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      product.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }
  return filtered.sort((a, b) => {
    switch (sortBy.value) {
      case 'price':
        return a.price - b.price
      case 'rating':
        return b.rating - a.rating
      default:
        return a.name.localeCompare(b.name)
    }
  })
})

const cartItemsCount = computed(() => {
  return cart.value.reduce((total, item) => total + item.quantity, 0)
})

const cartTotal = computed(() => {
  return cart.value.reduce((total, item) => total + (item.price * item.quantity), 0)
})

// Methods
const selectProduct = (product: Product) => {
  selectedProduct.value = product
  currentView.value = 'product-detail'
}

const addToCart = (product: Product, quantity: number = 1) => {
  const existingItem = cart.value.find(item => item.id === product.id)
  if (existingItem) {
    existingItem.quantity += quantity
  } else {
    cart.value.push({ ...product, quantity })
  }
  showToast('Product added to cart!', 'success')
}

const updateQuantity = (productId: number, newQuantity: number) => {
  if (newQuantity <= 0) {
    removeFromCart(productId)
    return
  }
  const item = cart.value.find(item => item.id === productId)
  if (item) {
    item.quantity = newQuantity
  }
}

const removeFromCart = (productId: number) => {
  cart.value = cart.value.filter(item => item.id !== productId)
  showToast('Product removed from cart', 'success')
}

const proceedToCheckout = () => {
  if (!user.value) {
    showToast('Please log in to proceed to checkout', 'error')
    router.get(route('login'))
    return
  }
  currentView.value = 'checkout'
}

const showToast = (message: string, type: string = 'success') => {
  toast.value = { show: true, message, type }
  setTimeout(() => {
    toast.value.show = false
  }, 3000)
}

const showProducts = () => {
  currentView.value = 'products'
}
</script>

<template>
  <Head title="Welcome">
    <link rel="preconnect" href="https://rsms.me/" />
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
  </Head>
  <div class="flex min-h-screen flex-col bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]">
    <Header
      v-model:searchQuery="searchQuery"
      :cartItemsCount="cartItemsCount"
      :currentView="currentView"
      @update:currentView="currentView = $event"
    />
    <main class="container mx-auto px-4 py-8">
      <ProductList
        v-if="currentView === 'products'"
        :products="filteredProducts"
        v-model:sortBy="sortBy"
        @selectProduct="selectProduct"
        @addToCart="addToCart"
      />
      <ProductDetail
        v-if="currentView === 'product-detail' && selectedProduct"
        :product="selectedProduct"
        @back="showProducts"
        @addToCart="addToCart"
      />
      <Cart
        v-if="currentView === 'cart'"
        :cart="cart"
        :cartTotal="cartTotal"
        @updateQuantity="updateQuantity"
        @removeFromCart="removeFromCart"
        @proceedToCheckout="proceedToCheckout"
        @continueShopping="showProducts"
      />
      <div v-if="toast.show" class="fixed bottom-4 right-4 z-50">
        <div class="rounded-lg border border-[#19140035] bg-[#FDFDFC] p-4 shadow-lg dark:border-[#3E3E3A] dark:bg-[#0a0a0a]">
          <div class="flex items-center space-x-2">
            <CheckCircle v-if="toast.type === 'success'" class="h-4 w-4 text-green-500" />
            <AlertCircle v-if="toast.type === 'error'" class="h-4 w-4 text-red-500" />
            <span class="text-sm">{{ toast.message }}</span>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>
