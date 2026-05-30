<template>
  <Head title="Housing Referral Form" />

  <AppLayout>
    <div class="p-4 max-w-3xl">

      <h2 class="text-xl font-bold mb-4">Housing Referral Form</h2>

      <!-- Service User -->
      <Multiselect
        v-model="selectedUser"
        :options="userOptions"
        label="label"
        track-by="value"
        placeholder="Select Service User"
        :disabled="isEditMode || userIdFromQuery"
      />

<div v-if="selectedUser" class="space-y-4 mt-4">

  <!-- Read-only system fields -->
  <fieldset class="mb-6 border p-4 rounded">

    <div>
      <label class="label">Gender</label>
      <input disabled v-model="form.gender" class="input" />
    </div>

    <div>
      <label class="label">Date of Birth</label>
      <input disabled v-model="form.date_of_birth" type="date" class="input" />
    </div>

    <div>
      <label class="label">Contact Number</label>
      <input disabled v-model="form.contact_number" class="input" />
    </div>
  </fieldset>

  <!-- Editable fields -->
  <fieldset class="mb-6 border p-4 rounded">

  <div>
    <label class="label">National Insurance Number</label>
    <input v-model="form.national_insurance_number" class="input"
      placeholder="e.g. QQ123456C" />
  </div>

  <div>
    <label class="label">Nationality</label>
    <input v-model="form.nationality" class="input"
      placeholder="e.g. British/Irish" />
  </div>

  <div>
    <label class="label">Previous Address</label>
    <textarea v-model="form.previous_address" class="input"
      placeholder="Full last known address" />
  </div>

  <!-- Boolean flags -->
  <div class="space-y-2">
    <label class="flex items-center gap-2">
      <input type="checkbox" v-model="form.prison" />
      Prison Release
    </label>

    <label class="flex items-center gap-2">
      <input type="checkbox" v-model="form.hospital" />
      Hospital Discharge
    </label>
    </div>
    </fieldset>
  </div>

  <!-- Housing points -->
  <fieldset class="mb-6 border p-4 rounded">
  
      <label class="flex items-center gap-2">
          FDA
        </label>
        <select v-model="form.fda" class="input">
            <option value="">Select outcome</option>
            <option>Yes</option>
            <option>No</option>
            <option>Currently Appealing</option>
            <option>Don't Know</option>
        </select>
  <div>
    <label class="label">Housing Points</label>
    <input v-model.number="form.housing_points" type="number" class="input"
      placeholder="e.g. 50" />
  </div>
  </fieldset>

  <!-- Medical -->
  <div>
    <label class="label">Medical Conditions</label>
    <textarea v-model="form.medical_conditions" class="input"
      placeholder="List any known conditions" />
  </div>

  <!-- Contacts -->
  <div>
    <label class="label">First Contact</label>
    <input v-model="form.first_contact" class="input"
      placeholder="Name / phone / relationship" />
  </div>

  <div>
    <label class="label">Second Contact</label>
    <input v-model="form.second_contact" class="input"
      placeholder="Optional contact person" />
  </div>

  <div>
    <label class="label">Third Contact</label>
    <input v-model="form.third_contact" class="input"
      placeholder="Optional contact person" />
  </div>

  <!-- Outcome -->
  <div>
    <label class="label">Outcome</label>
    <select v-model="form.outcome" class="input">
      <option value="">Select outcome</option>
      <option>SWEP</option>
      <option>Hostel Bed Provided</option>
      <option>No Availability</option>
      <option>No Duty of Care</option>
      <option>Currently Appealing</option>
      <option>Left before Outcome</option>
      <option>Declined Offered Accommodation</option>
      <option>No response before closure</option>
      <option>other</option>
    </select>
  </div>

  <!-- Sleeping bag -->
  <div>
    <label class="flex items-center gap-2">
      <input type="checkbox" v-model="form.sleeping_bag" />
      Sleeping Bag Provided
    </label>
  </div>

    <!-- Notes -->
  <div>
    <label class="label">Notes</label>
    <textarea v-model="form.notes" class="input"
      placeholder="Any additional case notes" />
  </div>

  <button class="btn btn-primary mt-4" @click="submit">
    Save Referral
  </button>

</div>

  </AppLayout>
</template>

<script lang="ts" setup>
import { ref, reactive, onMounted, computed } from 'vue';
import axios from 'axios';
import Multiselect from 'vue-multiselect';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';


import { usePage } from '@inertiajs/vue3';

const page = usePage();

const referralId = page.props.id ?? null;
const isEditMode = computed(() => !!referralId);

const selectedUser = ref<any>(null);
const isLockedUser = computed(() => isEditMode.value);


const query = new URLSearchParams(window.location.search);
const userIdFromQuery = query.get('user_id');

const allUsers = ref([]);

const form = reactive({
  service_user_id: null,

  gender: '',
  date_of_birth: '',
  contact_number: '',

  national_insurance_number: '',
  nationality: '',
  previous_address: '',

  prison: false,
  hospital: false,
  fda: false,

  housing_points: 0,

  medical_conditions: '',

  first_contact: '',
  second_contact: '',
  third_contact: '',

  notes: '',

  outcome: '',

  sleeping_bag: false,
});

const userOptions = ref([]);

onMounted(async () => {
  const usersRes = await axios.get('/api/service-users-full');

  userOptions.value = usersRes.data.map(u => ({
    label: u.name,
    value: u.id,
  }));

  // CREATE MODE prefill from query param
  const userId = userIdFromQuery

  if (userId && !isEditMode.value) {
    const user = usersRes.data.find(u => u.id == userId);
    form.gender = user.gender
    form.date_of_birth = user.dob
    form.contact_number = user.contact_number
    if (user) {
      selectedUser.value = {
        label: user.name,
        value: user.id,
      };
    }
  }

  // EDIT MODE → load referral
  if (isEditMode.value) {
    const res = await axios.get(`/api/housing-referrals/${referralId}`);

    const data = res.data;
    
    Object.assign(form, data);

    selectedUser.value = {
      label: data.service_user.name,
      value: data.service_user_id,
    };
  }
});

async function submit() {
  form.service_user_id = selectedUser.value.value;

  if (isEditMode.value) {
    await axios.post(`/api/housing-referrals/${referralId}`, form);
  } else {
    await axios.post('/api/housing-referrals', form);
  }

    router.visit(`/documents/housing-referral-forms`);

}
</script>

<style scoped>
.input {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  margin-bottom: 6px;
  border-radius: 4px;
}
.btn {
  padding: 10px;
  border-radius: 6px;
}
.btn-primary {
  background: #3b82f6;
  color: white;
}
</style>