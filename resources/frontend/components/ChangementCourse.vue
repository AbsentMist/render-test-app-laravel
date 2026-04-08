<template>
  <div class="flex flex-col md:flex-row md:items-center justify-between p-6 pb-0">
    <div class="relative w-full md:w-72">
      <input
        v-model="recherche"
        type="text"
        placeholder="Rechercher une course"
        class="w-full pl-4 pr-10 py-2.5 rounded-full bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-200 text-sm font-medium text-gray-700"
      />
      <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
    </div>
  </div>

  <div class="p-6">
    <div v-if="chargement" class="text-center py-10">Chargement des courses...</div>
    <div v-else class="flex flex-col gap-4">
      <MiniatureCourse :courses="coursesFiltrees" :evenement="evenement" mode="selection" @selectionner="$emit('selectionner', $event)"/>
    </div>
  </div>
</template>

<script setup>
/**
 * @fileoverview Composant ChangementCourse.
 * @description Action de changement de course à partir du contexte d'inscription courant.
 * @remarks Ce composant charge les courses de l'évènement cible, applique une recherche
 * locale et émet la course sélectionnée vers le parent.
 */
import { ref, computed, onMounted } from 'vue';
import courseParticipantService from '../services/courseParticipantService';
import MiniatureCourse from './MiniatureCourse.vue';

const props = defineProps({
  evenement: { type: Object, required: true },
});

const emit = defineEmits(['selectionner']);

const courses = ref([]);
const chargement = ref(true);
const recherche = ref('');

/**
 * Référence réactive de l'évènement transmis par le parent.
 * @type {import('vue').ComputedRef<Object>}
 */
const evenement = computed(() => props.evenement);

/**
 * Charge les courses liées à l'évènement sélectionné.
 * @returns {Promise<void>}
 */
async function chargerCourses() {
  try {
    const response = await courseParticipantService.getAllCourses(props.evenement.id);
    courses.value = response.data.courses;
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
const coursesFiltrees = computed(() =>
  courses.value.filter(c =>
    c.nom_course.toLowerCase().includes(recherche.value.toLowerCase())
  )
);

onMounted(chargerCourses);
</script>