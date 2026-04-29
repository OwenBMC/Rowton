<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { ref, onMounted } from 'vue'
import axios from 'axios'

defineOptions({
  layout: AppLayout,
})

const props = defineProps<{
  registration: any
}>()

const registration = ref<any>(props.registration || null)

onMounted(async () => {
  try {
    const res = await axios.get(`/api/registration/${props.registration.id}`)
    registration.value = res.data.registration
    console.log(registration)
  } catch (err) {
    console.error(err)
  }
})
</script>

<template>
<div v-if="registration">
  <!-- SERVICE USER -->
  <h2 class="text-xl font-bold mb-2">Service User</h2>

  <p>
    {{ registration.service_user?.first_name }}
    {{ registration.service_user?.middle_names }}
    {{ registration.service_user?.surname }}
  </p>

  <p>DOB: {{ registration.service_user?.dob }}</p>
  <p>Address: {{ registration.service_user?.address }}</p>
  <p>Postcode: {{ registration.service_user?.postcode }}</p>
  <p>Contact: {{ registration.service_user?.contact_number }}</p>
  <p v-if="registration.service_user?.food_allergies">Has Food Allergies</p>

  <!-- NEXT OF KIN -->
  <div class="mt-4" v-if="registration.next_of_kin">
    <h3 class="font-semibold">Next of Kin</h3>

    <p>{{ registration.next_of_kin.name }}</p>
    <p>{{ registration.next_of_kin.relationship }}</p>
    <p>{{ registration.next_of_kin.address }}</p>
    <p>{{ registration.next_of_kin.contact_number }}</p>

  </div>
  <div class="mt-4" v-if="registration.service_user?.next_of_kin">
    <h3 class="font-semibold">Next Of Kin</h3>

      <p class="text-sm text-gray-600">name: {{ registration.service_user?.next_of_kin.name }}</p>

      <p class="text-sm text-gray-600">
        address: {{ registration.service_user?.next_of_kin.address }}
      </p>

      <p class="text-sm text-gray-600">
        Phone: {{ registration.service_user?.next_of_kin.contact_number }}
      </p>
  </div>
  <!-- DOCTORS -->
  <div class="mt-4" v-if="registration.service_user?.doctors?.length">
    <h3 class="font-semibold">Doctors</h3>

    <div
      v-for="doctor in registration.service_user.doctors"
      :key="doctor.id"
      class="mb-2 border p-2 rounded"
    >
      <p class="font-medium">{{ doctor.name }}</p>

      <p class="text-sm text-gray-600">
        Practice: {{ doctor.practice?.name }}
      </p>

      <p class="text-sm text-gray-600">
        {{ doctor.practice?.address_line_1 }},
        {{ doctor.practice?.city }},
        {{ doctor.practice?.postcode }}
      </p>

      <p class="text-sm text-gray-600">
        Phone: {{ doctor.practice?.phone_number }}
      </p>
    </div>
  </div>

  <!-- REGISTRATION -->
  <div class="mt-4">
    <h3 class="font-semibold">Registration Info</h3>

    <p>Referral Date: {{ registration.referral_date }}</p>
    <p>Service User Signature: {{ registration.service_user_signature_date }}</p>
    <p>Volunteer Signature: {{ registration.volunteer_signature_date }}</p>
  </div>
</div>

<!-- LOADING STATE -->
<div v-else>
  Loading registration...
</div>
</template>