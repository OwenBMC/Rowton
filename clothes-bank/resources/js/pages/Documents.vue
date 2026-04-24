<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

defineOptions({
  layout: AppLayout,
})
</script>

<template>
  <div class="p-8 max-w-6xl mx-auto">
    <h1 class="text-3xl font-bold mb-8">Documents</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <Link
        href="/documents/registration-forms"
        class="border rounded-xl p-6 shadow hover:shadow-lg transition"
      >
        <h2 class="text-xl font-semibold mb-2">
          Registration Forms
        </h2>
        <p class="text-gray-600">
          View completed registrations and create new ones.
        </p>
      </Link>
    </div>
  </div>
</template>