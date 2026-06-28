<script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue';
    import Multiselect from 'vue-multiselect';
    import { ElTimePicker } from 'element-plus';
    import { computed, ref, onMounted, watch } from 'vue';
    import axios from 'axios';
    import type { BreadcrumbItem } from '@/types';

    defineOptions({ layout: AppLayout });

    interface Attendee {
  id: number;
  name: string;
}

interface Toiletry {
  label: string;
  short: string;
}

interface ServiceRecord {
    userId: number;
    displayName: string;
    services: Record<string, boolean>;
    toiletries: Toiletry[];

    lastIssued: Record<
        string,
        {
            date: string | null;
            daysAgo: number | null;
        }
    >;
}

const clothingOptions = [
'Coat', 'Hoodie', 'Sweat-shirt', 'Tee-shirt', 'Top',
'Tracksuit bottoms', 'Jeans', 'Shoes', 'Socks', 'Underwear', 'Hats', 'Scarves', 'Gloves',
];

const clothingLimits = {
    Coat: 180,
    Shoes: 90,
    Hoodie: 30,
};

function getClothingStatus(item: string, daysAgo: number | null) {

    const limit = clothingLimits[item as keyof typeof clothingLimits];

    if (daysAgo == null) {
        return 'none';
    }

    if (limit == null) {
        return 'neutral';
    }

    const ratio = daysAgo / limit;

    if (ratio >= 1) return 'safe';        // at or beyond limit
    if (ratio >= 0.7) return 'warning';   // approaching limit
    return 'danger';                      // too soon
}

async function toggleService(record, item, event) {

    const checked = event.target.checked;

    if (!checked) {
        record.services[item] = false;
        await saveRecord(record);
        return;
    }
    console.log(record)
    const history = record.lastIssued[item];

    const limit = clothingLimits[item];

    if (
        history &&
        history.daysAgo !== null &&
        history.daysAgo < limit
    ) {

        const confirmed = window.confirm(
            `${record.displayName} received ${item} only ${history.daysAgo} .\n\nIssue another anyway?`
        );

        if (!confirmed) {
            event.target.checked = false;
            return;
        }
    }

    record.services[item] = true;

    await saveRecord(record);
}

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


const selectedDate = ref(
  new Date().toISOString().split('T')[0]
);

const selectedUser = ref<any>(null);

const attendees = ref<Attendee[]>([]);

const serviceRecords = ref<ServiceRecord[]>([]);

const attendeeOptions = computed(() =>
    attendees.value.map(u => ({
        label: u.name,
        value: u.id,
        user: u,
    }))
);

  const tempRecord = {
    services: {},
    toiletries: [],
  };



async function loadAttendees() {
  const { data } = await axios.get(
    '/api/services-provided/attendees'
  );

  attendees.value = data;
}

async function loadServices() {
  const { data } = await axios.get(
    '/api/services-provided',
    {
      params: {
        date: selectedDate.value,
      },
    }
  );

  serviceRecords.value = data;
}

async function saveRecord(record: ServiceRecord) {
  await axios.post('/api/services-provided', {
    service_user_id: record.userId,
    attendance_date: selectedDate.value,
    services: record.services,
    toiletries: record.toiletries,
  });
}

watch(selectedDate, loadServices);



watch(selectedUser, async option => {

    if (!option) return;

    const existingIndex =
        serviceRecords.value.findIndex(r => r.userId === option.value);

    if (existingIndex >= 0) {
        const row = serviceRecords.value.splice(existingIndex, 1)[0];
        serviceRecords.value.unshift(row);
        return;
    }

    const { data } = await axios.get(
        `/api/services-provided/${option.value}`,
        {
            params: { date: selectedDate.value }
        }
    );

    serviceRecords.value.unshift(data);
});

onMounted(async () => {
  await Promise.all([
    loadAttendees(),
    loadServices(),
  ]);
});

</script>

<template>
    <div class="mb-4 flex gap-4 items-center">
    <label for="services-date">Date</label>

    <input
        id="services-date"
        type="date"
        v-model="selectedDate"
        class="input"
    />
</div>
    <table>
        <tr>
            <td>
                <Multiselect
                    v-model="selectedUser"
                    :options="attendeeOptions"
                    :searchable="true"
                    label="label"
                    track-by="value"
                    placeholder="Select attendee"
                />
            </td>
        </tr>
        <tr
  v-for="record in serviceRecords"
  :key="record.userId"
>
            <td class="border p-2 align-top font-semibold">
  {{ record.displayName }}
</td>
            <td class="border p-2 align-top">
                <label class="font-semibold text-sm block mb-1">Clothes:</label>
                <div class="flex flex-wrap gap-2 mb-2">
                  <label
                      v-for="item in clothingOptions"
                      :key="item"
                      class="flex flex-col border rounded p-2"
                  >
                      <div class="flex items-center gap-2">
                          <input
                              type="checkbox"
                              :checked="record.services[item]"
                              @change="toggleService(record, item, $event)"
                          />
                      
                          <span>{{ item }}</span>
                      </div>
                    
                      <small
                          class="font-semibold"
                          :class="{
                              'text-green-600': getClothingStatus(item, record.lastIssued?.[item]?.daysAgo) === 'safe',
                              'text-yellow-600': getClothingStatus(item, record.lastIssued?.[item]?.daysAgo) === 'warning',
                              'text-red-600': getClothingStatus(item, record.lastIssued?.[item]?.daysAgo) === 'danger',
                              'text-gray-500': record.lastIssued?.[item]?.daysAgo == null,
                          }"
                      >
                          Last given:
                          {{
                              record.lastIssued?.[item]?.daysAgo != null
                                  ? `${record.lastIssued[item].daysAgo}`
                                  : 'Never'
                          }}
                      </small>
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
                  @update:model-value="saveRecord(record)"
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
    </table>
</template>