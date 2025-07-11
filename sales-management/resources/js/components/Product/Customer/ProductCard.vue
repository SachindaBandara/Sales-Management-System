<script setup lang="ts">
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { toast } from 'vue-sonner'
import { Minus, Plus, ShoppingCart } from 'lucide-vue-next'

interface Category {
  id: number
  name: string
  slug: string
  products_count: number
}

interface Brand {
  id: number
  name: string
  slug: string
  products_count: number
}

interface Product {
  id: number
  name: string
  price: number
  first_image_url?: string // Updated to use first_image_url instead of image
  category?: Category
  brand?: Brand
  is_in_stock: boolean
}

const props = defineProps<{
  product: Product
}>()

const emit = defineEmits<{
  cartUpdated: [count: number]
}>()

const addingToCart = ref(false)
const quantity = ref(1)

// Handle add to cart action using Inertia.js
const addToCart = async () => {
  if (addingToCart.value) return

  addingToCart.value = true

  try {
    router.post(route('customer.cart.add', props.product.id), {
      quantity: quantity.value
    }, {
      onSuccess: (page) => {
        const data = page.props.flash as any
        if (data?.success) {
          toast.success('Success', {
            description: data.message,
          })
          emit('cartUpdated', data.cartCount || 0)
          quantity.value = 1 // Reset quantity
        }
      },
      onError: (errors) => {
        console.error('Cart add error:', errors)
        toast.error('Error', {
          description: 'Failed to add product to cart',
        })
      },
      onFinish: () => {
        addingToCart.value = false
      }
    })
  } catch (error) {
    console.error('Network error:', error)
    toast.error('Error', {
      description: 'Network error. Please try again.',
    })
    addingToCart.value = false
  }
}

const incrementQuantity = () => {
  if (quantity.value < 99) {
    quantity.value++
  }
}

const decrementQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}
</script>

<template>
  <div class="group border rounded-lg p-4 hover:shadow-lg transition-all duration-200 bg-white">
    <div class="relative overflow-hidden rounded-md mb-4">
      <img
        :src="product.first_image_url || '/images/placeholder.png'"
        :alt="product.name || 'Product Image'"
        class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-200"
      />
      <div v-if="!product.is_in_stock" class="absolute inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center">
        <span class="text-white font-bold text-lg">Out of Stock</span>
      </div>
    </div>

    <div class="space-y-2">
      <h4 class="font-semibold text-gray-800 line-clamp-2">{{ product.name }}</h4>
      <p class="text-gray-600 text-sm">{{ product.category?.name }}</p>
      <p class="text-gray-600 text-sm">{{ product.brand?.name }}</p>
      <p class="text-indigo-600 font-bold text-lg">LKR {{ product.price.toLocaleString() }}</p>
    </div>

    <div class="mt-4 space-y-3">
      <!-- Quantity selector -->
      <div v-if="product.is_in_stock" class="flex items-center justify-center space-x-2">
        <Button
          variant="outline"
          size="sm"
          @click="decrementQuantity"
          :disabled="quantity <= 1"
        >
          <Minus class="h-4 w-4" />
        </Button>
        <Input
          v-model.number="quantity"
          type="number"
          min="1"
          max="99"
          class="w-20 text-center"
        />
        <Button
          variant="outline"
          size="sm"
          @click="incrementQuantity"
          :disabled="quantity >= 99"
        >
          <Plus class="h-4 w-4" />
        </Button>
      </div>

      <!-- Action buttons -->
      <div class="flex space-x-2">
        <Link
          :href="route('customer.products.show', product.id)"
          class="flex-1 inline-flex items-center justify-center bg-indigo-500 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md transition-colors"
        >
          View Details
        </Link>
        <Button
          v-if="product.is_in_stock"
          :disabled="addingToCart"
          variant="default"
          class="flex-1 bg-green-500 hover:bg-green-700"
          @click="addToCart"
        >
          <ShoppingCart class="h-4 w-4 mr-2" />
          {{ addingToCart ? 'Adding...' : 'Add to Cart' }}
        </Button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
