<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <div class="bg-white rounded-xl shadow-lg p-8">
        <div class="text-center mb-6">
          <h1 class="text-2xl font-bold text-gray-800">Reset Password</h1>
          <p class="text-gray-600 text-sm mt-2">Enter your new password below</p>
        </div>

        <div v-if="!success">
          <form @submit.prevent="resetPassword" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
              <input v-model="email" type="email" required readonly class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
              <input v-model="password" type="password" required minlength="8" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent bg-white text-black" placeholder="Min. 8 characters" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
              <input v-model="passwordConfirmation" type="password" required minlength="8" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent bg-white text-black" placeholder="Re-enter password" />
            </div>
            <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>
            <button type="submit" :disabled="loading" class="w-full py-3 bg-aviation-olive text-white rounded-lg font-medium hover:bg-opacity-90 disabled:opacity-50">
              {{ loading ? 'Resetting...' : 'Reset Password' }}
            </button>
          </form>
        </div>

        <div v-else class="text-center">
          <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <p class="text-gray-800 font-semibold">Password Reset Successful</p>
          <p class="text-gray-600 text-sm mt-2">You can now log in with your new password.</p>
          <router-link to="/login" class="inline-block mt-4 px-6 py-3 bg-aviation-olive text-white rounded-lg font-medium hover:bg-opacity-90">
            Go to Login
          </router-link>
        </div>

        <div v-if="!success" class="mt-6 text-center">
          <router-link to="/login" class="text-sm text-aviation-olive hover:underline">Back to Login</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { API_URL } from '@/config/api';

const route = useRoute();

const token = ref('');
const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const loading = ref(false);
const error = ref('');
const success = ref(false);

onMounted(() => {
  token.value = (route.query.token as string) || '';
  email.value = (route.query.email as string) || '';
});

const resetPassword = async () => {
  loading.value = true;
  error.value = '';

  if (password.value !== passwordConfirmation.value) {
    error.value = 'Passwords do not match';
    loading.value = false;
    return;
  }

  try {
    const res = await fetch(`${API_URL}/reset-password`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({
        token: token.value,
        email: email.value,
        password: password.value,
        password_confirmation: passwordConfirmation.value,
      }),
    });
    const data = await res.json();
    if (res.ok && data.success) {
      success.value = true;
    } else {
      error.value = data.message || 'Failed to reset password';
    }
  } catch (e: any) {
    error.value = 'Network error. Please try again.';
  } finally {
    loading.value = false;
  }
};
</script>
