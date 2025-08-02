<template>
  <Link
    :href="href"
    :class="linkClasses"
  >
    <component
      :is="iconComponent"
      class="mr-3 h-5 w-5 flex-shrink-0"
    />
    <slot />
  </Link>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { LayoutDashboard, Users, Award, FolderTree, Package, ShoppingCart } from 'lucide-vue-next'

interface Props {
  href: string
  active?: boolean
  icon?: string
}

const props = withDefaults(defineProps<Props>(), {
  active: false
})

const iconComponents = {
  LayoutDashboard,
  Users,
  Award,
    FolderTree,
    Package,
    ShoppingCart
}

const iconComponent = computed(() => {
  return props.icon ? iconComponents[props.icon as keyof typeof iconComponents] : null
})

const linkClasses = computed(() => {
  const baseClasses = 'group flex items-center rounded-lg px-3 py-2 text-sm font-medium transition-colors'

  return props.active
    ? `${baseClasses} bg-primary text-primary-foreground`
    : `${baseClasses} text-muted-foreground hover:bg-accent hover:text-accent-foreground`
})
</script>
