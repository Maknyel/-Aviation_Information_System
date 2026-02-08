<template>
  <!-- Mobile Sidebar Overlay -->
  <div
    v-if="isOpen"
    class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
    @click="emit('toggle')"
  ></div>

  <!-- Sidebar -->
  <aside
    :class="[
      'fixed lg:static inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0',
      isOpen ? 'translate-x-0' : '-translate-x-full'
    ]"
  >
    <div class="p-4 h-full overflow-y-auto">
      <!-- User Profile Section -->
      <div class="mb-6 pb-4 border-b border-gray-200">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 bg-aviation-olive rounded-full flex items-center justify-center text-white text-lg font-bold">
            {{ user?.name?.charAt(0) || 'U' }}
          </div>
          <div>
            <p class="font-semibold text-gray-800">{{ user?.name }}</p>
            <p class="text-sm text-gray-500">{{ user?.role?.name }}</p>
          </div>
        </div>
      </div>

      <!-- Navigation Menu -->
      <nav>
        <router-link
          v-for="item in menuItems"
          :key="item.path"
          :to="item.path"
          @click="emit('toggle')"
          class="flex items-center gap-3 px-4 py-3 mb-2 rounded-lg transition-all"
          :class="isActive(item.path) ? 'bg-aviation-olive text-white' : 'text-gray-700 hover:bg-gray-100'"
        >
          <component :is="item.icon" class="w-5 h-5" />
          <span>{{ item.label }}</span>
        </router-link>
      </nav>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { computed, h } from 'vue';
import { useRoute } from 'vue-router';

const props = defineProps<{
  user: any;
  isOpen: boolean;
}>();

const emit = defineEmits(['toggle']);

const route = useRoute();

const isActive = (path: string) => {
  // Special handling for dashboard menu item
  if (path === '/home') {
    return route.path.includes('dashboard');
  }
  return route.path === path;
};

// Icon components
const DashboardIcon = () => h('svg', {
  fill: 'none',
  stroke: 'currentColor',
  viewBox: '0 0 24 24',
  class: 'w-5 h-5'
}, [
  h('path', {
    'stroke-linecap': 'round',
    'stroke-linejoin': 'round',
    'stroke-width': '2',
    d: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'
  })
]);

const CalendarIcon = () => h('svg', {
  fill: 'none',
  stroke: 'currentColor',
  viewBox: '0 0 24 24',
  class: 'w-5 h-5'
}, [
  h('path', {
    'stroke-linecap': 'round',
    'stroke-linejoin': 'round',
    'stroke-width': '2',
    d: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'
  })
]);

const RequestsIcon = () => h('svg', {
  fill: 'none',
  stroke: 'currentColor',
  viewBox: '0 0 24 24',
  class: 'w-5 h-5'
}, [
  h('path', {
    'stroke-linecap': 'round',
    'stroke-linejoin': 'round',
    'stroke-width': '2',
    d: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
  })
]);

const NotificationIcon = () => h('svg', {
  fill: 'none',
  stroke: 'currentColor',
  viewBox: '0 0 24 24',
  class: 'w-5 h-5'
}, [
  h('path', {
    'stroke-linecap': 'round',
    'stroke-linejoin': 'round',
    'stroke-width': '2',
    d: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'
  })
]);

const UsersIcon = () => h('svg', {
  fill: 'none',
  stroke: 'currentColor',
  viewBox: '0 0 24 24',
  class: 'w-5 h-5'
}, [
  h('path', {
    'stroke-linecap': 'round',
    'stroke-linejoin': 'round',
    'stroke-width': '2',
    d: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'
  })
]);

const ReportsIcon = () => h('svg', {
  fill: 'none',
  stroke: 'currentColor',
  viewBox: '0 0 24 24',
  class: 'w-5 h-5'
}, [
  h('path', {
    'stroke-linecap': 'round',
    'stroke-linejoin': 'round',
    'stroke-width': '2',
    d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'
  })
]);

const AuditIcon = () => h('svg', {
  fill: 'none',
  stroke: 'currentColor',
  viewBox: '0 0 24 24',
  class: 'w-5 h-5'
}, [
  h('path', {
    'stroke-linecap': 'round',
    'stroke-linejoin': 'round',
    'stroke-width': '2',
    d: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'
  })
]);

// Menu items based on role
const menuItems = computed(() => {
  const role = props.user?.role?.name;

  const baseMenu = [
    { path: '/home', label: 'Dashboard', icon: DashboardIcon },
  ];

  if (role === 'Admin') {
    return [
      ...baseMenu,
      { path: '/calendar', label: 'Calendar', icon: CalendarIcon },
      { path: '/requests', label: 'Request', icon: RequestsIcon },
      { path: '/users', label: 'User Management', icon: UsersIcon },
      { path: '/reports', label: 'Reports', icon: ReportsIcon },
      { path: '/activity-logs', label: 'Audit Logs', icon: AuditIcon },
    ];
  } else if (role === 'Staff') {
    return [
      ...baseMenu,
      { path: '/calendar', label: 'Calendar', icon: CalendarIcon },
      { path: '/requests', label: 'Request', icon: RequestsIcon },
      { path: '/reports', label: 'Reports', icon: ReportsIcon },
    ];
  } else if (role === 'Student') {
    return [
      ...baseMenu,
      { path: '/calendar', label: 'Calendar', icon: CalendarIcon },
      { path: '/requests', label: 'Request', icon: RequestsIcon },
    ];
  }

  return baseMenu;
});
</script>
