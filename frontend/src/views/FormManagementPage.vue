<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Form Management</h1>
        <p class="text-gray-600">Upload form templates and manage saved request records</p>
      </div>

      <!-- Tabs -->
      <div class="flex gap-2 mb-6">
        <button
          v-for="tab in tabs"
          :key="tab.value"
          @click="activeTab = tab.value"
          :class="[
            'px-5 py-2 rounded-lg text-sm font-medium transition-all',
            activeTab === tab.value ? 'bg-aviation-olive text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'
          ]"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Templates Tab -->
      <div v-if="activeTab === 'templates'">
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
          <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">Form Templates</h2>
            <button
              @click="showUploadModal = true"
              class="px-4 py-2 bg-aviation-olive text-white text-sm rounded-lg hover:bg-opacity-90 flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
              </svg>
              Upload Template
            </button>
          </div>

          <div v-if="loadingTemplates" class="p-8 text-center text-gray-500">Loading...</div>
          <div v-else-if="templates.length === 0" class="p-8 text-center text-gray-400">No templates uploaded yet</div>
          <table v-else class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">File</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Uploaded</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="template in templates" :key="template.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="font-medium text-gray-800">{{ template.name }}</div>
                  <div v-if="template.description" class="text-xs text-gray-500 mt-1">{{ template.description }}</div>
                </td>
                <td class="px-6 py-4">
                  <span class="px-2 py-1 text-xs rounded-full font-medium capitalize" :class="typeBadge(template.form_type)">
                    {{ formatType(template.form_type) }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ template.file_name }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(template.created_at) }}</td>
                <td class="px-6 py-4">
                  <div class="flex justify-end gap-2">
                    <button @click="downloadTemplate(template)" class="px-3 py-1 text-sm bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 flex items-center gap-1">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                      </svg>
                      Download
                    </button>
                    <button @click="confirmDeleteTemplate(template)" class="px-3 py-1 text-sm bg-red-50 text-red-700 rounded-lg hover:bg-red-100">Delete</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Saved Requests Tab -->
      <div v-if="activeTab === 'saved'">
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
          <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Saved Requests</h2>
          </div>

          <div v-if="loadingSaved" class="p-8 text-center text-gray-500">Loading...</div>
          <div v-else-if="savedForms.length === 0" class="p-8 text-center text-gray-400">No saved request forms yet</div>
          <table v-else class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Saved By</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date Saved</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="saved in savedForms" :key="saved.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <span class="px-2 py-1 text-xs rounded-full font-medium capitalize" :class="typeBadge(saved.request_type)">
                    {{ formatType(saved.request_type) }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-700">
                  #{{ saved.request_id }}
                  <span v-if="saved.request_data">
                    — {{ saved.request_data.title_of_event || saved.request_data.description_of_problem?.substring(0, 40) }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ saved.saved_by?.name || 'N/A' }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(saved.created_at) }}</td>
                <td class="px-6 py-4">
                  <div class="flex justify-end">
                    <button @click="printSavedRequest(saved)" class="px-3 py-1 text-sm bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 flex items-center gap-1">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                      </svg>
                      Print
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Upload Modal -->
    <FormTemplateUploadModal v-model="showUploadModal" @uploaded="handleUploaded" />

    <!-- Print Preview Modal -->
    <div v-if="showPrintModal && printData" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] flex flex-col">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between shrink-0">
          <h2 class="text-xl font-semibold text-gray-800">{{ printData.type }} #{{ printData.id }}</h2>
          <div class="flex gap-2">
            <button @click="doPrint" class="px-4 py-2 bg-aviation-olive text-white text-sm rounded-lg hover:bg-opacity-90">Print</button>
            <button @click="showPrintModal = false" class="p-2 hover:bg-gray-100 rounded-lg">
              <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>
        <div id="printable-content" class="overflow-y-auto flex-1 p-6 space-y-3 text-sm">
          <template v-for="(value, field) in printData" :key="field">
            <div v-if="String(field) !== 'type' && String(field) !== 'id'" class="flex gap-2">
              <span class="font-medium text-gray-600 capitalize min-w-32">{{ formatKey(String(field)) }}:</span>
              <span class="text-gray-800">{{ Array.isArray(value) ? (value as string[]).join(', ') : value }}</span>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- Delete Template Confirm -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
      <div class="bg-white rounded-xl shadow-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Delete Template</h3>
        <p class="text-gray-600 mb-6">Are you sure you want to delete <strong>{{ deletingTemplate?.name }}</strong>?</p>
        <div class="flex gap-3 justify-end">
          <button @click="showDeleteConfirm = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Cancel</button>
          <button @click="deleteTemplate" :disabled="deleting" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 disabled:opacity-50">
            {{ deleting ? 'Deleting...' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AppLayout from '@/components/AppLayout.vue';
import FormTemplateUploadModal from '@/components/FormTemplateUploadModal.vue';
import { API_URL } from '@/config/api';

const activeTab = ref('templates');
const tabs = [
  { value: 'templates', label: 'Form Templates' },
  { value: 'saved', label: 'Saved Requests' },
];

const templates = ref<any[]>([]);
const savedForms = ref<any[]>([]);
const loadingTemplates = ref(false);
const loadingSaved = ref(false);
const showUploadModal = ref(false);
const showPrintModal = ref(false);
const printData = ref<any>(null);
const showDeleteConfirm = ref(false);
const deletingTemplate = ref<any>(null);
const deleting = ref(false);

const authHeaders = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`,
  Accept: 'application/json',
});

const formatDate = (d: string) => {
  if (!d) return '';
  return new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const formatType = (type: string) => {
  const map: Record<string, string> = {
    facility_request: 'Facility Request',
    work_order: 'Work Order',
    general: 'General',
  };
  return map[type] || type;
};

const typeBadge = (type: string) => {
  const map: Record<string, string> = {
    facility_request: 'bg-green-100 text-green-700',
    work_order: 'bg-blue-100 text-blue-700',
    general: 'bg-gray-100 text-gray-700',
  };
  return map[type] || 'bg-gray-100 text-gray-700';
};

const formatKey = (key: string) => key.replace(/_/g, ' ');

const fetchTemplates = async () => {
  loadingTemplates.value = true;
  try {
    const res = await fetch(`${API_URL}/form-templates`, { headers: authHeaders() });
    const data = await res.json();
    if (data.success) templates.value = data.data;
  } catch (e) {
    console.error(e);
  } finally {
    loadingTemplates.value = false;
  }
};

const fetchSavedForms = async () => {
  loadingSaved.value = true;
  try {
    const res = await fetch(`${API_URL}/form-templates/saved`, { headers: authHeaders() });
    const data = await res.json();
    if (data.success) savedForms.value = data.data;
  } catch (e) {
    console.error(e);
  } finally {
    loadingSaved.value = false;
  }
};

const downloadTemplate = (template: any) => {
  const a = document.createElement('a');
  a.href = `${API_URL}/form-templates/${template.id}/download`;
  a.setAttribute('download', template.file_name);

  const token = localStorage.getItem('token');
  fetch(a.href, { headers: { Authorization: `Bearer ${token}` } })
    .then(res => res.blob())
    .then(blob => {
      const url = URL.createObjectURL(blob);
      a.href = url;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
    });
};

const handleUploaded = () => {
  fetchTemplates();
};

const confirmDeleteTemplate = (template: any) => {
  deletingTemplate.value = template;
  showDeleteConfirm.value = true;
};

const deleteTemplate = async () => {
  if (!deletingTemplate.value) return;
  deleting.value = true;
  try {
    const res = await fetch(`${API_URL}/form-templates/${deletingTemplate.value.id}`, {
      method: 'DELETE',
      headers: authHeaders(),
    });
    const data = await res.json();
    if (data.success) {
      showDeleteConfirm.value = false;
      fetchTemplates();
    }
  } catch (e) {
    console.error(e);
  } finally {
    deleting.value = false;
  }
};

const printSavedRequest = async (saved: any) => {
  try {
    const res = await fetch(`${API_URL}/form-templates/print/${saved.request_type}/${saved.request_id}`, {
      headers: authHeaders(),
    });
    const data = await res.json();
    if (data.success) {
      printData.value = data.data;
      showPrintModal.value = true;
    }
  } catch (e) {
    console.error(e);
  }
};

const doPrint = () => {
  const content = document.getElementById('printable-content');
  if (!content) return;
  const w = window.open('', '_blank');
  if (!w) return;
  w.document.write(`<html><head><title>Print</title><style>body{font-family:sans-serif;padding:20px;font-size:13px}.row{display:flex;gap:8px;margin-bottom:8px}.label{font-weight:600;min-width:140px;color:#555;text-transform:capitalize}.value{color:#111}</style></head><body>`);
  w.document.write(content.innerHTML);
  w.document.write('</body></html>');
  w.document.close();
  w.print();
};

onMounted(() => {
  fetchTemplates();
  fetchSavedForms();
});
</script>
