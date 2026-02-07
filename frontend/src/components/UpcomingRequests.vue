<template>
  <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Upcoming Requests</h3>
    <div v-if="loading" class="text-center text-gray-500 text-sm py-4">Loading...</div>
    <div v-else-if="upcomingRequests.length === 0" class="text-center text-gray-500 text-sm py-4">No upcoming requests</div>
    <div v-else class="space-y-3">
      <div v-for="request in upcomingRequests.slice(0, limit)" :key="`${request.type}-${request.id}`" class="p-4 bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="flex items-start justify-between mb-3">
          <div class="flex items-start gap-2">
            <div class="w-2 h-2 rounded-full bg-blue-500 mt-1.5"></div>
            <div>
              <h4 class="text-sm font-semibold text-gray-800">{{ request.title }}</h4>
              <p class="text-xs text-gray-600 mt-0.5">{{ request.subtitle }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ formatTime(request.time) }}, {{ formatDate(request.date) }}</p>
            </div>
          </div>
          <span class="text-xs text-gray-600 whitespace-nowrap">Status: <span class="capitalize font-medium">{{ request.status }}</span></span>
        </div>
        <div class="flex gap-2">
          <button
            v-if="user?.role?.name === 'Student'"
            class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition-colors font-medium"
          >
            Send to HR
          </button>
          <button
            @click="viewRequestDetails(request)"
            :disabled="loadingDetails"
            class="flex-1 px-4 py-2 bg-aviation-olive text-white text-sm rounded-lg hover:bg-opacity-90 transition-colors disabled:opacity-50 font-medium"
          >
            Details
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Facility Request Details Modal -->
  <FacilityRequestDetailsModal v-model="showDetailsModal" :request="selectedRequest" @statusUpdated="refresh" />

  <!-- Work Order Details Modal -->
  <WorkOrderDetailsModal v-model="showWorkOrderDetailsModal" :order="selectedWorkOrder" @statusUpdated="refresh" />
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import FacilityRequestDetailsModal from '@/components/FacilityRequestDetailsModal.vue';
import WorkOrderDetailsModal from '@/components/WorkOrderDetailsModal.vue';
import { useDashboard } from '@/composables/useDashboard';
import { API_URL } from '@/config/api';

const props = withDefaults(defineProps<{
  limit?: number;
}>(), {
  limit: 3,
});

const user = ref<any>(null);

const {
  upcomingRequests,
  loading,
  fetchUpcomingRequests,
} = useDashboard();

const formatDate = (date: string) => {
  if (!date) return '';
  const d = new Date(date);
  return d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', timeZone: 'UTC' });
};

const formatTime = (time: string) => {
  if (!time) return '';
  // Handle both "HH:mm" and "HH:mm:ss" formats
  const parts = time.split(':');
  const h = parseInt(parts[0]);
  const minutes = parts[1];
  const ampm = h >= 12 ? 'PM' : 'AM';
  const hour12 = h % 12 || 12;
  return `${hour12}:${minutes} ${ampm}`;
};

const showDetailsModal = ref(false);
const selectedRequest = ref<any>(null);
const loadingDetails = ref(false);
const showWorkOrderDetailsModal = ref(false);
const selectedWorkOrder = ref<any>(null);

const viewRequestDetails = async (request: any) => {
  if (request.type === 'facility') {
    loadingDetails.value = true;
    try {
      const token = localStorage.getItem('token');
      const response = await fetch(`${API_URL}/facility-requests/${request.id}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      });
      const data = await response.json();
      if (!response.ok) throw new Error(data.message || 'Failed to fetch request details');
      selectedRequest.value = data.data;
      showDetailsModal.value = true;
    } catch (error) {
      console.error('Error fetching request details:', error);
    } finally {
      loadingDetails.value = false;
    }
  } else if (request.type === 'work_order') {
    loadingDetails.value = true;
    try {
      const token = localStorage.getItem('token');
      const response = await fetch(`${API_URL}/work-orders/${request.id}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      });
      const data = await response.json();
      if (!response.ok) throw new Error(data.message || 'Failed to fetch work order details');
      selectedWorkOrder.value = data.data;
      showWorkOrderDetailsModal.value = true;
    } catch (error) {
      console.error('Error fetching work order details:', error);
    } finally {
      loadingDetails.value = false;
    }
  }
};

const refresh = () => {
  fetchUpcomingRequests();
};

defineExpose({ refresh });

onMounted(() => {
  const userStr = localStorage.getItem('user');
  if (userStr) {
    user.value = JSON.parse(userStr);
  }
  fetchUpcomingRequests();
});
</script>
