<template>
  <button 
    @click="handleExport"
    :disabled="disabled || isExporting"
    class="bg-green-500 hover:bg-green-700 disabled:bg-green-300 text-white font-bold py-2 px-4 rounded transition-colors duration-200 flex items-center space-x-2"
  >
    <svg v-if="isExporting" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
    </svg>
    <span>{{ isExporting ? exportingLabel : label }}</span>
  </button>
</template>

<script setup lang="ts">
interface Props {
  disabled?: boolean;
  isExporting?: boolean;
  label?: string;
  exportingLabel?: string;
}

interface Emits {
  (e: 'click'): void;
}

const props = withDefaults(defineProps<Props>(), {
  disabled: false,
  isExporting: false,
  label: 'Export Excel',
  exportingLabel: 'Exporting...'
});

const emit = defineEmits<Emits>();

const handleExport = () => {
  if (!props.disabled && !props.isExporting) {
    emit('click');
  }
};
</script>
