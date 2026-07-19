<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Multiselect from 'vue-multiselect';
import { computed, ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const terminology = page.props.terminology as Record<string, string>;

defineOptions({
    layout: AppLayout,
});


interface Attendee {
    id: number;
    name: string;
}


interface ServiceItem {
    id: number;
    name: string;
    short: string|null;
}


interface ServiceCategory {
    id: number;
    name: string;
    items: ServiceItem[];
}


interface Eligibility {
    allowed: boolean;
    reason?: string;
}


interface ServiceRecord {
    userId: number;
    displayName: string;

    services: Record<number, boolean>;

    eligibility?: Record<number, Eligibility>;
}


const selectedDate = ref(
    new Date().toISOString().split('T')[0]
);


const attendees = ref<Attendee[]>([]);

const serviceCategories = ref<ServiceCategory[]>([]);

const serviceRecords = ref<ServiceRecord[]>([]);

const selectedUser = ref<any>(null);

const attendeeOptions = computed(() =>
    attendees.value.map(user => ({
        label: user.name,
        value: user.id,
        user,
    }))
);



async function loadAttendees()
{
    const { data } = await axios.get(
        '/api/services-provided/attendees'
    );

    attendees.value = data;
}



async function loadServiceOptions()
{
    const { data } = await axios.get(
        '/api/services-provided/options'
    );

    serviceCategories.value = data;
}



async function loadServices()
{
    const { data } = await axios.get(
        '/api/services-provided',
        {
            params:{
                date:selectedDate.value
            }
        }
    );

    serviceRecords.value = data;
}

function serviceEligibility(
    record:ServiceRecord,
    item:ServiceItem
)
{
    return record.eligibility?.[item.id] ?? {
        allowed:true
    };
}


async function saveRecord(record: ServiceRecord)
{
    try {

        await axios.post(
            '/api/services-provided',
            {
                service_user_id: record.userId,
                attendance_date: selectedDate.value,
                services: record.services,
            }
        );

    } catch (error:any) {

        if (
            error.response?.data?.eligible === false
        ) {

            alert(
                error.response.data.reason
            );

            await loadServices();

            return;
        }

        throw error;
    }
}



async function toggleService(
    record: ServiceRecord,
    item: ServiceItem,
    event:any
) {

    const selected = event.target.checked;


    try {

        await axios.post(
            '/api/services-provided',
            {
                service_user_id: record.userId,
                attendance_date: selectedDate.value,
                service_item_id: item.id,
                selected,
            }
        );


        record.services[item.id] = selected;


    } catch(error:any) {

        event.target.checked = false;


        if(error.response?.data?.reason) {

            const proceed = confirm(
                `${error.response.data.reason}\n\nContinue anyway?`
            );


            if(proceed) {

                await axios.post(
                    '/api/services-provided',
                    {
                        service_user_id: record.userId,
                        attendance_date: selectedDate.value,
                        service_item_id: item.id,
                        selected:true,
                        override:true,
                    }
                );


                record.services[item.id] = true;
            }
        }
    }
}


watch(
    selectedDate,
    loadServices
);



watch(
    selectedUser,
    async option => {

        if (!option) {
            return;
        }


        const existingIndex =
            serviceRecords.value.findIndex(
                record => record.userId === option.value
            );


        if (existingIndex >= 0) {

            const row =
                serviceRecords.value.splice(
                    existingIndex,
                    1
                )[0];


            serviceRecords.value.unshift(row);

            return;
        }


        const { data } = await axios.get(
            `/api/services-provided/${option.value}`,
            {
                params:{
                    date:selectedDate.value
                }
            }
        );


        serviceRecords.value.unshift(data);
    }
);



onMounted(async () => {

    await Promise.all([
        loadAttendees(),
        loadServiceOptions(),
        loadServices(),
    ]);

});

</script>



<template>

<div class="p-6">

    <div class="mb-4 flex gap-4 items-center">

        <label>
            Date
        </label>

        <input
            type="date"
            v-model="selectedDate"
            class="border rounded px-3 py-2"
        />

    </div>


    <div class="mb-6">

        <Multiselect
            v-model="selectedUser"
            :options="attendeeOptions"
            label="label"
            track-by="value"
            :searchable="true"
            placeholder="Select attendee"
        />

    </div>



    <table class="w-full border-collapse">

        <thead>

        <tr class="bg-gray-50">

            <th class="border p-3 text-left">
                {{terminology.service_user}}
            </th>

            <th class="border p-3 text-left">
                Services
            </th>

        </tr>

        </thead>


        <tbody>


        <tr
            v-for="record in serviceRecords"
            :key="record.userId"
        >

            <td class="border p-3 align-top font-semibold">

                {{ record.displayName }}

            </td>


            <td class="border p-3">


                <div
                    v-for="category in serviceCategories"
                    :key="category.id"
                    class="mb-5"
                >

                    <h3 class="font-semibold mb-2">
                        {{ category.name }}
                    </h3>


                    <div class="flex flex-wrap gap-2">


                        <label
    v-for="item in category.items"
    :key="item.id"
    class="border rounded p-2 flex flex-col"
    :class="{
        'bg-red-50 border-red-300':
            !serviceEligibility(record,item).allowed
    }"
>

<div class="flex items-center gap-2">

    <input
        type="checkbox"
        :checked="record.services?.[item.id]"
        @change="toggleService(record,item,$event)"
    />


    <span>
        {{ item.name }}
    </span>


    <small
        v-if="item.short"
        class="text-gray-500"
    >
        ({{ item.short }})
    </small>

</div>

<small
    v-if="record.eligibility?.[item.id]?.reason"
    class="block mt-1 text-xs"
    :class="{
        'text-red-600': record.eligibility[item.id].type === 'rule',
        'text-yellow-600': record.eligibility[item.id].type === 'frequency',
    }"
>
    {{ record.eligibility[item.id].reason }}
</small>


                        </label>


                    </div>


                </div>


            </td>


        </tr>



        <tr v-if="!serviceRecords.length">

            <td
                colspan="2"
                class="p-6 text-center text-gray-500"
            >
                No attendees found.
            </td>

        </tr>


        </tbody>

    </table>


</div>

</template>