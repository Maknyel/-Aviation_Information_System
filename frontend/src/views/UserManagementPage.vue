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
                  <template v-if="['Staff', 'Admin', 'Employee'].includes(user.role?.name)">
                    <button @click="openSkillsModal(user, 'add')" class="px-3 py-1 text-sm bg-green-50 text-green-700 rounded-lg hover:bg-green-100">Add Skills</button>
                    <button @click="openSkillsModal(user, 'edit')" :disabled="!user.skills?.length" class="px-3 py-1 text-sm bg-yellow-50 text-yellow-800 rounded-lg hover:bg-yellow-100 disabled:opacity-40 disabled:cursor-not-allowed">Edit Skills</button>
                  </template>
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
            <p class="text-xs text-gray-400 mt-1">Must be a recognized provider (Gmail, Yahoo, Outlook, etc.)</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password {{ editingUser ? '(leave blank to keep)' : '' }}</label>
            <PasswordInput v-model="userForm.password" :required="!editingUser" minlength="8" dense />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select v-model="userForm.role_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-aviation-olive bg-white text-black">
              <option v-for="role in assignableRoles" :key="role.id" :value="role.id">{{ role.name }}</option>
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

    <!-- Skills Modal (Add Skills / Edit Skills) -->
    <div v-if="showSkillsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">
          {{ skillsMode === 'add' ? 'Add Skills' : 'Edit Skills' }} - {{ skillsUser?.name }}
        </h2>

        <!-- Existing skills (read-only when adding) -->
        <div v-if="skillsMode === 'add' && existingSkills.length" class="mb-4">
          <p class="text-xs font-medium text-gray-500 uppercase mb-1">Current skills</p>
          <div class="flex flex-wrap gap-1">
            <span v-for="(sk, i) in existingSkills" :key="i" class="px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded-full">
              {{ sk.skill }} ({{ sk.proficiency }})
            </span>
          </div>
        </div>

        <datalist id="skill-suggestions">
          <option v-for="name in skillSuggestions" :key="name" :value="name" />
        </datalist>

        <div class="space-y-3 mb-4 max-h-72 overflow-y-auto">
          <div v-for="(skill, index) in skillsForm" :key="index" class="flex gap-2">
            <input
              v-model="skill.skill"
              list="skill-suggestions"
              type="text"
              maxlength="100"
              placeholder="Skill name"
              class="flex-1 min-w-0 px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white text-black"
            />
            <select v-model="skill.proficiency" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white text-black">
              <option value="beginner">Beginner</option>
              <option value="intermediate">Intermediate</option>
              <option value="expert">Expert</option>
            </select>
            <button v-if="skillsMode === 'edit' || skillsForm.length > 1" @click="skillsForm.splice(index, 1)" class="px-2 text-red-500 hover:text-red-700" title="Remove">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <p v-if="skillsMode === 'edit' && skillsForm.length === 0" class="text-sm text-gray-500">All skills removed. Saving will clear this user's skills.</p>
        </div>
        <button v-if="skillsMode === 'add'" @click="skillsForm.push({ skill: '', proficiency: 'intermediate' })" class="text-sm text-aviation-olive hover:underline mb-4">+ Add another skill</button>
        <div v-if="skillsError" class="text-red-600 text-sm mb-2">{{ skillsError }}</div>
        <div class="flex gap-3 justify-end pt-2">
          <button @click="showSkillsModal = false" class="px-4 py-2 border border-gray-300 white rounded-lg hover:bg-gray-500 transition-colors bg-gray-400">Cancel</button>
          <button @click="saveSkills" :disabled="saving" class="px-4 py-2 bg-aviation-olive text-white rounded-lg hover:bg-opacity-90 disabled:opacity-50">
            {{ saving ? 'Saving...' : (skillsMode === 'add' ? 'Add Skills' : 'Save Changes') }}
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
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/components/AppLayout.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { API_URL } from '@/config/api';
import { useToast } from '@/composables/useToast';
import { isAllowedEmailDomain } from '@/utils/validation';

const toast = useToast();

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
    Requester: 'bg-green-100 text-green-700',
    Employee: 'bg-yellow-100 text-yellow-700',
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

// Only one Admin account exists, so Admin is never offered as a role to assign.
// The existing admin keeps the option so editing that account still works.
const assignableRoles = computed(() =>
  roles.value.filter(r => r.name !== 'Admin' || editingUser.value?.role?.name === 'Admin')
);

const openCreateModal = () => {
  editingUser.value = null;
  userForm.value = { name: '', email: '', password: '', role_id: assignableRoles.value[0]?.id, department_id: null };
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
  if (!isAllowedEmailDomain(userForm.value.email)) {
    formError.value = 'Please use a recognized email provider (Gmail, Yahoo, Outlook, etc.)';
    toast.error(formError.value);
    return;
  }

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
      toast.error(formError.value);
      return;
    }

    showUserModal.value = false;
    toast.success(editingUser.value ? 'User updated successfully' : 'User created successfully');
    fetchUsers();
  } catch (e: any) {
    formError.value = e.message;
    toast.error(formError.value || 'Failed to save user');
  } finally {
    saving.value = false;
  }
};

const skillsMode = ref<'add' | 'edit'>('add');
const skillsError = ref('');
const existingSkills = ref<{ skill: string; proficiency: string }[]>([]);
const skillSuggestions = ['electrical', 'plumbing', 'IT', 'carpentry', 'general'];

const openSkillsModal = (user: any, mode: 'add' | 'edit') => {
  skillsUser.value = user;
  skillsMode.value = mode;
  skillsError.value = '';
  existingSkills.value = (user.skills || []).map((sk: any) => ({ skill: sk.skill, proficiency: sk.proficiency }));
  // Add: start with one blank entry. Edit: work on a copy of the current skills.
  skillsForm.value = mode === 'add'
    ? [{ skill: '', proficiency: 'intermediate' }]
    : existingSkills.value.map(sk => ({ ...sk }));
  showSkillsModal.value = true;
};

const saveSkills = async () => {
  skillsError.value = '';
  const entered = skillsForm.value.map(sk => ({ skill: sk.skill.trim(), proficiency: sk.proficiency }));

  if (entered.some(sk => !sk.skill)) {
    skillsError.value = 'Skill name cannot be empty.';
    return;
  }
  if (skillsMode.value === 'add' && entered.length === 0) {
    skillsError.value = 'Enter at least one skill.';
    return;
  }

  // Add keeps the existing skills and appends the new ones; Edit replaces the whole list
  const payload = skillsMode.value === 'add' ? [...existingSkills.value, ...entered] : entered;
  const names = payload.map(sk => sk.skill.toLowerCase());
  if (new Set(names).size !== names.length) {
    skillsError.value = 'Each skill can only be listed once for a user.';
    return;
  }

  saving.value = true;
  try {
    const res = await fetch(`${API_URL}/users/${skillsUser.value.id}/skills`, {
      method: 'PUT',
      headers: { ...getAuthHeaders(), 'Content-Type': 'application/json' },
      body: JSON.stringify({ skills: payload }),
    });
    const data = await res.json();
    if (!res.ok || data.success === false) {
      toast.error(data.message || 'Failed to save skills');
      return;
    }
    showSkillsModal.value = false;
    toast.success(skillsMode.value === 'add' ? 'Skills added successfully' : 'Skills updated successfully');
    fetchUsers();
  } catch (e) {
    console.error(e);
    toast.error('Failed to save skills');
  } finally { saving.value = false; }
};

const confirmDelete = (user: any) => {
  deletingUser.value = user;
  showDeleteModal.value = true;
};

const deleteUser = async () => {
  saving.value = true;
  try {
    const res = await fetch(`${API_URL}/users/${deletingUser.value.id}`, { method: 'DELETE', headers: getAuthHeaders() });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || data.success === false) {
      toast.error(data.message || 'Failed to delete user');
      return;
    }
    showDeleteModal.value = false;
    toast.success('User deleted successfully');
    fetchUsers();
  } catch (e) {
    console.error(e);
    toast.error('Failed to delete user');
  } finally { saving.value = false; }
};

onMounted(() => {
  fetchUsers();
  fetchRoles();
  fetchDepartments();
});
</script>
