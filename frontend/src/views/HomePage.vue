<template>
  <div class="min-h-screen flex flex-col">
    <!-- Header -->
    <AppHeader @toggleSidebar="toggleSidebar" />

    <!-- Main Layout -->
    <div class="flex flex-1">
      <!-- Sidebar -->
      <AppSidebar :user="user" :isOpen="sidebarOpen" @toggle="toggleSidebar" />

      <!-- Main Content -->
      <main class="flex-1 bg-gradient-to-br from-aviation-white to-gray-50 p-6 overflow-auto">
        <div class="max-w-7xl mx-auto">
          <!-- Welcome Header -->
          <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Dashboard</h1>
            <p class="text-gray-600">Welcome back, {{ user?.name }}!</p>
          </div>

          <!-- Dashboard Cards Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <!-- Facility Request Card -->
            <div class="bg-aviation-olive rounded-xl shadow-lg p-6 text-white">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Facility Request</h3>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
              </div>
              <p class="text-white text-opacity-90 text-sm">Manage and track facility requests</p>
            </div>

            <!-- Work Order Request Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Work Order</h3>
                <div class="w-10 h-10 bg-aviation-olive bg-opacity-20 rounded-lg flex items-center justify-center">
                  <svg class="w-6 h-6 text-aviation-olive" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                </div>
              </div>
              <p class="text-gray-600 text-sm">Submit and monitor work orders</p>
            </div>

            <!-- Stats Cards -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Facility Requests</h3>
                <div class="text-3xl font-bold text-aviation-olive">0</div>
              </div>
              <p class="text-gray-600 text-sm">Pending requests</p>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Facility Approved</h3>
                <div class="text-3xl font-bold text-aviation-olive">0</div>
              </div>
              <p class="text-gray-600 text-sm">Approved facility requests</p>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Facility Maintenance</h3>
                <div class="text-3xl font-bold text-aviation-olive">0</div>
              </div>
              <p class="text-gray-600 text-sm">Under maintenance</p>
            </div>
          </div>

          <!-- Calendar and Upcoming Requests -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Calendar Widget -->
            <div class="lg:col-span-1 bg-white rounded-xl shadow-lg p-6 border border-gray-100">
              <h3 class="text-lg font-semibold text-gray-800 mb-4">January 2026</h3>
              <div class="calendar">
                <div class="grid grid-cols-7 gap-2 text-center text-sm">
                  <div class="font-semibold text-gray-600">Mon</div>
                  <div class="font-semibold text-gray-600">Tue</div>
                  <div class="font-semibold text-gray-600">Wed</div>
                  <div class="font-semibold text-gray-600">Thu</div>
                  <div class="font-semibold text-gray-600">Fri</div>
                  <div class="font-semibold text-gray-600">Sat</div>
                  <div class="font-semibold text-gray-600">Sun</div>

                  <div v-for="day in 31" :key="day" class="p-2 hover:bg-aviation-olive hover:text-white rounded cursor-pointer transition-colors">
                    {{ day }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Upcoming Requests -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6 border border-gray-100">
              <h3 class="text-lg font-semibold text-gray-800 mb-4">Upcoming Requests</h3>
              <div class="space-y-4">
                <div class="p-4 bg-gray-50 rounded-lg">
                  <div class="flex items-center justify-between">
                    <div>
                      <h4 class="font-semibold text-gray-800">Multi Purpose Hall</h4>
                      <p class="text-sm text-gray-600">Leadership Seminar - January 15-16, 2026</p>
                    </div>
                    <span class="px-3 py-1 bg-aviation-olive text-white text-sm rounded-full">Pending</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AppHeader from '@/components/AppHeader.vue';
import AppSidebar from '@/components/AppSidebar.vue';

const user = ref<any>(null);
const sidebarOpen = ref(false);

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

onMounted(() => {
  const userStr = localStorage.getItem('user');
  if (userStr) {
    user.value = JSON.parse(userStr);
  }
});
</script>
