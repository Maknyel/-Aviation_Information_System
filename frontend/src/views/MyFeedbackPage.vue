<template>
  <AppLayout>
    <div class="max-w-4xl mx-auto">
      <div class="mb-8 flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-800 mb-2">My Feedback</h1>
          <p class="text-gray-600">Ratings and comments you've left on your requests</p>
        </div>
        <button
          @click="openModal"
          class="px-4 py-2 bg-aviation-olive text-white rounded-lg hover:bg-opacity-90 transition-all flex items-center gap-2"
        >
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
          Add Feedback
        </button>
      </div>

      <!-- Summary -->
      <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 text-center">
          <div class="text-3xl font-bold text-aviation-olive">{{ averageRating || '-' }}</div>
          <p class="text-xs text-gray-600 mt-1">Your Average Rating</p>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 text-center">
          <div class="text-3xl font-bold text-aviation-olive">{{ feedbackList.length }}</div>
          <p class="text-xs text-gray-600 mt-1">Feedback Submitted</p>
        </div>
      </div>

      <!-- List -->
      <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-800">Your Reviews</h2>
        </div>
        <div class="p-6 space-y-3">
          <div v-if="loading" class="text-center py-8 text-gray-500">Loading...</div>
          <div v-else-if="feedbackList.length === 0" class="text-center py-8 text-gray-400">
            You haven't left any feedback yet. Once one of your requests is approved or completed, you can rate the service from its Details page.
          </div>
          <div v-else v-for="fb in feedbackList" :key="fb.id" class="p-4 bg-gray-50 rounded-xl border border-gray-200">
            <div class="flex items-center justify-between mb-1">
              <div class="flex items-center gap-1">
                <span v-for="i in 5" :key="i" class="text-lg" :class="i <= fb.rating ? 'text-yellow-400' : 'text-gray-300'">&#9733;</span>
                <span class="text-sm text-gray-600 ml-1">{{ fb.rating }}/5</span>
              </div>
              <span class="px-2 py-0.5 text-xs rounded font-medium" :class="fb.request_type === 'facility_request' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700'">
                {{ fb.request_type === 'facility_request' ? 'Facility Request' : 'Work Order' }} #{{ fb.request_id }}
              </span>
            </div>
            <p v-if="fb.comment" class="text-sm text-gray-700 mt-1">{{ fb.comment }}</p>
            <p class="text-xs text-gray-400 mt-2">Submitted {{ formatDate(fb.created_at) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Feedback Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" @click.self="closeModal">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
          <h2 class="text-xl font-bold text-gray-800">Add Feedback</h2>
          <button @click="closeModal" class="p-1 hover:bg-gray-100 rounded-lg">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <div class="p-6">
          <div v-if="loadingEligible" class="text-center py-4 text-gray-500 text-sm">Loading your requests...</div>
          <div v-else-if="eligibleRequests.length === 0" class="text-sm text-gray-400 italic">
            No requests are ready for feedback right now. Once a facility request is approved or a work order is completed — and you haven't rated it yet — it'll show up here.
          </div>
          <form v-else @submit.prevent="submitFeedback" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Which request?</label>
              <select v-model="selectedKey" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent bg-white text-black">
                <option value="">Select a request...</option>
                <option v-for="r in eligibleRequests" :key="r.key" :value="r.key">{{ r.label }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
              <div class="flex items-center gap-1">
                <button v-for="i in 5" :key="i" type="button" @click="rating = i" class="text-3xl transition-colors" :class="i <= rating ? 'text-yellow-400' : 'text-gray-300 hover:text-yellow-200'">
                  &#9733;
                </button>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Comment <span class="text-gray-400 font-normal">(optional)</span></label>
              <textarea v-model="comment" rows="3" placeholder="Tell us about your experience..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent bg-white text-black"></textarea>
            </div>
            <div v-if="submitError" class="text-red-600 text-sm">{{ submitError }}</div>
            <div class="flex gap-3 justify-end pt-2">
              <button type="button" @click="closeModal" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                Cancel
              </button>
              <button type="submit" :disabled="!selectedKey || rating === 0 || submitting" class="px-6 py-2 bg-aviation-olive text-white rounded-lg hover:bg-opacity-90 disabled:opacity-50">
                {{ submitting ? 'Submitting...' : 'Submit Feedback' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/components/AppLayout.vue';
import { API_URL } from '@/config/api';

const feedbackList = ref<any[]>([]);
const loading = ref(false);

const facilityRequests = ref<any[]>([]);
const workOrders = ref<any[]>([]);
const loadingEligible = ref(false);

const showModal = ref(false);
const selectedKey = ref('');
const rating = ref(0);
const comment = ref('');
const submitting = ref(false);
const submitError = ref('');

const openModal = () => {
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  selectedKey.value = '';
  rating.value = 0;
  comment.value = '';
  submitError.value = '';
};

const averageRating = computed(() => {
  if (!feedbackList.value.length) return 0;
  const sum = feedbackList.value.reduce((acc, fb) => acc + fb.rating, 0);
  return Math.round((sum / feedbackList.value.length) * 10) / 10;
});

const eligibleRequests = computed(() => {
  const reviewedKeys = new Set(feedbackList.value.map((fb: any) => `${fb.request_type}:${fb.request_id}`));

  const facility = facilityRequests.value
    .filter((r: any) => r.status === 'approved' && !reviewedKeys.has(`facility_request:${r.id}`))
    .map((r: any) => ({
      key: `facility_request:${r.id}`,
      request_type: 'facility_request',
      request_id: r.id,
      label: `Facility Request #${r.id} — ${r.venue_requested}${r.date_of_event ? ` (${formatDate(r.date_of_event)})` : ''}`,
    }));

  const orders = workOrders.value
    .filter((o: any) => o.status === 'completed' && !reviewedKeys.has(`work_order:${o.id}`))
    .map((o: any) => ({
      key: `work_order:${o.id}`,
      request_type: 'work_order',
      request_id: o.id,
      label: `Work Order #${o.id} — ${o.location}${o.date ? ` (${formatDate(o.date)})` : ''}`,
    }));

  return [...facility, ...orders];
});

const getAuthHeaders = () => ({
  'Authorization': `Bearer ${localStorage.getItem('token')}`,
  'Accept': 'application/json',
});

const formatDate = (d: string) => {
  if (!d) return '';
  return new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const fetchFeedback = async () => {
  loading.value = true;
  try {
    const res = await fetch(`${API_URL}/feedbacks`, { headers: getAuthHeaders() });
    const data = await res.json();
    if (data.success) feedbackList.value = data.data;
  } catch (e) {
    console.error('Error fetching feedback:', e);
  } finally {
    loading.value = false;
  }
};

const fetchEligibleSource = async () => {
  loadingEligible.value = true;
  try {
    const [frRes, woRes] = await Promise.all([
      fetch(`${API_URL}/facility-requests`, { headers: getAuthHeaders() }),
      fetch(`${API_URL}/work-orders`, { headers: getAuthHeaders() }),
    ]);
    const [frData, woData] = await Promise.all([frRes.json(), woRes.json()]);
    if (frData.success) facilityRequests.value = frData.data;
    if (woData.success) workOrders.value = woData.data;
  } catch (e) {
    console.error('Error fetching requests:', e);
  } finally {
    loadingEligible.value = false;
  }
};

const submitFeedback = async () => {
  const selected = eligibleRequests.value.find((r: any) => r.key === selectedKey.value);
  if (!selected || rating.value === 0) return;

  submitting.value = true;
  submitError.value = '';
  try {
    const res = await fetch(`${API_URL}/feedbacks`, {
      method: 'POST',
      headers: { ...getAuthHeaders(), 'Content-Type': 'application/json' },
      body: JSON.stringify({
        request_type: selected.request_type,
        request_id: selected.request_id,
        rating: rating.value,
        comment: comment.value || null,
      }),
    });
    const data = await res.json();
    if (!res.ok) {
      submitError.value = data.message || 'Failed to submit feedback';
      return;
    }
    feedbackList.value.unshift(data.data);
    closeModal();
  } catch (e: any) {
    submitError.value = e.message || 'Failed to submit feedback';
  } finally {
    submitting.value = false;
  }
};

onMounted(() => {
  fetchFeedback();
  fetchEligibleSource();
});
</script>
