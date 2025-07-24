<template>
  <div class="w-full">
    <!-- Table Container -->
    <div class="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow>
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
            v-for="user in sortedUsers" 
            :key="user.id"
            :class="getUserRowClass(user)"
            class="transition-colors hover:bg-muted/50"
          >
            <!-- User Column -->
            <TableCell class="py-4">
              <div class="flex items-center space-x-3">
                <Avatar class="h-9 w-9">
                  <AvatarFallback :class="getAvatarClass(user)">
                    {{ getUserInitial(user.name) }}
                  </AvatarFallback>
                </Avatar>
                <div class="min-w-0 flex-1">
                  <div class="flex items-center space-x-2">
                    <p class="text-sm font-medium text-foreground truncate">
                      {{ user.name }}
                    </p>
                    <Badge 
                      v-if="user.role === 'admin'" 
                      variant="secondary" 
                      class="bg-purple-100 text-purple-700 hover:bg-purple-100"
                    >
                      <Crown class="w-3 h-3 mr-1" />
                      Admin
                    </Badge>
                  </div>
                  <p class="text-xs text-muted-foreground truncate">
                    {{ user.email }}
                  </p>
                </div>
              </div>
            </TableCell>

            <!-- Role Column -->
            <TableCell>
              <Badge :variant="getRoleBadgeVariant(user.role)">
                {{ user.role }}
              </Badge>
            </TableCell>

            <!-- Status Column -->
            <TableCell>
              <div class="flex items-center space-x-2">
                <div 
                  :class="getStatusIndicatorClass(user.is_active)"
                  class="w-2 h-2 rounded-full"
                ></div>
                <Badge :variant="getStatusBadgeVariant(user.is_active)">
                  {{ user.is_active ? 'Active' : 'Inactive' }}
                </Badge>
              </div>
            </TableCell>

            <!-- Date Column -->
            <TableCell class="text-muted-foreground">
              <div class="flex items-center space-x-2">
                <Calendar class="w-4 h-4" />
                <span class="text-sm">{{ formatDate(user.created_at) }}</span>
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
                  <DropdownMenuItem @click="$emit('view', user)">
                    <Eye class="w-4 h-4 mr-2" />
                    View Details
                  </DropdownMenuItem>
                  <DropdownMenuItem @click="$emit('edit', user)">
                    <Edit class="w-4 h-4 mr-2" />
                    Edit User
                  </DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem @click="$emit('toggle-status', user)">
                    <component 
                      :is="user.is_active ? UserX : UserCheck" 
                      class="w-4 h-4 mr-2" 
                    />
                    {{ user.is_active ? 'Deactivate' : 'Activate' }}
                  </DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem 
                    @click="$emit('delete', user)"
                    :disabled="user.role === 'admin'"
                    class="text-destructive focus:text-destructive"
                  >
                    <Trash2 class="w-4 h-4 mr-2" />
                    Delete User
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </TableCell>
          </TableRow>

          <!-- Empty State -->
          <TableRow v-if="sortedUsers.length === 0">
            <TableCell :colspan="columns.length + 1" class="h-32 text-center">
              <div class="flex flex-col items-center justify-center space-y-3">
                <Users class="w-12 h-12 text-muted-foreground/50" />
                <div class="space-y-1">
                  <p class="text-sm font-medium text-muted-foreground">
                    {{ emptyMessage }}
                  </p>
                  <p class="text-xs text-muted-foreground">
                    Try adjusting your search criteria or add new users.
                  </p>
                </div>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination" class="flex items-center justify-between px-2 py-4">
      <div class="text-sm text-muted-foreground">
        Showing {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }} users
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
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import {
  MoreHorizontal,
  Eye,
  Edit,
  Trash2,
  UserX,
  UserCheck,
  Crown,
  Calendar,
  Users,
  ChevronLeft,
  ChevronRight,
} from 'lucide-vue-next';

interface User {
  id: number;
  name: string;
  email: string;
  role: string;
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

interface TableColumn {
  key: string;
  label: string;
}

interface Props {
  users: User[];
  pagination?: Pagination;
  columns?: TableColumn[];
  emptyMessage?: string;
  sortAdminsFirst?: boolean;
}

interface Emits {
  (e: 'view', user: User): void;
  (e: 'edit', user: User): void;
  (e: 'toggle-status', user: User): void;
  (e: 'delete', user: User): void;
  (e: 'paginate', url: string): void;
}

const props = withDefaults(defineProps<Props>(), {
  columns: () => [
    { key: 'user', label: 'User' },
    { key: 'role', label: 'Role' },
    { key: 'status', label: 'Status' },
    { key: 'created_at', label: 'Joined' }
  ],
  emptyMessage: 'No users found',
  sortAdminsFirst: true
});

const emit = defineEmits<Emits>();

const sortedUsers = computed(() => {
  if (!props.sortAdminsFirst) return props.users;
  
  return [...props.users].sort((a, b) => {
    // Admins first
    if (a.role === 'admin' && b.role !== 'admin') return -1;
    if (a.role !== 'admin' && b.role === 'admin') return 1;
    // Then by name
    return a.name.localeCompare(b.name);
  });
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

const getUserRowClass = (user: User): string => {
  return user.role === 'admin' ? 'bg-purple-50/30' : '';
};

const getAvatarClass = (user: User): string => {
  return user.role === 'admin' 
    ? 'bg-purple-100 text-purple-700 font-semibold' 
    : 'bg-primary/10 text-primary font-semibold';
};

const getRoleBadgeVariant = (role: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
  return role === 'admin' ? 'secondary' : 'outline';
};

const getStatusBadgeVariant = (isActive: boolean): 'default' | 'secondary' | 'destructive' | 'outline' => {
  return isActive ? 'default' : 'secondary';
};

const getStatusIndicatorClass = (isActive: boolean): string => {
  return isActive 
    ? 'bg-green-500' 
    : 'bg-gray-400';
};

const getUserInitial = (name: string): string => {
  return name.charAt(0).toUpperCase();
};

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit'
  });
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