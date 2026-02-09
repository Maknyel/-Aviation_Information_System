import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router';
import HomePage from '../views/HomePage.vue'
import LoginPage from '../views/LoginPage.vue'
import CalendarPage from '../views/CalendarPage.vue'
import RequestsPage from '../views/RequestsPage.vue'
import NotificationsPage from '../views/NotificationsPage.vue'
import ProfilePage from '../views/ProfilePage.vue'
import RequesterDashboard from '../views/RequesterDashboard.vue'
import StaffDashboard from '../views/StaffDashboard.vue'
import AdminDashboard from '../views/AdminDashboard.vue'
import UserManagementPage from '../views/UserManagementPage.vue'
import ReportsPage from '../views/ReportsPage.vue'
import ActivityLogsPage from '../views/ActivityLogsPage.vue'
import ForgotPasswordPage from '../views/ForgotPasswordPage.vue'
import EmailTestPage from '../views/EmailTestPage.vue'
import DepartmentsPage from '../views/DepartmentsPage.vue'
import ResetPasswordPage from '../views/ResetPasswordPage.vue'

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'Login',
    component: LoginPage
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: ForgotPasswordPage
  },
  {
    path: '/reset-password',
    name: 'ResetPassword',
    component: ResetPasswordPage
  },
  {
    path: '/home',
    name: 'Home',
    redirect: (to) => {
      const userStr = localStorage.getItem('user');
      if (userStr) {
        const user = JSON.parse(userStr);
        const role = user?.role?.name;

        if (role === 'Requester') return '/requester-dashboard';
        if (role === 'Staff') return '/staff-dashboard';
        if (role === 'Admin') return '/admin-dashboard';
      }
      return '/requester-dashboard';
    },
    meta: { requiresAuth: true }
  },
  {
    path: '/requester-dashboard',
    name: 'RequesterDashboard',
    component: RequesterDashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/staff-dashboard',
    name: 'StaffDashboard',
    component: StaffDashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin-dashboard',
    name: 'AdminDashboard',
    component: AdminDashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/calendar',
    name: 'Calendar',
    component: CalendarPage,
    meta: { requiresAuth: true }
  },
  {
    path: '/requests',
    name: 'Requests',
    component: RequestsPage,
    meta: { requiresAuth: true }
  },
  {
    path: '/notifications',
    name: 'Notifications',
    component: NotificationsPage,
    meta: { requiresAuth: true }
  },
  {
    path: '/users',
    name: 'UserManagement',
    component: UserManagementPage,
    meta: { requiresAuth: true, role: 'Admin' }
  },
  {
    path: '/reports',
    name: 'Reports',
    component: ReportsPage,
    meta: { requiresAuth: true }
  },
  {
    path: '/activity-logs',
    name: 'ActivityLogs',
    component: ActivityLogsPage,
    meta: { requiresAuth: true }
  },
  {
    path: '/departments',
    name: 'Departments',
    component: DepartmentsPage,
    meta: { requiresAuth: true, role: 'Admin' }
  },
  {
    path: '/email-templates',
    name: 'EmailTemplates',
    component: EmailTestPage,
    meta: { requiresAuth: true, role: 'Admin' }
  },
  {
    path: '/profile',
    name: 'Profile',
    component: ProfilePage,
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token');

  if (to.meta.requiresAuth && !token) {
    next('/login');
  } else if (to.path === '/login' && token) {
    next('/home');
  } else {
    next();
  }
})

export default router
