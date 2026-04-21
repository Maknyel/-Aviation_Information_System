<template>
  <div v-if="modelValue" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
      <div class="p-6 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800">Upload Form Template</h2>
        <button @click="emit('update:modelValue', false)" class="p-2 hover:bg-gray-100 rounded-lg">
          <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <form @submit.prevent="upload" class="p-6 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Template Name *</label>
          <input v-model="form.name" required type="text" placeholder="e.g. Facility Request Form 2026" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-aviation-olive" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Form Type *</label>
          <select v-model="form.form_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none">
            <option value="facility_request">Facility Request</option>
            <option value="work_order">Work Order</option>
            <option value="general">General</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea v-model="form.description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none resize-none" placeholder="Optional description..."></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">File *</label>
          <div
            class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-aviation-olive transition-colors"
            @click="fileInput?.click()"
          >
            <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
            </svg>
            <p class="text-sm text-gray-500">{{ selectedFileName || 'Click to select PDF, DOC, or DOCX (max 10MB)' }}</p>
          </div>
          <input ref="fileInput" type="file" accept=".pdf,.doc,.docx" class="hidden" @change="handleFileChange" />
        </div>

        <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>
        <div v-if="success" class="text-green-600 text-sm">Template uploaded successfully!</div>

        <div class="flex gap-3 justify-end pt-2">
          <button type="button" @click="emit('update:modelValue', false)" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Cancel</button>
          <button type="submit" :disabled="uploading || !selectedFile" class="px-4 py-2 bg-aviation-olive text-white rounded-lg text-sm hover:bg-opacity-90 disabled:opacity-50">
            {{ uploading ? 'Uploading...' : 'Upload' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { API_URL } from '@/config/api';

const props = defineProps<{ modelValue: boolean }>();
const emit = defineEmits(['update:modelValue', 'uploaded']);

const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);
const selectedFileName = ref('');
const uploading = ref(false);
const error = ref('');
const success = ref(false);

const form = ref({
  name: '',
  form_type: 'facility_request',
  description: '',
});

const handleFileChange = (e: Event) => {
  const input = e.target as HTMLInputElement;
  if (input.files && input.files[0]) {
    selectedFile.value = input.files[0];
    selectedFileName.value = input.files[0].name;
  }
};

const upload = async () => {
  if (!selectedFile.value) return;
  uploading.value = true;
  error.value = '';
  success.value = false;

  try {
    const formData = new FormData();
    formData.append('name', form.value.name);
    formData.append('form_type', form.value.form_type);
    formData.append('description', form.value.description);
    formData.append('file', selectedFile.value);

    const res = await fetch(`${API_URL}/form-templates`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
        Accept: 'application/json',
      },
      body: formData,
    });

    const data = await res.json();
    if (data.success) {
      success.value = true;
      emit('uploaded', data.data);
      setTimeout(() => {
        emit('update:modelValue', false);
        resetForm();
      }, 1000);
    } else {
      error.value = data.message || 'Upload failed';
    }
  } catch (e) {
    error.value = 'An error occurred during upload';
  } finally {
    uploading.value = false;
  }
};

const resetForm = () => {
  form.value = { name: '', form_type: 'facility_request', description: '' };
  selectedFile.value = null;
  selectedFileName.value = '';
  error.value = '';
  success.value = false;
  if (fileInput.value) fileInput.value.value = '';
};

watch(() => props.modelValue, (val) => {
  if (!val) resetForm();
});
</script>
