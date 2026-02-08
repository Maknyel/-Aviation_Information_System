<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto">
      <!-- Welcome Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Dashboard</h1>
        <p class="text-gray-600">Welcome back, {{ user?.name }}!</p>
      </div>

      <!-- Action Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Facility Request Card -->
        <div @click="showFacilityModal = true" class="bg-gradient-to-br from-aviation-olive to-green-700 rounded-xl shadow-lg p-8 text-white cursor-pointer hover:shadow-xl transition-all">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-2xl font-bold">Facility<br/>Request</h3>
            <svg class="w-12 h-12 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
          </div>
        </div>

        <!-- Work Order Request Card -->
        <div @click="showWorkOrderModal = true" class="bg-gradient-to-br from-green-600 to-aviation-olive rounded-xl shadow-lg p-8 text-white cursor-pointer hover:shadow-xl transition-all">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-2xl font-bold">Work<br/>Order<br/>Request</h3>
            <svg class="w-12 h-12 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
          </div>
        </div>
      </div>

      <!-- Layout Grid: Calendar, Stats, Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Left Column: Calendar and Upcoming Requests -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Calendar Widget -->
          <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
            <SimpleCalendar :events="calendarEvents" @dateUpdated="handleDateUpdated" @monthChange="handleMonthChange" />
          </div>

          <!-- Upcoming Requests Section -->
          <UpcomingRequests />
        </div>

        <!-- Right Columns: Stats Cards and Charts -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Stats Cards Grid -->
          <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <!-- Facility Requests Today -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 text-center hover:shadow-xl transition-shadow flex items-center justify-center gap-2 flex-col">
              <div class="w-24 h-24 rounded-full border-4 border-gray-300 flex items-center justify-center bg-white shadow-lg hover:shadow-xl transition-shadow">
                <div class="text-4xl font-bold text-aviation-olive mb-2">{{ statistics.facility_requests_today }}</div>
              </div>
              <p class="text-xs text-gray-600 font-medium">Facility Requests Today</p>
            </div>

            <!-- Facility Pending Requests -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 text-center hover:shadow-xl transition-shadow flex items-center justify-center gap-2 flex-col">
              <div class="w-24 h-24 rounded-full border-4 border-gray-300 flex items-center justify-center bg-white shadow-lg hover:shadow-xl transition-shadow">
                <div class="text-4xl font-bold text-aviation-olive mb-2">{{ statistics.facility_pending_requests }}</div>
              </div>
              <p class="text-xs text-gray-600 font-medium">Facility Pending Requests</p>
            </div>

            <!-- Pending Maintenance -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 text-center hover:shadow-xl transition-shadow flex items-center justify-center gap-2 flex-col">
              <div class="w-24 h-24 rounded-full border-4 border-gray-300 flex items-center justify-center bg-white shadow-lg hover:shadow-xl transition-shadow">
                <div class="text-4xl font-bold text-aviation-olive mb-2">{{ statistics.pending_maintenance }}</div>
              </div>
              <p class="text-xs text-gray-600 font-medium">Pending Maintenance</p>
            </div>

            <!-- Work Orders Today -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 text-center hover:shadow-xl transition-shadow flex items-center justify-center gap-2 flex-col">
              <div class="w-24 h-24 rounded-full border-4 border-gray-300 flex items-center justify-center bg-white shadow-lg hover:shadow-xl transition-shadow">
                <div class="text-4xl font-bold text-aviation-olive mb-2">{{ statistics.work_orders_today }}</div>
              </div>
              <p class="text-xs text-gray-600 font-medium">Work Orders Today</p>
            </div>

            <!-- Facility Approved Requests -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 text-center hover:shadow-xl transition-shadow flex items-center justify-center gap-2 flex-col">
              <div class="w-24 h-24 rounded-full border-4 border-gray-300 flex items-center justify-center bg-white shadow-lg hover:shadow-xl transition-shadow">
                <div class="text-4xl font-bold text-aviation-olive mb-2">{{ statistics.facility_approved_requests }}</div>
              </div>
              <p class="text-xs text-gray-600 font-medium">Facility Approved Requests</p>
            </div>

            <!-- Urgent Repairs -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 text-center hover:shadow-xl transition-shadow flex items-center justify-center gap-2 flex-col">
              <div class="w-24 h-24 rounded-full border-4 border-gray-300 flex items-center justify-center bg-white shadow-lg hover:shadow-xl transition-shadow">
                <div class="text-4xl font-bold text-aviation-olive mb-2">{{ statistics.urgent_repairs }}</div>
              </div>
              <p class="text-xs text-gray-600 font-medium">Urgent Repairs</p>
            </div>
          </div>

          <!-- Charts Section -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Venue Usage Chart -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-gray-800">Venue Usage</h3>
                <div class="flex items-center gap-1 text-xs text-gray-600">
                  <span>{{ currentMonthLabel }}</span>
                </div>
              </div>
              <div class="h-48 flex items-center justify-center">
                <template v-if="venueUsageData.labels.length > 0">
                  <StatsChart
                    type="doughnut"
                    :labels="venueUsageData.labels"
                    :data="venueUsageData.values"
                    :backgroundColor="chartColors.slice(0, venueUsageData.labels.length)"
                  />
                </template>
                <p v-else class="text-sm text-gray-400">No venue usage data</p>
              </div>
              <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                <div v-for="(label, index) in venueUsageData.labels" :key="label" class="flex items-center gap-1">
                  <div class="w-2 h-2 rounded-full" :style="{ backgroundColor: chartColors[index % chartColors.length] }"></div>
                  <span class="text-gray-600">{{ label }}</span>
                </div>
              </div>
            </div>

            <!-- Maintenance Chart -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-gray-800">Maintenance</h3>
                <div class="flex items-center gap-1 text-xs text-gray-600">
                  <span>{{ currentMonthLabel }}</span>
                </div>
              </div>
              <div class="h-48 flex items-center justify-center">
                <template v-if="maintenanceData.labels.length > 0">
                  <StatsChart
                    type="doughnut"
                    :labels="maintenanceData.labels"
                    :data="maintenanceData.values"
                    :backgroundColor="chartColors.slice(0, maintenanceData.labels.length)"
                  />
                </template>
                <p v-else class="text-sm text-gray-400">No maintenance data</p>
              </div>
              <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                <div v-for="(label, index) in maintenanceData.labels" :key="label" class="flex items-center gap-1">
                  <div class="w-2 h-2 rounded-full" :style="{ backgroundColor: chartColors[index % chartColors.length] }"></div>
                  <span class="text-gray-600">{{ label }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Facility Request Modal -->
    <FacilityRequestModal v-model="showFacilityModal" @success="handleFacilityRequestSuccess" />

    <!-- Work Order Modal -->
    <WorkOrderModal v-model="showWorkOrderModal" @success="handleWorkOrderSuccess" />

    <!-- Facility Request Details Modal -->
    <FacilityRequestDetailsModal v-model="showDetailsModal" :request="selectedRequest" @statusUpdated="handleStatusUpdated" />

    <!-- Work Order Details Modal -->
    <WorkOrderDetailsModal v-model="showWorkOrderDetailsModal" :order="selectedWorkOrder" @statusUpdated="handleStatusUpdated" />
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/components/AppLayout.vue';
import SimpleCalendar from '@/components/SimpleCalendar.vue';
import StatsChart from '@/components/StatsChart.vue';
import FacilityRequestModal from '@/components/FacilityRequestModal.vue';
import WorkOrderModal from '@/components/WorkOrderModal.vue';
import FacilityRequestDetailsModal from '@/components/FacilityRequestDetailsModal.vue';
import WorkOrderDetailsModal from '@/components/WorkOrderDetailsModal.vue';
import UpcomingRequests from '@/components/UpcomingRequests.vue';
import { API_URL } from '@/config/api';
import { useDashboard } from '@/composables/useDashboard';

const {
  statistics,
  venueUsageData,
  maintenanceData,
  calendarEvents,
  loading,
  loadAllDashboardData,
  fetchVenueUsage,
  fetchMaintenanceData,
  fetchCalendarEvents,
} = useDashboard();

const user = ref<any>(null);
const showFacilityModal = ref(false);
const showWorkOrderModal = ref(false);
const showDetailsModal = ref(false);
const selectedRequest = ref<any>(null);
const loadingDetails = ref(false);
const showWorkOrderDetailsModal = ref(false);
const selectedWorkOrder = ref<any>(null);
const currentMonth = ref(new Date().getMonth() + 1);
const currentYear = ref(new Date().getFullYear());

const currentMonthLabel = computed(() => {
  const date = new Date(currentYear.value, currentMonth.value - 1);
  return date.toLocaleString('default', { month: 'long', year: 'numeric' });
});

const chartColors = ['#4A7C59', '#5A8C69', '#6C9A6C', '#7DAA7D', '#8EBA8E', '#A0CCA0', '#B2DDB2'];

const handleFacilityRequestSuccess = (request: any) => {
  console.log('Facility request submitted:', request);
  loadAllDashboardData();
};

const handleWorkOrderSuccess = (workOrder: any) => {
  console.log('Work order submitted:', workOrder);
  loadAllDashboardData();
};

const handleDateUpdated = () => {
  console.log('Event date updated - refreshing dashboard');
  loadAllDashboardData();
};

const handleMonthChange = ({ month, year }: { month: number; year: number }) => {
  currentMonth.value = month;
  currentYear.value = year;
  fetchCalendarEvents(month, year);
  fetchVenueUsage(month, year);
  fetchMaintenanceData(month, year);
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
  } else if (type === 'work_order') {
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
  console.log('Status updated - refreshing dashboard');
  loadAllDashboardData();
};

onMounted(() => {
  const userStr = localStorage.getItem('user');
  if (userStr) {
    user.value = JSON.parse(userStr);
  }
  loadAllDashboardData();
});
</script>
