<template>
  <div class="p-6">
    <h2 class="text-2xl font-semibold">Bienvenue sur votre tableau de bord</h2>
    <p class="mt-4 text-gray-600">Ici s'afficheront les actualités de Running Geneva.</p>
  </div>
  <FormulaireEvenement />
</template>

<script>
import FormulaireEvenement from '../components/FormulaireEvenement.vue';

export default {
  components: {
    FormulaireEvenement
  },
  mounted() {
    initModals();
  }
};
</script>