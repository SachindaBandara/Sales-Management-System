<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ShoppingBag, Mail, Phone, MapPin, Clock, Send, MessageSquare, HelpCircle, Truck } from 'lucide-vue-next';

const inquiryTypes = [
    { value: 'general', label: 'General Inquiry' },
    { value: 'support', label: 'Customer Support' },
    { value: 'orders', label: 'Order Status' },
    { value: 'returns', label: 'Returns & Refunds' },
    { value: 'partnership', label: 'Business Partnership' },
    { value: 'press', label: 'Press & Media' }
];

const contactInfo = [
    {
        icon: Mail,
        title: 'Email Us',
        details: 'hello@shopvue.com',
        description: 'Send us an email anytime'
    },
    {
        icon: Phone,
        title: 'Call Us',
        details: '+1 (555) 123-4567',
        description: 'Mon-Fri from 8am to 6pm EST'
    },
    {
        icon: MapPin,
        title: 'Visit Us',
        details: '123 Commerce Street, Suite 100\nNew York, NY 10001',
        description: 'Our headquarters'
    },
    {
        icon: Clock,
        title: 'Business Hours',
        details: 'Monday - Friday: 8am - 6pm EST\nSaturday: 9am - 4pm EST\nSunday: Closed',
        description: 'Customer service hours'
    }
];

const supportCategories = [
    {
        icon: MessageSquare,
        title: 'Live Chat',
        description: 'Get instant help from our support team',
        action: 'Start Chat',
        available: true
    },
    {
        icon: HelpCircle,
        title: 'Help Center',
        description: 'Browse our comprehensive FAQ and guides',
        action: 'Visit Help Center',
        available: true
    },
    {
        icon: Truck,
        title: 'Order Tracking',
        description: 'Track your order status and delivery',
        action: 'Track Order',
        available: true
    }
];

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
    inquiry_type: 'general'
});

const submitForm = () => {
    form.post('/contact', {
        onSuccess: () => {
            // Handle success
            alert('Thank you for your message! We\'ll get back to you soon.');
            form.reset();
        },
        onError: () => {
            // Handle errors
            alert('There was an error sending your message. Please try again.');
        }
    });
};
</script>

<template>
    <Head title="Contact Us - ShopVue">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    
    <div class="min-h-screen bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]">
        <!-- Header -->
        <header class="border-b border-[#19140035] dark:border-[#3E3E3A]">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo -->
                    <Link href="/" class="flex items-center gap-2">
                        <ShoppingBag class="h-8 w-8 text-[#1b1b18] dark:text-[#EDEDEC]" />
                        <span class="text-xl font-semibold">ShopVue</span>
                    </Link>
                    
                    <!-- Navigation -->
                    <nav class="hidden md:flex items-center gap-8">
                        <Link href="/products" class="text-sm hover:text-gray-600 dark:hover:text-gray-300">
                            Products
                        </Link>
                        <Link href="/categories" class="text-sm hover:text-gray-600 dark:hover:text-gray-300">
                            Categories
                        </Link>
                        <Link href="/about" class="text-sm hover:text-gray-600 dark:hover:text-gray-300">
                            About
                        </Link>
                        <Link href="/contact" class="text-sm font-semibold border-b-2 border-[#1b1b18] dark:border-[#EDEDEC]">
                            Contact
                        </Link>
                    </nav>
                    
                    <!-- Auth Links -->
                    <div class="flex items-center gap-4">
                        <Link
                            v-if="$page.props.auth?.user"
                            :href="route('dashboard')"
                            class="inline-flex items-center gap-2 rounded-sm border border-[#19140035] px-4 py-2 text-sm leading-normal hover:border-[#1915014a] dark:border-[#3E3E3A] dark:hover:border-[#62605b]"
                        >
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="inline-block rounded-sm border border-transparent px-4 py-2 text-sm leading-normal hover:border-[#19140035] dark:hover:border-[#3E3E3A]"
                            >
                                Log in
                            </Link>
                            <Link
                                :href="route('register')"
                                class="inline-block rounded-sm border border-[#19140035] px-4 py-2 text-sm leading-normal hover:border-[#1915014a] dark:border-[#3E3E3A] dark:hover:border-[#62605b]"
                            >
                                Register
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="py-20 lg:py-32">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <h1 class="text-4xl font-bold tracking-tight sm:text-6xl lg:text-7xl">
                        Get in
                        <span class="text-gray-600 dark:text-gray-400">Touch</span>
                    </h1>
                    <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-400">
                        We're here to help! Whether you have questions about our products, need support, 
                        or want to share feedback, we'd love to hear from you.
                    </p>
                </div>
            </div>
        </section>

        <!-- Quick Support Options -->
        <section class="border-t border-[#19140035] py-16 dark:border-[#3E3E3A]">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Quick Support</h2>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                        Need immediate assistance? Try these options
                    </p>
                </div>
                
                <div class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-8 sm:mt-20 lg:mx-0 lg:max-w-none lg:grid-cols-3">
                    <div
                        v-for="category in supportCategories"
                        :key="category.title"
                        class="rounded-lg border border-[#19140035] bg-white p-8 text-center hover:border-[#1915014a] dark:border-[#3E3E3A] dark:bg-[#1a1a1a] dark:hover:border-[#62605b]"
                    >
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                            <component :is="category.icon" class="h-8 w-8 text-[#1b1b18] dark:text-[#EDEDEC]" />
                        </div>
                        <h3 class="mt-6 text-lg font-semibold">{{ category.title }}</h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">{{ category.description }}</p>
                        <button
                            class="mt-6 rounded-sm bg-[#1b1b18] px-4 py-2 text-sm font-semibold text-white hover:bg-[#2a2a27] dark:bg-[#EDEDEC] dark:text-[#1b1b18] dark:hover:bg-[#d4d4d1]"
                            :class="{ 'opacity-50 cursor-not-allowed': !category.available }"
                            :disabled="!category.available"
                        >
                            {{ category.action }}
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Form and Info -->
        <section class="py-16 lg:py-24">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-16 lg:grid-cols-2">
                    <!-- Contact Form -->
                    <div>
                        <h2 class="text-3xl font-bold tracking-tight">Send us a Message</h2>
                        <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                            Fill out the form below and we'll get back to you within 24 hours.
                        </p>
                        
                        <form @submit.prevent="submitForm" class="mt-8 space-y-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="name" class="block text-sm font-medium">
                                        Full Name *
                                    </label>
                                    <input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        required
                                        class="mt-2 block w-full rounded-sm border border-[#19140035] px-4 py-3 text-sm focus:border-[#1915014a] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#1a1a1a] dark:focus:border-[#62605b]"
                                        placeholder="Enter your full name"
                                    />
                                    <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.name }}
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="email" class="block text-sm font-medium">
                                        Email Address *
                                    </label>
                                    <input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        required
                                        class="mt-2 block w-full rounded-sm border border-[#19140035] px-4 py-3 text-sm focus:border-[#1915014a] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#1a1a1a] dark:focus:border-[#62605b]"
                                        placeholder="Enter your email"
                                    />
                                    <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.email }}
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label for="inquiry_type" class="block text-sm font-medium">
                                    Inquiry Type
                                </label>
                                <select
                                    id="inquiry_type"
                                    v-model="form.inquiry_type"
                                    class="mt-2 block w-full rounded-sm border border-[#19140035] px-4 py-3 text-sm focus:border-[#1915014a] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#1a1a1a] dark:focus:border-[#62605b]"
                                >
                                    <option
                                        v-for="type in inquiryTypes"
                                        :key="type.value"
                                        :value="type.value"
                                    >
                                        {{ type.label }}
                                    </option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="subject" class="block text-sm font-medium">
                                    Subject *
                                </label>
                                <input
                                    id="subject"
                                    v-model="form.subject"
                                    type="text"
                                    required
                                    class="mt-2 block w-full rounded-sm border border-[#19140035] px-4 py-3 text-sm focus:border-[#1915014a] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#1a1a1a] dark:focus:border-[#62605b]"
                                    placeholder="Brief description of your inquiry"
                                />
                                <div v-if="form.errors.subject" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.subject }}
                                </div>
                            </div>
                            
                            <div>
                                <label for="message" class="block text-sm font-medium">
                                    Message *
                                </label>
                                <textarea
                                    id="message"
                                    v-model="form.message"
                                    rows="6"
                                    required
                                    class="mt-2 block w-full rounded-sm border border-[#19140035] px-4 py-3 text-sm focus:border-[#1915014a] focus:outline-none dark:border-[#3E3E3A] dark:bg-[#1a1a1a] dark:focus:border-[#62605b]"
                                    placeholder="Please provide details about your inquiry..."
                                ></textarea>
                                <div v-if="form.errors.message" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.message }}
                                </div>
                            </div>
                            
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-sm bg-[#1b1b18] px-6 py-3 text-sm font-semibold text-white hover:bg-[#2a2a27] disabled:opacity-50 dark:bg-[#EDEDEC] dark:text-[#1b1b18] dark:hover:bg-[#d4d4d1]"
                            >
                                <Send class="h-4 w-4" />
                                {{ form.processing ? 'Sending...' : 'Send Message' }}
                            </button>
                        </form>
                    </div>
                    
                    <!-- Contact Information -->
                    <div>
                        <h2 class="text-3xl font-bold tracking-tight">Contact Information</h2>
                        <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                            Prefer to reach out directly? Here are all the ways to get in touch.
                        </p>
                        
                        <div class="mt-8 space-y-8">
                            <div
                                v-for="info in contactInfo"
                                :key="info.title"
                                class="flex gap-4"
                            >
                                <div class="flex-shrink-0">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                        <component :is="info.icon" class="h-6 w-6 text-gray-400" />
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold">{{ info.title }}</h3>
                                    <p class="mt-1 whitespace-pre-line text-gray-600 dark:text-gray-400">{{ info.details }}</p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-500">{{ info.description }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Map Placeholder -->
                        <div class="mt-12">
                            <h3 class="text-lg font-semibold">Find Us</h3>
                            <div class="mt-4 aspect-video rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                <div class="text-center">
                                    <Mail class="mx-auto h-12 w-12 text-gray-400" />
                                    <p class="mt-2 text-sm text-gray-500">Interactive map coming soon</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="border-t border-[#19140035] py-16 dark:border-[#3E3E3A] lg:py-24">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Frequently Asked Questions</h2>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                        Quick answers to common questions
                    </p>
                </div>
                
                <div class="mx-auto mt-16 max-w-3xl space-y-8">
                    <div class="rounded-lg border border-[#19140035] bg-white p-6 dark:border-[#3E3E3A] dark:bg-[#1a1a1a]">
                        <h3 class="text-lg font-semibold">What are your shipping options?</h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            We offer free standard shipping on orders over $50. Express shipping is available for $9.99, 
                            and overnight shipping for $19.99. International shipping rates vary by location.
                        </p>
                    </div>
                    
                    <div class="rounded-lg border border-[#19140035] bg-white p-6 dark:border-[#3E3E3A] dark:bg-[#1a1a1a]">
                        <h3 class="text-lg font-semibold">What is your return policy?</h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            We offer a 30-day hassle-free return policy. Items must be in original condition with tags attached. 
                            Return shipping is free for defective items, otherwise a $5.99 return fee applies.
                        </p>
                    </div>
                    
                    <div class="rounded-lg border border-[#19140035] bg-white p-6 dark:border-[#3E3E3A] dark:bg-[#1a1a1a]">
                        <h3 class="text-lg font-semibold">How can I track my order?</h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Once your order ships, you'll receive a tracking number via email. You can also track your order 
                            by logging into your account or using our order tracking tool above.
                        </p>
                    </div>
                    
                    <div class="rounded-lg border border-[#19140035] bg-white p-6 dark:border-[#3E3E3A] dark:bg-[#1a1a1a]">
                        <h3 class="text-lg font-semibold">Do you offer customer support?</h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Yes! Our customer support team is available Monday-Friday 8am-6pm EST, and Saturday 9am-4pm EST. 
                            You can reach us via live chat, email, or phone.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-[#19140035] py-12 dark:border-[#3E3E3A]">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <ShoppingBag class="h-6 w-6" />
                            <span class="text-lg font-semibold">ShopVue</span>
                        </div>
                        <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                            Your trusted partner for premium products and exceptional shopping experience.
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-sm font-semibold">Shop</h3>
                        <ul class="mt-4 space-y-2">
                            <li><Link href="/products" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">All Products</Link></li>
                            <li><Link href="/categories" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">Categories</Link></li>
                            <li><Link href="/deals" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">Deals</Link></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-sm font-semibold">Support</h3>
                        <ul class="mt-4 space-y-2">
                            <li><Link href="/help" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">Help Center</Link></li>
                            <li><Link href="/contact" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">Contact Us</Link></li>
                            <li><Link href="/returns" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">Returns</Link></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-sm font-semibold">Company</h3>
                        <ul class="mt-4 space-y-2">
                            <li><Link href="/about" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">About</Link></li>
                            <li><Link href="/careers" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">Careers</Link></li>
                            <li><Link href="/privacy" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">Privacy</Link></li>
                        </ul>
                    </div>
                </div>
                
                <div class="mt-8 border-t border-[#19140035] pt-8 dark:border-[#3E3E3A]">
                    <p class="text-center text-sm text-gray-600 dark:text-gray-400">
                        © 2024 ShopVue. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>