
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
      <div
        v-for="course in coursesFiltrees"
        :key="course.id"
        @click="ouvrirInscription(course)"
        class="p-1.5 rounded-2xl flex items-stretch shadow-sm border cursor-pointer transition-transform hover:-translate-y-0.5"
        :style="evenement ? { 
            borderColor: evenement.couleur_primaire + '40', 
            backgroundColor: evenement.couleur_secondaire + '26' /* 26 = 15% d'opacité. Tu peux mettre '33' pour 20% si tu veux un peu plus foncé */
        } : { backgroundColor: '#ffffff', borderColor: '#f3f4f6' }" 
      >
        <div 
          class="w-48 md:w-56 rounded-xl border-2 border-white flex flex-col items-center justify-center p-4 text-center text-white shrink-0"
          :style="{ backgroundColor: evenement?.couleur_primaire || '#5e7082' }"
        >
          <h3 class="text-xl font-medium leading-tight whitespace-pre-line">{{ evenement?.nom }}</h3>
          <div 
            class="mt-3 px-4 py-1 rounded-full text-sm font-semibold tracking-wide bg-opacity-30 bg-black"
          >
            {{ formaterDate(evenement?.date) }}
          </div>
        </div>

        <div class="flex-1 flex flex-col md:flex-row md:items-center justify-between p-4 pl-6">
          <div :style="{ color: evenement?.couleur_primaire }">
            <h4 class="text-xl font-semibold mb-2">{{ course.nom_course }}</h4>

            <div class="text-sm font-semibold">
              Tarif CHF {{ course.tarif }}
            </div>
            <div class="text-xs opacity-80">
              + Frais de plateforme et frais bancaire
            </div>

            <div class="text-sm font-bold mt-4 flex items-center gap-2">
              <Icon icon="mdi:ticket-confirmation" class="w-5 h-5" />
              Dossards restants : {{ course.dossards_restants }}
            </div>
          </div>

          <div class="flex items-center gap-8 mt-4 md:mt-0">
            <svg :style="{ color: evenement?.couleur_primaire }" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
            </svg>
          </div>
        </div>
      </div>
    </div>
  </div>
  <PopupInscriptionCourse v-if="popupInscription" :course="courseSelectionnee" :couleur-primaire="evenement?.couleur_primaire" @close="popupInscription = false"/>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { Icon } from '@iconify/vue';
import courseParticipantService from '../services/courseParticipantService';
import { useThemeStore } from '../stores/theme'; // 👈 1. Import du store
import Title from '../components/Title.vue';
import PopupInscriptionCourse from '../components/PopupInscriptionCourse.vue';

const route = useRoute();
const themeStore = useThemeStore(); // 👈 2. Initialisation du store
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
