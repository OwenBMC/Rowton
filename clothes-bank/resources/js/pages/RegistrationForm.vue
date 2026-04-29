<script setup lang="ts">
import { ref, reactive, onMounted, watch, computed } from 'vue';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import Multiselect from 'vue-multiselect';
import type { RegistrationFormData } from '@/types/registration';

const practices = ref<any[]>([]);
const doctors = ref<any[]>([]);

const selectedPractice = ref<any>(null);
const selectedDoctor = ref<any>(null);

const newPracticeData = reactive({
  name: '',
  address_line_1: '',
  address_line_2: '',
  city: '',
  county: '',
  postcode: '',
  phone_number: '',
});

const newDoctorData = reactive({
  name: '',
});

const practiceOptions = computed(() => [
  ...practices.value.map(p => ({
    label: p.name,
    value: p.id,
    practice: p,
  })),
  {
    label: 'Add New Practice',
    value: null,
    isAddNew: true,
  },
]);

const doctorOptions = computed(() => [
  ...doctors.value.map(d => ({
    label: d.name,
    value: d.id,
    doctor: d,
  })),
  {
    label: 'Add New Doctor',
    value: null,
    isAddNew: true,
  },
]);

watch(selectedPractice, async (option) => {
  if (!option) return;

  // NEW PRACTICE
  if (option.isAddNew) {
    Object.assign(newPracticeData, {
      name: '',
      address_line_1: '',
      address_line_2: '',
      city: '',
      county: '',
      postcode: '',
      phone_number: '',
    });

    doctors.value = [];
    selectedDoctor.value = null;
    return;
  }

  // EXISTING PRACTICE
  const p = option.practice;

  Object.assign(newPracticeData, {
    name: p.name || '',
    address_line_1: p.address_line_1 || '',
    address_line_2: p.address_line_2 || '',
    city: p.city || '',
    county: p.county || '',
    postcode: p.postcode || '',
    phone_number: p.phone_number || '',
  });

  // load doctors
  const res = await axios.get(`/api/practices/${p.id}/doctors`);
  doctors.value = res.data;

  selectedDoctor.value = null;
});

watch(selectedDoctor, (option) => {
  if (!option) return;

  if (option.isAddNew) {
    newDoctorData.name = '';
    return;
  }

  const d = option.doctor;

  newDoctorData.name = d.name || '';
});

const selectedPracticeId = ref<number | null>(null);
const selectedDoctorId = ref<number | null>(null);

const savePractice = async () => {
  try {
    // CREATE
    if (selectedPractice.value?.isAddNew) {
      const res = await axios.post('/api/practices', newPracticeData);
      console.log("practice res", res)
      selectedPractice.value = {
        label: newPracticeData.name,
        value: res.data.id,
        practice: res.data,
      };
    }

    // UPDATE
    else if (selectedPractice.value?.value) {
      await axios.put(
        `/api/practices/${selectedPractice.value.value}`,
        newPracticeData
      );
    }

    successMessage.value = 'Practice saved';
  } catch (err) {
    errorMessage.value = 'Failed to save practice';
  }
};

const saveDoctor = async () => {
  try {
    let practiceId = selectedPractice.value?.value;

    if (!practiceId) {
      errorMessage.value = 'Select a practice first';
      return;
    }

    // CREATE
    if (selectedDoctor.value?.isAddNew) {
      const res = await axios.post('/api/doctors', {
        ...newDoctorData,
        practice_id: practiceId,
      });

      selectedDoctor.value = {
        label: newDoctorData.name,
        value: res.data.id,
        doctor: res.data,
      };
    }

    // UPDATE
    else if (selectedDoctor.value?.value) {
      await axios.put(
        `/api/doctors/${selectedDoctor.value.value}`,
        newDoctorData
      );
    }

    successMessage.value = 'Doctor saved';
  } catch (err) {
    errorMessage.value = 'Failed to save doctor';
  }
};

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

defineOptions({ layout: AppLayout });

const todaysDate = new Date().toISOString().split('T')[0];

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
    let practiceId = selectedPractice.value?.value;
    let doctorId = selectedDoctor.value?.value;
    console.log(practiceId, doctorId)
    // CREATE PRACTICE (if new)
    if (selectedPractice.value?.isAddNew) {
      const res = await axios.post('/api/practices', newPracticeData);
      practiceId = res.data.id;
    } else if (practiceId) {
      // OPTIONAL update existing practice
      await axios.put(`/api/practices/${practiceId}`, newPracticeData);
    }

    // CREATE DOCTOR (if new)
    if (selectedDoctor.value?.isAddNew) {
      const res = await axios.post('/api/doctors', {
        ...newDoctorData,
        practice_id: practiceId,
      });

      doctorId = res.data.id;
    } else if (doctorId) {
      // OPTIONAL update existing doctor
      await axios.put(`/api/doctors/${doctorId}`, newDoctorData);
    }

    // attach doctor to registration payload
    console.log("doctorid", doctorId)
    console.log("selectedDoctor.value.doctor", selectedDoctor.value.doctor)
    const payload = {
      ...form,
      doctor_id: selectedDoctor.value.doctor.id,    
    };
    console.log("payload", payload)

    const url = selectedServiceUserId.value
      ? `/api/registration/${selectedServiceUserId.value}`
      : '/api/registration';

    await axios.post(url, payload, { withCredentials: true });

    successMessage.value = 'Registration saved successfully!';
    errorMessage.value = '';
  } catch (err: any) {
    errorMessage.value =
      err.response?.data?.message ||
      'There was a problem saving the registration.';

    successMessage.value = '';
  }
};

onMounted(async () => {
  const res = await axios.get('/api/practices');
  practices.value = res.data;
});

let practiceId = selectedPractice?.value;
let doctorId = selectedDoctor?.value;
watch(selectedPracticeId, async (id) => {
  selectedDoctorId.value = null;

  if (!id) return;

  const res = await axios.get(`/api/practices/${id}/doctors`);
  doctors.value = res.data;
});

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

<fieldset class="mb-6 border p-4 rounded">
  <legend class="font-semibold mb-2">Doctor & Practice</legend>

  <!-- PRACTICE SELECT -->
  <Multiselect
    v-model="selectedPractice"
    :options="practiceOptions"
    :searchable="true"
    :close-on-select="true"
    label="label"
    track-by="value"
    placeholder="Search or add practice"
    class="w-full mb-2"
  >
    <template #option="{ option }">
      <div class="flex justify-between">
        <span>{{ option.label }}</span>
        <span v-if="option.isAddNew" class="text-blue-600 text-xs font-semibold">
          + New
        </span>
      </div>
    </template>
  </Multiselect>

  <!-- NEW PRACTICE INPUT -->
<div v-if="selectedPractice" class="space-y-2 mb-4">
  <input v-model="newPracticeData.name" class="input" placeholder="Practice Name" />
  <input v-model="newPracticeData.address_line_1" class="input" placeholder="Address Line 1" />
  <input v-model="newPracticeData.address_line_2" class="input" placeholder="Address Line 2" />
  <input v-model="newPracticeData.city" class="input" placeholder="City" />
  <input v-model="newPracticeData.county" class="input" placeholder="County" />
  <input v-model="newPracticeData.postcode" class="input" placeholder="Postcode" />
  <input v-model="newPracticeData.phone_number" class="input" placeholder="Phone Number" />
  <button
    type="button"
    class="btn btn-primary"
    @click="savePractice"
  >
    Save Practice
  </button>
</div>

  <!-- DOCTOR SELECT -->
  <Multiselect
    v-if="selectedPractice && !selectedPractice.isAddNew"
    v-model="selectedDoctor"
    :options="doctorOptions"
    :searchable="true"
    :close-on-select="true"
    label="label"
    track-by="value"
    placeholder="Search or add doctor"
    class="w-full mb-2"
  >
    <template #option="{ option }">
      <div class="flex justify-between">
        <span>{{ option.label }}</span>
        <span v-if="option.isAddNew" class="text-blue-600 text-xs font-semibold">
          + New
        </span>
      </div>
    </template>
  </Multiselect>

  <!-- NEW DOCTOR INPUT -->
<div v-if="selectedDoctor" class="space-y-2">
  <input v-model="newDoctorData.name" class="input" placeholder="Doctor Name" />
    <button
    type="button"
    class="btn btn-primary"
    @click="saveDoctor"
    :disabled="!selectedPractice"
  >
    Save Doctor
  </button>
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