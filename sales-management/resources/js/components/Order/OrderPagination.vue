<script setup lang="ts">
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Pagination, PaginationContent, PaginationFirst, PaginationItem, PaginationLast, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';

interface Props {
  currentPage: number;
  lastPage: number;
  perPage: number;
  total: number;
}

interface Emits {
  (e: 'page-change', page: number): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Compute page numbers for pagination
const pageNumbers = computed(() => {
  const maxPagesToShow = 5;
  const currentPage = props.currentPage;
  const lastPage = props.lastPage;
  const startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
  const endPage = Math.min(lastPage, startPage + maxPagesToShow - 1);
  return Array.from({ length: endPage - startPage + 1 }, (_, i) => startPage + i);
});

const handlePageChange = (page: number) => {
  if (page >= 1 && page <= props.lastPage) {
    emit('page-change', page);
  }
};
</script>

<template>
  <Pagination
    v-if="lastPage > 1"
    :total="total"
    :itemsPerPage="perPage"
    :page="currentPage"
    class="mt-4"
  >
    <PaginationContent>
      <PaginationFirst
        :disabled="currentPage === 1"
        @click="handlePageChange(1)"
      />
      <PaginationPrevious
        :disabled="currentPage === 1"
        @click="handlePageChange(currentPage - 1)"
      />
      <PaginationItem
        v-for="page in pageNumbers"
        :key="page"
        :value="page"
        :isActive="page === currentPage"
      >
        <Button
          :variant="page === currentPage ? 'default' : 'outline'"
          @click="handlePageChange(page)"
        >
          {{ page }}
        </Button>
      </PaginationItem>
      <PaginationNext
        :disabled="currentPage === lastPage"
        @click="handlePageChange(currentPage + 1)"
      />
      <PaginationLast
        :disabled="currentPage === lastPage"
        @click="handlePageChange(lastPage)"
      />
    </PaginationContent>
  </Pagination>
</template>
