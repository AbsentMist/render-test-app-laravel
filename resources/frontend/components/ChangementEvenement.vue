<template>
  <div class="p-6">
    <div v-if="chargement" class="text-center py-10 text-body">
      Chargement des évènements...
    </div>
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <MiniatureEvenement :evenements="evenements" mode="selection" @selectionner="$emit('selectionner', $event)"/>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import evenementParticipantService from '../services/evenementParticipantService';
import Title from './Title.vue';
import MiniatureEvenement from './MiniatureEvenement.vue';

const emit = defineEmits(['ouvrir']);

const evenements = ref([]);
const chargement = ref(true);

async function chargerEvenements() {
  try {
    const response = await evenementParticipantService.getAllEvenements();
    const data = response.data.map(evt => ({
      ...evt,
      logo_base64: evt.logo_base64 ? atob(evt.logo_base64.split(',')[1]) : null
    }));
    evenements.value = await Promise.all(data);
  } catch (error) {
    console.error("Erreur lors du chargement des évènements :", error);
  } finally {
    chargement.value = false;
  }
}

onMounted(chargerEvenements);
</script>