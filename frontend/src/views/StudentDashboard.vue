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
            <SimpleCalendar :events="calendarEvents" @monthChange="handleMonthChange" @dateUpdated="handleDateUpdated" />
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
                <div class="text-4xl font-bold text-aviation-olive mb-2">{{ loading ? '...' : statistics.facility_requests_today }}</div>
              </div>
              <p class="text-xs text-gray-600 font-medium">Facility Requests Today</p>
            </div>

            <!-- Facility Pending Requests -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 text-center hover:shadow-xl transition-shadow flex items-center justify-center gap-2 flex-col">
              <div class="w-24 h-24 rounded-full border-4 border-gray-300 flex items-center justify-center bg-white shadow-lg hover:shadow-xl transition-shadow">
                <div class="text-4xl font-bold text-aviation-olive mb-2">{{ loading ? '...' : statistics.facility_pending_requests }}</div>
              </div>
              <p class="text-xs text-gray-600 font-medium">Facility Pending Requests</p>
            </div>

            <!-- Pending Maintenance -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 text-center hover:shadow-xl transition-shadow flex items-center justify-center gap-2 flex-col">
              <div class="w-24 h-24 rounded-full border-4 border-gray-300 flex items-center justify-center bg-white shadow-lg hover:shadow-xl transition-shadow">
                <div class="text-4xl font-bold text-aviation-olive mb-2">{{ loading ? '...' : statistics.pending_maintenance }}</div>
              </div>
              <p class="text-xs text-gray-600 font-medium">Pending Maintenance</p>
            </div>

            <!-- Work Orders Today -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 text-center hover:shadow-xl transition-shadow flex items-center justify-center gap-2 flex-col">
              <div class="w-24 h-24 rounded-full border-4 border-gray-300 flex items-center justify-center bg-white shadow-lg hover:shadow-xl transition-shadow">
                <div class="text-4xl font-bold text-aviation-olive mb-2">{{ loading ? '...' : statistics.work_orders_today }}</div>
              </div>
              <p class="text-xs text-gray-600 font-medium">Work Orders Today</p>
            </div>

            <!-- Facility Approved Requests -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 text-center hover:shadow-xl transition-shadow flex items-center justify-center gap-2 flex-col">
              <div class="w-24 h-24 rounded-full border-4 border-gray-300 flex items-center justify-center bg-white shadow-lg hover:shadow-xl transition-shadow">
                <div class="text-4xl font-bold text-aviation-olive mb-2">{{ loading ? '...' : statistics.facility_approved_requests }}</div>
              </div>
              <p class="text-xs text-gray-600 font-medium">Facility Approved Requests</p>
            </div>

            <!-- Urgent Repairs -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 text-center hover:shadow-xl transition-shadow flex items-center justify-center gap-2 flex-col">
              <div class="w-24 h-24 rounded-full border-4 border-gray-300 flex items-center justify-center bg-white shadow-lg hover:shadow-xl transition-shadow">
                <div class="text-4xl font-bold text-aviation-olive mb-2">{{ loading ? '...' : statistics.urgent_repairs }}</div>
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
                <div class="flex items-center gap-2">
                  <button @click="previousChartMonth" class="p-1 hover:bg-gray-100 rounded">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                  </button>
                  <span class="text-sm text-gray-600 min-w-[120px] text-center">{{ chartMonthYear }}</span>
                  <button @click="nextChartMonth" :disabled="!canGoNextMonth" :class="canGoNextMonth ? 'hover:bg-gray-100' : 'opacity-50 cursor-not-allowed'" class="p-1 rounded">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                  </button>
                </div>
              </div>
              <div class="h-48 flex items-center justify-center">
                <div v-if="loading" class="text-gray-500 text-sm">Loading chart...</div>
                <div v-else-if="venueUsageData.labels.length === 0" class="text-gray-500 text-sm">No data available</div>
                <StatsChart
                  v-else
                  type="doughnut"
                  :labels="venueUsageData.labels"
                  :data="venueUsageData.values"
                  :backgroundColor="chartColors"
                />
              </div>
              <div v-if="!loading && venueUsageData.labels.length > 0" class="mt-3 grid grid-cols-2 gap-2 text-xs">
                <div v-for="(label, index) in venueUsageData.labels" :key="label" class="flex items-center gap-1">
                  <div class="w-2 h-2 rounded-full" :style="{ backgroundColor: chartColors[index] }"></div>
                  <span class="text-gray-600">{{ label }}</span>
                </div>
              </div>
            </div>

            <!-- Maintenance Chart -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-gray-800">Maintenance</h3>
                <div class="flex items-center gap-2">
                  <button @click="previousChartMonth" class="p-1 hover:bg-gray-100 rounded">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                  </button>
                  <span class="text-sm text-gray-600 min-w-[120px] text-center">{{ chartMonthYear }}</span>
                  <button @click="nextChartMonth" :disabled="!canGoNextMonth" :class="canGoNextMonth ? 'hover:bg-gray-100' : 'opacity-50 cursor-not-allowed'" class="p-1 rounded">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                  </button>
                </div>
              </div>
              <div class="h-48 flex items-center justify-center">
                <div v-if="loading" class="text-gray-500 text-sm">Loading chart...</div>
                <div v-else-if="maintenanceData.labels.length === 0" class="text-gray-500 text-sm">No data available</div>
                <StatsChart
                  v-else
                  type="doughnut"
                  :labels="maintenanceData.labels"
                  :data="maintenanceData.values"
                  :backgroundColor="maintenanceColors"
                />
              </div>
              <div v-if="!loading && maintenanceData.labels.length > 0" class="mt-3 grid grid-cols-2 gap-2 text-xs">
                <div v-for="(label, index) in maintenanceData.labels" :key="label" class="flex items-center gap-1">
                  <div class="w-2 h-2 rounded-full" :style="{ backgroundColor: maintenanceColors[index] }"></div>
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

  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import AppLayout from '@/components/AppLayout.vue';
import SimpleCalendar from '@/components/SimpleCalendar.vue';
import StatsChart from '@/components/StatsChart.vue';
import FacilityRequestModal from '@/components/FacilityRequestModal.vue';
import WorkOrderModal from '@/components/WorkOrderModal.vue';
import UpcomingRequests from '@/components/UpcomingRequests.vue';
import { useDashboard } from '@/composables/useDashboard';

const user = ref<any>(null);
const showFacilityModal = ref(false);
const showWorkOrderModal = ref(false);
const {
  statistics,
  venueUsageData,
  maintenanceData,
  calendarEvents,
  loading,
  loadAllDashboardData,
  fetchCalendarEvents,
  fetchVenueUsage,
  fetchMaintenanceData,
} = useDashboard();

// Chart date navigation
const chartDate = ref(new Date());

const chartMonthYear = computed(() => {
  const options: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'long' };
  return chartDate.value.toLocaleDateString('en-US', options);
});

const canGoNextMonth = computed(() => {
  const now = new Date();
  return chartDate.value < new Date(now.getFullYear(), now.getMonth(), 1);
});

const previousChartMonth = () => {
  chartDate.value = new Date(chartDate.value.getFullYear(), chartDate.value.getMonth() - 1, 1);
  fetchVenueUsage(chartDate.value.getMonth() + 1, chartDate.value.getFullYear());
  fetchMaintenanceData(chartDate.value.getMonth() + 1, chartDate.value.getFullYear());
};

const nextChartMonth = () => {
  if (canGoNextMonth.value) {
    chartDate.value = new Date(chartDate.value.getFullYear(), chartDate.value.getMonth() + 1, 1);
    fetchVenueUsage(chartDate.value.getMonth() + 1, chartDate.value.getFullYear());
    fetchMaintenanceData(chartDate.value.getMonth() + 1, chartDate.value.getFullYear());
  }
};

// Computed properties for chart colors
const chartColors = computed(() => {
  const baseColors = ['#4A7C59', '#5A8C69', '#6C9A6C', '#7DAA7D', '#8EBA8E'];
  const labels = venueUsageData.value.labels.length;
  return baseColors.slice(0, labels);
});

const maintenanceColors = computed(() => {
  const baseColors = ['#4A7C59', '#5A8C69', '#6C9A6C', '#7DAA7D', '#8EBA8E'];
  const labels = maintenanceData.value.labels.length;
  return baseColors.slice(0, labels);
});

const handleFacilityRequestSuccess = (request: any) => {
  console.log('Facility request submitted:', request);
  loadAllDashboardData(); // Refresh dashboard data
};

const handleWorkOrderSuccess = (workOrder: any) => {
  console.log('Work order submitted:', workOrder);
  loadAllDashboardData(); // Refresh dashboard data
};

const handleDateUpdated = () => {
  console.log('Event date updated');
  loadAllDashboardData(); // Refresh all dashboard data including calendar
};

const getStatusColor = (status: string) => {
  const colors: { [key: string]: string } = {
    pending: 'bg-yellow-500',
    approved: 'bg-green-500',
    rejected: 'bg-red-500',
    canceled: 'bg-gray-500',
    in_progress: 'bg-blue-500',
    completed: 'bg-green-600',
  };
  return colors[status] || 'bg-gray-500';
};

const handleMonthChange = (data: { month: number; year: number }) => {
  fetchCalendarEvents(data.month, data.year);
};

onMounted(() => {
  const userStr = localStorage.getItem('user');
  if (userStr) {
    user.value = JSON.parse(userStr);
  }
  loadAllDashboardData();
});
</script>
