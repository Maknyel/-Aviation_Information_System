<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto">
      <div class="mb-8 flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-800 mb-2">Inventory</h1>
          <p class="text-gray-600">Track facility items and monitor availability in real time</p>
        </div>
        <button
          v-if="isAdmin"
          @click="openCreateModal"
          class="px-4 py-2 bg-aviation-olive text-white text-sm rounded-lg hover:bg-opacity-90 transition-all flex items-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          Add Item
        </button>
      </div>

      <!-- Summary Cards -->
      <!-- Date context badge -->
      <div class="mb-4 flex items-center gap-2 text-xs text-gray-500">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        Deployed &amp; Available figures are based on <span class="font-semibold text-gray-700 mx-1">approved</span> facility requests for today
        <span class="font-semibold text-aviation-olive">{{ todayPH }}</span>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-lg p-5 border border-gray-100 text-center">
          <div class="text-3xl font-bold text-aviation-olive mb-1">{{ overview.total_items }}</div>
          <div class="text-sm text-gray-600">Total Items</div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-5 border border-gray-100 text-center">
          <div class="text-3xl font-bold text-blue-600 mb-1">{{ overview.total_deployed }}</div>
          <div class="text-sm text-gray-600">Deployed Today</div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-5 border border-gray-100 text-center">
          <div class="text-3xl font-bold text-green-600 mb-1">{{ overview.total_available }}</div>
          <div class="text-sm text-gray-600">Available Today</div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-5 border border-gray-100 text-center">
          <div class="text-3xl font-bold text-gray-700 mb-1">{{ overview.by_category?.length || 0 }}</div>
          <div class="text-sm text-gray-600">Categories</div>
        </div>
      </div>

      <!-- Items Table -->
      <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center gap-4">
            <input
              v-model="search"
              type="text"
              placeholder="Search items..."
              class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-aviation-olive"
            />
            <select v-model="categoryFilter" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none">
              <option value="">All Categories</option>
              <option value="furniture">Furniture</option>
              <option value="av_equipment">AV Equipment</option>
              <option value="utility">Utility</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>

        <div v-if="loading" class="p-8 text-center text-gray-500">Loading...</div>
        <div v-else-if="filteredItems.length === 0" class="p-8 text-center text-gray-500">No inventory items found</div>
        <table v-else class="w-full table-fixed">
          <colgroup>
            <col class="w-44">
            <col class="w-32">
            <col class="w-24">
            <col class="w-28">
            <col class="w-28">
            <col class="w-24">
            <col>
            <col class="w-28">
          </colgroup>
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
              <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
              <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Deployed</th>
              <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Condition</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location (Today)</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="item in filteredItems" :key="item.id" class="hover:bg-gray-50">
              <td class="px-4 py-4">
                <div class="font-medium text-gray-800 truncate">{{ item.name }}</div>
                <div v-if="item.notes" class="text-xs text-gray-500 mt-1 truncate">{{ item.notes }}</div>
              </td>
              <td class="px-4 py-4">
                <span class="px-2 py-1 text-xs rounded-full font-medium capitalize" :class="categoryBadge(item.category)">
                  {{ formatCategory(item.category) }}
                </span>
              </td>
              <td class="px-4 py-4 text-center text-sm text-gray-700">{{ item.total_quantity }} {{ item.unit }}</td>
              <td class="px-4 py-4 text-center text-sm font-medium" :class="(item.deployed_quantity ?? 0) > 0 ? 'text-blue-600' : 'text-gray-400'">
                {{ item.deployed_quantity ?? 0 }}
              </td>
              <td class="px-4 py-4 text-center text-sm font-medium" :class="(item.available_quantity ?? item.total_quantity) > 0 ? 'text-green-600' : 'text-red-500'">
                {{ item.available_quantity ?? item.total_quantity }}
              </td>
              <td class="px-4 py-4">
                <span class="px-2 py-1 text-xs rounded-full font-medium capitalize" :class="conditionBadge(item.condition)">
                  {{ item.condition }}
                </span>
              </td>
              <td class="px-4 py-4">
                <div v-if="item.locations?.length" class="flex flex-wrap gap-1">
                  <span
                    v-for="loc in item.locations"
                    :key="loc"
                    class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700 font-medium whitespace-nowrap"
                  >
                    {{ loc }}
                  </span>
                </div>
                <span v-else class="text-xs text-gray-400 italic">—</span>
              </td>
              <td class="px-4 py-4">
                <div class="flex justify-end gap-2">
                  <button
                    v-if="isAdmin"
                    @click="openEditModal(item)"
                    class="px-3 py-1 text-sm bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100"
                  >
                    Edit
                  </button>
                  <button
                    v-if="isAdmin"
                    @click="confirmDelete(item)"
                    class="px-3 py-1 text-sm bg-red-50 text-red-700 rounded-lg hover:bg-red-100"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Item Create/Edit Modal -->
    <InventoryItemModal
      v-model="showItemModal"
      :item="editingItem"
      @saved="handleItemSaved"
    />


    <!-- Delete Confirmation -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
      <div class="bg-white rounded-xl shadow-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Delete Item</h3>
        <p class="text-gray-600 mb-6">Are you sure you want to delete <strong>{{ deletingItem?.name }}</strong>? This cannot be undone.</p>
        <div class="flex gap-3 justify-end">
          <button @click="showDeleteConfirm = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Cancel</button>
          <button @click="deleteItem" :disabled="deleting" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 disabled:opacity-50">
            {{ deleting ? 'Deleting...' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/components/AppLayout.vue';
import InventoryItemModal from '@/components/InventoryItemModal.vue';
import { API_URL } from '@/config/api';

const user = ref<any>(null);
const isAdmin = computed(() => user.value?.role?.name === 'Admin');

const todayPH = new Date().toLocaleDateString('en-PH', {
  timeZone: 'Asia/Manila',
  year: 'numeric',
  month: 'long',
  day: 'numeric',
});

const loading = ref(false);
const items = ref<any[]>([]);
const overview = ref<any>({ total_items: 0, total_deployed: 0, total_available: 0, by_category: [] });
const search = ref('');
const categoryFilter = ref('');

const showItemModal = ref(false);
const editingItem = ref<any>(null);
const showDeleteConfirm = ref(false);
const deletingItem = ref<any>(null);
const deleting = ref(false);

const filteredItems = computed(() => {
  return items.value.filter(item => {
    const matchSearch = !search.value || item.name.toLowerCase().includes(search.value.toLowerCase());
    const matchCat = !categoryFilter.value || item.category === categoryFilter.value;
    return matchSearch && matchCat;
  });
});

const formatCategory = (cat: string) => {
  const map: Record<string, string> = {
    furniture: 'Furniture',
    av_equipment: 'AV Equipment',
    utility: 'Utility',
    other: 'Other',
  };
  return map[cat] || cat;
};

const categoryBadge = (cat: string) => {
  const map: Record<string, string> = {
    furniture: 'bg-amber-100 text-amber-700',
    av_equipment: 'bg-blue-100 text-blue-700',
    utility: 'bg-gray-100 text-gray-700',
    other: 'bg-purple-100 text-purple-700',
  };
  return map[cat] || 'bg-gray-100 text-gray-700';
};

const conditionBadge = (cond: string) => {
  const map: Record<string, string> = {
    good: 'bg-green-100 text-green-700',
    fair: 'bg-yellow-100 text-yellow-700',
    poor: 'bg-red-100 text-red-700',
  };
  return map[cond] || 'bg-gray-100 text-gray-700';
};

const authHeaders = () => ({
  Authorization: `Bearer ${localStorage.getItem('token')}`,
  Accept: 'application/json',
});

const fetchItems = async () => {
  loading.value = true;
  try {
    const res = await fetch(`${API_URL}/inventory`, { headers: authHeaders() });
    const data = await res.json();
    if (data.success) items.value = data.data;
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const fetchOverview = async () => {
  try {
    const res = await fetch(`${API_URL}/inventory/overview`, { headers: authHeaders() });
    const data = await res.json();
    if (data.success) overview.value = data.data;
  } catch (e) {
    console.error(e);
  }
};

const openCreateModal = () => {
  editingItem.value = null;
  showItemModal.value = true;
};

const openEditModal = (item: any) => {
  editingItem.value = { ...item };
  showItemModal.value = true;
};


const handleItemSaved = () => {
  fetchItems();
  fetchOverview();
};

const confirmDelete = (item: any) => {
  deletingItem.value = item;
  showDeleteConfirm.value = true;
};

const deleteItem = async () => {
  if (!deletingItem.value) return;
  deleting.value = true;
  try {
    const res = await fetch(`${API_URL}/inventory/${deletingItem.value.id}`, {
      method: 'DELETE',
      headers: authHeaders(),
    });
    const data = await res.json();
    if (data.success) {
      showDeleteConfirm.value = false;
      fetchItems();
      fetchOverview();
    } else {
      alert(data.message || 'Cannot delete item');
    }
  } catch (e) {
    console.error(e);
  } finally {
    deleting.value = false;
  }
};

onMounted(() => {
  const userStr = localStorage.getItem('user');
  if (userStr) user.value = JSON.parse(userStr);
  fetchItems();
  fetchOverview();
});
</script>
