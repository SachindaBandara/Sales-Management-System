<template>
  <Card class="mb-6">
    <CardContent class="p-6">
      <form @submit.prevent="handleSearch" class="space-y-4">
        <!-- Main Filter Layout - Works for all screen sizes -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

          <!-- Search Input -->
          <div class="space-y-2">
            <Label for="search">Search</Label>
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

          <!-- Role Filter -->
          <div v-if="showRoleFilter" class="space-y-2">
            <Label for="role">Role</Label>
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button 
                  variant="outline" 
                  class="w-full justify-between"
                  id="role"
                >
                  <span class="truncate">
                    {{ localFilters.role ? getRoleLabel(localFilters.role) : 'All Roles' }}
                  </span>
                  <ChevronDown class="ml-2 h-4 w-4 opacity-50" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="w-full min-w-[200px]" align="start">
                <DropdownMenuItem 
                  @click="selectRole('')"
                  :class="{ 'bg-accent': !localFilters.role }"
                >
                  <Check 
                    v-if="!localFilters.role" 
                    class="mr-2 h-4 w-4" 
                  />
                  <span v-else class="mr-6"></span>
                  All Roles
                </DropdownMenuItem>
                <DropdownMenuItem 
                  v-for="role in roleOptions" 
                  :key="role.value"
                  @click="selectRole(role.value)"
                  :class="{ 'bg-accent': localFilters.role === role.value }"
                >
                  <Check 
                    v-if="localFilters.role === role.value" 
                    class="mr-2 h-4 w-4" 
                  />
                  <span v-else class="mr-6"></span>
                  {{ role.label }}
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>

          <!-- Status Filter -->
          <div v-if="showStatusFilter" class="space-y-2">
            <Label for="status">Status</Label>
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button 
                  variant="outline" 
                  class="w-full justify-between"
                  id="status"
                >
                  <span class="truncate flex items-center">
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
            v-if="localFilters.role" 
            variant="secondary"
            class="flex items-center gap-1"
          >
            Role: {{ getRoleLabel(localFilters.role) }}
            <X 
              class="h-3 w-3 cursor-pointer hover:text-destructive" 
              @click="clearFilter('role')"
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
  role?: string;
  status?: string;
}

interface Props {
  filters: Filters;
  searchPlaceholder?: string;
  showRoleFilter?: boolean;
  showStatusFilter?: boolean;
  roleOptions?: FilterOption[];
  statusOptions?: FilterOption[];
}

interface Emits {
  (e: 'search', filters: Filters): void;
  (e: 'clear'): void;
  (e: 'filter-change', filters: Filters): void;
}

const props = withDefaults(defineProps<Props>(), {
  searchPlaceholder: 'Search users...',
  showRoleFilter: true,
  showStatusFilter: true,
  roleOptions: () => [
    { value: 'admin', label: 'Administrator' },
    { value: 'customer', label: 'Customer' },
    { value: 'manager', label: 'Manager' },
    { value: 'staff', label: 'Staff' }
  ],
  statusOptions: () => [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
  ]
});

const emit = defineEmits<Emits>();

const localFilters = ref<Filters>({
  search: props.filters.search || '',
  role: props.filters.role || '',
  status: props.filters.status || ''
});

// Computed property to check if there are active filters
const hasActiveFilters = computed(() => {
  return !!(localFilters.value.search || localFilters.value.role || localFilters.value.status);
});

// Helper functions to get labels
const getRoleLabel = (value: string): string => {
  const role = props.roleOptions.find(r => r.value === value);
  return role?.label || value;
};

const getStatusLabel = (value: string): string => {
  const status = props.statusOptions.find(s => s.value === value);
  return status?.label || value;
};

const getStatusVariant = (value: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
  switch (value) {
    case 'active':
      return 'default';
    case 'inactive':
      return 'destructive';
    case 'suspended':
      return 'destructive';
    case 'pending':
      return 'outline';
    default:
      return 'secondary';
  }
};

// Filter selection handlers
const selectRole = (value: string) => {
  localFilters.value.role = value;
};

const selectStatus = (value: string) => {
  localFilters.value.status = value;
};

// Watch for external filter changes
watch(() => props.filters, (newFilters) => {
  localFilters.value = {
    search: newFilters.search || '',
    role: newFilters.role || '',
    status: newFilters.status || ''
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
    role: '',
    status: ''
  };
  emit('clear');
};

const clearFilter = (filterType: keyof Filters) => {
  localFilters.value[filterType] = '';
  emit('search', { ...localFilters.value });
};
</script>

<style scoped>
/* Additional custom styles if needed */
.lucide {
  @apply flex-shrink-0;
}
</style>