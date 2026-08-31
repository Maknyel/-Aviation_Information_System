<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Reports & Analytics</h1>
        <div class="flex items-center gap-3">
          <button
            v-if="!loading && !loadError"
            @click="downloadPdf"
            class="px-4 py-2 bg-aviation-olive text-white rounded-lg hover:bg-opacity-90 transition-all flex items-center gap-2 text-sm font-medium"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Download PDF
          </button>
          <select v-model="selectedYear" @change="loadReports" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive bg-white text-black">
            <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>
      </div>

      <div v-if="loading" class="p-8 text-center text-gray-500">Loading reports...</div>
      <div v-else-if="loadError" class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-6 flex items-center justify-between">
        <span>{{ loadError }}</span>
        <button @click="loadReports" class="px-3 py-1 text-sm bg-red-100 rounded-lg hover:bg-red-200">Retry</button>
      </div>
      <template v-else>

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
      <div class="grid grid-cols-1 2xl:grid-cols-2 gap-6 mb-6">
        <!-- Monthly Volume Chart -->
        <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-100">
          <h3 class="text-2xl font-semibold text-gray-800 mb-6">Monthly Request Volume ({{ selectedYear }})</h3>
          <div class="h-[28rem]">
            <StatsChart
              v-if="monthlyVolume.labels.length"
              type="bar"
              :labels="monthlyVolume.labels"
              :data="monthlyVolume.facility_requests"
              title="Facility Requests"
              :backgroundColor="'#4A7C59'"
            />
            <p v-else class="text-gray-400 text-sm text-center mt-32">No data available</p>
          </div>
        </div>

        <!-- Completion Time Chart -->
        <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-100">
          <h3 class="text-2xl font-semibold text-gray-800 mb-6">Avg. Completion Time - Hours ({{ selectedYear }})</h3>
          <div class="h-[28rem]">
            <StatsChart
              v-if="completionTime.labels.length"
              type="line"
              :labels="completionTime.labels"
              :data="completionTime.values"
              title="Hours"
              :backgroundColor="'#5A8C69'"
              :borderColor="'#4A7C59'"
            />
            <p v-else class="text-gray-400 text-sm text-center mt-32">No data available</p>
          </div>
        </div>
      </div>

      <!-- Staff Performance -->
      <div class="bg-white rounded-xl shadow-lg border border-gray-100 mb-6">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-800">Staff Performance</h3>
        </div>
        <div v-if="staffPerformance.length === 0" class="p-8 text-center text-gray-500">No performance data</div>
        <div v-else class="max-h-96 overflow-y-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200 sticky top-0 z-10">
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
      </template>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/components/AppLayout.vue';
import StatsChart from '@/components/StatsChart.vue';
import { API_URL } from '@/config/api';
import { escapeHtml } from '@/utils/auth';

const currentYear = new Date().getFullYear();
const selectedYear = ref(currentYear);
const yearOptions = Array.from({ length: 5 }, (_, i) => currentYear - i);

const summary = ref<any>({});
const monthlyVolume = ref<any>({ labels: [], facility_requests: [], work_orders: [] });
const completionTime = ref<any>({ labels: [], values: [] });
const staffPerformance = ref<any[]>([]);
const hotspots = ref<any[]>([]);
const loading = ref(false);
const loadError = ref('');

const maxHotspot = computed(() => Math.max(...hotspots.value.map((h: any) => h.total_orders), 1));

const getAuthHeaders = () => ({
  'Authorization': `Bearer ${localStorage.getItem('token')}`,
  'Accept': 'application/json',
});

const loadReports = async () => {
  loading.value = true;
  loadError.value = '';
  try {
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
  } catch (e) {
    console.error(e);
    loadError.value = 'Failed to load reports. Please try again.';
  } finally {
    loading.value = false;
  }
};

const downloadPdf = () => {
  const w = window.open('', '_blank');
  if (!w) return;

  const monthlyRows = monthlyVolume.value.labels.map((label: string, i: number) =>
    `<tr><td>${escapeHtml(label)}</td><td>${monthlyVolume.value.facility_requests[i] ?? 0}</td><td>${monthlyVolume.value.work_orders[i] ?? 0}</td></tr>`
  ).join('');

  const staffRows = staffPerformance.value.map((s: any) =>
    `<tr><td>${escapeHtml(s.name)}</td><td>${s.total_assigned}</td><td>${s.completed}</td><td>${s.in_progress}</td><td>${s.completion_rate}%</td><td>${s.average_rating ?? '-'}</td></tr>`
  ).join('');

  const hotspotRows = hotspots.value.map((h: any) =>
    `<tr><td>${escapeHtml(h.location)}</td><td>${h.total_orders}</td><td>${h.urgent_count}</td></tr>`
  ).join('');

  w.document.write(`<html><head><title>Reports & Analytics ${escapeHtml(String(selectedYear.value))}</title>
    <style>
      body{font-family:sans-serif;padding:24px;color:#222}
      h1{margin-bottom:4px}
      h2{margin-top:28px;margin-bottom:8px;font-size:16px}
      .summary{display:flex;gap:24px;margin:16px 0;flex-wrap:wrap}
      .card{border:1px solid #ddd;border-radius:8px;padding:12px 16px;min-width:140px}
      .card .num{font-size:22px;font-weight:700;color:#4A7C59}
      .card .label{font-size:12px;color:#666}
      table{border-collapse:collapse;width:100%;font-size:13px}
      th,td{border-bottom:1px solid #eee;padding:6px 10px;text-align:left}
      th{background:#f7f7f7}
    </style>
  </head><body>
    <h1>Reports & Analytics</h1>
    <p>Year: ${escapeHtml(String(selectedYear.value))} — Generated ${escapeHtml(new Date().toLocaleString())}</p>

    <div class="summary">
      <div class="card"><div class="num">${summary.value.total_facility_requests ?? 0}</div><div class="label">Total Facility Requests</div></div>
      <div class="card"><div class="num">${summary.value.total_work_orders ?? 0}</div><div class="label">Total Work Orders</div></div>
      <div class="card"><div class="num">${summary.value.average_feedback_rating ?? 0}</div><div class="label">Avg. Feedback Rating</div></div>
      <div class="card"><div class="num">${summary.value.total_users ?? 0}</div><div class="label">Total Users</div></div>
    </div>

    <h2>Monthly Request Volume</h2>
    <table><thead><tr><th>Month</th><th>Facility Requests</th><th>Work Orders</th></tr></thead><tbody>${monthlyRows || '<tr><td colspan="3">No data</td></tr>'}</tbody></table>

    <h2>Staff Performance</h2>
    <table><thead><tr><th>Staff</th><th>Assigned</th><th>Completed</th><th>In Progress</th><th>Rate</th><th>Rating</th></tr></thead><tbody>${staffRows || '<tr><td colspan="6">No data</td></tr>'}</tbody></table>

    <h2>Facility Problem Hotspots</h2>
    <table><thead><tr><th>Location</th><th>Total Orders</th><th>Urgent</th></tr></thead><tbody>${hotspotRows || '<tr><td colspan="3">No data</td></tr>'}</tbody></table>
  </body></html>`);
  w.document.close();
  w.print();
};

onMounted(() => loadReports());
</script>
