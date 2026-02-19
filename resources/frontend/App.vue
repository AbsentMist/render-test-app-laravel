<script setup>
import SidebarUser from './components/SideBarUser.vue';
import Header from './components/Header.vue';
import Footer from './components/Footer.vue';
</script>

<template>
  <div class="bg-secondary-600 font-sans min-h-screen">
    
    <Header />
    
    <SidebarUser />

    <main class="sm:ml-64 pt-28 p-8 min-h-screen flex flex-col">
      
      <div class="flex-grow">
        <router-view />
      </div>

      <Footer />

    </main>

  </div>
</template>