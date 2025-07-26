<template>
  <div v-if="selectedItems.length > 0" class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4">
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <span class="text-sm font-medium text-blue-800">
          {{ selectedItems.length }} {{ itemType }}{{ selectedItems.length === 1 ? '' : 's' }} selected
        </span>
        <button 
          @click="showActions = !showActions" 
          class="text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors"
        >
          <span class="flex items-center">
            Bulk Actions
            <ChevronDown 
              :class="['ml-1 h-4 w-4 transition-transform', showActions ? 'rotate-180' : '']"
            />
          </span>
        </button>
      </div>
      <button 
        @click="$emit('clear-selection')" 
        class="text-sm text-blue-600 hover:text-blue-800 transition-colors"
      >
        Clear Selection
      </button>
    </div>

    <div v-if="showActions" class="mt-4 flex flex-wrap gap-2">
      <Button
        v-for="action in actions"
        :key="action.key"
        @click="handleBulkAction(action.key)"
        :disabled="isLoading"
        :variant="action.variant || 'default'"
        size="sm"
        class="transition-all"
      >
        <component 
          :is="action.icon" 
          v-if="action.icon" 
          class="mr-2 h-4 w-4" 
        />
        <Loader2 
          v-if="isLoading && loadingAction === action.key" 
          class="mr-2 h-4 w-4 animate-spin" 
        />
        {{ action.label }}
      </Button>
    </div>

    <!-- Action Confirmation Dialog -->
    <ActionConfirmationDialog
      :open="showConfirmDialog"
      :title="confirmDialog.title"
      :message="confirmDialog.message"
      :confirm-text="confirmDialog.confirmText"
      :confirm-variant="confirmDialog.variant"
      :is-confirming="isConfirming"
      @update:open="showConfirmDialog = $event"
      @confirm="executeConfirmedAction"
      @cancel="cancelConfirmation"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { 
  ChevronDown, 
  Loader2, 
  CheckCircle, 
  XCircle, 
  Archive, 
  Trash2,
} from 'lucide-vue-next';
import ActionConfirmationDialog from '@/components/Common/ActionConfirmationDialog.vue';

interface BulkAction {
  key: string;
  label: string;
  icon?: any;
  variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link' | null | undefined;
  confirmTitle?: string;
  confirmMessage?: string;
  confirmText?: string;
  requiresConfirmation?: boolean;
}

interface Props {
  selectedItems: number[];
  itemType?: string;
  isLoading?: boolean;
  actions?: BulkAction[];
  loadingAction?: string;
}

interface Emits {
  (e: 'bulk-action', action: string, items: number[]): void;
  (e: 'clear-selection'): void;
}

const props = withDefaults(defineProps<Props>(), {
  itemType: 'item',
  isLoading: false,
  actions: () => [
    {
      key: 'activate',
      label: 'Activate Selected',
      icon: CheckCircle,
      variant: 'default',
      confirmTitle: 'Activate Items',
      confirmMessage: 'Are you sure you want to activate the selected items?',
      confirmText: 'Activate',
      requiresConfirmation: true
    },
    {
      key: 'deactivate',
      label: 'Deactivate Selected',
      icon: XCircle,
      variant: 'outline',
      confirmTitle: 'Deactivate Items',
      confirmMessage: 'Are you sure you want to deactivate the selected items?',
      confirmText: 'Deactivate',
      requiresConfirmation: true
    },
    {
      key: 'archive',
      label: 'Archive Selected',
      icon: Archive,
      variant: 'secondary',
      confirmTitle: 'Archive Items',
      confirmMessage: 'Are you sure you want to archive the selected items?',
      confirmText: 'Archive',
      requiresConfirmation: true
    },
    {
      key: 'delete',
      label: 'Delete Selected',
      icon: Trash2,
      variant: 'destructive',
      confirmTitle: 'Delete Items',
      confirmMessage: 'Are you sure you want to delete the selected items? This action cannot be undone.',
      confirmText: 'Delete',
      requiresConfirmation: true
    }
  ]
});

const emit = defineEmits<Emits>();

const showActions = ref(false);
const showConfirmDialog = ref(false);
const isConfirming = ref(false);
const pendingAction = ref<string>('');
const confirmDialog = ref<{
  title: string;
  message: string;
  confirmText: string;
  variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link' | null | undefined;
}>({
  title: '',
  message: '',
  confirmText: '',
  variant: 'destructive'
});

const handleBulkAction = (actionKey: string) => {
  const action = props.actions.find(a => a.key === actionKey);
  
  if (!action) return;

  if (action.requiresConfirmation) {
    pendingAction.value = actionKey;
    confirmDialog.value = {
      title: action.confirmTitle || `${action.label}?`,
      message: action.confirmMessage || `Are you sure you want to ${action.label.toLowerCase()}?`,
      confirmText: action.confirmText || action.label,
      variant: action.variant || 'destructive'
    };
    showConfirmDialog.value = true;
  } else {
    emit('bulk-action', actionKey, props.selectedItems);
  }
};

const executeConfirmedAction = () => {
  if (!pendingAction.value) return;
  
  isConfirming.value = true;
  emit('bulk-action', pendingAction.value, props.selectedItems);
  
  // Close dialog after a short delay to show loading state
  setTimeout(() => {
    showConfirmDialog.value = false;
    isConfirming.value = false;
    pendingAction.value = '';
  }, 500);
};

const cancelConfirmation = () => {
  showConfirmDialog.value = false;
  pendingAction.value = '';
  isConfirming.value = false;
};
</script>

<style scoped>
/* Additional animations for smooth transitions */
.transition-all {
  transition: all 0.2s ease-in-out;
}

.transition-colors {
  transition: color 0.2s ease-in-out;
}

.rotate-180 {
  transform: rotate(180deg);
}

.transition-transform {
  transition: transform 0.2s ease-in-out;
}
</style>