<template>
  <div
    v-for="course in courses"
    :key="course.id"
    @click="handleClick(course)"
    class="p-1.5 rounded-2xl flex items-stretch shadow-sm border cursor-pointer transition-transform hover:-translate-y-0.5"
    :style="evenement ? { 
      borderColor: evenement.couleur_primaire + '40', 
      backgroundColor: evenement.couleur_secondaire + '26'
    } : { backgroundColor: '#ffffff', borderColor: '#f3f4f6' }"
  >
    <div
      class="w-48 md:w-56 rounded-xl border-2 border-white flex flex-col items-center justify-center p-4 text-center text-white shrink-0"
      :style="{ backgroundColor: evenement?.couleur_primaire || '#5e7082' }"
    >
      <h3 class="text-xl font-medium leading-tight whitespace-pre-line">{{ evenement?.nom }}</h3>
      <div class="mt-3 px-4 py-1 rounded-full text-sm text-white font-semibold tracking-wide bg-opacity-30" :style="{ backgroundColor: evenement?.couleur_secondaire + '30' }">
        <span v-if="course.date_debut === course.date_fin">
          {{ course.date_debut }}
        </span>
        <span v-else>
          {{ course.date_debut.split('-')[2] }}.{{ course.date_debut.split('-')[1] }} - {{ course.date_fin.split('-')[2] }}.{{ course.date_fin.split('-')[1] }}
        </span>
      </div>
    </div>
    <div class="flex-1 flex flex-col md:flex-row md:items-center justify-between p-4 pl-6">
      <div :style="{ color: evenement?.couleur_primaire }" class="w-full">
        <h4 class="text-xl font-semibold mb-2">{{ course.nom_course }}</h4>
        <div class="text-sm font-semibold">Tarif CHF {{ course.tarif }}</div>
        <div class="flex justify-between pr-4">
          <div class="text-xs opacity-80">+ Frais de plateforme et frais bancaire</div>
          <div class="text-xs font-semibold rounded-lg px-2 py-1 border-1" :style="{ borderColor: evenement?.couleur_primaire }">
            <span v-if="course.categorie">{{ course.categorie }}</span>
            <span v-if="course.sous_categorie"> - {{ course.sous_categorie }}</span>
          </div>
        </div>
        <div class="flex justify-between mt-4 items-center w-full pr-4">
          <div class="text-sm font-bold flex items-center gap-2">
            <Icon icon="mdi:ticket-confirmation" class="w-5 h-5" />
            Dossards restants : {{ course.dossards_restants }}
          </div>
          <div v-if="course.age_maximum" class="text-sm font-bold flex items-center gap-2">
              <Icon icon="mdi:account" class="w-5 h-5" />
              {{ course.age_minimum }} - {{ course.age_maximum }} ans
          </div>
          <div v-else class="text-sm font-bold flex items-center gap-2">
            <Icon icon="mdi:account" class="w-5 h-5" />
            {{ course.age_minimum }} ans et plus

          </div>
        </div>
      </div>
      <div class="flex items-center gap-8 mt-4 md:mt-0">
        <svg :style="{ color: evenement?.couleur_primaire }" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
        </svg>
      </div>
    </div>
  </div>
</template>

<script setup>
/**
 * @fileoverview Composant MiniatureCourse.
 * @description Carte de présentation d'une course avec actions contextuelles selon le mode d'usage.
 * @remarks En mode sélection, la carte émet la course choisie; en mode navigation,
 * la responsabilité du routage est laissée au parent selon le contexte d'affichage.
 */
import { Icon } from '@iconify/vue';

const props = defineProps({
  courses: { required: true, type: Array },
  evenement: { required: false, type: Object, default: null },
  mode: { type: String, default: 'navigation' }, // 'navigation' | 'selection'
});

const emit = defineEmits(['selectionner']);

/**
 * Relaye la sélection d'une course au composant parent.
 * @author Perroud Rémi
 * @param {Object} course Course sélectionnée.
 * @returns {void}
 */
function handleClick(course) {
  emit('selectionner', course);
}

/**
 * Formate la date d'évènement pour affichage localisé.
 * @author Perroud Rémi
 * @param {string} dateStr Date brute au format ISO.
 * @returns {string}
 */
</script>