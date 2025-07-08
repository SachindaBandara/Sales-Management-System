<script setup lang="ts">
import { computed } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import CartItem from '@/components/Cart/CartItem.vue'

interface CartItem {
  name: string
  quantity: number
  price: number
  image: string
  product_id: number
}

interface Props {
  cart: Record<string, CartItem>
  updatingItems: Set<string>
  loading: boolean
}

interface Emits {
  (e: 'update-quantity', id: string, quantity: number): void
  (e: 'remove-item', id: string): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const totalItems = computed(() =>
  Object.values(props.cart).reduce((sum, item) => sum + item.quantity, 0)
)

const handleUpdateQuantity = (id: string, quantity: number) => {
  emit('update-quantity', id, quantity)
}

const handleRemoveItem = (id: string) => {
  emit('remove-item', id)
}
</script>

<template>
  <div class="lg:col-span-2">
    <Card>
      <CardHeader>
        <CardTitle class="flex items-center justify-between">
          <span>Cart Items</span>
          <Badge>{{ totalItems }} {{ totalItems === 1 ? 'item' : 'items' }}</Badge>
        </CardTitle>
      </CardHeader>
      <CardContent class="space-y-4">
        <CartItem
          v-for="(item, id) in cart"
          :key="id"
          :id="String(id)"
          :item="item"
          :updating="updatingItems.has(String(id))"
          :disabled="loading"
          @update-quantity="handleUpdateQuantity"
          @remove-item="handleRemoveItem"
        />
      </CardContent>
    </Card>
  </div>
</template>
