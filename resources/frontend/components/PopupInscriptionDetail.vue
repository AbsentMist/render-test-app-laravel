<template>
  <div :class="inline ? 'flex flex-col h-full' : 'fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm'">
    <div
      :class="inline ? 'flex flex-col h-full w-full' : 'relative bg-white rounded-2xl shadow-2xl w-full max-w-8xl mx-4 flex flex-col overflow-hidden'"
      :style="inline ? '' : 'height: 80vh'"
    >
      <!-- Header -->
      <div v-if="!inline" class="flex items-center justify-between px-6 pt-5 pb-0 border-b border-gray-100 bg-tertiary-900">
        <div class="flex flex-col w-full">
          <div class="flex items-center justify-between">
            <div>
              <span class="px-6 text-subtitle font-medium text-secondary">Detail inscription</span>
              <div class="h-1 w-24 ml-6 rounded-r-full mb-2" :style="{ backgroundColor: inscription.course.evenement?.couleur_secondaire }"></div>
            </div>
            <button @click="$emit('close')" class="text-secondary hover:text-gray-600 transition-colors mr-1 self-start mt-1">
              <Icon icon="mdi:close" class="w-5 h-5" />
            </button>
          </div>

          <!-- Tabs -->
          <div class="flex gap-1 mt-3 px-6">
            <button
              v-for="tab in tabs"
              :key="tab.key"
              @click="activeTab = tab.key"
              class="px-4 py-2 text-sm font-medium rounded-t-lg transition-all duration-200 flex items-center gap-2"
              :class="activeTab === tab.key
                ? 'bg-white text-gray-800 shadow-sm'
                : 'text-secondary hover:text-secondary hover:bg-white/30'"
            >
              <Icon :icon="tab.icon" class="w-4 h-4" />
              {{ tab.label }}
            </button>
          </div>
        </div>
      </div>

      <!-- Tab Content -->
      <div class="flex-1 overflow-y-auto">

        <!-- Onglet Général -->
        <div v-if="activeTab === 'general'" class="p-6 space-y-6">

          <!-- Participant -->
          <section>
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
              <Icon icon="mdi:account" class="w-4 h-4" /> Participant
            </h3>
            <div class="bg-gray-50 rounded-xl p-4 grid grid-cols-2 md:grid-cols-4 gap-4">
              <div>
                <p class="text-xs text-gray-400">Nom complet</p>
                <p class="font-medium text-gray-800">{{ inscription.participant.prenom }} {{ inscription.participant.nom }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Date de naissance</p>
                <p class="font-medium text-gray-800">{{ formatDate(inscription.participant.date_naissance) }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Sexe</p>
                <p class="font-medium text-gray-800">{{ inscription.participant.sexe === 'M' ? 'Homme' : 'Femme' }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Nationalité</p>
                <p class="font-medium text-gray-800">{{ inscription.participant.nationalite }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Téléphone</p>
                <p class="font-medium text-gray-800">{{ inscription.participant.telephone }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Taille T-shirt</p>
                <p class="font-medium text-gray-800">{{ inscription.participant.taille_tshirt }}</p>
              </div>
              <div class="col-span-2 md:col-span-3">
                <p class="text-xs text-gray-400">Adresse</p>
                <p class="font-medium text-gray-800">
                  {{ inscription.participant.adresse }}, {{ inscription.participant.code_postal }} {{ inscription.participant.ville }}, {{ inscription.participant.pays }}
                </p>
              </div>
            </div>
          </section>

          <!-- Course -->
          <section>
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
              <Icon icon="mdi:run-fast" class="w-4 h-4" /> Course
            </h3>
            <div class="bg-gray-50 rounded-xl p-4 grid grid-cols-2 md:grid-cols-6 gap-4">
              <div>
                <p class="text-xs text-gray-400">Nom</p>
                <p class="font-medium text-gray-800">{{ inscription.course.nom }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Distance</p>
                <p class="font-medium text-gray-800">{{ inscription.course.distance }} km</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Type</p>
                <p class="font-medium text-gray-800">{{ inscription.course.type }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Date</p>
                <p class="font-medium text-gray-800">{{ formatDate(inscription.course.date_debut) }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Statut</p>
                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold"
                  :class="inscription.course.status === 'Ouvert' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                  {{ inscription.course.status }}
                </span>
              </div>
              <div>
                <p class="text-xs text-gray-400">Tarif</p>
                <p class="font-medium text-gray-800">{{ inscription.course.tarif }} CHF</p>
              </div>
            </div>
          </section>


          <!-- Paiement & Dossard -->
          <section>
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
              <Icon icon="mdi:receipt-text" class="w-4 h-4" /> Inscription
            </h3>
            <div class="bg-gray-50 rounded-xl p-4 grid grid-cols-2 md:grid-cols-5 gap-4">
              <div>
                <p class="text-xs text-gray-400">Statut</p>
                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold mt-1"
                  :class="inscription.status_paiement === 'Validé' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'">
                  {{ inscription.status_paiement }}
                </span>
              </div>
              <div>
                <p class="text-xs text-gray-400">Tarif payé</p>
                <p class="font-medium text-gray-800">{{ inscription.tarif }} CHF</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Date de paiement</p>
                <p class="font-medium text-gray-800">{{ inscription.date_paiement ?? '—' }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Rabais</p>
                <p class="font-medium text-gray-800">{{ inscription.montant_rabais }} CHF</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">N° inscription</p>
                <p class="font-medium text-gray-800 font-mono text-xs">{{ inscription.numero_inscription ?? '—' }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Ref. Groupage</p>
                <p class="font-medium text-gray-800">{{ inscription.ref_groupage ?? '—' }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Participe au challenge ?</p>
                <p class="font-medium text-gray-800">{{ inscription.participe_challenge ? 'Oui' : 'Non' }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Challenge</p>
                <p class="font-medium text-gray-800">{{ inscription.type_challenge ?? '—' }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Équipe challenge</p>
                <p class="font-medium text-gray-800">{{ inscription.equipe_challenge ?? '—' }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Dossard</p>
                <p class="font-medium text-gray-800">{{ inscription.dossard ?? '—' }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Code de participation</p>
                <p class="font-medium text-gray-800">{{ inscription.code_participant ?? '—' }}</p>
              </div>
            </div>
          </section>

          <!-- Document -->
          <section>
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
              <Icon icon="mdi:file-document-outline" class="w-4 h-4" /> Document
            </h3>
            <div class="bg-gray-50 rounded-xl p-4">
              <div v-if="inscription.id_document" class="flex items-center gap-3">
                <Icon icon="mdi:file-check" class="w-8 h-8 text-green-500" />
                <div>
                  <p class="font-medium text-gray-800">Document fourni</p>
                  <p class="text-xs text-gray-400">ID : {{ inscription.id_document }}</p>
                </div>
              </div>
              <div v-else class="flex items-center gap-3 text-gray-400">
                <Icon icon="mdi:file-remove-outline" class="w-8 h-8" />
                <p class="text-sm">Aucun document fourni</p>
              </div>
            </div>
          </section>

          <!-- Actions -->
          <section class="flex flex-wrap gap-3 pt-2">
            <button
              class="flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors"
              @click="ouvrirChangementCourse"
            >
              <Icon icon="mdi:swap-horizontal" class="w-4 h-4" />
              Changer de course
            </button>
            <button
              class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors"
              :style="{ backgroundColor: inscription.course.evenement.couleur_primaire, color: inscription.course.evenement.couleur_secondaire }"
              @click="$emit('modifier-inscription', inscription)"
            >
              <Icon icon="mdi:pencil" class="w-4 h-4" />
              Modifier l'inscription
            </button>
          </section>
        </div>

        <!-- Onglet Options -->
        <div v-if="activeTab === 'options'" class="p-6">
          <div v-if="inscription.groupe || inscription.participant.equipe_nom" class="space-y-4">
            <div v-if="inscription.groupe" class="bg-gray-50 rounded-xl p-4">
              <p class="text-xs text-gray-400 mb-1">Groupe</p>
              <p class="font-medium text-gray-800">{{ inscription.groupe }}</p>
            </div>
            <div v-if="inscription.participant.equipe_nom" class="bg-gray-50 rounded-xl p-4">
              <p class="text-xs text-gray-400 mb-1">Équipe</p>
              <p class="font-medium text-gray-800">{{ inscription.participant.equipe_nom }}</p>
            </div>
          </div>
          <div v-else class="flex flex-col items-center justify-center py-16 text-gray-400">
            <Icon icon="mdi:cog-outline" class="w-12 h-12 mb-3 opacity-40" />
            <p class="text-sm">Aucune option disponible pour cette inscription.</p>
          </div>
        </div>

        <!-- Onglet Questions -->
        <div v-if="activeTab === 'questions'" class="p-6">
          <div v-if="inscription.course.is_questionnaire" class="space-y-4">
            <p class="text-sm text-gray-500">Les réponses au questionnaire s'afficheront ici.</p>
          </div>
          <div v-else class="flex flex-col items-center justify-center py-16 text-gray-400">
            <Icon icon="mdi:comment-question-outline" class="w-12 h-12 mb-3 opacity-40" />
            <p class="text-sm">Aucune question associée à cette course.</p>
          </div>
        </div>

      </div>
    </div>

    <!-- Transition vers PopupChangementCourse -->
    <Transition name="slide-over">
      <PopupChangementCourseOrganisateur
        v-if="showChangementCourse"
        :inscription="inscription"
        :participants="participants"
        @close="showChangementCourse = false"
        @confirmer="onChangementConfirme"
      />
    </Transition>
  </div>
</template>

<script>
import { Icon } from '@iconify/vue';
import PopupChangementCourseOrganisateur from './PopupChangementCourseOrganisateur.vue';

export default {
  name: 'PopupInscriptionDetail',
  components: { Icon, PopupChangementCourseOrganisateur },
  emits: ['close', 'ajouter-panier', 'modifier-inscription'],
  props: {
    inscription: { type: Object, required: true },
    participants: { type: Array, default: () => [] },
    inline: { type: Boolean, default: false },
  },
  data() {
    return {
      activeTab: 'general',
      showChangementCourse: false,
      tabs: [
        { key: 'general',   label: 'Général',   icon: 'mdi:information-outline' },
        { key: 'options',   label: 'Options',   icon: 'mdi:cog-outline' },
        { key: 'questions', label: 'Questions', icon: 'mdi:comment-question-outline' },
      ],
    };
  },
  methods: {
    formatDate(dateStr) {
      if (!dateStr) return '—';
      const [y, m, d] = dateStr.split('-');
      return `${d}.${m}.${y}`;
    },
    ouvrirChangementCourse() {
      this.showChangementCourse = true;
    },
    onChangementConfirme(data) {
      this.showChangementCourse = false;
      this.$emit('ajouter-panier', data);
    },
  },
};
</script>