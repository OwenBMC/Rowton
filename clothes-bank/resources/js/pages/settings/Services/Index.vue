<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineOptions({
    layout: AppLayout,
});


interface Category {
    id: number;
    name: string;
    management_type: string;
    active: boolean;
}


interface ServiceItem {
    id: number;
    name: string;
    short: string | null;
    active: boolean;
    service_category_id: number;

    category: Category;
}


const props = defineProps<{
    services: ServiceItem[];
    categories: Category[];
}>();


const selectedCategory = ref<number | null>(null);


const filteredServices = computed(() => {

    if (!selectedCategory.value) {
        return props.services;
    }

    return props.services.filter(
        service =>
            service.service_category_id === selectedCategory.value
    );
});


const newCategory = ref({
    name: '',
    management_type: 'checkbox',
    active: true,
});


const newService = ref({
    service_category_id: '',
    name: '',
    short: '',
    active: true,
});



function addCategory()
{
    router.post(
        '/admin/settings/service-categories',
        newCategory.value,
        {
            onSuccess() {
                newCategory.value = {
                    name: '',
                    management_type: 'checkbox',
                    active: true,
                };
            },
        }
    );
}



function saveCategory(category: Category)
{
    router.put(
        `/admin/settings/service-categories/${category.id}`,
        {
            name: category.name,
            management_type: category.management_type,
            active: category.active,
        }
    );
}



function disableCategory(category: Category)
{
    if (!confirm(`Disable ${category.name}?`)) {
        return;
    }


    router.put(
        `/admin/settings/service-categories/${category.id}`,
        {
            ...category,
            active: false,
        }
    );
}



function addService()
{
    router.post(
        '/admin/settings/services',
        newService.value,
        {
            onSuccess() {

                newService.value = {
                    service_category_id: '',
                    name: '',
                    short: '',
                    active: true,
                };

            },
        }
    );
}



function saveService(service: ServiceItem)
{
    router.put(
        `/admin/settings/services/${service.id}`,
        {
            service_category_id: service.service_category_id,
            name: service.name,
            short: service.short,
            active: service.active,
        }
    );
}



function disableService(service: ServiceItem)
{
    if (!confirm(`Disable ${service.name}?`)) {
        return;
    }


    router.put(
        `/admin/settings/services/${service.id}`,
        {
            ...service,
            active: false,
        }
    );
}

</script>


<template>

<div class="p-6 max-w-6xl">


    <h1 class="text-2xl font-semibold mb-2">
        Services
    </h1>


    <p class="text-gray-600 mb-6">
        Configure service categories and the individual items available.
    </p>



    <!-- Categories -->

    <div class="border rounded-lg p-5 mb-8">


        <h2 class="font-semibold text-lg mb-4">
            Service categories
        </h2>



        <div class="flex gap-3 mb-5">

            <input
                v-model="newCategory.name"
                placeholder="Category name"
                class="border rounded px-3 py-2"
            />


            <select
                v-model="newCategory.management_type"
                class="border rounded px-3 py-2"
            >

                <option value="checkbox">
                    Checkbox list
                </option>

                <option value="multiselect">
                    Multi-select
                </option>

            </select>


            <button
                @click="addCategory"
                class="border rounded px-4 py-2"
            >
                Add
            </button>

        </div>



        <table class="w-full">

            <thead>
                <tr class="border-b">
                    <th class="text-left p-2">
                        Name
                    </th>

                    <th>
                        Input type
                    </th>

                    <th>
                        Active
                    </th>

                    <th></th>
                </tr>
            </thead>


            <tbody>

                <tr
                    v-for="category in categories"
                    :key="category.id"
                    class="border-b"
                >

                    <td class="p-2">

                        <input
                            v-model="category.name"
                            class="border rounded px-2 py-1"
                        />

                    </td>


                    <td class="text-center">

                        <select
                            v-model="category.management_type"
                            class="border rounded px-2 py-1"
                        >
                            <option value="checkbox">
                                Checkbox
                            </option>

                            <option value="multiselect">
                                Multiselect
                            </option>

                        </select>

                    </td>


                    <td class="text-center">

                        <input
                            type="checkbox"
                            v-model="category.active"
                        />

                    </td>


                    <td class="text-right">

                        <button
                            @click="saveCategory(category)"
                            class="border rounded px-3 py-1 mr-2"
                        >
                            Save
                        </button>



                    </td>

                </tr>

            </tbody>

        </table>


    </div>





    <!-- Services -->


    <div class="border rounded-lg p-5 mb-6">


        <h2 class="font-semibold text-lg mb-4">
            Add service item
        </h2>



        <div class="grid md:grid-cols-4 gap-3">


            <select
                v-model="newService.service_category_id"
                class="border rounded px-3 py-2"
            >

                <option value="">
                    Select category
                </option>


                <option
                    v-for="category in categories.filter(c => c.active)"
                    :key="category.id"
                    :value="category.id"
                >
                    {{ category.name }}
                </option>

            </select>



            <input
                v-model="newService.name"
                placeholder="Name"
                class="border rounded px-3 py-2"
            />



            <input
                v-model="newService.short"
                placeholder="Short code"
                class="border rounded px-3 py-2"
            />



            <button
                @click="addService"
                class="border rounded px-4"
            >
                Add
            </button>


        </div>


    </div>




    <div class="flex items-center gap-3 mb-4">

        <label class="font-semibold">
            Category:
        </label>


        <select
            v-model="selectedCategory"
            class="border rounded px-3 py-2"
        >

            <option :value="null">
                All categories
            </option>


            <option
                v-for="category in categories"
                :key="category.id"
                :value="category.id"
            >
                {{ category.name }}
            </option>

        </select>

    </div>




    <div class="border rounded-lg overflow-hidden">


        <table class="w-full">


            <thead class="bg-gray-50">

                <tr>

                    <th class="text-left p-3">
                        Name
                    </th>

                    <th>
                        Category
                    </th>

                    <th>
                        Short
                    </th>

                    <th>
                        Active
                    </th>

                    <th></th>

                </tr>

            </thead>



            <tbody>


                <tr
                    v-for="service in filteredServices"
                    :key="service.id"
                    class="border-t"
                >


                    <td class="p-3">

                        <input
                            v-model="service.name"
                            class="border rounded px-2 py-1"
                        />

                    </td>



                    <td class="text-center">

                        {{ categories.find(cat => cat.id === service.service_category_id).name }}

                    </td>



                    <td class="text-center">

                        <input
                            v-model="service.short"
                            class="border rounded px-2 py-1 w-20"
                        />

                    </td>



                    <td class="text-center">

                        <input
                            type="checkbox"
                            v-model="service.active"
                        />

                    </td>



                    <td class="text-right p-3">

                        <button
                            @click="saveService(service)"
                            class="border rounded px-3 py-1 mr-2"
                        >
                            Save
                        </button>


                    </td>


                </tr>



                <tr v-if="!filteredServices.length">

                    <td
                        colspan="5"
                        class="p-6 text-center text-gray-500"
                    >
                        No services configured.
                    </td>

                </tr>


            </tbody>


        </table>


    </div>


</div>

</template>