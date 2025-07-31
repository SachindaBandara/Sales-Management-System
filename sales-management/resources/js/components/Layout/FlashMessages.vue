<template>
    <div class="fixed top-4 right-4 z-50 w-full max-w-[90%] sm:max-w-md md:max-w-lg px-4 sm:px-0 space-y-4">
        <TransitionGroup name="fade-slide" tag="div">
            <Alert
                v-if="$page.props.flash.success"
                key="success"
                class="relative bg-green-50 text-green-800 border border-green-200 shadow-md rounded-lg px-4 py-3"
                variant="default"
            >
                <div class="flex items-start gap-3">
                    <CheckCircle class="h-5 w-5 text-green-600 mt-0.5" />
                    <AlertDescription class="flex-1 text-sm font-medium">
                        {{ $page.props.flash.success }}
                    </AlertDescription>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="absolute top-2 right-2 h-6 w-6 text-green-600 hover:text-green-800"
                        @click="$page.props.flash.success = null"
                    >
                        <X class="h-4 w-4" />
                    </Button>
                </div>
            </Alert>

            <Alert
                v-if="$page.props.flash.error"
                key="error"
                class="relative bg-red-50 text-red-800 border border-red-200 shadow-md rounded-lg px-4 py-3"
                variant="destructive"
            >
                <div class="flex items-start gap-3">
                    <AlertCircle class="h-5 w-5 text-red-600 mt-0.5" />
                    <AlertDescription class="flex-1 text-sm font-medium">
                        {{ $page.props.flash.error }}
                    </AlertDescription>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="absolute top-2 right-2 h-6 w-6 text-red-600 hover:text-red-800"
                        @click="$page.props.flash.error = null"
                    >
                        <X class="h-4 w-4" />
                    </Button>
                </div>
            </Alert>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.3s ease-in-out;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}

.fade-slide-move {
    transition: transform 0.3s ease-in-out;
}
</style>

<script setup lang="ts">
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { AlertCircle, CheckCircle, X } from 'lucide-vue-next';
</script>
