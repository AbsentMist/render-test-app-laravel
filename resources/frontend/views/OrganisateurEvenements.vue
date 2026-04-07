<template>
  <Title :texte="`Tableau de bord : évènements`" />
  <div class="p-6">
    <button @click="$router.push('/organisateur/formulaires?onglet=Evènement')" class="btn-tertiary px-4 py-2 rounded-lg inline-block mb-6">
      Nouveau
    </button>

    <p v-if="erreur" class="text-accent text-label mb-4">{{ erreur }}</p>

    <div v-if="chargement" class="text-body text-center py-8">
      Chargement des évènements...
    </div>

    <div v-else class="overflow-x-auto rounded-xl border border-default-medium">
      <table class="w-full text-sm text-left text-body">
        <thead class="bg-neutral-secondary-medium text-heading text-xs uppercase">
          <tr>
            <th class="px-4 py-3">Nom</th>
            <th class="px-4 py-3">Date début</th>
            <th class="px-4 py-3">Date fin</th>
            <th class="px-4 py-3 text-center">Actif</th>
            <th class="px-4 py-3 text-center">Interne</th>
            <th class="px-4 py-3">Actions</th>
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
            class="border-t border-default-medium hover:bg-neutral-secondary-medium transition-colors cursor-pointer"
            @click.stop="$router.push(`/organisateur/evenements/${evenement.id}/courses`)"
          >
            <td class="px-4 py-3 font-medium text-heading">
              {{ evenement.nom }}
            </td>

            <td class="px-4 py-3">
              {{ getDateDebutEvenement(evenement) }}
            </td>

            <td class="px-4 py-3">
              {{ getDateFinEvenement(evenement) }}
            </td>

            <td class="px-4 py-3 text-center">
              <svg v-if="evenement.is_actif" class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <svg v-else class="w-5 h-5 text-accent mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </td>

            <td class="px-4 py-3 text-center">
              <svg v-if="evenement.is_interne" class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <svg v-else class="w-5 h-5 text-accent mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </td>

            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <button
                  @click.stop="modifierEvenement(evenement)"
                  class="p-1.5 rounded-lg text-primary hover:bg-tertiary transition-colors"
                  title="Modifier"
                >
                  <Icon icon="lucide:square-pen" class="w-4 h-4" />
                </button>

                <button
                  @click.stop="confirmerSuppression(evenement)"
                  class="p-1.5 rounded-lg text-accent hover:bg-red-50 transition-colors"
                  title="Supprimer"
                >
                  <Icon icon="lucide:trash-2" class="w-4 h-4" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <PopupConfirmation
      v-if="evenementASupprimer"
      icon="mdi:alert-circle-outline"
      :message="`Voulez-vous vraiment supprimer l'évènement ${evenementASupprimer.nom} ? Cette action est irréversible.`"
      @confirm="supprimerEvenement"
      @cancel="evenementASupprimer = null"
    />

  </div> 
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api.js'
import Title from '../components/Title.vue'
import PopupConfirmation from '../components/PopupConfirmation.vue'
import { Icon } from '@iconify/vue'

const router = useRouter()
const evenements = ref([])
const chargement = ref(true)
const erreur = ref('')
const evenementASupprimer = ref(null)

// ===== GESTION DES DATES (Calcul dynamique) =====
function formaterDate(dateString) {
  if (!dateString) return '—'
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-CH', {
    day: '2-digit', month: '2-digit', year: 'numeric'
  })
}

function getDateDebutEvenement(evenement) {
  if (!evenement.courses || evenement.courses.length === 0) return '—'
  
  const dates = evenement.courses
    .map(c => c.debut_inscription)
    .filter(d => d) 
    .map(d => new Date(d).getTime())
  
  if (dates.length === 0) return '—'
  
  return formaterDate(new Date(Math.min(...dates)))
}

function getDateFinEvenement(evenement) {
  if (!evenement.courses || evenement.courses.length === 0) return '—'
  
  const dates = evenement.courses
    .map(c => c.fin_inscription)
    .filter(d => d)
    .map(d => new Date(d).getTime())
  
  if (dates.length === 0) return '—'
  
  return formaterDate(new Date(Math.max(...dates)))
}

// ===== CHARGER LES ÉVÉNEMENTS =====
async function chargerEvenements() {
  chargement.value = true
  erreur.value = ''
  try {
    const response = await api.get('/organisateur/evenements')
    evenements.value = response.data
  } catch (e) {
    erreur.value = 'Impossible de charger les évènements.'
  } finally {
    chargement.value = false
  }
}

// ===== MODIFIER =====
function modifierEvenement(evenement) {
  router.push(`/organisateur/formulaires?onglet=Evènement&id=${evenement.id}`);
}

// ===== SUPPRIMER =====
function confirmerSuppression(evenement) {
  evenementASupprimer.value = evenement
}

async function supprimerEvenement() {
  try {
    await api.delete(`/organisateur/evenements/${evenementASupprimer.value.id}`)
    evenements.value = evenements.value.filter(e => e.id !== evenementASupprimer.value.id)
    evenementASupprimer.value = null
  } catch (e) {
    erreur.value = 'Impossible de supprimer cet évènement.'
    evenementASupprimer.value = null
  }
}

onMounted(() => chargerEvenements())
</script>