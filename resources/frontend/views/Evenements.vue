
<template>
  <Title :texte="`Liste des évènements`" />
  <div class="p-6">
    <div v-if="chargement" class="text-center py-10 text-body">
      Chargement des évènements...
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <MiniatureEvenement :evenements="evenements"/>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import evenementParticipantService from '../services/evenementParticipantService';
import Title from '../components/Title.vue';
import MiniatureEvenement from '../components/MiniatureEvenement.vue';

const router = useRouter();
const evenements = ref([]);
const chargement = ref(true);

//CHARGEMENT DES DONNÉES
async function chargerEvenements() {
  try {
    const response = await evenementParticipantService.getAllEvenements();
    const data = response.data.data || response.data;

    // MiniatureEvenement attend logo_base64 décodé
    evenements.value = data.map(evt => ({
      ...evt,
      logo_base64: evt.logo_base64 ? atob(evt.logo_base64.split(',')[1]) : null
    }));
  } catch (error) {
    console.error("Erreur lors du chargement des évènements :", error);
  } finally {
    chargement.value = false;
  }
}

onMounted(() => {
  chargerEvenements();
});
</script>
