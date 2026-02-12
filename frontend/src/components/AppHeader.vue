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

        <h1 class="text-white text-xl font-semibold">General Service Department</h1>
      </div>

      <!-- User Dropdown -->
      <div class="relative">
        <button
          @click="toggleDropdown"
          class="flex items-center gap-3 px-3 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg transition-all text-white"
        >
          <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-aviation-olive text-sm font-bold overflow-hidden">
            <img v-if="user?.profile_picture" :src="getProfilePictureUrl(user.profile_picture)" alt="Profile" class="w-full h-full object-cover" />
            <span v-else>{{ user?.name?.charAt(0) || 'U' }}</span>
          </div>
          <span class="hidden sm:inline font-medium">{{ user?.name }}</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>

        <!-- Dropdown Menu -->
        <div
          v-if="dropdownOpen"
          class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50"
        >
          <router-link
            to="/profile"
            @click="closeDropdown"
            class="flex items-center gap-3 px-4 py-2 text-gray-700 hover:bg-gray-100 transition-all"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span>Profile</span>
          </router-link>

          <button
            @click="handleLogout"
            class="w-full flex items-center gap-3 px-4 py-2 text-gray-700 hover:bg-gray-100 transition-all"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
            <span>Logout</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Click outside to close dropdown -->
    <div v-if="dropdownOpen" @click="closeDropdown" class="fixed inset-0 z-40"></div>
  </header>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const emit = defineEmits(['toggleSidebar']);
const router = useRouter();
const user = ref<any>(null);
const dropdownOpen = ref(false);

const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value;
};

const closeDropdown = () => {
  dropdownOpen.value = false;
};

const getProfilePictureUrl = (path: string) => {
  if (!path) return '';
  const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
  return `${baseUrl.replace('/api', '')}/storage/${path}`;
};

const handleLogout = () => {
  localStorage.removeItem('token');
  localStorage.removeItem('user');
  router.push('/login');
};

onMounted(() => {
  const userStr = localStorage.getItem('user');
  if (userStr) {
    user.value = JSON.parse(userStr);
  }
});
</script>
