<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto">
      <!-- Welcome Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Request</h1>
        <p class="text-gray-600">View and manage your requests</p>
      </div>

      <!-- Main Grid Layout -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Request Section -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg border border-gray-100">
          <!-- Header with Dropdown -->
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between mb-4">
              <div class="relative">
                <select
                  v-model="requestType"
                  @change="fetchRequests()"
                  class="text-xl font-semibold text-gray-800 bg-transparent border-none outline-none cursor-pointer appearance-none pr-8"
                >
                  <option value="facility">Facility Requests</option>
                  <option value="workorder">Work Order Requests</option>
                </select>
                <svg class="w-4 h-4 text-gray-500 absolute right-0 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </div>
              <button
                @click="requestType === 'facility' ? showFacilityModal = true : showWorkOrderModal = true"
                class="px-4 py-2 bg-aviation-olive text-white text-sm rounded-lg hover:bg-opacity-90 transition-all flex items-center gap-2"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Request
              </button>
            </div>

            <!-- Filter Tabs -->
            <div class="flex gap-2 mt-4 flex-wrap">
              <button
                v-for="filter in filters"
                :key="filter.value"
                @click="activeFilter = filter.value"
                :class="[
                  'px-4 py-2 rounded-lg text-sm font-medium transition-all',
                  activeFilter === filter.value
                    ? 'bg-aviation-olive text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                ]"
              >
                {{ filter.label }}
              </button>
            </div>
          </div>

          <!-- Requests List -->
          <div class="p-6 space-y-4">
            <div v-if="loading" class="text-center py-8 text-gray-500">
              Loading...
            </div>
            <div v-else-if="filteredRequests.length === 0" class="text-center py-8 text-gray-500">
              No {{ requestType === 'facility' ? 'facility requests' : 'work orders' }} found
            </div>
            <div v-else v-for="request in filteredRequests" :key="request.id" class="p-4 bg-gray-50 rounded-xl border border-gray-200">
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
                    <h3 class="font-semibold text-gray-800">
                      {{ requestType === 'facility' ? request.venue_requested : request.location }}
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">
                      {{ requestType === 'facility' ? (request.user?.name || request.title_of_event) : request.requisitioner }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                      {{ requestType === 'facility' ? `${formatTime(request.time_of_event)}, ${formatDate(request.date_of_event)}` : `${formatTime(request.time)}, ${formatDate(request.date)}` }}
                    </p>
                  </div>
                </div>
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
              </div>
              <div class="flex justify-end">
                <button
                  @click="viewRequestDetails(requestType, request.id)"
                  :disabled="loadingDetails"
                  class="px-6 py-2 bg-aviation-olive text-white text-sm rounded-lg hover:bg-opacity-90 transition-all disabled:opacity-50"
                >
                  Details
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Calendar Widget -->
        <div class="lg:col-span-1 bg-white rounded-xl shadow-lg p-6 border border-gray-100">
          <SimpleCalendar @dateUpdated="handleDateUpdated" />
        </div>
      </div>
    </div>

    <!-- Facility Request Modal -->
    <FacilityRequestModal v-model="showFacilityModal" @success="handleRequestSuccess" />

    <!-- Work Order Modal -->
    <WorkOrderModal v-model="showWorkOrderModal" @success="handleRequestSuccess" />

    <!-- Facility Request Details Modal -->
    <FacilityRequestDetailsModal v-model="showDetailsModal" :request="selectedRequest" @statusUpdated="handleStatusUpdated" />

    <!-- Work Order Details Modal -->
    <WorkOrderDetailsModal v-model="showWorkOrderDetailsModal" :order="selectedWorkOrder" @statusUpdated="handleStatusUpdated" />
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import AppLayout from '@/components/AppLayout.vue';
import SimpleCalendar from '@/components/SimpleCalendar.vue';
import FacilityRequestModal from '@/components/FacilityRequestModal.vue';
import WorkOrderModal from '@/components/WorkOrderModal.vue';
import FacilityRequestDetailsModal from '@/components/FacilityRequestDetailsModal.vue';
import WorkOrderDetailsModal from '@/components/WorkOrderDetailsModal.vue';
import { API_URL } from '@/config/api';

const user = ref<any>(null);
const activeFilter = ref('all');
const requestType = ref<'facility' | 'workorder'>('facility');
const showFacilityModal = ref(false);
const showWorkOrderModal = ref(false);
const showDetailsModal = ref(false);
const selectedRequest = ref<any>(null);
const loadingDetails = ref(false);
const showWorkOrderDetailsModal = ref(false);
const selectedWorkOrder = ref<any>(null);
const requests = ref<any[]>([]);
const loading = ref(false);

const filters = [
  { label: 'All', value: 'all' },
  { label: 'Pending', value: 'pending' },
  { label: 'Approved', value: 'approved' },
  { label: 'Disapproved', value: 'rejected' },
  { label: 'Canceled', value: 'canceled' },
];

const formatTime = (time: string) => {
  if (!time) return '';
  // Handle HH:mm or HH:mm:ss format
  const [hours, minutes] = time.split(':').map(Number);
  if (isNaN(hours)) return time;
  const period = hours >= 12 ? 'PM' : 'AM';
  const h = hours % 12 || 12;
  return `${h}:${String(minutes).padStart(2, '0')} ${period}`;
};

const formatDate = (date: string) => {
  if (!date) return '';
  const d = new Date(date);
  if (isNaN(d.getTime())) return date;
  return d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
};

const filteredRequests = computed(() => {
  if (activeFilter.value === 'all') {
    return requests.value;
  }
  return requests.value.filter(req => req.status === activeFilter.value);
});

const fetchRequests = async () => {
  loading.value = true;
  try {
    const token = localStorage.getItem('token');
    const endpoint = requestType.value === 'facility' ? 'facility-requests' : 'work-orders';
    const statusParam = activeFilter.value !== 'all' ? `?status=${activeFilter.value}` : '';

    const response = await fetch(`${API_URL}/${endpoint}${statusParam}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to fetch requests');
    }

    requests.value = data.data || [];
  } catch (error) {
    console.error('Error fetching requests:', error);
    requests.value = [];
  } finally {
    loading.value = false;
  }
};

const handleRequestSuccess = (request: any) => {
  console.log('Request submitted:', request);
  fetchRequests(); // Refresh the list
};

const handleDateUpdated = () => {
  console.log('Event date updated - refreshing dashboard');
  // Refresh dashboard data when date is updated
  window.location.reload();
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
  console.log('Status updated - refreshing requests');
  fetchRequests();
};

watch(activeFilter, () => {
  fetchRequests();
});

onMounted(() => {
  const userStr = localStorage.getItem('user');
  if (userStr) {
    user.value = JSON.parse(userStr);
  }
  fetchRequests();
});
</script>
