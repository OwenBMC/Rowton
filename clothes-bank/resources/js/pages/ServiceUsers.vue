<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'

import AppLayout from '@/layouts/AppLayout.vue';

const users = ref([])
const search = ref('')
const housingFilter = ref('')
const blacklistFilter = ref('')
const loading = ref(false)


async function fetchUsers() {
  loading.value = true

  const res = await axios.get('/api/service-users', {
    params: {
      search: search.value,
      housing_status: housingFilter.value,
      blacklisted: blacklistFilter.value,
    }
  })

  users.value = res.data.data
  console.log(res)
  loading.value = false
}

onMounted(fetchUsers)

watch([search, housingFilter, blacklistFilter], () => {
  fetchUsers()
})

async function updateUser(user) {
  await axios.put(`/api/service-users/${user.id}`, user)
}
</script>

<template>
  <AppLayout>

  <div class="mb-4 flex gap-2">
    <input v-model="search" placeholder="Search users..." class="input" />

    <select v-model="housingFilter" class="input">
      <option value="">All Housing</option>
      <option value="unknown">Unknown</option>
      <option value="housed">Housed</option>
      <option value="housed">Hostel</option>
      <option value="homeless">Rough Sleeper</option>
      <option value="temporary">Temporary</option>
    </select>

    <select v-model="blacklistFilter" class="input">
      <option value="">All</option>
      <option value="true">Blacklisted</option>
      <option value="false">Not Blacklisted</option>
    </select>
  </div>
    <table class="w-full border">
    <thead>
      <tr>
        <th>Name</th>
        <th>DOB</th>
        <th>Contact</th>
        <th>Address</th>
        <th>Housing</th>
        <th>Blacklisted</th>
        <th>Actions</th>
      </tr>
    </thead>

    <tbody>
      <tr v-for="user in users" :key="user.id">
        <td>
          <input v-model="user.first_name" />
          <input v-model="user.surname" />
        </td>

        <td>
          <input v-model="user.dob" />
        </td>

        <td>
          <input v-model="user.contact_number" />
        </td>

        <td>
          <input v-model="user.address" />
        </td>

        <td>
          <select v-model="user.housing_status">
            <option value="unknown">Unknown</option>
            <option value="housed">Housed</option>
            <option value="homeless">Homeless</option>
            <option value="temporary">Temporary</option>
          </select>
        </td>

        <td>
          <input type="checkbox" v-model="user.is_blacklisted" />
        </td>

        <td>
          <button @click="updateUser(user)" class="btn">
            Save
          </button>
        </td>
      </tr>
    </tbody>
  </table>
  </AppLayout>
</template>