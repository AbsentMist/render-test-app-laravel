<template>
  <Title texte="Formulaires"/>
  <div class="p-6">
    <FormulaireOnglet :formulaires="Object.values(formulaires)" v-model="activeTab" />
    <FormulaireCourse v-if="activeTab === formulaires.COURSE" />
    <FormulaireOption v-if="activeTab === formulaires.OPTIONS" />
    <FormulaireTemplate v-if="activeTab === formulaires.TEMPLATE" />
    <FormulaireEvenement v-if="activeTab === formulaires.EVENEMENT" />
    <FormulaireCategorie v-if="activeTab === formulaires.CATEGORIE" />
    <FormulaireAvertissement v-if="activeTab === formulaires.AVERTISSEMENT" />
  </div>
</template>

<script>
/**
 * @fileoverview Vue OrganisateurFormulaires.
 * @description Espace de configuration des formulaires administrateur par onglets thématiques.
 * @remarks Cette vue sélectionne dynamiquement le sous-formulaire affiché selon l'onglet actif,
 * avec prise en charge d'une pré-sélection par paramètre d'URL.
 */
import FormulaireAvertissement from '../components/FormulaireAvertissement.vue';
import FormulaireCategorie from '../components/FormulaireCategorie.vue';
import FormulaireCourse from '../components/FormulaireCourse.vue';
import FormulaireEvenement from '../components/FormulaireEvenement.vue';
import FormulaireOnglet from '../components/FormulaireOnglet.vue';
import FormulaireOption from '../components/FormulaireOption.vue';
import FormulaireTemplate from '../components/FormulaireTemplate.vue';
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
    FormulaireOption,
    FormulaireCategorie,
    FormulaireAvertissement,
    FormulaireTemplate
  },
  /**
   * Initialise la navigation par onglets des formulaires organisateur.
   * @returns {{formulaires: Object, activeTab: string}} État local de la vue.
   */
  data() {
    return {
      formulaires,
      activeTab: this.$route.query.onglet || formulaires.COURSE,
    };
  },
  /**
   * Active les comportements modaux globaux requis par la page.
   * @returns {void}
   */
  mounted() {
    initModals();
  }
};
</script>