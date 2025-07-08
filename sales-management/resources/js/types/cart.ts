// Cart item interface
export interface CartItem {
  name: string
  quantity: number
  price: number
  image: string
  product_id: number
}

// Cart totals interface
export interface CartTotals {
  subtotal: number
  tax: number
  total: number
  taxRate: number
}

// Cart props interface for main component
export interface CartProps {
  cart?: Record<string, CartItem> | null
  cartTotals?: CartTotals
  cartCount?: number
  success?: string
}

// Cart state interface
export interface CartState {
  cart: Record<string, CartItem>
  cartTotals: CartTotals
  loading: boolean
  updatingItems: Set<string>
}

// Cart actions interface
export interface CartActions {
  updateQuantity: (id: string, quantity: number) => Promise<void>
  removeItem: (id: string) => Promise<void>
  clearCart: () => Promise<void>
  incrementQuantity: (id: string) => void
  decrementQuantity: (id: string) => void
}
