<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <div class="bg-white rounded-xl shadow-lg p-8">
        <div class="text-center mb-6">
          <h1 class="text-2xl font-bold text-gray-800">Forgot Password</h1>
          <p class="text-gray-600 text-sm mt-2">Enter your email to receive a password reset link</p>
        </div>

        <div v-if="!submitted">
          <form @submit.prevent="sendResetLink" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
              <input v-model="email" type="email" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent bg-white text-black" placeholder="your@email.com" />
            </div>
            <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>
            <button type="submit" :disabled="loading" class="w-full py-3 bg-aviation-olive text-white rounded-lg font-medium hover:bg-opacity-90 disabled:opacity-50">
              {{ loading ? 'Sending...' : 'Send Reset Link' }}
            </button>
          </form>
        </div>

        <div v-else class="text-center">
          <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <p class="text-gray-600">A password reset link has been sent to <strong>{{ email }}</strong></p>
          <p class="text-gray-500 text-sm mt-2">Check your email and follow the instructions.</p>
        </div>

        <div class="mt-6 text-center">
          <router-link to="/login" class="text-sm text-aviation-olive hover:underline">Back to Login</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { API_URL } from '@/config/api';

const email = ref('');
const loading = ref(false);
const error = ref('');
const submitted = ref(false);

const sendResetLink = async () => {
  loading.value = true;
  error.value = '';
  try {
    const res = await fetch(`${API_URL}/forgot-password`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ email: email.value }),
    });
    const data = await res.json();
    if (res.ok && data.success) {
      submitted.value = true;
    } else {
      error.value = data.message || 'Failed to send reset link';
    }
  } catch (e: any) {
    error.value = 'Network error. Please try again.';
  } finally {
    loading.value = false;
  }
};
</script>
