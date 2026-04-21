<template>
  <div v-if="modelValue" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
      <div class="p-6 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800">{{ isEditing ? 'Edit Item' : 'Add Inventory Item' }}</h2>
        <button @click="emit('update:modelValue', false)" class="p-2 hover:bg-gray-100 rounded-lg">
          <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <form @submit.prevent="save" class="p-6 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Item Name *</label>
          <input v-model="form.name" required type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-aviation-olive" placeholder="e.g. Monobloc Chair" />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
            <select v-model="form.category" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none">
              <option value="furniture">Furniture</option>
              <option value="av_equipment">AV Equipment</option>
              <option value="utility">Utility</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Condition</label>
            <select v-model="form.condition" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none">
              <option value="good">Good</option>
              <option value="fair">Fair</option>
              <option value="poor">Poor</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Total Quantity *</label>
            <input v-model.number="form.total_quantity" required type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-aviation-olive" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
            <input v-model="form.unit" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none" placeholder="pcs, sets..." />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
          <textarea v-model="form.notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none resize-none" placeholder="Optional notes..."></textarea>
        </div>

        <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>

        <div class="flex gap-3 justify-end pt-2">
          <button type="button" @click="emit('update:modelValue', false)" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Cancel</button>
          <button type="submit" :disabled="saving" class="px-4 py-2 bg-aviation-olive text-white rounded-lg text-sm hover:bg-opacity-90 disabled:opacity-50">
            {{ saving ? 'Saving...' : (isEditing ? 'Update Item' : 'Add Item') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { API_URL } from '@/config/api';

const props = defineProps<{
  modelValue: boolean;
  item?: any;
}>();

const emit = defineEmits(['update:modelValue', 'saved']);

const isEditing = computed(() => !!props.item?.id);

const defaultForm = () => ({
  name: '',
  category: 'furniture',
  total_quantity: 0,
  unit: 'pcs',
  condition: 'good',
  notes: '',
});

const form = ref(defaultForm());
const saving = ref(false);
const error = ref('');

watch(() => props.item, (val) => {
  if (val) {
    form.value = { ...defaultForm(), ...val };
  } else {
    form.value = defaultForm();
  }
}, { immediate: true });

const save = async () => {
  saving.value = true;
  error.value = '';
  try {
    const method = isEditing.value ? 'PUT' : 'POST';
    const url = isEditing.value ? `${API_URL}/inventory/${props.item.id}` : `${API_URL}/inventory`;

    const res = await fetch(url, {
      method,
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(form.value),
    });

    const data = await res.json();
    if (data.success) {
      emit('saved', data.data);
      emit('update:modelValue', false);
    } else {
      error.value = data.message || 'Failed to save item';
    }
  } catch (e) {
    error.value = 'An error occurred';
  } finally {
    saving.value = false;
  }
};
</script>
