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
            <th class="px-4 py-3 text-center">ID</th>
            <th class="px-4 py-3 text-center">Participant</th>
            <th class="px-4 py-3 text-center">Course</th>
            <th class="px-4 py-3 text-center">Date inscription</th>
            <th class="px-4 py-3 text-center">Tarif</th>
            <th class="px-4 py-3 text-center">Status</th>
            <th class="px-4 py-3 text-center">Type</th>
            <th class="px-4 py-3 text-center">Actions</th>
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
              <td class="px-4 py-3">{{ inscription.id }}</td>
              <td class="px-4 py-3">{{ inscription.participant.nom }} {{ inscription.participant.prenom }}</td>
              <td class="px-4 py-3 font-medium text-heading">
                {{ inscription.course.evenement.nom }} -
                {{ inscription.course.nom }}
              </td>
              <td class="px-4 py-3">{{ inscription.date_paiement.slice(0,10) ?? '—' }}</td>
              <td class="px-4 py-3 text-center">CHF {{ inscription.tarif }}</td>
              <td class="px-4 py-3">{{ inscription.status_paiement ?? '—' }}</td>
              <td class="px-4 py-3">{{ inscription.course.type ?? '—' }}</td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                    <button
                    @click="afficherDetail(inscription)"
                    class="p-1.5 rounded-lg text-primary hover:bg-tertiary transition-colors"
                    title="Modifier"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>

                    <button
                    @click="suppression(inscription)"
                    class="p-1.5 rounded-lg text-accent hover:bg-red-50 transition-colors"
                    title="Supprimer"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>
  <PopupAvertissementCourse v-if="popupAvertissement"
    :texte="texteInfo"
    @confirmer="afficherPopupChangement"
    @close="popupAvertissement = false"
  />
  <PopupChangementCourseOrganisateur v-if="popupChangement" 
    :inscription="inscription.actuel"
    :participants="participants" 
    @close="fermerPopupChangement"
  />
  <PopupInscriptionDetail
    v-if="popupDetail"
    :inscription="inscription.actuel"
    @modifier-inscription="onModifierInscription"
    @close="popupDetail = false"
  />
</template>

<script>
import { Icon } from '@iconify/vue';
import Title from '../components/Title.vue'
import inscriptionService from '../services/inscriptionService.js'
import PopupAvertissementCourse from '../components/PopupAvertissementCourse.vue';
import PopupChangementCourseOrganisateur from '../components/PopupChangementCourseOrganisateur.vue';
import PopupInscriptionDetail from '../components/PopupInscriptionDetail.vue';

export default {
  components: { 
    Title,
    Icon,
    PopupAvertissementCourse,
    PopupChangementCourseOrganisateur,
    PopupInscriptionDetail
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
      popupAvertissement: false,
      popupChangement: false,
      popupDetail: false,
      inscription:{
        actuel: null,
      },
      texteInfo: "En cas de sélection de course où le montant est supérieur à la course actuel, la différence devra être réglée.",
    }
  },
  methods: {
    async chargerInscriptions() {
      this.chargement = true;
      this.erreur = '';
      try {
        const response = await inscriptionService.getAllInscriptionsAdmin();
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
    async fermerPopupChangement(){
      this.popupChangement = false;
      await this.chargerInscriptions();
    },
    afficherDetail(inscription){
      this.inscription.actuel = inscription;
      this.popupDetail = true;
    },
    onModifierInscription(inscriptionMaj) {
      const idx = this.inscriptions.findIndex((i) => i.id === inscriptionMaj.id);

      if (idx > -1) {
        const rowActuelle = this.inscriptions[idx];
        this.inscriptions.splice(idx, 1, {
          ...rowActuelle,
          ...inscriptionMaj,
        });

        this.inscription.actuel = this.inscriptions[idx];
      } else {
        this.inscription.actuel = inscriptionMaj;
      }
    },
    async suppression(inscription){
      if(confirm(`Annuler l'inscription ${inscription.id} ?`)){
        try {
            await inscriptionService.updateInscriptionAdmin(inscription.id, { status_paiement: 'Annulé' });
            await this.chargerInscriptions();
        } catch (error) {
            console.error("Erreur annulation :", error);
        }
      }
    },
    changerInscription(inscription) {
      // À implémenter selon votre logique métier
      console.log('Changer inscription', inscription.id);
      this.inscription.actuel = inscription;
      this.popupAvertissement = true;
    },
    afficherPopupChangement(){
      this.popupAvertissement = false;
      this.popupChangement = true;
    }
  },

  async mounted() {
    await this.chargerInscriptions();
  }
}
</script>