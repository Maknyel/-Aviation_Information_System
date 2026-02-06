<template>
  <Modal v-model="isOpen" title="Details">
    <div class="space-y-3">
      <!-- Requestor -->
      <div class="flex gap-2">
        <label class="text-sm font-medium text-gray-900 min-w-[140px]">Requestor:</label>
        <p class="text-sm text-gray-800">{{ request?.user?.name || 'N/A' }}</p>
      </div>

      <!-- Venue Requested -->
      <div class="flex gap-2">
        <label class="text-sm font-medium text-gray-900 min-w-[140px]">Venue Requested:</label>
        <p class="text-sm text-gray-800">{{ request?.venue_requested || 'N/A' }}</p>
      </div>

      <!-- Title of Event -->
      <div class="flex gap-2">
        <label class="text-sm font-medium text-gray-900 min-w-[140px]">Title of Event:</label>
        <p class="text-sm text-gray-800">{{ request?.title_of_event || 'N/A' }}</p>
      </div>

      <!-- Date and Time of Event -->
      <div class="flex gap-2">
        <label class="text-sm font-medium text-gray-900 min-w-[140px]">Date and Time of Event:</label>
        <p class="text-sm text-gray-800">{{ formatDateTime(request?.date_of_event, request?.time_of_event) }}</p>
      </div>

      <!-- Equipment Needed and Quantity -->
      <div class="mt-4">
        <h3 class="text-sm font-medium text-gray-900 mb-3">Equipment Needed and Quantity:</h3>
        <div class="space-y-2 ml-8">
          <!-- Row 1 -->
          <div class="grid grid-cols-2 gap-x-16">
            <div>
              <span class="text-sm text-gray-800">Chairs:</span>
              <span class="text-sm text-gray-800 ml-28">{{ formatEquipment(request?.chair) }}</span>
            </div>
            <div>
              <span class="text-sm text-gray-800">Podium:</span>
              <span class="text-sm text-gray-800 ml-20">{{ formatEquipment(request?.podium) }}</span>
            </div>
          </div>

          <!-- Row 2 -->
          <div class="grid grid-cols-2 gap-x-16">
            <div>
              <span class="text-sm text-gray-800">Tent:</span>
              <span class="text-sm text-gray-800 ml-32">{{ formatEquipment(request?.tent) }}</span>
            </div>
            <div>
              <span class="text-sm text-gray-800">Tables:</span>
              <span class="text-sm text-gray-800 ml-24">{{ formatEquipment(request?.tables) }}</span>
            </div>
          </div>

          <!-- Row 3 -->
          <div class="grid grid-cols-3 gap-x-12">
            <div>
              <span class="text-sm text-gray-800">Booths:</span>
              <span class="text-sm text-gray-800 ml-16">{{ formatEquipment(request?.booths) }}</span>
            </div>
            <div>
              <span class="text-sm text-gray-800">Sound System:</span>
              <span class="text-sm text-gray-800 ml-6">{{ formatEquipment(request?.sound_system) }}</span>
            </div>
            <div>
              <span class="text-sm text-gray-800">Others:</span>
              <span class="text-sm text-gray-800 ml-12">{{ formatEquipment(request?.others, request?.others_description) }}</span>
            </div>
          </div>

          <!-- Row 4 -->
          <div class="grid grid-cols-2 gap-x-16">
            <div>
              <span class="text-sm text-gray-800">Extension:</span>
              <span class="text-sm text-gray-800 ml-20">{{ formatEquipment(request?.extension) }}</span>
            </div>
            <div>
              <span class="text-sm text-gray-800">Microphones:</span>
              <span class="text-sm text-gray-800 ml-12">{{ formatEquipment(request?.microphones) }}</span>
            </div>
          </div>

          <!-- Row 5 -->
          <div class="grid grid-cols-2 gap-x-16">
            <div>
              <span class="text-sm text-gray-800">Skirting:</span>
              <span class="text-sm text-gray-800 ml-24">{{ formatEquipment(request?.skirting) }}</span>
            </div>
            <div>
              <span class="text-sm text-gray-800">Flags:</span>
              <span class="text-sm text-gray-800 ml-28">{{ formatEquipment(request?.flags) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end gap-3">
        <button
          v-if="canApprove"
          type="button"
          @click="updateStatus('approved')"
          :disabled="updating"
          class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50"
        >
          {{ updating ? 'Updating...' : 'Approved' }}
        </button>
        <button
          v-if="canApprove"
          type="button"
          @click="updateStatus('rejected')"
          :disabled="updating"
          class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50"
        >
          {{ updating ? 'Updating...' : 'Disapproved' }}
        </button>
        <button
          v-if="!canApprove"
          type="button"
          @click="close"
          class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
        >
          Close
        </button>
      </div>
    </template>
  </Modal>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import Modal from './Modal.vue';
import { API_URL } from '@/config/api';

interface Props {
  modelValue: boolean;
  request: any;
}

const props = defineProps<Props>();
const emit = defineEmits(['update:modelValue', 'statusUpdated']);

const updating = ref(false);

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const canApprove = computed(() => {
  const userStr = localStorage.getItem('user');
  if (!userStr) return false;
  const user = JSON.parse(userStr);
  return user?.role?.name === 'Staff' || user?.role?.name === 'Admin';
});

const close = () => {
  isOpen.value = false;
};

const updateStatus = async (status: 'approved' | 'rejected') => {
  if (!props.request) return;

  updating.value = true;
  try {
    const token = localStorage.getItem('token');
    const response = await fetch(`${API_URL}/facility-requests/${props.request.id}/status`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ status })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to update status');
    }

    emit('statusUpdated', data.data);
    close();
  } catch (error) {
    console.error('Error updating status:', error);
    alert('Failed to update status. Please try again.');
  } finally {
    updating.value = false;
  }
};

const formatDateTime = (date: string, time: string) => {
  if (!date) return 'N/A';
  const dateObj = new Date(date);
  const formattedDate = dateObj.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
  return time ? `${formattedDate} at ${time}` : formattedDate;
};

const formatEquipment = (value: any, description?: string) => {
  // If value is falsy (false, 0, null, undefined, empty string), return 'None'
  if (!value) return 'None';

  // If value is true (boolean), return 'Yes' or the description if provided
  if (value === true) return description || 'Yes';

  // If value is a number or string (quantity), return it
  if (typeof value === 'number') return `${value}pcs`;
  if (typeof value === 'string' && value !== 'true' && value !== 'false') {
    // If it's a numeric string, add 'pcs'
    if (!isNaN(Number(value))) return `${value}pcs`;
    // Otherwise return the string as is
    return value;
  }

  return 'None';
};

const getStatusClass = (status: string) => {
  const classes: { [key: string]: string } = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    canceled: 'bg-gray-100 text-gray-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>
