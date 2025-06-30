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
  const baseClasses = 'block rounded-lg px-3 py-2 text-sm font-medium transition-colors'

  return props.active
    ? `${baseClasses} bg-primary text-primary-foreground`
    : `${baseClasses} text-muted-foreground hover:bg-accent hover:text-accent-foreground`
})
</script>
