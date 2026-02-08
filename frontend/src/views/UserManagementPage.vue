<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">User Management</h1>
        <button @click="openCreateModal" class="px-4 py-2 bg-aviation-olive text-white rounded-lg hover:bg-opacity-90 flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Add User
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-xl shadow-lg p-4 mb-6 border border-gray-100 flex flex-wrap gap-4">
        <input v-model="searchQuery" @input="fetchUsers" type="text" placeholder="Search by name or email..." class="flex-1 min-w-[200px] px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent bg-white text-black" />
        <select v-model="roleFilter" @change="fetchUsers" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive bg-white text-black">
          <option value="all">All Roles</option>
          <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
        </select>
        <select v-model="departmentFilter" @change="fetchUsers" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive bg-white text-black">
          <option value="all">All Departments</option>
          <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.code }} - {{ dept.name }}</option>
        </select>
      </div>

      <!-- Users Table -->
      <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div v-if="loading" class="p-8 text-center text-gray-500">Loading...</div>
        <div v-else-if="users.length === 0" class="p-8 text-center text-gray-500">No users found</div>
        <table v-else class="w-full">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Skills</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 bg-aviation-olive rounded-full flex items-center justify-center text-white text-sm font-bold">
                    {{ user.name?.charAt(0) }}
                  </div>
                  <span class="font-medium text-gray-800">{{ user.name }}</span>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ user.email }}</td>
              <td class="px-6 py-4">
                <span class="px-2 py-1 text-xs rounded-full font-medium" :class="getRoleBadgeClass(user.role?.name)">
                  {{ user.role?.name }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ user.department?.code || '-' }}</td>
              <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1">
                  <span v-for="skill in (user.skills || [])" :key="skill.id" class="px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded-full">
                    {{ skill.skill }}
                  </span>
                  <span v-if="!user.skills?.length" class="text-xs text-gray-400">-</span>
                </div>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex justify-end gap-2">
                  <button @click="openEditModal(user)" class="px-3 py-1 text-sm bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100">Edit</button>
                  <button @click="openSkillsModal(user)" v-if="user.role?.name === 'Staff' || user.role?.name === 'Admin'" class="px-3 py-1 text-sm bg-green-50 text-green-700 rounded-lg hover:bg-green-100">Skills</button>
                  <button @click="confirmDelete(user)" class="px-3 py-1 text-sm bg-red-50 text-red-700 rounded-lg hover:bg-red-100">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create/Edit User Modal -->
    <div v-if="showUserModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">{{ editingUser ? 'Edit User' : 'Create User' }}</h2>
        <form @submit.prevent="saveUser" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input v-model="userForm.name" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent bg-white text-black" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input v-model="userForm.email" type="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent bg-white text-black" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password {{ editingUser ? '(leave blank to keep)' : '' }}</label>
            <input v-model="userForm.password" type="password" :required="!editingUser" minlength="8" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive focus:border-transparent bg-white text-black" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select v-model="userForm.role_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive bg-white text-black">
              <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
            <select v-model="userForm.department_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive bg-white text-black">
              <option :value="null">None</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.code }} - {{ dept.name }}</option>
            </select>
          </div>
          <div v-if="formError" class="text-red-600 text-sm">{{ formError }}</div>
          <div class="flex gap-3 justify-end pt-2">
            <button type="button" @click="showUserModal = false" class="px-4 py-2 border border-gray-300 white rounded-lg hover:bg-gray-500 transition-colors bg-gray-400">Cancel</button>
            <button type="submit" :disabled="saving" class="px-4 py-2 bg-aviation-olive text-white rounded-lg hover:bg-opacity-90 disabled:opacity-50">
              {{ saving ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Skills Modal -->
    <div v-if="showSkillsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Manage Skills - {{ skillsUser?.name }}</h2>
        <div class="space-y-3 mb-4">
          <div v-for="(skill, index) in skillsForm" :key="index" class="flex gap-2">
            <select v-model="skill.skill" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white text-black">
              <option value="electrical">Electrical</option>
              <option value="plumbing">Plumbing</option>
              <option value="IT">IT</option>
              <option value="carpentry">Carpentry</option>
              <option value="general">General</option>
            </select>
            <select v-model="skill.proficiency" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white text-black">
              <option value="beginner">Beginner</option>
              <option value="intermediate">Intermediate</option>
              <option value="expert">Expert</option>
            </select>
            <button @click="skillsForm.splice(index, 1)" class="px-2 text-red-500 hover:text-red-700">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
        </div>
        <button @click="skillsForm.push({ skill: 'general', proficiency: 'intermediate' })" class="text-sm text-aviation-olive hover:underline mb-4">+ Add Skill</button>
        <div class="flex gap-3 justify-end pt-2">
          <button @click="showSkillsModal = false" class="px-4 py-2 border border-gray-300 white rounded-lg hover:bg-gray-500 transition-colors bg-gray-400">Cancel</button>
          <button @click="saveSkills" :disabled="saving" class="px-4 py-2 bg-aviation-olive text-white rounded-lg hover:bg-opacity-90 disabled:opacity-50">
            {{ saving ? 'Saving...' : 'Save Skills' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-2">Delete User</h2>
        <p class="text-gray-600 mb-4">Are you sure you want to delete <strong>{{ deletingUser?.name }}</strong>? This action cannot be undone.</p>
        <div class="flex gap-3 justify-end">
          <button @click="showDeleteModal = false" class="px-4 py-2 border border-gray-300 white rounded-lg hover:bg-gray-500 transition-colors bg-gray-400">Cancel</button>
          <button @click="deleteUser" :disabled="saving" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">
            {{ saving ? 'Deleting...' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AppLayout from '@/components/AppLayout.vue';
import { API_URL } from '@/config/api';

const users = ref<any[]>([]);
const roles = ref<any[]>([]);
const departments = ref<any[]>([]);
const loading = ref(false);
const saving = ref(false);
const searchQuery = ref('');
const roleFilter = ref('all');
const departmentFilter = ref('all');
const formError = ref('');

const showUserModal = ref(false);
const showSkillsModal = ref(false);
const showDeleteModal = ref(false);
const editingUser = ref<any>(null);
const skillsUser = ref<any>(null);
const deletingUser = ref<any>(null);

const userForm = ref({ name: '', email: '', password: '', role_id: null as number | null, department_id: null as number | null });
const skillsForm = ref<{ skill: string; proficiency: string }[]>([]);

const getAuthHeaders = () => ({
  'Authorization': `Bearer ${localStorage.getItem('token')}`,
  'Accept': 'application/json',
});

const getRoleBadgeClass = (role: string) => {
  const map: Record<string, string> = {
    Admin: 'bg-purple-100 text-purple-700',
    Staff: 'bg-blue-100 text-blue-700',
    Student: 'bg-green-100 text-green-700',
    HR: 'bg-orange-100 text-orange-700',
  };
  return map[role] || 'bg-gray-100 text-gray-700';
};

const fetchUsers = async () => {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    if (searchQuery.value) params.append('search', searchQuery.value);
    if (roleFilter.value !== 'all') params.append('role', roleFilter.value);
    if (departmentFilter.value !== 'all') params.append('department_id', departmentFilter.value);

    const res = await fetch(`${API_URL}/users?${params}`, { headers: getAuthHeaders() });
    const data = await res.json();
    if (data.success) users.value = data.data;
  } catch (e) { console.error(e); }
  finally { loading.value = false; }
};

const fetchRoles = async () => {
  try {
    const res = await fetch(`${API_URL}/roles`, { headers: getAuthHeaders() });
    const data = await res.json();
    if (data.success) roles.value = data.data;
  } catch (e) { console.error(e); }
};

const fetchDepartments = async () => {
  try {
    const res = await fetch(`${API_URL}/departments`, { headers: getAuthHeaders() });
    const data = await res.json();
    if (data.success) departments.value = data.data;
  } catch (e) { console.error(e); }
};

const openCreateModal = () => {
  editingUser.value = null;
  userForm.value = { name: '', email: '', password: '', role_id: roles.value[0]?.id, department_id: null };
  formError.value = '';
  showUserModal.value = true;
};

const openEditModal = (user: any) => {
  editingUser.value = user;
  userForm.value = { name: user.name, email: user.email, password: '', role_id: user.role_id, department_id: user.department_id };
  formError.value = '';
  showUserModal.value = true;
};

const saveUser = async () => {
  saving.value = true;
  formError.value = '';
  try {
    const url = editingUser.value ? `${API_URL}/users/${editingUser.value.id}` : `${API_URL}/users`;
    const method = editingUser.value ? 'PUT' : 'POST';

    const body: any = { ...userForm.value };
    if (editingUser.value && !body.password) delete body.password;

    const res = await fetch(url, {
      method,
      headers: { ...getAuthHeaders(), 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });
    const data = await res.json();

    if (!res.ok) {
      formError.value = data.message || 'Failed to save user';
      return;
    }

    showUserModal.value = false;
    fetchUsers();
  } catch (e: any) {
    formError.value = e.message;
  } finally {
    saving.value = false;
  }
};

const openSkillsModal = (user: any) => {
  skillsUser.value = user;
  skillsForm.value = (user.skills || []).map((s: any) => ({ skill: s.skill, proficiency: s.proficiency }));
  if (skillsForm.value.length === 0) skillsForm.value.push({ skill: 'general', proficiency: 'intermediate' });
  showSkillsModal.value = true;
};

const saveSkills = async () => {
  saving.value = true;
  try {
    const res = await fetch(`${API_URL}/users/${skillsUser.value.id}/skills`, {
      method: 'PUT',
      headers: { ...getAuthHeaders(), 'Content-Type': 'application/json' },
      body: JSON.stringify({ skills: skillsForm.value }),
    });
    await res.json();
    showSkillsModal.value = false;
    fetchUsers();
  } catch (e) { console.error(e); }
  finally { saving.value = false; }
};

const confirmDelete = (user: any) => {
  deletingUser.value = user;
  showDeleteModal.value = true;
};

const deleteUser = async () => {
  saving.value = true;
  try {
    await fetch(`${API_URL}/users/${deletingUser.value.id}`, { method: 'DELETE', headers: getAuthHeaders() });
    showDeleteModal.value = false;
    fetchUsers();
  } catch (e) { console.error(e); }
  finally { saving.value = false; }
};

onMounted(() => {
  fetchUsers();
  fetchRoles();
  fetchDepartments();
});
</script>
