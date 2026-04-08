<template>
  <div
    v-for="evt in evenementsColorises"
    :key="evt.id"
    @click="handleClick(evt)"
    class="relative h-48 rounded-xl p-4 flex flex-col justify-end cursor-pointer transition-transform hover:-translate-y-1 shadow-sm"
    :style="{ backgroundColor: evt.couleur_primaire || '#53687e', isolation: 'isolate' }"
  >
    <div
      class="absolute top-4 right-4 w-6 h-6 rounded-full border flex items-center justify-center text-xs font-medium"
      :style="{ borderColor: evt.couleur_secondaire, color: evt.couleur_secondaire }"
    >
      <Icon icon="mdi:exclamation-thick" class="w-4 h-4" />
    </div>
    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none p-6">
      <img
        v-if="evt.logo_colorise"
        :src="evt.logo_colorise"
        class="max-h-16 max-w-48 object-contain"
        alt="Logo évènement"
      />
      <span
        v-else
        class="text-xl font-black tracking-widest text-center uppercase"
        :style="{ color: evt.couleur_secondaire }"
      >
        {{ evt.nom }}
      </span>
    </div>
    <div class="relative z-10 font-normal text-base" :style="{ color: evt.couleur_secondaire }">
      {{ evt.nom }}
    </div>
  </div>
</template>

<script setup>
/**
 * @fileoverview Composant MiniatureEvenement.
 * @description Carte de présentation d'un événement avec navigation ou sélection selon le mode.
 * @remarks Le composant prépare une version colorisée du logo pour chaque évènement
 * afin de respecter la charte visuelle sans dépendre d'assets pré-calculés.
 */
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { Icon } from '@iconify/vue';

const props = defineProps({
  evenements: {
    required: true,
    type: Array,  
  },
  mode: { 
    type: String, 
    default: 'navigation' }, // 'navigation' | 'selection'
});

const router = useRouter();
const evenementsColorises = ref([]);
const emit = defineEmits(['selectionner']);

/**
 * Gère le clic sur une carte selon le mode courant.
 * @param {Object} evt Évènement sélectionné.
 * @returns {void}
 */
function handleClick(evt) {
  if (props.mode === 'selection') {
    emit('selectionner', evt);
  } else {
    router.push({ name: 'ListeCourses', params: { idEvenement: evt.id } });
  }
}

/**
 * Applique une teinte sur un logo afin de l'adapter à la palette de l'évènement.
 * @param {string} logoSrc Source de l'image à recolorer.
 * @param {string} couleur Couleur cible.
 * @returns {Promise<string>}
 */
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

/**
 * Prépare les évènements enrichis avec leur logo colorisé au montage.
 * @returns {Promise<void>}
 */
onMounted(async () => {
  evenementsColorises.value = await Promise.all(
    props.evenements.map(async (evt) => ({
      ...evt,
      logo_colorise: evt.logo_base64
        ? await coloriserLogo(evt.logo_base64, evt.couleur_secondaire)
        : null,
    }))
  );
});
</script>