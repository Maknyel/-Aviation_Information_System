<template>
  <div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-aviation-olive shadow-md">
      <div class="max-w-7xl mx-auto px-4 py-4">
        <h1 class="text-white text-xl font-semibold text-center">Aviation Information System</h1>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center px-4 py-8 bg-gradient-to-br from-aviation-white to-gray-50">
      <div class="w-full max-w-md">
        <!-- Logo Section -->
        <div class="text-center mb-8">
          <div class="flex justify-center mb-4">
            <img src="/logo.png" alt="Philippine State College of Aeronautics" class="h-24 w-24 object-contain" />
          </div>
          <h1 class="text-3xl font-bold text-aviation-olive mb-2">Aviation Information System</h1>
          <p class="text-gray-600">Please login to continue</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
          <form @submit.prevent="handleLogin" class="space-y-6">
            <!-- Email Input -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
              <input
                v-model="email"
                type="email"
                required
                placeholder="Enter your email"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent outline-none transition-all bg-white text-black"
              />
            </div>

            <!-- Password Input -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
              <input
                v-model="password"
                type="password"
                required
                placeholder="Enter your password"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent outline-none transition-all bg-white text-black"
              />
            </div>

            <!-- Error Message -->
            <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
              {{ error }}
            </div>

            <!-- Login Button -->
            <button
              type="submit"
              :disabled="loading"
              class="w-full bg-aviation-olive hover:bg-opacity-90 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
            >
              <span v-if="loading" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Logging in...
              </span>
              <span v-else>Login</span>
            </button>
          </form>
        </div>

        <!-- Test Accounts -->
        <!-- <div class="mt-8 bg-white bg-opacity-80 rounded-lg p-6 text-center shadow-sm">
          <p class="font-semibold text-gray-700 mb-3">Test Accounts:</p>
          <div class="space-y-1 text-sm text-gray-600">
            <p>admin@aviation.com - password</p>
            <p>staff@aviation.com - password</p>
            <p>student@aviation.com - password</p>
          </div>
        </div> -->
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { API_URL } from '@/config/api';

const router = useRouter();
const email = ref('');
const password = ref('');
const loading = ref(false);
const error = ref('');

const handleLogin = async () => {
  loading.value = true;
  error.value = '';

  try {
    const response = await axios.post(`${API_URL}/login`, {
      email: email.value,
      password: password.value,
    });

    if (response.data.token) {
      localStorage.setItem('token', response.data.token);
      localStorage.setItem('user', JSON.stringify(response.data.user));
      router.push('/home');
    }
  } catch (err: any) {
    if (err.response && err.response.data && err.response.data.message) {
      error.value = err.response.data.message;
    } else {
      error.value = 'Login failed. Please check your credentials.';
    }
  } finally {
    loading.value = false;
  }
};
</script>

