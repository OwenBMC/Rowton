<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const activeBlacklist = ref<any[]>([]);
const eligibleUsers = ref<any[]>([]);

const search = ref('');
const openFormUserId = ref<number | null>(null);

const successMessage = ref('');
const errorMessage = ref('');

/**
 * Per-user form state (keyed by user id)
 */
const blacklistForms = ref<Record<number, any>>({});

/**
 * LOAD DATA
 */
const loadData = async () => {
  const [blacklistRes, usersRes] = await Promise.all([
    axios.get('/api/blacklist'),
    axios.get('/api/blacklist/eligible-users'),
  ]);

  activeBlacklist.value = blacklistRes.data;
  eligibleUsers.value = usersRes.data;

  // initialise form defaults
  usersRes.data.forEach((u: any) => {
    if (!blacklistForms.value[u.id]) {
      blacklistForms.value[u.id] = {
        service_user_id: u.id,
        blacklist_start_date: new Date().toISOString().split('T')[0],
        blacklist_end_date: null,
        note: '',
      };
    }
  });
};

onMounted(loadData);

/**
 * FILTER USERS
 */
const filteredUsers = computed(() => {
  if (!search.value) return eligibleUsers.value;

  return eligibleUsers.value.filter((u) =>
    `${u.first_name} ${u.surname}`
      .toLowerCase()
      .includes(search.value.toLowerCase())
  );
});

/**
 * OPEN FORM
 */
const toggleForm = (userId: number) => {
  openFormUserId.value = openFormUserId.value === userId ? null : userId;
};

/**
 * SUBMIT BLACKLIST
 */
const submitBlacklist = async (userId: number) => {
  try {
    const payload = blacklistForms.value[userId];

    await axios.post('/api/blacklist', payload);

    successMessage.value = 'User blacklisted';
    errorMessage.value = '';

    openFormUserId.value = null;

    await loadData();
  } catch (err) {
    successMessage.value = '';
    errorMessage.value = 'Failed to blacklist user';
  }
};

/**
 * REMOVE BLACKLIST
 */
const removeBlacklist = async (entry: any) => {
  await axios.delete(`/api/blacklist/${entry.id}`);
  await loadData();
};
</script>

<template>
  <div class="p-4 space-y-10">

    <h1 class="text-2xl font-bold">Blacklist Management</h1>

    <!-- ACTIVE BLACKLIST -->
    <section>
      <h2 class="text-lg font-semibold mb-3">
        Currently Blacklisted Users
      </h2>

      <table class="w-full border-collapse border">
        <thead>
          <tr>
            <th class="border p-2 text-left">Name</th>
            <th class="border p-2 text-left">Start</th>
            <th class="border p-2 text-left">End</th>
            <th class="border p-2 text-left">Note</th>
            <th class="border p-2 text-left">Action</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="entry in activeBlacklist" :key="entry.id">
            <td class="border p-2">
              {{ entry.service_user?.first_name }} {{ entry.service_user?.surname }}
            </td>

            <td class="border p-2">
              {{ entry.blacklist_start_date }}
            </td>

            <td class="border p-2">
              {{ entry.blacklist_end_date ?? 'Active' }}
            </td>

            <td class="border p-2">
              {{ entry.note || '-' }}
            </td>

            <td class="border p-2">
              <button
                class="bg-red-600 text-white px-2 py-1 rounded"
                @click="removeBlacklist(entry)"
              >
                End
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </section>

    <!-- SEARCH USERS -->
    <section>
      <h2 class="text-lg font-semibold mb-3">
        Search Service Users
      </h2>

      <input
        v-model="search"
        class="border p-2 w-full mb-4"
        placeholder="Search users..."
      />

      <table class="w-full border-collapse border">
        <thead>
          <tr>
            <th class="border p-2 text-left">Name</th>
            <th class="border p-2 text-left">Action</th>
          </tr>
        </thead>

        <tbody>
          <template v-for="user in filteredUsers" :key="user.id">
            <tr>
              <td class="border p-2">
                {{ user.first_name }} {{ user.surname }}
              </td>

              <td class="border p-2">
                <button
                  class="bg-black text-white px-3 py-1 rounded"
                  @click="toggleForm(user.id)"
                >
                  Blacklist
                </button>
              </td>
            </tr>

            <!-- EXPANDABLE FORM ROW -->
            <tr v-if="openFormUserId === user.id">
              <td colspan="2" class="border p-3 bg-gray-50">
                
                <div class="grid grid-cols-3 gap-3">

                  <!-- Start Date -->
                  <div>
                    <label class="text-sm">Start Date</label>
                    <input
                      type="date"
                      v-model="blacklistForms[user.id].blacklist_start_date"
                      class="border p-2 w-full"
                    />
                  </div>

                  <!-- End Date -->
                  <div>
                    <label class="text-sm">End Date</label>
                    <input
                      type="date"
                      v-model="blacklistForms[user.id].blacklist_end_date"
                      class="border p-2 w-full"
                    />
                  </div>

                  <!-- Note -->
                  <div>
                    <label class="text-sm">Note</label>
                    <input
                      v-model="blacklistForms[user.id].note"
                      class="border p-2 w-full"
                      placeholder="Reason..."
                    />
                  </div>
                </div>

                <div class="mt-3 flex gap-2">
                  <button
                    class="bg-green-600 text-white px-3 py-1 rounded"
                    @click="submitBlacklist(user.id)"
                  >
                    Confirm Blacklist
                  </button>

                  <button
                    class="bg-gray-400 text-white px-3 py-1 rounded"
                    @click="openFormUserId = null"
                  >
                    Cancel
                  </button>
                </div>

              </td>
            </tr>
          </template>

          <tr v-if="filteredUsers.length === 0">
            <td colspan="2" class="p-3 text-gray-500">
              No users found
            </td>
          </tr>
        </tbody>
      </table>
    </section>

    <!-- MESSAGES -->
    <p v-if="successMessage" class="text-green-600">
      {{ successMessage }}
    </p>

    <p v-if="errorMessage" class="text-red-600">
      {{ errorMessage }}
    </p>

  </div>
</template>