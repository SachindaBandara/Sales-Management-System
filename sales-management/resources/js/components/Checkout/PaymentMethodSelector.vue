<script lang="ts">
import { defineComponent, PropType, ref, watch } from 'vue';

export default defineComponent({
    name: 'PaymentMethodSelector',
    props: {
        paymentMethod: {
            type: String as PropType<'cash_on_delivery' | 'card' | 'paypal'>,
            required: true
        },
        errors: {
            type: Object as PropType<Record<string, string>>,
            default: () => ({})
        }
    },
    setup(props, { emit }) {
        // Local reactive state to manage the selected payment method
        const localPaymentMethod = ref<'cash_on_delivery' | 'card' | 'paypal'>(props.paymentMethod);

        // Watch for changes in the prop to sync local state
        watch(() => props.paymentMethod, (newValue) => {
            localPaymentMethod.value = newValue;
        });

        // Watch for changes in local state and emit to parent
        watch(localPaymentMethod, (newValue) => {
            emit('update:paymentMethod', newValue);
        });

        return {
            localPaymentMethod
        };
    }
});
</script>

<template>
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Payment Method</h2>

        <div class="space-y-4">
            <label class="flex items-center">
                <input type="radio" v-model="localPaymentMethod" value="cash_on_delivery"
                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" />
                <span class="ml-2 text-sm text-gray-700">Cash on Delivery</span>
            </label>

            <label class="flex items-center">
                <input type="radio" v-model="localPaymentMethod" value="card"
                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" />
                <span class="ml-2 text-sm text-gray-700">Credit/Debit Card</span>
            </label>

            <label class="flex items-center">
                <input type="radio" v-model="localPaymentMethod" value="paypal"
                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" />
                <span class="ml-2 text-sm text-gray-700">PayPal</span>
            </label>
        </div>

        <span v-if="errors.payment_method" class="text-red-500 text-sm">
            {{ errors.payment_method }}
        </span>
    </div>
</template>
