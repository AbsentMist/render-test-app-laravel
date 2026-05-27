<script setup>
/**
 * @fileoverview Composant Header.
 * @description En-tête principal de l'application avec gestion profil, invitations et mini-panier.
 * @remarks Orchestre l'état d'affichage entre menus profil/panier et adapte la navigation selon le rôle utilisateur.
 */
import { Icon } from '@iconify/vue';
import { useAuthStore } from '../stores/auth';
import { useThemeStore } from '../stores/theme';
import { useCartStore } from '../stores/cart'; 
import { useRouter } from 'vue-router';
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import groupeService from '../services/groupeService';
import echangeDossardService from '../services/echangeDossardService';
import api from '../services/api';
import PopupAccepterInvitationCourse from './PopupAccepterInvitationCourse.vue';

const authStore = useAuthStore();
const themeStore = useThemeStore();
const cartStore = useCartStore(); 
const router = useRouter();


const invitations = ref([]);
const demandesEchange = ref([]);
const notificationsInfo = ref([]);
const isProfileDropdownOpen = ref(false);
const deductionChangement = ref(0);
const notificationsRefreshIntervalId = ref(null);
const notificationsRefreshMs = 10000;
const invitationEnCoursAcceptation = ref(null);
const isCartButtonHovered = ref(false);
const isCartChevronHovered = ref(false);
const headerLogoUrl = ref(null);

/**
 * Retourne le style dynamique du bouton panier selon le thème et l'état hover.
 * @param {boolean} isHovered État du hover
 * @returns {object}
 */
const getCartButtonStyle = (isHovered) => {
  if (!themeStore.primaryColor) {
    // Sans thème personnalisé, utilise les couleurs par défaut
    return {
      backgroundColor: isHovered ? '#bfd309' : '#d9f20b',
    };
  }
  
  // Avec thème personnalisé, utilise la couleur secondaire
  const baseColor = themeStore.secondaryColor;
  // Réduit légèrement l'opacité pour le hover
  return {
    backgroundColor: isHovered ? baseColor + 'e6' : baseColor,
  };
};

/**
 * Observe le panier pour recalculer la déduction liée aux changements de course.
 */
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

/**
 * Total affiché dans le mini-panier après déduction éventuelle.
 * @returns {number}
 */
const totalMiniPanier = computed(() => {
  let st = cartStore.cartTotal - deductionChangement.value;
  return st > 0 ? st : 0; 
});

/**
 * Bascule entre l'affichage participant et administrateur.
 * @returns {Promise<void>}
 */
const handleToggleMode = async () => {
  authStore.toggleAdminMode();
  if (authStore.showAdminLayout) {
    router.push('/organisateur/evenements');
  } else {
    router.push('/accueil');
  }
};

/**
 * Nom d'affichage utilisateur selon le rôle et les données disponibles.
 * @returns {{top: string, bottom: string}}
 */
const userDisplayName = computed(() => {
  if (authStore.isAdmin) {
    return { top: 'Rôle', bottom: 'Administrateur' };
  }
  const prenom = authStore.user?.participant?.prenom || 'Utilisateur';
  const nom = authStore.user?.participant?.nom || '';
  return { top: prenom, bottom: nom.toUpperCase() };
});

/**
 * Source de l'avatar utilisateur.
 * Utilise la photo participant si disponible, sinon null pour afficher l'icone par defaut.
 * @returns {string|null}
 */
const profileAvatarSource = computed(() => {
  const photo = authStore.user?.participant?.photo;
  if (!photo) return null;
  return photo.startsWith('data:') ? photo : `data:image/jpeg;base64,${photo}`;
});

/**
 * Ferme le mini-panier puis navigue vers la page panier.
 * @returns {void}
 */
const allerAuPanier = () => {
  cartStore.fermerDropdown();
  isProfileDropdownOpen.value = false;
  router.push('/panier');
};

/**
 * Charge les invitations en attente pour le participant connecté.
 * @returns {Promise<void>}
 */
const chargerInvitations = async () => {
  if (authStore.user?.participant) {
    try {
      const [groupesRes, echangesRes, infosRes] = await Promise.all([
        groupeService.getMesInvitations(),
        echangeDossardService.mesDemandesRecues(),
        api.get('/participant/notifications-info'),
      ]);

      invitations.value = (groupesRes.data || []).map((invit) => ({
        ...invit,
        tag: 'Invitation à un groupe',
      }));

      demandesEchange.value = (echangesRes.data || []).map((demande) => ({
        ...demande,
        tag: 'Demande échange dossard',
      }));

      notificationsInfo.value = (Array.isArray(infosRes.data) ? infosRes.data : []).map((notification) => ({
        ...notification,
        tag: notification.title || 'Information',
      }));

    } catch (e) {
      console.error("Erreur lors du chargement des invitations", e);
    }
  }
};

const rafraichirNotifications = () => {
  chargerInvitations();
};

const totalNotifications = computed(() => invitations.value.length + demandesEchange.value.length + notificationsInfo.value.length);

onMounted(() => {
  chargerInvitations();

  notificationsRefreshIntervalId.value = window.setInterval(() => {
    rafraichirNotifications();
  }, notificationsRefreshMs);

  window.addEventListener('membership-notifications-updated', rafraichirNotifications);
  
  // Initialise le logo du header
  mettreAJourLogoHeader();
});

/**
 * Applique une teinte sur le logo afin de l'adapter à la palette de l'évènement.
 * @param {string} logoSrc Source de l'image à recolorer.
 * @param {string} couleur Couleur cible.
 * @returns {Promise<string>}
 */
async function coloriserLogo(logoSrc, couleur) {
  return new Promise((resolve) => {
    const img = new Image();
    img.onload = () => {
      const canvas = document.createElement('canvas');
      canvas.width = img.width;
      canvas.height = img.height;
      const ctx = canvas.getContext('2d');
      ctx.drawImage(img, 0, 0);
      ctx.globalCompositeOperation = 'source-atop';
      ctx.fillStyle = couleur;
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      resolve(canvas.toDataURL());
    };
    img.src = logoSrc;
  });
}

/**
 * Met à jour l'URL du logo du header en formatant et colorisant le logo du themeStore.
 * @returns {Promise<void>}
 */
const mettreAJourLogoHeader = async () => {
  if (themeStore.logo && themeStore.secondaryColor) {
    const logo = themeStore.logo;
    const logoDataUri = logo.startsWith('data:') ? logo : `data:image/png;base64,${logo}`;
    headerLogoUrl.value = await coloriserLogo(logoDataUri, themeStore.secondaryColor);
  } else {
    headerLogoUrl.value = null;
  }
};

/**
 * Observe les changements du logo du thème et met à jour l'affichage du header.
 */
watch(() => themeStore.logo, () => {
  mettreAJourLogoHeader();
});

/**
 * Colorise les logos des événements présents dans le mini-panier.
 * @param {Array} panier Liste des articles du panier
 * @returns {Promise<void>}
 */
const coloriserLogosParier = async (panier = cartStore.inscriptions) => {
  for (const item of panier) {
    if (item.courseDetails?.evenement && item.courseDetails.evenement.couleur_secondaire && !item.courseLogoColorized) {
      try {
        const logo = item.courseDetails.evenement.logo_base64 || item.courseDetails.evenement.logo;
        if (logo) {
          const logoDataUri = logo.startsWith('data:') ? logo : `data:image/png;base64,${logo}`;
          item.courseLogoColorized = await coloriserLogo(logoDataUri, item.courseDetails.evenement.couleur_secondaire);
        }
      } catch (e) {
        console.error('Erreur colorisation logo:', e);
      }
    }
  }
};

/**
 * Observe le panier pour coloriser les logos des événements.
 */
watch(() => cartStore.inscriptions, async (nouveauPanier) => {
  await coloriserLogosParier(nouveauPanier);
}, { deep: true });

onBeforeUnmount(() => {
  if (notificationsRefreshIntervalId.value) {
    window.clearInterval(notificationsRefreshIntervalId.value);
    notificationsRefreshIntervalId.value = null;
  }

  window.removeEventListener('membership-notifications-updated', rafraichirNotifications);
});

watch(isProfileDropdownOpen, (isOpen) => {
  if (isOpen) {
    rafraichirNotifications();
  }
});

/**
 * Ouvre/ferme le menu profil et garantit l'exclusivité avec le panier.
 * @returns {void}
 */
const toggleProfileDropdown = () => {
  isProfileDropdownOpen.value = !isProfileDropdownOpen.value;
  if (isProfileDropdownOpen.value) {
    cartStore.fermerDropdown();
  }
};

/**
 * Ouvre/ferme le mini-panier et garantit l'exclusivité avec le profil.
 * @returns {void}
 */
const toggleCartDropdown = () => {
  cartStore.toggleDropdown();
  if (cartStore.isDropdownOpen) {
    isProfileDropdownOpen.value = false;
  }
};

/**
 * Indique si une invitation est expirée selon la date de fin d'inscription de la course.
 * @param {object} invit
 * @returns {boolean}
 */
const estInvitationExpiree = (invit) => {
  if (!invit.course?.fin_inscription) return false;
  const fin = new Date(invit.course.fin_inscription);
  fin.setHours(23, 59, 59, 999);
  return new Date() > fin;
};

/**
 * Affiche le popup pour accepter une invitation avec questionnaire, ou accepte directement si pas de questionnaire.
 * @param {object} invit L'invitation à accepter
 * @returns {Promise<void>}
 */
const afficherPopupAccepterInvitation = async (invit) => {
  // Si la course n'a pas de questionnaire, accepter directement
  if (!invit.course?.is_questionnaire) {
    try {
      await groupeService.accepterInvitation(invit.id);
      invitations.value = invitations.value.filter(g => g.id !== invit.id);
      alert("Invitation acceptée ! Vous êtes maintenant validé dans le groupe.");
    } catch (error) {
      console.error("Erreur lors de l'acceptation :", error);
      alert("Une erreur est survenue lors de l'acceptation de l'invitation.");
    }
    return;
  }
  
  // Sinon, afficher le popup pour remplir le questionnaire
  invitationEnCoursAcceptation.value = invit;
};

/**
 * Ferme le popup d'acceptation d'invitation.
 * @returns {void}
 */
const fermerPopupAccepterInvitation = () => {
  invitationEnCoursAcceptation.value = null;
};

/**
 * Traite l'acceptation réussie de l'invitation depuis le popup.
 * Retire l'invitation de la liste et ferme le popup.
 * @param {object} data Données d'acceptation ({ idGroupe, reponses })
 * @returns {void}
 */
const onInvitationAcceptee = (data) => {
  invitations.value = invitations.value.filter(g => g.id !== data.idGroupe);
  fermerPopupAccepterInvitation();
  alert("Invitation acceptée ! Vous êtes maintenant validé dans le groupe.");
};

/**
 * Refuse une invitation groupe et met à jour la liste locale.
 * @param {number} idGroupe
 * @returns {Promise<void>}
 */
const refuserInvitation = async (idGroupe) => {
  try {
    await groupeService.refuserInvitation(idGroupe);
    invitations.value = invitations.value.filter(g => g.id !== idGroupe);

    alert("L'invitation a bien été refusée/supprimée."); 
    
  } catch (error) {
    console.error("Erreur lors du refus :", error);
    alert("Une erreur est survenue lors de l'action."); 
  }
};

/**
 * Supprime une notification d'information côté serveur et la retire de l'affichage local.
 * @param {number} idNotification
 * @returns {Promise<void>}
 */
const supprimerNotificationInfo = async (idNotification) => {
  try {
    await api.delete(`/participant/notifications-info/${idNotification}`);
    notificationsInfo.value = notificationsInfo.value.filter((notification) => notification.id !== idNotification);
  } catch (error) {
    console.error('Erreur lors de la suppression de la notification', error);
  }
};

const isMembershipInfoNotification = (notification) => [
  'membership_approved_info',
  'membership_refused_info',
  'new_membership_request',
  'membership_invitation_to_complete',
  'membership_invitation_info',
  'membership_invitation_cancelled_info',
  'membership_invitation_cancelled_participant',
].includes(notification?.type);

const getInfoNotificationCardClass = (notification) => {
  if (isMembershipInfoNotification(notification)) {
    return 'bg-blue-50 border border-blue-200';
  }
  return 'bg-amber-50 border border-amber-200';
};

const getInfoNotificationTagClass = (notification) => {
  if (isMembershipInfoNotification(notification)) {
    return 'bg-blue-100 text-blue-700 border border-blue-200';
  }
  return 'bg-amber-100 text-amber-700 border border-amber-200';
};

const getInfoNotificationIcon = (notification) => {
  if (isMembershipInfoNotification(notification)) {
    return 'mdi:account-group-outline';
  }
  return 'mdi:information-outline';
};

const getInfoNotificationRoute = (notification) => {
  if (notification?.type === 'membership_invitation_to_complete') {
    return '/membership';
  }
  return null;
};

const ouvrirNotificationInfo = async (notification) => {
  const route = getInfoNotificationRoute(notification);
  if (route) {
    isProfileDropdownOpen.value = false;
    await router.push(route);
    await supprimerNotificationInfo(notification.id);
  }
};

/**
 * Déconnecte l'utilisateur via le store et redirige vers la page de login.
 * Garde le dropdown profil fermé et gère les erreurs silencieusement.
 */
const handleLogout = async () => {
  try {
    await authStore.logout();
  } catch (e) {
    console.error('Erreur lors de la déconnexion', e);
  } finally {
    isProfileDropdownOpen.value = false;
    try {
      await router.push('/login');
    } catch (e) {
      // Si le routeur est indisponible, on fait un fallback vers la page login
      window.location.href = '/login';
    }
  }
};

/**
 * Récupère la source du logo d'un événement formatée en data URI.
 * @param {object} evenement
 * @returns {string|null}
 */
const getLogoSource = (evenement) => {
  if (!evenement) return null;
  const logo = evenement.logo_base64 || evenement.logo;
  if (!logo) return null;
  return logo.startsWith('data:') ? logo : `data:image/png;base64,${logo}`;
};
</script>

<template>
  <PopupAccepterInvitationCourse
    v-if="invitationEnCoursAcceptation"
    :invitation="invitationEnCoursAcceptation"
    @close="fermerPopupAccepterInvitation"
    @accepte="onInvitationAcceptee"
  />

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
          <img v-if="headerLogoUrl" :src="headerLogoUrl" class="max-h-15 me-3 object-contain" alt="Logo événement" />
          <img v-else src="../assets/thumbnail_RGVA_LOGO_PRINCIPAL_BLANC_RVB.png" class="max-h-20 me-3" alt="Running Geneva Logo" />
        </router-link>
      </div>

      <div class="flex items-center gap-8 pr-4">

        <div class="relative hidden md:flex items-center">
          
          <div class="flex items-stretch shadow-sm rounded-xl transition-transform hover:scale-105">
            <button
              v-if="!authStore.showAdminLayout"
              @click="allerAuPanier"
              @mouseenter="isCartButtonHovered = true"
              @mouseleave="isCartButtonHovered = false"
              class="flex items-center gap-2 text-primary px-4 py-2 rounded-l-xl transition-colors font-bold text-sm"
              :style="getCartButtonStyle(isCartButtonHovered)"
              >
              <Icon icon="lucide:shopping-cart" class="w-4 h-4" />
              Panier
            </button>
            
            <button
              v-if="!authStore.showAdminLayout"
              @click="toggleCartDropdown"
              @mouseenter="isCartChevronHovered = true"
              @mouseleave="isCartChevronHovered = false"
              class="flex items-center justify-center text-primary px-2 rounded-r-xl border-l border-primary/20 transition-colors"
              :style="{ ...getCartButtonStyle(isCartChevronHovered), borderLeftColor: themeStore.primaryColor ? themeStore.primaryColor + '33' : 'rgba(0, 0, 0, 0.1)' }"
              >
              <Icon icon="lucide:chevron-down" class="w-4 h-4 transition-transform" :class="cartStore.isDropdownOpen ? 'rotate-180' : ''" />
            </button>
          </div>

          <span 
            v-if="cartStore.cartCount > 0 && !authStore.showAdminLayout" 
            class="absolute -top-2 -right-2  rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold shadow-sm pointer-events-none z-10 text-secondary bg-accent "
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
                      <img v-if="item.courseLogoColorized" :src="item.courseLogoColorized" class="absolute inset-0 w-full h-full object-contain p-1.5" />
                      <img v-else-if="getLogoSource(item.courseDetails?.evenement)" :src="getLogoSource(item.courseDetails?.evenement)" class="absolute inset-0 w-full h-full object-contain p-1.5" />
                      <span v-else class="text-[10px] text-white font-bold text-center px-1 leading-tight relative z-10">{{ item.courseDetails?.evenement?.nom || 'Course' }}</span>
                    </div>
                    
                    <div class="flex-1 flex flex-col justify-between">
                      <div>
                        <h3 v-if="item.type === 'options_supplementaires'" class="text-[1.1rem] font-medium text-[#0e0f54] leading-tight mb-1">Ajout d'option</h3>
                        <h3 v-else class="text-[1.1rem] font-medium text-[#0e0f54] leading-tight mb-1">{{ item.courseDetails?.nom_course || 'Nouvelle inscription' }}</h3>
                        <p v-if="item.type === 'options_supplementaires'" class="text-[0.8rem] text-[#0e0f54] font-semibold">
                          - {{ item.courseDetails?.nom  }} 
                        </p>
                        <p v-else class="flex text-[0.8rem] text-[#0e0f54] font-semibold">
                          <Icon :icon="'mdi:account'" class="w-4 h-4 mr-1" /> 
                          {{ (item.participant?.length ? item.participant : item.groupeEphemere?.participants || []).map(p => p.prenom + " " + p.nom).join(", ") }}
                        </p>
                        <div v-if="item.options && Object.keys(item.options).length > 0">
                          <p v-for="(opt, key) in item.options" :key="key" class="text-[0.8rem] text-[#0e0f54] font-sm mt-0.5 ml-2">
                            - {{ opt.quantite ? opt.quantite + ' x ' : '' }}{{ opt.option?.nom }}
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
          <Icon :icon="authStore.showAdminLayout ? 'lucide:settings' : 'lucide:eye'" class="w-4 h-4" />
          {{ authStore.showAdminLayout ? 'Vue Organisateur' : 'Vue Participant' }}
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
              <img
                v-if="profileAvatarSource"
                :src="profileAvatarSource"
                alt="Photo de profil"
                class="w-full h-full rounded-full object-cover"
              />
              <Icon v-else icon="lucide:circle-user-round" class="w-7 h-7" />
              
              <span
                v-if="totalNotifications > 0 && authStore.user?.participant"
                class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] font-bold border-2 shadow-sm pointer-events-none"
                :class="themeStore.primaryColor ? 'border-[var(--primary-color)]' : 'border-[#EAE6F5]'"
              >
                {{ totalNotifications }}
              </span>
            </button>

            <div
              v-if="isProfileDropdownOpen"
              class="absolute top-full right-0 mt-4 w-[320px] max-h-[calc(100vh-7rem)] overflow-hidden bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 z-50 cursor-default text-left flex flex-col"
            >
              <div class="absolute -top-2 right-4 w-4 h-4 bg-white rotate-45 border-l border-t border-gray-100"></div>

              <div class="flex items-center justify-between mb-2">
                 <h2 class="text-[1.35rem] font-medium text-[#0e0f54] leading-tight">Mon Profil</h2>
                 <router-link to="/profil" @click="isProfileDropdownOpen = false" class="text-xs text-blue-500 hover:text-blue-700 font-medium transition-colors">Voir mon compte</router-link>
              </div>
              <div class="h-1 w-12 rounded-r-full bg-red-200 mb-6"></div>

              <div v-if="authStore.user?.participant" class="flex flex-1 flex-col min-h-0">
                <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                  <Icon icon="lucide:mail" class="w-4 h-4" />
                  Mes notifications ({{ totalNotifications }})
                </h3>

                <div class="flex-1 min-h-0 overflow-y-auto pr-1 flex flex-col gap-3">
                  <div v-if="totalNotifications === 0" class="text-sm text-gray-500 italic text-center py-4 bg-gray-50 rounded-xl border border-gray-100">
                    Vous n'avez aucune notification en attente.
                  </div>

                  <div v-else class="flex flex-col gap-3">
                    <div v-for="invit in invitations" :key="`groupe-${invit.id}`" class="bg-white border border-gray-200 shadow-sm rounded-xl p-4">
                    <div class="flex justify-between items-start mb-1 gap-2">
                      <p class="font-bold text-[#0e0f54] text-sm">{{ invit.nom }}</p>
                      <span class="shrink-0 bg-slate-100 text-slate-700 text-[10px] font-bold px-2 py-0.5 rounded-full border border-slate-200">
                        {{ invit.tag }}
                      </span>
                    </div>

                    <div class="flex justify-between items-start mb-1">
                      <span v-if="estInvitationExpiree(invit)" class="bg-red-100 text-red-600 text-[10px] font-bold px-2 py-0.5 rounded-full border border-red-200">
                        Expirée
                      </span>
                    </div>
                    
                    <p class="text-xs text-gray-500 mb-3 font-medium">Invitation à rejoindre un groupe {{ invit.type }}</p>

                    <div class="flex gap-2 mt-2">
                      <button
                        v-if="!estInvitationExpiree(invit)"
                        @click="afficherPopupAccepterInvitation(invit)"
                        class="flex-1 bg-[#d9f20b] hover:bg-[#c4da0a] text-[#0e0f54] py-2 rounded-lg text-xs font-bold transition-colors shadow-sm"
                      >
                        Accepter
                      </button>
                      
                      <button
                        @click="refuserInvitation(invit.id)"
                        class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-100 py-2 rounded-lg text-xs font-bold transition-colors"
                        :class="estInvitationExpiree(invit) ? 'w-full' : 'flex-1'"
                      >
                        {{ estInvitationExpiree(invit) ? 'Supprimer l\'invitation' : 'Refuser' }}
                      </button>
                    </div>
                  </div>

                    <div v-if="demandesEchange.length > 0" class="pt-2 border-t border-gray-100">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3 flex items-center gap-2">
                      <Icon icon="mdi:swap-horizontal" class="w-4 h-4 text-blue-500" />
                      Demandes d'échange de dossard
                    </h4>

                    <div class="flex flex-col gap-3">
                      <div v-for="demande in demandesEchange" :key="`echange-${demande.id}`" class="bg-white border border-gray-200 shadow-sm rounded-xl p-4">
                        <div class="flex flex-col gap-2 mb-1">
                          <p class="font-bold text-[#0e0f54] text-sm leading-snug">{{ demande.course?.evenement?.nom }} · {{ demande.course?.nom }}</p>
                          <span class="shrink-0 bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded-full border border-blue-200">
                            {{ demande.tag }}
                          </span>
                        </div>

                        <p class="text-xs text-gray-500 mb-3 font-medium">
                          Dossard n°{{ demande.ancienne_inscription?.dossard?.numero ?? '—' }} · Envoyé par {{ demande.ancienne_inscription?.participant?.user?.email ?? '—' }}
                        </p>

                        <router-link
                          to="/echange-dossard"
                          class="inline-flex items-center justify-center w-full bg-[#0e0f54] hover:bg-[#0e0f54]/90 text-white py-2 rounded-lg text-xs font-bold transition-colors shadow-sm"
                        >
                          Voir la demande
                        </router-link>
                      </div>
                    </div>
                  </div>

                    <div v-if="notificationsInfo.length > 0" class="pt-2 border-t border-gray-100">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3 flex items-center gap-2">
                      <Icon icon="mdi:information-outline" class="w-4 h-4 text-amber-500" />
                      Notifications d'information
                    </h4>

                    <div class="flex flex-col gap-3">
                      <div
                        v-for="notification in notificationsInfo"
                        :key="`info-${notification.id}`"
                        class="shadow-sm rounded-xl p-4"
                        :class="getInfoNotificationCardClass(notification)"
                      >
                        <div class="flex flex-col gap-2 mb-1">
                          <div class="flex items-center gap-2">
                            <Icon :icon="getInfoNotificationIcon(notification)" class="w-4 h-4 text-[#0e0f54]" />
                            <p class="font-bold text-[#0e0f54] text-sm leading-snug">{{ notification.title }}</p>
                          </div>
                          <span class="shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full w-fit" :class="getInfoNotificationTagClass(notification)">
                            {{ notification.tag }}
                          </span>
                        </div>

                        <p class="text-xs text-gray-600 font-medium">
                          {{ notification.content }}
                        </p>

                        <div class="mt-3 flex justify-end gap-2">
                          <button
                            v-if="getInfoNotificationRoute(notification)"
                            type="button"
                            @click="ouvrirNotificationInfo(notification)"
                            class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wide bg-[#0e0f54] text-white border border-[#0e0f54] hover:bg-[#0e0f54]/90 transition-colors"
                          >
                            Voir
                          </button>
                          <button
                            type="button"
                            @click="supprimerNotificationInfo(notification.id)"
                            class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wide bg-white text-gray-700 border border-gray-200 hover:bg-gray-100 transition-colors"
                          >
                            Fermer
                          </button>
                        </div>
                      </div>
                    </div>
                    </div>
                  </div>
                </div>

                <div class="pt-4 mt-4 border-t border-gray-100 shrink-0">
                  <button
                    type="button"
                    @click="handleLogout"
                    data-testid="profile-logout-button"
                    class="inline-flex items-center gap-2 rounded-lg px-2 py-1 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 hover:text-red-700"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span>Se déconnecter</span>
                  </button>
                </div>
              </div>

            </div>
          </div>
        </div>

      </div>
    </div>
  </nav>
</template>