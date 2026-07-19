<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({
    layout: AppLayout,
});

interface EnumDefinition {
    id: number;
    key: string;
    label: string;
    active: boolean;
    sort_order: number;
}

const props = defineProps<{
    group: string;
    enums: EnumDefinition[];
}>();

const newLabel = ref('');

function addEnum() {
    if (!newLabel.value.trim()) {
        return;
    }

    router.post(
        `/admin/settings/enums/${props.group}`,
        {
            label: newLabel.value,
        },
        {
            onSuccess: () => {
                newLabel.value = '';
            },
        }
    );
}

function updateEnum(item: EnumDefinition) {
    router.put(
        `/admin/settings/enums/${item.id}`,
        {
            label: item.label,
            active: item.active,
            sort_order: item.sort_order,
        }
    );
}

function deleteEnum(item: EnumDefinition) {
    if (!confirm(`Delete "${item.label}"?`)) {
        return;
    }

    router.delete(`/admin/settings/enums/${item.id}`);
}
</script>

<template>
    <div class="p-6 max-w-4xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <Link
                    href="/admin/settings/enums"
                    class="text-sm text-gray-500 hover:underline"
                >
                    ← Back to enums
                </Link>

                <h1 class="text-2xl font-semibold mt-2 capitalize">
                    {{ group.replaceAll('_', ' ') }}
                </h1>

                <p class="text-gray-600 mt-1">
                    Manage the available options for this enum.
                </p>
            </div>
        </div>


        <div class="border rounded-lg p-4 mb-6">
            <h2 class="font-semibold mb-3">
                Add new value
            </h2>

            <div class="flex gap-3">
                <input
                    v-model="newLabel"
                    type="text"
                    placeholder="Display name"
                    class="border rounded px-3 py-2 flex-1"
                />

                <button
                    @click="addEnum"
                    class="bg-black text-white rounded px-4 py-2"
                >
                    Add
                </button>
            </div>
        </div>


        <div class="border rounded-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-3">
                            Display name
                        </th>

                        <th class="p-3">
                            Key
                        </th>

                        <th class="p-3">
                            Active
                        </th>

                        <th class="p-3">
                            Order
                        </th>

                        <th class="p-3">
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="item in enums"
                        :key="item.id"
                        class="border-t"
                    >
                        <td class="p-3">
                            <input
                                v-model="item.label"
                                class="border rounded px-2 py-1 w-full"
                            />
                        </td>

                        <td class="p-3 text-gray-500">
                            {{ item.key }}
                        </td>

                        <td class="p-3 text-center">
                            <input
                                type="checkbox"
                                v-model="item.active"
                            />
                        </td>

                        <td class="p-3">
                            <input
                                type="number"
                                v-model="item.sort_order"
                                class="border rounded px-2 py-1 w-20"
                            />
                        </td>

                        <td class="p-3 flex gap-2 justify-end">
                            <button
                                @click="updateEnum(item)"
                                class="border rounded px-3 py-1 hover:bg-gray-50"
                            >
                                Save
                            </button>

                            <button
                                @click="deleteEnum(item)"
                                class="text-red-600 border border-red-200 rounded px-3 py-1 hover:bg-red-50"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                    <tr v-if="!enums?.length">
                        <td
                            colspan="5"
                            class="p-6 text-center text-gray-500"
                        >
                            No values configured.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>