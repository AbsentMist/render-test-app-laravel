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
      <MiniatureCourse :courses="courses" :evenement="evenement" mode="selection" @selectionner="$emit('selectionner', $event)"/>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import courseParticipantService from '../services/courseParticipantService';
import Title from './Title.vue';
import MiniatureCourse from './MiniatureCourse.vue';

const props = defineProps({
  evenement: { type: Object, required: true },
});

const emit = defineEmits(['selectionner']);

const courses = ref([]);
const chargement = ref(true);
const recherche = ref('');

// On utilise l'objet evenement passé en prop directement,
// plus besoin de le recharger
const evenement = computed(() => props.evenement);

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

const coursesFiltrees = computed(() =>
  courses.value.filter(c =>
    c.nom_course.toLowerCase().includes(recherche.value.toLowerCase())
  )
);

function formaterDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('fr-CH');
}

onMounted(chargerCourses);
</script>