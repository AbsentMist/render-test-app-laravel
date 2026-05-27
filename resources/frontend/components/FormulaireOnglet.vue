<template>
  <div class="flex flex-wrap gap-1 sm:gap-0">
    <button v-for="(formulaire, index) in formulaires" :key="index" @click="selectionnerFormulaire(formulaire)"
    :class="['px-2 sm:px-4 py-1 sm:py-2 text-sm font-medium border -mb-px transition-colors rounded-t-base whitespace-nowrap',
        modelValue === formulaire 
          ? 'border-default-medium border-b-secondary bg-secondary text-accent' 
          : 'border-transparent text-body hover:text-heading'
      ]">
      {{ formulaire }}
    </button>
  </div>
</template>

<script>
/**
 * @fileoverview Composant FormulaireOnglet.
 * @description Navigation par onglets pour sélectionner le formulaire actif dans l'espace organisateur.
 * @remarks Le composant reçoit une liste d'onglets et expose un v-model pour laisser
 * le parent piloter l'affichage du formulaire actif.
 */
export default {
  name: 'FormulaireOnglet',
  props: {
    formulaires: {
      type: Array,
      default: () => []
    },
    modelValue: {
      type: String,
      default: ''
    }
  },
  emits: ['update:modelValue'],
  methods: {
    /**
     * Propage la sélection d'un onglet vers le parent.
     * @author Neris Alessandro
     * @param {string} formulaire Nom de l'onglet sélectionné.
     * @returns {void}
     */
    selectionnerFormulaire(formulaire) {
      this.$emit('update:modelValue', formulaire);
    },
  },
};
</script>