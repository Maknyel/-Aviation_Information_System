<template>
  <Modal v-model="isOpen" title="Work Order Request">
    <form @submit.prevent="submitForm" class="space-y-4">
      <!-- Location and Room Number -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
          <input
            v-model="formData.location"
            type="text"
            placeholder="Building D"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Room No. / Area</label>
          <input
            v-model="formData.room_number"
            type="text"
            placeholder="Room 1038"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent"
          />
        </div>
      </div>

      <!-- Date and Time -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
          <input
            v-model="formData.date"
            type="date"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Time</label>
          <input
            v-model="formData.time"
            type="time"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent"
          />
        </div>
      </div>

      <!-- Details of Request -->
      <div>
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Details of Request</h3>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Description of Problem</label>
          <textarea
            v-model="formData.description_of_problem"
            placeholder="Broken Fan"
            rows="4"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent"
          ></textarea>
        </div>

        <!-- Image Upload -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Upload Image (Optional)</label>
          <div class="flex items-start gap-4">
            <div class="flex-1">
              <input
                type="file"
                @change="handleImageChange"
                accept="image/*"
                ref="imageInput"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent text-sm"
              />
              <p class="text-xs text-gray-500 mt-1">Max size: 2MB (JPEG, JPG, PNG, GIF)</p>
            </div>
            <div v-if="imagePreview" class="relative">
              <img :src="imagePreview" alt="Preview" class="w-20 h-20 object-cover rounded-lg border border-gray-300">
              <button
                type="button"
                @click="removeImage"
                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Requisitioner and Status -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Requisitioner</label>
          <input
            v-model="formData.requisitioner"
            type="text"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
          <select
            v-model="formData.priority"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent"
          >
            <option value="urgent">Urgent</option>
            <option value="high">High Priority</option>
            <option value="medium">Medium Priority</option>
            <option value="low">Low Priority</option>
          </select>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-sm text-red-600">{{ error }}</p>
      </div>

      <!-- Success Message -->
      <div v-if="success" class="p-4 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-sm text-green-600">{{ success }}</p>
      </div>
    </form>

    <template #footer>
      <div class="flex justify-end gap-3">
        <button
          type="button"
          @click="closeModal"
          class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
        >
          Cancel
        </button>
        <button
          type="button"
          @click="submitForm"
          :disabled="loading"
          class="px-6 py-2 bg-aviation-olive text-white rounded-lg hover:bg-opacity-90 transition-colors disabled:opacity-50"
        >
          {{ loading ? 'Submitting...' : 'Submit' }}
        </button>
      </div>
    </template>
  </Modal>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import Modal from './Modal.vue';
import { API_URL } from '@/config/api';

interface Props {
  modelValue: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits(['update:modelValue', 'success']);

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const formData = ref({
  location: '',
  room_number: '',
  date: '',
  time: '',
  description_of_problem: '',
  requisitioner: '',
  priority: 'medium'
});

const imageFile = ref<File | null>(null);
const imagePreview = ref<string>('');
const imageInput = ref<HTMLInputElement | null>(null);

const loading = ref(false);
const error = ref('');
const success = ref('');

const handleImageChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];

  if (file) {
    // Validate file size (2MB max)
    if (file.size > 2 * 1024 * 1024) {
      error.value = 'Image size must be less than 2MB';
      if (imageInput.value) imageInput.value.value = '';
      return;
    }

    imageFile.value = file;

    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);
  }
};

const removeImage = () => {
  imageFile.value = null;
  imagePreview.value = '';
  if (imageInput.value) imageInput.value.value = '';
};

const resetForm = () => {
  formData.value = {
    location: '',
    room_number: '',
    date: '',
    time: '',
    description_of_problem: '',
    requisitioner: '',
    priority: 'medium'
  };
  removeImage();
  error.value = '';
  success.value = '';
};

const closeModal = () => {
  resetForm();
  isOpen.value = false;
};

const submitForm = async () => {
  loading.value = true;
  error.value = '';
  success.value = '';

  try {
    const token = localStorage.getItem('token');

    // Create FormData for file upload
    const formDataToSend = new FormData();
    formDataToSend.append('location', formData.value.location);
    formDataToSend.append('room_number', formData.value.room_number);
    formDataToSend.append('date', formData.value.date);
    formDataToSend.append('time', formData.value.time);
    formDataToSend.append('description_of_problem', formData.value.description_of_problem);
    formDataToSend.append('requisitioner', formData.value.requisitioner);
    formDataToSend.append('priority', formData.value.priority);

    if (imageFile.value) {
      formDataToSend.append('image', imageFile.value);
    }

    const response = await fetch(`${API_URL}/work-orders`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: formDataToSend
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to submit work order');
    }

    success.value = 'Work order submitted successfully!';
    emit('success', data.data);

    // Close modal after a short delay
    setTimeout(() => {
      closeModal();
    }, 1500);
  } catch (err: any) {
    error.value = err.message || 'An error occurred while submitting the work order';
  } finally {
    loading.value = false;
  }
};
</script>
