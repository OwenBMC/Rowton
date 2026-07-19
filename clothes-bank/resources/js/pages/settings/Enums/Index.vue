<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';

defineOptions({
    layout: AppLayout,
});

defineProps<{
    groups: {
        group: string;
        count: number;
    }[];
}>();
</script>

<template>
    <h1 class="text-2xl font-bold mb-6">
        Enums
    </h1>

    <div class="rounded border divide-y">
        <a
            v-for="group in groups"
            :key="group.group"
            :href="`/admin/settings/enums/${group.group}`"
            class="flex justify-between items-center p-4 hover:bg-muted transition"
        >
            <span class="capitalize">
                {{ group.group.replaceAll('_', ' ') }}
            </span>

            <span class="text-muted-foreground">
                {{ group.count }}
            </span>
        </a>
    </div>
</template>