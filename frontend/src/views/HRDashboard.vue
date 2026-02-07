<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto">
      <!-- Welcome Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Dashboard</h1>
        <p class="text-gray-600">Welcome back, {{ user?.name }}!</p>
      </div>

      <!-- Main Grid Layout -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Facility Request Section -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg border border-gray-100">
          <!-- Header with Dropdown -->
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <div class="relative">
                <select
                  v-model="activeSection"
                  class="text-xl font-semibold text-gray-800 bg-transparent border-none outline-none cursor-pointer appearance-none pr-8"
                >
                  <option value="facility">Facility Request</option>
                  <option value="family">Family</option>
                  <option value="word">Word</option>
                </select>
                <svg class="w-4 h-4 text-gray-500 absolute right-0 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </div>
              <button
                @click="showFacilityModal = true"
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
            <!-- Request Item 1 -->
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
              <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                  <div class="w-3 h-3 bg-blue-500 rounded-full mt-1"></div>
                  <div>
                    <h3 class="font-semibold text-gray-800">Multi Purpose Hall</h3>
                    <p class="text-sm text-gray-600 mt-1">Supreme Student Council</p>
                    <p class="text-xs text-gray-500 mt-1">9:00am - 5:00pm, January 15-16, 2026</p>
                  </div>
                </div>
                <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs rounded-full font-medium whitespace-nowrap">
                  Status: Pending
                </span>
              </div>
              <div class="flex justify-end">
                <button class="px-6 py-2 bg-aviation-olive text-white text-sm rounded-lg hover:bg-opacity-90 transition-all">
                  Details
                </button>
              </div>
            </div>

            <!-- Request Item 2 -->
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
              <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                  <div class="w-3 h-3 bg-yellow-500 rounded-full mt-1"></div>
                  <div>
                    <h3 class="font-semibold text-gray-800">Multi Purpose Hall</h3>
                    <p class="text-sm text-gray-600 mt-1">ACES Organization</p>
                    <p class="text-xs text-gray-500 mt-1">9:00am - 12:00nn, January 17, 2026</p>
                  </div>
                </div>
                <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs rounded-full font-medium whitespace-nowrap">
                  Status: Pending
                </span>
              </div>
              <div class="flex justify-end">
                <button class="px-6 py-2 bg-aviation-olive text-white text-sm rounded-lg hover:bg-opacity-90 transition-all">
                  Details
                </button>
              </div>
            </div>

            <!-- Request Item 3 -->
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
              <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                  <div class="w-3 h-3 bg-orange-500 rounded-full mt-1"></div>
                  <div>
                    <h3 class="font-semibold text-gray-800">Covered Court</h3>
                    <p class="text-sm text-gray-600 mt-1">AME Organization</p>
                    <p class="text-xs text-gray-500 mt-1">9:00am - 5:00pm, January 17, 2026</p>
                  </div>
                </div>
                <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs rounded-full font-medium whitespace-nowrap">
                  Status: Pending
                </span>
              </div>
              <div class="flex justify-end">
                <button class="px-6 py-2 bg-aviation-olive text-white text-sm rounded-lg hover:bg-opacity-90 transition-all">
                  Details
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column: Calendar and Upcoming Requests -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Calendar Widget -->
          <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
            <SimpleCalendar @dateUpdated="handleDateUpdated" />
          </div>

          <!-- Upcoming Requests Section -->
          <UpcomingRequests />
        </div>
      </div>
    </div>

    <!-- Facility Request Modal -->
    <FacilityRequestModal v-model="showFacilityModal" @success="handleRequestSuccess" />
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AppLayout from '@/components/AppLayout.vue';
import SimpleCalendar from '@/components/SimpleCalendar.vue';
import FacilityRequestModal from '@/components/FacilityRequestModal.vue';
import UpcomingRequests from '@/components/UpcomingRequests.vue';

const user = ref<any>(null);
const activeFilter = ref('all');
const activeSection = ref('facility');
const showFacilityModal = ref(false);

const filters = [
  { label: 'All', value: 'all' },
  { label: 'Pending', value: 'pending' },
  { label: 'Approved', value: 'approved' },
  { label: 'Disapproved', value: 'disapproved' },
  { label: 'Canceled', value: 'canceled' },
];

const handleRequestSuccess = (request: any) => {
  console.log('Facility request submitted:', request);
  // You can add logic here to refresh the list or show a notification
};

const handleDateUpdated = () => {
  console.log('Event date updated - refreshing dashboard');
  // Refresh dashboard data when date is updated
  window.location.reload();
};

onMounted(() => {
  const userStr = localStorage.getItem('user');
  if (userStr) {
    user.value = JSON.parse(userStr);
  }
});
</script>
