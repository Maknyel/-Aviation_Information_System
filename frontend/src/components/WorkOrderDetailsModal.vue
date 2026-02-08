<template>
  <Modal v-model="isOpen" title="Details">
    <div class="space-y-3">
      <!-- Requestor -->
      <div class="flex gap-2">
        <label class="text-sm font-semibold text-gray-900 min-w-[140px]">Requestor:</label>
        <p class="text-sm text-aviation-olive font-medium">{{ order?.user?.name || order?.requisitioner || 'N/A' }}</p>
      </div>

      <!-- Location -->
      <div class="flex gap-2">
        <label class="text-sm font-semibold text-gray-900 min-w-[140px]">Location:</label>
        <p class="text-sm text-aviation-olive font-medium">{{ order?.location || 'N/A' }}</p>
      </div>

      <!-- Room No. or Area -->
      <div class="flex gap-2">
        <label class="text-sm font-semibold text-gray-900 min-w-[140px]">Room No. or Area:</label>
        <p class="text-sm text-aviation-olive font-medium">{{ order?.room_number || 'N/A' }}</p>
      </div>

      <!-- Description -->
      <div class="flex gap-2">
        <label class="text-sm font-semibold text-gray-900 min-w-[140px]">Description:</label>
        <p class="text-sm text-aviation-olive font-medium">{{ order?.description_of_problem || 'N/A' }}</p>
      </div>

      <!-- Image -->
      <div v-if="order?.image" class="flex gap-2">
        <label class="text-sm font-semibold text-gray-900 min-w-[140px]">Image:</label>
        <div>
          <img :src="getImageUrl(order.image)" alt="Work Order Image" class="max-w-xs rounded-lg border border-gray-300 shadow-sm">
        </div>
      </div>
    </div>

    <!-- Assigned To -->
    <div v-if="order?.assignee" class="mt-3 flex gap-2">
      <label class="text-sm font-semibold text-gray-900 min-w-[140px]">Assigned To:</label>
      <p class="text-sm text-aviation-olive font-medium">{{ order.assignee.name }}</p>
    </div>

    <!-- Admin Remarks -->
    <div v-if="order?.admin_remarks" class="mt-3 flex gap-2">
      <label class="text-sm font-semibold text-gray-900 min-w-[140px]">Remarks:</label>
      <p class="text-sm text-aviation-olive font-medium">{{ order.admin_remarks }}</p>
    </div>

    <!-- Approval Timeline -->
    <div v-if="order?.approval_steps?.length" class="mt-4 border-t border-gray-200 pt-4">
      <h4 class="text-sm font-semibold text-aviation-olive font-medium mb-2">Approval Timeline</h4>
      <div class="space-y-2">
        <div v-for="step in order.approval_steps" :key="step.id" class="flex items-center gap-3 text-sm">
          <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs"
            :class="{ 'bg-green-500': step.status === 'approved', 'bg-red-500': step.status === 'rejected', 'bg-gray-300': step.status === 'pending' }">
            {{ step.step_order }}
          </div>
          <div>
            <span class="font-medium">{{ step.approver_role }}</span>
            <span v-if="step.approver" class="text-gray-500"> - {{ step.approver.name }}</span>
            <span class="ml-2 px-2 py-0.5 text-xs rounded-full" :class="{ 'bg-green-100 text-green-700': step.status === 'approved', 'bg-red-100 text-red-700': step.status === 'rejected', 'bg-gray-100 text-gray-600': step.status === 'pending' }">
              {{ step.status }}
            </span>
            <span v-if="step.acted_at" class="text-xs text-gray-400 ml-2">{{ new Date(step.acted_at).toLocaleString() }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Feedback -->
    <FeedbackForm v-if="order?.id" :request-type="'work_order'" :request-id="order.id" :request-status="order?.status" />

    <template #footer>
      <div class="flex justify-center gap-8">
        <button
          v-if="canApprove && order?.status === 'pending'"
          type="button"
          @click="handleStatusUpdate('approved')"
          :disabled="updating"
          class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50"
        >
          {{ updating ? 'Updating...' : 'Approved' }}
        </button>
        <button
          v-if="canApprove && order?.status === 'pending'"
          type="button"
          @click="handleStatusUpdate('rejected')"
          :disabled="updating"
          class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50"
        >
          {{ updating ? 'Updating...' : 'Disapproved' }}
        </button>
        <button
          v-if="!canApprove || order?.status !== 'pending'"
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
import FeedbackForm from './FeedbackForm.vue';
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
