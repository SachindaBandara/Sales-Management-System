<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 xl:w-1/2 shadow-lg rounded-md bg-white max-h-3/4 overflow-y-auto">
      <div class="mt-3">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-red-900">{{ title }}</h3>
          <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <!-- Errors List -->
        <div v-if="errors.length > 0" class="space-y-3 max-h-96 overflow-y-auto">
          <div v-for="error in errors" 
               :key="`error-${error.row}`" 
               class="p-3 bg-red-50 border border-red-200 rounded-lg">
            <div class="font-medium text-red-900 text-sm mb-1">
              {{ rowLabel }} {{ error.row }}
            </div>
            <ul class="text-red-800 text-sm space-y-1">
              <li v-for="errorMsg in error.errors" :key="errorMsg">• {{ errorMsg }}</li>
            </ul>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-8">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">{{ emptyMessage }}</h3>
        </div>

        <!-- Actions -->
        <div class="flex justify-end pt-4">
          <button
            @click="$emit('close')"
            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 transition-colors duration-200"
          >
            {{ closeText }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface ImportError {
  row: number;
  errors: string[];
}

interface Props {
  show: boolean;
  errors: ImportError[];
  title?: string;
  rowLabel?: string;
  emptyMessage?: string;
  closeText?: string;
}

interface Emits {
  (e: 'close'): void;
}

withDefaults(defineProps<Props>(), {
  title: 'Import Errors',
  rowLabel: 'Row',
  emptyMessage: 'No errors found',
  closeText: 'Close'
});

defineEmits<Emits>();
</script>