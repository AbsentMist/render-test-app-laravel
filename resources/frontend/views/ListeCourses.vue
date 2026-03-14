
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
/></template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { Icon } from '@iconify/vue';
import courseParticipantService from '../services/courseParticipantService';
import { useThemeStore } from '../stores/theme'; // 👈 1. Import du store
import Title from '../components/Title.vue';
import PopupInscriptionCourse from '../components/PopupInscriptionCourse.vue';
import { useAuthStore } from '../stores/auth';
import MiniatureCourse from '../components/MiniatureCourse.vue';

const authStore = useAuthStore();
const participants = computed(() => {
    const p = authStore.user?.participant;
    return p ? [p] : [];
});
const route = useRoute();
const themeStore = useThemeStore(); // 2. Initialisation du store
const evenement = ref(null);
const courses = ref([]);
const chargement = ref(true);
const recherche = ref('');
const popupInscription = ref(false);

const idEvenement = computed(() => route.params.idEvenement);

const courseSelectionnee = ref(null);

function ouvrirInscription(course) {
    courseSelectionnee.value = course;
    popupInscription.value = true;
    console.log(course);
}
// ===== CHARGEMENT DES COURSES =====
async function chargerDonnees() {
  try {
    const response = await courseParticipantService.getAllCourses(idEvenement.value);
    evenement.value = response.data.evenement;
    courses.value = response.data.courses;

    // ✨ 3. C'EST ICI QUE LA MAGIE OPÈRE : 
    // On indique au Store les couleurs de l'évènement pour que la Sidebar et le Header changent !
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

// Filtre de recherche
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

onMounted(() => {
  chargerDonnees();
});
</script>
