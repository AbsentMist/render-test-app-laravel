<template>
  <div class="min-h-screen">
    <!-- Navbar cachée sur les pages login/inscription -->
    <nav v-if="!isAuthPage" class="p-4 bg-white shadow-sm flex gap-4">
      <router-link to="/" class="text-blue-600 font-bold">Tableau de bord</router-link>
      <router-link to="/inscriptions" class="text-gray-600 hover:text-blue-600">Mes inscriptions</router-link>
      <router-link to="/resultats" class="text-gray-600 hover:text-blue-600">Mes résultats</router-link>
    </nav>

    <main>
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

// Masque la navbar sur les pages d'authentification
const isAuthPage = computed(() => {
  return ['login', 'inscription'].includes(route.name)
})
</script>