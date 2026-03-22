<script setup>
import { Icon } from '@iconify/vue';
import { useAuthStore } from '../stores/auth';
import { useThemeStore } from '../stores/theme';
import { useCartStore } from '../stores/cart'; 
import { useRouter } from 'vue-router';
import { ref, computed, onMounted, watch } from 'vue';
import groupeService from '../services/groupeService';
import api from '../services/api';

const authStore = useAuthStore();
const themeStore = useThemeStore();
const cartStore = useCartStore(); 
const router = useRouter();


const invitations = ref([]);
const isProfileDropdownOpen = ref(false);

// 🌟 NOUVEAUTÉ : Calcul de la déduction en temps réel pour le mini-panier
const deductionChangement = ref(0);

watch(() => cartStore.inscriptions, async (nouveauPanier) => {
  let deduction = 0;
  for (const article of nouveauPanier) {
    if (article.ancienneInscriptionId) {
      try {
        const res = await api.get(`/participant/inscriptions/${article.ancienneInscriptionId}`);
        if (res.data && res.data.tarif) {
          deduction += parseFloat(res.data.tarif);
        }
      } catch (e) {
        console.error('Erreur récupération ancienne inscription', e);
      }
    }
  }
  deductionChangement.value = deduction;
}, { immediate: true, deep: true });

// Le total affiché dans le mini-panier
const totalMiniPanier = computed(() => {
  let st = cartStore.cartTotal - deductionChangement.value;
  return st > 0 ? st : 0; 
});

const handleToggleMode = async () => {
  authStore.toggleAdminMode();
  if (authStore.showAdminLayout) {
    router.push('/organisateur/evenements');
  } else {
    router.push('/accueil');
  }
};

const userDisplayName = computed(() => {
  if (authStore.isAdmin) {
    return { top: 'Rôle', bottom: 'Administrateur' };
  }
  const prenom = authStore.user?.participant?.prenom || 'Utilisateur';
  const nom = authStore.user?.participant?.nom || '';
  return { top: prenom, bottom: nom.toUpperCase() };
});

const allerAuPanier = () => {
  cartStore.fermerDropdown();
  isProfileDropdownOpen.value = false; // Ferme le profil si ouvert
  router.push('/panier');
};

//GESTION DES INVITATIONS

// Récupérer les invitations au chargement du Header
const chargerInvitations = async () => {
  if (authStore.user?.participant) {
    try {
      const res = await groupeService.getMesInvitations();
      invitations.value = res.data;
    } catch (e) {
      console.error("Erreur lors du chargement des invitations", e);
    }
  }
};

onMounted(() => {
  chargerInvitations();
});

// Gestion de l'ouverture du menu de profil
const toggleProfileDropdown = () => {
  isProfileDropdownOpen.value = !isProfileDropdownOpen.value;
  if (isProfileDropdownOpen.value) {
    cartStore.fermerDropdown();
  }
};

// Gestion de l'ouverture du menu panier
const toggleCartDropdown = () => {
  cartStore.toggleDropdown();
  if (cartStore.isDropdownOpen) {
    isProfileDropdownOpen.value = false;
  }
};

// Lors de l'acceptation d'une invitation
const accepterInvitation = async (idGroupe) => {
  try {
    await groupeService.accepterInvitation(idGroupe);
    invitations.value = invitations.value.filter(g => g.id !== idGroupe);
    alert("Invitation acceptée ! Vous êtes maintenant validé dans le groupe.");
  } catch (error) {
    console.error("Erreur lors de l'acceptation :", error);
  }
};

// Lors d'un refus d'une invitation
const refuserInvitation = async (idGroupe) => {
  try {
    await groupeService.refuserInvitation(idGroupe);
    invitations.value = invitations.value.filter(g => g.id !== idGroupe);
    
    //Message de confirmation du refus
    alert("L'invitation a bien été refusée. Le fondateur en sera informé."); 
    
  } catch (error) {
    console.error("Erreur lors du refus :", error);
    alert("Une erreur est survenue lors du refus de l'invitation."); 
  }
};
</script>

<template>
  <nav
    class="fixed top-0 z-50 w-full border-b shadow-sm h-20 transition-colors duration-300"
    :class="themeStore.primaryColor ? '' : 'bg-primary-900 border-primary-900'"
    :style="themeStore.primaryColor ? { backgroundColor: themeStore.primaryColor, borderBottomColor: themeStore.secondaryColor } : {}"
  >
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

        <div class="relative hidden md:flex items-center">
          
          <div class="flex items-stretch shadow-sm rounded-xl transition-transform hover:scale-105">
            <button
              v-if="!authStore.showAdminLayout"
              @click="allerAuPanier"
              class="flex items-center gap-2 bg-tertiary hover:bg-tertiary/90 text-primary px-4 py-2 rounded-l-xl transition-colors font-bold text-sm"
            >
              <Icon icon="lucide:shopping-cart" class="w-4 h-4" />
              Panier
            </button>
            
            <button
              v-if="!authStore.showAdminLayout"
              @click="toggleCartDropdown"
              class="flex items-center justify-center bg-tertiary hover:bg-tertiary/90 text-primary px-2 rounded-r-xl border-l border-primary/20 transition-colors"
            >
              <Icon icon="lucide:chevron-down" class="w-4 h-4 transition-transform" :class="cartStore.isDropdownOpen ? 'rotate-180' : ''" />
            </button>
          </div>

          <span 
            v-if="cartStore.cartCount > 0 && !authStore.showAdminLayout" 
            class="absolute -top-2 -right-2 bg-white text-tertiary rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold border-2 border-tertiary shadow-sm pointer-events-none z-10"
          >
            {{ cartStore.cartCount }}
          </span>

          <div 
            v-if="cartStore.isDropdownOpen && !authStore.showAdminLayout" 
            class="absolute top-full right-0 mt-4 w-[400px] bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 z-50 cursor-default text-left"
          >
            <div class="absolute -top-2 right-8 w-4 h-4 bg-white rotate-45 border-l border-t border-gray-100"></div>

            <h2 class="text-[1.35rem] font-medium text-[#0e0f54] mb-1">Ajouté au panier</h2>
            <div class="h-1 w-12 rounded-r-full bg-red-200 mb-6"></div>

            <div v-if="cartStore.cartCount === 0" class="text-center py-6">
              <Icon icon="lucide:shopping-bag" class="w-12 h-12 text-gray-300 mx-auto mb-2" />
              <p class="text-gray-500 font-medium">Votre panier est vide.</p>
            </div>
            
            <div v-else class="flex flex-col">
              
                <div class="max-h-[300px] overflow-y-auto mb-2 space-y-4 pr-2 pt-1">
                  <div v-for="(item, index) in cartStore.inscriptions" :key="index" class="flex gap-4 items-stretch border-b border-gray-100 pb-4 last:border-0 last:pb-0">
                    
                    <div 
                      class="w-[72px] h-[72px] rounded-xl flex items-center justify-center overflow-hidden shrink-0 shadow-sm relative"
                      :style="{ backgroundColor: item.courseDetails?.evenement?.couleur_primaire || '#5C8E9A' }"
                    >
                      <img v-if="item.courseDetails?.evenement?.logo_base64" :src="'data:image/png;base64,' + item.courseDetails.evenement.logo_base64" class="absolute inset-0 w-full h-full object-contain p-1.5" />
                      <span v-else class="text-[10px] text-white font-bold text-center px-1 leading-tight relative z-10">{{ item.courseDetails?.evenement?.nom || 'Course' }}</span>
                    </div>
                    
                    <div class="flex-1 flex flex-col justify-between">
                      <div>
                        <h3 class="text-[1.1rem] font-medium text-[#0e0f54] leading-tight mb-1">{{ item.courseDetails?.nom_course || 'Nouvelle inscription' }}</h3>
                        <p class="text-[0.8rem] text-[#0e0f54] font-semibold">
                          - {{ item.courseDetails?.categorie || 'Catégorie' }} <span v-if="item.courseDetails?.sous_categorie">• {{ item.courseDetails?.sous_categorie }}</span>
                        </p>
                        <div v-if="item.options && Object.keys(item.options).length > 0">
                          <p v-for="(opt, key) in item.options" :key="key" class="text-[0.8rem] text-[#0e0f54] font-semibold mt-0.5">
                            - {{ opt.quantite ? opt.quantite + ' ' : '1x ' }}{{ opt.option?.nom }}
                          </p>
                        </div>
                      </div>

                      <div class="text-right mt-2">
                        <span class="text-lg font-medium text-[#0e0f54]">{{ item.tarif || 0 }}.-</span>
                      </div>
                    </div>
                  </div>
                </div>

                <div v-if="deductionChangement > 0" class="flex justify-between items-center pt-4 border-t border-gray-200">
                  <span class="text-xs font-medium text-green-600">Déduction (Changement de course)</span>
                  <span class="text-sm font-bold text-green-600">- {{ deductionChangement.toFixed(2) }}.-</span>
                </div>

                <div class="flex justify-between items-center mb-6 pt-2" :class="deductionChangement === 0 ? 'border-t border-gray-200 mt-4' : ''">
                    <span class="text-sm font-medium text-gray-500">Total</span>
                    <span class="text-xl font-bold text-[#0e0f54]">{{ totalMiniPanier.toFixed(2) }}.-</span>
                </div>

                <div class="flex gap-3">
                  <button 
                    @click="cartStore.fermerDropdown()" 
                    class="flex-1 bg-[#0e0f54] text-white py-2.5 rounded-xl text-[0.95rem] font-medium hover:bg-[#0e0f54]/90 transition-colors"
                  >
                    Fermer
                  </button>
                  <button 
                    @click="allerAuPanier" 
                    class="flex-1 bg-[#d9f20b] text-[#0e0f54] py-2.5 rounded-xl text-[0.95rem] font-medium hover:bg-[#c4da0a] transition-colors shadow-sm"
                  >
                    Voir le panier
                  </button>
                </div>
            </div>
          </div>
        </div>

        <button
          v-if="authStore.isAdmin"
          @click="handleToggleMode()"
          class="hidden md:flex items-center gap-2 bg-secondary hover:bg-secondary-600 text-primary-900 px-4 py-2 rounded-xl transition-colors font-bold text-sm"
        >
          <Icon :icon="authStore.showAdminLayout ? 'lucide:eye' : 'lucide:settings'" class="w-4 h-4" />
          {{ authStore.showAdminLayout ? 'Vue Participant' : 'Vue Organisateur' }}
        </button>


        <div class="relative flex items-center gap-4">
          
          <router-link to="/profil" class="flex flex-col items-start hidden sm:flex hover:opacity-80 transition-opacity">
            <div
                class="w-8 h-[2px] mb-1"
                :class="themeStore.secondaryColor ? '' : 'bg-tertiary'"
                :style="themeStore.secondaryColor ? { backgroundColor: themeStore.secondaryColor } : {}"
            ></div>

            <span class="text-[15px] leading-tight font-medium" :class="themeStore.primaryColor ? 'text-white' : 'text-secondary'">
              {{ userDisplayName.top }}
            </span>
            <span class="text-[15px] leading-tight font-bold uppercase" :class="themeStore.primaryColor ? 'text-white' : 'text-secondary'">
              {{ userDisplayName.bottom }}
            </span>
          </router-link>

          <div class="relative">
            <button
              @click="toggleProfileDropdown"
              class="w-11 h-11 rounded-full flex items-center justify-center border shadow-inner transition-colors duration-300 relative"
              :class="themeStore.primaryColor ? 'text-white border-white bg-transparent hover:bg-white/10' : 'bg-[#EAE6F5] text-primary-900 border-primary-900 hover:bg-[#dcd6ee]'"
            >
              <Icon icon="lucide:circle-user-round" class="w-7 h-7" />
              
              <span
                v-if="invitations.length > 0 && authStore.user?.participant"
                class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] font-bold border-2 shadow-sm pointer-events-none"
                :class="themeStore.primaryColor ? 'border-[var(--primary-color)]' : 'border-[#EAE6F5]'"
              >
                {{ invitations.length }}
              </span>
            </button>

            <div
              v-if="isProfileDropdownOpen"
              class="absolute top-full right-0 mt-4 w-[320px] bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 z-50 cursor-default text-left"
            >
              <div class="absolute -top-2 right-4 w-4 h-4 bg-white rotate-45 border-l border-t border-gray-100"></div>

              <div class="flex items-center justify-between mb-2">
                 <h2 class="text-[1.35rem] font-medium text-[#0e0f54] leading-tight">Mon Profil</h2>
                 <router-link to="/profil" @click="isProfileDropdownOpen = false" class="text-xs text-blue-500 hover:text-blue-700 font-medium transition-colors">Voir mon compte</router-link>
              </div>
              <div class="h-1 w-12 rounded-r-full bg-red-200 mb-6"></div>

              <div v-if="authStore.user?.participant">
                <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                  <Icon icon="lucide:mail" class="w-4 h-4" />
                  Mes invitations ({{ invitations.length }})
                </h3>

                <div v-if="invitations.length === 0" class="text-sm text-gray-500 italic text-center py-4 bg-gray-50 rounded-xl border border-gray-100">
                  Vous n'avez aucune invitation en attente.
                </div>

                <div v-else class="flex flex-col gap-3 max-h-[300px] overflow-y-auto pr-1">
                  <div v-for="invit in invitations" :key="invit.id" class="bg-white border border-gray-200 shadow-sm rounded-xl p-4">
                    <p class="font-bold text-[#0e0f54] text-sm mb-1">{{ invit.nom }}</p>
                    <p class="text-xs text-gray-500 mb-3 font-medium">Invitation à rejoindre un groupe {{ invit.type }}</p>

                    <div class="flex gap-2 mt-2">
                      <button
                        @click="accepterInvitation(invit.id)"
                        class="flex-1 bg-[#d9f20b] hover:bg-[#c4da0a] text-[#0e0f54] py-2 rounded-lg text-xs font-bold transition-colors shadow-sm"
                      >
                        Accepter
                      </button>
                      <button
                        @click="refuserInvitation(invit.id)"
                        class="flex-1 bg-red-50 hover:bg-red-100 text-red-600 border border-red-100 py-2 rounded-lg text-xs font-bold transition-colors"
                      >
                        Refuser
                      </button>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>

      </div>
    </div>
  </nav>
</template>