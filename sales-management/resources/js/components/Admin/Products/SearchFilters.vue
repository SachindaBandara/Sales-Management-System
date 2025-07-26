<template>
  <Card class="mb-6">
    <CardContent class="p-6">
      <form @submit.prevent="handleSearch" class="space-y-4">
        <!-- Main Filter Layout -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
          
          <!-- Search Input -->
          <div class="space-y-2">
            <Label for="search">Search</Label>
            <div class="relative">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
              <Input
                id="search"
                v-model="localFilters.search"
                type="text"
                placeholder="Search by name or SKU..."
                class="pl-10"
              />
            </div>
          </div>

          <!-- Status Filter -->
          <div class="space-y-2">
            <Label for="status">Status</Label>
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
                  <div class="flex items-center">
                    <Badge 
                      :variant="getStatusVariant(status.value)"
                      class="mr-2 text-xs"
                    >
                      {{ status.label }}
                    </Badge>
                  </div>
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>

          <!-- Brand Filter -->
          <div class="space-y-2">
            <Label for="brand">Brand</Label>
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button 
                  variant="outline" 
                  class="w-full justify-between"
                  id="brand"
                >
                  <span class="truncate">
                    {{ localFilters.brand_id ? getBrandLabel(localFilters.brand_id) : 'All Brands' }}
                  </span>
                  <ChevronDown class="ml-2 h-4 w-4 opacity-50" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="w-full min-w-[200px]" align="start">
                <DropdownMenuItem 
                  @click="selectBrand('')"
                  :class="{ 'bg-accent': !localFilters.brand_id }"
                >
                  <Check 
                    v-if="!localFilters.brand_id" 
                    class="mr-2 h-4 w-4" 
                  />
                  <span v-else class="mr-6"></span>
                  All Brands
                </DropdownMenuItem>
                <DropdownMenuItem 
                  v-for="brand in brands" 
                  :key="brand.id"
                  @click="selectBrand(brand.id.toString())"
                  :class="{ 'bg-accent': localFilters.brand_id === brand.id.toString() }"
                >
                  <Check 
                    v-if="localFilters.brand_id === brand.id.toString()" 
                    class="mr-2 h-4 w-4" 
                  />
                  <span v-else class="mr-6"></span>
                  {{ brand.name }}
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>

          <!-- Category Filter -->
          <div class="space-y-2">
            <Label for="category">Category</Label>
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button 
                  variant="outline" 
                  class="w-full justify-between"
                  id="category"
                >
                  <span class="truncate">
                    {{ localFilters.category_id ? getCategoryLabel(localFilters.category_id) : 'All Categories' }}
                  </span>
                  <ChevronDown class="ml-2 h-4 w-4 opacity-50" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="w-full min-w-[200px]" align="start">
                <DropdownMenuItem 
                  @click="selectCategory('')"
                  :class="{ 'bg-accent': !localFilters.category_id }"
                >
                  <Check 
                    v-if="!localFilters.category_id" 
                    class="mr-2 h-4 w-4" 
                  />
                  <span v-else class="mr-6"></span>
                  All Categories
                </DropdownMenuItem>
                <DropdownMenuItem 
                  v-for="category in categories" 
                  :key="category.id"
                  @click="selectCategory(category.id.toString())"
                  :class="{ 'bg-accent': localFilters.category_id === category.id.toString() }"
                >
                  <Check 
                    v-if="localFilters.category_id === category.id.toString()" 
                    class="mr-2 h-4 w-4" 
                  />
                  <span v-else class="mr-6"></span>
                  {{ category.name }}
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
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
            v-if="localFilters.brand_id" 
            variant="secondary"
            class="flex items-center gap-1"
          >
            Brand: {{ getBrandLabel(localFilters.brand_id) }}
            <X 
              class="h-3 w-3 cursor-pointer hover:text-destructive" 
              @click="clearFilter('brand_id')"
            />
          </Badge>
          <Badge 
            v-if="localFilters.category_id" 
            variant="secondary"
            class="flex items-center gap-1"
          >
            Category: {{ getCategoryLabel(localFilters.category_id) }}
            <X 
              class="h-3 w-3 cursor-pointer hover:text-destructive" 
              @click="clearFilter('category_id')"
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

interface Brand {
  id: number;
  name: string;
}

interface Category {
  id: number;
  name: string;
}

interface Filters {
  search?: string;
  status?: string;
  brand_id?: string;
  category_id?: string;
}

interface Props {
  filters: Filters;
  brands: Brand[];
  categories: Category[];
}

interface Emits {
  (e: 'search', filters: Filters): void;
  (e: 'clear'): void;
  (e: 'filter-change', filters: Filters): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const statusOptions = [
  { value: 'active', label: 'Active' },
  { value: 'draft', label: 'Draft' },
  { value: 'archived', label: 'Archived' }
];

const localFilters = ref<Filters>({
  search: props.filters.search || '',
  status: props.filters.status || '',
  brand_id: props.filters.brand_id || '',
  category_id: props.filters.category_id || ''
});

// Computed property to check if there are active filters
const hasActiveFilters = computed(() => {
  return !!(
    localFilters.value.search || 
    localFilters.value.status || 
    localFilters.value.brand_id || 
    localFilters.value.category_id
  );
});

// Helper functions to get labels
const getStatusLabel = (value: string): string => {
  const status = statusOptions.find(s => s.value === value);
  return status?.label || value;
};

const getBrandLabel = (value: string): string => {
  const brand = props.brands.find(b => b.id.toString() === value);
  return brand?.name || value;
};

const getCategoryLabel = (value: string): string => {
  const category = props.categories.find(c => c.id.toString() === value);
  return category?.name || value;
};

const getStatusVariant = (value: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
  switch (value) {
    case 'active':
      return 'default';
    case 'draft':
      return 'outline';
    case 'archived':
      return 'destructive';
    default:
      return 'secondary';
  }
};

// Filter selection handlers
const selectStatus = (value: string) => {
  localFilters.value.status = value;
};

const selectBrand = (value: string) => {
  localFilters.value.brand_id = value;
};

const selectCategory = (value: string) => {
  localFilters.value.category_id = value;
};

// Watch for external filter changes
watch(() => props.filters, (newFilters) => {
  localFilters.value = {
    search: newFilters.search || '',
    status: newFilters.status || '',
    brand_id: newFilters.brand_id || '',
    category_id: newFilters.category_id || ''
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
    brand_id: '',
    category_id: ''
  };
  emit('clear');
};

const clearFilter = (filterType: keyof Filters) => {
  localFilters.value[filterType] = '';
  emit('search', { ...localFilters.value });
};
</script>

