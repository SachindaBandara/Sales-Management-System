<script lang="ts">
import { defineComponent, ref, reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import OrderSummary from '@/components/Checkout/OrderSummary.vue';
import BillingAddressForm from '@/components/Checkout/BillingAddressForm.vue';
import ShippingAddressForm from '@/components/Checkout/ShippingAddressForm.vue';
import PaymentMethodSelector from '@/components/Checkout/PaymentMethodSelector.vue';
import OrderNotes from '@/components/Checkout/OrderNotes.vue';
import { CartItem, CartTotals, User, FormData, Errors } from '@/types/order';

export default defineComponent({
    name: 'Checkout',
    components: {
        OrderSummary,
        BillingAddressForm,
        ShippingAddressForm,
        PaymentMethodSelector,
        OrderNotes
    },
    props: {
        cart: {
            type: Object as () => Record<string, CartItem>,
            required: true
        },
        cartTotals: {
            type: Object as () => CartTotals,
            required: true
        },
        user: {
            type: Object as () => User,
            default: () => ({})
        },
        errors: {
            type: Object as () => Errors,
            default: () => ({})
        }
    },
    setup(props) {
        const processing = ref<boolean>(false);

        const form = reactive<FormData>({
            billing_address: {
                first_name: props.user?.first_name || '',
                last_name: props.user?.last_name || '',
                email: props.user?.email || '',
                phone: props.user?.phone || '',
                address_line_1: '',
                address_line_2: '',
                city: '',
                state: '',
                postal_code: '',
                country: ''
            },
            shipping_address: {
                first_name: '',
                last_name: '',
                email: '',
                phone: '',
                address_line_1: '',
                address_line_2: '',
                city: '',
                state: '',
                postal_code: '',
                country: ''
            },
            shipping_same_as_billing: true,
            payment_method: 'cash_on_delivery',
            notes: ''
        });

        watch(() => form.shipping_same_as_billing, (newValue) => {
            if (newValue) {
                form.shipping_address = { ...form.billing_address };
            }
        });

        const submitOrder = (): void => {
            processing.value = true;

            router.post(route('customer.orders.store'), form, {
                onFinish: () => {
                    processing.value = false;
                }
            });
        };

        return {
            form,
            processing,
            submitOrder
        };
    }
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto lg:max-w-none">
                <h1 class="text-3xl font-bold text-gray-900 mb-8">Checkout</h1>

                <form @submit.prevent="submitOrder" class="lg:grid lg:grid-cols-2 lg:gap-x-12 xl:gap-x-16">
                    <!-- Left Column - Billing & Shipping -->
                    <div class="space-y-8">
                        <BillingAddressForm v-model:address="form.billing_address" :errors="errors" />

                        <div class="bg-white shadow rounded-lg p-6">
                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input type="checkbox" v-model="form.shipping_same_as_billing"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                    <span class="ml-2 text-sm text-gray-700">Same as billing address</span>
                                </label>
                            </div>
                            <ShippingAddressForm v-model:address="form.shipping_address" :errors="errors"
                                :showForm="!form.shipping_same_as_billing" />
                        </div>

                        <PaymentMethodSelector v-model:paymentMethod="form.payment_method" :errors="errors" />
                        <OrderNotes v-model:notes="form.notes" />
                    </div>

                    <!-- Right Column - Order Summary -->
                    <div class="mt-10 lg:mt-0">
                        <OrderSummary :cart="cart" :cartTotals="cartTotals" :isProcessing="processing"
                            @submit="submitOrder" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
