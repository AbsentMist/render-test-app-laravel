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
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
        </svg>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Icon } from '@iconify/vue';
import { useRouter } from 'vue-router';

const props = defineProps({
  courses: { required: true, type: Array },
  evenement: { required: false, type: Object, default: null },
  mode: { type: String, default: 'navigation' }, // 'navigation' | 'selection'
});

const router = useRouter();
const emit = defineEmits(['selectionner']);

function handleClick(course) {
  emit('selectionner', course);
}

function formaterDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('fr-CH');
}
</script>