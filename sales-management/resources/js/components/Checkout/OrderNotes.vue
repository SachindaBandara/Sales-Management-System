<script lang="ts">
import { defineComponent, ref, watch } from 'vue';

export default defineComponent({
    name: 'OrderNotes',
    props: {
        notes: {
            type: String,
            default: ''
        }
    },
    setup(props, { emit }) {
        // Local reactive state to manage the notes value
        const localNotes = ref<string>(props.notes);

        // Watch for changes in the prop to sync local state
        watch(() => props.notes, (newValue) => {
            localNotes.value = newValue;
        });

        // Watch for changes in local state and emit to parent
        watch(localNotes, (newValue) => {
            emit('update:notes', newValue);
        });

        return {
            localNotes
        };
    }
});
</script>

<template>
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Order Notes (Optional)</h2>
        <textarea v-model="localNotes" rows="4"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
            placeholder="Special delivery instructions, etc."></textarea>
    </div>
</template>
