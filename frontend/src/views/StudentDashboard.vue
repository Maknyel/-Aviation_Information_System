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
        <div class="bg-gradient-to-br from-aviation-olive to-green-700 rounded-xl shadow-lg p-8 text-white cursor-pointer hover:shadow-xl transition-all">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-2xl font-bold">Facility<br/>Request</h3>
            <svg class="w-12 h-12 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
          </div>
        </div>

        <!-- Work Order Request Card -->
        <div class="bg-gradient-to-br from-green-600 to-aviation-olive rounded-xl shadow-lg p-8 text-white cursor-pointer hover:shadow-xl transition-all">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-2xl font-bold">Work<br/>Order<br/>Request</h3>
            <svg class="w-12 h-12 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
          </div>
        </div>
      </div>

      <!-- Layout Grid: Calendar, Upcoming Requests, Stats -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Calendar Widget -->
        <div class="lg:col-span-1 bg-white rounded-xl shadow-lg p-6 border border-gray-100">
          <SimpleCalendar />

          <!-- Upcoming Requests Section -->
          <div class="mt-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">Upcoming Requests</h3>
            <div class="space-y-3">
              <div class="p-3 bg-gray-50 rounded-lg border-l-4 border-aviation-olive">
                <div class="flex items-center gap-2 mb-2">
                  <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                  <h4 class="text-sm font-semibold text-gray-800">Multi Purpose Hall</h4>
                </div>
                <p class="text-xs text-gray-500 mb-2">Status: Pending</p>
                <div class="flex gap-2">
                  <button class="px-3 py-1 bg-aviation-olive bg-opacity-10 text-aviation-olive text-xs rounded hover:bg-opacity-20 transition-all flex-1">
                    Send to HR
                  </button>
                  <button class="px-3 py-1 bg-aviation-olive text-white text-xs rounded hover:bg-opacity-90 transition-all flex-1">
                    Details
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Stats Cards Grid -->
        <div class="lg:col-span-2 grid grid-cols-2 md:grid-cols-3 gap-4">
          <!-- Facility Requests Today -->
          <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 text-center hover:shadow-xl transition-shadow">
            <div class="text-4xl font-bold text-aviation-olive mb-2">0</div>
            <p class="text-xs text-gray-600 font-medium">Facility Requests Today</p>
          </div>

          <!-- Facility Pending Requests -->
          <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 text-center hover:shadow-xl transition-shadow">
            <div class="text-4xl font-bold text-aviation-olive mb-2">0</div>
            <p class="text-xs text-gray-600 font-medium">Facility Pending Requests</p>
          </div>

          <!-- Pending Maintenance -->
          <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 text-center hover:shadow-xl transition-shadow">
            <div class="text-4xl font-bold text-aviation-olive mb-2">0</div>
            <p class="text-xs text-gray-600 font-medium">Pending Maintenance</p>
          </div>

          <!-- Work Orders Today -->
          <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 text-center hover:shadow-xl transition-shadow">
            <div class="text-4xl font-bold text-aviation-olive mb-2">0</div>
            <p class="text-xs text-gray-600 font-medium">Work Orders Today</p>
          </div>

          <!-- Facility Approved Requests -->
          <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 text-center hover:shadow-xl transition-shadow">
            <div class="text-4xl font-bold text-aviation-olive mb-2">0</div>
            <p class="text-xs text-gray-600 font-medium">Facility Approved Requests</p>
          </div>

          <!-- Urgent Repairs -->
          <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 text-center hover:shadow-xl transition-shadow">
            <div class="text-4xl font-bold text-aviation-olive mb-2">0</div>
            <p class="text-xs text-gray-600 font-medium">Urgent Repairs</p>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Venue Usage Chart -->
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Venue Usage</h3>
            <div class="flex items-center gap-2 text-sm text-gray-600">
              <span>December 2025</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </div>
          </div>
          <div class="h-64 flex items-center justify-center">
            <StatsChart
              type="doughnut"
              :labels="['Building A', 'Covered Court', 'Building B', 'Hangar', 'Others']"
              :data="[15, 15, 20, 10, 20]"
              :backgroundColor="['#4A7C59', '#5A8C69', '#6C9A6C', '#7DAA7D', '#8EBA8E']"
            />
          </div>
          <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 bg-[#4A7C59] rounded-full"></div>
              <span class="text-gray-600">Building A <span class="font-semibold">15%</span></span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 bg-[#5A8C69] rounded-full"></div>
              <span class="text-gray-600">Covered Court <span class="font-semibold">15%</span></span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 bg-[#6C9A6C] rounded-full"></div>
              <span class="text-gray-600">Building B <span class="font-semibold">20%</span></span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 bg-[#7DAA7D] rounded-full"></div>
              <span class="text-gray-600">Hangar <span class="font-semibold">10%</span></span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 bg-[#8EBA8E] rounded-full"></div>
              <span class="text-gray-600">Others <span class="font-semibold">20%</span></span>
            </div>
          </div>
        </div>

        <!-- Maintenance Chart -->
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Maintenance</h3>
            <div class="flex items-center gap-2 text-sm text-gray-600">
              <span>December 2025</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </div>
          </div>
          <div class="h-64 flex items-center justify-center">
            <StatsChart
              type="doughnut"
              :labels="['Building A', 'Covered Court', 'Building B', 'Hangar', 'Others']"
              :data="[15, 15, 10, 10, 33]"
              :backgroundColor="['#4A7C59', '#5A8C69', '#6C9A6C', '#7DAA7D', '#8EBA8E']"
            />
          </div>
          <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 bg-[#4A7C59] rounded-full"></div>
              <span class="text-gray-600">Building A <span class="font-semibold">15%</span></span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 bg-[#5A8C69] rounded-full"></div>
              <span class="text-gray-600">Covered Court <span class="font-semibold">15%</span></span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 bg-[#6C9A6C] rounded-full"></div>
              <span class="text-gray-600">Building B <span class="font-semibold">10%</span></span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 bg-[#7DAA7D] rounded-full"></div>
              <span class="text-gray-600">Hangar <span class="font-semibold">10%</span></span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 bg-[#8EBA8E] rounded-full"></div>
              <span class="text-gray-600">Others <span class="font-semibold">33%</span></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AppLayout from '@/components/AppLayout.vue';
import SimpleCalendar from '@/components/SimpleCalendar.vue';
import StatsChart from '@/components/StatsChart.vue';

const user = ref<any>(null);

onMounted(() => {
  const userStr = localStorage.getItem('user');
  if (userStr) {
    user.value = JSON.parse(userStr);
  }
});
</script>
