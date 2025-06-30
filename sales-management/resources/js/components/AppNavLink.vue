<template>
  <Link
    :href="href"
    :class="linkClasses"
  >
    <slot />
  </Link>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

interface Props {
  href: string
  active?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  active: false
})

const linkClasses = computed(() => {
  const baseClasses = 'text-sm font-medium transition-colors hover:text-primary'

  return props.active
    ? `${baseClasses} text-primary`
    : `${baseClasses} text-muted-foreground`
})
</script>
