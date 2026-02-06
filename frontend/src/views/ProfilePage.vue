<template>
  <AppLayout>
    <div class="max-w-4xl mx-auto">
          <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Profile Settings</h1>
            <p class="text-gray-600">Manage your account information</p>
          </div>

          <!-- Profile Form -->
          <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-100">
            <!-- Profile Picture Section -->
            <div class="mb-8 pb-6 border-b border-gray-200">
              <h2 class="text-xl font-semibold text-gray-800 mb-4">Profile Picture</h2>
              <div class="flex items-center gap-6">
                <div class="w-24 h-24 bg-aviation-olive rounded-full flex items-center justify-center text-white text-3xl font-bold overflow-hidden">
                  <img v-if="profilePicturePreview || user?.profile_picture" :src="profilePicturePreview || getProfilePictureUrl(user?.profile_picture)" alt="Profile" class="w-full h-full object-cover" />
                  <span v-else>{{ user?.name?.charAt(0) || 'U' }}</span>
                </div>
                <div>
                  <input type="file" ref="fileInput" @change="handleFileChange" accept="image/*" class="hidden" />
                  <button @click="$refs.fileInput.click()" class="px-4 py-2 bg-aviation-olive text-white rounded-lg hover:bg-opacity-90 transition-all">
                    Change Picture
                  </button>
                  <p class="text-sm text-gray-500 mt-2">JPG, PNG or GIF (Max 2MB)</p>
                </div>
              </div>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-6">
              <!-- Name Field -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                <input
                  v-model="formData.name"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent outline-none transition-all bg-white text-black"
                  placeholder="Enter your full name"
                />
              </div>

              <!-- Email Field -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                <input
                  v-model="formData.email"
                  type="email"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent outline-none transition-all bg-white text-black"
                  placeholder="Enter your email"
                />
              </div>

              <!-- Change Password Section -->
              <div class="pt-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Change Password</h3>

                <div class="space-y-4">
                  <!-- Current Password -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                    <input
                      v-model="formData.current_password"
                      type="password"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent outline-none transition-all bg-white text-black"
                      placeholder="Enter current password"
                    />
                  </div>

                  <!-- New Password -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                    <input
                      v-model="formData.password"
                      type="password"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent outline-none transition-all bg-white text-black"
                      placeholder="Enter new password (min 6 characters)"
                    />
                  </div>

                  <!-- Confirm Password -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                    <input
                      v-model="formData.password_confirmation"
                      type="password"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent outline-none transition-all bg-white text-black"
                      placeholder="Confirm new password"
                    />
                  </div>
                </div>
              </div>

              <!-- Success Message -->
              <div v-if="successMessage" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                {{ successMessage }}
              </div>

              <!-- Error Message -->
              <div v-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                {{ errorMessage }}
              </div>

              <!-- Submit Button -->
              <div class="flex justify-end gap-3 pt-4">
                <button
                  type="button"
                  @click="resetForm"
                  class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="loading"
                  class="px-6 py-3 bg-aviation-olive text-white rounded-lg hover:bg-opacity-90 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ loading ? 'Saving...' : 'Save Changes' }}
                </button>
              </div>
            </form>
          </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AppLayout from '@/components/AppLayout.vue';
import axios from 'axios';
import { API_URL } from '@/config/api';

const user = ref<any>(null);
const loading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const profilePicturePreview = ref('');
const selectedFile = ref<File | null>(null);

const formData = ref({
  name: '',
  email: '',
  current_password: '',
  password: '',
  password_confirmation: '',
});

const getProfilePictureUrl = (path: string) => {
  if (!path) return '';
  return `http://localhost:8000/storage/${path}`;
};

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];

  if (file) {
    selectedFile.value = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      profilePicturePreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);
  }
};

const resetForm = () => {
  if (user.value) {
    formData.value.name = user.value.name;
    formData.value.email = user.value.email;
  }
  formData.value.current_password = '';
  formData.value.password = '';
  formData.value.password_confirmation = '';
  profilePicturePreview.value = '';
  selectedFile.value = null;
  successMessage.value = '';
  errorMessage.value = '';
};

const handleSubmit = async () => {
  loading.value = true;
  successMessage.value = '';
  errorMessage.value = '';

  try {
    const token = localStorage.getItem('token');
    const formDataToSend = new FormData();

    if (formData.value.name !== user.value.name) {
      formDataToSend.append('name', formData.value.name);
    }

    if (formData.value.email !== user.value.email) {
      formDataToSend.append('email', formData.value.email);
    }

    if (formData.value.password) {
      formDataToSend.append('current_password', formData.value.current_password);
      formDataToSend.append('password', formData.value.password);
      formDataToSend.append('password_confirmation', formData.value.password_confirmation);
    }

    if (selectedFile.value) {
      formDataToSend.append('profile_picture', selectedFile.value);
    }

    const response = await axios.post(`${API_URL}/profile/update`, formDataToSend, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'multipart/form-data',
      },
    });

    if (response.data.user) {
      user.value = response.data.user;
      localStorage.setItem('user', JSON.stringify(response.data.user));
      successMessage.value = 'Profile updated successfully!';

      // Clear password fields
      formData.value.current_password = '';
      formData.value.password = '';
      formData.value.password_confirmation = '';
      selectedFile.value = null;
      profilePicturePreview.value = '';
    }
  } catch (err: any) {
    if (err.response?.data?.errors) {
      const errors = err.response.data.errors;
      errorMessage.value = Object.values(errors).flat().join(', ');
    } else if (err.response?.data?.message) {
      errorMessage.value = err.response.data.message;
    } else {
      errorMessage.value = 'Failed to update profile. Please try again.';
    }
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  const userStr = localStorage.getItem('user');
  if (userStr) {
    user.value = JSON.parse(userStr);
    formData.value.name = user.value.name;
    formData.value.email = user.value.email;
  }
});
</script>
