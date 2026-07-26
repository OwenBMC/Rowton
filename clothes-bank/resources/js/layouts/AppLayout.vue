<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { usePage } from '@inertiajs/vue3';
import type { BreadcrumbItemType } from '@/types';
import VolunteerSelectionModal from '@/components/VolunteerSelectionModal.vue';
import { useVolunteerStore } from '@/stores/useVolunteerStore';
import { computed } from 'vue';


interface Props {
  breadcrumbs?: BreadcrumbItemType[];
}

const page = usePage();
const authUser = page.props.auth?.user;
const volunteerStore = useVolunteerStore();
const isVolunteerAccount = computed(() => page.props.auth?.user?.user_type === 'volunteer_shared');

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => [],
});
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs" :user="authUser">
    <slot />
  </AppLayout>
  <div>
        <!-- Active Volunteer Banner / Switcher in Header (Optional) -->
        <div 
            v-if="isVolunteerAccount && volunteerStore.activeVolunteer" 
            class="bg-amber-500/10 border-b border-amber-500/20 px-4 py-1.5 flex items-center justify-between text-xs text-amber-700 dark:text-amber-300"
        >
            <span>
                Active Volunteer: <strong>{{ volunteerStore.activeVolunteer.first_name }} {{ volunteerStore.activeVolunteer.last_name }}</strong>
            </span>
            <Button 
                variant="ghost" 
                size="sm" 
                class="h-6 px-2 text-xs" 
                @click="volunteerStore.clearActiveVolunteer()"
            >
                Switch Volunteer
            </Button>
        </div>

        <!-- Your App Navigation / Main Layout Content -->
        <slot />

        <!-- Force Selection Modal -->
        <VolunteerSelectionModal />
    </div>
</template>
