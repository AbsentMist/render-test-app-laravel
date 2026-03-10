
<template>
  <Title :texte="`Liste des évènements`" />
  <div class="p-6">
    <div v-if="chargement" class="text-center py-10 text-body">
      Chargement des évènements...
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="evt in evenements"
        :key="evt.id"
        @click="goToListeCourses(evt.id)"
        class="relative h-48 rounded-xl p-4 flex flex-col justify-end cursor-pointer transition-transform hover:-translate-y-1 shadow-sm"
        :style="{ backgroundColor: evt.couleur_primaire || '#53687e', isolation: 'isolate' }"
      >
        <div 
          class="absolute top-4 right-4 w-6 h-6 rounded-full border flex items-center justify-center text-xs font-medium"
          :style="{ borderColor: evt.couleur_secondaire, color: evt.couleur_secondaire }"
        >
          i
        </div>

        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none p-6">
          <img v-if="evt.logo_colorise" 
            :src="evt.logo_colorise" class="max-h-16 max-w-48 object-contain" alt="Logo évènement" />
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

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import evenementParticipantService from '../services/evenementParticipantService';
import Title from '../components/Title.vue';

const router = useRouter();
const evenements = ref([]);
const chargement = ref(true);

//CHARGEMENT DES DONNÉES
async function chargerEvenements() {
  try {
    const response = await evenementParticipantService.getAllEvenements();
    const data = response.data.map(evt => ({
      ...evt,
      logo_base64: evt.logo_base64 ? atob(evt.logo_base64.split(',')[1]) : null
    }));

    evenements.value = await Promise.all(
      data.map(async (evt) => ({
        ...evt,
        logo_colorise: evt.logo_base64
          ? await coloriserLogo(evt.logo_base64, evt.couleur_secondaire)
          : null
      }))
    );

    console.log(response.data);
  } catch (error) {
    console.error("Erreur lors du chargement des évènements :", error);
  } finally {
    chargement.value = false;
  }
}

async function coloriserLogo(logoSrc, couleur) {
  return new Promise((resolve) => {
    const img = new Image();
    img.onload = () => {
      const canvas = document.createElement('canvas');
      canvas.width = img.width;
      canvas.height = img.height;
      const ctx = canvas.getContext('2d');
      ctx.drawImage(img, 0, 0);
      ctx.globalCompositeOperation = 'source-atop';
      ctx.fillStyle = couleur;
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      resolve(canvas.toDataURL());
    };
    img.src = logoSrc;
  });
}

function goToListeCourses(idEvenement) {
  router.push({ name: 'ListeCourses', params: { idEvenement } });
}

onMounted(() => {
  chargerEvenements();
});
</script>
