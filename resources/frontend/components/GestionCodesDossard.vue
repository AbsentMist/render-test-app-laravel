<template>
  <div class="space-y-5">

    <div class="flex items-center justify-between">
      <h3 class="text-sm font-semibold text-heading uppercase tracking-wider flex items-center gap-2">
        <Icon icon="mdi:ticket-account-outline" class="w-4 h-4" />
        Codes dossard personnalisés
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
      <Icon icon="mdi:ticket-off-outline" class="w-8 h-8 mx-auto mb-2 opacity-40" />
      <p class="text-sm">Aucun code dossard pour cette course.</p>
    </div>

    <!-- Liste -->
    <div v-else class="overflow-x-auto rounded-xl border border-default-medium">
      <table class="w-full text-sm text-left text-body">
        <thead class="bg-neutral-secondary-medium text-heading text-xs uppercase">
          <tr>
            <th class="px-4 py-3">Code</th>
            <th class="px-4 py-3">Nom personnalisé</th>
            <th class="px-4 py-3 text-center">Utilisations</th>
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
            <td class="px-4 py-3">{{ code.nom_personnalise ?? '—' }}</td>
            <td class="px-4 py-3 text-center">
              <span
                class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold"
                :class="code.utilisations_actuelles >= code.utilisations_max
                  ? 'bg-red-100 text-red-600'
                  : 'bg-green-100 text-green-700'"
              >
                {{ code.utilisations_actuelles }} / {{ code.utilisations_max }}
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
        {{ codeEnEdition ? 'Modifier le code' : 'Créer un code dossard' }}
      </h4>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        <!-- Code -->
        <div>
          <label class="block text-xs font-medium text-heading mb-1">
            Code <span class="text-red-400">*</span>
          </label>
          <input
            v-model="formulaire.code"
            type="text"
            placeholder="Ex: MRBEAST2026"
            :disabled="!!codeEnEdition"
            class="w-full rounded-lg border border-default-medium px-3 py-2 text-sm text-heading bg-white focus:outline-none focus:ring-2 focus:ring-tertiary uppercase disabled:opacity-50"
            @input="formulaire.code = formulaire.code.toUpperCase()"
          />
          <p v-if="codeEnEdition" class="text-xs text-gray-400 mt-1">Le code ne peut pas être modifié après création.</p>
        </div>

        <!-- Nb utilisations max -->
        <div>
          <label class="block text-xs font-medium text-heading mb-1">
            Nombre de participants <span class="text-red-400">*</span>
          </label>
          <input
            v-model="formulaire.utilisations_max"
            type="number"
            min="1"
            placeholder="Ex: 1 pour VIP, 50 pour sponsor"
            class="w-full rounded-lg border border-default-medium px-3 py-2 text-sm text-heading bg-white focus:outline-none focus:ring-2 focus:ring-tertiary"
          />
        </div>

        <!-- Nom personnalisé -->
        <div class="sm:col-span-2">
          <label class="block text-xs font-medium text-heading mb-1">
            Nom personnalisé sur le dossard
            <span class="text-gray-400">(optionnel)</span>
          </label>
          <input
            v-model="formulaire.nom_personnalise"
            type="text"
            placeholder="Ex: MrBeast, Sponsor Running Geneva..."
            class="w-full rounded-lg border border-default-medium px-3 py-2 text-sm text-heading bg-white focus:outline-none focus:ring-2 focus:ring-tertiary"
          />
          <p class="text-xs text-gray-400 mt-1">
            Si vide, le nom du participant sera utilisé comme d'habitude.
          </p>
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
      message="Êtes-vous sûr de vouloir supprimer ce code dossard ?"
      @confirm="supprimerCode"
      @cancel="popupSuppression = false; codeASupprimer = null"
    />
  </div>
</template>

<script>
/**
 * @fileoverview GestionCodesDossard
 * @description Composant organisateur pour créer, modifier et supprimer
 *              des codes dossard personnalisés pour une course.
 *
 * Un code peut être utilisé par 1 ou N participants.
 * Le nom personnalisé est appliqué sur le dossard lors de l'inscription.
 */
import { Icon } from '@iconify/vue';
import PopupConfirmation from './PopupConfirmation.vue';
import codeDossardService from '../services/codeDossardService';

export default {
  name: 'GestionCodesDossard',
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

      formulaireVisible: false,
      codeEnEdition: null,
      formulaire: this.formulaireVide(),

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
        nom_personnalise: '',
        utilisations_max: 1,
      };
    },

    async chargerCodes() {
      this.chargement = true;
      try {
        const res = await codeDossardService.getCodesParCourse(this.idCourse);
        this.codes = res.data;
      } catch (e) {
        console.error('Erreur chargement codes dossard:', e);
      } finally {
        this.chargement = false;
      }
    },

    ouvrirFormulaire(code = null) {
      this.codeEnEdition = code;
      this.formulaire = code
        ? {
            code: code.code,
            nom_personnalise: code.nom_personnalise ?? '',
            utilisations_max: code.utilisations_max,
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
      if (!this.formulaire.code || !this.formulaire.utilisations_max) {
        this.erreur = 'Le code et le nombre de participants sont obligatoires.';
        return;
      }

      this.chargementAction = true;
      this.erreur = null;

      const payload = {
        ...this.formulaire,
        nom_personnalise: this.formulaire.nom_personnalise || null,
      };

      try {
        if (this.codeEnEdition) {
          const res = await codeDossardService.modifierCode(this.codeEnEdition.id, payload);
          const idx = this.codes.findIndex(c => c.id === this.codeEnEdition.id);
          if (idx > -1) this.codes.splice(idx, 1, res.data);
        } else {
          const res = await codeDossardService.creerCode(this.idCourse, payload);
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
        await codeDossardService.supprimerCode(this.codeASupprimer.id);
        this.codes = this.codes.filter(c => c.id !== this.codeASupprimer.id);
      } catch (e) {
        this.erreur = e.response?.data?.message ?? 'Erreur lors de la suppression.';
      } finally {
        this.chargementAction = false;
        this.codeASupprimer = null;
      }
    },
  },
};
</script>
