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
              <SelectItem value="pending">Pending</SelectItem>
              <SelectItem value="processing">Processing</SelectItem>
              <SelectItem value="shipped">Shipped</SelectItem>
              <SelectItem value="delivered">Delivered</SelectItem>
              <SelectItem value="cancelled">Cancelled</SelectItem>
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
              <SelectItem value="pending">Pending</SelectItem>
              <SelectItem value="paid">Paid</SelectItem>
              <SelectItem value="failed">Failed</SelectItem>
              <SelectItem value="refunded">Refunded</SelectItem>
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
        <Button @click="handleClearFilters" variant="outline" size="sm">
          Clear Filters
        </Button>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { reactive, watch } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Button } from '@/components/ui/button'

interface Filters {
  status?: string
  payment_status?: string
  search?: string
  date_from?: string
  date_to?: string
}

const props = defineProps<{
  filters: Filters
}>()

const emit = defineEmits<{
  (e: 'update:filters', filters: Filters): void
  (e: 'apply-filters'): void
  (e: 'clear-filters'): void
}>()

// Create a local reactive copy of filters to avoid mutating props
const localFilters = reactive<Filters>({ ...props.filters })

// Sync localFilters with props.filters when props.filters changes
watch(() => props.filters, (newFilters) => {
  Object.assign(localFilters, newFilters)
}, { deep: true })

// Emit updates to parent when localFilters change
const handleInput = () => {
  emit('update:filters', { ...localFilters })
  emit('apply-filters')
}

// Handle clear filters action
const handleClearFilters = () => {
  Object.keys(localFilters).forEach((key) => {
    localFilters[key as keyof Filters] = ''
  })
  emit('update:filters', { ...localFilters })
  emit('clear-filters')
}
</script>