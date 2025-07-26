<template>
  <div class="w-full">
    <!-- Table Container -->
    <div class="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead class="font-medium">Category</TableHead>
            <TableHead class="font-medium">Slug</TableHead>
            <TableHead class="font-medium">Parent</TableHead>
            <TableHead class="font-medium">Status</TableHead>
            <TableHead class="font-medium">Created</TableHead>
            <TableHead class="font-medium">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow
            v-for="category in sortedCategories"
            :key="category.id"
            class="transition-colors hover:bg-muted/50"
          >
            <!-- Category Column -->
            <TableCell class="py-4">
              <div class="flex items-center space-x-3">
                <Avatar class="h-10 w-10">
                  <img
                    v-if="category.image"
                    :src="category.image"
                    class="h-10 w-10 rounded-full object-cover"
                    alt="Category image"
                  />
                  <AvatarFallback class="bg-blue-100 text-blue-600 font-semibold">
                    {{ category.name.charAt(0).toUpperCase() }}
                  </AvatarFallback>
                </Avatar>
                <div class="min-w-0 flex-1">
                  <div class="text-sm font-medium text-foreground truncate">
                    {{ category.name }}
                  </div>
                  <p
                    v-if="category.description"
                    class="text-xs text-muted-foreground truncate max-w-xs"
                  >
                    {{ category.description }}
                  </p>
                </div>
              </div>
            </TableCell>

            <!-- Slug Column -->
            <TableCell class="text-sm text-muted-foreground">
              {{ category.slug }}
            </TableCell>

            <!-- Parent Column -->
            <TableCell class="text-sm text-muted-foreground">
              {{ category.parent ? category.parent.name : '-' }}
            </TableCell>

            <!-- Status Column -->
            <TableCell>
              <div class="flex items-center space-x-2">
                <div
                  :class="getStatusIndicatorClass(category.is_active)"
                  class="w-2 h-2 rounded-full"
                ></div>
                <Badge :variant="getStatusBadgeVariant(category.is_active)">
                  {{ category.is_active ? 'Active' : 'Inactive' }}
                </Badge>
              </div>
            </TableCell>

            <!-- Created At Column -->
            <TableCell class="text-muted-foreground">
              <div class="flex items-center space-x-2">
                <Calendar class="w-4 h-4" />
                <span class="text-sm">{{ formatDate(category.created_at) }}</span>
              </div>
            </TableCell>

            <!-- Actions Column -->
            <TableCell>
              <DropdownMenu>
                <DropdownMenuTrigger asChild>
                  <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                    <MoreHorizontal class="w-4 h-4" />
                    <span class="sr-only">Open menu</span>
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-48">
                  <DropdownMenuItem @click="$emit('view', category)">
                    <Eye class="w-4 h-4 mr-2" />
                    View Details
                  </DropdownMenuItem>
                  <DropdownMenuItem @click="$emit('edit', category)">
                    <Edit class="w-4 h-4 mr-2" />
                    Edit Category
                  </DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem @click="$emit('toggle-status', category)">
                    <component
                      :is="category.is_active ? XCircle : CheckCircle"
                      class="w-4 h-4 mr-2"
                    />
                    {{ category.is_active ? 'Deactivate' : 'Activate' }}
                  </DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem
                    @click="$emit('delete', category)"
                    class="text-destructive focus:text-destructive"
                  >
                    <Trash2 class="w-4 h-4 mr-2" />
                    Delete Category
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </TableCell>
          </TableRow>

          <!-- Empty State -->
          <TableRow v-if="sortedCategories.length === 0">
            <TableCell :colspan="6" class="h-32 text-center">
              <div class="flex flex-col items-center justify-center space-y-3">
                <Box class="w-12 h-12 text-muted-foreground/50" />
                <div class="space-y-1">
                  <p class="text-sm font-medium text-muted-foreground">
                    No categories found
                  </p>
                  <p class="text-xs text-muted-foreground">
                    Try adjusting your search criteria or add new categories.
                  </p>
                </div>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <!-- Pagination -->
    <Pagination
      :pagination="pagination"
      @paginate="$emit('paginate', $event)"
    />
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
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import {
  MoreHorizontal,
  Eye,
  Edit,
  Trash2,
  XCircle,
  CheckCircle,
  Calendar,
  Box,
} from 'lucide-vue-next';
import Pagination from '@/components/Common/Pagination.vue';

interface Category {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  image: string | null;
  parent_id: number | null;
  parent: Category | null;
  sort_order: number;
  is_active: boolean;
  created_at: string;
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

interface Props {
  categories: Category[];
  pagination?: Pagination;
}

interface Emits {
  (e: 'view', category: Category): void;
  (e: 'edit', category: Category): void;
  (e: 'toggle-status', category: Category): void;
  (e: 'delete', category: Category): void;
  (e: 'paginate', url: string): void;
}

const props = defineProps<Props>();
defineEmits<Emits>();

const sortedCategories = computed(() => {
  return [...props.categories].sort((a, b) => {
    if (a.sort_order !== b.sort_order) {
      return a.sort_order - b.sort_order;
    }
    return a.name.localeCompare(b.name);
  });
});

const getStatusBadgeVariant = (isActive: boolean): 'default' | 'secondary' | 'destructive' | 'outline' => {
  return isActive ? 'default' : 'destructive';
};

const getStatusIndicatorClass = (isActive: boolean): string => {
  return isActive ? 'bg-green-500' : 'bg-red-500';
};

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  });
};
</script>
