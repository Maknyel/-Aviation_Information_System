<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto">
      <!-- Welcome Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Notification</h1>
        <p class="text-gray-600">View all user requests</p>
      </div>

      <!-- Requests List -->
      <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-xl font-semibold text-gray-800">All Requests</h2>
        </div>

        <div class="p-6 space-y-4">
          <div v-if="loading" class="text-center py-8 text-gray-500">
            Loading...
          </div>
          <div v-else-if="allRequests.length === 0" class="text-center py-8 text-gray-500">
            No requests found
          </div>
          <div v-else v-for="request in allRequests" :key="`${request.type}-${request.id}`" class="p-4 bg-gray-50 rounded-xl border border-gray-200">
            <div class="flex items-start justify-between mb-3">
              <div class="flex items-center gap-3">
                <div
                  class="w-3 h-3 rounded-full mt-1"
                  :class="{
                    'bg-yellow-500': request.status === 'pending',
                    'bg-green-500': request.status === 'approved',
                    'bg-red-500': request.status === 'rejected',
                    'bg-gray-500': request.status === 'canceled',
                    'bg-blue-500': request.status === 'in_progress',
                  }"
                ></div>
                <div>
                  <div class="flex items-center gap-2 mb-1">
                    <span
                      class="px-2 py-0.5 text-xs rounded font-medium"
                      :class="request.type === 'facility' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700'"
                    >
                      {{ request.type === 'facility' ? 'Facility' : 'Work Order' }}
                    </span>
                  </div>
                  <h3 class="font-semibold text-gray-800">
                    {{ request.type === 'facility' ? request.venue_requested : request.location }}
                  </h3>
                  <p class="text-sm text-gray-600 mt-1">
                    {{ request.type === 'facility' ? (request.user?.name || request.title_of_event) : request.requisitioner }}
                  </p>
                  <p class="text-xs text-gray-500 mt-1">
                    {{ request.type === 'facility' ? `${request.time_of_event}, ${request.date_of_event}` : `${request.time}, ${request.date}` }}
                  </p>
                </div>
              </div>
              <div class="flex flex-col items-end gap-2">
                <span
                  class="px-3 py-1 text-xs rounded-full font-medium whitespace-nowrap capitalize"
                  :class="{
                    'bg-yellow-100 text-yellow-700': request.status === 'pending',
                    'bg-green-100 text-green-700': request.status === 'approved',
                    'bg-red-100 text-red-700': request.status === 'rejected',
                    'bg-gray-100 text-gray-700': request.status === 'canceled',
                    'bg-blue-100 text-blue-700': request.status === 'in_progress',
                  }"
                >
                  {{ request.status }}
                </span>
                <button
                  @click="viewRequestDetails(request.type, request.id)"
                  :disabled="loadingDetails"
                  class="px-6 py-2 bg-aviation-olive text-white text-sm rounded-lg hover:bg-opacity-90 transition-all disabled:opacity-50"
                >
                  Details
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Facility Request Details Modal -->
    <FacilityRequestDetailsModal v-model="showDetailsModal" :request="selectedRequest" @statusUpdated="handleStatusUpdated" />

    <!-- Work Order Details Modal -->
    <WorkOrderDetailsModal v-model="showWorkOrderDetailsModal" :order="selectedWorkOrder" @statusUpdated="handleStatusUpdated" />
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import AppLayout from '@/components/AppLayout.vue';
import FacilityRequestDetailsModal from '@/components/FacilityRequestDetailsModal.vue';
import WorkOrderDetailsModal from '@/components/WorkOrderDetailsModal.vue';
import { API_URL } from '@/config/api';

const user = ref<any>(null);
const showDetailsModal = ref(false);
const selectedRequest = ref<any>(null);
const loadingDetails = ref(false);
const showWorkOrderDetailsModal = ref(false);
const selectedWorkOrder = ref<any>(null);
const facilityRequests = ref<any[]>([]);
const workOrders = ref<any[]>([]);
const loading = ref(false);

const allRequests = computed(() => {
  const facility = facilityRequests.value.map(req => ({ ...req, type: 'facility' }));
  const workOrder = workOrders.value.map(req => ({ ...req, type: 'workorder' }));

  // Combine and sort by date (newest first)
  return [...facility, ...workOrder].sort((a, b) => {
    const dateA = new Date(a.type === 'facility' ? a.date_of_event : a.date);
    const dateB = new Date(b.type === 'facility' ? b.date_of_event : b.date);
    return dateB.getTime() - dateA.getTime();
  });
});

const fetchAllRequests = async () => {
  loading.value = true;
  try {
    const token = localStorage.getItem('token');

    // Fetch facility requests
    const facilityResponse = await fetch(`${API_URL}/facility-requests`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    const facilityData = await facilityResponse.json();
    if (facilityResponse.ok) {
      facilityRequests.value = facilityData.data || [];
    }

    // Fetch work orders
    const workOrderResponse = await fetch(`${API_URL}/work-orders`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    const workOrderData = await workOrderResponse.json();
    if (workOrderResponse.ok) {
      workOrders.value = workOrderData.data || [];
    }
  } catch (error) {
    console.error('Error fetching requests:', error);
  } finally {
    loading.value = false;
  }
};

const viewRequestDetails = async (type: string, id: number) => {
  if (type === 'facility') {
    loadingDetails.value = true;
    try {
      const token = localStorage.getItem('token');
      const response = await fetch(`${API_URL}/facility-requests/${id}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Failed to fetch request details');
      }

      selectedRequest.value = data.data;
      showDetailsModal.value = true;
    } catch (error) {
      console.error('Error fetching request details:', error);
    } finally {
      loadingDetails.value = false;
    }
  } else if (type === 'workorder') {
    loadingDetails.value = true;
    try {
      const token = localStorage.getItem('token');
      const response = await fetch(`${API_URL}/work-orders/${id}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Failed to fetch work order details');
      }

      selectedWorkOrder.value = data.data;
      showWorkOrderDetailsModal.value = true;
    } catch (error) {
      console.error('Error fetching work order details:', error);
    } finally {
      loadingDetails.value = false;
    }
  }
};

const handleStatusUpdated = () => {
  console.log('Status updated - refreshing notifications');
  fetchAllRequests();
};

onMounted(() => {
  const userStr = localStorage.getItem('user');
  if (userStr) {
    user.value = JSON.parse(userStr);
  }
  fetchAllRequests();
});
</script>
