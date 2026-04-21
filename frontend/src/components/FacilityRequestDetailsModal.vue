<template>
  <Modal v-model="isOpen" title="Details">
    <div class="space-y-3">
      <!-- Requestor -->
      <div class="flex gap-2">
        <label class="text-sm font-semibold text-gray-900 min-w-[140px]">Requestor:</label>
        <p class="text-sm text-aviation-olive font-medium">{{ request?.user?.name || 'N/A' }}</p>
      </div>

      <!-- Venue Requested -->
      <div class="flex gap-2">
        <label class="text-sm font-semibold text-gray-900 min-w-[140px]">Venue Requested:</label>
        <p class="text-sm text-aviation-olive font-medium">{{ request?.venue_requested || 'N/A' }}</p>
      </div>

      <!-- Title of Event -->
      <div class="flex gap-2">
        <label class="text-sm font-semibold text-gray-900 min-w-[140px]">Title of Event:</label>
        <p class="text-sm text-aviation-olive font-medium">{{ request?.title_of_event || 'N/A' }}</p>
      </div>

      <!-- Date and Time of Event -->
      <div class="flex gap-2">
        <label class="text-sm font-semibold text-gray-900 min-w-[140px]">Date and Time of Event:</label>
        <p class="text-sm text-aviation-olive font-medium">{{ formatDateTime(request?.date_of_event, request?.time_of_event) }}</p>
      </div>

      <!-- Attachment -->
      <div v-if="request?.attachment_path" class="flex gap-2 items-start">
        <label class="text-sm font-semibold text-gray-900 min-w-[140px]">Attachment:</label>
        <div>
          <img
            v-if="isImage"
            :src="attachmentUrl"
            class="max-h-48 rounded-lg border border-gray-200 object-contain cursor-pointer"
            @click="openAttachment"
          />
          <a
            v-else
            :href="attachmentUrl"
            target="_blank"
            class="inline-flex items-center gap-1.5 text-sm text-aviation-olive underline hover:opacity-80"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
            </svg>
            {{ attachmentFileName }}
          </a>
        </div>
      </div>

      <!-- Equipment Needed and Quantity -->
      <div class="mt-4">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Equipment Needed and Quantity:</h3>
        <div v-if="!request?.request_items?.length" class="text-sm text-gray-400 italic ml-4">No items requested.</div>
        <div v-else class="grid grid-cols-2 gap-2 ml-4">
          <div
            v-for="ri in request.request_items"
            :key="ri.id"
            class="flex items-center justify-between px-3 py-2 bg-gray-50 rounded-lg border border-gray-100"
          >
            <span class="text-sm text-gray-700">{{ ri.inventory_item?.name }}</span>
            <span class="text-sm font-semibold text-aviation-olive">{{ ri.quantity }} {{ ri.inventory_item?.unit }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Inventory Availability Check -->
    <div v-if="canApprove && inventoryData" class="mt-4 border-t border-gray-200 pt-4">
      <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-semibold text-gray-800">Inventory Availability</h4>
        <span v-if="loadingInventory" class="text-xs text-gray-400">Loading...</span>
      </div>

      <div v-if="!loadingInventory">
        <!-- No requested items tracked -->
        <div v-if="inventoryData.inventory.length === 0" class="text-xs text-gray-400 italic">No equipment items from this request are tracked in inventory.</div>

        <!-- Inventory status table -->
        <div v-else class="overflow-x-auto">
          <table class="w-full text-xs">
            <thead>
              <tr class="border-b border-gray-100">
                <th class="text-left py-1 pr-4 font-medium text-gray-500">Item Requested</th>
                <th class="text-center py-1 px-3 font-medium text-gray-500">Total</th>
                <th class="text-center py-1 px-3 font-medium text-gray-500">In Use</th>
                <th class="text-center py-1 px-3 font-medium text-gray-500">Available</th>
                <th class="text-left py-1 pl-3 font-medium text-gray-500">Other Events Using This</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="inv in inventoryData.inventory" :key="inv.item_id" class="border-b border-gray-50">
                <td class="py-1.5 pr-4 font-medium text-gray-700">{{ inv.label }}</td>
                <template>
                  <td class="py-1.5 px-3 text-center text-gray-600">{{ inv.total }}</td>
                  <td class="py-1.5 px-3 text-center font-medium" :class="inv.in_use > 0 ? 'text-blue-600' : 'text-gray-400'">{{ inv.in_use }}</td>
                  <td class="py-1.5 px-3 text-center font-semibold" :class="inv.available > 0 ? 'text-green-600' : 'text-red-500'">
                    {{ inv.available }} {{ inv.available > 0 ? '✓' : '✗' }}
                  </td>
                  <td class="py-1.5 pl-3 text-gray-500">
                    <span v-if="inv.other_events?.length > 0">
                      {{ inv.other_events.map((e: any) => e.title).join(', ') }}
                    </span>
                    <span v-else class="italic text-gray-400">None</span>
                  </td>
                </template>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Other bookings same venue/date -->
        <div v-if="inventoryData.other_bookings_same_day?.length > 0" class="mt-3 p-2 bg-amber-50 rounded-lg">
          <p class="text-xs font-medium text-amber-700 mb-1">Other bookings at this venue on the same date:</p>
          <div v-for="booking in inventoryData.other_bookings_same_day" :key="booking.id" class="text-xs text-gray-600 ml-2">
            • {{ booking.title_of_event }} at {{ booking.time_of_event }} ({{ booking.user?.name }})
          </div>
        </div>
      </div>
    </div>

    <!-- Approval Timeline -->
    <div v-if="request?.approval_steps?.length" class="mt-4 border-t border-gray-200 pt-4">
      <h4 class="text-sm font-semibold text-gray-800 mb-2">Approval Timeline</h4>
      <div class="space-y-2">
        <div v-for="step in request.approval_steps" :key="step.id" class="flex items-center gap-3 text-sm">
          <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs"
            :class="{ 'bg-green-500': step.status === 'approved', 'bg-red-500': step.status === 'rejected', 'bg-gray-300': step.status === 'pending' }">
            {{ step.step_order }}
          </div>
          <div>
            <span class="font-medium">{{ step.approver_role }}</span>
            <span v-if="step.approver" class="text-gray-500"> - {{ step.approver.name }}</span>
            <span class="ml-2 px-2 py-0.5 text-xs rounded-full" :class="{ 'bg-green-100 text-green-700': step.status === 'approved', 'bg-red-100 text-red-700': step.status === 'rejected', 'bg-gray-100 text-gray-600': step.status === 'pending' }">
              {{ step.status }}
            </span>
            <span v-if="step.acted_at" class="text-xs text-gray-400 ml-2">{{ new Date(step.acted_at).toLocaleString() }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Feedback -->
    <FeedbackForm v-if="request?.id" :request-type="'facility_request'" :request-id="request.id" :request-status="request?.status" />

    <template #footer>
      <div class="flex flex-wrap justify-center gap-3">
        <button
          v-if="canApprove"
          type="button"
          @click="saveRequest"
          :disabled="saving"
          class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors disabled:opacity-50 text-sm"
        >
          {{ saving ? 'Saving...' : (saved ? 'Saved' : 'Save') }}
        </button>
        <button
          v-if="canApprove"
          type="button"
          @click="printRequest"
          :disabled="printing"
          class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors disabled:opacity-50 text-sm"
        >
          {{ printing ? '...' : 'Print' }}
        </button>
        <button
          v-if="canApprove && request?.status === 'pending'"
          type="button"
          @click="updateStatus('approved')"
          :disabled="updating"
          class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50"
        >
          {{ updating ? 'Updating...' : 'Approved' }}
        </button>
        <button
          v-if="canApprove && request?.status === 'pending'"
          type="button"
          @click="updateStatus('rejected')"
          :disabled="updating"
          class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50"
        >
          {{ updating ? 'Updating...' : 'Disapproved' }}
        </button>
        <button
          v-if="!canApprove || request?.status !== 'pending'"
          type="button"
          @click="close"
          class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
        >
          Close
        </button>
      </div>
    </template>
  </Modal>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import Modal from './Modal.vue';
import FeedbackForm from './FeedbackForm.vue';
import { API_URL } from '@/config/api';

interface Props {
  modelValue: boolean;
  request: any;
}

const props = defineProps<Props>();
const emit = defineEmits(['update:modelValue', 'statusUpdated']);

const STORAGE_URL = API_URL.replace('/api', '/storage');

const attachmentUrl = computed(() =>
  props.request?.attachment_path ? `${STORAGE_URL}/${props.request.attachment_path}` : ''
);

const attachmentFileName = computed(() =>
  props.request?.attachment_path?.split('/').pop() ?? ''
);

const isImage = computed(() => /\.(jpg|jpeg|png|gif|webp)$/i.test(attachmentFileName.value));

const openAttachment = () => window.open(attachmentUrl.value, '_blank');

const updating = ref(false);
const saving = ref(false);
const saved = ref(false);
const printing = ref(false);
const inventoryData = ref<any>(null);
const loadingInventory = ref(false);

const fetchInventoryCheck = async (requestId: number) => {
  loadingInventory.value = true;
  try {
    const res = await fetch(`${API_URL}/facility-requests/${requestId}/inventory-check`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
        Accept: 'application/json',
      },
    });
    const data = await res.json();
    if (data.success) inventoryData.value = data.data;
  } catch (e) {
    console.error(e);
  } finally {
    loadingInventory.value = false;
  }
};

watch(() => props.request, (req) => {
  inventoryData.value = null;
  saved.value = false;
  if (req?.id && canApprove.value) {
    fetchInventoryCheck(req.id);
  }
}, { immediate: true });

const authHeaders = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`,
  Accept: 'application/json',
  'Content-Type': 'application/json',
});

const saveRequest = async () => {
  if (!props.request) return;
  saving.value = true;
  try {
    const res = await fetch(`${API_URL}/form-templates/save-request`, {
      method: 'POST',
      headers: authHeaders(),
      body: JSON.stringify({ request_type: 'facility_request', request_id: props.request.id }),
    });
    const data = await res.json();
    if (data.success) saved.value = true;
  } catch (e) {
    console.error(e);
  } finally {
    saving.value = false;
  }
};

const printRequest = async () => {
  if (!props.request) return;
  printing.value = true;
  try {
    const res = await fetch(`${API_URL}/form-templates/print/facility_request/${props.request.id}`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}`, Accept: 'application/json' },
    });
    const data = await res.json();
    if (data.success) {
      const w = window.open('', '_blank');
      if (!w) return;
      const rows = Object.entries(data.data)
        .filter(([k]) => k !== 'type' && k !== 'id')
        .map(([k, v]) => `<tr><td style="font-weight:600;padding:6px 12px;color:#555;min-width:140px;text-transform:capitalize">${k.replace(/_/g,' ')}</td><td style="padding:6px 12px">${Array.isArray(v) ? (v as string[]).join(', ') : v}</td></tr>`)
        .join('');
      w.document.write(`<html><head><title>${data.data.type} #${data.data.id}</title><style>body{font-family:sans-serif;padding:24px}h2{margin-bottom:16px}table{border-collapse:collapse;width:100%}td{border-bottom:1px solid #eee;font-size:13px}</style></head><body><h2>${data.data.type} — ${data.data.event_title || ''} #${data.data.id}</h2><table>${rows}</table></body></html>`);
      w.document.close();
      w.print();
    }
  } catch (e) {
    console.error(e);
  } finally {
    printing.value = false;
  }
};

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const canApprove = computed(() => {
  const userStr = localStorage.getItem('user');
  if (!userStr) return false;
  const user = JSON.parse(userStr);
  return user?.role?.name === 'Staff' || user?.role?.name === 'Admin';
});

const close = () => {
  isOpen.value = false;
};

const updateStatus = async (status: 'approved' | 'rejected') => {
  if (!props.request) return;

  updating.value = true;
  try {
    const token = localStorage.getItem('token');
    const response = await fetch(`${API_URL}/facility-requests/${props.request.id}/status`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ status })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Failed to update status');
    }

    emit('statusUpdated', data.data);
    close();
  } catch (error) {
    console.error('Error updating status:', error);
    alert('Failed to update status. Please try again.');
  } finally {
    updating.value = false;
  }
};

const formatDateTime = (date: string, time: string) => {
  if (!date) return 'N/A';
  const dateObj = new Date(date);
  const formattedDate = dateObj.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
  return time ? `${formattedDate} at ${time}` : formattedDate;
};


const getStatusClass = (status: string) => {
  const classes: { [key: string]: string } = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    canceled: 'bg-gray-100 text-gray-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>
