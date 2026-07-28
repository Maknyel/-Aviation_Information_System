<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
    <div class="text-center">
      <p class="text-6xl font-bold text-aviation-olive mb-2">404</p>
      <h1 class="text-2xl font-semibold text-gray-800 mb-2">Page not found</h1>
      <p class="text-gray-500 mb-6">The page you're looking for doesn't exist or has been moved.</p>
      <router-link
        to="/home"
        class="inline-block px-6 py-2 bg-aviation-olive text-white rounded-lg hover:bg-opacity-90 transition-all"
      >
        Go back home
      </router-link>
    </div>
  </div>
</template>
