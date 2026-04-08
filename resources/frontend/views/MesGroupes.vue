<template>
  <Title :texte="`Mes groupes`" />
  <div class="p-6">

    <div v-if="chargement" class="text-body text-center py-8">
      Chargement des groupes...
    </div>

    <div v-else-if="groupes.length === 0" class="text-center py-12 text-gray-400">
      <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
      </svg>
      <p class="text-sm">Vous n'êtes membre d'aucun groupe pour le moment.</p>
    </div>

    <div v-else class="overflow-x-auto rounded-xl border border-default-medium">
      <table class="w-full text-sm text-left text-body">
        <thead class="bg-neutral-secondary-medium text-heading text-xs uppercase">
          <tr>
            <th class="px-4 py-3">Nom du groupe</th>
            <th class="px-4 py-3">Course</th>
            <th class="px-4 py-3">Type</th>
            <th class="px-4 py-3 text-center">Membres</th>
            <th class="px-4 py-3 text-center">Mon rôle</th>
            <th class="px-4 py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="groupe in groupes"
            :key="groupe.id"
            class="border-t border-default-medium hover:bg-neutral-secondary-medium transition-colors"
          >
            <td class="px-4 py-3 font-medium text-heading">{{ groupe.nom }}</td>
            <td class="px-4 py-3">{{ groupe.course?.nom ?? '—' }}</td>
            <td class="px-4 py-3">{{ groupe.course?.type ?? '—' }}</td>            <td class="px-4 py-3 text-center">{{ groupe.participants?.length ?? 0 }}</td>
            <td class="px-4 py-3 text-center">
              <span
                class="px-2 py-0.5 rounded-full text-xs font-semibold"
                :class="monRole(groupe) === 'Fondateur' ? 'bg-tertiary text-primary' : 'bg-gray-100 text-gray-600'"
              >
                {{ monRole(groupe) }}
              </span>
            </td>
            <td class="px-4 py-3 text-center">
              <button
                v-if="monRole(groupe) === 'Fondateur'"
                @click="ouvrirGestion(groupe)"
                class="p-1.5 rounded-lg text-primary hover:bg-tertiary transition-colors"
                title="Gérer le groupe"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </button>
              <span v-else class="text-xs text-gray-400">—</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>

  <!-- Popup gestion groupe -->
  <PopupGestionGroupe
    v-if="groupeSelectionne"
    :groupe="groupeSelectionne"
    :mes-participants="mesParticipants"
    @close="groupeSelectionne = null"
    @mis-a-jour="onGroupeMisAJour"
  />
</template>

<script>
/**
 * @fileoverview Vue MesGroupes.
 * @description Écran participant de consultation et gestion des groupes actifs.
 * @remarks Affiche les groupes de type relais/groupe, ouvre la popup de gestion
 * et répercute les modifications renvoyées par celle-ci.
 */
import Title from '../components/Title.vue';
import PopupGestionGroupe from '../components/PopupGestionGroupe.vue';
import groupeService from '../services/groupeService';
import participantService from '../services/participantService';
import { useAuthStore } from '../stores/auth';

export default {
  name: 'MesGroupes',
  components: { Title, PopupGestionGroupe },
  data() {
    return {
      groupes: [],
      mesParticipants: [],
      chargement: true,
      groupeSelectionne: null,
    };
  },
  methods: {
    /**
     * Détermine le rôle courant de l'utilisateur dans un groupe.
     * @param {Object} groupe
     * @returns {string}
     */
    monRole(groupe) {
      const authStore = useAuthStore();
      const monId = authStore.user?.participant?.id;
      const moi = groupe.participants?.find(p => p.id === monId);
      if (!moi) return '—';
      const statut = moi.pivot?.statut ?? '';
      if (statut === 'Fondateur' || statut === 'fondateur') return 'Fondateur';
      return 'Membre';
    },
    /**
     * Ouvre la popup de gestion pour le groupe sélectionné.
     * @param {Object} groupe
     * @returns {void}
     */
    ouvrirGestion(groupe) {
      this.groupeSelectionne = { ...groupe };
    },
    /**
     * Met à jour la liste locale après une modification validée.
     * @param {Object} groupeMaj
     * @returns {void}
     */
    onGroupeMisAJour(groupeMaj) {
      const idx = this.groupes.findIndex(g => g.id === groupeMaj.id);
      if (idx > -1) this.groupes.splice(idx, 1, groupeMaj);
      this.groupeSelectionne = null;
    },
    /**
     * Charge les groupes gérés par le participant.
     * @returns {Promise<void>}
     */
    async chargerGroupes() {
    this.chargement = true;
    try {
        const response = await groupeService.getGroupes();
        // Filtrer : garder uniquement les groupes avec un fondateur (Relais/Groupe)
        // Exclure les groupes challenge (Entreprise) qui n'ont pas de fondateur
        this.groupes = response.data.filter(g =>
    g.type === 'Groupe' &&
    (g.course?.type === 'Relais' || g.course?.type === 'Groupe')
);
    } catch (e) {
        console.error('Erreur chargement groupes:', e);
    } finally {
        this.chargement = false;
    }
},
    /**
     * Charge les participants liés au compte utilisateur.
     * @returns {Promise<void>}
     */
    async chargerParticipants() {
      try {
        const response = await participantService.getMesParticipants();
        this.mesParticipants = response.data;
      } catch (e) {
        console.error('Erreur chargement participants:', e);
      }
    },
  },
  async mounted() {
    await Promise.all([this.chargerGroupes(), this.chargerParticipants()]);
  },
};
</script>