<template>
    <div class="flex items-center">
        <Checkbox
            :id="id"
            :checked="checkedValue"
            @update:checked="handleUpdate"
        />
        <Label :for="id" class="ml-2 text-sm text-gray-600">{{ label }}</Label>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    id?: string;
    label: string;
    modelValue: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void;
}>();

// Computed property to manage checkbox value
const checkedValue = computed(() => {
    return props.modelValue;
});

// Handle checkbox updates, filtering out indeterminate state
const handleUpdate = (value: boolean | 'indeterminate') => {
    if (value !== 'indeterminate') {
        emit('update:modelValue', value);
    } else {
        // Maintain current modelValue if indeterminate is received
        emit('update:modelValue', props.modelValue);
    }
};
</script>
