<template>
  <Title texte="Formulaires"/>
  <div class="p-6">
    <FormulaireOnglet :formulaires="Object.values(formulaires)" v-model="activeTab" />
    <FormulaireCourse v-if="activeTab === formulaires.COURSE" />
    <FormulaireOption v-if="activeTab === formulaires.OPTIONS" />
    <FormulaireEvenement v-if="activeTab === formulaires.EVENEMENT" />
  </div>
</template>

<script>
import FormulaireCourse from '../components/FormulaireCourse.vue';
import FormulaireEvenement from '../components/FormulaireEvenement.vue';
import FormulaireOnglet from '../components/FormulaireOnglet.vue';
import FormulaireOption from '../components/FormulaireOption.vue';
import Title from '../components/Title.vue';

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
    Title,
    FormulaireEvenement,
    FormulaireOnglet,
    FormulaireCourse,
    FormulaireOption
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