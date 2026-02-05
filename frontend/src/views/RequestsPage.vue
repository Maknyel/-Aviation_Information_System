<template>
  <AppLayout>
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
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AppLayout from '@/components/AppLayout.vue';

const user = ref<any>(null);

onMounted(() => {
  const userStr = localStorage.getItem('user');
  if (userStr) {
    user.value = JSON.parse(userStr);
  }
});
</script>
