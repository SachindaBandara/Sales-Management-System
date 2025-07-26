<template>
    <div class="w-full">
        <!-- Table Container -->
        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="font-medium">Brand</TableHead>
                        <TableHead class="font-medium">Slug</TableHead>
                        <TableHead class="font-medium">Status</TableHead>
                        <TableHead class="font-medium">Created</TableHead>
                        <TableHead class="font-medium">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="brand in sortedBrands" :key="brand.id" class="transition-colors hover:bg-muted/50">
                        <!-- Brand Column -->
                        <TableCell class="py-4">
                            <div class="flex items-center space-x-3">
                                <Avatar class="h-10 w-10">
                                    <img v-if="brand.logo" :src="brand.logo" class="h-10 w-10 rounded-full object-cover" alt="Brand logo" />
                                    <AvatarFallback class="bg-blue-100 font-semibold text-blue-600">
                                        {{ brand.name.charAt(0).toUpperCase() }}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="min-w-0 flex-1">
                                    <div class="truncate text-sm font-medium text-foreground">
                                        {{ brand.name }}
                                    </div>
                                    <p v-if="brand.description" class="max-w-xs truncate text-xs text-muted-foreground">
                                        {{ brand.description }}
                                    </p>
                                </div>
                            </div>
                        </TableCell>

                        <!-- Slug Column -->
                        <TableCell class="text-sm text-muted-foreground">
                            {{ brand.slug }}
                        </TableCell>

                        <!-- Status Column -->
                        <TableCell>
                            <div class="flex items-center space-x-2">
                                <div :class="getStatusIndicatorClass(brand.is_active)" class="h-2 w-2 rounded-full"></div>
                                <Badge :variant="getStatusBadgeVariant(brand.is_active)">
                                    {{ brand.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </div>
                        </TableCell>

                        <!-- Created At Column -->
                        <TableCell class="text-muted-foreground">
                            <div class="flex items-center space-x-2">
                                <Calendar class="h-4 w-4" />
                                <span class="text-sm">{{ formatDate(brand.created_at) }}</span>
                            </div>
                        </TableCell>

                        <!-- Actions Column -->
                        <TableCell>
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                                        <MoreHorizontal class="h-4 w-4" />
                                        <span class="sr-only">Open menu</span>
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-48">
                                    <DropdownMenuItem @click="$emit('view', brand)">
                                        <Eye class="mr-2 h-4 w-4" />
                                        View Details
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="$emit('edit', brand)">
                                        <Edit class="mr-2 h-4 w-4" />
                                        Edit Brand
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem @click="$emit('toggle-status', brand)">
                                        <component :is="brand.is_active ? XCircle : CheckCircle" class="mr-2 h-4 w-4" />
                                        {{ brand.is_active ? 'Deactivate' : 'Activate' }}
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem @click="$emit('delete', brand)" class="text-destructive focus:text-destructive">
                                        <Trash2 class="mr-2 h-4 w-4" />
                                        Delete Brand
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </TableCell>
                    </TableRow>

                    <!-- Empty State -->
                    <TableRow v-if="sortedBrands.length === 0">
                        <TableCell :colspan="5" class="h-32 text-center">
                            <div class="flex flex-col items-center justify-center space-y-3">
                                <Box class="h-12 w-12 text-muted-foreground/50" />
                                <div class="space-y-1">
                                    <p class="text-sm font-medium text-muted-foreground">No brands found</p>
                                    <p class="text-xs text-muted-foreground">Try adjusting your search criteria or add new brands.</p>
                                </div>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <Pagination :pagination="pagination" @paginate="$emit('paginate', $event)" />
    </div>
</template>

<script setup lang="ts">
import Pagination from '@/components/Common/Pagination.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Box, Calendar, CheckCircle, Edit, Eye, MoreHorizontal, Trash2, XCircle } from 'lucide-vue-next';
import { computed } from 'vue';

interface Brand {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    logo: string | null;
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
    brands: Brand[];
    pagination?: Pagination;
}

interface Emits {
    (e: 'view', brand: Brand): void;
    (e: 'edit', brand: Brand): void;
    (e: 'toggle-status', brand: Brand): void;
    (e: 'delete', brand: Brand): void;
    (e: 'paginate', url: string): void;
}

const props = defineProps<Props>();

defineEmits<Emits>();

const sortedBrands = computed(() => {
    return [...props.brands].sort((a, b) => a.name.localeCompare(b.name));
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
