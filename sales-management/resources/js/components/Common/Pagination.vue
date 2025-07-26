<template>
  <div v-if="pagination" class="flex items-center justify-between px-2 py-4">
    <!-- Page Range Info -->
    <div class="text-sm text-muted-foreground">
      Showing {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }} items
    </div>

    <!-- Pagination Controls -->
    <div class="flex items-center space-x-2">
      <!-- Previous Button -->
      <Button
        variant="outline"
        size="sm"
        @click="goToPreviousPage"
        :disabled="!hasPreviousPage"
        class="h-8 px-3"
        aria-label="Go to previous page"
      >
        <ChevronLeft class="w-4 h-4 mr-1" />
        Previous
      </Button>

      <!-- Page Numbers -->
      <div class="flex items-center space-x-1">
        <Button
          v-for="link in paginationNumbers"
          :key="link.label"
          :variant="link.active ? 'default' : 'outline'"
          size="sm"
          @click="link.url && $emit('paginate', link.url)"
          :disabled="!link.url"
          class="h-8 w-8 p-0"
          :aria-label="`Go to page ${link.label}`"
          :aria-current="link.active ? 'page' : undefined"
        >
          {{ link.label }}
        </Button>
      </div>

      <!-- Next Button -->
      <Button
        variant="outline"
        size="sm"
        @click="goToNextPage"
        :disabled="!hasNextPage"
        class="h-8 px-3"
        aria-label="Go to next page"
      >
        Next
        <ChevronRight class="w-4 h-4 ml-1" />
      </Button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

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
  pagination?: Pagination;
}

interface Emits {
  (e: 'paginate', url: string): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Filter pagination links to show only page numbers (exclude Previous/Next)
const paginationNumbers = computed(() => {
  if (!props.pagination) return [];

  return props.pagination.links.filter(link => {
    const label = link.label.replace(/(<([^>]+)>)/gi, '');
    return label !== 'Previous' && label !== 'Next' && !isNaN(Number(label));
  });
});

// Check if previous page exists
const hasPreviousPage = computed(() => {
  if (!props.pagination) return false;
  const prevLink = props.pagination.links.find(link =>
    link.label.includes('Previous')
  );
  return prevLink?.url !== null;
});

// Check if next page exists
const hasNextPage = computed(() => {
  if (!props.pagination) return false;
  const nextLink = props.pagination.links.find(link =>
    link.label.includes('Next')
  );
  return nextLink?.url !== null;
});

// Navigate to previous page
const goToPreviousPage = () => {
  if (!props.pagination) return;
  const prevLink = props.pagination.links.find(link =>
    link.label.includes('Previous')
  );
  if (prevLink?.url) {
    emit('paginate', prevLink.url);
  }
};

// Navigate to next page
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
