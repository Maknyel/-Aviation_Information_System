<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Departments</h1>
        <button @click="openCreateModal" class="px-4 py-2 bg-aviation-olive text-white rounded-lg hover:bg-opacity-90 flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Add Department
        </button>
      </div>

      <!-- Search -->
      <div class="bg-white rounded-xl shadow-lg p-4 mb-6 border border-gray-100">
        <input v-model="searchQuery" type="text" placeholder="Search by name or code..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent bg-white text-black" />
      </div>

      <!-- Departments Table -->
      <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div v-if="loading" class="p-8 text-center text-gray-500">Loading...</div>
        <div v-else-if="filteredDepartments.length === 0" class="p-8 text-center text-gray-500">No departments found</div>
        <table v-else class="w-full">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Users</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="dept in filteredDepartments" :key="dept.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <span class="px-2 py-1 text-xs bg-aviation-olive bg-opacity-10 text-aviation-olive rounded-full font-medium">{{ dept.code }}</span>
              </td>
              <td class="px-6 py-4 font-medium text-gray-800">{{ dept.name }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ dept.description || '-' }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ dept.users_count ?? 0 }}</td>
              <td class="px-6 py-4 text-right">
                <div class="flex justify-end gap-2">
                  <button @click="openEditModal(dept)" class="px-3 py-1 text-sm bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100">Edit</button>
                  <button @click="confirmDelete(dept)" class="px-3 py-1 text-sm bg-red-50 text-red-700 rounded-lg hover:bg-red-100">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">{{ editingDept ? 'Edit Department' : 'Create Department' }}</h2>
        <form @submit.prevent="saveDepartment" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input v-model="form.name" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent bg-white text-black" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Code</label>
            <input v-model="form.code" type="text" required maxlength="20" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent bg-white text-black" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea v-model="form.description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent bg-white text-black"></textarea>
          </div>
          <div v-if="formError" class="text-red-600 text-sm">{{ formError }}</div>
          <div class="flex gap-3 justify-end pt-2">
            <button type="button" @click="showModal = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-500 transition-colors bg-gray-400">Cancel</button>
            <button type="submit" :disabled="saving" class="px-4 py-2 bg-aviation-olive text-white rounded-lg hover:bg-opacity-90 disabled:opacity-50">
              {{ saving ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-2">Delete Department</h2>
        <p class="text-gray-600 mb-4">Are you sure you want to delete <strong>{{ deletingDept?.name }}</strong>? This action cannot be undone.</p>
        <div class="flex gap-3 justify-end">
          <button @click="showDeleteModal = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-500 transition-colors bg-gray-400">Cancel</button>
          <button @click="deleteDepartment" :disabled="saving" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">
            {{ saving ? 'Deleting...' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/components/AppLayout.vue';
import { API_URL } from '@/config/api';

const departments = ref<any[]>([]);
const loading = ref(false);
const saving = ref(false);
const searchQuery = ref('');
const formError = ref('');

const showModal = ref(false);
const showDeleteModal = ref(false);
const editingDept = ref<any>(null);
const deletingDept = ref<any>(null);

const form = ref({ name: '', code: '', description: '' });

const getAuthHeaders = () => ({
  'Authorization': `Bearer ${localStorage.getItem('token')}`,
  'Accept': 'application/json',
});

const filteredDepartments = computed(() => {
  if (!searchQuery.value) return departments.value;
  const q = searchQuery.value.toLowerCase();
  return departments.value.filter(
    (d: any) => d.name.toLowerCase().includes(q) || d.code.toLowerCase().includes(q)
  );
});

const fetchDepartments = async () => {
  loading.value = true;
  try {
    const res = await fetch(`${API_URL}/departments`, { headers: getAuthHeaders() });
    const data = await res.json();
    if (data.success) departments.value = data.data;
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  editingDept.value = null;
  form.value = { name: '', code: '', description: '' };
  formError.value = '';
  showModal.value = true;
};

const openEditModal = (dept: any) => {
  editingDept.value = dept;
  form.value = { name: dept.name, code: dept.code, description: dept.description || '' };
  formError.value = '';
  showModal.value = true;
};

const saveDepartment = async () => {
  saving.value = true;
  formError.value = '';
  try {
    const url = editingDept.value ? `${API_URL}/departments/${editingDept.value.id}` : `${API_URL}/departments`;
    const method = editingDept.value ? 'PUT' : 'POST';

    const res = await fetch(url, {
      method,
      headers: { ...getAuthHeaders(), 'Content-Type': 'application/json' },
      body: JSON.stringify(form.value),
    });
    const data = await res.json();

    if (!res.ok) {
      formError.value = data.message || 'Failed to save department';
      return;
    }

    showModal.value = false;
    fetchDepartments();
  } catch (e: any) {
    formError.value = e.message;
  } finally {
    saving.value = false;
  }
};

const confirmDelete = (dept: any) => {
  deletingDept.value = dept;
  showDeleteModal.value = true;
};

const deleteDepartment = async () => {
  saving.value = true;
  try {
    await fetch(`${API_URL}/departments/${deletingDept.value.id}`, { method: 'DELETE', headers: getAuthHeaders() });
    showDeleteModal.value = false;
    fetchDepartments();
  } catch (e) {
    console.error(e);
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchDepartments();
});
</script>
