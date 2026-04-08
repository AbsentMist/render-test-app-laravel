<script setup>
/**
 * @fileoverview Composant SideBarAdmin.
 * @description Barre latérale de navigation pour l'interface administrateur/organisateur.
 * @remarks La sidebar adapte son style à la couleur de thème active et centralise
 * la déconnexion depuis l'espace d'administration.
 */
import { Icon } from '@iconify/vue';
import { useAuthStore } from '../stores/auth';
import { useThemeStore } from '../stores/theme';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const themeStore = useThemeStore();
const router = useRouter();

/**
 * Déconnecte l'utilisateur puis redirige vers l'écran de connexion.
 * @returns {Promise<void>}
 */
const handleLogout = async () => {
  await authStore.logout(); 
  router.push('/login'); 
};
</script>

<template>
  <aside 
    id="separator-sidebar" 
    class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-all duration-300 -translate-x-full sm:translate-x-0 shadow-lg border-r" 
    aria-label="Sidebar"
    :class="themeStore.primaryColor ? '' : 'bg-primary-300 text-secondary border-primary-900/20'"
    :style="themeStore.primaryColor ? { 
        backgroundColor: themeStore.primaryColor + '1A', 
        color: '#0e0f54', 
        borderColor: themeStore.primaryColor + '33' 
    } : {}"
  >
     <div class="h-full px-4 pb-4 overflow-y-auto">
        <ul class="space-y-1 text-[1rem] leading-[1.5rem] mt-4">
           <li>
              <router-link 
                to="/organisateur/evenements" 
                class="flex items-center px-3 py-2.5 rounded-lg transition-all duration-200" 
                :class="themeStore.primaryColor ? 'hover:bg-white/50 text-[#0e0f54]' : 'text-secondary hover:bg-tertiary hover:text-primary'"
                :active-class="themeStore.primaryColor ? 'bg-white shadow-sm font-bold' : 'bg-tertiary !text-primary'"
              >
                 <Icon icon="lucide:monitor" class="w-5 h-5 opacity-90 transition duration-75" />
                 <span class="ms-3 font-medium">Tableau de bord</span>
              </router-link>
           </li>
           
           <li>
              <router-link 
                to="/organisateur/inscriptions" 
                class="flex items-center px-3 py-2.5 rounded-lg transition-all duration-200" 
                :class="themeStore.primaryColor ? 'hover:bg-white/50 text-[#0e0f54]' : 'text-secondary hover:bg-tertiary hover:text-primary'"
                :active-class="themeStore.primaryColor ? 'bg-white shadow-sm font-bold' : 'bg-tertiary !text-primary'"
              >
                 <Icon icon="lucide:clipboard-list" class="w-5 h-5 opacity-90 transition duration-75" />
                 <span class="ms-3 font-medium">Inscription</span>
              </router-link>
           </li>

           <li>
              <router-link 
                to="/organisateur/formulaires" 
                class="flex items-center px-3 py-2.5 rounded-lg transition-all duration-200" 
                :class="themeStore.primaryColor ? 'hover:bg-white/50 text-[#0e0f54]' : 'text-secondary hover:bg-tertiary hover:text-primary'"
                :active-class="themeStore.primaryColor ? 'bg-white shadow-sm font-bold' : 'bg-tertiary !text-primary'"
              >
                 <Icon icon="lucide:id-card" class="w-5 h-5 opacity-90 transition duration-75" />
                 <span class="ms-3 font-medium">Formulaires</span>
              </router-link>
           </li>
           
           <li>
            <button @click="handleLogout" class="flex items-center px-3 py-2.5 rounded-lg text-red-500 hover:bg-red-50 transition-colors w-full text-left mt-2">
               <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
               <span class="font-medium">Se déconnecter</span>
            </button>
         </li>
        </ul>
     </div>
  </aside>
</template>