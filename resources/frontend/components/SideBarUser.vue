<script setup>
import { Icon } from '@iconify/vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const handleLogout = async () => {
  await authStore.logout(); 
  router.push('/login');   
};
</script>

<template>
  <aside id="separator-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full sm:translate-x-0 bg-primary-300 text-secondary shadow-lg border-r border-primary-900/20" aria-label="Sidebar">
     
     <div class="h-full px-4 pb-4 overflow-y-auto">
        <ul class="space-y-1 text-[1rem] leading-[1.5rem] mt-4">
           <li>
              <router-link to="/accueil" class="flex items-center px-3 py-2.5 rounded-lg text-secondary hover:bg-tertiary hover:text-primary transition-colors duration-200" active-class="bg-tertiary !text-primary">
                 <Icon icon="lucide:monitor" class="w-5 h-5 opacity-90 transition duration-75" />
                 <span class="ms-3 font-medium">Tableau de bord</span>
              </router-link>
           </li>
           
           <li>
              <router-link to="/evenements" class="flex items-center px-3 py-2.5 rounded-lg text-secondary hover:bg-tertiary hover:text-primary transition-colors duration-200" active-class="bg-tertiary !text-primary">
                 <Icon icon="lucide:calendar-days" class="w-5 h-5 opacity-90 transition duration-75" />
                 <span class="ms-3 font-medium">Liste des évènements</span>
              </router-link>
           </li>

           <li>
              <router-link to="/inscriptions" class="flex items-center px-3 py-2.5 rounded-lg text-secondary hover:bg-tertiary hover:text-primary transition-colors duration-200" active-class="bg-tertiary !text-primary">
                 <Icon icon="lucide:clipboard-list" class="w-5 h-5 opacity-90 transition duration-75" />
                 <span class="ms-3 font-medium">Mes inscriptions</span>
              </router-link>
           </li>
           
           <li>
              <router-link to="/resultats" class="flex items-center px-3 py-2.5 rounded-lg text-secondary hover:bg-tertiary hover:text-primary transition-colors duration-200" active-class="bg-tertiary !text-primary">
                 <Icon icon="lucide:medal" class="w-5 h-5 opacity-90 transition duration-75" />
                 <span class="ms-3 font-medium">Mes résultats</span>
              </router-link>
           </li>

           <li>
              <router-link to="/membership" class="flex items-center px-3 py-2.5 rounded-lg text-secondary hover:bg-tertiary hover:text-primary transition-colors duration-200" active-class="bg-tertiary !text-primary">
                 <Icon icon="lucide:id-card" class="w-5 h-5 opacity-90 transition duration-75" />
                 <span class="ms-3 font-medium">Membership</span>
              </router-link>
           </li>

           <li>
              <router-link to="/course-collecte" class="flex items-center px-3 py-2.5 rounded-lg text-secondary hover:bg-tertiary hover:text-primary transition-colors duration-200" active-class="bg-tertiary !text-primary">
                 <Icon icon="lucide:hand-coins" class="w-5 h-5 opacity-90 transition duration-75" />
                 <span class="ms-3 font-medium">Course de collecte</span>
              </router-link>
           </li>

           <li>
              <router-link to="/echange-dossard" class="flex items-center px-3 py-2.5 rounded-lg text-secondary hover:bg-tertiary hover:text-primary transition-colors duration-200" active-class="bg-tertiary !text-primary">
                 <Icon icon="lucide:arrow-right-left" class="w-5 h-5 opacity-90 transition duration-75" />
                 <span class="ms-3 font-medium">Echange de dossard</span>
              </router-link>
           </li>
           <li>
            <button @click="handleLogout" class="flex items-center px-3 py-2.5 rounded-lg text-red-500 hover:bg-red-50 transition-colors w-full text-left">
               <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
               <span class="font-medium">Se déconnecter</span>
            </button>
         </li>
        </ul>
     </div>
  </aside>
</template>