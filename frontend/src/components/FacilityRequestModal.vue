<template>
  <Modal v-model="isOpen" title="Facility Request">
    <form @submit.prevent="submitForm" class="space-y-4">
      <!-- Venue + Location -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-aviation-olive mb-2">Venue Requested</label>
          <select v-model="formData.venue_requested" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive bg-white text-black">
            <option value="">Select Venue</option>
            <option>Multi Purpose Hall</option>
            <option>Covered Court</option>
            <option>Building A</option>
            <option>Building B</option>
            <option>Hangar</option>
            <option>Others</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-aviation-olive mb-2">Location/Room Number</label>
          <input v-model="formData.location_room_number" type="text" placeholder="Specify location/room for Bldg A, B, Hangar" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive bg-white text-black text-sm" />
        </div>
      </div>

      <!-- Title of Event -->
      <div>
        <label class="block text-sm font-medium text-aviation-olive mb-2">Title of Event</label>
        <input v-model="formData.title_of_event" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive bg-white text-black" />
      </div>

      <!-- Time + Date -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-aviation-olive mb-2">Time of Event</label>
          <input v-model="formData.time_of_event" type="time" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive bg-white text-black" />
        </div>
        <div>
          <label class="block text-sm font-medium text-aviation-olive mb-2">Date of Event</label>
          <input v-model="formData.date_of_event" type="date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive bg-white text-black" />
        </div>
      </div>

      <!-- Details of Request — Inventory Items -->
      <div>
        <div class="flex items-center justify-between mb-2">
          <h3 class="text-sm font-semibold text-aviation-olive">Details of Request</h3>
          <span v-if="loadingAvailability" class="text-xs text-gray-400">Checking availability...</span>
          <span v-else-if="formData.date_of_event" class="text-xs text-gray-400">Availability for {{ formData.date_of_event }}</span>
        </div>

        <div v-if="loadingItems" class="text-center py-4 text-gray-500 text-sm">Loading items...</div>

        <div v-else class="grid grid-cols-2 gap-3">
          <div
            v-for="item in inventoryItems"
            :key="item.id"
            :class="['flex flex-col gap-1 p-2 rounded-lg border', isSelected(item.id) ? 'border-aviation-olive bg-olive-50' : 'border-gray-200']"
          >
            <label :class="['flex items-center gap-2', isUnavailable(item.id) ? 'cursor-not-allowed opacity-50' : 'cursor-pointer']">
              <input
                type="checkbox"
                :checked="isSelected(item.id)"
                :disabled="isUnavailable(item.id)"
                @change="toggleItem(item.id)"
                class="w-4 h-4 bg-white border-gray-300 rounded focus:ring-aviation-olive disabled:cursor-not-allowed"
              />
              <span class="text-sm text-aviation-olive font-medium">{{ item.name }}</span>
              <span :class="['text-xs ml-auto', isUnavailable(item.id) ? 'text-red-500' : 'text-gray-400']">
                <template v-if="loadingAvailability && formData.date_of_event">Checking...</template>
                <template v-else-if="isUnavailable(item.id)">Not available</template>
                <template v-else-if="availability[item.id]">{{ availability[item.id].available }} avail</template>
                <template v-else>{{ item.total_quantity }} avail</template>
              </span>
            </label>

            <div v-if="isSelected(item.id)" class="flex items-center gap-2 ml-6">
              <input
                type="number"
                min="1"
                :max="getMax(item.id)"
                :value="getQty(item.id)"
                @input="setQty(item.id, $event)"
                :placeholder="`1–${getMax(item.id)}`"
                class="w-24 px-2 py-1 text-xs border border-gray-300 rounded focus:ring-aviation-olive bg-white text-black"
              />
              <span class="text-xs text-gray-400">{{ item.unit }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Attachment -->
      <div>
        <label class="block text-sm font-medium text-aviation-olive mb-2">Attachment <span class="text-gray-400 font-normal">(optional — PDF, Word, Image, max 10 MB)</span></label>
        <input
          type="file"
          accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
          @change="handleFile"
          class="block w-full text-sm text-gray-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-aviation-olive file:text-white hover:file:bg-opacity-90 cursor-pointer"
        />
        <p v-if="attachmentFile" class="text-xs text-gray-500 mt-1">Selected: {{ attachmentFile.name }}</p>
      </div>

      <!-- Error -->
      <div v-if="error" class="p-3 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-sm text-red-600">{{ error }}</p>
      </div>

      <!-- Success -->
      <div v-if="success" class="p-3 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-sm text-green-600">{{ success }}</p>
      </div>
    </form>

    <template #footer>
      <div class="flex justify-center gap-8">
        <button type="button" @click="closeModal" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-500 transition-colors bg-gray-400">
          Cancel
        </button>
        <button type="button" @click="submitForm" :disabled="loading" class="px-6 py-2 bg-aviation-olive text-white rounded-lg hover:bg-opacity-90 transition-colors disabled:opacity-50">
          {{ loading ? 'Submitting...' : 'Submit Request' }}
        </button>
      </div>
    </template>
  </Modal>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import Modal from './Modal.vue';
import { API_URL } from '@/config/api';

interface InventoryItem {
  id: number;
  name: string;
  unit: string;
  total_quantity: number;
}

interface AvailabilityInfo {
  item_id: number;
  item_name: string;
  total: number;
  in_use: number;
  available: number;
}

const props = defineProps<{ modelValue: boolean }>();
const emit = defineEmits(['update:modelValue', 'success']);

const isOpen = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
});

const formData = ref({
  venue_requested: '',
  location_room_number: '',
  title_of_event: '',
  time_of_event: '',
  date_of_event: '',
});

// { inventory_item_id: quantity }
const selectedItems = ref<Record<number, number>>({});

const inventoryItems = ref<InventoryItem[]>([]);
const availability = ref<Record<number, AvailabilityInfo>>({});
const loadingItems = ref(false);
const loadingAvailability = ref(false);
const loading = ref(false);
const error = ref('');
const success = ref('');
const attachmentFile = ref<File | null>(null);

const handleFile = (e: Event) => {
  attachmentFile.value = (e.target as HTMLInputElement).files?.[0] ?? null;
};

const authHeaders = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`,
  Accept: 'application/json',
});

const fetchItems = async () => {
  loadingItems.value = true;
  try {
    const res = await fetch(`${API_URL}/inventory`, { headers: authHeaders() });
    const data = await res.json();
    if (data.success) inventoryItems.value = data.data;
  } catch {
    // silently fail
  } finally {
    loadingItems.value = false;
  }
};

const fetchAvailability = async (date: string) => {
  if (!date) { availability.value = {}; return; }
  loadingAvailability.value = true;
  try {
    const res = await fetch(`${API_URL}/inventory/facility-availability?date=${date}`, { headers: authHeaders() });
    const data = await res.json();
    if (data.success) availability.value = data.data;
  } catch {
    // silently fail
  } finally {
    loadingAvailability.value = false;
  }
};

watch(() => formData.value.date_of_event, async (date) => {
  await fetchAvailability(date);
  for (const id in selectedItems.value) {
    const numId = Number(id);
    if (availability.value[numId] && availability.value[numId].available <= 0) {
      delete selectedItems.value[numId];
    }
  }
});

const isSelected = (id: number) => id in selectedItems.value;

const isUnavailable = (id: number) => {
  if (!formData.value.date_of_event) return false;
  const info = availability.value[id];
  return info !== undefined && info.available <= 0;
};

const getMax = (id: number): number => {
  const info = availability.value[id];
  if (info) return info.available;
  const item = inventoryItems.value.find(i => i.id === id);
  return item?.total_quantity ?? 9999;
};

const getQty = (id: number) => selectedItems.value[id] ?? 1;

const setQty = (id: number, e: Event) => {
  let val = parseInt((e.target as HTMLInputElement).value) || 1;
  const max = getMax(id);
  if (val > max) val = max;
  if (val < 1) val = 1;
  selectedItems.value[id] = val;
  (e.target as HTMLInputElement).value = String(val);
};

const toggleItem = (id: number) => {
  if (id in selectedItems.value) {
    delete selectedItems.value[id];
  } else {
    selectedItems.value[id] = 1;
  }
};

const resetForm = () => {
  formData.value = { venue_requested: '', location_room_number: '', title_of_event: '', time_of_event: '', date_of_event: '' };
  selectedItems.value = {};
  availability.value = {};
  attachmentFile.value = null;
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
    const fd = new FormData();
    Object.entries(formData.value).forEach(([k, v]) => { if (v) fd.append(k, v); });

    Object.entries(selectedItems.value).forEach(([id, qty], i) => {
      fd.append(`items[${i}][inventory_item_id]`, id);
      fd.append(`items[${i}][quantity]`, String(qty));
    });

    if (attachmentFile.value) fd.append('attachment', attachmentFile.value);

    const token = localStorage.getItem('token');
    const response = await fetch(`${API_URL}/facility-requests`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      body: fd,
    });

    const data = await response.json();
    if (!response.ok) throw new Error(data.message || 'Failed to submit facility request');

    success.value = 'Facility request submitted successfully!';
    emit('success', data.data);
    setTimeout(() => closeModal(), 1500);
  } catch (err: any) {
    error.value = err.message || 'An error occurred';
  } finally {
    loading.value = false;
  }
};

onMounted(fetchItems);
</script>
