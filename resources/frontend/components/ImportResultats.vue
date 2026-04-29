<template>
  <div class="space-y-5">

    <div class="flex items-center justify-between">
      <h3 class="text-sm font-semibold text-heading uppercase tracking-wider flex items-center gap-2">
        <Icon icon="mdi:medal-outline" class="w-4 h-4" />
        Résultats de la course
      </h3>
    </div>

    <!-- Résultats existants -->
    <div v-if="resultats.length > 0" class="space-y-3">
      <div class="flex items-center justify-between">
        <p class="text-xs text-body">{{ resultats.length }} résultat(s) importé(s)</p>
        <button
          @click="confirmerSuppression = true"
          class="text-xs text-red-400 hover:text-red-600 font-medium flex items-center gap-1 transition-colors"
        >
          <Icon icon="mdi:trash-can-outline" class="w-3.5 h-3.5" />
          Supprimer et réimporter
        </button>
      </div>

      <div class="overflow-x-auto rounded-xl border border-default-medium">
        <table class="w-full text-sm text-left text-body">
          <thead class="bg-neutral-secondary-medium text-heading text-xs uppercase">
            <tr>
              <th class="px-4 py-2">Position</th>
              <th class="px-4 py-2">Dossard</th>
              <th class="px-4 py-2">Participant</th>
              <th class="px-4 py-2">Temps</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="r in resultats"
              :key="r.id"
              class="border-t border-default-medium hover:bg-neutral-secondary-medium transition-colors"
            >
              <td class="px-4 py-2 font-semibold">
                <span
                  class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold"
                  :class="badgePosition(r.position)"
                >
                  {{ r.position ?? '—' }}
                </span>
              </td>
              <td class="px-4 py-2">{{ r.dossard ?? '—' }}</td>
              <td class="px-4 py-2">{{ r.prenom }} {{ r.nom }}</td>
              <td class="px-4 py-2 font-mono text-xs">{{ r.temps_course ?? 'DNS/DNF' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Zone d'import -->
    <div v-if="resultats.length === 0 || afficherImport" class="space-y-4">
      <p class="text-xs text-body">
        Importez le fichier Excel fourni par votre entreprise de chronométrage.
        Les colonnes attendues sont : <strong>Dos</strong>, <strong>Nom Prénom</strong>, <strong>Arrivée</strong>.
      </p>

      <!-- Dropzone -->
      <div
        class="border-2 border-dashed rounded-xl flex flex-col items-center justify-center py-8 gap-3 cursor-pointer transition-all"
        :class="[
          glisser ? 'border-tertiary bg-tertiary/10' : 'border-default-medium hover:border-tertiary',
          fichierSelectionne ? 'border-green-400 bg-green-50' : ''
        ]"
        @dragover.prevent="glisser = true"
        @dragleave="glisser = false"
        @drop.prevent="deposerFichier"
        @click="$refs.inputFichier.click()"
      >
        <Icon
          :icon="fichierSelectionne ? 'mdi:file-check-outline' : 'mdi:upload-outline'"
          class="w-8 h-8"
          :class="fichierSelectionne ? 'text-green-500' : 'text-gray-400'"
        />
        <div class="text-center">
          <p class="text-sm font-medium text-heading">
            {{ fichierSelectionne ? fichierSelectionne.name : 'Glissez votre fichier Excel ici' }}
          </p>
          <p class="text-xs text-body mt-0.5">
            {{ fichierSelectionne ? `${(fichierSelectionne.size / 1024).toFixed(1)} Ko` : 'ou cliquez pour parcourir — .xlsx, .xls' }}
          </p>
        </div>
        <input
          ref="inputFichier"
          type="file"
          accept=".xlsx,.xls"
          class="hidden"
          @change="selectionnerFichier"
        />
      </div>

      <!-- Message résultat import -->
      <div v-if="messageImport" class="rounded-xl px-4 py-3 text-sm flex items-start gap-2"
        :class="erreurImport ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-green-50 border border-green-200 text-green-700'"
      >
        <Icon :icon="erreurImport ? 'mdi:alert-circle-outline' : 'mdi:check-circle-outline'" class="w-4 h-4 shrink-0 mt-0.5" />
        <p>{{ messageImport }}</p>
      </div>

      <!-- Bouton importer -->
      <div class="flex gap-2 justify-end">
        <button
          v-if="resultats.length > 0"
          @click="afficherImport = false"
          class="btn-accent-300 px-4 py-2 text-sm"
        >
          Annuler
        </button>
        <button
          @click="importerFichier"
          :disabled="!fichierSelectionne || chargementImport"
          class="btn-tertiary px-4 py-2 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="chargementImport">Importation...</span>
          <span v-else>Importer les résultats</span>
        </button>
      </div>
    </div>

    <!-- Bouton afficher zone import si résultats déjà présents -->
    <button
      v-if="resultats.length > 0 && !afficherImport"
      @click="afficherImport = true"
      class="btn-tertiary px-4 py-2 text-sm flex items-center gap-2"
    >
      <Icon icon="mdi:upload-outline" class="w-4 h-4" />
      Réimporter
    </button>

    <!-- Popup confirmation suppression -->
    <PopupConfirmation
      v-if="confirmerSuppression"
      message="Êtes-vous sûr de vouloir supprimer tous les résultats de cette course ? Vous pourrez réimporter ensuite."
      @confirm="supprimerResultats"
      @cancel="confirmerSuppression = false"
    />
  </div>
</template>

<script>
/**
 * @fileoverview ImportResultats
 * @description Composant organisateur pour importer et afficher les résultats
 *              d'une course depuis un fichier Excel du chronométreur.
 */
import { Icon } from '@iconify/vue';
import PopupConfirmation from './PopupConfirmation.vue';
import resultatService from '../services/resultatService';

export default {
  name: 'ImportResultats',
  components: { Icon, PopupConfirmation },
  props: {
    idCourse: { type: Number, required: true },
  },
  data() {
    return {
      resultats: [],
      chargement: true,
      chargementImport: false,
      fichierSelectionne: null,
      glisser: false,
      messageImport: null,
      erreurImport: false,
      afficherImport: false,
      confirmerSuppression: false,
    };
  },
  async mounted() {
    await this.chargerResultats();
  },
  methods: {
    async chargerResultats() {
      this.chargement = true;
      try {
        const res = await resultatService.getResultatsParCourse(this.idCourse);
        this.resultats = res.data;
      } catch (e) {
        console.error('Erreur chargement résultats:', e);
      } finally {
        this.chargement = false;
      }
    },

    selectionnerFichier(event) {
      this.fichierSelectionne = event.target.files[0] || null;
      this.messageImport = null;
    },

    deposerFichier(event) {
      this.glisser = false;
      const fichier = event.dataTransfer.files[0];
      if (fichier && (fichier.name.endsWith('.xlsx') || fichier.name.endsWith('.xls'))) {
        this.fichierSelectionne = fichier;
        this.messageImport = null;
      }
    },

    async importerFichier() {
      if (!this.fichierSelectionne) return;
      this.chargementImport = true;
      this.messageImport = null;
      this.erreurImport = false;

      try {
        const res = await resultatService.importerResultats(this.idCourse, this.fichierSelectionne);
        this.messageImport = res.data.message;
        this.fichierSelectionne = null;
        this.afficherImport = false;
        await this.chargerResultats();
      } catch (e) {
        this.erreurImport = true;
        this.messageImport = e.response?.data?.message ?? 'Erreur lors de l\'import.';
      } finally {
        this.chargementImport = false;
      }
    },

    async supprimerResultats() {
      this.confirmerSuppression = false;
      try {
        await resultatService.supprimerResultats(this.idCourse);
        this.resultats = [];
        this.afficherImport = true;
      } catch (e) {
        console.error('Erreur suppression:', e);
      }
    },

    badgePosition(position) {
      if (!position) return 'bg-gray-100 text-gray-400';
      if (position === 1) return 'bg-yellow-100 text-yellow-600';
      if (position === 2) return 'bg-gray-200 text-gray-600';
      if (position === 3) return 'bg-orange-100 text-orange-600';
      return 'bg-neutral-secondary-medium text-heading';
    },
  },
};
</script>
