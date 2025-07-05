<template>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <Alert v-if="$page.props.flash.success" class="mt-4" variant="default">
            <CheckCircle class="h-4 w-4" />
            <AlertDescription>
                {{ $page.props.flash.success }}
            </AlertDescription>
        </Alert>

        <Alert v-if="$page.props.flash.error" class="mt-4" variant="destructive">
            <AlertCircle class="h-4 w-4" />
            <AlertDescription>
                {{ $page.props.flash.error }}
            </AlertDescription>
        </Alert>
    </div>
</template>

<script setup lang="ts">
import { Alert, AlertDescription } from '@/components/ui/alert';
import { AlertCircle, CheckCircle } from 'lucide-vue-next';
</script>
