<template>
  <Card>
    <CardHeader>
      <CardTitle>Recent Activity</CardTitle>
    </CardHeader>
    <CardContent>
      <div class="text-center py-8">
        <FileText class="mx-auto h-12 w-12 text-gray-400 mb-4" />
        <h3 class="text-sm font-medium text-gray-900">No activity logs</h3>
        <p class="mt-1 text-sm text-gray-500">Activity logging is not yet implemented.</p>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { FileText } from 'lucide-vue-next';
</script>
