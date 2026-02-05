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
          <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
              {{ user?.role?.name === 'Admin' ? 'All Requests' : 'My Requests' }}
            </h1>
            <p class="text-gray-600">
              {{ user?.role?.name === 'Admin' ? 'Manage all facility and work order requests' : 'View and manage your requests' }}
            </p>
          </div>

          <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
            <p class="text-gray-500">Requests content coming soon...</p>
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
