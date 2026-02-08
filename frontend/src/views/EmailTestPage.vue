<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto">
      <h1 class="text-3xl font-bold text-gray-800 mb-6">Email Templates</h1>

      <!-- SMTP Status -->
      <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">SMTP Configuration</h3>
        <div v-if="smtpStatus" class="grid grid-cols-2 md:grid-cols-5 gap-4">
          <div>
            <p class="text-xs text-gray-500 uppercase">Status</p>
            <span class="inline-block px-3 py-1 rounded-full text-sm font-medium mt-1" :class="smtpStatus.configured ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
              {{ smtpStatus.configured ? 'Configured' : 'Not Configured' }}
            </span>
          </div>
          <div>
            <p class="text-xs text-gray-500 uppercase">Sender Name</p>
            <p class="text-sm font-medium text-gray-800 mt-1">{{ smtpStatus.from_name }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500 uppercase">Host</p>
            <p class="text-sm font-medium text-gray-800 mt-1">{{ smtpStatus.host }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500 uppercase">Port</p>
            <p class="text-sm font-medium text-gray-800 mt-1">{{ smtpStatus.port }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500 uppercase">From Email</p>
            <p class="text-sm font-medium text-gray-800 mt-1">{{ smtpStatus.from_address }}</p>
          </div>
        </div>
      </div>

      <!-- Template Selection & Send Test -->
      <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 mb-6">
        <div class="flex flex-col md:flex-row gap-4 items-end">
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Email Template</label>
            <select v-model="selectedTemplate" @change="previewTemplate" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive bg-white text-black">
              <option value="">Select a template...</option>
              <option v-for="t in templates" :key="t.name" :value="t.name">{{ t.label }}</option>
            </select>
          </div>
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Test Email Address</label>
            <input v-model="testEmail" type="email" placeholder="recipient@example.com" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent bg-white text-black" />
          </div>
          <button @click="sendTestEmail" :disabled="!selectedTemplate || !testEmail || sending" class="px-6 py-2 bg-aviation-olive text-white rounded-lg hover:bg-opacity-90 disabled:opacity-50 whitespace-nowrap">
            {{ sending ? 'Sending...' : 'Send Test Email' }}
          </button>
        </div>
        <div v-if="sendMessage" class="mt-3 text-sm" :class="sendSuccess ? 'text-green-600' : 'text-red-600'">
          {{ sendMessage }}
        </div>
      </div>

      <!-- Sample Data -->
      <div v-if="sampleData && Object.keys(sampleData).length" class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Sample Data</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div v-for="(value, key) in sampleData" :key="key">
            <p class="text-xs text-gray-500 uppercase">{{ formatKey(key) }}</p>
            <p class="text-sm font-medium text-gray-800 mt-1">{{ value }}</p>
          </div>
        </div>
      </div>

      <!-- Template Preview -->
      <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-800">Template Preview</h3>
          <span v-if="selectedTemplate" class="px-3 py-1 bg-aviation-olive bg-opacity-10 text-aviation-olive rounded-full text-sm font-medium">{{ selectedTemplate }}</span>
        </div>
        <div v-if="loading" class="p-8 text-center text-gray-500">Loading preview...</div>
        <div v-else-if="!previewHtml" class="p-8 text-center text-gray-400">Select a template above to preview</div>
        <div v-else class="p-4">
          <iframe ref="previewFrame" class="w-full border border-gray-200 rounded-lg" style="min-height: 600px;" sandbox="allow-same-origin"></iframe>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick, watch } from 'vue';
import AppLayout from '@/components/AppLayout.vue';
import { API_URL } from '@/config/api';

const templates = ref<any[]>([]);
const selectedTemplate = ref('');
const testEmail = ref('');
const previewHtml = ref('');
const sampleData = ref<any>({});
const loading = ref(false);
const sending = ref(false);
const sendMessage = ref('');
const sendSuccess = ref(false);
const smtpStatus = ref<any>(null);
const previewFrame = ref<HTMLIFrameElement | null>(null);

const getAuthHeaders = () => ({
  'Authorization': `Bearer ${localStorage.getItem('token')}`,
  'Accept': 'application/json',
});

const fetchTemplates = async () => {
  try {
    const res = await fetch(`${API_URL}/email/templates`, { headers: getAuthHeaders() });
    const data = await res.json();
    if (data.success) templates.value = data.data;
  } catch (e) { console.error(e); }
};

const fetchSmtpStatus = async () => {
  try {
    const res = await fetch(`${API_URL}/email/smtp-status`, { headers: getAuthHeaders() });
    const data = await res.json();
    if (data.success) smtpStatus.value = data.data;
  } catch (e) { console.error(e); }
};

const previewTemplate = async () => {
  if (!selectedTemplate.value) {
    previewHtml.value = '';
    sampleData.value = {};
    return;
  }

  loading.value = true;
  sendMessage.value = '';
  try {
    const res = await fetch(`${API_URL}/email/preview`, {
      method: 'POST',
      headers: { ...getAuthHeaders(), 'Content-Type': 'application/json' },
      body: JSON.stringify({ template: selectedTemplate.value }),
    });
    const data = await res.json();
    if (data.success) {
      previewHtml.value = data.data.html;
      sampleData.value = data.data.sample_data;
      await nextTick();
      renderPreview();
    }
  } catch (e) { console.error(e); }
  finally { loading.value = false; }
};

const renderPreview = () => {
  if (previewFrame.value && previewHtml.value) {
    const doc = previewFrame.value.contentDocument;
    if (doc) {
      doc.open();
      doc.write(previewHtml.value);
      doc.close();
    }
  }
};

watch(previewHtml, async () => {
  await nextTick();
  renderPreview();
});

const sendTestEmail = async () => {
  sending.value = true;
  sendMessage.value = '';
  try {
    const res = await fetch(`${API_URL}/email/send-test`, {
      method: 'POST',
      headers: { ...getAuthHeaders(), 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: testEmail.value, template: selectedTemplate.value }),
    });
    const data = await res.json();
    sendSuccess.value = data.success;
    sendMessage.value = data.message;
  } catch (e: any) {
    sendSuccess.value = false;
    sendMessage.value = e.message || 'Failed to send test email';
  } finally { sending.value = false; }
};

const formatKey = (key: string) => key.replace(/_/g, ' ').replace(/([A-Z])/g, ' $1');

onMounted(() => {
  fetchTemplates();
  fetchSmtpStatus();
});
</script>
