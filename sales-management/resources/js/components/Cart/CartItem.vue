<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Trash2, Minus, Plus } from 'lucide-vue-next'

interface CartItem {
  name: string
  quantity: number
  price: number
  image: string
  product_id: number
}

interface Props {
  id: string
  item: CartItem
  updating: boolean
  disabled: boolean
}

interface Emits {
  (e: 'update-quantity', id: string, quantity: number): void
  (e: 'remove-item', id: string): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const incrementQuantity = () => {
  if (props.item.quantity < 99) {
    emit('update-quantity', props.id, props.item.quantity + 1)
  }
}

const decrementQuantity = () => {
  if (props.item.quantity > 1) {
    emit('update-quantity', props.id, props.item.quantity - 1)
  }
}

const onQuantityChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  const newQuantity = parseInt(target.value)

  if (newQuantity && newQuantity >= 1 && newQuantity <= 99) {
    emit('update-quantity', props.id, newQuantity)
  }
}

const handleRemove = () => {
  emit('remove-item', props.id)
}
</script>

<template>
  <div class="flex items-center gap-4 p-4 border rounded-lg hover:shadow-sm transition-shadow">
    <!-- Product Image -->
    <div class="flex-shrink-0">
      <img
        :src="item.image || '/images/placeholder.png'"
        :alt="item.name"
        class="w-20 h-20 object-cover rounded-md"
      />
    </div>

    <!-- Product Details -->
    <div class="flex-1 min-w-0">
      <h3 class="font-semibold text-gray-900 truncate">{{ item.name }}</h3>
      <p class="text-indigo-600 font-medium">LKR {{ item.price.toLocaleString() }}</p>
      <p class="text-sm text-gray-500">
        Subtotal: LKR {{ (item.price * item.quantity).toLocaleString() }}
      </p>
    </div>

    <!-- Quantity Controls -->
    <div class="flex items-center space-x-2">
      <Button
        variant="outline"
        size="sm"
        :disabled="item.quantity <= 1 || updating"
        @click="decrementQuantity"
      >
        <Minus class="h-4 w-4" />
      </Button>
      <Input
        :value="item.quantity"
        type="number"
        min="1"
        max="99"
        class="w-20 text-center"
        :disabled="updating"
        @change="onQuantityChange"
      />
      <Button
        variant="outline"
        size="sm"
        :disabled="item.quantity >= 99 || updating"
        @click="incrementQuantity"
      >
        <Plus class="h-4 w-4" />
      </Button>
    </div>

    <!-- Remove Button -->
    <Button
      variant="ghost"
      size="sm"
      :disabled="disabled"
      @click="handleRemove"
      class="text-red-600 hover:text-red-700 hover:bg-red-50"
    >
      <Trash2 class="h-4 w-4" />
    </Button>
  </div>
</template>
