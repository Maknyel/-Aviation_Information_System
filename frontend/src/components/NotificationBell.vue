<template>
  <div class="relative">
    <button
      @click="toggleOpen"
      class="relative p-2 text-white hover:bg-white hover:bg-opacity-20 rounded-lg transition-all"
      aria-label="Notifications"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>
      <span
        v-if="unreadCount > 0"
        class="absolute top-0.5 right-0.5 min-w-[18px] h-[18px] px-1 flex items-center justify-center bg-red-500 text-white text-[10px] font-bold rounded-full"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <!-- Side Panel -->
    <div
      v-if="open"
      class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] bg-white rounded-lg shadow-xl border border-gray-200 z-50 max-h-[70vh] flex flex-col"
    >
      <div class="p-3 border-b border-gray-200 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-800">Notifications</h3>
        <button v-if="notifications.length" @click="markAllRead" class="text-xs text-aviation-olive hover:underline">
          Mark all read
        </button>
      </div>

      <div class="overflow-y-auto flex-1">
        <div v-if="loading" class="p-6 text-center text-sm text-gray-400">Loading...</div>
        <div v-else-if="notifications.length === 0" class="p-6 text-center text-sm text-gray-400">No notifications</div>
        <button
          v-for="n in notifications.slice(0, 10)"
          :key="n.id"
          @click="handleClick(n)"
          class="w-full text-left p-3 border-b border-gray-100 hover:bg-gray-50 transition-colors flex gap-2"
          :class="!n.is_read ? 'bg-green-50' : ''"
        >
          <div class="mt-1 w-2 h-2 rounded-full shrink-0" :class="n.is_read ? 'bg-transparent' : 'bg-aviation-olive'"></div>
          <div class="min-w-0">
            <p class="text-sm font-medium text-gray-800 truncate">{{ n.title }}</p>
            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ n.message }}</p>
            <p class="text-[11px] text-gray-400 mt-1">{{ timeAgo(n.created_at) }}</p>
          </div>
        </button>
      </div>

      <div class="p-2 border-t border-gray-200 text-center">
        <router-link to="/notifications" @click="open = false" class="text-xs text-aviation-olive hover:underline font-medium">
          View All
        </router-link>
      </div>
    </div>

    <div v-if="open" @click="open = false" class="fixed inset-0 z-40"></div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import { API_URL } from '@/config/api';
import { useRealtimeNotifications } from '@/composables/useRealtimeNotifications';

const router = useRouter();
const open = ref(false);
const loading = ref(false);
const notifications = ref<any[]>([]);
const unreadCount = ref(0);

const authHeaders = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`,
  Accept: 'application/json',
});

const fetchUnreadCount = async () => {
  try {
    const res = await fetch(`${API_URL}/notifications/unread-count`, { headers: authHeaders() });
    const data = await res.json();
    if (data.success) unreadCount.value = data.data.count;
  } catch (e) {
    console.error('Error fetching unread count:', e);
  }
};

const fetchNotifications = async () => {
  loading.value = true;
  try {
    const res = await fetch(`${API_URL}/notifications`, { headers: authHeaders() });
    const data = await res.json();
    if (data.success) notifications.value = data.data;
  } catch (e) {
    console.error('Error fetching notifications:', e);
  } finally {
    loading.value = false;
  }
};

const toggleOpen = () => {
  open.value = !open.value;
  if (open.value) fetchNotifications();
};

const markAllRead = async () => {
  try {
    await fetch(`${API_URL}/notifications/mark-all-read`, { method: 'PATCH', headers: authHeaders() });
    notifications.value = notifications.value.map(n => ({ ...n, is_read: true }));
    unreadCount.value = 0;
  } catch (e) {
    console.error('Error marking all as read:', e);
  }
};

const handleClick = async (n: any) => {
  if (!n.is_read) {
    try {
      await fetch(`${API_URL}/notifications/${n.id}/read`, { method: 'PATCH', headers: authHeaders() });
      n.is_read = true;
      unreadCount.value = Math.max(0, unreadCount.value - 1);
    } catch (e) {
      console.error('Error marking notification as read:', e);
    }
  }
  open.value = false;
  router.push('/notifications');
};

const timeAgo = (dateStr: string) => {
  const seconds = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
  if (seconds < 60) return 'Just now';
  const minutes = Math.floor(seconds / 60);
  if (minutes < 60) return `${minutes}m ago`;
  const hours = Math.floor(minutes / 60);
  if (hours < 24) return `${hours}h ago`;
  const days = Math.floor(hours / 24);
  return `${days}d ago`;
};

useRealtimeNotifications(() => {
  fetchUnreadCount();
  if (open.value) fetchNotifications();
});

let interval: ReturnType<typeof setInterval> | undefined;

onMounted(() => {
  fetchUnreadCount();
  interval = setInterval(fetchUnreadCount, 60000);
});

onBeforeUnmount(() => {
  if (interval) clearInterval(interval);
});
</script>
