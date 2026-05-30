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
const { data } = await axios.get('/api/reports/attendance', {
  params: {
    ...filters,
    service_users: filters.service_users.map((u: any) => u.value),
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

      <input v-model="filters.from" type="date" class="input" />
      <input v-model="filters.to" type="date" class="input" />

      <select v-model="filters.chart_mode" class="input">
        <option value="total">Total</option>
        <option value="gender">By Gender (Stacked)</option>
        <option value="housing">By Housing (Stacked)</option>
        <option value="registration">By Registration Status (Stacked)</option>
      </select>

      <select v-model="filters.gender" class="input">
        <option value="">All Genders</option>
        <option value="male">Male</option>
        <option value="female">Female</option>
        <option value="other">Other</option>
      </select>

      <select v-model="filters.housing_status" class="input">
        <option value="">All Housing</option>
        <option value="homeless">Rough Sleeper</option>
        <option value="hostel">Hostel</option>
        <option value="housed">Housed</option>
      </select>

      <select v-model="filters.registered" class="input">
        <option value="">All Users</option>
        <option value="registered">Registered</option>
        <option value="unregistered">Unregistered</option>
      </select>
<!-- USER FILTER -->
<Multiselect
  v-model="filters.service_users"
  :options="userOptions"
  :multiple="true"
  :close-on-select="false"
  :searchable="true"
  track-by="value"
  label="label"
  placeholder="Filter specific users"
  class="input"
/>
    </div>

    <button
      @click="loadReport"
      class="btn btn-primary mb-6"
    >
      Apply Filters
    </button>

    <!-- CHART -->
    <div style="height:800px">

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