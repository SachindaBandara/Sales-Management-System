<template>
    <div class="flex items-center space-x-4">
        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <Button variant="ghost" class="relative h-10 w-auto px-3">
                    <div class="flex items-center space-x-3">
                        <Avatar :fallback="$page.props.auth.user.name.charAt(0).toUpperCase()" size="sm" />
                        <Badge variant="secondary" class="ml-2">
                            {{ $page.props.auth.user.name }}
                        </Badge>
                        <ChevronDown class="h-4 w-4 text-muted-foreground" />
                    </div>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="w-56">
                <DropdownMenuLabel>
                    <div class="flex flex-col space-y-1">
                        <p class="text-sm font-medium">{{ $page.props.auth.user.name }}</p>
                        <p class="text-xs text-muted-foreground">{{ $page.props.auth.user.email }}</p>
                    </div>
                </DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem as-child>
                    <Link :href="route('profile.edit')" class="flex items-center">
                        <User class="mr-2 h-4 w-4" />
                        Profile
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem as-child>
                    <Link :href="route('logout')" method="post" as="button" class="flex w-full items-center text-red-600">
                        <LogOut class="mr-2 h-4 w-4" />
                        Log out
                    </Link>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Avatar from '@/components/ui/avatar/Avatar.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import Button from '@/components/ui/button/Button.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { ChevronDown, LogOut, User } from 'lucide-vue-next';
</script>
