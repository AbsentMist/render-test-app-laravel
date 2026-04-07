<template>
  <Title :texte="`Inscriptions`" />
  <div class="p-6">
    <p v-if="erreur" class="text-accent text-label mb-4">{{ erreur }}</p>

    <!-- Composant de filtrage + export -->
    <FiltreInscriptions
      :nb-resultats="inscriptionsFiltrees.length"
      @update:filtres="onFiltresChange"
      @exporter="exporter"
    />

    <div v-if="chargement" class="text-body text-center py-8">
      Chargement des inscriptions...
    </div>
    <div v-else class="overflow-x-auto rounded-xl border border-default-medium">
      <table class="w-full text-sm text-left text-body">
        <thead class="bg-neutral-secondary-medium text-heading text-xs uppercase">
          <tr>
            <th class="px-4 py-3 text-center cursor-pointer hover:bg-neutral-secondary-dark transition-colors" @click="changerTri('dossard')">
              Dossard
              <span v-if="tri.colonne === 'dossard'" class="ml-1">{{ tri.direction === 'asc' ? '▲' : '▼' }}</span>
            </th>
            <th class="px-4 py-3 text-center cursor-pointer hover:bg-neutral-secondary-dark transition-colors" @click="changerTri('nom')">
              Nom
              <span v-if="tri.colonne === 'nom'" class="ml-1">{{ tri.direction === 'asc' ? '▲' : '▼' }}</span>
            </th>
            <th class="px-4 py-3 text-center cursor-pointer hover:bg-neutral-secondary-dark transition-colors" @click="changerTri('prenom')">
              Prénom
              <span v-if="tri.colonne === 'prenom'" class="ml-1">{{ tri.direction === 'asc' ? '▲' : '▼' }}</span>
            </th>
            <th class="px-4 py-3 text-center cursor-pointer hover:bg-neutral-secondary-dark transition-colors" @click="changerTri('course')">
              Course
              <span v-if="tri.colonne === 'course'" class="ml-1">{{ tri.direction === 'asc' ? '▲' : '▼' }}</span>
            </th>
            <th class="px-4 py-3 text-center cursor-pointer hover:bg-neutral-secondary-dark transition-colors" @click="changerTri('date')">
              Date inscription
              <span v-if="tri.colonne === 'date'" class="ml-1">{{ tri.direction === 'asc' ? '▲' : '▼' }}</span>
            </th>
            <th class="px-4 py-3 text-center cursor-pointer hover:bg-neutral-secondary-dark transition-colors" @click="changerTri('tarif')">
              Tarif
              <span v-if="tri.colonne === 'tarif'" class="ml-1">{{ tri.direction === 'asc' ? '▲' : '▼' }}</span>
            </th>
            <th class="px-4 py-3 text-center cursor-pointer hover:bg-neutral-secondary-dark transition-colors" @click="changerTri('status')">
              Status
              <span v-if="tri.colonne === 'status'" class="ml-1">{{ tri.direction === 'asc' ? '▲' : '▼' }}</span>
            </th>
            <th class="px-4 py-3 text-center cursor-pointer hover:bg-neutral-secondary-dark transition-colors" @click="changerTri('type')">
              Type
              <span v-if="tri.colonne === 'type'" class="ml-1">{{ tri.direction === 'asc' ? '▲' : '▼' }}</span>
            </th>
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
  <PopupConfirmation
    v-if="inscriptionASupprimer"
    :message="`Annuler l'inscription ${inscriptionASupprimer.id} ?`"
    icon="mdi:alert-circle-outline"
    @confirm="confirmerSuppression"
    @cancel="inscriptionASupprimer = null"
  />
</template>

<script>
import { Icon } from '@iconify/vue';
import Title from '../components/Title.vue';
import FiltreInscriptions from '../components/FiltreInscriptions.vue';
import inscriptionService from '../services/inscriptionService.js';
import PopupAvertissementCourse from '../components/PopupAvertissementCourse.vue';
import PopupChangementCourseOrganisateur from '../components/PopupChangementCourseOrganisateur.vue';
import PopupInscriptionDetailOrganisateur from '../components/PopupInscriptionDetailOrganisateur.vue';
import PopupConfirmation from '../components/PopupConfirmation.vue';

export default {
  components: {
    Title,
    Icon,
    FiltreInscriptions,
    PopupAvertissementCourse,
    PopupChangementCourseOrganisateur,
    PopupInscriptionDetailOrganisateur,
    PopupConfirmation,
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
      inscriptionASupprimer: null,
      inscription: { actuel: null },
      texteInfo: "En cas de sélection de course où le montant est supérieur à la course actuel, la différence devra être réglée.",
      // Filtres reçus du composant FiltreInscriptions
      filtres: { recherche: '', status: '', type: '' },
      // Tri
      tri: { colonne: 'date', direction: 'desc' },
    };
  },
  computed: {
    inscriptionsFiltrees() {
      let resultats = this.inscriptions.filter(inscription => {

        // Filtre texte unifié : nom, prénom, dossard, groupe, entreprise, course
        if (this.filtres.recherche) {
          const r = this.filtres.recherche.toLowerCase();
          const nom       = inscription.participant?.nom?.toLowerCase() ?? '';
          const prenom    = inscription.participant?.prenom?.toLowerCase() ?? '';
          const dossard   = inscription.dossard?.numero?.toString() ?? '';
          const groupe    = inscription.groupe?.nom?.toLowerCase() ?? '';
          const equipe    = inscription.equipe_challenge?.toLowerCase() ?? '';
          const course    = inscription.course?.nom?.toLowerCase() ?? '';
          const evenement = inscription.course?.evenement?.nom?.toLowerCase() ?? '';

          if (!nom.includes(r) && !prenom.includes(r) &&
              !dossard.includes(r) && !groupe.includes(r) &&
              !equipe.includes(r) && !course.includes(r) &&
              !evenement.includes(r)) {
            return false;
          }
        }

        // Filtre statut
        if (this.filtres.status && inscription.status_paiement !== this.filtres.status) {
          return false;
        }

        // Filtre type de course
        if (this.filtres.type && inscription.course?.type !== this.filtres.type) {
          return false;
        }

        return true;
      });

      // Tri
      resultats = [...resultats].sort((a, b) => {
        let valeurA, valeurB;
        switch (this.tri.colonne) {
          case 'dossard':
            valeurA = a.dossard?.numero ?? 0;
            valeurB = b.dossard?.numero ?? 0;
            break;
          case 'nom':
            valeurA = a.participant?.nom ?? '';
            valeurB = b.participant?.nom ?? '';
            break;
          case 'prenom':
            valeurA = a.participant?.prenom ?? '';
            valeurB = b.participant?.prenom ?? '';
            break;
          case 'course':
            valeurA = `${a.course?.evenement?.nom ?? ''} ${a.course?.nom ?? ''}`.toLowerCase();
            valeurB = `${b.course?.evenement?.nom ?? ''} ${b.course?.nom ?? ''}`.toLowerCase();
            break;
          case 'tarif':
            valeurA = parseFloat(a.tarif ?? 0);
            valeurB = parseFloat(b.tarif ?? 0);
            break;
          case 'status':
            valeurA = a.status_paiement ?? '';
            valeurB = b.status_paiement ?? '';
            break;
          case 'type':
            valeurA = a.course?.type ?? '';
            valeurB = b.course?.type ?? '';
            break;
          case 'date':
          default:
            valeurA = a.date_paiement ?? '';
            valeurB = b.date_paiement ?? '';
        }
        if (typeof valeurA === 'string') {
          valeurA = valeurA.toLowerCase();
          valeurB = valeurB.toLowerCase();
        }
        if (valeurA < valeurB) return this.tri.direction === 'asc' ? -1 : 1;
        if (valeurA > valeurB) return this.tri.direction === 'asc' ? 1 : -1;
        return 0;
      });

      return resultats;
    },
  },
  methods: {
    onFiltresChange(nouveauxFiltres) {
      this.filtres = nouveauxFiltres;
    },
    changerTri(colonne) {
      if (this.tri.colonne === colonne) {
        this.tri.direction = this.tri.direction === 'asc' ? 'desc' : 'asc';
      } else {
        this.tri.colonne    = colonne;
        this.tri.direction  = 'asc';
      }
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
        this.erreur = 'Impossible de charger les inscriptions.';
      } finally {
        this.chargement = false;
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
      const idx = this.inscriptions.findIndex(i => i.id === inscriptionMaj.id);
      if (idx > -1) {
        this.inscriptions.splice(idx, 1, { ...this.inscriptions[idx], ...inscriptionMaj });
        this.inscription.actuel = this.inscriptions[idx];
      } else {
        this.inscription.actuel = inscriptionMaj;
      }
    },
    async suppression(inscription) {
      this.inscriptionASupprimer = inscription;
    },
    async confirmerSuppression() {
      if (!this.inscriptionASupprimer) return;
      try {
        await inscriptionService.updateInscriptionAdmin(this.inscriptionASupprimer.id, { status_paiement: 'Annulé' });
        this.inscriptionASupprimer = null;
        await this.chargerInscriptions();
      } catch (error) {
        console.error('Erreur annulation :', error);
      }
    },
    afficherPopupChangement() {
      this.popupAvertissement = false;
      this.popupChangement = true;
    },
    async exporter(format) {
      try {
        const response = await inscriptionService.exportInscriptionsAdmin(format);
        const url  = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href  = url;
        const extension = format === 'csv' ? 'csv' : 'xlsx';
        link.setAttribute('download', `inscriptions_${new Date().toISOString().slice(0, 10)}.${extension}`);
        document.body.appendChild(link);
        link.click();
        link.parentNode.removeChild(link);
        window.URL.revokeObjectURL(url);
      } catch (error) {
        console.error("Erreur lors de l'export :", error);
        this.erreur = "Impossible d'exporter les inscriptions.";
      }
    },
  },
  async mounted() {
    await this.chargerInscriptions();
  },
};
</script>