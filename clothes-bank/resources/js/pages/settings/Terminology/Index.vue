<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';

defineOptions({
    layout: AppLayout,
});

interface Term {
    id: number;
    key: string;
    label: string;
    value: string;
}

const props = defineProps<{
    terms: Term[];
}>();

function save(term: Term) {
    router.put(
        `/admin/settings/terminology/${term.id}`,
        {
            value: term.value,
        }
    );
}
</script>

<template>
    <div class="p-6 max-w-3xl">

        <h1 class="text-2xl font-semibold mb-2">
            Terminology
        </h1>

        <p class="text-gray-600 mb-6">
            Change the wording used throughout the system.
        </p>


        <div class="border rounded-lg divide-y">

            <div
                v-for="term in terms"
                :key="term.id"
                class="p-4 flex items-center gap-4"
            >

                <div class="flex-1">
                    <div class="font-semibold">
                        {{ term.label }}
                    </div>

                    <div class="text-sm text-gray-500">
                        {{ term.key }}
                    </div>
                </div>


                <input
                    v-model="term.value"
                    class="border rounded px-3 py-2"
                />


                <button
                    @click="save(term)"
                    class="border rounded px-3 py-2 hover:bg-gray-50"
                >
                    Save
                </button>

            </div>

        </div>

    </div>
</template>