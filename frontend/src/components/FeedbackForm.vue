<template>
  <div v-if="show" class="mt-4 border-t border-gray-200 pt-4">
    <h4 class="text-sm font-semibold text-gray-800 mb-2">Feedback & Rating</h4>

    <!-- Existing feedback -->
    <div v-if="existingFeedback" class="bg-green-50 rounded-lg p-3 mb-3">
      <div class="flex items-center gap-1 mb-1">
        <span v-for="i in 5" :key="i" class="text-lg" :class="i <= existingFeedback.rating ? 'text-yellow-400' : 'text-gray-300'">&#9733;</span>
        <span class="text-sm text-gray-600 ml-2">{{ existingFeedback.rating }}/5</span>
      </div>
      <p v-if="existingFeedback.comment" class="text-sm text-gray-600">{{ existingFeedback.comment }}</p>
      <p class="text-xs text-gray-400 mt-1">Submitted by {{ existingFeedback.user?.name }}</p>
    </div>

    <!-- Submit feedback form -->
    <div v-else-if="canSubmit">
      <div class="flex items-center gap-1 mb-2">
        <button v-for="i in 5" :key="i" @click="rating = i" class="text-2xl transition-colors" :class="i <= rating ? 'text-yellow-400' : 'text-gray-300 hover:text-yellow-200'">
          &#9733;
        </button>
      </div>
      <textarea v-model="comment" rows="2" placeholder="Leave a comment (optional)..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-aviation-olive focus:border-transparent mb-2 bg-white text-black"></textarea>
      <div v-if="error" class="text-red-600 text-xs mb-2">{{ error }}</div>
      <button @click="submitFeedback" :disabled="rating === 0 || submitting" class="px-4 py-2 bg-aviation-olive text-white text-sm rounded-lg hover:bg-opacity-90 disabled:opacity-50">
        {{ submitting ? 'Submitting...' : 'Submit Feedback' }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { API_URL } from '@/config/api';

const props = defineProps<{
  requestType: 'facility_request' | 'work_order';
  requestId: number;
  requestStatus: string;
}>();

const emit = defineEmits(['submitted']);

const rating = ref(0);
const comment = ref('');
const submitting = ref(false);
const error = ref('');
const existingFeedback = ref<any>(null);
const feedbacks = ref<any[]>([]);

const show = computed(() => props.requestStatus === 'completed' || props.requestStatus === 'approved');
const canSubmit = computed(() => !existingFeedback.value && show.value);

const getAuthHeaders = () => ({
  'Authorization': `Bearer ${localStorage.getItem('token')}`,
  'Accept': 'application/json',
});

const loadFeedback = async () => {
  if (!props.requestId) return;
  try {
    const res = await fetch(`${API_URL}/feedbacks/${props.requestType}/${props.requestId}`, { headers: getAuthHeaders() });
    const data = await res.json();
    if (data.success) {
      feedbacks.value = data.data.feedbacks;
      const userStr = localStorage.getItem('user');
      if (userStr) {
        const user = JSON.parse(userStr);
        existingFeedback.value = feedbacks.value.find((f: any) => f.user_id === user.id) || null;
      }
    }
  } catch (e) { console.error(e); }
};

const submitFeedback = async () => {
  submitting.value = true;
  error.value = '';
  try {
    const res = await fetch(`${API_URL}/feedbacks`, {
      method: 'POST',
      headers: { ...getAuthHeaders(), 'Content-Type': 'application/json' },
      body: JSON.stringify({
        request_type: props.requestType,
        request_id: props.requestId,
        rating: rating.value,
        comment: comment.value || null,
      }),
    });
    const data = await res.json();
    if (!res.ok) {
      error.value = data.message || 'Failed to submit feedback';
      return;
    }
    existingFeedback.value = data.data;
    emit('submitted');
  } catch (e: any) {
    error.value = e.message;
  } finally {
    submitting.value = false;
  }
};

watch(() => props.requestId, () => {
  if (props.requestId) {
    rating.value = 0;
    comment.value = '';
    existingFeedback.value = null;
    loadFeedback();
  }
}, { immediate: true });
</script>
