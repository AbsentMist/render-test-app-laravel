<template>
  <div class="flex flex-wrap items-center gap-3 mb-4">
    <div class="flex flex-wrap items-center gap-3 flex-1 min-w-0">
      <div class="relative flex-[1_1_280px] min-w-[280px]">
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
          class="w-full h-11 pl-9 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base px-4 focus:ring-brand focus:border-brand shadow-xs"
          @input="emitFiltres"
        />
      </div>

      <div class="relative min-w-[180px] flex-[0_0_180px]">
        <select
          v-model="filtresInternes.status"
          class="w-full h-11 appearance-none bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base px-4 pr-10 focus:ring-brand focus:border-brand shadow-xs cursor-pointer"
          @change="emitFiltres"
        >
          <option value="">Tous les statuts</option>
          <option value="Validé">Validé</option>
          <option value="En attente">En attente</option>
          <option value="Annulé">Annulé</option>
          <option value="Transféré">Transféré</option>
          <option value="Echangé">Echangé</option>
        </select>
        <Icon icon="mdi:chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-body" />
      </div>

      <div class="relative min-w-[180px] flex-[0_0_180px]">
        <select
          v-model="filtresInternes.type"
          class="w-full h-11 appearance-none bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base px-4 pr-10 focus:ring-brand focus:border-brand shadow-xs cursor-pointer"
          @change="emitFiltres"
        >
          <option value="">Tous les types</option>
          <option value="Individuel">Individuel</option>
          <option value="Relais">Relais</option>
          <option value="Groupe">Groupe</option>
        </select>
        <Icon icon="mdi:chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-body" />
      </div>

      <button
        v-if="filtresActifs"
        @click="reinitialiser"
        class="h-11 px-4 text-sm text-accent hover:text-red-700 border border-accent rounded-base transition-colors flex items-center justify-center whitespace-nowrap"
      >
        Réinitialiser
      </button>

      <span class="flex items-center h-11 text-xs text-body px-2 whitespace-nowrap">
        {{ nbResultats }} résultat(s)
      </span>
    </div>

    <div class="relative ml-auto shrink-0">
      <div class="relative">
        <span
          v-if="copieConfirmee"
          class="absolute -top-8 left-1/2 -translate-x-1/2 whitespace-nowrap text-xs text-primary bg-tertiary p-2 rounded-xl"
        >
          Copié
        </span>
        <button
          @click="toggleMenuExport"
          class="px-3 py-2 text-sm text-heading bg-neutral-secondary-medium border border-default-medium hover:bg-neutral-tertiary-medium rounded-base transition-colors flex items-center gap-2 shadow-xs"
        >
          <Icon icon="mdi:export" class="w-4 h-4" />
          Exporter
          <Icon :icon="menuExportOuvert ? 'mdi:chevron-up' : 'mdi:chevron-down'" class="w-4 h-4" />
        </button>

        <div
          v-if="menuExportOuvert"
          class="absolute right-0 mt-2 w-56 bg-white border border-default-medium rounded-lg shadow-lg z-30 overflow-hidden"
        >
          <button
            class="w-full text-left px-4 py-3 text-sm hover:bg-neutral-secondary-medium flex items-center gap-2"
            @click="exporter('email')"
          >
            <Icon icon="mdi:email" class="w-4 h-4" />
            Exporter les emails
          </button>
          <button
            class="w-full text-left px-4 py-3 text-sm hover:bg-neutral-secondary-medium flex items-center gap-2"
            @click="exporter('logistique')"
          >
            <Icon icon="mdi:clipboard-text-outline" class="w-4 h-4" />
            Export logistique
          </button>
          <button
            class="w-full text-left px-4 py-3 text-sm hover:bg-neutral-secondary-medium flex items-center gap-2"
            @click="exporter('banque')"
          >
            <Icon icon="mdi:bank" class="w-4 h-4" />
            Export banque
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import { Icon } from '@iconify/vue';

/**
 * @fileoverview Composant FiltreInscriptions.
 * @description Barre de filtrage et d'export pour la liste des inscriptions côté organisateur.
 * @remarks Le composant maintient des filtres internes puis émet une charge utile unifiée
 * utilisée par la vue parente pour appliquer la recherche et déclencher les exports.
 */
export default {
  name: 'FiltreInscriptions',
  components: { Icon },
  props: {
    nbResultats: { type: Number, default: 0 },
    copieConfirmee: { type: Boolean, default: false },
  },
  emits: ['update:filtres', 'exporter'],
  /**
   * Initialise les filtres manipulés localement dans l'interface.
   * @returns {{filtresInternes: {recherche: string, status: string, type: string}}}
   */
  data() {
    return {
      menuExportOuvert: false,
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
     * Ouvre ou ferme le menu d'export.
     * @returns {void}
     */
    toggleMenuExport() {
      this.menuExportOuvert = !this.menuExportOuvert;
    },
    /**
     * Émet l'état courant des filtres vers le parent.
     * @returns {void}
     */
    emitFiltres() {
      this.$emit('update:filtres', { ...this.filtresInternes });
    },
    /**
     * Déclenche un export puis ferme le menu.
     * @param {string} type Type d'export demandé.
     * @returns {void}
     */
    exporter(type) {
      this.menuExportOuvert = false;
      this.$emit('exporter', type);
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
  mounted() {
    this._fermerMenuExport = (event) => {
      if (!this.$el.contains(event.target)) {
        this.menuExportOuvert = false;
      }
    };
    document.addEventListener('click', this._fermerMenuExport);
  },
  beforeUnmount() {
    document.removeEventListener('click', this._fermerMenuExport);
  },
};
</script>
