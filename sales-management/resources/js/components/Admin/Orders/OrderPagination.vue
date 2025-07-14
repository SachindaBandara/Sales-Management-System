<template>
  <div class="mt-6 flex justify-between items-center">
    <div class="text-sm text-gray-600">
      Showing {{ orders.from }} to {{ orders.to }} of {{ orders.total }} orders
    </div>
    <div class="flex gap-2">
      <Button
        @click="$emit('change-page', orders.current_page - 1)"
        :disabled="!orders.prev_page_url"
        variant="outline"
        size="sm"
      >
        Previous
      </Button>
      <Button
        @click="$emit('change-page', orders.current_page + 1)"
        :disabled="!orders.next_page_url"
        variant="outline"
        size="sm"
      >
        Next
      </Button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { OrdersPagination } from '@/types/order'

defineProps<{
  orders: OrdersPagination
}>()

defineEmits<{
  (e: 'change-page', page: number): void
}>()
</script>