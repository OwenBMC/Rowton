<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue';
import axios from 'axios';
import Multiselect from 'vue-multiselect';


import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Tooltip,
  Legend,
} from 'chart.js';

import { Bar } from 'vue-chartjs';

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Tooltip,
  Legend
);

const today = new Date();

const allUsers = ref<any[]>([]);
const selectedUsers = ref<any[]>([]);

const filters = reactive({
  from: new Date(today.getFullYear(), today.getMonth(), 1)
    .toISOString()
    .split('T')[0],

  to: today.toISOString().split('T')[0],

  chart_mode: 'total',

  gender: '',
  housing_status: '',
  registered: '',
  age_min: '',
  age_max: '',
  service_users: [],
});

const summary = ref<any>(null);

const chartData = ref({
  labels: [],
  datasets: [],
});

function getColor(name: string) {
  const map: any = {
    male: '#3b82f6',
    female: '#ec4899',
    other: '#a855f7',

    homeless: '#ef4444',
    hostel: '#f59e0b',
    housed: '#10b981',

    registered: '#3b82f6',
    unregistered: '#f97316',

    Total: '#3b82f6',
  };

  return map[name] || '#64748b';
}

async function loadReport() {
  console.log("filters", {
    ...filters,
    service_users: filters.service_users.map((u: any) => u.value ?? u.id),
  },)
const { data } = await axios.get('/api/reports/attendance', {
  params: {
    ...filters,
    service_user_ids: filters.service_users.map((u: any) => u.value ?? u.id),
  },
});


  summary.value = data.summary;

  // ----------------------------
  // NORMALISE SERIES FORMAT
  // ----------------------------

chartData.value = {
  labels: data.labels,

  datasets: data.series.map((s: any) => {
    const values = Array.isArray(s.data)
      ? s.data
      : data.labels.map((d: string, i: number) =>
          Array.isArray(s.data) ? s.data[i] : (s.data?.[d] ?? 0)
        );

    return {
      type: 'bar',
      label: s.name,
      data: values,
      backgroundColor: getColor(s.name),
    };
  }),
};
}
const userOptions = computed(() =>
  allUsers.value.map(u => ({
    label: u.name,
    value: u.id,
  }))
);

onMounted(async () => {
  const [reportRes, usersRes] = await Promise.all([
    loadReport(),
    axios.get('/api/service-users-full'),
  ]);

  allUsers.value = usersRes.data;
});

</script>

<template>
  <div class="p-6">

    <h1 class="text-2xl font-bold mb-6">
      Attendance Analytics
    </h1>

    <!-- SUMMARY -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

      <div class="border p-4 rounded">
        <div>Total Unique</div>
        <div class="text-2xl font-bold">
          {{ summary?.total_unique_attendees }}
        </div>
      </div>

    </div>

    <!-- FILTERS -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

  <div>
    <label for="from-date" class="block text-sm font-medium mb-1">
      From Date
    </label>
    <input
      id="from-date"
      v-model="filters.from"
      type="date"
      class="input"
    />
  </div>

  <div>
    <label for="to-date" class="block text-sm font-medium mb-1">
      To Date
    </label>
    <input
      id="to-date"
      v-model="filters.to"
      type="date"
      class="input"
    />
  </div>

  <div>
    <label for="chart-mode" class="block text-sm font-medium mb-1">
      Report Type
    </label>
    <select
      id="chart-mode"
      v-model="filters.chart_mode"
      class="input"
    >
      <option value="total">Total</option>
      <option value="gender">By Gender (Stacked)</option>
      <option value="housing">By Housing (Stacked)</option>
      <option value="registration">By Registration Status (Stacked)</option>
    </select>
  </div>

  <div>
    <label for="gender-filter" class="block text-sm font-medium mb-1">
      Gender
    </label>
    <select
      id="gender-filter"
      v-model="filters.gender"
      class="input"
    >
      <option value="">All Genders</option>
      <option value="male">Male</option>
      <option value="female">Female</option>
      <option value="other">Other</option>
    </select>
  </div>

  <div>
    <label for="housing-filter" class="block text-sm font-medium mb-1">
      Housing Status
    </label>
    <select
      id="housing-filter"
      v-model="filters.housing_status"
      class="input"
    >
      <option value="">All Housing</option>
      <option value="homeless">Rough Sleeper</option>
      <option value="hostel">Hostel</option>
      <option value="housed">Housed</option>
    </select>
  </div>

  <div>
    <label for="registration-filter" class="block text-sm font-medium mb-1">
      Registration Status
    </label>
    <select
      id="registration-filter"
      v-model="filters.registered"
      class="input"
    >
      <option value="">All Users</option>
      <option value="registered">Registered</option>
      <option value="unregistered">Unregistered</option>
    </select>
  </div>

  <div>
    <label
      id="service-user-filter-label"
      class="block text-sm font-medium mb-1"
    >
      Service Users
    </label>

    <Multiselect
      v-model="filters.service_users"
      :options="userOptions"
      :multiple="true"
      :close-on-select="false"
      :searchable="true"
      track-by="value"
      label="label"
      placeholder="Filter specific users"
      aria-labelledby="service-user-filter-label"
      class="input"
    />
  </div>

</div>

    <button
      @click="loadReport"
      class="btn btn-primary mb-6"
    >
      Apply Filters
    </button>

    <!-- CHART -->
    <div style="height:60vh">

        <Bar
        :data="chartData"
        :options="{
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { stacked: filters.chart_mode !== 'total' },
                y: { stacked: filters.chart_mode !== 'total',
                     ticks: {
                        precision: 0, 
                      }, 
                    }
            }
        }"
        />
    </div>

  </div>
</template>

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