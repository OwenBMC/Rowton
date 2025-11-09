<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref, watch, reactive, onMounted } from 'vue';
import Multiselect from 'vue-multiselect';
import { Head, usePage } from '@inertiajs/vue3';
import type { AppPageProps as BasePageProps } from '@/types';
import axios from 'axios';

interface ServiceUser {
  id: number;
  name?: string;
  nickname?: string;
  isBlacklisted: boolean
}

interface Toiletry {
  label: string;
  short: string;
}

interface AttendanceRecord {
  id: number;
  displayName: string;
  arrival_time: string;
  departure_time: string;
  services: Record<string, boolean>;
  toiletries: Toiletry[];
  isBlacklisted: boolean;
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Attendance List', href: dashboard().url },
];

interface AttendancePageProps extends BasePageProps {
  serviceUsers: ServiceUser[];
  date: string;
}

const userServicesMap = reactive<Record<number, { services: Record<string, boolean>; toiletries: Toiletry[]; }>>({});
const clothingOptions = [
  'Coat',
  'Hoodie',
  'Sweat-shirt',
  'Tee-shirt',
  'Top',
  'Tracksuit bottoms',
  'Jeans',
  'Shoes',
  'Socks',
  'Underwear',
  'Hats',
  'Scarves',
  'Gloves',
];

const toiletriesOptions = [
  { label: 'Brush/comb', short: 'B/CB' },
  { label: 'Conditioner', short: 'C' },
  { label: 'Deodorant', short: 'D' },
  { label: 'Sanitary Products', short: 'San' },
  { label: 'Shampoo', short: 'SH' },
  { label: 'Shower Gel', short: 'SG' },
  { label: 'Soap', short: 'S' },
  { label: 'Toothbrush', short: 'TB' },
  { label: 'Toothpaste', short: 'TP' },
  { label: 'Wipes', short: 'W' },
];
const allServiceUsers = ref<ServiceUser[]>([]);
const serviceUsersInAttendance = ref<AttendanceRecord[]>([]);
const newAttendee = ref('');

const page = usePage<AttendancePageProps>();
console.log('Inertia props:', page.props)
const serviceUsers = ref(page.props.serviceUsers || []);
const currentDate = ref(page.props.date);

serviceUsersInAttendance.value = serviceUsers.value
  .filter(u => u != null)
  .map(user => ({
    id: user.id,
    displayName: user.name || user.nickname || 'Unknown',
    arrival_time: '',
    departure_time: '',
    services: {},       // your services init
    toiletries: [],
    isBlacklisted: user.isBlacklisted || false,
  }));
console.log("Service users from prop", JSON.stringify(serviceUsers.value))
serviceUsers.value.forEach((user: any) => {
  if (!user) return; // skip null/undefined
  const attendances = user.attendances || [];
  const servicesProvided = user.services_provided || [];

  const services: Record<string, boolean> = {};
  clothingOptions.forEach(item => {
    services[item] = servicesProvided.some((s: any) => s.service_name === item);
  });

  serviceUsersInAttendance.value.push({
    id: user.id ?? Date.now(),
    displayName: user.name && user.nickname
      ? `${user.name} (${user.nickname})`
      : user.name || user.nickname || 'Unknown',
    arrival_time: attendances[0]?.arrival_time || '',
    departure_time: attendances[0]?.departure_time || '',
    services,
    toiletries: servicesProvided
      .filter((s: any) => s.category === 'toiletry')
      .map((s: any) => ({ label: s.service_name, short: s.code || s.service_name })),
    isBlacklisted: user.isBlacklisted ?? false,
  });

  // initialize services map for multi-row syncing
  if (!userServicesMap[user.id]) {
    userServicesMap[user.id] = {
      services: { ...services },
      toiletries: servicesProvided
        .filter((s: any) => s.category === 'toiletry')
        .map((s: any) => ({ label: s.service_name, short: s.code || s.service_name })),
    };
  }
});

function addUserFromInput(value: string) {
    console.log("addUserFromInput", value)
  const trimmed = value.trim();
  if (!trimmed) return;

  // Ensure the array exists
  if (!serviceUsersInAttendance.value) {
    serviceUsersInAttendance.value = [];
  }
    console.log("array exists")

  // Try to find the user by name or nickname
  const user = allServiceUsers.value.find(
    (u) =>
      u.name?.toLowerCase() === trimmed.toLowerCase() ||
      u.nickname?.toLowerCase() === trimmed.toLowerCase()
  );
    console.log("user", JSON.stringify(user))

  const id = user?.id ?? Date.now();
  console.log("id", id)
  const displayName = user
    ? user.name && user.nickname
      ? `${user.name} (${user.nickname})`
      : user.name || user.nickname || 'Unknown'
    : trimmed;

  // Prevent duplicates
  const alreadyExists = serviceUsersInAttendance.value.some(a => a.id === id);
  if (!alreadyExists) {
    console.log("already doesn't exists")
    // Initialize services & toiletries if needed
    const services = userServicesMap[id]?.services || {};
    const toiletries = userServicesMap[id]?.toiletries || [];

    // Push into attendance array
    serviceUsersInAttendance.value.push({
      id,
      displayName,
      arrival_time: '',
      departure_time: '',
      services,
      toiletries,
      isBlacklisted: user?.isBlacklisted ?? false,
    });

    // Ensure the services map exists for syncing multiple rows
    if (!userServicesMap[id]) {
      userServicesMap[id] = {
        services: services || {},
        toiletries: toiletries || [],
      };
    }
  }

  // Clear input
  newAttendee.value = '';
}


let inputTimeout: number | null = null;

function handleInput(e: Event) {
    console.log("handleInput", inputTimeout)
  if (inputTimeout) clearTimeout(inputTimeout);
  const value = (e.target as HTMLInputElement).value;
  console.log("handle value", value)
  console.log("suvalues", JSON.stringify(serviceUsers.value))
  inputTimeout = window.setTimeout(() => {
    const isExactMatch = allServiceUsers.value.some(
      (u) =>
        u.name?.toLowerCase() === value.toLowerCase() ||
        u.nickname?.toLowerCase() === value.toLowerCase()
    );
    if (isExactMatch) addUserFromInput(value);
  }, 150);

}

// watch(
//   serviceUsersInAttendance,
//   (newVal) => {
//     console.log('--- Attendance updated ---');
//     newVal.forEach((record) => {
//       console.log(`User: ${record.displayName}`);
//       console.log('Toiletries:', record.toiletries);
//       console.log('---');
//     });
//   },
//   { deep: true } // needed to watch nested arrays/objects
// );


async function loadServiceUsers() {
  try {
    const response = await axios.get('/service-users');
    allServiceUsers.value = response.data;
    console.log("su response", response.data)
  } catch (error) {
    console.error('Failed to load service users:', error);
  }
}

onMounted(() => {
  loadServiceUsers();
});

</script>

<template>

    <Head title="Attendance List" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Arrival Time</th>
                    <th>Departure Time</th>
                    <th>Services Provided</th>
                </tr>
            </thead>
            <tbody>
                        <tr v-for="record in serviceUsersInAttendance" :key="record.id">
          <td class="border p-2">{{ record.name }}</td>
          <td class="border p-2">
            <input
              type="time"
              v-model="record.arrival_time"
              class="border rounded p-1"
            />
          </td>
          <td class="border p-2">
            <input
              type="time"
              v-model="record.departure_time"
              class="border rounded p-1"
            />
          </td>
          <td class="border p-2 text-gray-500 italic">
            (Add services later)
          </td>
        </tr>

        <tr>
          <td class="border p-2">
            <input
              v-model="newAttendee"
              list="serviceUsersList"
              placeholder="Add attendee..."
              class="border rounded p-1 w-full"
            />
            <datalist id="serviceUsersList">
                <option v-for="serviceUser in serviceUsers" :key="serviceUser.id" :value="serviceUser.name">
                    {{ serviceUser.name }} {{ serviceUser.nickname ? `(${serviceUser.nickname})` : ''}}
                </option>
            </datalist>
          </td>
        </tr>
            </tbody>
        </table> -->

<table class="table-auto border-collapse border w-full">
      <thead>
        <tr class="bg-gray-100">
          <th class="border p-2 text-left">Name</th>
          <th class="border p-2 text-left">Arrival Time</th>
          <th class="border p-2 text-left">Departure Time</th>
          <th class="border p-2 text-left">Services Provided</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="record in serviceUsersInAttendance" :key="record.id">
          <td class="border p-2">{{ record.displayName }}</td>

          <td class="border p-2">
            <input type="time" v-model="record.arrival_time" class="border rounded p-1" />
          </td>

          <td class="border p-2">
            <input type="time" v-model="record.departure_time" class="border rounded p-1" />
          </td>

          <td class="border p-2 align-top">
              <label class="font-semibold text-sm block mb-1">Clothes:</label>
            <div class="flex flex-wrap gap-2 mb-2">
              <label
                v-for="item in clothingOptions"
                :key="item"
                class="flex items-center space-x-1 text-sm"
              >
                <input type="checkbox" v-model="record.services[item]" />
                <span>{{ item }}</span>
              </label>
            </div>

            <!-- Toiletries multiselect -->
            <div class="mt-2">
              <label class="font-semibold text-sm block mb-1">Toiletries:</label>
<Multiselect
  v-model="record.toiletries"
  :options="toiletriesOptions"
  :multiple="true"
  :close-on-select="false"
  :clear-on-select="false"
  :preserve-search="true"
  label="label"
  track-by="short"
  placeholder="Select toiletries"
  class="w-full"
>
  <!-- Option slot with clear tick -->
<template #option="{ option }">
  <div
    class="flex items-center px-2 py-1 rounded cursor-pointer"
    :class="{
      'bg-blue-100 text-blue-800': record.toiletries.some(t => t.short === option.short),
      'hover:bg-blue-50': !record.toiletries.some(t => t.short === option.short)
    }"
  >
    <span class="flex-1">{{ option.label }} ({{ option.short }})</span>
    <span
      v-if="record.toiletries.some(t => t.short === option.short)"
      class="text-blue-600 font-bold ml-2"
    >
      ✔
    </span>
  </div>
</template>

  <!-- Tag chips -->
  <template #selection="{ values }">
    <div class="flex flex-wrap gap-1">
      <span
        v-for="item in values"
        :key="item.short"
        class="bg-blue-100 text-blue-800 rounded px-2 py-1 text-xs font-semibold"
      >
        {{ item.short }}
      </span>
    </div>
  </template>
</Multiselect>
            </div>
          </td>
        </tr>

        <!-- Input row -->
        <tr>
          <td class="border p-2">
            <input
              v-model="newAttendee"
              list="serviceUsersList"
              placeholder="Add attendee..."
              class="border rounded p-1 w-full"
              @input="handleInput"
              @keydown.enter.prevent="addUserFromInput(newAttendee)"
            />
            <datalist id="serviceUsersList">
              <option
                v-for="user in allServiceUsers"
                :key="user.id"
                :value="user.name || user.nickname"
              >
                {{ user.name && user.nickname ? `${user.name} (${user.nickname})` : user.name || user.nickname }}
              </option>
            </datalist>

          </td>
        </tr>
      </tbody>
    </table>
  </AppLayout>
</template>

<style>
.multiselect__option--selected {
  background-color: #dbeafe !important; /* Tailwind blue-100 */
  color: #1e3a8a !important; /* Tailwind blue-800 */
  font-weight: 600;
}

.multiselect__option--highlight {
  background-color: #bfdbfe !important; /* Tailwind blue-200 */
  color: #1e3a8a !important;
}
</style>