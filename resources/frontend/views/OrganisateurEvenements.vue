<template>
  <div class="p-6">

    <!-- En-tête -->
    <h1 class="text-subtitle mb-6">Tableau de bord : évènements</h1>

    <!-- Bouton Nouveau -->
    <button @click="$router.push('/organisateur/formulaires?onglet=Evènement')" class="btn-tertiary px-4 py-2 rounded-lg inline-block mb-6">
  Nouveau
</button>

    <!-- Message d'erreur -->
    <p v-if="erreur" class="text-accent text-label mb-4">{{ erreur }}</p>

    <!-- Chargement -->
    <div v-if="chargement" class="text-body text-center py-8">
      Chargement des évènements...
    </div>

    <!-- Tableau -->
    <div v-else class="overflow-x-auto rounded-xl border border-default-medium">
      <table class="w-full text-sm text-left text-body">
        <thead class="bg-neutral-secondary-medium text-heading text-xs uppercase">
          <tr>
            <th class="px-4 py-3">Nom</th>
            <th class="px-4 py-3">Date début</th>
            <th class="px-4 py-3">Date fin</th>
            <th class="px-4 py-3 text-center">Actif</th>
            <th class="px-4 py-3 text-center">Interne</th>
            <th class="px-4 py-3">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="evenements.length === 0">
            <td colspan="6" class="text-center px-4 py-6 text-body">
              Aucun évènement trouvé.
            </td>
          </tr>
          <tr
            v-for="evenement in evenements"
            :key="evenement.id"
            class="border-t border-default-medium hover:bg-neutral-secondary-medium transition-colors"
          >
            <!-- Nom -->
            <td class="px-4 py-3 font-medium text-heading">
              {{ evenement.nom }}
            </td>

            <!-- Date début (TODO 9.2) -->
            <td class="px-4 py-3">
              <!-- TODO (9.2) : remplacer par vraie date min des courses liées -->
              <!-- GET /api/organisateur/courses/{id_evenement} -->
              —
            </td>

            <!-- Date fin (TODO 9.2) -->
            <td class="px-4 py-3">
              <!-- TODO (9.2) : remplacer par vraie date max des courses liées -->
              —
            </td>

            <!-- Actif -->
            <td class="px-4 py-3 text-center">
              <svg v-if="evenement.is_actif" class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <svg v-else class="w-5 h-5 text-accent mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </td>

            <!-- Interne -->
            <td class="px-4 py-3 text-center">
              <svg v-if="evenement.is_interne" class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <svg v-else class="w-5 h-5 text-accent mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </td>

            <!-- Actions -->
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <!-- Bouton Modifier -->
                <button
                  @click="modifierEvenement(evenement)"
                  class="p-1.5 rounded-lg text-primary hover:bg-tertiary transition-colors"
                  title="Modifier"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>

                <!-- Bouton Supprimer -->
                <button
                  @click="confirmerSuppression(evenement)"
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
        </tbody>
      </table>
    </div>

    <!-- Popup confirmation suppression -->
    <div v-if="evenementASupprimer" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 shadow-xl max-w-sm w-full mx-4">
        <h2 class="text-heading font-bold text-lg mb-2">Confirmer la suppression</h2>
        <p class="text-body mb-6">
          Voulez-vous vraiment supprimer l'évènement <strong>{{ evenementASupprimer.nom }}</strong> ?
          Cette action est irréversible.
        </p>
        <div class="flex justify-end gap-3">
          <button @click="evenementASupprimer = null" class="btn-accent-300 px-4 py-2 rounded-xl">
            Annuler
          </button>
          <button @click="supprimerEvenement" class="bg-accent text-white px-4 py-2 rounded-xl hover:opacity-90">
            Supprimer
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const evenements = ref([])
const chargement = ref(true)
const erreur = ref('')
const evenementASupprimer = ref(null)

// ===== CHARGER LES ÉVÉNEMENTS =====
async function chargerEvenements() {
  chargement.value = true
  erreur.value = ''
  try {
    const response = await axios.get('/api/organisateur/evenements')
    evenements.value = response.data
  } catch (e) {
    erreur.value = 'Impossible de charger les évènements.'
  } finally {
    chargement.value = false
  }
}

// ===== MODIFIER =====
function modifierEvenement(evenement) {
  // TODO (4.2) : passer les données de l'événement au FormulaireEvenement en mode édition
  router.push('/organisateur/formulaires')
}

// ===== SUPPRIMER =====
function confirmerSuppression(evenement) {
  evenementASupprimer.value = evenement
}

async function supprimerEvenement() {
  try {
    await axios.delete(`/api/organisateur/evenements/${evenementASupprimer.value.id}`)
    evenements.value = evenements.value.filter(e => e.id !== evenementASupprimer.value.id)
    evenementASupprimer.value = null
  } catch (e) {
    erreur.value = 'Impossible de supprimer cet évènement.'
    evenementASupprimer.value = null
  }
}

onMounted(() => chargerEvenements())
</script>