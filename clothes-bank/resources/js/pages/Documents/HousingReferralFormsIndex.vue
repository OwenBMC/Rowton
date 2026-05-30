<template>
  <Head title="Housing Referrals" />

  <AppLayout :breadcrumbs="breadcrumbs">

    <!-- DATE SELECT -->
    <div class="flex items-center gap-3 mb-4">
      <label class="font-semibold">Select Date:</label>
      <input type="date" v-model="selectedDate" class="border p-1 rounded" />
    </div>

    <!-- TOP TABLE: TODAY / SELECTED DATE REFERRALS -->
    <h3 class="text-lg font-bold mb-2">
      Housing Referrals for {{ selectedDate }}
    </h3>

    <table class="table-auto border-collapse border w-full mb-6">
      <thead>
        <tr>
          <th class="border p-2 text-left">Name</th>
          <th class="border p-2 text-left">Outcome</th>
          <th class="border p-2 text-left">Actions</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="ref in referralsForDate" :key="ref.id">
          <td class="border p-2">{{ ref.service_user?.name }}</td>
          <td class="border p-2">{{ ref.outcome || 'Pending' }}</td>

          <td class="border p-2">
            <button class="btn btn-primary" @click="editReferral(ref)">
              Edit
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- SECOND SECTION: ELIGIBLE USERS -->
    <h3 class="text-lg font-bold mb-2">
      Registered Service Users (Eligible for Referral)
    </h3>

    <!-- SEARCH -->
    <input
      v-model="search"
      placeholder="Search users..."
      class="border p-2 w-full mb-3"
    />

    <table class="table-auto border-collapse border w-full">
      <thead>
        <tr>
          <th class="border p-2 text-left">Name</th>
          <th class="border p-2 text-left">Action</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="user in filteredUsers" :key="user.id">
          <td class="border p-2">{{ user.name }}</td>

          <td class="border p-2">
            <Link
                :href="`/registration/${user.id}`"
                class="px-3 py-1 bg-blue-600 text-white rounded"
              >
                Create
              </Link>
            <button class="btn btn-primary" @click="createReferral(user)">
              New Referral
            </button>
          </td>
        </tr>
      </tbody>
    </table>

  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';

const breadcrumbs = [
  { title: 'Housing Referrals', href: '/housing-referrals' },
];

const selectedDate = ref(new Date().toISOString().split('T')[0]);

const referralsForDate = ref<any[]>([]);
const registeredUsers = ref<any[]>([]);
const search = ref('');

/**
 * LOAD DATA WHEN DATE CHANGES
 */
async function loadReferrals(date: string) {
  const res = await axios.get('/api/housing-referrals', {
    params: { date },
  });

  referralsForDate.value = res.data;
}

/**
 * LOAD REGISTERED USERS (eligible only)
 */
async function loadRegisteredUsers() {
  const res = await axios.get('/api/service-users/registered');
  registeredUsers.value = res.data;
}

onMounted(async () => {
  await loadReferrals(selectedDate.value);
  await loadRegisteredUsers();
});

watch(selectedDate, async (newDate) => {
  await loadReferrals(newDate);
});

/**
 * FILTERED USERS
 */
const filteredUsers = computed(() => {
    const referredThisDate = referralsForDate.value?.map(referral => referral.service_user?.id)
  if (!search.value) return registeredUsers.value.filter(u => !referredThisDate.includes(u.id));

  return registeredUsers.value.filter(u =>
    u.name.toLowerCase().includes(search.value.toLowerCase()) && !referredThisDate.includes(u.id)
  );
});

/**
 * CREATE NEW REFERRAL (redirect to form with prefill)
 */
function createReferral(user: any) {
  router.visit(`/housing-referrals/create?user_id=${user.id}`);
}

/**
 * EDIT EXISTING REFERRAL (reopen form with data)
 */
function editReferral(ref: any) {
  router.visit(`/housing-referrals/${ref.id}/edit`);
}
</script>