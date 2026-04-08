<template>
  <div class="flex flex-wrap gap-3 mb-4">
    <div class="relative flex-1 min-w-[250px]">
      <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
        <svg class="w-4 h-4 text-body" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
      <input
        v-model="filtresInternes.recherche"
        type="text"
        placeholder="Rechercher par nom, prénom, dossard, entreprise..."
        class="w-full pl-9 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base px-3 py-2 focus:ring-brand focus:border-brand shadow-xs"
        @input="emitFiltres"
      />
    </div>

    <select
      v-model="filtresInternes.status"
      class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base px-3 py-2 focus:ring-brand focus:border-brand shadow-xs"
      @change="emitFiltres"
    >
      <option value="">Tous les statuts</option>
      <option value="Validé">Validé</option>
      <option value="En attente">En attente</option>
      <option value="Annulé">Annulé</option>
    </select>

    <select
      v-model="filtresInternes.type"
      class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base px-3 py-2 focus:ring-brand focus:border-brand shadow-xs"
      @change="emitFiltres"
    >
      <option value="">Tous les types</option>
      <option value="Individuel">Individuel</option>
      <option value="Relais">Relais</option>
      <option value="Groupe">Groupe</option>
    </select>

    <button
      v-if="filtresActifs"
      @click="reinitialiser"
      class="px-3 py-2 text-sm text-accent hover:text-red-700 border border-accent rounded-base transition-colors"
    >
      Réinitialiser
    </button>

    <span class="flex items-center text-xs text-body px-2">
      {{ nbResultats }} résultat(s)
    </span>

    <div class="flex gap-2 ml-auto">
      <button
        @click="$emit('exporter', 'xlsx')"
        class="px-3 py-2 text-sm text-white bg-green-600 hover:bg-green-700 rounded-base transition-colors flex items-center gap-2 shadow-xs"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        Excel
      </button>
      <button
        @click="$emit('exporter', 'csv')"
        class="px-3 py-2 text-sm text-heading bg-neutral-secondary-medium border border-default-medium hover:bg-neutral-tertiary-medium rounded-base transition-colors flex items-center gap-2 shadow-xs"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        CSV
      </button>
    </div>

  </div>
</template>

<script>
/**
 * @fileoverview Composant FiltreInscriptions.
 * @description Barre de filtrage et d'export pour la liste des inscriptions côté organisateur.
 * @remarks Le composant maintient des filtres internes puis émet une charge utile unifiée
 * utilisée par la vue parente pour appliquer la recherche et déclencher les exports.
 */
export default {
  name: 'FiltreInscriptions',
  props: {
    nbResultats: { type: Number, default: 0 },
  },
  emits: ['update:filtres', 'exporter'],
  /**
   * Initialise les filtres manipulés localement dans l'interface.
   * @returns {{filtresInternes: {recherche: string, status: string, type: string}}}
   */
  data() {
    return {
      filtresInternes: {
        recherche: '',
        status:    '',
        type:      '',
      },
    };
  },
  computed: {
    /**
     * Indique si au moins un filtre est actif.
     * @returns {boolean}
     */
    filtresActifs() {
      return this.filtresInternes.recherche ||
             this.filtresInternes.status ||
             this.filtresInternes.type;
    },
  },
  methods: {
    /**
     * Émet l'état courant des filtres vers le parent.
     * @returns {void}
     */
    emitFiltres() {
      this.$emit('update:filtres', { ...this.filtresInternes });
    },
    /**
     * Réinitialise les filtres puis notifie le parent.
     * @returns {void}
     */
    reinitialiser() {
      this.filtresInternes = { recherche: '', status: '', type: '' };
      this.emitFiltres();
    },
  },
};
</script>
