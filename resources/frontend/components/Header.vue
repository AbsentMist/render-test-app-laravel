<script setup>
import { Icon } from '@iconify/vue';
</script>

<template>
  <nav class="fixed top-0 z-50 w-full bg-primary-900 border-b border-primary-900 shadow-sm h-20">
    <div class="px-3 lg:px-5 lg:pl-3 h-full flex items-center justify-between">
      
      <div class="flex items-center justify-start">
        <button data-drawer-target="separator-sidebar" data-drawer-toggle="separator-sidebar" aria-controls="separator-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-secondary rounded-lg sm:hidden hover:bg-primary-300 focus:outline-none focus:ring-2 focus:ring-tertiary">
          <span class="sr-only">Ouvrir le menu</span>
          <Icon icon="lucide:menu" class="w-6 h-6" />
        </button>
        
        <router-link to="/accueil" class="flex ms-2 md:me-24">
          <img src="../assets/thumbnail_RGVA_LOGO_PRINCIPAL_BLANC_RVB.png" class="h-12 me-3" alt="Running Geneva Logo" />
        </router-link>
      </div>

      <div class="flex items-center gap-8 pr-4">
        
        <div class="flex items-center gap-1">
          <router-link to="/panier" class="flex items-center gap-2 bg-tertiary hover:bg-tertiary-600 text-primary px-5 py-2.5 rounded-l-[18px] transition-colors group">
            <Icon icon="lucide:shopping-cart" class="w-5 h-5 transition-transform group-hover:scale-110" />
            <span class="font-bold text-[15px] hidden sm:block">Panier</span>
          </router-link>
          
          <button class="bg-tertiary hover:bg-tertiary-600 text-primary px-3 py-2.5 rounded-r-[18px] flex items-center justify-center transition-colors">
            <Icon icon="lucide:chevron-down" class="w-5 h-5" />
          </button>
        </div>

        <router-link to="/profil" class="flex items-center gap-4 hover:opacity-80 transition-opacity">
          <div class="flex flex-col items-start hidden sm:flex">
            <div class="w-8 h-[2px] bg-tertiary mb-1"></div>
            <span class="text-[15px] leading-tight font-medium text-secondary">Romeo</span>
            <span class="text-[15px] leading-tight font-bold text-secondary uppercase">SANTOS</span>
          </div>
          
          <div class="w-11 h-11 rounded-full bg-[#EAE6F5] flex items-center justify-center text-primary-900 border border-primary-900 shadow-inner">
            <Icon icon="lucide:circle-user-round" class="w-7 h-7" />
          </div>
        </router-link>

      </div>
    </div>
  </nav>
</template>