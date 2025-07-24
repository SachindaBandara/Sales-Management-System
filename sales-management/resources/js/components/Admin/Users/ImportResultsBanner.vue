<template>
  <div v-if="results" class="mb-6">
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
      <div class="flex items-start">
        <svg class="h-5 w-5 text-blue-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
        </svg>
        <div class="flex-1">
          <div class="text-blue-700 text-sm font-medium">{{ title }}</div>
          <div class="text-blue-600 text-sm mt-1">
            <span v-if="results.success_count > 0" class="mr-4">
              ✅ {{ results.success_count }} {{ successLabel }}
            </span>
            <span v-if="results.update_count > 0" class="mr-4">
              🔄 {{ results.update_count }} {{ updateLabel }}
            </span>
            <span v-if="results.error_count > 0" class="mr-4 text-red-600">
              ❌ {{ results.error_count }} errors
            </span>
          </div>
          <button 
            v-if="results.error_count > 0" 
            @click="$emit('show-errors')" 
            class="text-blue-800 text-sm underline mt-1"
          >
            View Error Details
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface ImportResults {
  success_count: number;
  update_count: number;
  error_count: number;
  total_processed: number;
}

interface Props {
  results?: ImportResults;
  title?: string;
  successLabel?: string;
  updateLabel?: string;
}

interface Emits {
  (e: 'show-errors'): void;
}

withDefaults(defineProps<Props>(), {
  title: 'Import Results Summary',
  successLabel: 'items created',
  updateLabel: 'items updated'
});

defineEmits<Emits>();
</script>