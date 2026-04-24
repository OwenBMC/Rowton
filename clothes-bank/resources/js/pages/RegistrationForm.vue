<script setup lang="ts">
import { ref, reactive, onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import type { RegistrationFormData } from '@/types/registration';

// Inertia props
const props = defineProps<{
  serviceUserId?: number | null;
  service_user?: any;
  unregistered_users?: Array<{
    id: number;
    first_name: string;
    middle_names?: string;
    surname?: string;
    name?: string;
  }>;
}>();

// Use AppLayout
defineOptions({ layout: AppLayout });

// Today’s date helper
const todaysDate = new Date().toISOString().split('T')[0];

// Form state
const form = reactive<RegistrationFormData>({
  first_name: props.service_user?.first_name || '',
  middle_names: props.service_user?.middle_names || '',
  surname: props.service_user?.surname || '',
  dob: props.service_user?.dob || '',
  address: props.service_user?.address || '',
  postcode: props.service_user?.postcode || '',
  contact_number: props.service_user?.contact_number || '',
  food_allergies: props.service_user?.food_allergies || false,
  referral_date: props.service_user?.registration?.referral_date || todaysDate,
  nok_name: props.service_user?.next_of_kin?.name || '',
  nok_relationship: props.service_user?.next_of_kin?.relationship || '',
  nok_address: props.service_user?.next_of_kin?.address || '',
  nok_contact_number: props.service_user?.next_of_kin?.contact_number || '',
  gp_name: props.service_user?.doctors?.[0]?.name || '',
  gp_address: props.service_user?.doctors?.[0]?.address || '',
  gp_contact_number: props.service_user?.doctors?.[0]?.contact_number || '',
  service_user_signature_date: props.service_user?.registration?.service_user_signature_date || todaysDate,
  volunteer_signature_date: props.service_user?.registration?.volunteer_signature_date || todaysDate,
});

// Selected user
const selectedServiceUserId = ref<number | ''>(props.serviceUserId ?? '');
const unregisteredUsers = ref(props.unregistered_users || []);
const successMessage = ref('');
const errorMessage = ref('');

// Fetch full details for selected user
const fetchSelectedUser = async (id: number) => {
  if (!id) return;
  try {
    const { data } = await axios.get(`/api/service-users/${id}`, { withCredentials: true });
    const su = data.service_user;
    if (!su) return;

    form.first_name = su.first_name;
    form.middle_names = su.middle_names || '';
    form.surname = su.surname || '';
    form.dob = su.dob || '';
    form.address = su.address || '';
    form.postcode = su.postcode || '';
    form.contact_number = su.contact_number || '';
    form.food_allergies = su.food_allergies || false;
    form.nok_name = su.next_of_kin?.name || '';
    form.nok_relationship = su.next_of_kin?.relationship || '';
    form.nok_address = su.next_of_kin?.address || '';
    form.nok_contact_number = su.next_of_kin?.contact_number || '';
    form.gp_name = su.doctors?.[0]?.name || '';
    form.gp_address = su.doctors?.[0]?.address || '';
    form.gp_contact_number = su.doctors?.[0]?.contact_number || '';
    form.referral_date = su.registration?.referral_date || todaysDate;
    form.service_user_signature_date = su.registration?.service_user_signature_date || todaysDate;
    form.volunteer_signature_date = su.registration?.volunteer_signature_date || todaysDate;
  } catch (err) {
    console.error(err);
    errorMessage.value = 'Failed to fetch service user details.';
  }
};

// Watch selected user
watch(selectedServiceUserId, async (newId) => {
  if (!newId) {
    // New user — clear fields
    form.first_name = '';
    form.middle_names = '';
    form.surname = '';
    form.dob = '';
    form.address = '';
    form.postcode = '';
    form.contact_number = '';
    form.food_allergies = false;
    form.nok_name = '';
    form.nok_relationship = '';
    form.nok_address = '';
    form.nok_contact_number = '';
    form.gp_name = '';
    form.gp_address = '';
    form.gp_contact_number = '';
    form.referral_date = todaysDate;
    form.service_user_signature_date = todaysDate;
    form.volunteer_signature_date = todaysDate;
    return;
  }

  // Prefill names from unregisteredUsers if available
  const selected = unregisteredUsers.value.find(u => u.id === newId);
  if (selected) {
    form.first_name = selected.first_name;
    form.middle_names = selected.middle_names || '';
    form.surname = selected.surname || '';
  }

  // Fetch full details from API
  await fetchSelectedUser(newId);
});

// Submit
const submitForm = async () => {
  try {
    const url = selectedServiceUserId.value
      ? `/api/registration/${selectedServiceUserId.value}`
      : '/api/registration';

    await axios.post(url, form, { withCredentials: true });
    successMessage.value = 'Registration saved successfully!';
    errorMessage.value = '';
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'There was a problem saving the registration.';
    successMessage.value = '';
  }
};
</script>

<template>
  <div class="m-2">
    <h1 class="text-2xl font-bold mb-6">Service User Registration</h1>

    <form @submit.prevent="submitForm">
      <!-- SERVICE USER -->
      <fieldset class="mb-6 border p-4 rounded">

        <!-- Select existing or add new -->
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Select Existing User or Add New</label>
          <select v-model="selectedServiceUserId" class="input">
            <option value="">Add New</option>
            <option v-for="user in unregisteredUsers" :key="user.id" :value="user.id">
              {{ user.first_name }} {{ user.surname }}
            </option>
          </select>
        </div>

        <!-- Name fields -->
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">First Name</label>
          <input v-model="form.first_name" type="text" class="input" placeholder="John" />
        </div>
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Middle Name(s)</label>
          <input v-model="form.middle_names" type="text" class="input" placeholder="Alfred" />
        </div>
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Surname</label>
          <input v-model="form.surname" type="text" class="input" placeholder="Doe" />
        </div>

        <!-- DOB / Address / Contact -->
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Date of Birth</label>
          <input v-model="form.dob" type="date" class="input" />
        </div>
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Address</label>
          <input v-model="form.address" type="text" class="input" placeholder="123 Main Street" />
        </div>
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Postcode</label>
          <input v-model="form.postcode" type="text" class="input" placeholder="AB12 3CD" />
        </div>
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Contact Number</label>
          <input v-model="form.contact_number" type="text" class="input" placeholder="01234 567890" />
        </div>

        <div class="mb-3 flex items-center">
          <input id="food_allergies" v-model="form.food_allergies" type="checkbox" class="mr-2" />
          <label for="food_allergies" class="text-sm">Has food allergies</label>
        </div>

        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Referral Date</label>
          <input v-model="form.referral_date" type="date" class="input" />
        </div>
      </fieldset>

      <!-- NEXT OF KIN -->
      <fieldset class="mb-6 border p-4 rounded">
        <legend class="font-semibold mb-2">Next of Kin</legend>

        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Name</label>
          <input v-model="form.nok_name" type="text" class="input" placeholder="Jane Doe" />
        </div>
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Relationship</label>
          <input v-model="form.nok_relationship" type="text" class="input" placeholder="Sister" />
        </div>
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Address</label>
          <input v-model="form.nok_address" type="text" class="input" placeholder="123 Elm Street" />
        </div>
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Contact Number</label>
          <input v-model="form.nok_contact_number" type="text" class="input" placeholder="01234 567890" />
        </div>
      </fieldset>

      <!-- DOCTOR -->
      <fieldset class="mb-6 border p-4 rounded">
        <legend class="font-semibold mb-2">Doctor</legend>

        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">G.P Name</label>
          <input v-model="form.gp_name" type="text" class="input" placeholder="Dr. Smith" />
        </div>
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Address</label>
          <input v-model="form.gp_address" type="text" class="input" placeholder="456 Oak Street" />
        </div>
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Contact Number</label>
          <input v-model="form.gp_contact_number" type="text" class="input" placeholder="01234 567890" />
        </div>
      </fieldset>

      <!-- SIGNATURES -->
      <fieldset class="mb-6 border p-4 rounded">
        <legend class="font-semibold mb-2">Signatures</legend>

        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Service User Signature Date</label>
          <input v-model="form.service_user_signature_date" type="date" class="input" />
        </div>
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Volunteer Signature Date</label>
          <input v-model="form.volunteer_signature_date" type="date" class="input" />
        </div>
      </fieldset>

      <button type="submit" class="btn btn-primary">Submit Registration</button>

      <p v-if="successMessage" class="mt-4 text-green-600">{{ successMessage }}</p>
      <p v-if="errorMessage" class="mt-4 text-red-600">{{ errorMessage }}</p>
    </form>
  </div>
</template>

<style scoped>
.input {
  display: block;
  width: 100%;
  padding: 0.5rem;
  margin-bottom: 0.25rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}
.btn {
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
}
.btn-primary {
  background-color: #3b82f6;
  color: white;
  border: none;
}
</style>