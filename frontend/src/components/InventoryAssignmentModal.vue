<template>
  <div v-if="modelValue" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] flex flex-col">
      <div class="p-6 border-b border-gray-200 flex items-center justify-between shrink-0">
        <div>
          <h2 class="text-xl font-semibold text-gray-800">Item Locations</h2>
          <p class="text-sm text-gray-500 mt-1" v-if="item">
            {{ item.name }} — <span class="text-green-600 font-medium">{{ item.available_quantity }} available</span> / {{ item.total_quantity }} total
          </p>
        </div>
        <button @click="emit('update:modelValue', false)" class="p-2 hover:bg-gray-100 rounded-lg">
          <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <div class="overflow-y-auto flex-1 p-6">
        <div v-if="loadingAssignments" class="text-center py-8 text-gray-500 text-sm">Loading...</div>

        <template v-else>
          <!-- Facility Request Deployments -->
          <div class="mb-2 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700">Scheduled in Facility Requests</h3>
            <span class="text-xs text-gray-400">Upcoming pending/approved only</span>
          </div>

          <div v-if="facilityDeployments.length === 0" class="text-center py-6 bg-gray-50 rounded-lg text-gray-400 text-sm">
            No upcoming facility requests using this item
          </div>
          <div v-else class="space-y-2">
            <div
              v-for="dep in facilityDeployments"
              :key="dep.id"
              class="flex items-start justify-between p-4 bg-green-50 rounded-lg border border-green-100"
            >
              <div class="flex-1 min-w-0">
                <div class="font-medium text-gray-800 text-sm">{{ dep.location }}</div>
                <div class="text-xs text-gray-600 mt-0.5 truncate">{{ dep.title }}</div>
                <div class="text-xs text-gray-500 mt-1">
                  <span class="font-medium">{{ dep.quantity }} {{ item?.unit || 'pcs' }}</span>
                  &nbsp;·&nbsp;{{ dep.date }} at {{ dep.time }}
                  &nbsp;·&nbsp;by {{ dep.requested_by }}
                </div>
              </div>
              <span :class="['text-xs px-2 py-1 rounded-full font-medium shrink-0 ml-3 mt-0.5',
                dep.status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700']">
                {{ dep.status }}
              </span>
            </div>
          </div>

          <!-- Summary bar -->
          <div v-if="facilityDeployments.length > 0" class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-100 text-xs text-blue-700">
            Total scheduled: <span class="font-semibold">{{ totalScheduled }} {{ item?.unit || 'pcs' }}</span>
            of {{ item?.total_quantity }} — <span class="font-semibold">{{ item?.available_quantity }}</span> still available
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { API_URL } from '@/config/api';

const props = defineProps<{
  modelValue: boolean;
  item?: any;
}>();

const emit = defineEmits(['update:modelValue']);

const facilityDeployments = ref<any[]>([]);
const loadingAssignments = ref(false);

const totalScheduled = computed(() =>
  facilityDeployments.value.reduce((sum, d) => sum + (d.quantity || 0), 0)
);

const authHeaders = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`,
  Accept: 'application/json',
});

const fetchAssignments = async () => {
  if (!props.item?.id) return;
  loadingAssignments.value = true;
  try {
    const res = await fetch(`${API_URL}/inventory/${props.item.id}/assignments`, { headers: authHeaders() });
    const data = await res.json();
    if (data.success) {
      facilityDeployments.value = data.facility_deployments ?? [];
    }
  } catch (e) {
    console.error(e);
  } finally {
    loadingAssignments.value = false;
  }
};

watch(() => [props.modelValue, props.item], ([open]) => {
  if (open && props.item) {
    fetchAssignments();
  }
});
</script>
