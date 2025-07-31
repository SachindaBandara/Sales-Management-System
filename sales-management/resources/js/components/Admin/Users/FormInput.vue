<template>
    <div>
        <Label :for="id">{{ label }}</Label>
        <Input
            :id="id"
            v-model="inputValue"
            :type="type"
            class="mt-1"
            :required="required"
        />
        <p v-if="error" class="text-red-600 text-sm mt-1">{{ error }}</p>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    id: string;
    label: string;
    type: string;
    modelValue: string;
    error?: string;
    required?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

// Computed property to handle v-model without mutating prop
const inputValue = computed({
    get() {
        return props.modelValue;
    },
    set(value: string) {
        emit('update:modelValue', value);
    },
});
</script>
