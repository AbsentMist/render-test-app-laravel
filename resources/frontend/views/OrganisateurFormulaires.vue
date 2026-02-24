<template>
  <div class="p-6">
    <FormulaireOnglet :formulaires="Object.values(formulaires)" v-model="activeTab" />
    <FormulaireCourse v-if="activeTab === formulaires.COURSE" />
    <FormulaireEvenement v-if="activeTab === formulaires.EVENEMENT" />
  </div>
</template>

<script>
import FormulaireCourse from '../components/FormulaireCourse.vue';
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
    FormulaireOnglet,
    FormulaireCourse
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