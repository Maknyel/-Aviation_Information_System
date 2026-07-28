<template>
  <router-view />
  <ToastContainer />
</template>

<script setup lang="ts">
// Using standard Vue Router since we're using minimal Ionic components
import ToastContainer from '@/components/ToastContainer.vue';
</script>
