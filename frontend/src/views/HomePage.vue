<template>
  <ion-page>
    <ion-header :translucent="true">
      <ion-toolbar color="secondary">
        <ion-title class="font-semibold">Aviation Information System</ion-title>
        <ion-buttons slot="end">
          <ion-button @click="handleLogout" class="hover:bg-white hover:bg-opacity-20 rounded-lg transition-all">
            <ion-icon slot="icon-only" :icon="logOutOutline"></ion-icon>
          </ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true">
      <div class="min-h-screen bg-gradient-to-br from-aviation-white to-gray-50 p-4 md:p-8">
        <div class="max-w-6xl mx-auto">
          <!-- Welcome Header -->
          <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Welcome Back!</h1>
            <p class="text-gray-600">Here's your dashboard overview</p>
          </div>

          <!-- User Profile Card -->
          <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8 mb-6 border border-gray-100">
            <div class="flex items-center mb-6">
              <div class="w-16 h-16 bg-aviation-olive rounded-full flex items-center justify-center text-white text-2xl font-bold mr-4">
                {{ user?.name?.charAt(0) || 'U' }}
              </div>
              <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ user?.name }}</h2>
                <span :class="getRoleBadgeClasses(user?.role)" class="inline-block px-3 py-1 rounded-full text-sm font-semibold mt-1">
                  {{ user?.role }}
                </span>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-600 mb-2">Email Address</h3>
                <p class="text-gray-800 font-medium">{{ user?.email }}</p>
              </div>
              <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-600 mb-2">Role</h3>
                <p class="text-gray-800 font-medium">{{ user?.role }}</p>
              </div>
            </div>
          </div>

          <!-- Dashboard Cards Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <!-- Quick Stats Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Quick Stats</h3>
                <div class="w-10 h-10 bg-aviation-olive bg-opacity-20 rounded-lg flex items-center justify-center">
                  <svg class="w-6 h-6 text-aviation-olive" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                  </svg>
                </div>
              </div>
              <p class="text-gray-600 text-sm">Your statistics and analytics will appear here</p>
            </div>

            <!-- Recent Activity Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Recent Activity</h3>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                  <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
              </div>
              <p class="text-gray-600 text-sm">Your recent activities will be listed here</p>
            </div>

            <!-- Notifications Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Notifications</h3>
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                  <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                  </svg>
                </div>
              </div>
              <p class="text-gray-600 text-sm">You have no new notifications</p>
            </div>
          </div>

          <!-- Main Dashboard Content -->
          <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8 border border-gray-100">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Dashboard</h2>
            <div class="border-t border-gray-200 pt-6">
              <p class="text-gray-600 mb-4">Welcome to your dashboard. This is where your main content will be displayed based on your role.</p>
              <div class="bg-aviation-olive bg-opacity-10 border border-aviation-olive border-opacity-30 rounded-lg p-4">
                <p class="text-aviation-olive font-medium">Your personalized content for the {{ user?.role }} role will appear here.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import {
  IonContent,
  IonHeader,
  IonPage,
  IonTitle,
  IonToolbar,
  IonButtons,
  IonButton,
  IonIcon,
} from '@ionic/vue';
import { logOutOutline } from 'ionicons/icons';

const router = useRouter();
const user = ref<any>(null);

onMounted(() => {
  const userStr = localStorage.getItem('user');
  if (userStr) {
    user.value = JSON.parse(userStr);
  }
});

const getRoleBadgeClasses = (role: string) => {
  const classes: any = {
    Admin: 'bg-red-100 text-red-800 border border-red-200',
    HR: 'bg-yellow-100 text-yellow-800 border border-yellow-200',
    Employees: 'bg-aviation-olive bg-opacity-20 text-aviation-olive border border-aviation-olive border-opacity-30',
    Staff: 'bg-blue-100 text-blue-800 border border-blue-200',
    Students: 'bg-green-100 text-green-800 border border-green-200',
  };
  return classes[role] || 'bg-gray-100 text-gray-800 border border-gray-200';
};

const handleLogout = () => {
  localStorage.removeItem('token');
  localStorage.removeItem('user');
  router.push('/login');
};
</script>
