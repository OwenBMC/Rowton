import { defineStore } from 'pinia';
import { ref } from 'vue';

export interface Volunteer {
    id: number;
    first_name: string;
    last_name: string;
}

export const useVolunteerStore = defineStore('volunteer', () => {
    const activeVolunteer = ref<Volunteer | null>(
        JSON.parse(localStorage.getItem('active_volunteer') || 'null')
    );

    function setActiveVolunteer(volunteer: Volunteer) {
        activeVolunteer.value = volunteer;
        localStorage.setItem('active_volunteer', JSON.stringify(volunteer));
    }

    function clearActiveVolunteer() {
        activeVolunteer.value = null;
        localStorage.removeItem('active_volunteer');
    }

    return {
        activeVolunteer,
        setActiveVolunteer,
        clearActiveVolunteer,
    };
});