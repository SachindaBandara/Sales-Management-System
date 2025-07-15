<template>
  <Card class="mb-6">
    <CardContent class="p-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div>
          <Label for="search">Search</Label>
          <Input
            id="search"
            v-model="localFilters.search"
            placeholder="Order number or customer..."
            @input="handleInput"
          />
        </div>

        <div>
          <Label for="status">Status</Label>
          <Select v-model="localFilters.status" @update:modelValue="handleInput">
            <SelectTrigger>
              <SelectValue placeholder="All statuses" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">All statuses</SelectItem>
              <SelectItem
                v-for="status in orderStatuses"
                :key="status.value"
                :value="status.value"
              >
                <div class="flex items-center gap-2">
                  <div
                    class="w-2 h-2 rounded-full"
                    :class="status.color"
                  ></div>
                  {{ status.label }}
                </div>
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div>
          <Label for="payment_status">Payment Status</Label>
          <Select v-model="localFilters.payment_status" @update:modelValue="handleInput">
            <SelectTrigger>
              <SelectValue placeholder="All payment statuses" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">All payment statuses</SelectItem>
              <SelectItem
                v-for="paymentStatus in paymentStatuses"
                :key="paymentStatus.value"
                :value="paymentStatus.value"
              >
                <div class="flex items-center gap-2">
                  <div
                    class="w-2 h-2 rounded-full"
                    :class="paymentStatus.color"
                  ></div>
                  {{ paymentStatus.label }}
                </div>
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div>
          <Label for="date_from">Date From</Label>
          <Input
            id="date_from"
            v-model="localFilters.date_from"
            type="date"
            @change="handleInput"
          />
        </div>

        <div>
          <Label for="date_to">Date To</Label>
          <Input
            id="date_to"
            v-model="localFilters.date_to"
            type="date"
            @change="handleInput"
          />
        </div>
      </div>

      <div class="mt-4 flex gap-2">
        <Button
          @click="handleClearFilters"
          variant="outline"
          size="sm"
          :disabled="!hasActiveFilters"
          class="gap-2"
        >
          <X class="w-4 h-4" />
          Clear Filters
        </Button>

        <div class="flex items-center text-sm text-gray-500">
          <span v-if="hasActiveFilters">
            {{ activeFiltersCount }} filter{{ activeFiltersCount > 1 ? 's' : '' }} active
          </span>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { reactive, watch, computed } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Button } from '@/components/ui/button'
import { X } from 'lucide-vue-next'
import { Filters } from '@/types/order'

const props = defineProps<{
  filters: Filters
}>()

const emit = defineEmits<{
  (e: 'update:filters', filters: Filters): void
  (e: 'apply-filters'): void
  (e: 'clear-filters'): void
}>()

const localFilters = reactive<Filters>({ ...props.filters })

// Order status options with visual indicators
const orderStatuses = [
  { value: 'pending', label: 'Pending', color: 'bg-yellow-500' },
  { value: 'confirmed', label: 'Confirmed', color: 'bg-blue-500' },
  { value: 'processing', label: 'Processing', color: 'bg-purple-500' },
  { value: 'shipped', label: 'Shipped', color: 'bg-indigo-500' },
  { value: 'out_for_delivery', label: 'Out for Delivery', color: 'bg-orange-500' },
  { value: 'delivered', label: 'Delivered', color: 'bg-green-500' },
  { value: 'cancelled', label: 'Cancelled', color: 'bg-red-500' },
  { value: 'returned', label: 'Returned', color: 'bg-gray-500' },
  { value: 'refunded', label: 'Refunded', color: 'bg-pink-500' }
]

// Payment status options with visual indicators
const paymentStatuses = [
  { value: 'pending', label: 'Pending', color: 'bg-yellow-500' },
  { value: 'processing', label: 'Processing', color: 'bg-blue-500' },
  { value: 'paid', label: 'Paid', color: 'bg-green-500' },
  { value: 'failed', label: 'Failed', color: 'bg-red-500' },
  { value: 'cancelled', label: 'Cancelled', color: 'bg-gray-500' },
  { value: 'refunded', label: 'Refunded', color: 'bg-pink-500' },
  { value: 'partial_refund', label: 'Partial Refund', color: 'bg-orange-500' },
  { value: 'chargeback', label: 'Chargeback', color: 'bg-red-600' }
]

// Computed property to check if any filters are active
const hasActiveFilters = computed(() => {
  return Object.values(localFilters).some(value => value !== '' && value !== null && value !== undefined)
})

// Computed property to count active filters
const activeFiltersCount = computed(() => {
  return Object.values(localFilters).filter(value => value !== '' && value !== null && value !== undefined).length
})

watch(() => props.filters, (newFilters) => {
  Object.assign(localFilters, newFilters)
}, { deep: true })

const handleInput = () => {
  emit('update:filters', { ...localFilters })
  emit('apply-filters')
}

const handleClearFilters = () => {
  Object.keys(localFilters).forEach((key) => {
    localFilters[key as keyof Filters] = ''
  })
  emit('update:filters', { ...localFilters })
  emit('clear-filters')
}
</script>
