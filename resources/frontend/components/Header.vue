<script setup>
import { Icon } from '@iconify/vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';
import { computed } from 'vue'; // 👈 Import de computed

const authStore = useAuthStore();
const router = useRouter();

// Fonction pour basculer de vue
const handleToggleMode = async () => {
  authStore.toggleAdminMode();
  if (authStore.showAdminLayout) {
    router.push('/organisateur/evenements');
  } else {
    router.push('/accueil');
  }
};

// ✨ NOUVEAU : Le rôle Admin reste figé pour toi, peu importe la vue
const userDisplayName = computed(() => {
  // 1. Si l'utilisateur est un Admin (dans la base de données), 
  // on affiche "Administrateur" en permanence.
  if (authStore.isAdmin) {
    return {
      top: 'Rôle',
      bottom: 'Administrateur'
    };
  }

  // 2. Pour les autres (simples participants), on affiche Prénom Nom
  const prenom = authStore.user?.participant?.prenom || 'Utilisateur';
  const nom = authStore.user?.participant?.nom || '';

  return {
    top: prenom,
    bottom: nom.toUpperCase()
  };
});
</script>

<template>
  <nav class="fixed top-0 z-50 w-full bg-primary-900 border-b border-primary-900 shadow-sm h-20">
    <div class="px-3 lg:px-5 lg:pl-3 h-full flex items-center justify-between">
      
      <div class="flex items-center justify-start">
        <button data-drawer-target="separator-sidebar" data-drawer-toggle="separator-sidebar" aria-controls="separator-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-secondary rounded-lg sm:hidden hover:bg-primary-300 focus:outline-none focus:ring-2 focus:ring-tertiary">
          <Icon icon="lucide:menu" class="w-6 h-6" />
        </button>
        <router-link to="/accueil" class="flex ms-2 md:me-24">
          <img src="../assets/thumbnail_RGVA_LOGO_PRINCIPAL_BLANC_RVB.png" class="h-12 me-3" alt="Running Geneva Logo" />
        </router-link>
      </div>

      <div class="flex items-center gap-8 pr-4">
        
        <button 
          v-if="authStore.isAdmin"
          @click="handleToggleMode()" 
          class="hidden md:flex items-center gap-2 bg-secondary hover:bg-secondary-600 text-primary-900 px-4 py-2 rounded-xl transition-colors font-bold text-sm"
        >
          <Icon :icon="authStore.showAdminLayout ? 'lucide:eye' : 'lucide:settings'" class="w-4 h-4" />
          {{ authStore.showAdminLayout ? 'Vue Participant' : 'Vue Organisateur' }}
        </button>

        <router-link to="/profil" class="flex items-center gap-4 hover:opacity-80 transition-opacity">
          <div class="flex flex-col items-start hidden sm:flex">
            <div class="w-8 h-[2px] bg-tertiary mb-1"></div>
            
            <span class="text-[15px] leading-tight font-medium text-secondary">
              {{ userDisplayName.top }}
            </span>
            <span class="text-[15px] leading-tight font-bold text-secondary uppercase">
              {{ userDisplayName.bottom }}
            </span>
          </div>
          
          <div class="w-11 h-11 rounded-full bg-[#EAE6F5] flex items-center justify-center text-primary-900 border border-primary-900 shadow-inner">
            <Icon icon="lucide:circle-user-round" class="w-7 h-7" />
          </div>
        </router-link>

      </div>
    </div>
  </nav>
</template>