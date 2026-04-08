<template>
  <Title :texte="`Mes inscriptions`" />
  <div class="p-6">
    <p v-if="erreur" class="text-accent text-label mb-4">{{ erreur }}</p>
    <div v-if="chargement" class="text-body text-center py-8">
      Chargement des inscriptions...
    </div>
    <div v-else class="overflow-x-auto rounded-xl border border-default-medium">
      <table class="w-full text-sm text-left text-body">
        <thead class="bg-neutral-secondary-medium text-heading text-xs uppercase">
          <tr>
            <th class="px-4 py-3 text-center">Evènement</th>
            <th class="px-4 py-3 text-center">Groupe</th>
            <th class="px-4 py-3 text-center">Equipe/club</th>
            <th class="px-4 py-3 text-center">Tarif</th>
            <th class="px-4 py-3 text-center">Status</th>
            <th class="px-4 py-3 text-center">N° Dossard</th>
            <th class="px-4 py-3 text-center">Participant</th>
            <th class="px-4 py-3 w-8"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="inscriptions.length === 0">
            <td colspan="9" class="text-center px-4 py-6 text-body">
              Aucune inscription trouvé.
            </td>
          </tr>

          <template v-for="inscription in inscriptions" :key="inscription.id">
            <!-- Ligne principale -->
            <tr
              class="border-t border-default-medium hover:bg-neutral-secondary-medium transition-colors"
              :class="inscription.status_paiement=='Annulé' ? 'bg-accent-600' : ''"
            >
              <td class="px-4 py-3 font-medium text-heading">
                {{ inscription.course.evenement.nom }} -
                {{ inscription.course.nom }}
              </td>
              <td class="px-4 py-3">{{ inscription.groupe?.nom ?? '—' }}</td>
              <td class="px-4 py-3">{{ inscription.equipe ?? '—' }}</td>
              <td class="px-4 py-3 text-center">CHF {{ inscription.tarif }}</td>
              <td class="px-4 py-3">{{ inscription.status_paiement ?? '—' }}</td>
              <td class="px-4 py-3">{{  inscription.dossard?.numero ?? '—' }}</td>
              <td class="px-4 py-3">{{ inscription.participant.nom }} {{ inscription.participant.prenom }}</td>
              <td class="px-4 py-3">
                <button
                        @click="detailInscription(inscription)"
                        class="ml-auto items-center gap-1.5 px-4 my-2 py-1.5 rounded-lg btn-tertiary text-xs font-medium transition-colors"
                      >
                        Détail
                    </button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>
  <PopupInscriptionDetailParticipant v-if="popupDetail" 
    :inscription="inscription.actuel"
    :participants="participants"
    @close="popupDetail = false"
    @ajouter-panier="onChangementConfirme"
  />
  <PopupAvertissementCourse v-if="popupAvertissement"
    :texte="texteInfo"
    @confirmer="afficherPopupChangement"
    @close="popupAvertissement = false"
  />
  <PopupChangementCourseParticipant v-if="popupChangement" 
    :inscription="inscription.actuel"
    :participants="participants" 
    @close="fermerPopupChangement"
  />
</template>

<script>
/**
 * @fileoverview Vue ParticipantInscriptions.
 * @description Historique des inscriptions du participant avec accès au détail et changement de course.
 * @remarks Cette vue alimente les popups de détail/changement et synchronise la liste après action.
 */
import { Icon } from '@iconify/vue';
import Title from '../components/Title.vue'
import inscriptionService from '../services/inscriptionService.js'
import PopupAvertissementCourse from '../components/PopupAvertissementCourse.vue';
import PopupChangementCourseParticipant from '../components/PopupChangementCourseParticipant.vue';
import PopupInscriptionDetailParticipant from '../components/PopupInscriptionDetailParticipant.vue';

export default {
  components: { 
    Title,
    Icon,
    PopupAvertissementCourse,
    PopupChangementCourseParticipant,
    PopupInscriptionDetailParticipant
  },
  emits: ['close'],
  data() {
    return {
      inscriptions: [],
      participants: [],
      chargement: true,
      erreur: '',
      evenementASupprimer: null,
      expandedRows: [],
      popupDetail: false,
      popupAvertissement: false,
      popupChangement: false,
      inscription:{
        actuel: null,
      },
      texteInfo: "En cas de sélection de course où le montant est supérieur à la course actuel, la différence devra être réglée.",
    }
  },
  methods: {
    /**
     * Charge les inscriptions du participant et la liste unique des participants associés.
     * @returns {Promise<void>}
     */
    async chargerInscriptions() {
      this.chargement = true;
      this.erreur = '';
      try {
        const response = await inscriptionService.getMesInscriptions();
        this.inscriptions = response.data;
        // Dédoublonner par id
      const tousParticipants = this.inscriptions.map(i => i.participant);
      this.participants = tousParticipants.filter(
          (p, index, self) => self.findIndex(x => x.id === p.id) === index
      );
        console.log(response?.data);
      } catch (e) {
        console.error(e);
        this.erreur = 'Impossible de charger les inscriptions.'
      } finally {
        this.chargement = false
      }
    },
    /**
     * Ferme la popup de changement puis recharge les inscriptions.
     * @returns {Promise<void>}
     */
    async fermerPopupChangement(){
      this.popupChangement = false;
      await this.chargerInscriptions();
    },

    /**
     * Ouvre/ferme le détail d'une ligne d'inscription.
     * @param {number} id
     * @returns {void}
     */
    toggleExpand(id) {
      const index = this.expandedRows.indexOf(id);
      if (index === -1) {
        this.expandedRows.push(id); // ouvrir
      } else {
        this.expandedRows.splice(index, 1); // fermer
      }
    },
    /**
     * Ouvre la popup de détail pour l'inscription sélectionnée.
     * @param {Object} inscription
     * @returns {void}
     */
    detailInscription(inscription){
      this.inscription.actuel = inscription;
      this.popupDetail = true;
    },
    /**
     * Lance le flux de changement de course via popup d'avertissement.
     * @param {Object} inscription
     * @returns {void}
     */
    changerInscription(inscription) {
      this.inscription.actuel = inscription;
      this.popupAvertissement = true;
    },
    /**
     * Ouvre la popup de changement de course après confirmation de l'avertissement.
     * @returns {void}
     */
    afficherPopupChangement(){
      this.popupAvertissement = false;
      this.popupChangement = true;
    },
    /**
     * Ferme le détail et relaie les données du changement vers le parent.
     * @param {Object} data
     * @returns {void}
     */
    onChangementConfirme(data) {
      this.popupDetail = false;
      this.$emit('ajouter-panier', data);
    }
  },

  async mounted() {
    await this.chargerInscriptions();
  }
}
</script>