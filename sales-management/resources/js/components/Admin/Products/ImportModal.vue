<template>
  <div 
    v-if="show" 
    class="bg-opacity-50 fixed inset-0 z-50 h-full w-full overflow-y-auto bg-gray-600"
  >
    <div class="relative top-20 mx-auto w-11/12 rounded-md border bg-white p-5 shadow-lg md:w-3/4 lg:w-1/2">
      <div class="mt-3">
        <div class="mb-4 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
          <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 transition-colors">
            <X class="h-6 w-6" />
          </button>
        </div>

        <!-- Requirements Section -->
        <Card class="mb-6">
          <CardHeader>
            <CardTitle class="text-sm font-medium text-blue-900">
              {{ requirementsTitle }}
            </CardTitle>
          </CardHeader>
          <CardContent>
            <ul class="space-y-1 text-sm text-blue-800">
              <li v-for="requirement in requirements" :key="requirement">
                • {{ requirement }}
              </li>
            </ul>
            <div class="mt-3">
              <Button 
                variant="link" 
                class="p-0 text-blue-600 hover:text-blue-800 text-sm h-auto"
                @click="$emit('download-template')"
              >
                <FileText class="h-4 w-4 mr-1" />
                {{ templateButtonText }}
              </Button>
            </div>
          </CardContent>
        </Card>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <Label class="text-sm font-medium text-gray-700">{{ fileLabel }}</Label>
            <Input
              ref="fileInput"
              type="file"
              :accept="acceptedFormats"
              @change="handleFileChange"
              class="mt-2 block w-full text-sm text-gray-500 
                     file:mr-4 file:rounded-full file:border-0 file:bg-blue-50 
                     file:px-4 file:py-2 file:text-sm file:font-semibold 
                     file:text-blue-700 hover:file:bg-blue-100"
            />
            <p class="mt-1 text-sm text-gray-500">{{ supportedFormatsText }}</p>
            <p v-if="fileError" class="mt-1 text-sm text-red-600">{{ fileError }}</p>
          </div>

          <!-- File Preview -->
          <div v-if="selectedFile" class="p-3 bg-gray-50 rounded-lg">
            <div class="flex items-center">
              <File class="h-5 w-5 text-gray-400 mr-2" />
              <div>
                <div class="text-sm font-medium text-gray-900">{{ selectedFile.name }}</div>
                <div class="text-sm text-gray-500">{{ formatFileSize(selectedFile.size) }}</div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-3 pt-4">
            <Button
              type="button"
              variant="outline"
              @click="$emit('close')"
            >
              {{ cancelText }}
            </Button>
            <Button
              type="submit"
              :disabled="!selectedFile || isSubmitting"
              class="flex items-center space-x-2"
            >
              <Loader2 v-if="isSubmitting" class="h-4 w-4 animate-spin" />
              <span>{{ isSubmitting ? submittingText : submitText }}</span>
            </Button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { File, FileText, Loader2, X } from 'lucide-vue-next';

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
    'Name (Required): Product name',
    'SKU (Optional): Product SKU (auto-generated if empty)',
    'Price (Optional): Product price',
    'Description (Optional): Product description',
    'Status (Optional): active/inactive, 1/0, true/false, yes/no',
    'Brand (Optional): Brand name',
    'Category (Optional): Category name',
    'Stock Quantity (Optional): Stock quantity'
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

<style scoped>
.transition-colors {
  transition: color 0.2s ease-in-out;
}

/* Custom file input styling */
input[type="file"]::file-selector-button {
  margin-right: 1rem;
  border-radius: 9999px;
  border: none;
  background-color: rgb(239 246 255);
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: rgb(29 78 216);
  cursor: pointer;
  transition: background-color 0.2s ease-in-out;
}

input[type="file"]::file-selector-button:hover {
  background-color: rgb(219 234 254);
}
</style>