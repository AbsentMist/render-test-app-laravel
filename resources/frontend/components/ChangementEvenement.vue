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
/**
 * @fileoverview Composant ChangementEvenement.
 * @description Action de changement d'événement depuis une carte événement en mode organisateur.
 * @remarks Ce composant charge les évènements disponibles, prépare leurs données d'affichage
 * puis relaie la sélection vers le parent.
 */
import { ref, onMounted } from 'vue';
import evenementParticipantService from '../services/evenementParticipantService';
import MiniatureEvenement from './MiniatureEvenement.vue';

const emit = defineEmits(['selectionner']);

const evenements = ref([]);
const chargement = ref(true);

/**
 * Charge et normalise les évènements destinés à la sélection.
 * @returns {Promise<void>}
 */
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