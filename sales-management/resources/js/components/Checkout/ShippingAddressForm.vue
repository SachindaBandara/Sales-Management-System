<script lang="ts">
import { defineComponent, PropType, reactive, watch } from 'vue';
import { Address, Errors } from '@/types/order';

export default defineComponent({
    name: 'ShippingAddressForm',
    props: {
        address: {
            type: Object as PropType<Address>,
            required: true
        },
        errors: {
            type: Object as PropType<Errors>,
            default: () => ({})
        },
        showForm: {
            type: Boolean,
            default: true
        }
    },
    setup(props, { emit }) {
        const form = reactive<Address>({ ...props.address });

        watch(form, (newValue) => {
            emit('update:address', { ...newValue });
        }, { deep: true });

        return {
            form
        };
    }
});
</script>

<template>
    <div class="bg-white shadow rounded-lg p-6" v-if="showForm">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Shipping Address</h2>

        <div class="grid grid-cols-1 gap-y-4 sm:grid-cols-2 sm:gap-x-4">
            <div>
                <label for="shipping_first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                <input type="text" id="shipping_first_name" v-model="form.first_name"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    required />
                <span v-if="errors['shipping_address.first_name']" class="text-red-500 text-sm">
                    {{ errors['shipping_address.first_name'] }}
                </span>
            </div>

            <div>
                <label for="shipping_last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                <input type="text" id="shipping_last_name" v-model="form.last_name"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    required />
                <span v-if="errors['shipping_address.last_name']" class="text-red-500 text-sm">
                    {{ errors['shipping_address.last_name'] }}
                </span>
            </div>

            <div class="sm:col-span-2">
                <label for="shipping_email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="shipping_email" v-model="form.email"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    required />
                <span v-if="errors['shipping_address.email']" class="text-red-500 text-sm">
                    {{ errors['shipping_address.email'] }}
                </span>
            </div>

            <div class="sm:col-span-2">
                <label for="shipping_phone" class="block text-sm font-medium text-gray-700">Phone</label>
                <input type="tel" id="shipping_phone" v-model="form.phone"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    required />
                <span v-if="errors['shipping_address.phone']" class="text-red-500 text-sm">
                    {{ errors['shipping_address.phone'] }}
                </span>
            </div>

            <div class="sm:col-span-2">
                <label for="shipping_address_1" class="block text-sm font-medium text-gray-700">Address Line 1</label>
                <input type="text" id="shipping_address_1" v-model="form.address_line_1"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    required />
                <span v-if="errors['shipping_address.address_line_1']" class="text-red-500 text-sm">
                    {{ errors['shipping_address.address_line_1'] }}
                </span>
            </div>

            <div class="sm:col-span-2">
                <label for="shipping_address_2" class="block text-sm font-medium text-gray-700">Address Line 2 (Optional)</label>
                <input type="text" id="shipping_address_2" v-model="form.address_line_2"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
            </div>

            <div>
                <label for="shipping_city" class="block text-sm font-medium text-gray-700">City</label>
                <input type="text" id="shipping_city" v-model="form.city"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    required />
                <span v-if="errors['shipping_address.city']" class="text-red-500 text-sm">
                    {{ errors['shipping_address.city'] }}
                </span>
            </div>

            <div>
                <label for="shipping_state" class="block text-sm font-medium text-gray-700">State</label>
                <input type="text" id="shipping_state" v-model="form.state"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    required />
                <span v-if="errors['shipping_address.state']" class="text-red-500 text-sm">
                    {{ errors['shipping_address.state'] }}
                </span>
            </div>

            <div>
                <label for="shipping_postal_code" class="block text-sm font-medium text-gray-700">Postal Code</label>
                <input type="text" id="shipping_postal_code" v-model="form.postal_code"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    required />
                <span v-if="errors['shipping_address.postal_code']" class="text-red-500 text-sm">
                    {{ errors['shipping_address.postal_code'] }}
                </span>
            </div>

            <div>
                <label for="shipping_country" class="block text-sm font-medium text-gray-700">Country</label>
                <select id="shipping_country" v-model="form.country"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    required>
                    <option value="">Select Country</option>
                    <option value="US">United States</option>
                    <option value="CA">Canada</option>
                    <option value="GB">United Kingdom</option>
                    <option value="AU">Australia</option>
                </select>
                <span v-if="errors['shipping_address.country']" class="text-red-500 text-sm">
                    {{ errors['shipping_address.country'] }}
                </span>
            </div>
        </div>
    </div>
</template>
