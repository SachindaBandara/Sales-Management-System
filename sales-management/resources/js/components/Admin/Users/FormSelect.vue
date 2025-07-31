<template>
    <div>
        <Label :for="id">{{ label }}</Label>
        <Select v-model="selectValue" :required="required">
            <SelectTrigger class="mt-1 w-full">
                <SelectValue :placeholder="placeholder" />
            </SelectTrigger>
            <SelectContent class="z-[100] bg-white shadow-lg rounded-md">
                <SelectItem
                    v-for="option in options"
                    :key="option.value"
                    :value="option.value"
                    class="cursor-pointer hover:bg-gray-100"
                >
                    {{ option.label }}
                </SelectItem>
            </SelectContent>
        </Select>
        <p v-if="error" class="text-red-600 text-sm mt-1">{{ error }}</p>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    id: string;
    label: string;
    options: { value: string; label: string }[];
    modelValue: string;
    error?: string;
    required?: boolean;
    placeholder?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const selectValue = computed({
    get() {
        return props.modelValue;
    },
    set(value: string) {
        emit('update:modelValue', value);
    },
});
</script>

