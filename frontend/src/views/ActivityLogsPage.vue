<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto">
      <h1 class="text-3xl font-bold text-gray-800 mb-6">Audit Trail & Activity Logs</h1>

      <!-- Filters -->
      <div class="bg-white rounded-xl shadow-lg p-4 mb-6 border border-gray-100 flex flex-wrap gap-4">
        <select v-model="actionFilter" @change="fetchLogs" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive bg-white text-black">
          <option value="all">All Actions</option>
          <option value="created">Created</option>
          <option value="updated">Updated</option>
          <option value="deleted">Deleted</option>
          <option value="status_changed">Status Changed</option>
          <option value="assigned">Assigned</option>
          <option value="login">Login</option>
          <option value="logout">Logout</option>
        </select>
        <select v-model="modelFilter" @change="fetchLogs" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive bg-white text-black">
          <option value="all">All Types</option>
          <option value="FacilityRequest">Facility Requests</option>
          <option value="WorkOrder">Work Orders</option>
          <option value="User">Users</option>
          <option value="Department">Departments</option>
        </select>
        <input v-model="dateFrom" @change="fetchLogs" type="date" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive bg-white text-black" placeholder="From" />
        <input v-model="dateTo" @change="fetchLogs" type="date" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive bg-white text-black" placeholder="To" />
      </div>

      <!-- Logs List -->
      <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <div v-if="loading" class="p-8 text-center text-gray-500">Loading...</div>
        <div v-else-if="logs.length === 0" class="p-8 text-center text-gray-500">No activity logs found</div>
        <div v-else class="divide-y divide-gray-200">
          <div v-for="log in logs" :key="log.id" class="p-4 hover:bg-gray-50">
            <div class="flex items-start gap-3">
              <div class="mt-1 w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" :class="getActionColor(log.action)">
                {{ getActionIcon(log.action) }}
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="font-medium text-gray-800">{{ log.user?.name || 'System' }}</span>
                  <span class="px-2 py-0.5 text-xs rounded-full font-medium" :class="getActionBadgeClass(log.action)">
                    {{ log.action }}
                  </span>
                  <span v-if="log.model_type" class="text-xs text-gray-500">{{ log.model_type }} #{{ log.model_id }}</span>
                </div>
                <p class="text-sm text-gray-600 mt-1">{{ log.description }}</p>
                <div class="flex items-center gap-4 mt-1 text-xs text-gray-400">
                  <span>{{ formatDate(log.created_at) }}</span>
                  <span v-if="log.ip_address">IP: {{ log.ip_address }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="p-4 border-t border-gray-200 flex items-center justify-between">
          <p class="text-sm text-gray-600">Page {{ pagination.current_page }} of {{ pagination.last_page }} ({{ pagination.total }} total)</p>
          <div class="flex gap-2">
            <button @click="goToPage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1" class="px-3 py-1 border border-gray-300 rounded-lg text-sm disabled:opacity-50 hover:bg-gray-50">Prev</button>
            <button @click="goToPage(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page" class="px-3 py-1 border border-gray-300 rounded-lg text-sm disabled:opacity-50 hover:bg-gray-50">Next</button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AppLayout from '@/components/AppLayout.vue';
import { API_URL } from '@/config/api';

const logs = ref<any[]>([]);
const loading = ref(false);
const actionFilter = ref('all');
const modelFilter = ref('all');
const dateFrom = ref('');
const dateTo = ref('');
const currentPage = ref(1);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });

const getAuthHeaders = () => ({
  'Authorization': `Bearer ${localStorage.getItem('token')}`,
  'Accept': 'application/json',
});

const getActionColor = (action: string) => {
  const map: Record<string, string> = {
    created: 'bg-green-500', updated: 'bg-blue-500', deleted: 'bg-red-500',
    status_changed: 'bg-yellow-500', assigned: 'bg-purple-500',
    login: 'bg-teal-500', logout: 'bg-gray-500',
  };
  return map[action] || 'bg-gray-500';
};

const getActionIcon = (action: string) => {
  const map: Record<string, string> = {
    created: '+', updated: 'E', deleted: 'X', status_changed: 'S',
    assigned: 'A', login: 'IN', logout: 'O',
  };
  return map[action] || '?';
};

const getActionBadgeClass = (action: string) => {
  const map: Record<string, string> = {
    created: 'bg-green-100 text-green-700', updated: 'bg-blue-100 text-blue-700',
    deleted: 'bg-red-100 text-red-700', status_changed: 'bg-yellow-100 text-yellow-700',
    assigned: 'bg-purple-100 text-purple-700', login: 'bg-teal-100 text-teal-700',
    logout: 'bg-gray-100 text-gray-700',
  };
  return map[action] || 'bg-gray-100 text-gray-700';
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const fetchLogs = async () => {
  loading.value = true;
  try {
    const params = new URLSearchParams({ page: currentPage.value.toString() });
    if (actionFilter.value !== 'all') params.append('action', actionFilter.value);
    if (modelFilter.value !== 'all') params.append('model_type', modelFilter.value);
    if (dateFrom.value) params.append('date_from', dateFrom.value);
    if (dateTo.value) params.append('date_to', dateTo.value);

    const res = await fetch(`${API_URL}/activity-logs?${params}`, { headers: getAuthHeaders() });
    const data = await res.json();
    if (data.success) {
      logs.value = data.data.data;
      pagination.value = {
        current_page: data.data.current_page,
        last_page: data.data.last_page,
        total: data.data.total,
      };
    }
  } catch (e) { console.error(e); }
  finally { loading.value = false; }
};

const goToPage = (page: number) => {
  currentPage.value = page;
  fetchLogs();
};

onMounted(() => fetchLogs());
</script>
