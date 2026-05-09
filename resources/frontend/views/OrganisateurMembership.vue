<template>
  <div>
    <Title texte="Membership" />

    <div class="p-6 space-y-6">
      <div class="flex flex-wrap flex-col gap-3 mb-4">
        <div class="flex flex-wrap gap-3 items-center">
          <div class="relative flex-1 min-w-62.5">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <svg class="w-4 h-4 text-body" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input
              v-model="recherche"
              type="text"
              placeholder="Rechercher par nom, prénom, téléphone, email..."
              class="w-full pl-9 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base px-3 py-2 focus:ring-brand focus:border-brand shadow-xs"
            />
          </div>

          <button
            v-if="recherche"
            @click="recherche = ''"
            class="px-3 py-2 text-sm text-accent hover:text-red-700 border border-accent rounded-base transition-colors"
          >
            Réinitialiser
          </button>

          <span class="flex items-center text-xs text-body px-2">
            {{ demandesFiltrees.length }} résultat(s)
          </span>
        </div>
      </div>

      <!-- Filtres -->
      <div class="flex flex-col sm:flex-row gap-3">
        <button
          @click="filtreStatus = 'En attente'"
          :class="[
            'px-4 py-2 rounded-lg font-semibold transition-colors text-sm',
            filtreStatus === 'En attente'
              ? 'bg-primary text-white'
              : 'bg-neutral-secondary-medium text-body hover:bg-neutral-secondary-dark',
          ]"
        >
          En attente ({{ demandesEnAttente.length }})
        </button>
        <button
          @click="filtreStatus = 'Approuvée'"
          :class="[
            'px-4 py-2 rounded-lg font-semibold transition-colors text-sm',
            filtreStatus === 'Approuvée'
              ? 'bg-primary text-white'
              : 'bg-neutral-secondary-medium text-body hover:bg-neutral-secondary-dark',
          ]"
        >
          Approuvées ({{ demandesApprouvees.length }})
        </button>
        <button
          @click="filtreStatus = 'Refusée'"
          :class="[
            'px-4 py-2 rounded-lg font-semibold transition-colors text-sm',
            filtreStatus === 'Refusée'
              ? 'bg-primary text-white'
              : 'bg-neutral-secondary-medium text-body hover:bg-neutral-secondary-dark',
          ]"
        >
          Refusées ({{ demandesRefusees.length }})
        </button>
      </div>

      <!-- Chargement -->
      <div v-if="chargement" class="text-body text-center py-12">
        <p class="mb-2">Chargement des demandes...</p>
      </div>

      <!-- Tableau des demandes -->
      <div v-else class="overflow-x-auto rounded-xl border border-default-medium">
        <table class="w-full text-sm">
          <thead class="bg-neutral-secondary-medium text-heading text-xs uppercase">
            <tr>
              <th class="px-4 py-3 text-left">Candidat</th>
              <th class="px-4 py-3 text-left">Contact</th>
              <th class="px-4 py-3 text-left">Date demande</th>
              <th class="px-4 py-3 text-left">Statut</th>
              <th class="px-4 py-3 text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="demande in demandesFiltrees"
              :key="demande.id"
              class="border-t border-default-medium hover:bg-neutral-secondary-medium transition-colors cursor-pointer"
              @click="ouvrirDetail(demande)"
            >
              <td class="px-4 py-3">
                <div>
                  <p class="font-semibold text-heading">{{ demande.prenom }} {{ demande.nom }}</p>
                  <p class="text-xs text-body">{{ formatDate(demande.date_naissance) }}</p>
                </div>
              </td>
              <td class="px-4 py-3">
                <div class="space-y-0.5">
                  <p class="text-heading text-sm">{{ demande.email }}</p>
                  <p class="text-xs text-body">{{ demande.telephone }}</p>
                </div>
              </td>
              <td class="px-4 py-3 text-sm">
                {{ formatDate(demande.date_creation) }}
              </td>
              <td class="px-4 py-3">
                <span
                  class="inline-block px-3 py-1 rounded-full text-xs font-bold"
                  :class="{
                    'bg-yellow-100 text-yellow-700': demande.status === 'En attente',
                    'bg-green-100 text-green-700': demande.status === 'Approuvée',
                    'bg-red-100 text-red-700': demande.status === 'Refusée',
                  }"
                >
                  {{ demande.status }}
                </span>
              </td>
              <td class="px-4 py-3" @click.stop>
                <div class="flex gap-2 justify-center">
                  <button
                    v-if="demande.status === 'En attente'"
                    @click="ouvrirApprouve(demande)"
                    class="px-3 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors"
                  >
                    Approuver
                  </button>
                  <button
                    v-if="demande.status === 'En attente'"
                    @click="ouvrirRefus(demande)"
                    class="px-3 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors"
                  >
                    Refuser
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="demandesFiltrees.length === 0">
              <td colspan="5" class="text-center px-4 py-8 text-body">
                Aucune demande de membership avec ce statut.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL Détail (Style conforme aux modales de l'app) -->
    <div v-if="modalDetail" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Header avec couleur tertiary-900 -->
        <div class="bg-tertiary-900 text-white px-6 py-4 flex items-center justify-between border-b-4 border-white">
          <h2 class="text-lg font-bold">Demande de membership</h2>
          <button @click="modalDetail = false" class="text-white hover:opacity-80 transition-opacity">
            <Icon icon="mdi:close" class="w-6 h-6" />
          </button>
        </div>

        <!-- Contenu scrollable -->
        <div v-if="demandeSelectionnee" class="overflow-y-auto flex-1 p-8 space-y-8">
          <!-- Section CANDIDAT -->
          <div>
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2">
              <Icon icon="mdi:account-outline" class="w-4 h-4" />
              Candidat
            </h3>
            <div class="grid grid-cols-4 gap-6">
              <div>
                <label class="text-xs font-semibold text-gray-400 uppercase">Prénom</label>
                <p class="text-base font-semibold text-gray-900 mt-1">{{ demandeSelectionnee.prenom }}</p>
              </div>
              <div>
                <label class="text-xs font-semibold text-gray-400 uppercase">Nom</label>
                <p class="text-base font-semibold text-gray-900 mt-1">{{ demandeSelectionnee.nom }}</p>
              </div>
              <div>
                <label class="text-xs font-semibold text-gray-400 uppercase">Date naissance</label>
                <p class="text-base text-gray-900 mt-1">{{ formatDate(demandeSelectionnee.date_naissance) }}</p>
              </div>
              <div>
                <label class="text-xs font-semibold text-gray-400 uppercase">Statut</label>
                <div class="mt-1">
                  <span
                    class="inline-block px-3 py-1 rounded-full text-xs font-bold"
                    :class="{
                      'bg-yellow-100 text-yellow-700': demandeSelectionnee.status === 'En attente',
                      'bg-green-100 text-green-700': demandeSelectionnee.status === 'Approuvée',
                      'bg-red-100 text-red-700': demandeSelectionnee.status === 'Refusée',
                    }"
                  >
                    {{ demandeSelectionnee.status }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Section CONTACT -->
          <div>
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2">
              <Icon icon="mdi:phone-outline" class="w-4 h-4" />
              Contact
            </h3>
            <div class="grid grid-cols-3 gap-6">
              <div>
                <label class="text-xs font-semibold text-gray-400 uppercase">Email</label>
                <p class="text-base text-gray-900 mt-1">{{ demandeSelectionnee.email }}</p>
              </div>
              <div>
                <label class="text-xs font-semibold text-gray-400 uppercase">Téléphone</label>
                <p class="text-base text-gray-900 mt-1">{{ demandeSelectionnee.telephone }}</p>
              </div>
            </div>
          </div>

          <!-- Section ADRESSE -->
          <div>
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2">
              <Icon icon="mdi:map-marker-outline" class="w-4 h-4" />
              Adresse
            </h3>
            <p class="text-base text-gray-900">
              {{ demandeSelectionnee.adresse }}, {{ demandeSelectionnee.code_postal }} {{ demandeSelectionnee.ville }}, {{ demandeSelectionnee.pays }}
            </p>
          </div>

          <!-- Section MOTIVATION -->
          <div>
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2">
              <Icon icon="mdi:file-document-outline" class="w-4 h-4" />
              Motivation
            </h3>
            <div class="bg-gray-50 p-4 rounded border border-gray-200 text-base text-gray-700 whitespace-pre-wrap">
              {{ demandeSelectionnee.description }}
            </div>
          </div>

          <!-- Section DÉCISION (si approuvée/refusée) -->
          <div v-if="demandeSelectionnee.status !== 'En attente'">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2">
              <Icon icon="mdi:check-circle-outline" class="w-4 h-4" />
              Décision
            </h3>
            <div class="space-y-2 text-base text-gray-900">
              <p><strong>Statut :</strong> {{ demandeSelectionnee.status }}</p>
              <p v-if="demandeSelectionnee.date_decision"><strong>Date :</strong> {{ formatDate(demandeSelectionnee.date_decision) }}</p>
              <p v-if="demandeSelectionnee.notes_admin"><strong>Raison :</strong> {{ demandeSelectionnee.notes_admin }}</p>
            </div>
          </div>
        </div>

        <!-- Actions au bas -->
        <div v-if="demandeSelectionnee && demandeSelectionnee.status === 'En attente'" class="border-t border-gray-100 bg-white px-8 py-4 flex gap-3 justify-end">
          <button
            @click="ouvrirRefus(demandeSelectionnee)"
            class="flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors"
          >
            <Icon icon="mdi:close" class="w-4 h-4" />
            Refuser
          </button>
          <button
            @click="ouvrirApprouve(demandeSelectionnee)"
            class="px-6 py-2 bg-[#0e0f54] hover:bg-[#0e0f54]/90 text-white rounded-lg font-semibold transition-colors flex items-center gap-2"
          >
            <Icon icon="mdi:check" class="w-4 h-4" />
            Approuver
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL Approuver -->
    <div v-if="modalApprouver" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl p-6 max-w-md w-full space-y-4 shadow-lg">
        <h3 class="text-lg font-semibold text-heading">Approuver cette demande ?</h3>
        <p class="text-sm text-body">Le rôle membre sera ajouté au compte existant lié à cette adresse email.</p>

        <div class="flex gap-3 justify-end pt-4 border-t">
          <button
            @click="modalApprouver = false"
            :disabled="chargementAction"
            class="px-4 py-2 bg-neutral-secondary-medium text-body rounded-lg font-semibold hover:bg-neutral-secondary-dark transition-colors disabled:opacity-50"
          >
            Annuler
          </button>
          <button
            @click="approuverDemande"
            :disabled="chargementAction"
            class="px-4 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors disabled:bg-gray-400"
          >
            <span v-if="!chargementAction">Approuver</span>
            <span v-else>Traitement...</span>
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL Refuser -->
    <div v-if="modalRefuser" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl p-6 max-w-md w-full space-y-4 shadow-lg">
        <h3 class="text-lg font-semibold text-heading">Refuser cette demande ?</h3>

        <textarea
          v-model="raison"
          class="w-full px-4 py-2 border border-default-medium rounded-lg text-body focus:outline-none focus:ring-2 focus:ring-primary"
          placeholder="Motif du refus..."
          rows="4"
        ></textarea>

        <div class="flex gap-3 justify-end pt-4 border-t">
          <button
            @click="modalRefuser = false"
            class="px-4 py-2 bg-neutral-secondary-medium text-body rounded-lg font-semibold hover:bg-neutral-secondary-dark transition-colors"
          >
            Annuler
          </button>
          <button
            @click="refuserDemande"
            :disabled="!raison.trim() || chargementAction"
            class="px-4 py-2 bg-accent text-white rounded-lg font-semibold hover:bg-accent/90 disabled:bg-gray-400 transition-colors"
          >
            <span v-if="!chargementAction">Refuser</span>
            <span v-else>Traitement...</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Title from '../components/Title.vue';
import { Icon } from '@iconify/vue';
import membershipService from '../services/membershipService';

export default {
  name: 'OrganisateurMembership',
  components: { Title, Icon },

  data() {
    return {
      demandes: [],
      filtreStatus: 'En attente',
      recherche: '',
      modalDetail: false,
      modalApprouver: false,
      modalRefuser: false,
      demandeSelectionnee: null,
      chargement: true,
      chargementAction: false,
      raison: '',
    };
  },

  computed: {
    demandesEnAttente() {
      return this.demandes.filter((d) => d.status === 'En attente');
    },
    demandesApprouvees() {
      return this.demandes.filter((d) => d.status === 'Approuvée');
    },
    demandesRefusees() {
      return this.demandes.filter((d) => d.status === 'Refusée');
    },
    demandesFiltrees() {
      const recherche = this.recherche.trim().toLowerCase();

      return this.demandes.filter((demande) => {
        const correspondStatus = demande.status === this.filtreStatus;

        if (!recherche) {
          return correspondStatus;
        }

        const cible = [
          demande.prenom,
          demande.nom,
          demande.email,
          demande.telephone,
        ]
          .filter(Boolean)
          .join(' ')
          .toLowerCase();

        return correspondStatus && cible.includes(recherche);
      });
    },
  },

  mounted() {
    this.chargerDemandes();
  },

  methods: {
    async chargerDemandes() {
      this.chargement = true;
      try {
        const response = await membershipService.listerDemandes();
        this.demandes = response.data || [];
      } catch (error) {
        console.error('Erreur:', error);
      } finally {
        this.chargement = false;
      }
    },

    ouvrirDetail(demande) {
      this.demandeSelectionnee = JSON.parse(JSON.stringify(demande));
      this.modalDetail = true;
    },

    ouvrirApprouve(demande) {
      this.demandeSelectionnee = demande;
      this.modalApprouver = true;
    },

    ouvrirRefus(demande) {
      this.demandeSelectionnee = demande;
      this.raison = '';
      this.modalRefuser = true;
    },

    async approuverDemande() {
      this.chargementAction = true;
      try {
        await membershipService.approuverDemande(this.demandeSelectionnee.id);
        this.modalApprouver = false;
        this.modalDetail = false;
        await this.chargerDemandes();
        window.dispatchEvent(new CustomEvent('membership-notifications-updated'));
      } catch (error) {
        console.error('Erreur:', error);
      } finally {
        this.chargementAction = false;
      }
    },

    async refuserDemande() {
      if (!this.raison.trim()) return;
      this.chargementAction = true;
      try {
        await membershipService.refuserDemande(this.demandeSelectionnee.id, this.raison);
        this.modalRefuser = false;
        this.modalDetail = false;
        await this.chargerDemandes();
        window.dispatchEvent(new CustomEvent('membership-notifications-updated'));
      } catch (error) {
        console.error('Erreur:', error);
      } finally {
        this.chargementAction = false;
      }
    },

    formatDate(date) {
      if (!date) return '—';
      return new Date(date).toLocaleDateString('fr-CH');
    },
  },
};
</script>
