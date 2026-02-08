<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Reports & Analytics</h1>
        <select v-model="selectedYear" @change="loadReports" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive bg-white text-black">
          <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
        </select>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 text-center">
          <div class="text-3xl font-bold text-aviation-olive">{{ summary.total_facility_requests }}</div>
          <p class="text-xs text-gray-600 mt-1">Total Facility Requests</p>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 text-center">
          <div class="text-3xl font-bold text-aviation-olive">{{ summary.total_work_orders }}</div>
          <p class="text-xs text-gray-600 mt-1">Total Work Orders</p>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 text-center">
          <div class="text-3xl font-bold text-aviation-olive">{{ summary.average_feedback_rating || 0 }}</div>
          <p class="text-xs text-gray-600 mt-1">Avg. Feedback Rating</p>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 text-center">
          <div class="text-3xl font-bold text-aviation-olive">{{ summary.total_users }}</div>
          <p class="text-xs text-gray-600 mt-1">Total Users</p>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Monthly Volume Chart -->
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Request Volume ({{ selectedYear }})</h3>
          <div class="h-64">
            <StatsChart
              v-if="monthlyVolume.labels.length"
              type="bar"
              :labels="monthlyVolume.labels"
              :data="monthlyVolume.facility_requests"
              title="Facility Requests"
              :backgroundColor="'#4A7C59'"
            />
            <p v-else class="text-gray-400 text-sm text-center mt-20">No data available</p>
          </div>
        </div>

        <!-- Completion Time Chart -->
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Avg. Completion Time - Hours ({{ selectedYear }})</h3>
          <div class="h-64">
            <StatsChart
              v-if="completionTime.labels.length"
              type="line"
              :labels="completionTime.labels"
              :data="completionTime.values"
              title="Hours"
              :backgroundColor="'#5A8C69'"
              :borderColor="'#4A7C59'"
            />
            <p v-else class="text-gray-400 text-sm text-center mt-20">No data available</p>
          </div>
        </div>
      </div>

      <!-- Staff Performance -->
      <div class="bg-white rounded-xl shadow-lg border border-gray-100 mb-6">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-800">Staff Performance</h3>
        </div>
        <div v-if="staffPerformance.length === 0" class="p-8 text-center text-gray-500">No performance data</div>
        <table v-else class="w-full">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Staff</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Assigned</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Completed</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">In Progress</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Rate</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Rating</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Skills</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="staff in staffPerformance" :key="staff.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 font-medium text-gray-800">{{ staff.name }}</td>
              <td class="px-6 py-4 text-center text-gray-600">{{ staff.total_assigned }}</td>
              <td class="px-6 py-4 text-center text-green-600 font-medium">{{ staff.completed }}</td>
              <td class="px-6 py-4 text-center text-blue-600">{{ staff.in_progress }}</td>
              <td class="px-6 py-4 text-center">
                <span :class="staff.completion_rate >= 80 ? 'text-green-600' : staff.completion_rate >= 50 ? 'text-yellow-600' : 'text-red-600'" class="font-medium">
                  {{ staff.completion_rate }}%
                </span>
              </td>
              <td class="px-6 py-4 text-center">
                <span v-if="staff.average_rating" class="text-yellow-600 font-medium">{{ staff.average_rating }}/5</span>
                <span v-else class="text-gray-400">-</span>
              </td>
              <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1">
                  <span v-for="skill in staff.skills" :key="skill" class="px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded-full">{{ skill }}</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Hotspots -->
      <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-800">Facility Problem Hotspots (Last 6 Months)</h3>
        </div>
        <div v-if="hotspots.length === 0" class="p-8 text-center text-gray-500">No hotspot data</div>
        <div v-else class="p-6 space-y-3">
          <div v-for="spot in hotspots" :key="spot.location" class="flex items-center gap-4">
            <div class="flex-1">
              <div class="flex items-center justify-between mb-1">
                <span class="text-sm font-medium text-gray-800">{{ spot.location }}</span>
                <span class="text-sm text-gray-600">{{ spot.total_orders }} orders ({{ spot.urgent_count }} urgent)</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-aviation-olive h-2 rounded-full" :style="{ width: `${(spot.total_orders / maxHotspot) * 100}%` }"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/components/AppLayout.vue';
import StatsChart from '@/components/StatsChart.vue';
import { API_URL } from '@/config/api';

const currentYear = new Date().getFullYear();
const selectedYear = ref(currentYear);
const yearOptions = Array.from({ length: 5 }, (_, i) => currentYear - i);

const summary = ref<any>({});
const monthlyVolume = ref<any>({ labels: [], facility_requests: [], work_orders: [] });
const completionTime = ref<any>({ labels: [], values: [] });
const staffPerformance = ref<any[]>([]);
const hotspots = ref<any[]>([]);

const maxHotspot = computed(() => Math.max(...hotspots.value.map((h: any) => h.total_orders), 1));

const getAuthHeaders = () => ({
  'Authorization': `Bearer ${localStorage.getItem('token')}`,
  'Accept': 'application/json',
});

const loadReports = async () => {
  const year = selectedYear.value;
  const headers = getAuthHeaders();

  const [summaryRes, volumeRes, completionRes, perfRes, hotspotsRes] = await Promise.all([
    fetch(`${API_URL}/reports/summary`, { headers }),
    fetch(`${API_URL}/reports/monthly-volume?year=${year}`, { headers }),
    fetch(`${API_URL}/reports/completion-time?year=${year}`, { headers }),
    fetch(`${API_URL}/reports/staff-performance?year=${year}`, { headers }),
    fetch(`${API_URL}/reports/hotspots`, { headers }),
  ]);

  const [summaryData, volumeData, completionData, perfData, hotspotsData] = await Promise.all([
    summaryRes.json(), volumeRes.json(), completionRes.json(), perfRes.json(), hotspotsRes.json(),
  ]);

  if (summaryData.success) summary.value = summaryData.data;
  if (volumeData.success) monthlyVolume.value = volumeData.data;
  if (completionData.success) completionTime.value = completionData.data;
  if (perfData.success) staffPerformance.value = perfData.data;
  if (hotspotsData.success) hotspots.value = hotspotsData.data;
};

onMounted(() => loadReports());
</script>
