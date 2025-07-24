<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
          <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <!-- Requirements Section -->
        <div class="mb-6 p-4 bg-blue-50 rounded-lg">
          <h4 class="text-sm font-medium text-blue-900 mb-2">{{ requirementsTitle }}</h4>
          <ul class="text-sm text-blue-800 space-y-1">
            <li v-for="requirement in requirements" :key="requirement">
              • {{ requirement }}
            </li>
          </ul>
          <div class="mt-3">
            <button @click="$emit('download-template')" class="text-blue-600 hover:text-blue-800 text-sm underline">
              📄 {{ templateButtonText }}
            </button>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ fileLabel }}
            </label>
            <input
              ref="fileInput"
              type="file"
              :accept="acceptedFormats"
              @change="handleFileChange"
              class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
            />
            <p class="mt-1 text-sm text-gray-500">{{ supportedFormatsText }}</p>
            <div v-if="fileError" class="mt-1 text-sm text-red-600">
              {{ fileError }}
            </div>
          </div>

          <!-- File Preview -->
          <div v-if="selectedFile" class="p-3 bg-gray-50 rounded-lg">
            <div class="flex items-center">
              <svg class="h-5 w-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
              </svg>
              <div>
                <div class="text-sm font-medium text-gray-900">{{ selectedFile.name }}</div>
                <div class="text-sm text-gray-500">{{ formatFileSize(selectedFile.size) }}</div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="$emit('close')"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors duration-200"
            >
              {{ cancelText }}
            </button>
            <button
              type="submit"
              :disabled="!selectedFile || isSubmitting"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:bg-blue-300 disabled:cursor-not-allowed transition-colors duration-200 flex items-center space-x-2"
            >
              <svg v-if="isSubmitting" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ isSubmitting ? submittingText : submitText }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';

interface Props {
  show: boolean;
  title?: string;
  requirementsTitle?: string;
  requirements?: string[];
  templateButtonText?: string;
  fileLabel?: string;
  acceptedFormats?: string;
  supportedFormatsText?: string;
  cancelText?: string;
  submitText?: string;
  submittingText?: string;
  isSubmitting?: boolean;
  fileError?: string;
}

interface Emits {
  (e: 'close'): void;
  (e: 'submit', file: File): void;
  (e: 'download-template'): void;
}

const props = withDefaults(defineProps<Props>(), {
  title: 'Import from Excel',
  requirementsTitle: 'Excel Format Requirements:',
  requirements: () => [
    'Name (Required): Item name',
    'Description (Optional): Item description',
    'Status (Optional): active/inactive, 1/0, true/false, yes/no'
  ],
  templateButtonText: 'Download Excel Template',
  fileLabel: 'Select Excel File',
  acceptedFormats: '.xlsx,.xls,.csv',
  supportedFormatsText: 'Supported formats: .xlsx, .xls, .csv (Max: 10MB)',
  cancelText: 'Cancel',
  submitText: 'Import',
  submittingText: 'Importing...',
  isSubmitting: false
});

const emit = defineEmits<Emits>();

const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);

watch(() => props.show, (newShow) => {
  if (!newShow) {
    selectedFile.value = null;
    if (fileInput.value) {
      fileInput.value.value = '';
    }
  }
});

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    selectedFile.value = target.files[0];
  }
};

const handleSubmit = () => {
  if (selectedFile.value && !props.isSubmitting) {
    emit('submit', selectedFile.value);
  }
};

const formatFileSize = (bytes: number): string => {
  return `${Math.round(bytes / 1024)} KB`;
};
</script>