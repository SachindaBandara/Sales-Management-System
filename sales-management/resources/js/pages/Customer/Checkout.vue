<script lang="ts">
import { defineComponent, ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import OrderSummary from '@/components/Checkout/OrderSummary.vue';

interface CartItem {
    name: string
    sku?: string
    price: number
    quantity: number
    image?: string
}

interface CartTotals {
    subtotal: number
    tax: number
    taxRate: number
    total: number
}

interface Address {
    first_name: string
    last_name: string
    email: string
    phone: string
    address_line_1: string
    address_line_2: string
    city: string
    state: string
    postal_code: string
    country: string
}

interface User {
    first_name?: string
    last_name?: string
    email?: string
    phone?: string
}

interface FormData {
    billing_address: Address
    shipping_address: Address
    shipping_same_as_billing: boolean
    payment_method: 'cash_on_delivery' | 'card' | 'paypal'
    notes: string
}

interface Errors {
    [key: string]: string
}

export default defineComponent({
    name: 'Checkout',
    components: {
        OrderSummary
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
        const processing = ref<boolean>(false)

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
        })

        const copyBillingToShipping = (): void => {
            if (form.shipping_same_as_billing) {
                form.shipping_address = { ...form.billing_address }
            }
        }

        const submitOrder = (): void => {
            processing.value = true

            router.post(route('customer.orders.store'), form, {
                onFinish: () => {
                    processing.value = false
                }
            })
        }

        return {
            form,
            processing,
            copyBillingToShipping,
            submitOrder
        }
    }
})
</script>

<template>

        <div class="min-h-screen bg-gray-50 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl mx-auto lg:max-w-none">
                    <h1 class="text-3xl font-bold text-gray-900 mb-8">Checkout</h1>

                    <form @submit.prevent="submitOrder" class="lg:grid lg:grid-cols-2 lg:gap-x-12 xl:gap-x-16">
                        <!-- Left Column - Billing & Shipping -->
                        <div class="space-y-8">
                            <!-- Billing Address -->
                            <div class="bg-white shadow rounded-lg p-6">
                                <h2 class="text-lg font-medium text-gray-900 mb-4">Billing Address</h2>

                                <div class="grid grid-cols-1 gap-y-4 sm:grid-cols-2 sm:gap-x-4">
                                    <div>
                                        <label for="billing_first_name"
                                            class="block text-sm font-medium text-gray-700">First Name</label>
                                        <input type="text" id="billing_first_name"
                                            v-model="form.billing_address.first_name"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            required />
                                        <span v-if="errors['billing_address.first_name']" class="text-red-500 text-sm">
                                            {{ errors['billing_address.first_name'] }}
                                        </span>
                                    </div>

                                    <div>
                                        <label for="billing_last_name"
                                            class="block text-sm font-medium text-gray-700">Last Name</label>
                                        <input type="text" id="billing_last_name"
                                            v-model="form.billing_address.last_name"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            required />
                                        <span v-if="errors['billing_address.last_name']" class="text-red-500 text-sm">
                                            {{ errors['billing_address.last_name'] }}
                                        </span>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="billing_email"
                                            class="block text-sm font-medium text-gray-700">Email</label>
                                        <input type="email" id="billing_email" v-model="form.billing_address.email"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            required />
                                        <span v-if="errors['billing_address.email']" class="text-red-500 text-sm">
                                            {{ errors['billing_address.email'] }}
                                        </span>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="billing_phone"
                                            class="block text-sm font-medium text-gray-700">Phone</label>
                                        <input type="tel" id="billing_phone" v-model="form.billing_address.phone"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            required />
                                        <span v-if="errors['billing_address.phone']" class="text-red-500 text-sm">
                                            {{ errors['billing_address.phone'] }}
                                        </span>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="billing_address_1"
                                            class="block text-sm font-medium text-gray-700">Address Line 1</label>
                                        <input type="text" id="billing_address_1"
                                            v-model="form.billing_address.address_line_1"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            required />
                                        <span v-if="errors['billing_address.address_line_1']"
                                            class="text-red-500 text-sm">
                                            {{ errors['billing_address.address_line_1'] }}
                                        </span>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="billing_address_2"
                                            class="block text-sm font-medium text-gray-700">Address Line 2
                                            (Optional)</label>
                                        <input type="text" id="billing_address_2"
                                            v-model="form.billing_address.address_line_2"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                                    </div>

                                    <div>
                                        <label for="billing_city"
                                            class="block text-sm font-medium text-gray-700">City</label>
                                        <input type="text" id="billing_city" v-model="form.billing_address.city"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            required />
                                        <span v-if="errors['billing_address.city']" class="text-red-500 text-sm">
                                            {{ errors['billing_address.city'] }}
                                        </span>
                                    </div>

                                    <div>
                                        <label for="billing_state"
                                            class="block text-sm font-medium text-gray-700">State</label>
                                        <input type="text" id="billing_state" v-model="form.billing_address.state"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            required />
                                        <span v-if="errors['billing_address.state']" class="text-red-500 text-sm">
                                            {{ errors['billing_address.state'] }}
                                        </span>
                                    </div>

                                    <div>
                                        <label for="billing_postal_code"
                                            class="block text-sm font-medium text-gray-700">Postal Code</label>
                                        <input type="text" id="billing_postal_code"
                                            v-model="form.billing_address.postal_code"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            required />
                                        <span v-if="errors['billing_address.postal_code']" class="text-red-500 text-sm">
                                            {{ errors['billing_address.postal_code'] }}
                                        </span>
                                    </div>

                                    <div>
                                        <label for="billing_country"
                                            class="block text-sm font-medium text-gray-700">Country</label>
                                        <select id="billing_country" v-model="form.billing_address.country"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            required>
                                            <option value="">Select Country</option>
                                            <option value="US">United States</option>
                                            <option value="CA">Canada</option>
                                            <option value="GB">United Kingdom</option>
                                            <option value="AU">Australia</option>
                                            <!-- Add more countries as needed -->
                                        </select>
                                        <span v-if="errors['billing_address.country']" class="text-red-500 text-sm">
                                            {{ errors['billing_address.country'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Shipping Address -->
                            <div class="bg-white shadow rounded-lg p-6">
                                <h2 class="text-lg font-medium text-gray-900 mb-4">Shipping Address</h2>

                                <div class="mb-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" v-model="form.shipping_same_as_billing"
                                            @change="copyBillingToShipping"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                        <span class="ml-2 text-sm text-gray-700">Same as billing address</span>
                                    </label>
                                </div>

                                <div v-if="!form.shipping_same_as_billing"
                                    class="grid grid-cols-1 gap-y-4 sm:grid-cols-2 sm:gap-x-4">
                                    <div>
                                        <label for="shipping_first_name"
                                            class="block text-sm font-medium text-gray-700">First Name</label>
                                        <input type="text" id="shipping_first_name"
                                            v-model="form.shipping_address.first_name"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            required />
                                        <span v-if="errors['shipping_address.first_name']" class="text-red-500 text-sm">
                                            {{ errors['shipping_address.first_name'] }}
                                        </span>
                                    </div>

                                    <div>
                                        <label for="shipping_last_name"
                                            class="block text-sm font-medium text-gray-700">Last Name</label>
                                        <input type="text" id="shipping_last_name"
                                            v-model="form.shipping_address.last_name"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            required />
                                        <span v-if="errors['shipping_address.last_name']" class="text-red-500 text-sm">
                                            {{ errors['shipping_address.last_name'] }}
                                        </span>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="shipping_email"
                                            class="block text-sm font-medium text-gray-700">Email</label>
                                        <input type="email" id="shipping_email" v-model="form.shipping_address.email"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            required />
                                        <span v-if="errors['shipping_address.email']" class="text-red-500 text-sm">
                                            {{ errors['shipping_address.email'] }}
                                        </span>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="shipping_phone"
                                            class="block text-sm font-medium text-gray-700">Phone</label>
                                        <input type="tel" id="shipping_phone" v-model="form.shipping_address.phone"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            required />
                                        <span v-if="errors['shipping_address.phone']" class="text-red-500 text-sm">
                                            {{ errors['shipping_address.phone'] }}
                                        </span>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="shipping_address_1"
                                            class="block text-sm font-medium text-gray-700">Address Line 1</label>
                                        <input type="text" id="shipping_address_1"
                                            v-model="form.shipping_address.address_line_1"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            required />
                                        <span v-if="errors['shipping_address.address_line_1']"
                                            class="text-red-500 text-sm">
                                            {{ errors['shipping_address.address_line_1'] }}
                                        </span>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="shipping_address_2"
                                            class="block text-sm font-medium text-gray-700">Address Line 2
                                            (Optional)</label>
                                        <input type="text" id="shipping_address_2"
                                            v-model="form.shipping_address.address_line_2"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                                    </div>

                                    <div>
                                        <label for="shipping_city"
                                            class="block text-sm font-medium text-gray-700">City</label>
                                        <input type="text" id="shipping_city" v-model="form.shipping_address.city"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            required />
                                        <span v-if="errors['shipping_address.city']" class="text-red-500 text-sm">
                                            {{ errors['shipping_address.city'] }}
                                        </span>
                                    </div>

                                    <div>
                                        <label for="shipping_state"
                                            class="block text-sm font-medium text-gray-700">State</label>
                                        <input type="text" id="shipping_state" v-model="form.shipping_address.state"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            required />
                                        <span v-if="errors['shipping_address.state']" class="text-red-500 text-sm">
                                            {{ errors['shipping_address.state'] }}
                                        </span>
                                    </div>

                                    <div>
                                        <label for="shipping_postal_code"
                                            class="block text-sm font-medium text-gray-700">Postal Code</label>
                                        <input type="text" id="shipping_postal_code"
                                            v-model="form.shipping_address.postal_code"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            required />
                                        <span v-if="errors['shipping_address.postal_code']"
                                            class="text-red-500 text-sm">
                                            {{ errors['shipping_address.postal_code'] }}
                                        </span>
                                    </div>

                                    <div>
                                        <label for="shipping_country"
                                            class="block text-sm font-medium text-gray-700">Country</label>
                                        <select id="shipping_country" v-model="form.shipping_address.country"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            required>
                                            <option value="">Select Country</option>
                                            <option value="US">United States</option>
                                            <option value="CA">Canada</option>
                                            <option value="GB">United Kingdom</option>
                                            <option value="AU">Australia</option>
                                            <!-- Add more countries as needed -->
                                        </select>
                                        <span v-if="errors['shipping_address.country']" class="text-red-500 text-sm">
                                            {{ errors['shipping_address.country'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="bg-white shadow rounded-lg p-6">
                                <h2 class="text-lg font-medium text-gray-900 mb-4">Payment Method</h2>

                                <div class="space-y-4">
                                    <label class="flex items-center">
                                        <input type="radio" v-model="form.payment_method" value="cash_on_delivery"
                                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" />
                                        <span class="ml-2 text-sm text-gray-700">Cash on Delivery</span>
                                    </label>

                                    <label class="flex items-center">
                                        <input type="radio" v-model="form.payment_method" value="card"
                                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" />
                                        <span class="ml-2 text-sm text-gray-700">Credit/Debit Card</span>
                                    </label>

                                    <label class="flex items-center">
                                        <input type="radio" v-model="form.payment_method" value="paypal"
                                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" />
                                        <span class="ml-2 text-sm text-gray-700">PayPal</span>
                                    </label>
                                </div>

                                <span v-if="errors.payment_method" class="text-red-500 text-sm">
                                    {{ errors.payment_method }}
                                </span>
                            </div>

                            <!-- Order Notes -->
                            <div class="bg-white shadow rounded-lg p-6">
                                <h2 class="text-lg font-medium text-gray-900 mb-4">Order Notes (Optional)</h2>

                                <textarea v-model="form.notes" rows="4"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Special delivery instructions, etc."></textarea>
                            </div>
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
