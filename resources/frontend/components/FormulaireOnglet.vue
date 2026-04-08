<template>
  <div class="flex">
    <button v-for="(formulaire, index) in formulaires" :key="index" @click="selectionnerFormulaire(formulaire)"
    :class="['px-4 py-2 text-sm font-medium border -mb-px transition-colors rounded-t-base',
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
     * @param {string} formulaire Nom de l'onglet sélectionné.
     * @returns {void}
     */
    selectionnerFormulaire(formulaire) {
      this.$emit('update:modelValue', formulaire);
    },
  },
};
</script>