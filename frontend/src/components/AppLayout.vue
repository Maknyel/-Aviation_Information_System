<template>
  <div class="h-screen flex flex-col overflow-hidden">
    <!-- Header - Fixed at top -->
    <AppHeader @toggleSidebar="toggleSidebar" />

    <!-- Main Layout - Takes remaining height -->
    <div class="flex flex-1 overflow-hidden">
      <!-- Sidebar - Fixed height, scrollable if needed -->
      <AppSidebar :user="user" :isOpen="sidebarOpen" @toggle="toggleSidebar" />

      <!-- Main Content - Scrollable only -->
      <main class="flex-1 bg-gradient-to-br from-aviation-white to-gray-50 overflow-y-auto">
        <div class="p-6">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AppHeader from './AppHeader.vue';
import AppSidebar from './AppSidebar.vue';

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
