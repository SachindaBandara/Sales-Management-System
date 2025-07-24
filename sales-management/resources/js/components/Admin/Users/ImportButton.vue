<template>
  <button 
    @click="handleImport"
    :disabled="disabled"
    class="bg-purple-500 hover:bg-purple-700 disabled:bg-purple-300 text-white font-bold py-2 px-4 rounded transition-colors duration-200 flex items-center space-x-2"
  >
    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
    </svg>
    <span>{{ label }}</span>
  </button>
</template>

<script setup lang="ts">
interface Props {
  disabled?: boolean;
  label?: string;
}

interface Emits {
  (e: 'click'): void;
}

const props = withDefaults(defineProps<Props>(), {
  disabled: false,
  label: 'Import Excel'
});

const emit = defineEmits<Emits>();

const handleImport = () => {
  if (!props.disabled) {
    emit('click');
  }
};
</script>
