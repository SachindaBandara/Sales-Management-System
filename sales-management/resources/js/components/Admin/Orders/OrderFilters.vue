<template>
  <Card class="mb-6">
    <CardContent class="p-6">
      <form @submit.prevent="handleSearch" class="space-y-4">
        <!-- Main Filter Layout - Responsive grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
          <!-- Search Input -->
          <div class="space-y-2">
            <Label for="search">Search Orders</Label>
            <div class="relative">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
              <Input
                id="search"
                v-model="localFilters.search"
                type="text"
                :placeholder="searchPlaceholder"
                class="pl-10"
              />
            </div>
          </div>

          <!-- Status Filter -->
          <div class="space-y-2">
            <Label for="status">Order Status</Label>
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button 
                  variant="outline" 
                  class="w-full justify-between"
                  id="status"
                >
                  <span class="truncate">
                    {{ localFilters.status ? getStatusLabel(localFilters.status) : 'All Status' }}
                  </span>
                  <ChevronDown class="ml-2 h-4 w-4 opacity-50" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="w-full min-w-[200px]" align="start">
                <DropdownMenuItem 
                  @click="selectStatus('')"
                  :class="{ 'bg-accent': !localFilters.status }"
                >
                  <Check 
                    v-if="!localFilters.status" 
                    class="mr-2 h-4 w-4" 
                  />
                  <span v-else class="mr-6"></span>
                  All Status
                </DropdownMenuItem>
                <DropdownMenuItem 
                  v-for="status in statusOptions" 
                  :key="status.value"
                  @click="selectStatus(status.value)"
                  :class="{ 'bg-accent': localFilters.status === status.value }"
                >
                  <Check 
                    v-if="localFilters.status === status.value" 
                    class="mr-2 h-4 w-4" 
                  />
                  <span v-else class="mr-6"></span>
                  <Badge 
                    :variant="getStatusVariant(status.value)"
                    class="mr-2 text-xs"
                  >
                    {{ status.label }}
                  </Badge>
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>

          <!-- Payment Status Filter -->
          <div class="space-y-2">
            <Label for="payment_status">Payment Status</Label>
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button 
                  variant="outline" 
                  class="w-full justify-between"
                  id="payment_status"
                >
                  <span class="truncate">
                    {{ localFilters.payment_status ? getPaymentStatusLabel(localFilters.payment_status) : 'All Payment Status' }}
                  </span>
                  <ChevronDown class="ml-2 h-4 w-4 opacity-50" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="w-full min-w-[200px]" align="start">
                <DropdownMenuItem 
                  @click="selectPaymentStatus('')"
                  :class="{ 'bg-accent': !localFilters.payment_status }"
                >
                  <Check 
                    v-if="!localFilters.payment_status" 
                    class="mr-2 h-4 w-4" 
                  />
                  <span v-else class="mr-6"></span>
                  All Payment Status
                </DropdownMenuItem>
                <DropdownMenuItem 
                  v-for="status in paymentStatusOptions" 
                  :key="status.value"
                  @click="selectPaymentStatus(status.value)"
                  :class="{ 'bg-accent': localFilters.payment_status === status.value }"
                >
                  <Check 
                    v-if="localFilters.payment_status === status.value" 
                    class="mr-2 h-4 w-4" 
                  />
                  <span v-else class="mr-6"></span>
                  <Badge 
                    :variant="getPaymentStatusVariant(status.value)"
                    class="mr-2 text-xs"
                  >
                    {{ status.label }}
                  </Badge>
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>

          <!-- Date From Filter -->
          <div class="space-y-2">
            <Label for="date_from">From Date</Label>
            <Input
              id="date_from"
              v-model="localFilters.date_from"
              type="date"
              class="w-full"
            />
          </div>

          <!-- Date To Filter -->
          <div class="space-y-2">
            <Label for="date_to">To Date</Label>
            <Input
              id="date_to"
              v-model="localFilters.date_to"
              type="date"
              class="w-full"
            />
          </div>

          <!-- Action Buttons -->
          <div class="space-y-2">
            <Label class="invisible">Actions</Label>
            <div class="flex gap-2 flex-col sm:flex-row">
              <Button 
                type="submit" 
                class="w-full sm:w-auto"
                size="default"
              >
                <Search class="mr-2 h-4 w-4" />
                Search
              </Button>
              <Button 
                type="button" 
                variant="outline"
                @click="handleClear"
                size="default"
                class="w-full sm:w-auto"
              >
                <X class="mr-2 h-4 w-4" />
                Clear
              </Button>
            </div>
          </div>
        </div>

        <!-- Active Filters Display -->
        <div v-if="hasActiveFilters" class="flex flex-wrap gap-2 pt-4 border-t">
          <div class="text-sm text-muted-foreground mr-2">Active filters:</div>
          <Badge 
            v-if="localFilters.search" 
            variant="secondary"
            class="flex items-center gap-1"
          >
            Search: "{{ localFilters.search }}"
            <X 
              class="h-3 w-3 cursor-pointer hover:text-destructive" 
              @click="clearFilter('search')"
            />
          </Badge>
          <Badge 
            v-if="localFilters.status" 
            variant="secondary"
            class="flex items-center gap-1"
          >
            Status: {{ getStatusLabel(localFilters.status) }}
            <X 
              class="h-3 w-3 cursor-pointer hover:text-destructive" 
              @click="clearFilter('status')"
            />
          </Badge>
          <Badge 
            v-if="localFilters.payment_status" 
            variant="secondary"
            class="flex items-center gap-1"
          >
            Payment Status: {{ getPaymentStatusLabel(localFilters.payment_status) }}
            <X 
              class="h-3 w-3 cursor-pointer hover:text-destructive" 
              @click="clearFilter('payment_status')"
            />
          </Badge>
          <Badge 
            v-if="localFilters.date_from" 
            variant="secondary"
            class="flex items-center gap-1"
          >
            From: {{ formatDate(localFilters.date_from) }}
            <X 
              class="h-3 w-3 cursor-pointer hover:text-destructive" 
              @click="clearFilter('date_from')"
            />
          </Badge>
          <Badge 
            v-if="localFilters.date_to" 
            variant="secondary"
            class="flex items-center gap-1"
          >
            To: {{ formatDate(localFilters.date_to) }}
            <X 
              class="h-3 w-3 cursor-pointer hover:text-destructive" 
              @click="clearFilter('date_to')"
            />
          </Badge>
        </div>
      </form>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger
} from '@/components/ui/dropdown-menu';
import { Search, X, ChevronDown, Check } from 'lucide-vue-next';

interface FilterOption {
  value: string;
  label: string;
}

interface Filters {
  search?: string;
  status?: string;
  payment_status?: string;
  date_from?: string;
  date_to?: string;
}

interface Props {
  filters: Filters;
  searchPlaceholder?: string;
  statusOptions?: FilterOption[];
  paymentStatusOptions?: FilterOption[];
}

interface Emits {
  (e: 'search', filters: Filters): void;
  (e: 'clear'): void;
  (e: 'filter-change', filters: Filters): void;
}

const props = withDefaults(defineProps<Props>(), {
  searchPlaceholder: 'Search orders...',
  statusOptions: () => [
    { value: 'pending', label: 'Pending' },
    { value: 'processing', label: 'Processing' },
    { value: 'shipped', label: 'Shipped' },
    { value: 'delivered', label: 'Delivered' },
    { value: 'cancelled', label: 'Cancelled' }
  ],
  paymentStatusOptions: () => [
    { value: 'pending', label: 'Pending' },
    { value: 'paid', label: 'Paid' },
    { value: 'failed', label: 'Failed' },
    { value: 'refunded', label: 'Refunded' }
  ]
});

const emit = defineEmits<Emits>();

const localFilters = ref<Filters>({
  search: props.filters.search || '',
  status: props.filters.status || '',
  payment_status: props.filters.payment_status || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || ''
});

// Computed property to check if there are active filters
const hasActiveFilters = computed(() => {
  return !!(localFilters.value.search || 
           localFilters.value.status || 
           localFilters.value.payment_status || 
           localFilters.value.date_from || 
           localFilters.value.date_to);
});

// Helper functions to get labels
const getStatusLabel = (value: string): string => {
  const status = props.statusOptions.find(s => s.value === value);
  return status?.label || value;
};

const getPaymentStatusLabel = (value: string): string => {
  const status = props.paymentStatusOptions.find(s => s.value === value);
  return status?.label || value;
};

const getStatusVariant = (value: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
  switch (value) {
    case 'delivered':
      return 'default';
    case 'cancelled':
      return 'destructive';
    case 'pending':
      return 'outline';
    case 'processing':
    case 'shipped':
      return 'secondary';
    default:
      return 'secondary';
  }
};

const getPaymentStatusVariant = (value: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
  switch (value) {
    case 'paid':
      return 'default';
    case 'failed':
      return 'destructive';
    case 'pending':
      return 'outline';
    case 'refunded':
      return 'secondary';
    default:
      return 'secondary';
  }
};

// Filter selection handlers
const selectStatus = (value: string) => {
  localFilters.value.status = value;
};

const selectPaymentStatus = (value: string) => {
  localFilters.value.payment_status = value;
};

// Format date for display in badges
const formatDate = (date: string): string => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit'
  });
};

// Watch for external filter changes
watch(() => props.filters, (newFilters) => {
  localFilters.value = {
    search: newFilters.search || '',
    status: newFilters.status || '',
    payment_status: newFilters.payment_status || '',
    date_from: newFilters.date_from || '',
    date_to: newFilters.date_to || ''
  };
}, { deep: true });

// Watch for local filter changes and emit them
watch(localFilters, (newFilters) => {
  emit('filter-change', { ...newFilters });
}, { deep: true });

const handleSearch = () => {
  emit('search', { ...localFilters.value });
};

const handleClear = () => {
  localFilters.value = {
    search: '',
    status: '',
    payment_status: '',
    date_from: '',
    date_to: ''
  };
  emit('clear');
};

const clearFilter = (filterType: keyof Filters) => {
  localFilters.value[filterType] = '';
  emit('search', { ...localFilters.value });
};
</script>


