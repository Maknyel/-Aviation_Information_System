<template>
  <Modal v-model="isOpen" title="Details">
    <div class="space-y-3">
      <!-- Requestor -->
      <div class="flex gap-2">
        <label class="text-sm font-medium text-gray-900 min-w-[140px]">Requestor:</label>
        <p class="text-sm text-gray-800">{{ order?.user?.name || order?.requisitioner || 'N/A' }}</p>
      </div>

      <!-- Location -->
      <div class="flex gap-2">
        <label class="text-sm font-medium text-gray-900 min-w-[140px]">Location:</label>
        <p class="text-sm text-gray-800">{{ order?.location || 'N/A' }}</p>
      </div>

      <!-- Room No. or Area -->
      <div class="flex gap-2">
        <label class="text-sm font-medium text-gray-900 min-w-[140px]">Room No. or Area:</label>
        <p class="text-sm text-gray-800">{{ order?.room_number || 'N/A' }}</p>
      </div>

      <!-- Description -->
      <div class="flex gap-2">
        <label class="text-sm font-medium text-gray-900 min-w-[140px]">Description:</label>
        <p class="text-sm text-gray-800">{{ order?.description_of_problem || 'N/A' }}</p>
      </div>

      <!-- Image -->
      <div v-if="order?.image" class="flex gap-2">
        <label class="text-sm font-medium text-gray-900 min-w-[140px]">Image:</label>
        <div>
          <img :src="getImageUrl(order.image)" alt="Work Order Image" class="max-w-xs rounded-lg border border-gray-300 shadow-sm">
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end gap-3">
        <button
          v-if="canApprove"
          type="button"
          @click="handleStatusUpdate('approved')"
          :disabled="updating"
          class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50"
        >
          {{ updating ? 'Updating...' : 'Approved' }}
        </button>
        <button
          v-if="canApprove"
          type="button"
          @click="handleStatusUpdate('rejected')"
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
  order: any;
  showActionButtons?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  showActionButtons: false
});

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

const getImageUrl = (imagePath: string) => {
  // If it's already a full URL, return it
  if (imagePath.startsWith('http')) {
    return imagePath;
  }
  // Otherwise, construct the URL from the backend storage
  return `${API_URL.replace('/api', '')}/storage/${imagePath}`;
};

const handleStatusUpdate = async (status: 'approved' | 'rejected') => {
  if (!props.order) return;

  updating.value = true;
  try {
    const token = localStorage.getItem('token');
    const response = await fetch(`${API_URL}/work-orders/${props.order.id}/status`, {
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
  } finally {
    updating.value = false;
  }
};
</script>
