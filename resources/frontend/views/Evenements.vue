<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import evenementParticipantService from '../services/evenementParticipantService';

const router = useRouter();
const evenements = ref([]);
const chargement = ref(true);

//CHARGEMENT DES DONNÉES
async function chargerEvenements() {
  try {
    const response = await evenementParticipantService.getAllEvenements();
    evenements.value = response.data;
  } catch (error) {
    console.error("Erreur lors du chargement des évènements :", error);
  } finally {
    chargement.value = false;
  }
}


function goToListeCourses(idEvenement) {
  router.push({ name: 'ListeCourses', params: { idEvenement } });
}

onMounted(() => {
  chargerEvenements();
});
</script>

<template>
  <div class="p-6">
    <div class="mb-8">
      <h2 class="text-2xl font-normal text-gray-900">Liste des évènements</h2>
      <div class="h-1 w-20 bg-pink-200 mt-2 rounded-full"></div>
    </div>

    <div v-if="chargement" class="text-center py-10 text-body">
      Chargement des évènements...
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="evt in evenements"
        :key="evt.id"
        @click="goToListeCourses(evt.id)"
        class="relative h-48 rounded-xl p-4 flex flex-col justify-end cursor-pointer transition-transform hover:-translate-y-1 shadow-sm"
        :style="{ backgroundColor: evt.couleur_primaire || '#53687e' }"
      >
        <div 
          class="absolute top-4 right-4 w-6 h-6 rounded-full border flex items-center justify-center text-xs font-medium"
          :style="{ borderColor: evt.couleur_secondaire, color: evt.couleur_secondaire }"
        >
          i
        </div>

        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none p-6">
          <img v-if="evt.logo" :src="evt.logo" class="max-h-24 object-contain" alt="Logo évènement" />
          <span v-else class="text-xl font-black tracking-widest text-center uppercase" :style="{ color: evt.couleur_secondaire }">
            {{ evt.nom }}
          </span>
        </div>

        <div class="relative z-10 font-normal text-base" :style="{ color: evt.couleur_secondaire }">
          {{ evt.nom }}
        </div>
      </div>
    </div>
  </div>
</template>