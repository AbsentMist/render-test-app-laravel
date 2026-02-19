<script setup>
import SidebarUser from './components/SideBarUser.vue';
import Header from './components/Header.vue';
import Footer from './components/Footer.vue';
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

// Masque la sidebar/header sur les pages d'authentification
const isAuthPage = computed(() => {
  return ['login', 'inscription'].includes(route.name)
})
</script>

<template>
  <div class="bg-secondary-600 font-sans min-h-screen">

    <template v-if="!isAuthPage">
      <Header />
      <SidebarUser />
    </template>

    <main :class="!isAuthPage ? 'sm:ml-64 pt-28 p-8 min-h-screen flex flex-col' : 'min-h-screen'">
      <div class="flex-grow">
        <router-view />
      </div>
      <Footer v-if="!isAuthPage" />
    </main>

  </div>
</template>