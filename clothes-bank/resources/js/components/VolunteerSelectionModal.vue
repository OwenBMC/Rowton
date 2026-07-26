<script setup lang="ts">
import { computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { useVolunteerStore, type Volunteer } from '@/stores/useVolunteerStore';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

const page = usePage();
const volunteerStore = useVolunteerStore();

// Check if user is a shared volunteer account
const isVolunteerAccount = computed(() => page.props.auth?.user?.user_type === 'volunteer_shared');

// List of available volunteers from Inertia share
const volunteers = computed<Volunteer[]>(() => (page.props.volunteers as Volunteer[]) || []);

// Modal stays open if logged into a volunteer account and no volunteer is active
const isOpen = computed(() => isVolunteerAccount.value && !volunteerStore.activeVolunteer);

function selectVolunteer(volunteer: Volunteer) {
    volunteerStore.setActiveVolunteer(volunteer);
}

function handleLogout() {
    router.post('/logout');
}
</script>

<template>
    <Dialog :open="isOpen">
        <!-- Prevent closing modal by clicking overlay or pressing Esc -->
        <DialogContent 
            class="sm:max-w-[425px]" 
            :close-button="false" 
            @pointer-down-outside.prevent 
            @escape-key-down.prevent
        >
            <DialogHeader>
                <DialogTitle>Who is operating this station?</DialogTitle>
                <DialogDescription>
                    Please select your profile to continue. All actions taken will be recorded under your name.
                </DialogDescription>
            </DialogHeader>

            <div class="grid gap-3 py-4 max-h-[300px] overflow-y-auto">
                <Button
                    v-for="volunteer in volunteers"
                    :key="volunteer.id"
                    variant="outline"
                    class="w-full justify-start text-left h-12 text-base font-medium"
                    @click="selectVolunteer(volunteer)"
                >
                    👤 {{ volunteer.first_name }} {{ volunteer.last_name }}
                </Button>

                <div v-if="volunteers.length === 0" class="text-center text-sm text-muted-foreground py-4">
                    No active volunteers found. Please contact an administrator.
                </div>
            </div>

            <div class="pt-2 border-t flex justify-end">
                <Button variant="ghost" size="sm" @click="handleLogout">
                    Sign Out
                </Button>
            </div>
        </DialogContent>
    </Dialog>
</template>