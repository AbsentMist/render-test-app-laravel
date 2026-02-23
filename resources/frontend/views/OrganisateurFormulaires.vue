<template>
  <div class="p-6">
    <FormulaireOnglet :formulaires="Object.values(formulaires)" v-model="activeTab" />
    <FormulaireEvenement v-if="activeTab === formulaires.EVENEMENT" />
  </div>
</template>

<script>
import FormulaireEvenement from '../components/FormulaireEvenement.vue';
import FormulaireOnglet from '../components/FormulaireOnglet.vue';

const formulaires = {
    COURSE: "Course",
    OPTIONS: "Options",
    QUESTIONNAIRE: "Questionnaire",
    TEMPLATE: "Template",
    EVENEMENT: "Evènement",
    CATEGORIE: "Catégorie",
    AVERTISSEMENT: "Avertissement"
};

export default {
  components: {
    FormulaireEvenement,
    FormulaireOnglet
  },
  data() {
    return {
      formulaires,
      activeTab: formulaires.COURSE,
    };
  },
  mounted() {
    initModals();
  }
};
</script>