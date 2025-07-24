<template>
  <Dialog :open="show" @update:open="$emit('close')">
    <DialogContent class="sm:max-w-[550px] max-h-[80vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle class="text-lg font-semibold text-gray-900">{{ title }}</DialogTitle>
      </DialogHeader>
      
      <!-- Requirements Section -->
      <Card class="mb-6">
        <CardHeader>
          <CardTitle class="text-sm font-medium text-blue-900">{{ requirementsTitle }}</CardTitle>
        </CardHeader>
        <CardContent>
          <ul class="text-sm text-blue-800 space-y-1">
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
            class="mt-2"
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
        <DialogFooter>
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
            :class="{ 'flex items-center space-x-2': true }"
          >
            <Loader2 v-if="isSubmitting" class="h-4 w-4 animate-spin" />
            <span>{{ isSubmitting ? submittingText : submitText }}</span>
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { File, FileText, Loader2 } from 'lucide-vue-next';

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