<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import axios from 'axios'
import AppLayout from '@/layouts/AppLayout.vue';

const users = ref([])
const search = ref('')
const housingFilter = ref('')
const blacklistFilter = ref('')
const loading = ref(false)

// --- Pagination State ---
const currentPage = ref(1)
const lastPage = ref(1)
const totalRecords = ref(0)

async function fetchUsers(page = 1) {
  loading.value = true
  try {
    const res = await axios.get('/api/service-users', {
      params: {
        page: page, // Send page to Laravel
        search: search.value,
        housing_status: housingFilter.value,
        blacklisted: blacklistFilter.value,
      }
    })

    // Assuming Laravel standard LengthAwarePaginator
    users.value = res.data.data
    currentPage.value = res.data.current_page
    lastPage.value = res.data.last_page
    totalRecords.value = res.data.total
  } catch (err) {
    console.error("Fetch failed", err)
  } finally {
    loading.value = false
  }
}

onMounted(() => fetchUsers())

// Watch filters and reset to page 1 on change
watch([search, housingFilter, blacklistFilter], () => {
  fetchUsers(1)
})

async function updateUser(user) {
  try {
    await axios.put(`/api/service-users/${user.id}`, user)
    alert('User updated successfully')
  } catch (err) {
    alert('Update failed')
  }
}

async function deleteUser(user) {
  if (!confirm(`Are you sure you want to permanently delete ${user.first_name} ${user.surname}?`)) return;
  
  try {
    await axios.delete(`/api/service-users/${user.id}`)
    fetchUsers(currentPage.value) // Refresh current view
  } catch (err) {
    alert('Delete failed. User might be linked to attendance records.')
  }
}

// --- DOB Helper Logic ---
const currentYear = new Date().getFullYear();
const years = Array.from({ length: 100 }, (_, i) => currentYear - i);
const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

// Helper to handle DOB string splitting/joining
function updateDob(user, part, value) {
  let [y, m, d] = (user.dob || "1990-01-01").split('-');
  if (part === 'y') y = value;
  if (part === 'm') m = value.toString().padStart(2, '0');
  if (part === 'd') d = value.toString().padStart(2, '0');
  user.dob = `${y}-${m}-${d}`;
}
</script>

<template>
  <AppLayout>
    <div class="mb-4 flex flex-wrap gap-2 items-center bg-gray-50 p-4 rounded border">
      <input v-model="search" placeholder="Search by name or nickname..." class="input border p-2 rounded" />

      <select v-model="housingFilter" class="input border p-2 rounded">
        <option value="">All Housing</option>
        <option value="unknown">Unknown</option>
        <option value="housed">Housed</option>
        <option value="hostel">Hostel</option>
        <option value="homeless">Rough Sleeper</option>
        <option value="temporary">Temporary</option>
      </select>

      <select v-model="blacklistFilter" class="input border p-2 rounded">
        <option value="">All Statuses</option>
        <option value="true">Barred</option>
        <option value="false">Not Barred</option>
      </select>
      
      <span class="text-sm text-gray-500 ml-auto">Total: {{ totalRecords }}</span>
    </div>

    <div class="overflow-x-auto shadow rounded-lg border">
      <table class="w-full text-sm text-left">
        <thead class="bg-gray-100 uppercase text-xs font-bold">
          <tr>
            <th class="p-3">Name Details</th>
            <th class="p-3">Nickname</th>
            <th class="p-3">DOB (Y/M/D)</th>
            <th class="p-3">Contact/Address</th>
            <th class="p-3">Housing</th>
            <th class="p-3 text-center">Barred</th>
            <th class="p-3 text-center">Actions</th>
          </tr>
        </thead>
        
        <tbody class="divide-y">
          <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
            <td class="p-2 space-y-1">
              <input v-model="user.first_name" class="w-full border rounded px-1" placeholder="First" />
              <input v-model="user.middle_names" class="w-full border rounded px-1 text-xs" placeholder="Middles" />
              <input v-model="user.surname" class="w-full border rounded px-1" placeholder="Surname" />
              <input v-model="user.nickname" class="w-full border rounded px-1" placeholder="Nickname" />
            </td>
            
            <td class="p-2">
              <input v-model="user.nickname" class="w-full border rounded px-1" />
            </td>
            
            <td class="p-2">
              <input v-model="user.dob" type="date" class="input" />
            </td>
            
            <td class="p-2 space-y-1">
              <input v-model="user.contact_number" class="w-full border rounded px-1 text-xs" placeholder="Phone" />
              <input v-model="user.address" class="w-full border rounded px-1 text-xs" placeholder="Address" />
            </td>
            
            <td class="p-2 text-xs">
              <select v-model="user.housing_status" class="w-full border rounded">
                <option value="unknown">Unknown</option>
                <option value="housed">Housed</option>
                <option value="homeless">Rough Sleeper</option>
                <option value="temporary">Temporary</option>
              </select>
            </td>
            
            <td class="p-2 text-center">
              <input type="checkbox" v-model="user.is_blacklisted" class="w-5 h-5" />
            </td>
            
            <td class="p-2">
              <div class="flex flex-col gap-2">
                <button @click="updateUser(user)" class="bg-blue-600 text-white text-xs py-1 px-2 rounded hover:bg-blue-700">
                  Save
                </button>
                <button @click="deleteUser(user)" class="text-red-500 hover:text-red-700 text-[10px] font-bold uppercase">
                  Delete
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex justify-between items-center bg-white p-4 border rounded">
      <button 
        class="px-4 py-2 border rounded disabled:opacity-30 hover:bg-gray-50"
        :disabled="currentPage === 1" 
        @click="fetchUsers(currentPage - 1)"
      >
        Previous
      </button>
      <span class="text-sm font-medium">Page {{ currentPage }} of {{ lastPage }}</span>
      <button 
        class="px-4 py-2 border rounded disabled:opacity-30 hover:bg-gray-50"
        :disabled="currentPage === lastPage" 
        @click="fetchUsers(currentPage + 1)"
      >
        Next
      </button>
    </div>
  </AppLayout>
</template>

<style scoped>
  input {
    padding: 2px 4px;
  }
</style>