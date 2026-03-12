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
      <div
        v-for="course in coursesFiltrees"
        :key="course.id"
        @click="$emit('selectionner', course)"
        class="p-1.5 rounded-2xl flex items-stretch shadow-sm border cursor-pointer transition-transform hover:-translate-y-0.5"
        :style="{ borderColor: evenement.couleur_primaire + '40', backgroundColor: evenement.couleur_secondaire + '26' }"
      >
        <div
          class="w-48 md:w-56 rounded-xl border-2 border-white flex flex-col items-center justify-center p-4 text-center text-white shrink-0"
          :style="{ backgroundColor: evenement?.couleur_primaire || '#5e7082' }"
        >
          <h3 class="text-xl font-medium leading-tight whitespace-pre-line">{{ evenement?.nom }}</h3>
          <div class="mt-3 px-4 py-1 rounded-full text-sm font-semibold tracking-wide bg-opacity-30 bg-black">
            {{ formaterDate(evenement?.date) }}
          </div>
        </div>

        <div class="flex-1 flex flex-col md:flex-row md:items-center justify-between p-4 pl-6">
          <div :style="{ color: evenement?.couleur_primaire }">
            <h4 class="text-xl font-semibold mb-2">{{ course.nom_course }}</h4>
            <div class="text-sm font-semibold">Tarif CHF {{ course.tarif }}</div>
            <div class="text-xs opacity-80">+ Frais de plateforme et frais bancaire</div>
            <div class="text-sm font-bold mt-4 flex items-center gap-2">
              <Icon icon="mdi:ticket-confirmation" class="w-5 h-5" />
              Dossards restants : {{ course.dossards_restants }}
            </div>
          </div>
          <div class="flex items-center gap-8 mt-4 md:mt-0">
            <svg :style="{ color: evenement?.couleur_primaire }" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import courseParticipantService from '../services/courseParticipantService';
import Title from './Title.vue';

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