<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

interface Props {
  breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => [],
});

// Access page props to determine user role
const page = usePage();
const userRole = computed(() => page.props.auth.user.role || 'customer');

// Determine which layout to use based on user role
const layoutComponent = computed(() =>
  userRole.value === 'admin' ? AppHeaderLayout : AppSidebarLayout
);
</script>

<template>
  <component :is="layoutComponent" :breadcrumbs="breadcrumbs">
    <slot />
  </component>
</template>
