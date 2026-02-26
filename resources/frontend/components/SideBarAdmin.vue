<script setup>
import { Icon } from '@iconify/vue';
</script>

<template>
  <aside id="separator-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full sm:translate-x-0 bg-primary-300 text-secondary shadow-lg border-r border-primary-900/20" aria-label="Sidebar">
     
     <div class="h-full px-4 pb-4 overflow-y-auto">
        <ul class="space-y-1 text-[1rem] leading-[1.5rem] mt-4">
           <li>
              <router-link to="/organisateur/evenements" class="flex items-center px-3 py-2.5 rounded-lg text-secondary hover:bg-tertiary hover:text-primary transition-colors duration-200" active-class="bg-tertiary !text-primary">
                 <Icon icon="lucide:monitor" class="w-5 h-5 opacity-90 transition duration-75" />
                 <span class="ms-3 font-medium">Tableau de bord</span>
              </router-link>
           </li>
           
           <li>
              <router-link to="/evenements" class="flex items-center px-3 py-2.5 rounded-lg text-secondary hover:bg-tertiary hover:text-primary transition-colors duration-200" active-class="bg-tertiary !text-primary">
                 <Icon icon="lucide:clipboard-list" class="w-5 h-5 opacity-90 transition duration-75" />
                 <span class="ms-3 font-medium">Inscription</span>
              </router-link>
           </li>

           <li>
              <router-link to="/organisateur/formulaires" class="flex items-center px-3 py-2.5 rounded-lg text-secondary hover:bg-tertiary hover:text-primary transition-colors duration-200" active-class="bg-tertiary !text-primary">
                 <Icon icon="lucide:id-card" class="w-5 h-5 opacity-90 transition duration-75" />
                 <span class="ms-3 font-medium">Formulaires</span>
              </router-link>
           </li>
        </ul>
     </div>
  </aside>
</template>