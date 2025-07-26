<template>
  <div class="w-full">
    <!-- Table Container -->
    <div class="rounded-md border bg-white shadow-sm">
      <div class="overflow-x-auto">
        <Table>
          <TableHeader>
            <TableRow class="bg-gray-50">
              <TableHead class="font-medium w-12">
                <input
                  type="checkbox"
                  :checked="allSelected"
                  :indeterminate="someSelected"
                  @change="toggleSelectAll"
                  class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
              </TableHead>
              <TableHead 
                v-for="column in columns" 
                :key="column.key"
                class="font-medium"
              >
                {{ column.label }}
              </TableHead>
              <TableHead class="font-medium">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow 
              v-for="product in sortedProducts" 
              :key="product.id"
              class="transition-colors hover:bg-gray-50"
            >
              <!-- Checkbox Column -->
              <TableCell class="py-4">
                <input
                  type="checkbox"
                  :checked="selectedProducts.includes(product.id)"
                  @change="toggleProductSelection(product.id)"
                  class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
              </TableCell>

              <!-- Product Column -->
              <TableCell class="py-4">
                <div class="flex items-center space-x-3">
                  <div class="relative h-12 w-12 flex-shrink-0">
                    <img
                      :src="getImageUrl(product)"
                      @error="handleImageError"
                      class="h-12 w-12 rounded-lg border border-gray-200 object-cover shadow-sm"
                      :alt="product.name"
                    />
                    <div
                      v-if="product.image_count > 1"
                      class="absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center rounded-full bg-blue-500 text-xs text-white"
                    >
                      {{ product.image_count }}
                    </div>
                  </div>
                  <div class="min-w-0 flex-1">
                    <div class="text-sm font-medium text-gray-900 truncate">
                      {{ product.name }}
                    </div>
                    <div v-if="product.short_description" class="max-w-xs truncate text-sm text-gray-500">
                      {{ product.short_description }}
                    </div>
                  </div>
                </div>
              </TableCell>

              <!-- SKU Column -->
              <TableCell class="py-4">
                <div class="font-mono text-sm text-gray-900">{{ product.sku }}</div>
              </TableCell>

              <!-- Price Column -->
              <TableCell class="py-4">
                <div class="text-sm font-semibold text-gray-900">
                  {{ formatPrice(product.price) }}
                </div>
                <div v-if="product.compare_price && product.compare_price > product.price" class="text-sm text-gray-500">
                  <span class="line-through">{{ formatPrice(product.compare_price) }}</span>
                  <span class="ml-2 font-medium text-red-600"> (-{{ product.discount_percentage }}%) </span>
                </div>
              </TableCell>

              <!-- Stock Column -->
              <TableCell class="py-4">
                <div v-if="product.track_quantity" class="text-sm">
                  <span :class="getStockStatusColor(product.stock_quantity)" class="font-medium">
                    {{ product.stock_quantity }}
                  </span>
                  <span class="text-gray-500"> units</span>
                </div>
                <div v-else class="text-sm text-gray-500">Not tracked</div>
              </TableCell>

              <!-- Images Column -->
              <TableCell class="py-4">
                <div class="flex items-center space-x-2">
                  <span class="text-sm font-medium text-gray-900">
                    {{ product.image_count || 0 }}
                  </span>
                  <button
                    v-if="product.image_count > 0"
                    @click="$emit('view-images', product)"
                    class="text-sm text-blue-600 hover:text-blue-800 transition-colors"
                  >
                    View
                  </button>
                  <button
                    v-if="product.image_count > 0"
                    @click="$emit('delete-all-images', product)"
                    class="text-sm text-red-600 hover:text-red-800 transition-colors"
                  >
                    Clear
                  </button>
                </div>
              </TableCell>

              <!-- Brand Column -->
              <TableCell class="py-4 text-sm text-gray-500">
                {{ product.brand?.name ?? 'No brand' }}
              </TableCell>

              <!-- Category Column -->
              <TableCell class="py-4 text-sm text-gray-500">
                {{ product.category?.name ?? 'No category' }}
              </TableCell>

              <!-- Status Column -->
              <TableCell class="py-4">
                <button
                  @click="$emit('toggle-status', product)"
                  :class="getStatusBadgeClass(product.status)"
                  class="inline-flex rounded-full px-2 py-1 text-xs leading-5 font-semibold transition-opacity hover:opacity-80"
                >
                  {{ product.status.charAt(0).toUpperCase() + product.status.slice(1) }}
                </button>
              </TableCell>

              <!-- Created Date Column -->
              <TableCell class="py-4 text-sm text-gray-500">
                <div class="flex items-center space-x-2">
                  <Calendar class="w-4 h-4" />
                  <span>{{ formatDate(product.created_at) }}</span>
                </div>
              </TableCell>

              <!-- Actions Column -->
              <TableCell class="py-4">
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                      <MoreHorizontal class="w-4 h-4" />
                      <span class="sr-only">Open menu</span>
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" class="w-48">
                    <DropdownMenuItem @click="$emit('view', product)">
                      <Eye class="w-4 h-4 mr-2" />
                      View Details
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="$emit('edit', product)">
                      <Edit class="w-4 h-4 mr-2" />
                      Edit Product
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem @click="$emit('duplicate', product)">
                      <Copy class="w-4 h-4 mr-2" />
                      Duplicate
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="$emit('toggle-status', product)">
                      <component 
                        :is="product.status === 'active' ? EyeOff : Eye" 
                        class="w-4 h-4 mr-2" 
                      />
                      {{ product.status === 'active' ? 'Deactivate' : 'Activate' }}
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem 
                      @click="$emit('delete', product)"
                      class="text-destructive focus:text-destructive"
                    >
                      <Trash2 class="w-4 h-4 mr-2" />
                      Delete Product
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </TableCell>
            </TableRow>

            <!-- Empty State -->
            <TableRow v-if="sortedProducts.length === 0">
              <TableCell :colspan="columns.length + 2" class="h-32 text-center">
                <div class="flex flex-col items-center justify-center space-y-3">
                  <Package class="w-12 h-12 text-muted-foreground/50" />
                  <div class="space-y-1">
                    <p class="text-sm font-medium text-muted-foreground">
                      {{ emptyMessage }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                      Try adjusting your search criteria or add new products.
                    </p>
                  </div>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination" class="flex items-center justify-between px-2 py-4">
      <div class="text-sm text-muted-foreground">
        Showing {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }} products
      </div>
      
      <div class="flex items-center space-x-2">
        <Button
          variant="outline"
          size="sm"
          @click="goToPreviousPage"
          :disabled="!hasPreviousPage"
          class="h-8 px-3"
        >
          <ChevronLeft class="w-4 h-4 mr-1" />
          Previous
        </Button>
        
        <div class="flex items-center space-x-1">
          <Button
            v-for="link in paginationNumbers"
            :key="link.label"
            :variant="link.active ? 'default' : 'outline'"
            size="sm"
            @click="link.url && $emit('paginate', link.url)"
            :disabled="!link.url"
            class="h-8 w-8 p-0"
          >
            {{ link.label }}
          </Button>
        </div>
        
        <Button
          variant="outline"
          size="sm"
          @click="goToNextPage"
          :disabled="!hasNextPage"
          class="h-8 px-3"
        >
          Next
          <ChevronRight class="w-4 h-4 ml-1" />
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import {
  MoreHorizontal,
  Eye,
  EyeOff,
  Edit,
  Trash2,
  Copy,
  Calendar,
  Package,
  ChevronLeft,
  ChevronRight,
} from 'lucide-vue-next';

interface Brand {
  id: number;
  name: string;
}

interface Category {
  id: number;
  name: string;
}

interface Product {
  id: number;
  name: string;
  slug: string;
  short_description: string | null;
  sku: string;
  price: number;
  compare_price: number | null;
  stock_quantity: number;
  track_quantity: boolean;
  images: string[];
  image_urls: string[];
  first_image_url: string;
  image_count: number;
  status: string;
  brand: Brand | null;
  category: Category | null;
  created_at: string;
  discount_percentage: number;
  is_in_stock: boolean;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface Pagination {
  from: number;
  to: number;
  total: number;
  links: PaginationLink[];
}

interface TableColumn {
  key: string;
  label: string;
}

interface Props {
  products: Product[];
  selectedProducts: number[];
  pagination?: Pagination;
  columns?: TableColumn[];
  emptyMessage?: string;
  sortNewestFirst?: boolean;
}

interface Emits {
  (e: 'view', product: Product): void;
  (e: 'edit', product: Product): void;
  (e: 'duplicate', product: Product): void;
  (e: 'toggle-status', product: Product): void;
  (e: 'delete', product: Product): void;
  (e: 'view-images', product: Product): void;
  (e: 'delete-all-images', product: Product): void;
  (e: 'paginate', url: string): void;
  (e: 'toggle-select-all'): void;
  (e: 'toggle-product-selection', productId: number): void;
}

const props = withDefaults(defineProps<Props>(), {
  columns: () => [
    { key: 'product', label: 'Product' },
    { key: 'sku', label: 'SKU' },
    { key: 'price', label: 'Price' },
    { key: 'stock', label: 'Stock' },
    { key: 'images', label: 'Images' },
    { key: 'brand', label: 'Brand' },
    { key: 'category', label: 'Category' },
    { key: 'status', label: 'Status' },
    { key: 'created_at', label: 'Created' }
  ],
  emptyMessage: 'No products found',
  sortNewestFirst: true
});

const emit = defineEmits<Emits>();

const sortedProducts = computed(() => {
  if (!props.sortNewestFirst) return props.products;
  
  return [...props.products].sort((a, b) => {
    return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
  });
});

const allSelected = computed(() => {
  return sortedProducts.value.length > 0 && props.selectedProducts.length === sortedProducts.value.length;
});

const someSelected = computed(() => {
  return props.selectedProducts.length > 0 && props.selectedProducts.length < sortedProducts.value.length;
});

const paginationNumbers = computed(() => {
  if (!props.pagination) return [];
  
  return props.pagination.links.filter(link => {
    const label = link.label.replace(/(<([^>]+)>)/gi, '');
    return label !== 'Previous' && label !== 'Next' && !isNaN(Number(label));
  });
});

const hasPreviousPage = computed(() => {
  if (!props.pagination) return false;
  const prevLink = props.pagination.links.find(link => 
    link.label.includes('Previous')
  );
  return prevLink?.url !== null;
});

const hasNextPage = computed(() => {
  if (!props.pagination) return false;
  const nextLink = props.pagination.links.find(link => 
    link.label.includes('Next')
  );
  return nextLink?.url !== null;
});

const toggleSelectAll = () => {
  emit('toggle-select-all');
};

const toggleProductSelection = (productId: number) => {
  emit('toggle-product-selection', productId);
};

const getImageUrl = (product: Product): string => {
  // First try to get from image_urls array
  if (product.image_urls && product.image_urls.length > 0) {
    return product.image_urls[0];
  }

  // Then try first_image_url
  if (product.first_image_url) {
    return product.first_image_url;
  }

  // Finally try images array (fallback)
  if (product.images && product.images.length > 0) {
    return `/storage/${product.images[0]}`;
  }

  // Return placeholder if no image
  return '/images/placeholder.jpg';
};

const handleImageError = (event: Event) => {
  const target = event.target as HTMLImageElement;
  target.src = '/images/placeholder.jpg';
};

const getStockStatusColor = (quantity: number): string => {
  if (quantity <= 5) return 'text-red-600';
  if (quantity <= 20) return 'text-yellow-600';
  return 'text-green-600';
};

const getStatusBadgeClass = (status: string): string => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    draft: 'bg-yellow-100 text-yellow-800',
    archived: 'bg-red-100 text-red-800',
  };
  return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800';
};

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatPrice = (price: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'LKR',
    minimumFractionDigits: 2,
  }).format(price);
};

const goToPreviousPage = () => {
  if (!props.pagination) return;
  const prevLink = props.pagination.links.find(link => 
    link.label.includes('Previous')
  );
  if (prevLink?.url) {
    emit('paginate', prevLink.url);
  }
};

const goToNextPage = () => {
  if (!props.pagination) return;
  const nextLink = props.pagination.links.find(link => 
    link.label.includes('Next')
  );
  if (nextLink?.url) {
    emit('paginate', nextLink.url);
  }
};
</script>

<style scoped>
.transition-colors {
  transition: color 0.2s ease-in-out;
}

.transition-opacity {
  transition: opacity 0.2s ease-in-out;
}
</style>