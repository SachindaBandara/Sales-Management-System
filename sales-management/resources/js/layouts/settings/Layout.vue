<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetTrigger } from '@/components/ui/sheet';
import { Menu } from 'lucide-vue-next';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const sidebarNavItems: NavItem[] = [
  {
    title: 'Profile',
    href: '/settings/profile',
  },
  {
    title: 'Password',
    href: '/settings/password',
  },
  {
    title: 'Appearance',
    href: '/settings/appearance',
  },
];

const page = usePage();
const isSheetOpen = ref(false);

const currentPath = page.props.ziggy?.location ? new URL(page.props.ziggy.location).pathname : '';

function closeSheet() {
  isSheetOpen.value = false;
}
</script>

<template>
  <div class="min-h-screen bg-background">
    <!-- Mobile Header with Menu Trigger -->
    <header class="lg:hidden sticky top-0 z-10 bg-background border-b border-border px-4 py-3 flex items-center justify-between">
      <h1 class="text-lg font-semibold">Settings</h1>
      <Sheet v-model:open="isSheetOpen">
        <SheetTrigger as-child>
          <Button variant="ghost" size="icon">
            <Menu class="h-6 w-6" />
            <span class="sr-only">Open menu</span>
          </Button>
        </SheetTrigger>
        <SheetContent side="left" class="w-64 p-0">
          <div class="flex h-full flex-col">
            <div class="p-4 border-b border-border">
              <h2 class="text-lg font-semibold">Settings</h2>
            </div>
            <nav class="flex-1 flex flex-col space-y-1 p-4">
              <Button
                v-for="item in sidebarNavItems"
                :key="item.href"
                variant="ghost"
                :class="[
                  'w-full justify-start text-left',
                  currentPath === item.href ? 'bg-muted hover:bg-muted' : 'hover:bg-muted/50'
                ]"
                @click="closeSheet"
                as-child
              >
                <Link :href="item.href">
                  {{ item.title }}
                </Link>
              </Button>
            </nav>
          </div>
        </SheetContent>
      </Sheet>
    </header>

    <!-- Main Content -->
    <div class="px-4 py-6 lg:px-8 lg:py-8 max-w-7xl mx-auto">
      <div class="flex flex-col lg:flex-row lg:space-x-12">
        <!-- Desktop Sidebar -->
        <aside class="hidden lg:block lg:w-64">
          <nav class="flex flex-col space-y-1">
            <Button
              v-for="item in sidebarNavItems"
              :key="item.href"
              variant="ghost"
              :class="[
                'w-full justify-start text-left',
                currentPath === item.href ? 'bg-muted hover:bg-muted' : 'hover:bg-muted/50'
              ]"
              as-child
            >
              <Link :href="item.href">
                {{ item.title }}
              </Link>
            </Button>
          </nav>
        </aside>

        <!-- Content Area -->
        <main class="flex-1">
          <section class="max-w-4xl mx-auto lg:mx-0 space-y-8">
            <slot />
          </section>
        </main>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Ensure smooth transitions for sheet animations */
.sheet-content {
  transition: transform 0.3s ease-in-out;
}

/* Improve button focus states for accessibility */
button:focus-visible {
  outline: 2px solid hsl(var(--primary));
  outline-offset: 2px;
}
</style>