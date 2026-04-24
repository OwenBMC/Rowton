<template>
  <Head title="Attendance List" />

  <AppLayout :breadcrumbs="breadcrumbs">
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
        <!-- Existing attendees -->
        <tr v-for="record in serviceUsersInAttendance" :key="record.id ?? record.displayName">
          <td class="border p-2">
            <span>{{ record.displayName }}</span>
            <span v-if="record.isBlacklisted" class="text-red-600 font-semibold ml-2">(Blacklisted)</span>
          </td>

          <!-- Arrival Time -->
          <td class="border p-2">
            <ElTimePicker
              :key="`${record.id ?? record.displayName}_arrival_${timePickerKey}`"
              v-model="record.arrival_time"
              value-format="HH:mm"
              format="HH:mm"
              :disabled-hours="getDisabledTimes(record, 'arrival').disabledHours"
              :disabled-minutes="getDisabledTimes(record, 'arrival').disabledMinutes"
              @visible-change="v => { pickerOpen = v; updateAttendance(record, 'arrival_time') }"
              @update:model-value="() => updateAttendance(record, 'arrival_time')"
            />
          </td>

          <!-- Departure Time -->
          <td class="border p-2">
            <ElTimePicker
              :key="`${record.id ?? record.displayName}_departure_${timePickerKey}`"
              v-model="record.departure_time"
              value-format="HH:mm"
              format="HH:mm"
              :disabled-hours="getDisabledTimes(record, 'departure').disabledHours"
              :disabled-minutes="getDisabledTimes(record, 'departure').disabledMinutes"
              @visible-change="v => { pickerOpen = v; updateAttendance(record, 'departure_time') }"
              @update:model-value="() => updateAttendance(record, 'departure_time')"
            />
          </td>

          <!-- Services & Toiletries -->
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
              <template #option="{ option }">
                <div
                  class="flex items-center px-2 py-1 rounded cursor-pointer"
                  :class="{
                    'bg-blue-100 text-blue-800': record.toiletries?.some(t => t.short === option.short),
                    'hover:bg-blue-50': !record.toiletries?.some(t => t.short === option.short)
                  }"
                >
                  <span class="flex-1">{{ option.label }} ({{ option.short }})</span>
                  <span v-if="record.toiletries?.some(t => t.short === option.short)" class="text-blue-600 font-bold ml-2">✔</span>
                </div>
              </template>

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
          </td>
        </tr>

        <!-- Add New Attendee Row -->
        <tr>
          <td class="border p-2">
            <label for="attendance-new-user" class="block text-sm font-medium mb-1">Add Attendee</label>

            <!-- Existing user selector -->
          <Multiselect
            v-model="newAttendee.selectedUserId"
            :options="userOptions"
            :searchable="true"
            :close-on-select="true"
            :allow-empty="true"
            label="label"
            track-by="value"
            placeholder="Search or add user"
            class="w-full mb-2"
          >
            <!-- Custom option -->
            <template #option="{ option }">
              <div class="flex justify-between items-center">
                <span>{{ option.label }}</span>
                <span v-if="option.isAddNew" class="text-blue-600 text-xs font-semibold">
                  + New
                </span>
              </div>
            </template>
          </Multiselect>

            <!-- New user fields -->
            <div v-if="!newAttendee.isAddNew" class="space-y-2">
              <input v-model="newAttendee.first_name" type="text" placeholder="First Name" class="input" />
              <input v-model="newAttendee.middle_names" type="text" placeholder="Middle Names" class="input" />
              <input v-model="newAttendee.surname" type="text" placeholder="Surname" class="input" />
              <input v-model="newAttendee.nickname" type="text" placeholder="Nickname (optional)" class="input" />
            </div>

            <button type="button" class="btn btn-primary mt-2" @click="addUserFromInput(newAttendee)">
              Add Attendee
            </button>
          </td>
          <td colspan="3"></td>
        </tr>
      </tbody>
    </table>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import Multiselect from 'vue-multiselect';
import { ElTimePicker } from 'element-plus';
import { reactive, ref, onMounted, computed } from 'vue';
import axios from 'axios';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Attendance List', href: '/' },
];

interface ServiceUser {
  id: number;
  name: string;
  nickname?: string;
  isBlacklisted: boolean;
}

interface AttendanceRecord {
  id?: number;
  displayName: string;
  arrival_time?: string;
  departure_time?: string;
  services: Record<string, boolean>;
  toiletries: { label: string; short: string }[];
  isBlacklisted: boolean;
}

const page = usePage();
const serviceUsers = ref<ServiceUser[]>(page.props.serviceUsers || []);
const serviceUsersInAttendance = ref<AttendanceRecord[]>([]);

const allServiceUsers = ref<ServiceUser[]>([]);
const timePickerKey = ref(0);
const pickerOpen = ref(false);

const clothingOptions = [
  'Coat', 'Hoodie', 'Sweat-shirt', 'Tee-shirt', 'Top',
  'Tracksuit bottoms', 'Jeans', 'Shoes', 'Socks', 'Underwear', 'Hats', 'Scarves', 'Gloves',
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

const newAttendee = reactive({
  selectedUserId: '',
  first_name: '',
  middle_names: '',
  surname: '',
  nickname: '',
});

// Prefill serviceUsersInAttendance
onMounted(() => {
  serviceUsersInAttendance.value = serviceUsers.value.map(u => ({
    id: undefined,
    displayName: u.name,
    arrival_time: '',
    departure_time: '',
    services: {},
    toiletries: [],
    isBlacklisted: u.isBlacklisted,
  }));

  // Fetch all users for the dropdown
  axios.get('/api/service-users').then(res => {
    allServiceUsers.value = res.data;
  });
});

function addUserFromInput(attendee: typeof newAttendee) {
  let user: ServiceUser | undefined;
  let tempId = Date.now(); // temporary ID for frontend

  // Determine if existing user
  if (attendee.selectedUserId) {
    user = allServiceUsers.value.find(u => u.id === Number(attendee.selectedUserId));
  }

  // Construct display name
  const fullName = user
    ? user.name
    : [attendee.first_name, attendee.middle_names, attendee.surname].filter(Boolean).join(' ');

  const displayName = attendee.nickname
    ? `${fullName} (${attendee.nickname})`
    : fullName;

  // Create temporary attendance record
  const tempRecord: AttendanceRecord = {
    id: user?.id ?? tempId,
    displayName,
    arrival_time: '',
    departure_time: '',
    services: {},
    toiletries: [],
    isBlacklisted: user?.isBlacklisted ?? false,
  };

  serviceUsersInAttendance.value.push(tempRecord);

  // Helper to post to attendances table
  const postAttendance = (userId: number) => {
    axios.post('/api/attendance', {
      date: new Date().toISOString().split('T')[0],
      attendees: [
        {
          id: userId,
          displayName: tempRecord.displayName,
          arrival_time: null,
          departure_time: null,
        },
      ],
    }).catch(err => console.error('Error adding attendance:', err));
  };

  // If new user, create them first
  if (!user) {
    axios.post('/api/service-users', {
      first_name: attendee.first_name,
      middle_names: attendee.middle_names,
      surname: attendee.surname,
      nickname: attendee.nickname,
    })
    .then(res => {
      const createdUserId = res.data.id;
      // Update temp attendance record with real ID
      tempRecord.id = createdUserId;

      // Add to all users for dropdown
      allServiceUsers.value.push({
        id: createdUserId,
        name: fullName,
        nickname: attendee.nickname,
        isBlacklisted: false,
      });

      // Finally post to attendance table
      postAttendance(createdUserId);
    })
    .catch(err => console.error('Error creating new service user:', err));
  } else {
    // Existing user → just post attendance
    postAttendance(user.id);
  }

  // Reset input fields
  attendee.selectedUserId = '';
  attendee.first_name = '';
  attendee.middle_names = '';
  attendee.surname = '';
  attendee.nickname = '';
}
// Attendance update stub
function getDisabledTimes(record: AttendanceRecord, field: 'arrival' | 'departure') {
  return {
    disabledHours: () => [],
    disabledMinutes: () => [],
  };
}
function updateAttendance(record: AttendanceRecord, field: 'arrival_time' | 'departure_time') {
  return (visible: boolean) => {};
}

const userOptions = computed(() => {
  const filtered = allServiceUsers.value.map(u => ({
    label: `${u.name}${u.nickname ? ` (${u.nickname})` : ''}`,
    value: u.id,
  }));

  return [
    ...filtered,
    { label: '', value: null, isAddNew: true },
  ];
});
</script>

<style scoped>
.input {
  display: block;
  width: 100%;
  padding: 0.5rem;
  margin-bottom: 0.25rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}
.btn {
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
}
.btn-primary {
  background-color: #3b82f6;
  color: white;
  border: none;
}
.multiselect__option--selected {
  background-color: #dbeafe !important;
  color: #1e3a8a !important;
  font-weight: 600;
}
.multiselect__option--highlight {
  background-color: #bfdbfe !important;
  color: #1e3a8a !important;
}
</style>