<template>
  <div v-if="results" class="mb-6">
    <Card class="border-blue-200">
      <CardContent class="p-4">
        <div class="flex items-start">
          <Info class="h-5 w-5 text-blue-400 mr-2 mt-0.5" />
          <div class="flex-1">
            <div class="text-blue-700 text-sm font-medium">{{ title }}</div>
            <div class="text-blue-600 text-sm mt-1 flex flex-wrap gap-4">
              <span v-if="results.success_count > 0">
                <Check class="h-4 w-4 inline-block mr-1 text-green-500" />
                {{ results.success_count }} {{ successLabel }}
              </span>
              <span v-if="results.update_count > 0">
                <RefreshCw class="h-4 w-4 inline-block mr-1 text-blue-500" />
                {{ results.update_count }} {{ updateLabel }}
              </span>
              <span v-if="results.error_count > 0" class="text-red-600">
                <X class="h-4 w-4 inline-block mr-1" />
                {{ results.error_count }} errors
              </span>
            </div>
            <Button 
              v-if="results.error_count > 0" 
              variant="link" 
              class="p-0 text-blue-800 text-sm h-auto mt-1"
              @click="$emit('show-errors')"
            >
              View Error Details
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Info, Check, RefreshCw, X } from 'lucide-vue-next';

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