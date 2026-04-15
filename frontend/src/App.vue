<script setup>
import { ref, onMounted } from 'vue'
import api from './api'

const health = ref('Checking...')
onMounted(async () => {
  try {
    const res = await api.get('/health')
    health.value = res.data.ok ? '✅ API Connected' : '❌ Offline'
  } catch {
    health.value = '❌ API Offline'
  }
})
</script>

<template>
  <v-app>
    <v-app-bar color="indigo-darken-3" app>
      <v-app-bar-title class="font-weight-bold">
        Media Analysis System Portfolio
      </v-app-bar-title>
      <v-chip size="small" color="green" variant="flat" class="mr-4">
        {{ health }}
      </v-chip>
      <v-spacer />
      <v-btn variant="text" to="/" router>Overview</v-btn>
      <v-btn variant="text" to="/listings" router>Listings</v-btn>
      <v-btn variant="text" to="/upload" router>Upload Demo</v-btn>
    </v-app-bar>

    <v-main class="pa-4">
      <router-view />
    </v-main>
  </v-app>
</template>
