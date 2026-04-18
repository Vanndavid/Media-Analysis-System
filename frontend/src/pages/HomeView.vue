<script setup>
import { ref, onMounted } from 'vue'
import api from '../api'

const health = ref('Checking...')
const topVideos = ref([])
const loading = ref(true)

const highlights = [
  'Built with Laravel, Vue 3, Vuetify, MySQL, Redis, and Docker Compose',
  'Implements asynchronous processing with queue-driven video lifecycle states',
  'Captures engagement analytics events and surfaces top-played assets',
  'Demonstrates clean API integration, UI composition, and production-oriented architecture',
]

async function fetchData() {
  try {
    const res = await api.get('/health')
    health.value = res.data.ok ? '✅ API Connected' : '❌ Offline'

    const topRes = await api.get('/analytics/top-videos')
    topVideos.value = topRes.data.map((row) => ({
      id: row.video_id,
      title: row.title,
      source_url: row.source_url,
      status: row.status,
      thumbnail_url: row.thumbnail_url,
      plays: row.plays,
      listing: row.listing_title
        ? {
            title: row.listing_title,
            address: row.listing_address,
          }
        : null,
    }))
  } catch (e) {
    console.error('Error fetching data', e)
    health.value = '❌ API Offline'
  } finally {
    loading.value = false
  }
}

onMounted(fetchData)

async function logPlay(videoId) {
  try {
    await api.post(`/videos/${videoId}/events`, { event_type: 'PLAY' })
  } catch (e) {
    console.warn('Failed to log play event', e)
  }
}
</script>

<template>
  <v-container>
    <v-card class="mb-6 pa-5 rounded-lg" elevation="2">
      <div class="d-flex flex-wrap align-center justify-space-between ga-4">
        <div>
          <p class="text-overline text-indigo font-weight-bold mb-1">PROJECT SHOWCASE</p>
          <h1 class="text-h4 font-weight-bold mb-2">Media Analysis System</h1>
          <p class="text-body-1 text-medium-emphasis mb-0">
            A job-ready full-stack project focused on scalable media workflows,
            queue-backed processing, and analytics visibility.
          </p>
        </div>
        <v-chip color="green" variant="tonal" size="large">{{ health }}</v-chip>
      </div>

      <v-row class="mt-4">
        <v-col v-for="item in highlights" :key="item" cols="12" md="6">
          <v-sheet color="grey-lighten-4" class="pa-3 rounded">
            ✅ {{ item }}
          </v-sheet>
        </v-col>
      </v-row>
    </v-card>

    <v-card class="pa-4 rounded-lg" elevation="2">
      <div class="d-flex align-center justify-space-between mb-4">
        <h2 class="text-h6 mb-0">Top 5 Videos by Engagement</h2>
        <v-chip color="indigo" variant="outlined">Portfolio Evidence</v-chip>
      </div>

      <v-skeleton-loader v-if="loading" type="list-item@5" />

      <v-row v-else>
        <v-col
          v-for="v in topVideos"
          :key="v.id"
          cols="12"
          md="6"
          lg="4"
        >
          <v-card elevation="3" class="rounded-lg overflow-hidden h-100">
            <v-img
              v-if="v.thumbnail_url"
              :src="v.thumbnail_url"
              height="180"
              cover
            />

            <v-card-title class="font-weight-bold">{{ v.title }}</v-card-title>

            <v-card-subtitle class="text-caption">
              <span v-if="v.listing">🏠 {{ v.listing.title }} — {{ v.listing.address }}</span>
              <span v-else>(No listing metadata)</span>
            </v-card-subtitle>

            <v-card-subtitle class="text-caption mb-1">
              Plays: {{ v.plays }} |
              Status:
              <span :class="v.status === 'READY' ? 'text-success' : 'text-warning'">
                {{ v.status }}
              </span>
            </v-card-subtitle>

            <v-card-text>
              <video
                v-if="v.status === 'READY'"
                :src="v.source_url"
                controls
                width="100%"
                class="rounded"
                @play="logPlay(v.id)"
              />
              <v-alert v-else type="info" class="mt-2">Video still processing...</v-alert>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <v-alert
        v-if="!topVideos.length && !loading"
        type="info"
        class="mt-4"
      >
        No analytics data yet — play some videos to generate measurable engagement insights.
      </v-alert>
    </v-card>
  </v-container>
</template>
