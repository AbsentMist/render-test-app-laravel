<template>
  <div class="flex flex-col md:flex-row md:items-center justify-between">
    <Title :texte="evenement?.nom ?? ''" :couleur="evenement?.couleur_secondaire" />
    <div class="relative w-full md:w-72">
      <input
        v-model="recherche"
        type="text"
        placeholder="Rechercher une course"
        class="w-full pl-4 pr-10 py-2.5 rounded-full bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-200 text-sm font-medium text-gray-700"
      />
      <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
      </div>
    </div>
  </div>

  <div class="p-6">
    <div v-if="chargement" class="text-center py-10">Chargement des courses...</div>
    <div v-else-if="courses.length === 0" class="flex flex-col items-center rounded-xl border border-default-medium bg-neutral-secondary-medium px-6 py-10 text-center">
      <Icon icon="mdi:info" class="w-8 h-8 text-gray-500 mx-auto mb-4"/>  
      <p class="text-heading font-medium">Cet évènement ne possède encore aucune course.</p>
    </div>    
    <div v-else class="flex flex-col gap-4">
      <MiniatureCourse 
        :courses="coursesFiltrees"
        :evenement="evenement"
        @selectionner="ouvrirInscription"
      />
    </div>
  </div>

  <PopupInscriptionCourse 
    v-if="popupInscription" 
    :course="courseSelectionnee"
    :participants="participants"
    :couleur-primaire="evenement?.couleur_primaire" 
    @close="popupInscription = false"
    @ajouter-panier="gererAjoutPanier"
  />
</template>

<script setup>
/**
 * @fileoverview Vue ListeCourses.
 * @description Liste des courses d'un évènement avec recherche et ouverture du tunnel d'inscription.
 * @remarks Cette vue synchronise le thème événementiel, récupère les participants du compte
 * et transmet les données d'inscription au panier.
 */
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { Icon } from '@iconify/vue';
import courseParticipantService from '../services/courseParticipantService';
import { useThemeStore } from '../stores/theme'; 
import Title from '../components/Title.vue';
import PopupInscriptionCourse from '../components/PopupInscriptionCourse.vue';
import { useAuthStore } from '../stores/auth';
import { useCartStore } from '../stores/cart'; 
import MiniatureCourse from '../components/MiniatureCourse.vue';
import participantService from '../services/participantService';

const authStore = useAuthStore();
const cartStore = useCartStore(); 

const participants = ref([]);

async function chargerParticipants() {
    try {
        const response = await participantService.getMesParticipants();
        participants.value = response.data;
    } catch (e) {
        // Fallback sur le participant principal du compte
        const p = authStore.user?.participant;
        participants.value = p ? [p] : [];
    }
}
const route = useRoute();
const themeStore = useThemeStore(); 
const evenement = ref(null);
const courses = ref([]);
const chargement = ref(true);
const recherche = ref('');
const popupInscription = ref(false);

const idEvenement = computed(() => route.params.idEvenement);

const courseSelectionnee = ref(null);

/**
 * Ouvre la popup d'inscription pour la course choisie.
 * @param {Object} course
 * @returns {void}
 */
function ouvrirInscription(course) {
    courseSelectionnee.value = course;
    popupInscription.value = true;
}

/**
 * Ajoute l'inscription préparée au panier puis ferme la popup.
 * @param {Object} donneesInscription
 * @returns {void}
 */
function gererAjoutPanier(donneesInscription) {
    cartStore.ajouterInscription(donneesInscription, courseSelectionnee.value);
    popupInscription.value = false;
}

/**
 * Charge l'évènement courant et ses courses.
 * @returns {Promise<void>}
 */
async function chargerDonnees() {
  try {
    const response = await courseParticipantService.getAllCourses(idEvenement.value);
    evenement.value = response.data.evenement;
    courses.value = response.data.courses;

    if (evenement.value) {
        themeStore.setTheme(
            evenement.value.couleur_primaire,
            evenement.value.couleur_secondaire
        );
    }

  } catch (error) {
    console.error("Erreur chargement courses :", error);
  } finally {
    chargement.value = false;
  }
}

/**
 * Filtre les courses selon la recherche texte.
 * @type {import('vue').ComputedRef<Array>}
 */
const coursesFiltrees = computed(() => {
  return courses.value.filter(c => 
    c.nom_course.toLowerCase().includes(recherche.value.toLowerCase())
  );
});

// Formatage de la date (JJ.MM.AAAA)
function formaterDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('fr-CH');
}

onMounted(async () => {
    await chargerParticipants();
  chargerDonnees();
});
</script>