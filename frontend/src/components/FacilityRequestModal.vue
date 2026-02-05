<template>
  <Modal v-model="isOpen" title="Facility Request">
    <form @submit.prevent="submitForm" class="space-y-4">
      <!-- Venue Requested and Location/Room Number -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Venue Requested</label>
          <select
            v-model="formData.venue_requested"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent"
          >
            <option value="">Select Venue</option>
            <option value="Multi Purpose Hall">Multi Purpose Hall</option>
            <option value="Covered Court">Covered Court</option>
            <option value="Building A">Building A</option>
            <option value="Building B">Building B</option>
            <option value="Hangar">Hangar</option>
            <option value="Others">Others</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Location/Room Number</label>
          <input
            v-model="formData.location_room_number"
            type="text"
            placeholder="Please specify the location/ room number for Bldg A, Bldg B, Hangar"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent text-sm"
          />
        </div>
      </div>

      <!-- Title of Event -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Title of Event</label>
        <input
          v-model="formData.title_of_event"
          type="text"
          required
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent"
        />
      </div>

      <!-- Time and Date of Event -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Time of Event</label>
          <input
            v-model="formData.time_of_event"
            type="time"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Date of Event</label>
          <input
            v-model="formData.date_of_event"
            type="date"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent"
          />
        </div>
      </div>

      <!-- Details of Request -->
      <div>
        <h3 class="text-sm font-semibold text-gray-700 mb-2">Details of Request</h3>
        <div class="grid grid-cols-2 gap-3">
          <!-- Row 1 -->
          <label class="flex items-center gap-2 cursor-pointer">
            <input v-model="formData.chair" type="checkbox" class="w-4 h-4 text-aviation-olive border-gray-300 rounded focus:ring-aviation-olive">
            <span class="text-sm text-gray-700">Chair</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input v-model="formData.podium" type="checkbox" class="w-4 h-4 text-aviation-olive border-gray-300 rounded focus:ring-aviation-olive">
            <span class="text-sm text-gray-700">Podium</span>
          </label>

          <!-- Row 2 -->
          <label class="flex items-center gap-2 cursor-pointer">
            <input v-model="formData.tent" type="checkbox" class="w-4 h-4 text-aviation-olive border-gray-300 rounded focus:ring-aviation-olive">
            <span class="text-sm text-gray-700">Tent</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input v-model="formData.tables" type="checkbox" class="w-4 h-4 text-aviation-olive border-gray-300 rounded focus:ring-aviation-olive">
            <span class="text-sm text-gray-700">Tables</span>
          </label>

          <!-- Row 3 -->
          <label class="flex items-center gap-2 cursor-pointer">
            <input v-model="formData.booths" type="checkbox" class="w-4 h-4 text-aviation-olive border-gray-300 rounded focus:ring-aviation-olive">
            <span class="text-sm text-gray-700">Booths</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input v-model="formData.sound_system" type="checkbox" class="w-4 h-4 text-aviation-olive border-gray-300 rounded focus:ring-aviation-olive">
            <span class="text-sm text-gray-700">Sound System</span>
          </label>

          <!-- Row 4 -->
          <label class="flex items-center gap-2 cursor-pointer">
            <input v-model="formData.extension" type="checkbox" class="w-4 h-4 text-aviation-olive border-gray-300 rounded focus:ring-aviation-olive">
            <span class="text-sm text-gray-700">Extension</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input v-model="formData.microphones" type="checkbox" class="w-4 h-4 text-aviation-olive border-gray-300 rounded focus:ring-aviation-olive">
            <span class="text-sm text-gray-700">Microphones</span>
          </label>

          <!-- Row 5 -->
          <label class="flex items-center gap-2 cursor-pointer">
            <input v-model="formData.skirting" type="checkbox" class="w-4 h-4 text-aviation-olive border-gray-300 rounded focus:ring-aviation-olive">
            <span class="text-sm text-gray-700">Skirting</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input v-model="formData.flags" type="checkbox" class="w-4 h-4 text-aviation-olive border-gray-300 rounded focus:ring-aviation-olive">
            <span class="text-sm text-gray-700">Flags</span>
          </label>

          <!-- Row 6 -->
          <label class="flex items-center gap-2 cursor-pointer col-span-2">
            <input v-model="formData.others" type="checkbox" class="w-4 h-4 text-aviation-olive border-gray-300 rounded focus:ring-aviation-olive">
            <span class="text-sm text-gray-700">Others</span>
          </label>
        </div>

        <!-- Others Description -->
        <div v-if="formData.others" class="mt-3">
          <textarea
            v-model="formData.others_description"
            placeholder="Please specify other requirements..."
            rows="2"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent text-sm"
          ></textarea>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="p-3 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-sm text-red-600">{{ error }}</p>
      </div>

      <!-- Success Message -->
      <div v-if="success" class="p-3 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-sm text-green-600">{{ success }}</p>
      </div>
    </form>

    <template #footer>
      <div class="flex justify-end gap-3">
        <button
          type="button"
          @click="closeModal"
          class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
        >
          Cancel
        </button>
        <button
          type="button"
          @click="submitForm"
          :disabled="loading"
          class="px-6 py-2 bg-aviation-olive text-white rounded-lg hover:bg-opacity-90 transition-colors disabled:opacity-50"
        >
          {{ loading ? 'Submitting...' : 'Submit Request' }}
        </button>
      </div>
    </template>
  </Modal>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import Modal from './Modal.vue';
import { API_URL } from '@/config/api';

interface Props {
  modelValue: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits(['update:modelValue', 'success']);

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const formData = ref({
  venue_requested: '',
  location_room_number: '',
  title_of_event: '',
  time_of_event: '',
  date_of_event: '',
  chair: false,
  podium: false,
  tent: false,
  tables: false,
  booths: false,
  sound_system: false,
  extension: false,
  microphones: false,
  skirting: false,
  flags: false,
  others: false,
  others_description: ''
});

const loading = ref(false);
const error = ref('');
const success = ref('');

const resetForm = () => {
  formData.value = {
    venue_requested: '',
    location_room_number: '',
    title_of_event: '',
    time_of_event: '',
    date_of_event: '',
    chair: false,
    podium: false,
    tent: false,
    tables: false,
    booths: false,
    sound_system: false,
    extension: false,
    microphones: false,
    skirting: false,
    flags: false,
    others: false,
    others_description: ''
  };
  error.value = '';
  success.value = '';
};

const closeModal = () => {
  resetForm();
  isOpen.value = false;
};

const submitForm = async () => {
  loading.value = true;
  error.value = '';
  success.value = '';

  try {
    const token = localStorage.getItem('token');
    const response = await fetch(`${API_URL}/facility-requests`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: JSON.stringify(formData.value)
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to submit facility request');
    }

    success.value = 'Facility request submitted successfully!';
    emit('success', data.data);

    // Close modal after a short delay
    setTimeout(() => {
      closeModal();
    }, 1500);
  } catch (err: any) {
    error.value = err.message || 'An error occurred while submitting the request';
  } finally {
    loading.value = false;
  }
};
</script>
