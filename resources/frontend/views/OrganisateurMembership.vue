<template>
  <div>
    <Title texte="Membership" />

    <div class="p-6 space-y-6">
      <div v-if="messageSucces" class="rounded-xl border border-green-200 bg-green-50 p-4">
        <p class="text-green-800 text-sm font-semibold">{{ messageSucces }}</p>
      </div>

      <div v-if="messageErreur" class="rounded-xl border border-red-200 bg-red-50 p-4">
        <p class="text-red-700 text-sm font-semibold">{{ messageErreur }}</p>
      </div>

      <div class="flex flex-col gap-3 mb-6">
        <button
          @click="ouvrirNouvelleInvitation"
          class="btn-tertiary px-4 py-2 rounded-lg inline-block w-fit shadow-xs"
        >
          Nouveau
        </button>

        <div class="flex flex-wrap items-center gap-3">
          <div class="relative flex-1 min-w-62.5">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <svg class="w-4 h-4 text-body" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input
              v-model="recherche"
              type="text"
              placeholder="Rechercher par nom, prénom, téléphone, email..."
              class="w-full pl-9 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base px-3 py-2 focus:ring-brand focus:border-brand shadow-xs"
            />
          </div>

          <div class="relative">
            <button
              @click.stop="showStatusDropdown = !showStatusDropdown"
              class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base px-3 py-2 focus:ring-brand focus:border-brand shadow-xs inline-flex items-center gap-2 min-w-[180px] justify-between hover:bg-neutral-tertiary-medium transition-colors"
            >
              <span>{{ filtreStatus === 'Tous' ? 'Tous les statuts' : filtreStatus }}</span>
              <Icon :icon="showStatusDropdown ? 'mdi:chevron-up' : 'mdi:chevron-down'" class="w-4 h-4" />
            </button>

            <div v-if="showStatusDropdown" class="absolute right-0 mt-2 w-56 bg-white border border-default-medium rounded-lg shadow-lg z-30 overflow-hidden">
              <button
                v-for="status in statusOptions"
                :key="status"
                @click="choisirStatus(status)"
                class="w-full text-left px-4 py-2 text-sm hover:bg-neutral-secondary-medium"
              >
                {{ status }}
              </button>
            </div>
          </div>

          <span class="text-xs text-body px-2">{{ demandesFiltrees.length }} résultat(s)</span>
        </div>
      </div>

      <div v-if="chargement" class="text-body text-center py-12">
        <p class="mb-2">Chargement des demandes...</p>
      </div>

      <div v-else class="overflow-x-auto rounded-xl border border-default-medium">
        <table class="w-full text-sm">
          <thead class="bg-neutral-secondary-medium text-heading text-xs uppercase">
            <tr>
              <th class="px-4 py-3 text-left">Participant</th>
              <th class="px-4 py-3 text-left">Contact</th>
              <th class="px-4 py-3 text-left">Date formulaire</th>
              <th class="px-4 py-3 text-left">Statut formulaire</th>
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
                <p class="font-semibold text-heading">{{ demande.prenom }} {{ demande.nom }}</p>
              </td>
              <td class="px-4 py-3">
                <div class="space-y-0.5">
                  <p class="text-heading text-sm">{{ demande.email }}</p>
                </div>
              </td>
              <td class="px-4 py-3 text-sm">{{ formatDate(demande.date_creation) }}</td>
              <td class="px-4 py-3">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold" :class="badgeFormulaireClass(demande.status)">
                  {{ labelStatusFormulaire(demande.status) }}
                </span>
              </td>
              <td class="px-4 py-3" @click.stop>
                <div class="flex gap-2 justify-center flex-wrap">
                  <button
                    v-if="demande.status === 'En attente de validation'"
                    @click="ouvrirApprouve(demande)"
                    class="px-3 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors"
                  >
                    Approuver
                  </button>
                  <button
                    v-if="demande.status === 'En attente de validation'"
                    @click="ouvrirACompleter(demande)"
                    class="px-3 py-1 text-xs font-semibold bg-amber-100 text-amber-700 rounded-lg hover:bg-amber-200 transition-colors"
                  >
                    À compléter
                  </button>
                  <button
                    v-if="demande.invitation_status !== 'Annulé' && demande.status !== 'Approuvée'"
                    @click="ouvrirAnnulerInvitation(demande)"
                    class="px-3 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors"
                  >
                    Annuler invitation
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="demandesFiltrees.length === 0">
              <td colspan="6" class="text-center px-4 py-8 text-body">Aucun formulaire membership pour ce filtre.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="modalDetail" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-6xl mx-4 flex flex-col overflow-hidden" style="height: 80vh">
        <div class="flex items-center justify-between px-6 pt-5 pb-0 border-b border-gray-100 bg-tertiary-900">
          <div class="flex flex-col w-full">
            <div class="flex items-center justify-between">
              <div>
                <span class="px-6 text-subtitle font-medium text-secondary">Formulaire Membership</span>
                <div class="h-1 w-24 ml-6 rounded-r-full mb-2" :style="{ backgroundColor: demandeSelectionnee?.invitation_status === 'Complété' ? '#d9f20b' : '#f0c96a' }"></div>
              </div>
              <button @click="modalDetail = false" class="text-secondary hover:text-gray-600 transition-colors mr-1 self-start mt-1">
                <Icon icon="mdi:close" class="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>

        <div class="flex-1 overflow-y-auto pb-20">
          <div v-if="demandeSelectionnee" class="p-6 space-y-6">
            <section>
              <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                <Icon icon="mdi:account" class="w-4 h-4" />
                Participant
              </h3>
              <div class="bg-gray-50 rounded-xl p-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                  <p class="text-xs text-gray-400">Nom complet</p>
                  <p class="font-medium text-gray-800">{{ demandeSelectionnee.prenom }} {{ demandeSelectionnee.nom }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Email</p>
                  <p class="font-medium text-gray-800">{{ demandeSelectionnee.email }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Prix</p>
                  <p class="font-medium text-gray-800">CHF {{ demandeSelectionnee.prix ?? '25.00' }}</p>
                </div>
              </div>
            </section>

            <section>
              <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                <Icon icon="mdi:receipt-text" class="w-4 h-4" />
                Membership
              </h3>
              <div class="bg-gray-50 rounded-xl p-4 grid grid-cols-2 md:grid-cols-5 gap-4">
                <div>
                  <p class="text-xs text-gray-400">Statut formulaire</p>
                  <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold mt-1" :class="badgeFormulaireClass(demandeSelectionnee.status)">
                    {{ labelStatusFormulaire(demandeSelectionnee.status) }}
                  </span>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Date formulaire</p>
                  <p class="font-medium text-gray-800">{{ formatDate(demandeSelectionnee.date_creation) }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Date décision</p>
                  <p class="font-medium text-gray-800">{{ formatDate(demandeSelectionnee.date_decision) }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Invitation</p>
                  <p class="font-medium text-gray-800">#{{ demandeSelectionnee.id_invitation ?? '—' }}</p>
                </div>
                <div class="col-span-2 md:col-span-5">
                  <p class="text-xs text-gray-400 mb-1">Adresse</p>
                  <p class="font-medium text-gray-800">{{ demandeSelectionnee.adresse }}, {{ demandeSelectionnee.code_postal }} {{ demandeSelectionnee.ville }}, {{ demandeSelectionnee.pays }}</p>
                </div>
                <div class="col-span-2 md:col-span-5">
                  <p class="text-xs text-gray-400 mb-1">Description</p>
                  <p class="font-medium text-gray-800 leading-relaxed">{{ demandeSelectionnee.description }}</p>
                </div>
              </div>
            </section>
          </div>
        </div>

        <div class="absolute bottom-0 left-0 right-0 border-t px-6 py-3 flex items-center justify-between transition-colors z-10 bg-white border-gray-100">
          <div class="flex items-center gap-2">
            <Icon icon="mdi:information-outline" class="w-5 h-5 text-accent" />
            <span class="text-sm font-medium text-amber-700">Consultation du formulaire membership</span>
          </div>
          <button class="flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors" @click="modalDetail = false">
            <Icon icon="mdi:close" class="w-4 h-4" />
            Fermer
          </button>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <div
        v-if="modalNouvelleInvitation"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/30"
        @click.self="modalNouvelleInvitation = false"
      >
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 flex flex-col overflow-hidden max-h-[90vh]">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-semibold text-heading">Nouvelle invitation membership</h3>
            <button
              type="button"
              @click="modalNouvelleInvitation = false"
              class="text-gray-400 hover:text-gray-600"
            >
              <Icon icon="mdi:close" class="w-5 h-5" />
            </button>
          </div>

          <div class="overflow-y-auto px-6 py-5 flex flex-col gap-4">
            <div class="flex flex-col gap-2">
              <label class="text-sm font-medium text-gray-700">Adresse email</label>
              <div class="flex gap-2">
                <input
                  v-model="invitationForm.email"
                  type="email"
                  placeholder="participant@email.ch"
                  class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40"
                  @keyup.enter="rechercherParticipantInvitation"
                />
                <button
                  type="button"
                  @click="rechercherParticipantInvitation"
                  class="btn-tertiary text-sm px-3"
                >
                  Rechercher
                </button>
              </div>
              <p v-if="invitationForm.erreur" class="flex items-center gap-2 text-xs text-orange-600">
                <Icon icon="mdi:information-outline" class="w-4 h-4 shrink-0" />
                {{ invitationForm.erreur }}
              </p>
            </div>

            <div
              v-if="invitationForm.participant"
              class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-xl px-4 py-3"
            >
              <div class="flex items-center gap-3">
                <Icon icon="mdi:account-check-outline" class="w-5 h-5 text-tertiary-900" />
                <span class="text-sm font-medium">
                  {{ invitationForm.participant.prenom }} {{ invitationForm.participant.nom }}
                </span>
              </div>
              <button
                type="button"
                @click="selectionnerParticipantTrouve"
                class="btn-tertiary text-xs px-3 py-1"
              >
                Sélectionner
              </button>
            </div>

            <div class="flex flex-col gap-2">
              <label class="text-sm font-medium text-gray-700">Commentaire (optionnel)</label>
              <textarea
                v-model="invitationForm.commentaire_admin"
                rows="3"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40"
                placeholder="Message pour le participant"
              ></textarea>
            </div>
          </div>

          <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100">
            <button
              @click="modalNouvelleInvitation = false"
              class="btn-accent-300 text-sm px-4 py-2"
              type="button"
            >
              Annuler
            </button>
            <button
              @click="creerInvitation"
              :disabled="!invitationForm.participant || chargementAction || !invitationForm.selectionnee"
              class="btn-tertiary text-sm px-4 py-2 disabled:opacity-50 disabled:cursor-not-allowed"
              type="button"
            >
              <span v-if="!chargementAction">Envoyer l'invitation</span>
              <span v-else>Envoi...</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <div v-if="modalApprouver" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl p-6 max-w-md w-full space-y-4 shadow-lg">
        <h3 class="text-lg font-semibold text-heading">Approuver ce formulaire ?</h3>
        <div class="flex gap-3 justify-end pt-4 border-t">
          <button @click="modalApprouver = false" class="px-4 py-2 bg-neutral-secondary-medium rounded-lg font-semibold">Annuler</button>
          <button @click="approuverDemande" :disabled="chargementAction" class="px-4 py-2 bg-green-600 text-white rounded-lg font-semibold disabled:opacity-50">
            <span v-if="!chargementAction">Approuver</span>
            <span v-else>Traitement...</span>
          </button>
        </div>
      </div>
    </div>

    <div v-if="modalACompleter" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl p-6 max-w-md w-full space-y-4 shadow-lg">
        <h3 class="text-lg font-semibold text-heading">Remettre à compléter</h3>
        <textarea
          v-model="commentaireACompleter"
          rows="4"
          class="w-full px-4 py-2 border border-default-medium rounded-lg"
          placeholder="Expliquez ce qu'il faut compléter"
        ></textarea>
        <div class="flex gap-3 justify-end pt-4 border-t">
          <button @click="modalACompleter = false" class="px-4 py-2 bg-neutral-secondary-medium rounded-lg font-semibold">Annuler</button>
          <button
            @click="remettreACompleterDemande"
            :disabled="!commentaireACompleter.trim() || chargementAction"
            class="px-4 py-2 bg-amber-600 text-white rounded-lg font-semibold disabled:opacity-50"
          >
            <span v-if="!chargementAction">Confirmer</span>
            <span v-else>Traitement...</span>
          </button>
        </div>
      </div>
    </div>

    <div v-if="modalAnnulerInvitation" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl p-6 max-w-md w-full space-y-4 shadow-lg">
        <h3 class="text-lg font-semibold text-heading">Annuler cette invitation ?</h3>
        <textarea
          v-model="commentaireAnnulation"
          rows="3"
          class="w-full px-4 py-2 border border-default-medium rounded-lg"
          placeholder="Commentaire d'annulation (optionnel)"
        ></textarea>
        <div class="flex gap-3 justify-end pt-4 border-t">
          <button @click="modalAnnulerInvitation = false" class="px-4 py-2 bg-neutral-secondary-medium rounded-lg font-semibold">Retour</button>
          <button @click="annulerInvitation" :disabled="chargementAction" class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold disabled:opacity-50">
            <span v-if="!chargementAction">Annuler l'invitation</span>
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
      recherche: '',
      filtreStatus: 'Tous',
      statusOptions: ['Tous', 'À compléter', 'En attente de validation', 'Approuvée', 'Annulé'],
      showStatusDropdown: false,
      modalDetail: false,
      modalApprouver: false,
      modalACompleter: false,
      modalAnnulerInvitation: false,
      modalNouvelleInvitation: false,
      demandeSelectionnee: null,
      chargement: true,
      chargementAction: false,
      messageSucces: '',
      messageErreur: '',
      commentaireACompleter: '',
      commentaireAnnulation: '',
      invitationForm: {
        email: '',
        commentaire_admin: '',
        participant: null,
        selectionnee: false,
        erreur: '',
      },
    };
  },

  computed: {
    demandesFiltrees() {
      const recherche = this.recherche.trim().toLowerCase();
      const source = Array.isArray(this.demandes) ? this.demandes : [];

      return source.filter((demande) => {
        const statusOk = this.filtreStatus === 'Tous' || demande.status === this.filtreStatus;
        if (!statusOk) return false;

        if (!recherche) return true;

        const cible = [demande.prenom, demande.nom, demande.email, demande.telephone]
          .filter(Boolean)
          .join(' ')
          .toLowerCase();

        return cible.includes(recherche);
      });
    },
  },

  mounted() {
    this.chargerDemandes();
    document.addEventListener('click', this.handleOutsideClick);
  },

  beforeUnmount() {
    document.removeEventListener('click', this.handleOutsideClick);
  },

  methods: {
    async chargerDemandes() {
      this.chargement = true;
      this.messageErreur = '';
      try {
        const response = await membershipService.listerDemandes();
        this.demandes = Array.isArray(response.data) ? response.data : [];
      } catch (error) {
        console.error('Erreur chargement membership:', error);
        this.messageErreur = 'Impossible de charger les formulaires membership.';
        this.demandes = [];
      } finally {
        this.chargement = false;
      }
    },

    handleOutsideClick() {
      this.showStatusDropdown = false;
    },

    choisirStatus(status) {
      this.filtreStatus = status;
      this.showStatusDropdown = false;
    },

    badgeFormulaireClass(status) {
      if (status === 'En attente de validation') return 'bg-yellow-100 text-yellow-700';
      if (status === 'Approuvée') return 'bg-green-100 text-green-700';
      if (status === 'À compléter') return 'bg-amber-100 text-amber-700';
      if (status === 'Annulé') return 'bg-red-100 text-red-700';
      return 'bg-gray-100 text-gray-700';
    },

    badgeInvitationClass(status) {
      if (status === 'En cours') return 'bg-blue-100 text-blue-700';
      if (status === 'Complété') return 'bg-green-100 text-green-700';
      if (status === 'Annulé') return 'bg-red-100 text-red-700';
      return 'bg-gray-100 text-gray-700';
    },

    labelStatusFormulaire(status) {
      if (!status) return '—';

      return {
        'À compléter': 'à compléter',
        'En attente de validation': 'en attente de validation',
        'Approuvée': 'approuvée',
        'Annulé': 'annulé',
      }[status] || status;
    },

    labelStatusInvitation(status) {
      if (!status) return '—';

      return {
        'En cours': 'en cours',
        'Complété': 'complété',
        'Annulé': 'annulé',
      }[status] || status;
    },

    formatDate(date) {
      if (!date) return '—';
      return new Date(date).toLocaleDateString('fr-CH');
    },

    ouvrirDetail(demande) {
      this.demandeSelectionnee = JSON.parse(JSON.stringify(demande));
      this.modalDetail = true;
    },

    ouvrirNouvelleInvitation() {
      this.invitationForm = {
        email: '',
        commentaire_admin: '',
        participant: null,
        selectionnee: false,
        erreur: '',
      };
      this.modalNouvelleInvitation = true;
    },

    async rechercherParticipantInvitation() {
      this.invitationForm.erreur = '';
      this.invitationForm.participant = null;
      this.invitationForm.selectionnee = false;

      const email = String(this.invitationForm.email || '').trim();
      if (!email) {
        this.invitationForm.erreur = 'Veuillez saisir un email.';
        return;
      }

      try {
        const response = await membershipService.rechercherParticipantParEmail(email);
        this.invitationForm.participant = response.data;
      } catch (error) {
        this.invitationForm.erreur = error.response?.data?.message || 'Participant introuvable.';
      }
    },

    selectionnerParticipantTrouve() {
      if (this.invitationForm.participant) {
        this.invitationForm.selectionnee = true;
      }
    },

    async creerInvitation() {
      if (!this.invitationForm.participant || !this.invitationForm.selectionnee) return;

      this.messageSucces = '';
      this.messageErreur = '';
      this.chargementAction = true;
      try {
        await membershipService.inviterParticipant({
          email: this.invitationForm.participant.email,
          commentaire_admin: this.invitationForm.commentaire_admin,
        });
        this.modalNouvelleInvitation = false;
        this.messageSucces = 'Invitation membership envoyée avec succès.';
        await this.chargerDemandes();
        window.dispatchEvent(new CustomEvent('membership-notifications-updated'));
      } catch (error) {
        this.invitationForm.erreur = error.response?.data?.message || 'Impossible de créer l\'invitation.';
        this.messageErreur = this.invitationForm.erreur;
      } finally {
        this.chargementAction = false;
      }
    },

    ouvrirApprouve(demande) {
      this.demandeSelectionnee = demande;
      this.modalApprouver = true;
    },

    async approuverDemande() {
      if (!this.demandeSelectionnee) return;
      this.messageSucces = '';
      this.messageErreur = '';
      this.chargementAction = true;
      try {
        await membershipService.approuverDemande(this.demandeSelectionnee.id);
        this.modalApprouver = false;
        this.modalDetail = false;
        this.messageSucces = 'Formulaire membership approuvé avec succès.';
        await this.chargerDemandes();
        window.dispatchEvent(new CustomEvent('membership-notifications-updated'));
      } catch (error) {
        console.error('Erreur approbation:', error);
        this.messageErreur = error.response?.data?.message || 'Impossible d\'approuver ce formulaire membership.';
      } finally {
        this.chargementAction = false;
      }
    },

    ouvrirACompleter(demande) {
      this.demandeSelectionnee = demande;
      this.commentaireACompleter = '';
      this.modalACompleter = true;
    },

    async remettreACompleterDemande() {
      if (!this.demandeSelectionnee || !this.commentaireACompleter.trim()) return;
      this.messageSucces = '';
      this.messageErreur = '';
      this.chargementAction = true;
      try {
        await membershipService.remettreACompleterDemande(this.demandeSelectionnee.id, this.commentaireACompleter);
        this.modalACompleter = false;
        this.modalDetail = false;
        this.messageSucces = 'Le formulaire a été remis à compléter.';
        await this.chargerDemandes();
        window.dispatchEvent(new CustomEvent('membership-notifications-updated'));
      } catch (error) {
        console.error('Erreur remise a completer:', error);
        this.messageErreur = error.response?.data?.message || 'Impossible de remettre ce formulaire à compléter.';
      } finally {
        this.chargementAction = false;
      }
    },

    ouvrirAnnulerInvitation(demande) {
      this.demandeSelectionnee = demande;
      this.commentaireAnnulation = '';
      this.modalAnnulerInvitation = true;
    },

    async annulerInvitation() {
      const invitationId = this.demandeSelectionnee?.id_invitation;
      if (!invitationId) return;

      this.messageSucces = '';
      this.messageErreur = '';
      this.chargementAction = true;
      try {
        await membershipService.annulerInvitation(invitationId, this.commentaireAnnulation);
        this.modalAnnulerInvitation = false;
        this.modalDetail = false;
        this.messageSucces = 'Invitation membership annulée avec succès.';
        await this.chargerDemandes();
        window.dispatchEvent(new CustomEvent('membership-notifications-updated'));
      } catch (error) {
        console.error('Erreur annulation invitation:', error);
        this.messageErreur = error.response?.data?.message || 'Impossible d\'annuler cette invitation.';
      } finally {
        this.chargementAction = false;
      }
    },
  },
};
</script>
