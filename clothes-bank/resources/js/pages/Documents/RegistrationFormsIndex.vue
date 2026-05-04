<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

defineOptions({
  layout: AppLayout,
})

defineProps<{
  completed: any[],
  unregistered: any[]
}>()
</script>

<template>
    <h1 class="text-3xl font-bold mb-8">Registration Forms</h1>

    <Link href="/registration" class="mb-4 px-3 py-1 bg-blue-600 text-white rounded">New Registration</Link>

    <!-- Completed -->
    <div class="mb-12">
      <h2 class="text-xl font-semibold mb-4">Completed Registrations</h2>

      <table class="w-full border rounded">
        <thead class="bg-gray-100">
          <tr>
            <th class="p-3 text-left">Name</th>
            <th class="p-3 text-left">Created</th>
            <th class="p-3 text-left">Actions</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="registration in completed"
            :key="registration.id"
            class="border-t"
          >
            <td class="p-3">
              {{ registration.service_user.first_name }}
              {{ registration.service_user.surname }}
            </td>

            <td class="p-3">
              {{ registration.created_at }}
            </td>

            <td class="p-3">
              <Link
                :href="`/registration/view/${registration.id}`"
                class="px-3 py-1 bg-gray-600 text-white rounded"
              >
                View
              </Link>
            </td>
          </tr>

          <tr v-if="!completed.length">
            <td colspan="3" class="p-3 text-gray-500">
              No completed registrations.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Unregistered -->
    <div>
      <h2 class="text-xl font-semibold mb-4">Users Without Registration</h2>

      <table class="w-full border rounded">
        <thead class="bg-gray-100">
          <tr>
            <th class="p-3 text-left">Name</th>
            <th class="p-3 text-left">Actions</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="user in unregistered"
            :key="user.id"
            class="border-t"
          >
            <td class="p-3">
              {{ user.first_name }} {{ user.surname }}
            </td>

            <td class="p-3">
              <Link
                :href="`/registration/${user.id}`"
                class="px-3 py-1 bg-blue-600 text-white rounded"
              >
                Create
              </Link>
            </td>
          </tr>

          <tr v-if="!unregistered.length">
            <td colspan="2" class="p-3 text-gray-500">
              All users have registrations.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
</template>