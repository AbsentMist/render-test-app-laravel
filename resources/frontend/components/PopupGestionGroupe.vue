<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 flex flex-col overflow-hidden max-h-[90vh]">

      <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
        <div>
          <h3 class="text-base font-semibold text-heading">Gérer le groupe</h3>
          <p class="text-xs text-gray-400 mt-0.5">
            {{ groupe.course?.nom ?? '—' }} ·
            <span class="font-medium">{{ typeLabel }}</span>
          </p>
        </div>
        <button type="button" @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="overflow-y-auto px-6 py-5 flex flex-col gap-6">

        <div v-if="inscriptionsFermees" class="flex items-start gap-2 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2">
          <svg class="w-4 h-4 shrink-0 mt-0.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <p>Les inscriptions pour cette course sont clôturées. Vous ne pouvez plus modifier le groupe ni ses membres.</p>
        </div>

        <div v-if="peutModifierNom" class="flex flex-col gap-2">
          <label class="text-sm font-medium text-gray-700">Nom du groupe</label>
          <div class="flex gap-2">
            <input
              v-model="nomGroupe"
              type="text"
              :disabled="inscriptionsFermees"
              class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-white disabled:bg-gray-50 disabled:text-gray-400"
            />
            <button
              type="button"
              @click="sauvegarderNom"
              :disabled="!nomGroupe.trim() || nomGroupe === groupeLocal.nom || sauvegarde || inscriptionsFermees"
              class="btn-tertiary text-sm px-4 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ sauvegarde ? '...' : 'Sauvegarder' }}
            </button>
          </div>
          <p v-if="messageNom" class="text-xs" :class="messageNom.type === 'ok' ? 'text-green-600' : 'text-red-500'">
            {{ messageNom.texte }}
          </p>
        </div>

        <div v-else class="flex items-start gap-2 text-xs text-gray-400 bg-gray-50 rounded-xl px-3 py-2">
          <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p>Le nom du groupe challenge est défini par l'organisateur et ne peut pas être modifié.</p>
        </div>

        <div class="flex flex-col gap-3">
          <div class="flex items-center justify-between">
            <h4 class="text-sm font-semibold text-gray-700">
              Membres
              <span class="text-gray-400 font-normal ml-1">
                ({{ groupeLocal.participants?.length ?? 0 }}
                <template v-if="maxMembres">/ {{ maxMembres }}</template>)
              </span>
            </h4>
            <button
              v-if="estGroupe && peutAjouter"
              type="button"
              @click="ouvrirAjout"
              :disabled="inscriptionsFermees"
              class="flex items-center gap-1 text-xs px-3 py-1.5 rounded-lg transition-colors border"
              :class="inscriptionsFermees ? 'text-gray-400 border-gray-200 bg-gray-50 opacity-50 cursor-not-allowed' : 'text-primary hover:text-tertiary-900 border-gray-200'"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Ajouter un membre
            </button>
          </div>

          <div
            v-for="membre in groupeLocal.participants"
            :key="membre.id"
            class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3 border border-gray-100"
          >
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <div>
                <p class="text-sm font-medium text-gray-800">{{ membre.prenom }} {{ membre.nom }}</p>
                <p class="text-xs text-gray-400">{{ roleLabel(membre) }}</p>
              </div>
            </div>

            <div class="flex items-center gap-2" v-if="!estFondateur(membre)">
              <button
                v-if="!estChallenge"
                type="button"
                @click="ouvrirRemplacement(membre)"
                :disabled="inscriptionsFermees"
                class="text-xs border rounded-lg px-3 py-1.5 transition-colors"
                :class="inscriptionsFermees ? 'text-gray-400 border-gray-200 bg-gray-50 opacity-50 cursor-not-allowed' : 'text-primary hover:text-tertiary-900 border-gray-200'"
              >
                Remplacer
              </button>
              <button
                v-if="estChallenge"
                type="button"
                @click="retirerMembre(membre)"
                :disabled="inscriptionsFermees"
                class="p-1.5 rounded-lg transition-colors"
                :class="inscriptionsFermees ? 'text-gray-400 opacity-50 cursor-not-allowed' : 'text-red-400 hover:text-red-600 hover:bg-red-50'"
                title="Retirer du groupe"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <div v-if="modeFormulaire" class="flex flex-col gap-3 border border-tertiary/30 bg-tertiary/5 rounded-xl p-4">
          <h4 class="text-sm font-semibold text-heading">
            <template v-if="membreARemplacer">
              Remplacer <span class="text-tertiary-900">{{ membreARemplacer.prenom }} {{ membreARemplacer.nom }}</span>
            </template>
            <template v-else>Ajouter un membre</template>
          </h4>

          <div v-if="participantsDisponibles.length > 0" class="flex flex-col gap-2">
            <label class="text-xs font-medium text-gray-600">Choisir parmi mes participants</label>
            <div class="grid grid-cols-2 gap-2">
              <button
                v-for="p in participantsDisponibles"
                :key="p.id"
                type="button"
                @click="nouveauMembre = p; emailRecherche = ''; participantTrouve = null"
                :class="[
                  'flex items-center gap-2 px-3 py-2 rounded-xl border-2 text-sm transition-all text-left',
                  nouveauMembre?.id === p.id
                    ? 'border-tertiary bg-tertiary text-primary'
                    : 'border-gray-200 bg-white text-gray-700 hover:border-tertiary'
                ]"
              >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                {{ p.prenom }} {{ p.nom }}
              </button>
            </div>
          </div>

          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-600">Ou rechercher par email</label>
            <div class="flex gap-2">
              <input
                v-model="emailRecherche"
                type="email"
                placeholder="email@exemple.com"
                class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40"
                @keyup.enter="rechercherParEmail"
              />
              <button type="button" @click="rechercherParEmail" class="btn-tertiary text-sm px-3">
                Rechercher
              </button>
            </div>
            <div v-if="participantTrouve" class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 mt-1">
              <span class="text-sm font-medium">{{ participantTrouve.prenom }} {{ participantTrouve.nom }}</span>
              <button type="button" @click="nouveauMembre = participantTrouve" class="btn-tertiary text-xs px-3 py-1">
                Sélectionner
              </button>
            </div>
            <p v-if="erreurRecherche" class="text-xs text-orange-600">{{ erreurRecherche }}</p>
          </div>

          <div v-if="nouveauMembre" class="flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-4 py-3">
            <span class="text-sm text-green-700">
              <template v-if="membreARemplacer">
                Remplacer par <strong>{{ nouveauMembre.prenom }} {{ nouveauMembre.nom }}</strong>
              </template>
              <template v-else>
                Ajouter <strong>{{ nouveauMembre.prenom }} {{ nouveauMembre.nom }}</strong>
              </template>
            </span>
            <div class="flex gap-2">
              <button type="button" @click="confirmerAction" :disabled="enCours"
                class="btn-tertiary text-xs px-3 py-1 disabled:opacity-50">
                {{ enCours ? '...' : 'Confirmer' }}
              </button>
              <button type="button" @click="annulerFormulaire" class="text-xs text-gray-400 hover:text-gray-600 px-2">
                Annuler
              </button>
            </div>
          </div>

          <p v-if="messageAction" class="text-xs" :class="messageAction.type === 'ok' ? 'text-green-600' : 'text-red-500'">
            {{ messageAction.texte }}
          </p>
        </div>

      </div>

      <div class="flex justify-end px-6 py-4 border-t border-gray-100">
        <button type="button" @click="$emit('close')" class="btn-accent-300 text-sm">
          Fermer
        </button>
      </div>

    </div>
  </div>
</template>

<script>
import groupeService from '../services/groupeService';
import api from '../services/api';
import { useAuthStore } from '../stores/auth';

export default {
  name: 'PopupGestionGroupe',
  props: {
    groupe:          { type: Object, required: true },
    mesParticipants: { type: Array, default: () => [] },
  },
  emits: ['close', 'mis-a-jour'],
  data() {
    return {
      groupeLocal:       JSON.parse(JSON.stringify(this.groupe)),
      nomGroupe:         this.groupe.nom,
      sauvegarde:        false,
      messageNom:        null,
      membreARemplacer:  null,
      modeFormulaire:    false,
      nouveauMembre:     null,
      emailRecherche:    '',
      participantTrouve: null,
      erreurRecherche:   null,
      enCours:           false,
      messageAction:     null,
    };
  },
  computed: {
    // Calcul de la date de fermeture
    inscriptionsFermees() {
      if (!this.groupeLocal.course?.fin_inscription) return false;
      const fin = new Date(this.groupeLocal.course.fin_inscription);
      fin.setHours(23, 59, 59, 999);
      return new Date() > fin;
    },
    // Type de la course liée
    typeCourse() {
      return this.groupeLocal.course?.type ?? '';
    },
    estRelais() {
      return this.typeCourse === 'Relais';
    },
    estGroupe() {
      return this.typeCourse === 'Groupe';
    },
    estChallenge() {
      // Challenge = type 'Entreprise' ou 'Groupe' dans la table Groupe (pas de fondateur)
      return this.groupeLocal.type === 'Entreprise' ||
        !this.groupeLocal.participants?.some(p =>
          p.pivot?.statut === 'Fondateur' || p.pivot?.statut === 'fondateur'
        );
    },
    typeLabel() {
      if (this.estChallenge) return 'Challenge';
      if (this.estRelais)    return 'Relais';
      if (this.estGroupe)    return 'Groupe';
      return this.typeCourse;
    },
    peutModifierNom() {
      return !this.estChallenge;
    },
    maxMembres() {
      return this.groupeLocal.course?.max_nb_personne ?? null;
    },
    peutAjouter() {
      if (!this.maxMembres) return true;
      return (this.groupeLocal.participants?.length ?? 0) < this.maxMembres;
    },
    // Participants disponibles (pas déjà dans le groupe)
    participantsDisponibles() {
      const idsGroupe = this.groupeLocal.participants?.map(p => p.id) ?? [];
      return this.mesParticipants.filter(p => !idsGroupe.includes(p.id));
    },
  },
  methods: {
    monId() {
      return useAuthStore().user?.participant?.id;
    },
    estFondateur(membre) {
      return membre.pivot?.statut === 'Fondateur' || membre.pivot?.statut === 'fondateur';
    },
    roleLabel(membre) {
      if (this.estFondateur(membre)) return 'Fondateur';
      if (membre.pivot?.statut === 'En attente') return 'En attente';
      return 'Membre';
    },

    // ── Nom ──────────────────────────────────────────────────────────────────
    async sauvegarderNom() {
    if (!this.nomGroupe.trim() || this.sauvegarde) return;
    this.sauvegarde = true;
    this.messageNom = null;
    try {
        await groupeService.updateGroupe(this.groupeLocal.id, { nom: this.nomGroupe.trim() });
        this.groupeLocal.nom = this.nomGroupe.trim();
        this.messageNom = { type: 'ok', texte: 'Nom modifié avec succès !' };
        this.$emit('mis-a-jour', { ...this.groupeLocal });
   } catch (e) {
    console.log('Erreur sauvegarderNom:', e); // ← ajouter
    this.messageNom = { type: 'erreur', texte: 'Impossible de modifier le nom.' };
} finally {
        this.sauvegarde = false;
    }
},

    // ── Formulaire ajout/remplacement ─────────────────────────────────────────
    ouvrirRemplacement(membre) {
      this.membreARemplacer  = membre;
      this.modeFormulaire    = true;
      this.nouveauMembre     = null;
      this.emailRecherche    = '';
      this.participantTrouve = null;
      this.erreurRecherche   = null;
      this.messageAction     = null;
    },
    ouvrirAjout() {
      this.membreARemplacer  = null;
      this.modeFormulaire    = true;
      this.nouveauMembre     = null;
      this.emailRecherche    = '';
      this.participantTrouve = null;
      this.erreurRecherche   = null;
      this.messageAction     = null;
    },
    annulerFormulaire() {
      this.membreARemplacer  = null;
      this.modeFormulaire    = false;
      this.nouveauMembre     = null;
      this.emailRecherche    = '';
      this.participantTrouve = null;
    },

    async rechercherParEmail() {
      if (!this.emailRecherche) return;
      this.participantTrouve = null;
      this.erreurRecherche   = null;
      try {
        const response = await api.get('/participant/rechercher-participant', { params: { email: this.emailRecherche } });
        this.participantTrouve = response.data;
      } catch {
        this.erreurRecherche = 'Aucun participant trouvé avec cette adresse email.';
      }
    },

    // ── Confirmer remplacement ou ajout ───────────────────────────────────────
    async confirmerAction() {
      if (!this.nouveauMembre || this.enCours) return;
      this.enCours = true;
      this.messageAction = null;
      try {
        if (this.membreARemplacer) {
          // Remplacement : retirer l'ancien + ajouter le nouveau
          await groupeService.removeParticipant(this.groupeLocal.id, this.membreARemplacer.id);
          await groupeService.addParticipant(this.groupeLocal.id, this.nouveauMembre.id);
          this.messageAction = { type: 'ok', texte: 'Membre remplacé avec succès !' };
        } else {
          // Ajout simple
          await groupeService.addParticipant(this.groupeLocal.id, this.nouveauMembre.id);
          this.messageAction = { type: 'ok', texte: 'Membre ajouté avec succès !' };
        }
        // Recharger le groupe
        const response = await groupeService.getGroupe(this.groupeLocal.id);
        this.groupeLocal = response.data;
        this.membreARemplacer = null;
        this.modeFormulaire   = false;
        this.nouveauMembre    = null;
        this.$emit('mis-a-jour', this.groupeLocal);
      } catch (e) {
        this.messageAction = { type: 'erreur', texte: 'Impossible d\'effectuer cette action. Veuillez réessayer.' };
      } finally {
        this.enCours = false;
      }
    },

    // ── Retirer membre (Challenge) ─────────────────────────────────────────────
    async retirerMembre(membre) {
      if (!confirm(`Retirer ${membre.prenom} ${membre.nom} du groupe ?`)) return;
      try {
        await groupeService.removeParticipant(this.groupeLocal.id, membre.id);
        const response = await groupeService.getGroupe(this.groupeLocal.id);
        this.groupeLocal = response.data;
        this.$emit('mis-a-jour', this.groupeLocal);
      } catch (e) {
        console.error('Erreur retrait membre:', e);
      }
    },
  },
};
</script>