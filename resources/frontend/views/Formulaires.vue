<template>
  <div class="p-6">
    <FormulaireEvenement />
  </div>
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