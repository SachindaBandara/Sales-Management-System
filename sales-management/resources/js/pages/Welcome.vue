<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import Header from '@/components/header/Header.vue'


</script>

<template>
  <Head title="Welcome">
    <link rel="preconnect" href="https://rsms.me/" />
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
  </Head>
  <div class="flex min-h-screen flex-col bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]">
    <Header />
 </div>
</template>
