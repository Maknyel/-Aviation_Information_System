<template>
  <header class="bg-aviation-olive shadow-md">
    <div class="max-w-full mx-auto px-4 py-4 flex items-center justify-between">
      <div class="flex items-center gap-4">
        <!-- Hamburger Menu Button (Mobile) -->
        <button
          @click="emit('toggleSidebar')"
          class="lg:hidden text-white p-2 hover:bg-white hover:bg-opacity-20 rounded-lg transition-all"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>

        <h1 class="text-white text-xl font-semibold">Aviation Information System</h1>
      </div>

      <button
        @click="handleLogout"
        class="flex items-center gap-2 px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg transition-all text-white"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
        </svg>
        <span class="hidden sm:inline">Logout</span>
      </button>
    </div>
  </header>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router';

const emit = defineEmits(['toggleSidebar']);
const router = useRouter();

const handleLogout = () => {
  localStorage.removeItem('token');
  localStorage.removeItem('user');
  router.push('/login');
};
</script>
