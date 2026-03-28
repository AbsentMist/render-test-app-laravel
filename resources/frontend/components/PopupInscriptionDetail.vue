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
                  :class="inscription.course.status === 'actif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
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
              <!-- Groupe/Équipe -->
            <div v-if="inscription.groupe || inscription.participant.equipe_nom" class="space-y-4">
              <div v-if="inscription.groupe" class="bg-gray-50 rounded-full py-0.5 px-2 inline-block ">
                <p class="text-xs text-gray-400 mb-1">Groupe</p>
                <p class="font-medium text-gray-800">{{ inscription.groupe.nom }}</p>
              </div>
              <div v-if="inscription.participant.equipe_nom" class="bg-gray-50 rounded-xl p-4">
                <p class="text-xs text-gray-400 mb-1">Équipe</p>
                <p class="font-medium text-gray-800">{{ inscription.participant.equipe_nom }}</p>
              </div>
            </div>
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
                <p class="font-medium text-gray-800">{{ inscription.numero_inscription ?? '—' }}</p>
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
              <Icon icon="mdi:file-document-outline" class="w-4 h-4" /> Documents
            </h3>
            <div class="bg-gray-50 rounded-xl p-4">
              <div v-if="inscription.documentsFournis && inscription.documentsFournis.length > 0" class="space-y-3">
                <div v-for="doc in inscription.documentsFournis" :key="doc.id" class="flex items-center justify-between bg-white rounded-lg p-3 border border-gray-200">
                  <div class="flex items-center gap-3">
                    <Icon icon="mdi:file-document-outline" class="w-6 h-6 text-secondary" />
                    <div>
                      <p class="font-medium text-gray-800">{{ doc.url.split('/').pop() }}</p>
                      <p class="text-xs text-gray-400">
                        <template v-if="doc.date_debut">Valable du {{ formatDate(doc.date_debut) }}
                          <template v-if="doc.date_fin">au {{ formatDate(doc.date_fin) }}</template>
                        </template>
                        <template v-if="doc.valable" class="text-green-600"> • Valide</template>
                      </p>
                    </div>
                  </div>
                  <div class="flex items-center gap-2">
                    <button
                      @click="telechargerDocument(doc)"
                      class="p-2 text-secondary hover:bg-secondary/10 rounded transition-colors"
                      title="Télécharger"
                    >
                      <Icon icon="mdi:download" class="w-4 h-4" />
                    </button>
                    <button
                      v-if="!etradeAdmin"
                      @click="supprimerDocument(doc.id)"
                      class="p-2 text-accent hover:bg-red-50 rounded transition-colors"
                      title="Supprimer"
                    >
                      <Icon icon="mdi:delete" class="w-4 h-4" />
                    </button>
                  </div>
                </div>
              </div>
              <div v-else class="flex items-center gap-3 text-gray-400">
                <Icon icon="mdi:file-remove-outline" class="w-8 h-8" />
                <p class="text-sm">Aucun document fourni</p>
              </div>
            </div>
          </section>

          <!-- Upload de documents -->
          <section v-if="!inline">
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
              <Icon icon="mdi:cloud-upload-outline" class="w-4 h-4" /> Ajouter un document
            </h3>
            <div class="bg-gray-50 rounded-xl p-4">
              <div
                class="border-2 border-dashed rounded-lg flex flex-col items-center justify-center py-6 gap-3 cursor-pointer transition-all"
                :class="glisserDocument ? 'border-secondary bg-secondary/5' : 'border-gray-300 hover:border-gray-400'"
                @dragover.prevent="glisserDocument = true"
                @dragleave="glisserDocument = false"
                @drop.prevent="deposerDocument"
                @click="$refs.inputDoc.click()"
              >
                <Icon icon="mdi:upload-outline" class="w-8 h-8 text-gray-400" />
                <p class="text-sm text-gray-600 text-center">
                  Glissez-déposez un fichier ou<br>
                  <span class="text-secondary font-medium">cliquez pour parcourir</span>
                </p>
                <p class="text-xs text-gray-400">PDF, JPG, PNG — max 10 Mo</p>
                <input
                  ref="inputDoc"
                  type="file"
                  class="hidden"
                  accept=".pdf,.jpg,.jpeg,.png"
                  @change="selectionnerDocument"
                />
              </div>
              <div v-if="chargementDocument" class="mt-3 text-center">
                <p class="text-sm text-gray-600">Chargement du document...</p>
              </div>
            </div>
          </section>
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
          <div class="space-y-4">
            <!-- Options disponibles -->
            <div v-if="coursComplet?.options && coursComplet.options.length > 0">
              <h4 class="text-sm font-semibold text-gray-700 mb-3">Options disponibles</h4>
              <div class="space-y-2">
                <div v-for="option in coursComplet.options" :key="'opt-' + option.id"
                  class="rounded-xl p-4 transition-colors"
                  :class="optionSelectionnee(option.id) ? 'bg-gray-50 border-2 border-tertiary-600' : 'bg-gray-50 border border-gray-200'">
                  <div class="flex justify-between items-start">
                    <div class="flex-1">
                      <p class="font-medium text-gray-800">{{ option.nom }}</p>
                      <p class="text-xs text-gray-500 mt-1">Type: {{ option.type }}</p>
                    </div>
                    <div class="text-right">
                      <p class="text-sm font-medium text-gray-700">{{ option.tarif }} CHF</p>
                      <div v-if="optionSelectionnee(option.id)" class="mt-2">
                        <div v-if="option.type === 'Quantifiable'" class="text-sm">
                          <span class="font-bold text-tertiary-900">✓ ×{{ optionSelectionnee(option.id).quantite }}</span>
                        </div>
                        <div v-else class="text-sm">
                          <span class="font-bold text-tertiary-900">✓ Sélectionné</span>
                        </div>
                      </div>
                      <div v-else class="mt-2 text-xs text-gray-400">Non sélectionné</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Aucune option -->
            <div v-else class="flex flex-col items-center justify-center py-12 text-gray-400">
              <Icon icon="mdi:cog-outline" class="w-12 h-12 mb-3 opacity-40" />
              <p class="text-sm">Aucune option disponible pour cette course.</p>
            </div>
          </div>
        </div>

        <!-- Onglet Questions -->
        <div v-if="activeTab === 'questions'" class="p-6">
          <div v-if="coursComplet?.questionnaire && coursComplet.questionnaire.length > 0" class="space-y-4">
            <div v-for="question in coursComplet.questionnaire" :key="'q-' + question.id"
              class="rounded-xl p-4 transition-colors"
              :class="reponseQuestion(question.id) ? 'bg-gray-50' : 'bg-gray-50 border border-gray-200'">
              <p class="font-medium text-gray-800 mb-3">{{ question.question }}</p>

              <!-- Toutes les réponses possibles -->
              <div class="space-y-2">
                <p class="text-xs text-gray-500 font-semibold">Réponses possibles:</p>
                <div v-for="(answer, idx) in question.answers" :key="idx"
                  class="flex items-center gap-2 p-2 rounded text-sm"
                  :class="reponseQuestion(question.id) === answer.texte ? 'bg-tertiary-300 text-primary' : 'text-gray-700'">
                  <div class="flex-shrink-0 w-4 h-4 rounded border"
                    :class="reponseQuestion(question.id) === answer.texte ? 'bg-accent-300 border-accent-600' : 'border-gray-300'">
                  </div>
                  {{ answer.texte }}
                </div>
              </div>
            </div>
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
import courseOrganisateurService from '../services/courseOrganisateurService';
import documentService from '../services/documentService';

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
      coursComplet: null,
      glisserDocument: false,
      chargementDocument: false,
      tabs: [
        { key: 'general',   label: 'Général',   icon: 'mdi:information-outline' },
        { key: 'options',   label: 'Options',   icon: 'mdi:cog-outline' },
        { key: 'questions', label: 'Questions', icon: 'mdi:comment-question-outline' },
      ],
    };
  },
  computed: {
    etradeAdmin() {
      // Vérifier si c'est un admin (on peut ajouter une prop pour ça)
      return false;
    }
  },
  methods: {
    formatDate(dateStr) {
      if (!dateStr) return '—';
      const [y, m, d] = dateStr.split('-');
      return `${d}.${m}.${y}`;
    },
    async chargerCourseComplete() {
      try {
        const response = await courseOrganisateurService.getCourse(this.inscription.id_course);
        this.coursComplet = response.data;
      } catch (e) {
        console.error('Erreur lors du chargement de la course :', e);
      }
    },
    optionSelectionnee(idOption) {
      const choix = (this.inscription.choix_options || []).find(c => c.id_option === idOption);
      return choix || null;
    },
    reponseQuestion(idQuestion) {
      const reponse = (this.inscription.reponses_questions || []).find(r => r.id_question === idQuestion);
      return reponse?.option?.texte_option || null;
    },
    async telechargerDocument(doc) {
      try {
        const response = await documentService.downloadDocument(doc.id);
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', doc.url.split('/').pop());
        document.body.appendChild(link);
        link.click();
        link.parentNode.removeChild(link);
        window.URL.revokeObjectURL(url);
      } catch (e) {
        console.error('Erreur téléchargement :', e);
      }
    },
    async supprimerDocument(idDoc) {
      if (confirm('Supprimer ce document ?')) {
        try {
          await documentService.deleteDocument(idDoc);
          // Retirer du tableau local
          const idx = this.inscription.documentsFournis.findIndex(d => d.id === idDoc);
          if (idx > -1) {
            this.inscription.documentsFournis.splice(idx, 1);
          }
        } catch (e) {
          console.error('Erreur suppression :', e);
        }
      }
    },
    async selectionnerDocument(event) {
      const fichier = event.target.files[0];
      if (fichier) {
        await this.uploadDocument(fichier);
      }
      event.target.value = '';
    },
    deposerDocument(event) {
      this.glisserDocument = false;
      const fichier = event.dataTransfer.files[0];
      if (fichier) {
        this.uploadDocument(fichier);
      }
    },
    async uploadDocument(fichier) {
      try {
        this.chargementDocument = true;
        const formData = new FormData();
        formData.append('file', fichier);

        const response = await documentService.uploadDocument(this.inscription.id, formData);

        // Ajouter le document à la liste
        if (!this.inscription.documentsFournis) {
          this.inscription.documentsFournis = [];
        }
        this.inscription.documentsFournis.push(response.data.document);
      } catch (e) {
        console.error('Erreur upload :', e);
      } finally {
        this.chargementDocument = false;
      }
    },
    ouvrirChangementCourse() {
      this.showChangementCourse = true;
    },
    onChangementConfirme(data) {
      this.showChangementCourse = false;
      this.$emit('ajouter-panier', data);
    },
  },
  mounted() {
    this.chargerCourseComplete();
  }
};
</script>