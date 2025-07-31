<template>
    <form @submit.prevent="submit">
        <div class="space-y-6">
            <FormInput
                id="name"
                v-model="form.name"
                label="Name"
                type="text"
                :error="errors?.name"
                required
            />
            <FormInput
                id="email"
                v-model="form.email"
                label="Email"
                type="email"
                :error="errors?.email"
                required
            />
            <FormSelect
                id="role"
                v-model="form.role"
                label="Role"
                :options="roleOptions"
                :error="errors?.role"
                required
                placeholder="Select Role"
            />
            <FormInput
                id="password"
                v-model="form.password"
                label="Password"
                type="password"
                :error="errors?.password"
                required
            />
            <FormInput
                id="password_confirmation"
                v-model="form.password_confirmation"
                label="Confirm Password"
                type="password"
                :error="errors?.password_confirmation"
                required
            />
            <FormCheckbox
                v-model="form.is_active"
                label="Active User"
            />
            <div class="flex justify-end space-x-4">
                <FormButton
                    :href="route('admin.users.index')"
                    variant="secondary"
                >
                    Cancel
                </FormButton>
                <FormButton
                    type="submit"
                    :disabled="form.processing"
                >
                    Create User
                </FormButton>
            </div>
        </div>
    </form>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import FormInput from './FormInput.vue';
import FormSelect from './FormSelect.vue';
import FormCheckbox from './FormCheckbox.vue';
import FormButton from './FormButton.vue';

defineProps<{
    errors?: Record<string, string>;
}>();

const form = useForm({
    name: '',
    email: '',
    role: '',
    password: '',
    password_confirmation: '',
    is_active: true,
});

const roleOptions = [
    { value: 'customer', label: 'Customer' },
    { value: 'admin', label: 'Admin' },
];

const submit = () => {
    form.post(route('admin.users.store'));
};
</script>
