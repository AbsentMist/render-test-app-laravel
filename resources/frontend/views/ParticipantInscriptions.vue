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
            <th class="px-4 py-3 w-8"></th>
            <th class="px-4 py-3">Evènement</th>
            <th class="px-4 py-3">Groupe</th>
            <th class="px-4 py-3">Equipe/club</th>
            <th class="px-4 py-3 text-center">Tarif</th>
            <th class="px-4 py-3">N° Dossard</th>
            <th class="px-4 py-3">Participant</th>
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
            >
              <!-- Bouton + / - -->
              <td class="px-4 py-3">
                <button
                  @click="toggleExpand(inscription.id)"
                  class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-200"
                  :class="expandedRows.includes(inscription.id)
                    ? 'bg-accent-600 hover:bg-accent hover:text-white'
                    : 'hover:bg-accent-600 bg-accent text-white hover:text-black'"
                  :aria-label="expandedRows.includes(inscription.id) ? 'Réduire' : 'Voir plus'"
                >
                  <Icon v-if="expandedRows.includes(inscription.id)" icon="mdi:minus" class="w-4 h-4" />
                  <Icon v-else icon="mdi:plus" class=" w-4 h-4" />
                </button>
              </td>
              <td class="px-4 py-3 font-medium text-heading">
                {{ inscription.course.evenement.nom }} -
                {{ inscription.course.nom }}
              </td>
              <td class="px-4 py-3">{{ inscription.groupe ?? '—' }}</td>
              <td class="px-4 py-3">{{ inscription.equipe ?? '—' }}</td>
              <td class="px-4 py-3 text-center">CHF {{ inscription.tarif }}</td>
              <td class="px-4 py-3">{{ inscription.dossard ?? '—' }}</td>
              <td class="px-4 py-3">{{ inscription.participant.nom }} {{ inscription.participant.prenom }}</td>
            </tr>

            <!-- Ligne expandée avec infos supplémentaires -->
            <tr
              v-if="expandedRows.includes(inscription.id)"
              class="border-t border-default-medium bg-neutral-secondary-medium"
            >
              <td colspan="9" class="px-6 py-4 relative">
                <div class="absolute right-5">
                  <button
                      @click="changerInscription(inscription)"
                      class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg btn-accent-300 text-xs font-medium transition-colors"
                    >
                      Changer de course
                  </button>
                </div>
                <div class="grid grid-cols-2 gap-x-12 gap-y-2 text-sm max-w-2xl">
                  <div class="flex items-center gap-2">
                    <span class="text-body font-medium">Date paiement</span>
                    <span class="text-heading">{{ inscription.date_paiement ?? '—' }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="text-body font-medium">N° inscription</span>
                    <span class="text-heading font-mono text-xs">{{ inscription.numero_inscription ?? '—' }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="text-body font-medium">Ref. Groupage</span>
                    <span class="text-heading">{{ inscription.ref_groupage ?? '—' }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="text-body font-medium">Participe au challenge ?</span>
                    <span class="text-heading">{{ inscription.participe_challenge ? 'Oui' : 'Non' }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="text-body font-medium">Challenge</span>
                    <span class="text-heading">{{ inscription.type_challenge ?? '—' }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="text-body font-medium">Equipe challenge</span>
                    <span class="text-heading">{{ inscription.equipe_challenge ?? '—' }}</span>
                  </div>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import { Icon } from '@iconify/vue';
import Title from '../components/Title.vue'
import inscriptionService from '../services/inscriptionService.js'

export default {
  components: { 
    Title,
    Icon
  },
  data() {
    return {
      inscriptions: [],
      chargement: true,
      erreur: '',
      evenementASupprimer: null,
      expandedRows: [], // IDs des lignes actuellement ouvertes
    }
  },
  methods: {
    async chargerInscriptions() {
      this.chargement = true;
      this.erreur = '';
      try {
        const response = await inscriptionService.getMesInscriptions();
        this.inscriptions = response.data;
        console.log(response?.data);
      } catch (e) {
        console.error(e);
        this.erreur = 'Impossible de charger les inscriptions.'
      } finally {
        this.chargement = false
      }
    },

    toggleExpand(id) {
      const index = this.expandedRows.indexOf(id);
      if (index === -1) {
        this.expandedRows.push(id); // ouvrir
      } else {
        this.expandedRows.splice(index, 1); // fermer
      }
    },

    changerInscription(inscription) {
      // À implémenter selon votre logique métier
      console.log('Changer inscription', inscription.id);
    },
  },

  async mounted() {
    await this.chargerInscriptions();
  }
}
</script>