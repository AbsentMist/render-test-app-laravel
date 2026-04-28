<template>
  <div class="space-y-5">

    <div class="flex items-center justify-between">
      <h3 class="text-sm font-semibold text-heading uppercase tracking-wider flex items-center gap-2">
        <Icon icon="mdi:tag-multiple-outline" class="w-4 h-4" />
        Codes de rabais
      </h3>
      <button @click="ouvrirFormulaire()" class="btn-tertiary px-3 py-1.5 text-xs flex items-center gap-1">
        <Icon icon="mdi:plus" class="w-4 h-4" />
        Nouveau code
      </button>
    </div>

    <!-- Chargement -->
    <div v-if="chargement" class="text-body text-sm text-center py-4">Chargement...</div>

    <!-- Aucun code -->
    <div v-else-if="codes.length === 0" class="text-center py-8 text-gray-400 rounded-xl border border-default-medium">
      <Icon icon="mdi:tag-off-outline" class="w-8 h-8 mx-auto mb-2 opacity-40" />
      <p class="text-sm">Aucun code de rabais pour cette course.</p>
    </div>

    <!-- Liste des codes -->
    <div v-else class="overflow-x-auto rounded-xl border border-default-medium">
      <table class="w-full text-sm text-left text-body">
        <thead class="bg-neutral-secondary-medium text-heading text-xs uppercase">
          <tr>
            <th class="px-4 py-3">Code</th>
            <th class="px-4 py-3">Réduction</th>
            <th class="px-4 py-3 text-center">Utilisations</th>
            <th class="px-4 py-3">Expiration</th>
            <th class="px-4 py-3 text-center">Statut</th>
            <th class="px-4 py-3 w-8"></th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="code in codes"
            :key="code.id"
            class="border-t border-default-medium hover:bg-neutral-secondary-medium transition-colors"
          >
            <td class="px-4 py-3 font-mono font-semibold text-heading">{{ code.code }}</td>
            <td class="px-4 py-3">
              <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                <Icon icon="mdi:tag-outline" class="w-3 h-3" />
                {{ code.type === 'pourcentage' ? `-${code.valeur}%` : `-${code.valeur} CHF` }}
              </span>
            </td>
            <td class="px-4 py-3 text-center">
              {{ code.utilisations_actuelles }}
              {{ code.utilisations_max !== null ? `/ ${code.utilisations_max}` : '/ ∞' }}
            </td>
            <td class="px-4 py-3">{{ code.date_expiration ? formatDate(code.date_expiration) : '—' }}</td>
            <td class="px-4 py-3 text-center">
              <span
                class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold"
                :class="code.actif ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
              >
                {{ code.actif ? 'Actif' : 'Inactif' }}
              </span>
            </td>
            <td class="px-4 py-3">
              <div class="flex gap-1">
                <button @click="ouvrirFormulaire(code)" class="p-1.5 rounded-lg hover:bg-neutral-secondary-medium transition-colors text-body">
                  <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                </button>
                <button @click="confirmerSuppression(code)" class="p-1.5 rounded-lg hover:bg-red-50 transition-colors text-red-400">
                  <Icon icon="mdi:trash-can-outline" class="w-4 h-4" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Formulaire création / modification -->
    <div v-if="formulaireVisible" class="rounded-xl border border-default-medium p-5 space-y-4 bg-neutral-secondary-medium">
      <h4 class="text-sm font-semibold text-heading">
        {{ codeEnEdition ? 'Modifier le code' : 'Créer un code de rabais' }}
      </h4>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        <!-- Code -->
        <div>
          <label class="block text-xs font-medium text-heading mb-1">Code <span class="text-red-400">*</span></label>
          <input
            v-model="formulaire.code"
            type="text"
            placeholder="Ex: ETUDIANT2026"
            class="w-full rounded-lg border border-default-medium px-3 py-2 text-sm text-heading bg-white focus:outline-none focus:ring-2 focus:ring-tertiary uppercase"
            @input="formulaire.code = formulaire.code.toUpperCase()"
          />
        </div>

        <!-- Type -->
        <div>
          <label class="block text-xs font-medium text-heading mb-1">Type <span class="text-red-400">*</span></label>
          <select
            v-model="formulaire.type"
            class="w-full rounded-lg border border-default-medium px-3 py-2 text-sm text-heading bg-white focus:outline-none focus:ring-2 focus:ring-tertiary"
          >
            <option value="pourcentage">Pourcentage (%)</option>
            <option value="montant_fixe">Montant fixe (CHF)</option>
          </select>
        </div>

        <!-- Valeur -->
        <div>
          <label class="block text-xs font-medium text-heading mb-1">
            Valeur <span class="text-red-400">*</span>
            <span class="text-gray-400">({{ formulaire.type === 'pourcentage' ? 'max 100%' : 'en CHF' }})</span>
          </label>
          <input
            v-model="formulaire.valeur"
            type="number"
            min="0.01"
            :max="formulaire.type === 'pourcentage' ? 100 : undefined"
            step="0.01"
            class="w-full rounded-lg border border-default-medium px-3 py-2 text-sm text-heading bg-white focus:outline-none focus:ring-2 focus:ring-tertiary"
          />
        </div>

        <!-- Utilisations max -->
        <div>
          <label class="block text-xs font-medium text-heading mb-1">
            Nb utilisations max
            <span class="text-gray-400">(vide = illimité)</span>
          </label>
          <input
            v-model="formulaire.utilisations_max"
            type="number"
            min="1"
            placeholder="Illimité"
            class="w-full rounded-lg border border-default-medium px-3 py-2 text-sm text-heading bg-white focus:outline-none focus:ring-2 focus:ring-tertiary"
          />
        </div>

        <!-- Date expiration -->
        <div>
          <label class="block text-xs font-medium text-heading mb-1">Date d'expiration <span class="text-gray-400">(optionnel)</span></label>
          <input
            v-model="formulaire.date_expiration"
            type="date"
            class="w-full rounded-lg border border-default-medium px-3 py-2 text-sm text-heading bg-white focus:outline-none focus:ring-2 focus:ring-tertiary"
          />
        </div>

        <!-- Actif -->
        <div class="flex items-center gap-3 pt-5">
          <input
            type="checkbox"
            id="actif"
            v-model="formulaire.actif"
            class="accent-primary w-4 h-4"
          />
          <label for="actif" class="text-sm text-heading">Code actif</label>
        </div>
      </div>

      <p v-if="erreur" class="text-sm text-red-600">{{ erreur }}</p>

      <div class="flex gap-2 justify-end pt-2">
        <button @click="fermerFormulaire" class="btn-accent-300 px-4 py-2 text-sm">Annuler</button>
        <button @click="sauvegarder" :disabled="chargementAction" class="btn-tertiary px-4 py-2 text-sm disabled:opacity-50">
          {{ chargementAction ? 'Sauvegarde...' : (codeEnEdition ? 'Modifier' : 'Créer') }}
        </button>
      </div>
    </div>

    <!-- Popup confirmation suppression -->
    <PopupConfirmation
      v-if="popupSuppression"
      message="Êtes-vous sûr de vouloir supprimer ce code de rabais ?"
      @confirm="supprimerCode"
      @cancel="popupSuppression = false; codeASupprimer = null"
    />
  </div>
</template>

<script>
/**
 * @fileoverview GestionCodesRabais
 * @description Composant pour l'organisateur permettant de créer, modifier
 *              et supprimer des codes de rabais pour une course donnée.
 */
import { Icon } from '@iconify/vue';
import PopupConfirmation from './PopupConfirmation.vue';
import codeRabaisService from '../services/codeRabaisService';

export default {
  name: 'GestionCodesRabais',
  components: { Icon, PopupConfirmation },
  props: {
    idCourse: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      codes: [],
      chargement: true,
      chargementAction: false,
      erreur: null,

      // Formulaire
      formulaireVisible: false,
      codeEnEdition: null,
      formulaire: this.formulaireVide(),

      // Suppression
      popupSuppression: false,
      codeASupprimer: null,
    };
  },
  async mounted() {
    await this.chargerCodes();
  },
  methods: {
    formulaireVide() {
      return {
        code: '',
        type: 'pourcentage',
        valeur: '',
        utilisations_max: '',
        date_expiration: '',
        actif: true,
      };
    },

    async chargerCodes() {
      this.chargement = true;
      try {
        const res = await codeRabaisService.getCodesParCourse(this.idCourse);
        this.codes = res.data;
      } catch (e) {
        console.error('Erreur chargement codes:', e);
      } finally {
        this.chargement = false;
      }
    },

    ouvrirFormulaire(code = null) {
      this.codeEnEdition = code;
      this.formulaire = code
        ? {
            code: code.code,
            type: code.type,
            valeur: code.valeur,
            utilisations_max: code.utilisations_max ?? '',
            date_expiration: code.date_expiration ?? '',
            actif: code.actif,
          }
        : this.formulaireVide();
      this.erreur = null;
      this.formulaireVisible = true;
    },

    fermerFormulaire() {
      this.formulaireVisible = false;
      this.codeEnEdition = null;
      this.erreur = null;
    },

    async sauvegarder() {
      if (!this.formulaire.code || !this.formulaire.valeur) {
        this.erreur = 'Le code et la valeur sont obligatoires.';
        return;
      }

      this.chargementAction = true;
      this.erreur = null;

      const payload = {
        ...this.formulaire,
        utilisations_max: this.formulaire.utilisations_max || null,
        date_expiration: this.formulaire.date_expiration || null,
      };

      try {
        if (this.codeEnEdition) {
          const res = await codeRabaisService.modifierCode(this.codeEnEdition.id, payload);
          const idx = this.codes.findIndex(c => c.id === this.codeEnEdition.id);
          if (idx > -1) this.codes.splice(idx, 1, res.data);
        } else {
          const res = await codeRabaisService.creerCode(this.idCourse, payload);
          this.codes.unshift(res.data);
        }
        this.fermerFormulaire();
      } catch (e) {
        this.erreur = e.response?.data?.message ?? 'Une erreur est survenue.';
      } finally {
        this.chargementAction = false;
      }
    },

    confirmerSuppression(code) {
      this.codeASupprimer = code;
      this.popupSuppression = true;
    },

    async supprimerCode() {
      this.popupSuppression = false;
      this.chargementAction = true;
      try {
        await codeRabaisService.supprimerCode(this.codeASupprimer.id);
        this.codes = this.codes.filter(c => c.id !== this.codeASupprimer.id);
      } catch (e) {
        this.erreur = e.response?.data?.message ?? 'Erreur lors de la suppression.';
      } finally {
        this.chargementAction = false;
        this.codeASupprimer = null;
      }
    },

    formatDate(dateStr) {
      return new Date(dateStr).toLocaleDateString('fr-CH', {
        day: '2-digit', month: '2-digit', year: 'numeric',
      });
    },
  },
};
</script>
