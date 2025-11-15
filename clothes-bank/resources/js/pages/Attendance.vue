<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref, watch, reactive, onMounted } from 'vue';
import Multiselect from 'vue-multiselect';
import { Head, usePage } from '@inertiajs/vue3';
import type { AppPageProps as BasePageProps } from '@/types';
import axios from 'axios';
import {ElTimePicker} from 'element-plus'


const range = (start: number, end: number) =>
  Array.from({ length: end - start + 1 }, (_, i) => start + i);

const timePickerKey = ref(0)

const pickerOpen = ref(false);

function isAnyPickerOpen() {
  return pickerOpen.value;
}

const pickerTicker = setInterval(() => {
  console.log("ticker", isAnyPickerOpen())
  if (!isAnyPickerOpen()) {
    console.log("picker up")
    timePickerKey.value = timePickerKey.value +1
  }
}, 30000)

function getDisabledTimes(record: AttendanceRecord, field: 'arrival' | 'departure') {
  const now = new Date();
  const nowHour = now.getHours();
  const nowMinute = now.getMinutes();

  const arrival = record.arrival_time ? record.arrival_time.split(':') : null;
  const arrivalHour = arrival ? Number(arrival[0]) : null;
  const arrivalMinute = arrival ? Number(arrival[1]) : null;

  const departure = record.departure_time ? record.departure_time.split(':') : null;
  const departureHour = departure ? Number(departure[0]) : null;
  const departureMinute = departure ? Number(departure[1]) : null;

  return {
    disabledHours: () => {
      // rule 1: no selecting future hours
      const disabled = range(nowHour + 1, 23);

      if (field === 'departure' && arrivalHour !== null) {
        // rule 2: departure cannot be before arrival
        disabled.push(...range(0, arrivalHour - 1));
      }

      return [...new Set(disabled)].filter(h => h >= 0 && h <= 23);
    },

    disabledMinutes: (hour: number) => {
      let disabled: number[] = [];

      // rule 3: if choosing the current hour, disable future minutes
      if (hour === nowHour) {
        disabled.push(...range(nowMinute + 1, 59));
      }

      if (field === 'departure' && arrivalHour === hour && arrivalMinute !== null) {
        // rule 4: departure minutes cannot be before arrival minutes
        disabled.push(...range(0, arrivalMinute - 1));
      }

      return [...new Set(disabled)].filter(m => m >= 0 && m <= 59);
    }
  };
}


interface ServiceUser {
  id: number;
  name: string;
  first_name?: string;
  middle_names?: string;
  surname?: string;
  nickname?: string;
  isBlacklisted: boolean;
  attendances: AttendanceRecord[];
  services_provided: []
}

interface Toiletry {
  label: string;
  short: string;
}

interface AttendanceRecord {
  id?: number;
  displayName: string;
  arrival_time?: string;
  departure_time?: string;
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
  .map(user => {
    const attendances = user.attendances || [];
    const servicesProvided = user.services_provided || [];

    const services: Record<string, boolean> = {};
    clothingOptions.forEach(item => {
      services[item] = servicesProvided.some((s: any) => s.service_name === item);
    });

    const firstAttendance = attendances[0]; 

    return {
      id: firstAttendance?.id,
      displayName: user.name,
      arrival_time: firstAttendance?.arrival_time || '',
      departure_time: firstAttendance?.departure_time || '',
      services,
      toiletries: servicesProvided
        .filter((s: any) => s.category === 'toiletry')
        .map((s: any) => ({ label: s.service_name, short: s.code || s.service_name })),
      isBlacklisted: user.isBlacklisted ?? false,
    };
  });

console.log(serviceUsersInAttendance.value)



function addUserFromInput(value: string) {
  const trimmed = value.trim();
  if (!trimmed) return;

  // Try to find an existing user by ID or name match
  // We'll assume allServiceUsers contains all DB users
  const user = allServiceUsers.value.find(u => {
    const fullName = `${u.first_name ?? ''} ${u.middle_names ?? ''} ${u.surname ?? ''}`.trim();
    const displayName = u.nickname ? `${fullName} (${u.nickname})` : fullName;
    return displayName.toLowerCase() === trimmed.toLowerCase();
  });

  let id: number;
  let displayName: string;

  if (user) {
    // Existing user: use their DB ID
    id = user.id;
    const fullName = `${user.first_name ?? ''} ${user.middle_names ?? ''} ${user.surname ?? ''}`.trim();
    displayName = user.nickname ? `${fullName} (${user.nickname})` : fullName;
  } else {
    // New user: temporarily generate an ID
    id = Date.now();
    displayName = trimmed;
  }

  // Prevent duplicates
  const exists = serviceUsersInAttendance.value.some(a => a.id === id);
  if (!exists) {
    serviceUsersInAttendance.value.push({
      id,
      displayName,
      arrival_time: '',
      departure_time: '',
      services: {},
      toiletries: [],
      isBlacklisted: user?.isBlacklisted ?? false,
    });
  }

  // Prepare payload: must always send a valid DB ID or create a new user first
  const payload = {
    date: currentDate.value,
    attendees: [
      {
        id: user?.id ?? null, // null if this is a new user
        displayName,
        arrival_time: '',
        departure_time: '',
      },
    ],
  };

  axios
    .post('/api/attendance', payload, {
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    })
    .then(res => {
      console.log('Attendance saved:', res.data);
      // Update local ID if new user was created
      if (!user && res.data?.createdUserId) {
        const local = serviceUsersInAttendance.value.find(a => a.id === id);
        if (local) local.id = res.data.createdUserId;
      }
    })
    .catch(err => {
      console.error('Error saving attendance:', err.response?.data || err);
    });

  newAttendee.value = '';
}



const updateTimeouts = ref<Record<string, number>>({});

function updateAttendance(record: AttendanceRecord, field: 'arrival_time' | 'departure_time') {
  
  if (!record.id) {
    console.warn("Attendance record ID is missing");
    return;
  }

  return (visible: boolean) => {
    if (!visible) { // picker was closed
      const payload = {
        id: record.id,
        [field]: record[field],
      };
      console.log("payload", payload);

      axios
        .post('/api/attendance/update', payload)
        .then((res) => {
          console.log(`Attendance ${field} updated:`, res.data);
        })
        .catch((err) => {
          console.error(`Error updating ${field}:`, err.response?.data || err);
        })
        .finally(() => {
          console.log("update complete");
          // optionally trigger any reactivity like rerendering pickers
          timePickerKey.value += 1;
        });
    }
  };
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
  <ElTimePicker
  :key="`${record.id}_${timePickerKey}`"
   @visible-change="v => {pickerOpen = v, updateAttendance(record, 'arrival_time')}"
    v-model="record.arrival_time"
    @update:model-value="() => updateAttendance(record, 'arrival_time')"
    value-format="HH:mm"
    format="HH:mm"
    :disabled-hours="getDisabledTimes(record, 'arrival').disabledHours"
    :disabled-minutes="getDisabledTimes(record, 'arrival').disabledMinutes"
  />
</td>

<td class="border p-2">
    <ElTimePicker
    :key="`${record.id}_${timePickerKey}`"
     @visible-change="v => {pickerOpen = v, updateAttendance(record, 'departure_time')}"
    v-model="record.departure_time"
    @update:model-value="() => updateAttendance(record, 'departure_time')"
    value-format="HH:mm"
    format="HH:mm"
    :disabled-hours="getDisabledTimes(record, 'departure').disabledHours"
    :disabled-minutes="getDisabledTimes(record, 'departure').disabledMinutes"
  />
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
      'bg-blue-100 text-blue-800': record.toiletries?.some(t => t.short === option.short),
      'hover:bg-blue-50': !record.toiletries?.some(t => t.short === option.short)
    }"
  >
    <span class="flex-1">{{ option.label }} ({{ option.short }})</span>
    <span
      v-if="record.toiletries?.some(t => t.short === option.short)"
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