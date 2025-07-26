<template>
  <Card class="mb-6">
    <CardContent class="p-6">
      <form @submit.prevent="handleSearch" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
          <!-- Search Input -->
          <div class="space-y-2">
            <Label for="search">Search by Name</Label>
            <div class="relative">
              <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
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
            <Label for="status">Status</Label>
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button
                  variant="outline"
                  class="w-full justify-between"
                  id="status"
                >
                  <span class="truncate">
                    {{ localFilters.status ? (localFilters.status === '1' ? 'Active' : 'Inactive') : 'All Status' }}
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

          <!-- Action Buttons -->
          <div class="space-y-2">
            <Label class="invisible">Actions</Label>
            <div class="flex gap-2 flex-col sm:flex-row">
              <Button
                type="submit"
                class="w-full sm:w-auto"
                size="default"
              >
                <SearchIcon class="mr-2 h-4 w-4" />
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
            Status: {{ localFilters.status === '1' ? 'Active' : 'Inactive' }}
            <X
              class="h-3 w-3 cursor-pointer hover:text-destructive"
              @click="clearFilter('status')"
            />
          </Badge>
        </div>
      </form>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Search as SearchIcon, X, ChevronDown, Check } from 'lucide-vue-next';

interface FilterOption {
  value: string;
  label: string;
}

interface Filters {
  search?: string;
  status?: string;
}

interface Props {
  filters: Filters;
  searchPlaceholder?: string;
}

interface Emits {
  (e: 'search', filters: Filters): void;
  (e: 'clear'): void;
  (e: 'filter-change', filters: Filters): void;
}

const props = withDefaults(defineProps<Props>(), {
  searchPlaceholder: 'Search categories...',
});

const emit = defineEmits<Emits>();

const localFilters = ref<Filters>({
  search: props.filters.search || '',
  status: props.filters.status || '',
});

const statusOptions: FilterOption[] = [
  { value: '1', label: 'Active' },
  { value: '0', label: 'Inactive' },
];

const hasActiveFilters = computed(() => {
  return !!(localFilters.value.search || localFilters.value.status);
});

const getStatusVariant = (value: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
  return value === '1' ? 'default' : 'destructive';
};

const selectStatus = (value: string) => {
  localFilters.value.status = value;
};

watch(() => props.filters, (newFilters) => {
  localFilters.value = {
    search: newFilters.search || '',
    status: newFilters.status || '',
  };
}, { deep: true });

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
  };
  emit('clear');
};

const clearFilter = (filterType: keyof Filters) => {
  localFilters.value[filterType] = '';
  emit('search', { ...localFilters.value });
};
</script>
