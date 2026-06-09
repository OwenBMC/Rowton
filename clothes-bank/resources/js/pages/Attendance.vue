<template>
  <Head title="Attendance List" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex items-center gap-3 mb-3">
      <label class="font-medium">Select Date:</label>
      <input type="date" v-model="selectedDate" class="border p-1 rounded" />
    </div>
    <h2>{{ selectedDate ?? localToday }}</h2>
    <div style="text-align: right;"><p>Total today: {{ totalUniqueAttended }}; Currently Present: {{ currentlyPresentCount }}</p></div>
    <table class="table-auto border-collapse border w-full">
      <thead>
        <tr>
          <th class="border p-2 text-left">Name</th>
          <th class="border p-2 text-left">Arrival Time</th>
          <th class="border p-2 text-left">Departure Time</th>
        </tr>
      </thead>
      
      <tbody>
        <!-- Add New Attendee Row -->
        <tr>
          <td class="border p-2">
            <label class="block text-sm font-medium mb-1">Add Attendee</label>
  
            <!-- SEARCHABLE USER SELECT -->
            <Multiselect
              v-model="selectedUser"
              :options="userOptions"
              :searchable="true"
              @search-change="searchQuery = $event"
              :close-on-select="true"
              :allow-empty="true"
              label="label"
              track-by="value"
              placeholder="Search or add user"
              class="w-full mb-2"
            >
              <template #option="{ option }">
                <div class="flex justify-between items-center">
                  <span>{{ option.label }}</span>
                  <span v-if="option.isAddNew" class="text-blue-600 text-xs font-semibold">
                    {{' '}}+ New
                  </span>
                </div>
              </template>
            </Multiselect>
  
            <!-- USER INPUT FIELDS -->
            <div v-if="selectedUser" class="space-y-2">
              <input v-model="newAttendee.first_name" type="text" placeholder="First Name" class="input" />
              <input v-model="newAttendee.middle_names" type="text" placeholder="Middle Names" class="input" />
              <input v-model="newAttendee.surname" type="text" placeholder="Surname" class="input" />
              <input v-model="newAttendee.nickname" type="text" placeholder="Nickname (optional)" class="input" />
              <button
              v-if="getActiveBlacklist(selectedUser?.user)"
              class="bg-red-600 text-white px-2 py-1 mt-2"
              type="button"
                @click="handleBlacklistClick"
              >
                Barred
              </button>
              <button
                v-else
                type="button"
                class="btn btn-primary mt-2"
                @click="addUserFromInput"
              >
                Add Attendee
              </button>
            </div>
  
          </td>
          <td></td>
          <td>
            <div class="mt-4 flex gap-2 items-center">
                <button
                v-if="selectedCheckout"
                class="btn btn-primary"
                @click="checkoutUser"
                :disabled="!selectedCheckout"
              >
                Check Out
              </button>
              <Multiselect
                v-model="selectedCheckout"
                :options="activeCheckouts"
                :searchable="true"
                :close-on-select="true"
                label="label"
                track-by="value"
                placeholder="Select user to check out"
                class="w-full"
              />
            </div>
          </td>
        </tr>
        <tr v-for="record in serviceUsersInAttendance" :key="record.id ?? record.displayName">
          <td class="border p-2">
            <span>{{ record.displayName }}</span>
            <span v-if="record.isBlacklisted" class="text-red-600 font-semibold ml-2">
              (Barred)
            </span>
          </td>

          <td class="border p-2">
            <ElTimePicker
              :key="`${record.id ?? record.displayName}_arrival_${timePickerKey}`"
              v-model="record.arrival_time"
              value-format="HH:mm"
              format="HH:mm"
              @update:model-value="() => updateAttendance(record)"
            />
          </td>

          <td class="border p-2">
            <ElTimePicker
              :key="`${record.id ?? record.displayName}_departure_${timePickerKey}`"
              v-model="record.departure_time"
              value-format="HH:mm"
              format="HH:mm"
              @update:model-value="() => updateAttendance(record)"
            />
          </td>
        </tr>

      </tbody>
    </table>
    <div
  v-if="blacklistModal"
  class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
>
  <div class="bg-white p-6 rounded w-[420px] space-y-4">

    <h2 class="text-lg font-bold text-red-600">
      Barred User Warning
    </h2>

    <p class="font-semibold">
      {{ blacklistModal.user.name }}
    </p>

    <p>
      <strong>Reason:</strong>
      {{ blacklistModal.blacklist.note || 'No reason provided' }}
    </p>

    <p>
      <strong>Start date:</strong>
      {{
        new Date(blacklistModal.blacklist.blacklist_start_date)
          .toLocaleDateString()
      }}
    </p>

    <p v-if="blacklistModal.blacklist.blacklist_end_date">
      <strong>Allowed to return from:</strong>
      {{
        new Date(blacklistModal.blacklist.blacklist_end_date)
          .toLocaleDateString()
      }}
    </p>

    <p v-else class="text-gray-600">
      It is not yet decided when they are allowed to return
    </p>

    <div class="flex justify-end gap-2 mt-4">

      <button
        class="bg-red-600 text-white px-3 py-1 rounded"
        @click="refuseEntry"
      >
        Refuse Entry
      </button>

      <button
        class="bg-green-600 text-white px-3 py-1 rounded"
        @click="allowEntry"
      >
        Allow Entry
      </button>

    </div>
  </div>
</div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import Multiselect from 'vue-multiselect';
import { ElTimePicker } from 'element-plus';
import { ref, reactive, computed, watch, onMounted } from 'vue';
import axios from 'axios';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
{ title: 'Attendance List', href: '/' },
];

const localToday = new Date().toLocaleDateString()

const selectedDate = ref(new Date().toISOString().split('T')[0]);

const isToday = computed(() => {
  const today = new Date().toISOString().split('T')[0];
  return selectedDate.value === today;
});

async function loadAttendance(date: string) {
  try {
    const res = await axios.get(`/api/attendance`, {
      params: { date },
    });

    serviceUsersInAttendance.value = res.data;
  } catch (err) {
    console.error(err);
  }
}

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
    toiletries: any[];
    isBlacklisted: boolean;
  }
  
  const page = usePage();
  const serviceUsersInAttendance = ref<AttendanceRecord[]>([]);
  const allServiceUsers = ref<ServiceUser[]>([]);

  const timePickerKey = ref(0);
  
  const selectedUser = ref<any>(null);
  function getCurrentTimeHHMM() {
    const now = new Date();
    return now.toTimeString().slice(0, 5); // "HH:mm"
  }

const newAttendee = reactive({
  selectedUserId: null as number | null,
  first_name: '',
  middle_names: '',
  surname: '',
  nickname: '',
  isAddNew: false,
});

const searchQuery = ref('');
const userOptions = computed(() => {
  console.log(allServiceUsers)
  let base = [
  {
    label: 'Add New',
    value: null,
    isAddNew: true,
  },
    ...allServiceUsers.value?.map(u => ({
      label: `${u.name}${u.nickname ? ` (${u.nickname})` : ''} ${u.housing_status === 'homeless' ? 'RS': ''}`,
      value: u.id,
      user: u,
    })),
  ];
  if (searchQuery.value?.trim()) {
    base.push({
      label: `"${searchQuery.value}"`,
      value: '__new__',
      isAddNew: true,
    });
  }
  return base
});

function confirmIfPastDate(action: string): boolean {
  if (isToday.value) return true;

  return confirm(
    `You are editing attendance for ${selectedDate.value}, not today.\n\n` +
    `This action (${action}) will modify historical data. Do you want to continue?`
  );
}

onMounted(async () => {
  try {
    const usersRes = await axios.get('/api/service-users-full');
    allServiceUsers.value = usersRes.data;

    await loadAttendance(selectedDate.value);
  } catch (err) {
    console.error(err);
  }
});

watch(selectedDate, async (newDate, oldDate) => {
  if (newDate === oldDate) return;
  await loadAttendance(newDate);
});

watch(selectedUser, (option) => {
  if (!option) return;

  // ADD NEW
  if (option.isAddNew) {
    newAttendee.isAddNew = true;
    newAttendee.selectedUserId = null;

    newAttendee.first_name = '';
    newAttendee.middle_names = '';
    newAttendee.surname = '';
    newAttendee.nickname = '';
    return;
  }

  newAttendee.isAddNew = false;
  newAttendee.selectedUserId = option.value;

  const user = option.user;

  if (user) {
    const parts = user.name.split(' ');

    newAttendee.first_name = parts[0] || '';
    newAttendee.middle_names = parts.slice(1, -1).join(' ') || '';
    newAttendee.surname = parts.slice(-1).join('') || '';
    newAttendee.nickname = user.nickname || '';
  }
});

const totalUniqueAttended = computed(() => {
  return new Set(serviceUsersInAttendance.value.map(su => su.userId)).values().toArray().length
});

const currentlyPresentCount = computed(() => {
  return new Set(serviceUsersInAttendance.value.filter(
    record => record.arrival_time && !record.departure_time
  ).map(su => su.userId)).values().toArray().length;
});

/**
 * Submit new attendee
 */
async function addUserFromInput() {
  if (!confirmIfPastDate('editing data')) return;
  let userId = newAttendee.selectedUserId;

  if (!userId) {
    const res = await axios.post('/api/service-users', {
      first_name: newAttendee.first_name,
      middle_names: newAttendee.middle_names,
      surname: newAttendee.surname,
      nickname: newAttendee.nickname,
    });

    userId = res.data.id;
    console.log("asu", allServiceUsers)

    allServiceUsers.value?.push({
      id: userId,
      name: `${newAttendee.first_name} ${newAttendee.middle_names} ${newAttendee.surname}`.trim(),
      nickname: newAttendee.nickname,
      isBlacklisted: false,
    });
  }

  const today = new Date().toISOString().split('T')[0];
  
  const response = await axios.post('/api/attendance', {
    date: selectedDate.value ?? today,
    attendees: [
      {
        id: userId,
        arrival_time: getCurrentTimeHHMM(),
        departure_time: null,
      },
    ],
  });
  console.log(response)
  const displayName = newAttendee.nickname
    ? `${newAttendee.first_name} ${newAttendee.middle_names} ${newAttendee.surname} (${newAttendee.nickname})`
    : `${newAttendee.first_name} ${newAttendee.middle_names} ${newAttendee.surname}`.trim();

  console.log("seleced", selectedUser.value)
  serviceUsersInAttendance.value.push({
    attendanceId: response.data.data[0].attendance_id,
    userId: userId,
    displayName,
    arrival_time: getCurrentTimeHHMM(),
    departure_time: '',
    services: {},
    toiletries: [],
    isBlacklisted: getActiveBlacklist(selectedUser.value?.user),
  });

  selectedUser.value = null;

  newAttendee.first_name = '';
  newAttendee.middle_names = '';
  newAttendee.surname = '';
  newAttendee.nickname = '';
  newAttendee.selectedUserId = null;
  newAttendee.isAddNew = false;
}

const blacklistModal = ref<null | any>(null);

function allowEntry() {
  blacklistModal.value = null;

  addUserFromInput()
}

function refuseEntry() {
  blacklistModal.value = null;

  selectedUser.value = null;
}

function getActiveBlacklist(user: any) {
  if (!user?.blacklist?.length) return null;

  const now = new Date();

  return user.blacklist.find((b: any) => {
    const startOk = new Date(b.blacklist_start_date) <= now;

    const endOk =
      !b.blacklist_end_date ||
      new Date(b.blacklist_end_date) >= now;

    return startOk && endOk;
  }) || null;
}

function handleBlacklistClick() {
  const user = selectedUser.value?.user;
  if (!user) return;

  const blacklist = getActiveBlacklist(user);
  if (!blacklist) return;

  blacklistModal.value = {
    user,
    blacklist,
  };
}

async function checkoutUser() {
  if (!selectedCheckout.value) return;
  if (!confirmIfPastDate('editing data')) return;

  const record = selectedCheckout.value.record;
  console.log("checkout record", record)


  record.departure_time = getCurrentTimeHHMM()

  updateAttendance(record)
  selectedCheckout.value = null;
}
const selectedCheckout = ref<any>(null); 

const activeCheckouts = computed(() => {
  const latestAttendancePerUser = new Map();

  serviceUsersInAttendance.value.forEach(record => {
    const existing = latestAttendancePerUser.get(record.userId);

    if (
      !existing ||
      record.attendanceId > existing.attendanceId
    ) {
      latestAttendancePerUser.set(record.userId, record);
    }
  });

  return Array.from(latestAttendancePerUser.values())
    .filter(record => record.arrival_time && !record.departure_time)
    .map(record => ({
      label: `${record.displayName} (arrived ${record.arrival_time})`,
      value: record.attendanceId,
      record,
    }));
});

function getDisabledTimes() {
  return {
    disabledHours: () => [],
    disabledMinutes: () => [],
  };
}

async function updateAttendance(record) {
  console.log("hello?")
  if (!confirmIfPastDate('updating attendance times')) return;
  console.log("Record", record)
  console.log("Date", selectedDate.value)
  try {
    await axios.post('/api/attendance/update', {
      id: record.attendanceId,
      date: selectedDate.value,
      arrival_time: record.arrival_time,
      departure_time: record.departure_time,
    });
  } catch (error) {
    console.error('Update failed:', error.response?.data || error.message);
  }
}

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
</style>