import { ref, computed } from 'vue'
import { toast } from 'vue-sonner'

interface CartItem {
  name: string
  quantity: number
  price: number
  image: string
  product_id: number
}

interface CartTotals {
  subtotal: number
  tax: number
  total: number
  taxRate: number
}

// Global cart state
const cartCount = ref(0)
const cartItems = ref<Record<string, CartItem>>({})
const cartTotals = ref<CartTotals>({
  subtotal: 0,
  tax: 0,
  total: 0,
  taxRate: 0
})

export const useCart = () => {
  const loading = ref(false)

  // Computed properties
  const isEmpty = computed(() => Object.keys(cartItems.value).length === 0)
  const itemCount = computed(() => Object.keys(cartItems.value).length)
  const totalItems = computed(() =>
    Object.values(cartItems.value).reduce((sum, item) => sum + item.quantity, 0)
  )

  // Get CSRF token
  const getCSRFToken = () => {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
  }

  // Add item to cart
  const addToCart = async (productId: number, quantity: number = 1) => {
    loading.value = true

    try {
      const response = await fetch(route('customer.cart.add', productId), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': getCSRFToken(),
        },
        body: JSON.stringify({ quantity })
      })

      const data = await response.json()

      if (data.success) {
        cartCount.value = data.cartCount
        cartTotals.value = data.cartTotals
        toast.success('Success', {
          description: data.message,
        })
        await refreshCart()
        return true
      } else {
        toast.error('Error', {
          description: data.message || 'Failed to add product to cart',
        })
        return false
      }
    } catch {
      toast.error('Error', {
        description: 'Network error. Please try again.',
      })
      return false
    } finally {
      loading.value = false
    }
  }

  // Update cart item quantity
  const updateQuantity = async (id: string, quantity: number) => {
    if (quantity < 1 || quantity > 99) return false

    loading.value = true

    try {
      const response = await fetch(route('customer.cart.update'), {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': getCSRFToken(),
        },
        body: JSON.stringify({ id, quantity })
      })

      const data = await response.json()

      if (data.success) {
        if (cartItems.value[id]) {
          cartItems.value[id].quantity = quantity
        }
        cartTotals.value = data.cartTotals
        cartCount.value = data.cartCount
        toast.success('Cart updated', {
          description: data.message,
        })
        return true
      } else {
        toast.error('Error', {
          description: data.message || 'Failed to update cart',
        })
        return false
      }
    } catch {
      toast.error('Error', {
        description: 'Network error. Please try again.',
      })
      return false
    } finally {
      loading.value = false
    }
  }

  // Remove item from cart
  const removeFromCart = async (id: string) => {
    loading.value = true

    try {
      const response = await fetch(route('customer.cart.remove'), {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': getCSRFToken(),
        },
        body: JSON.stringify({ id })
      })

      const data = await response.json()

      if (data.success) {
        delete cartItems.value[id]
        cartTotals.value = data.cartTotals
        cartCount.value = data.cartCount
        toast.success('Item removed', {
          description: data.message,
        })
        return true
      } else {
        toast.error('Error', {
          description: data.message || 'Failed to remove item',
        })
        return false
      }
    } catch {
      toast.error('Error', {
        description: 'Network error. Please try again.',
      })
      return false
    } finally {
      loading.value = false
    }
  }

  // Clear entire cart
  const clearCart = async () => {
    loading.value = true

    try {
      const response = await fetch(route('customer.cart.clear'), {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': getCSRFToken(),
        },
      })

      const data = await response.json()

      if (data.success) {
        cartItems.value = {}
        cartTotals.value = data.cartTotals
        cartCount.value = data.cartCount
        toast.success('Cart cleared', {
          description: data.message,
        })
        return true
      } else {
        toast.error('Error', {
          description: data.message || 'Failed to clear cart',
        })
        return false
      }
    } catch {
      toast.error('Error', {
        description: 'Network error. Please try again.',
      })
      return false
    } finally {
      loading.value = false
    }
  }

  // Refresh cart data
  const refreshCart = async () => {
    try {
      const response = await fetch(route('customer.cart.count'))
      const data = await response.json()
      cartCount.value = data.cartCount
    } catch (error) {
      console.error('Failed to refresh cart:', error)
    }
  }

  // Initialize cart count on page load
  const initializeCart = async () => {
    await refreshCart()
  }

  return {
    // State
    cartCount: computed(() => cartCount.value),
    cartItems: computed(() => cartItems.value),
    cartTotals: computed(() => cartTotals.value),
    loading: computed(() => loading.value),

    // Computed
    isEmpty,
    itemCount,
    totalItems,

    // Methods
    addToCart,
    updateQuantity, // Renamed from updateCartItem
    removeFromCart,
    clearCart,
    refreshCart,
    initializeCart,
  }
}
