<template>
  <Title :texte="`Inscriptions`" />
  <div class="p-6">
    <p v-if="erreur" class="text-accent text-label mb-4">{{ erreur }}</p>

    <!-- BARRE DE FILTRAGE -->
    <div class="flex flex-wrap gap-3 mb-4">
      <input
        v-model="filtres.recherche"
        type="text"
        placeholder="Rechercher par nom, prénom, entreprise..."
        class="flex-1 min-w-[200px] bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base px-3 py-2 focus:ring-brand focus:border-brand shadow-xs"
      />
      <input
        v-model="filtres.dossard"
        type="number"
        placeholder="N° dossard"
        class="w-36 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base px-3 py-2 focus:ring-brand focus:border-brand shadow-xs"
      />
      <select
        v-model="filtres.status"
        class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base px-3 py-2 focus:ring-brand focus:border-brand shadow-xs"
      >
        <option value="">Tous les statuts</option>
        <option value="Validé">Validé</option>
        <option value="En attente">En attente</option>
        <option value="Annulé">Annulé</option>
      </select>
      <select
      v-model="filtres.type"
      class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base px-3 py-2 focus:ring-brand focus:border-brand shadow-xs"
      >
        <option value="">Tous les types</option>
        <option value="Individuel">Individuel</option>
        <option value="Relais">Relais</option>
        <option value="Groupe">Groupe</option>
      </select>
      <button
        v-if="filtresActifs"
        @click="reinitialiserFiltres"
        class="px-3 py-2 text-sm text-accent hover:text-red-700 border border-accent rounded-base transition-colors"
      >
        Réinitialiser
      </button>
      <span class="flex items-center text-xs text-body px-2">
        {{ inscriptionsFiltrees.length }} résultat(s)
      </span>
    </div>

    <div v-if="chargement" class="text-body text-center py-8">
      Chargement des inscriptions...
    </div>
    <div v-else class="overflow-x-auto rounded-xl border border-default-medium">
      <table class="w-full text-sm text-left text-body">
        <thead class="bg-neutral-secondary-medium text-heading text-xs uppercase">
          <tr>
            <th class="px-4 py-3 text-center">Dossard</th>
            <th class="px-4 py-3 text-center">Nom</th>
            <th class="px-4 py-3 text-center">Prénom</th>
            <th class="px-4 py-3 text-center">Course</th>
            <th class="px-4 py-3 text-center">Date inscription</th>
            <th class="px-4 py-3 text-center">Tarif</th>
            <th class="px-4 py-3 text-center">Status</th>
            <th class="px-4 py-3 text-center">Type</th>
            <th class="px-4 py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="inscriptionsFiltrees.length === 0">
            <td colspan="9" class="text-center px-4 py-6 text-body">
              Aucune inscription trouvée.
            </td>
          </tr>

          <template v-for="inscription in inscriptionsFiltrees" :key="inscription.id">
            <tr
              class="border-t border-default-medium hover:bg-neutral-secondary-medium transition-colors"
              :class="inscription.status_paiement=='Annulé' ? 'bg-accent-600' : ''"
            >
              <td class="px-4 py-3">{{ inscription.dossard?.numero ?? '—' }}</td>
              <td class="px-4 py-3">{{ inscription.participant.nom }}</td>
              <td class="px-4 py-3">{{ inscription.participant.prenom }}</td>
              <td class="px-4 py-3 font-medium text-heading">
                {{ inscription.course.evenement.nom }} -
                {{ inscription.course.nom }}
              </td>
              <td class="px-4 py-3">{{ inscription.date_paiement?.slice(0, 10) || '—' }}</td>
              <td class="px-4 py-3 text-center">CHF {{ inscription.tarif }}</td>
              <td class="px-4 py-3">{{ inscription.status_paiement ?? '—' }}</td>
              <td class="px-4 py-3">{{ inscription.course.type ?? '—' }}</td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <button
                    @click="afficherDetail(inscription)"
                    class="p-1.5 rounded-lg text-primary hover:bg-tertiary transition-colors"
                    title="Voir détail"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    @click="suppression(inscription)"
                    class="p-1.5 rounded-lg text-accent hover:bg-red-50 transition-colors"
                    title="Annuler"
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
  <PopupInscriptionDetailOrganisateur
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
import PopupInscriptionDetailOrganisateur from '../components/PopupInscriptionDetailOrganisateur.vue';

export default {
  components: {
    Title,
    Icon,
    PopupAvertissementCourse,
    PopupChangementCourseOrganisateur,
    PopupInscriptionDetailOrganisateur
  },
  emits: ['close'],
  data() {
    return {
      inscriptions: [],
      participants: [],
      chargement: true,
      erreur: '',
      expandedRows: [],
      popupAvertissement: false,
      popupChangement: false,
      popupDetail: false,
      inscription: {
        actuel: null,
      },
      texteInfo: "En cas de sélection de course où le montant est supérieur à la course actuel, la différence devra être réglée.",
      // Filtres
      filtres: {
        recherche: '',
        dossard: '',
        status: '',
        type: '',
      },
    }
  },
  computed: {
    filtresActifs() {
      return this.filtres.recherche || this.filtres.dossard || this.filtres.status;
    },
    inscriptionsFiltrees() {
      return this.inscriptions.filter(inscription => {

        // Filtre par N° dossard
        if (this.filtres.dossard) {
          const numeroDossard = inscription.dossard?.numero?.toString() ?? '';
          if (!numeroDossard.includes(this.filtres.dossard.toString())) return false;
        }

        // Filtre par statut
        if (this.filtres.status && inscription.status_paiement !== this.filtres.status) {
          return false;
        }

        // Filtre par type de course
        if (this.filtres.type && inscription.course?.type !== this.filtres.type) {
          return false;
        }

        // Filtre texte : nom, prénom, groupe/entreprise, cours
        if (this.filtres.recherche) {
          const r = this.filtres.recherche.toLowerCase();
          const nom       = inscription.participant?.nom?.toLowerCase() ?? '';
          const prenom    = inscription.participant?.prenom?.toLowerCase() ?? '';
          const groupe    = inscription.groupe?.nom?.toLowerCase() ?? '';
          const equipe    = inscription.equipe_challenge?.toLowerCase() ?? '';
          const course    = inscription.course?.nom?.toLowerCase() ?? '';
          const evenement = inscription.course?.evenement?.nom?.toLowerCase() ?? '';

          if (!nom.includes(r) && !prenom.includes(r) &&
              !groupe.includes(r) && !equipe.includes(r) &&
              !course.includes(r) && !evenement.includes(r)) {
            return false;
          }
        }

        return true;
      });
    },
  },
  methods: {
    reinitialiserFiltres() {
      this.filtres = { recherche: '', dossard: '', status: '', type:'' };
    },
    async chargerInscriptions() {
      this.chargement = true;
      this.erreur = '';
      try {
        const response = await inscriptionService.getAllInscriptionsAdmin();
        this.inscriptions = response.data;
        const tousParticipants = this.inscriptions.map(i => i.participant);
        this.participants = tousParticipants.filter(
          (p, index, self) => self.findIndex(x => x.id === p.id) === index
        );
      } catch (e) {
        console.error(e);
        this.erreur = 'Impossible de charger les inscriptions.'
      } finally {
        this.chargement = false
      }
    },
    async fermerPopupChangement() {
      this.popupChangement = false;
      await this.chargerInscriptions();
    },
    afficherDetail(inscription) {
      this.inscription.actuel = inscription;
      this.popupDetail = true;
    },
    onModifierInscription(inscriptionMaj) {
      const idx = this.inscriptions.findIndex((i) => i.id === inscriptionMaj.id);
      if (idx > -1) {
        const rowActuelle = this.inscriptions[idx];
        this.inscriptions.splice(idx, 1, { ...rowActuelle, ...inscriptionMaj });
        this.inscription.actuel = this.inscriptions[idx];
      } else {
        this.inscription.actuel = inscriptionMaj;
      }
    },
    async suppression(inscription) {
      if (confirm(`Annuler l'inscription ${inscription.id} ?`)) {
        try {
          await inscriptionService.updateInscriptionAdmin(inscription.id, { status_paiement: 'Annulé' });
          await this.chargerInscriptions();
        } catch (error) {
          console.error("Erreur annulation :", error);
        }
      }
    },
    afficherPopupChangement() {
      this.popupAvertissement = false;
      this.popupChangement = true;
    }
  },
  async mounted() {
    await this.chargerInscriptions();
  }
}
</script>