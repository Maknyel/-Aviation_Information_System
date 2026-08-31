<template>
  <router-view />
  <ToastContainer />
</template>

<script setup lang="ts">
// Using standard Vue Router since we're using minimal Ionic components
import { onMounted } from 'vue';
import ToastContainer from '@/components/ToastContainer.vue';
import { initEcho } from '@/echo';

onMounted(() => {
  // Re-establish the realtime connection on page load/refresh if already logged in
  initEcho();
});
</script>
