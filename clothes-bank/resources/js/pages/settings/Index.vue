<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Tags, SlidersHorizontal, Languages, ShieldCheck } from 'lucide-vue-next';

const page = usePage();
const terminology = page.props.terminology as Record<string, string>;

defineOptions({
    layout: AppLayout,
});

const settingsSections = [
    {
        title: 'Enums',
        description: 'Manage configurable lists such as housing statuses, genders, FDA status and referral outcomes.',
        href: '/admin/settings/enums',
        icon: Tags,
    },
    {
        title: 'Services',
        description: 'Configure available services, categories and how services are managed.',
        href: '/admin/settings/services',
        icon: SlidersHorizontal,
    },
    {
        title: 'Eligibility Rules',
        description: 'Control which ${terminology.service_user} can receive specific services based on their attributes.',
        href: '/admin/settings/rules',
        icon: ShieldCheck,
    },
    {
        title: 'Terminology',
        description: 'Change the wording used throughout the system, such as ${terminology.service_user}, attendance and blacklist labels.',
        href: '/admin/settings/terminology',
        icon: Languages,
    },
];
</script>

<template>
    <div class="p-6">
        <h1 class="text-2xl font-semibold mb-2">
            Settings
        </h1>

        <p class="text-gray-600 mb-6">
            Manage application configuration and organisation preferences.
        </p>

        <div class="grid gap-4 md:grid-cols-2">
            <Link
                v-for="section in settingsSections"
                :key="section.title"
                :href="section.href"
                class="border rounded-lg p-5 hover:bg-gray-50 transition flex gap-4"
            >
                <div class="mt-1">
                    <component
                        :is="section.icon"
                        class="w-6 h-6"
                    />
                </div>

                <div>
                    <h2 class="font-semibold text-lg">
                        {{ section.title }}
                    </h2>

                    <p class="text-sm text-gray-600 mt-1">
                        {{ section.description }}
                    </p>
                </div>
            </Link>
        </div>
    </div>
</template>