<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-8xl mx-4 flex flex-col overflow-hidden" style="height: 80vh">
      <div class="flex items-center justify-between px-6 pt-5 pb-0 border-b border-gray-100 bg-tertiary-900">
        <div class="flex flex-col w-full">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div>
                <span class="px-6 text-subtitle font-medium text-secondary">Mon inscription</span>
                <div class="h-1 w-24 ml-6 rounded-r-full mb-2" :style="{ backgroundColor: inscription.course.evenement?.couleur_secondaire }"></div>
              </div>
              <span v-if="isEdit" class="flex items-center gap-1.5 bg-amber-100 text-amber-800 text-xs font-bold px-3 py-1 rounded-full">
                <Icon icon="mdi:pencil" class="w-3 h-3" /> Mode modification
              </span>
            </div>
            <button @click="$emit('close')" class="text-secondary hover:text-gray-600 transition-colors mr-1 self-start mt-1">
              <Icon icon="mdi:close" class="w-5 h-5" />
            </button>
          </div>

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

      <div class="flex-1 overflow-y-auto pb-20">

        <div v-if="activeTab === 'general'" class="p-6 space-y-6">

          <section>
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
              <Icon icon="mdi:account" class="w-4 h-4" /> Particpant
            </h3>
            <div class="bg-gray-50 rounded-xl p-4 grid grid-cols-2 md:grid-cols-4 gap-4">
              <div>
                <p class="text-xs text-gray-400">Nom complet</p>
                <p class="font-medium text-gray-800">{{ inscription.participant.prenom }} {{ inscription.participant.nom }}</p>
              </div>
            </div>
          </section>

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

          <section>
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
              <Icon icon="mdi:receipt-text" class="w-4 h-4" /> Inscription
            </h3>
            <div class="bg-gray-50 rounded-xl p-4 grid grid-cols-2 md:grid-cols-5 gap-4">

              <div v-if="inscription.groupe || inscription.participant.equipe_nom" class="col-span-2 md:col-span-5 flex gap-3">
                <div v-if="inscription.groupe" class="bg-white rounded-full py-0.5 px-3 border border-gray-200 inline-flex items-center gap-2">
                  <Icon icon="mdi:account-group" class="w-3 h-3 text-gray-400" />
                  <p class="text-xs font-medium text-gray-700">{{ inscription.groupe.nom }}</p>
                </div>
                <div v-if="inscription.participant.equipe_nom" class="bg-white rounded-full py-0.5 px-3 border border-gray-200 inline-flex items-center gap-2">
                  <Icon icon="mdi:shield-outline" class="w-3 h-3 text-gray-400" />
                  <p class="text-xs font-medium text-gray-700">{{ inscription.participant.equipe_nom }}</p>
                </div>
              </div>

              <div>
                <p class="text-xs text-gray-400">Statut paiement</p>
                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold mt-1"
                  :class="inscription.status_paiement === 'Validé' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'">
                  {{ inscription.status_paiement }}
                </span>
              </div>

              <div>
                <p class="text-xs text-gray-400">Tarif payé</p>
                <p class="font-medium text-gray-800 mt-1">{{ inscription.tarif }} CHF</p>
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
                <p class="text-xs text-gray-400">Dossard</p>
                <p class="font-medium text-gray-800">{{ inscription.dossard?.numero ?? '—' }}</p>
              </div>

              <div>
                <p class="text-xs text-gray-400">N° inscription</p>
                <p class="font-medium text-gray-800">{{ inscription.id ?? '—' }}</p>
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
                <p class="font-medium text-gray-800">
                  {{ inscription.type_challenge === 'Groupe' ? 'Université' : (inscription.type_challenge ?? '—') }}
                </p>
              </div>

              <div>
                <p class="text-xs text-gray-400">Équipe challenge</p>
                <p class="font-medium text-gray-800">{{ inscription.groupe?.nom ?? '—' }}</p>
              </div>

              <div>
                <p class="text-xs text-gray-400">Code de participation</p>
                <p class="font-medium text-gray-800">{{ inscription.code_participant ?? '—' }}</p>
              </div>

              <div>
                <p class="text-xs text-gray-400">Avertissement validé</p>
                <p class="font-medium text-gray-800">{{ inscription.avertissement_valide ? 'Oui' : 'Non' }}</p>
              </div>

            </div>
          </section>


          <section v-if="inscription.ancienne_inscription" class="border-l-2 border-tertiary pl-2">
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
              <Icon icon="mdi:history" class="w-4 h-4" /> Ancienne inscription
            </h3>
            <div class="p-2">
              <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                <Icon icon="mdi:run-fast" class="w-4 h-4" /> Course
              </h3>
              <div class="bg-gray-50 rounded-xl p-4 grid grid-cols-2 md:grid-cols-6 gap-4">
                <div>
                  <p class="text-xs text-gray-400">Nom</p>
                  <p class="font-medium text-gray-800">{{ inscription.ancienne_inscription.course?.nom ?? '—' }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Distance</p>
                  <p class="font-medium text-gray-800">{{ inscription.ancienne_inscription.course?.distance ?? '—' }} km</p>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Type</p>
                  <p class="font-medium text-gray-800">{{ inscription.ancienne_inscription.course?.type ?? '—' }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Date</p>
                  <p class="font-medium text-gray-800">{{ formatDate(inscription.ancienne_inscription.course?.date_debut) }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Statut</p>
                  <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold"
                    :class="inscription.ancienne_inscription.course?.status === 'actif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                    {{ inscription.ancienne_inscription.course?.status ?? '—' }}
                  </span>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Tarif</p>
                  <p class="font-medium text-gray-800">{{ inscription.ancienne_inscription.tarif }} CHF</p>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 grid grid-cols-2 md:grid-cols-5 gap-4 mt-2">
              <div v-if="inscription.ancienne_inscription.groupe || inscription.ancienne_inscription.participant?.equipe_nom" class="space-y-4">
                <div v-if="inscription.ancienne_inscription.groupe" class="rounded-full py-0.5 px-2 inline-block">
                  <p class="text-xs text-gray-400 mb-1">Groupe</p>
                  <p class="font-medium text-gray-800">{{ inscription.ancienne_inscription.groupe?.nom ?? '—' }}</p>
                </div>
                <div v-if="inscription.ancienne_inscription.participant?.equipe_nom">
                  <p class="text-xs text-gray-400 mb-1">Équipe</p>
                  <p class="font-medium text-gray-800">{{ inscription.ancienne_inscription.participant?.equipe_nom ?? '—' }}</p>
                </div>
              </div>
              <div>
                <p class="text-xs text-gray-400">Statut</p>
                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold mt-1"
                  :class="inscription.ancienne_inscription.status_paiement === 'Validé' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'">
                  {{ inscription.ancienne_inscription.status_paiement }}
                </span>
              </div>
              <div>
                <p class="text-xs text-gray-400">Tarif payé</p>
                <p class="font-medium text-gray-800">{{ inscription.ancienne_inscription.tarif }} CHF</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Date paiement</p>
                <p class="font-medium text-gray-800">{{ inscription.ancienne_inscription.date_paiement ?? '—' }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Dossard</p>
                <p class="font-medium text-gray-800">{{ inscription.ancienne_inscription.dossard?.numero ?? '—' }}</p>
              </div>
            </div>
          </section>

          <section>
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
              <Icon icon="mdi:file-document-outline" class="w-4 h-4" /> Documents
            </h3>
            <div v-if="inscription.course.document_description" class="my-2">
              <span class="text-sm text-gray-600">{{ inscription.course.document_description }}</span>
            </div>
            <div class="bg-gray-50 rounded-xl p-4">
              <div v-if="inscription.documents_fournis && inscription.documents_fournis.length > 0" class="space-y-3">
                <div v-for="doc in inscription.documents_fournis" :key="doc.id"
                  @click="ouvrirDocument(doc)"
                  class="flex items-center justify-between bg-white rounded-lg p-3 border border-gray-200 hover:bg-tertiary-300 cursor-pointer">
                  <div class="flex items-center gap-3">
                    <Icon icon="mdi:file-document-outline" class="w-6 h-6 text-primary" />
                    <div @click.stop>
                      <p class="font-medium text-gray-800">{{ doc.url.split('/').pop() }}</p>
                      <p class="text-xs text-gray-400">
                        <template v-if="doc.date_debut">Valable du {{ formatDate(doc.date_debut) }}
                          <template v-if="doc.date_fin">au {{ formatDate(doc.date_fin) }}</template>
                        </template>
                        <template v-if="doc.valable"> • Valide</template>
                      </p>
                    </div>
                  </div>
                  <div class="flex items-center gap-2">
                    <button @click.stop="telechargerDocument(doc)"
                      class="p-2 text-primary hover:bg-gray-100 rounded transition-colors" title="Télécharger">
                      <Icon icon="mdi:download" class="w-4 h-4" />
                    </button>
                    <button v-if="isEdit" @click.stop="supprimerDocument(doc.id)"
                      class="p-2 text-accent hover:bg-red-50 rounded transition-colors" title="Supprimer">
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

          <section v-if="isEdit">
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
              <Icon icon="mdi:cloud-upload-outline" class="w-4 h-4" /> Ajouter un document
            </h3>
            <div class="bg-gray-50 rounded-xl p-4">
              <div
                class="border-2 border-dashed rounded-lg flex flex-col items-center justify-center py-6 gap-3 cursor-pointer transition-all"
                :class="glisserDocument ? 'border-accent-600 bg-amber-50' : 'border-gray-300 hover:border-gray-300'"
                @dragover.prevent="glisserDocument = true"
                @dragleave="glisserDocument = false"
                @drop.prevent="deposerDocument"
                @click="$refs.inputDoc.click()"
              >
                <Icon icon="mdi:upload-outline" class="w-8 h-8 text-gray-400" />
                <p class="text-sm text-gray-600 text-center">
                  Glissez-déposez un fichier ou<br>
                  <span class="text-accent font-medium">cliquez pour parcourir</span>
                </p>
                <p class="text-xs text-gray-400">PDF, JPG, PNG — max 10 Mo</p>
                <input ref="inputDoc" type="file" class="hidden" accept=".pdf,.jpg,.jpeg,.png" @change="selectionnerDocument" />
              </div>
              <div v-if="chargementDocument" class="mt-3 text-center">
                <p class="text-sm text-gray-600">Chargement du document...</p>
              </div>
            </div>
          </section>

        </div>

        <div v-if="activeTab === 'options'" class="p-6">
          <div class="space-y-4">
            <div v-if="coursComplet?.options && coursComplet.options.length > 0">
              <h4 class="text-sm font-semibold text-gray-700 mb-3">
                {{ isEdit ? 'Cliquez sur une option pour la sélectionner / désélectionner' : 'Options sélectionnées' }}
              </h4>
              <div class="space-y-2">
                <div v-for="option in coursComplet.options" :key="'opt-' + option.id"
                  class="rounded-xl p-4 transition-all"
                  :class="[
                    optionSelectionneePourAffichage(option.id) ? 'bg-gray-50 border-2 border-tertiary-600' : 'bg-gray-50 border border-gray-200',
                    isEdit ? 'cursor-pointer hover:border-accent-600 hover:shadow-sm' : ''
                  ]"
                  @click="isEdit && toggleOption(option)"
                >
                  <div class="flex justify-between items-start">
                    <div class="flex items-center gap-3 flex-1">
                      <div v-if="isEdit" class="flex-shrink-0">
                        <Icon
                          :icon="optionSelectionneePourAffichage(option.id) ? 'mdi:checkbox-marked-circle' : 'mdi:checkbox-blank-circle-outline'"
                          class="w-5 h-5"
                          :class="optionSelectionneePourAffichage(option.id) ? 'text-tertiary-600' : 'text-gray-300'"
                        />
                      </div>
                      <div>
                        <p class="font-medium text-gray-800">{{ option.nom }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ option.description }}</p>
                      </div>
                    </div>
                    <div class="text-right ml-4">
                      <p class="text-sm font-medium text-gray-700">{{ option.tarif }} CHF</p>
                      <div v-if="optionSelectionneePourAffichage(option.id)" class="mt-2">
                        <div v-if="option.type === 'Quantifiable'" class="flex items-center gap-2 justify-end">
                          <template v-if="isEdit">
                            <input
                              type="number" min="0"
                              :value="getQuantiteOption(option.id)"
                              @click.stop
                              @input="mettreAJourQuantite(option.id, $event.target.value)"
                              class="border border-gray-300 rounded px-2 py-1 w-16 text-sm bg-white"
                            />
                            <span class="text-xs text-gray-400">unité(s)</span>
                          </template>
                          <span v-else class="font-bold text-tertiary-900">✓ ×{{ optionSelectionnee(option.id).quantite }}</span>
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
            <div v-else class="flex flex-col items-center justify-center py-12 text-gray-400">
              <Icon icon="mdi:cog-outline" class="w-12 h-12 mb-3 opacity-40" />
              <p class="text-sm">Aucune option disponible pour cette course.</p>
            </div>
          </div>
        </div>

        <div v-if="activeTab === 'questions'" class="p-6">
          <div v-if="coursComplet?.questionnaire && coursComplet.questionnaire.length > 0" class="space-y-4">
            <div v-for="question in coursComplet.questionnaire" :key="'q-' + question.id"
              class="bg-gray-50 rounded-xl p-4 border border-gray-200">
              <p class="font-medium text-gray-800 mb-3">{{ question.question || question.enonce || question.texte || '—' }}</p>

              <div class="space-y-2">
                <p class="text-xs text-gray-500 font-semibold">Votre réponse :</p>
                <div v-for="(answer, idx) in (question.answers || question.choix || [])" :key="idx"
                  class="flex items-center gap-2 p-2 rounded text-sm"
                  :class="reponseQuestionAffichage(question.id, answer.id) ? 'bg-tertiary-300 text-primary' : 'text-gray-700'">
                  <Icon
                    :icon="reponseQuestionAffichage(question.id, answer.id) ? 'mdi:radiobox-marked' : 'mdi:radiobox-blank'"
                    class="w-4 h-4 flex-shrink-0"
                    :class="reponseQuestionAffichage(question.id, answer.id) ? 'text-accent-600' : 'text-gray-300'"
                  />
                  {{ answer.option || answer.texte || answer.texte_option || answer.libelle || '—' }}
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

      <div class="absolute bottom-0 left-0 right-0 border-t px-6 py-3 flex items-center justify-between transition-colors z-10"
        :class="isEdit ? 'bg-amber-50 border-gray-300' : 'bg-white border-gray-100'">

        <div class="flex items-center gap-2">
          <Icon v-if="isEdit" icon="mdi:pencil-circle" class="w-5 h-5 text-accent" />
          <span v-if="isEdit" class="text-sm font-medium text-amber-700">Mode modification actif</span>
        </div>

        <div class="flex items-center gap-3" v-if="inscription.status_paiement !== 'Annulé'">
          <span v-if="inscriptionsFermees" class="text-sm text-accent italic mr-2">
            Les modifications sont clôturées pour cette course.
          </span>

          <button
            class="flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium transition-colors"
            :class="inscriptionsFermees ? 'bg-gray-100 text-gray-400 opacity-50 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-100'"
            @click="ouvrirChangementCourse"
            :disabled="inscriptionsFermees"
          >
            <Icon icon="mdi:swap-horizontal" class="w-4 h-4" />
            Changer de course
          </button>

          <template v-if="!isEdit">
            <button
              class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors"
              :class="inscriptionsFermees ? 'bg-gray-100 text-gray-400 opacity-50 cursor-not-allowed border border-gray-300' : 'btn-accent-300'"
              @click="activerEdition"
              :disabled="inscriptionsFermees"
            >
              <Icon icon="mdi:pencil" class=" w-4 h-4" />
              Modifier
            </button>
          </template>
          <template v-else>
            <button
              class="flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors"
              @click="annulerEdition"
            >
              <Icon icon="mdi:close" class="w-4 h-4" />
              Annuler
            </button>
            <button
              class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium bg-accent text-white hover:bg-accent-600 transition-colors shadow-sm"
              @click="sauvegarderEdition"
            >
              <Icon icon="mdi:check" class="w-4 h-4" />
              Sauvegarder
            </button>
          </template>
        </div>
      </div>

    </div>

  
      <PopupChangementCourseParticipant
        v-if="showChangementCourse"
        :inscription="inscription"
        :participants="participants"
        @close="fermerAvecPopup"
        @confirmer="onChangementConfirme"
      />

      <PopupConfirmation
        v-if="documentASupprimer"
        message="Supprimer ce document ?"
        icon="mdi:alert-circle-outline"
        @confirm="confirmerSuppressionDocument"
        @cancel="documentASupprimer = null"
      />
  </div>
</template>

<script>
/**
 * @fileoverview Composant PopupInscriptionDetailParticipant.
 * @description Modale de consultation et d'édition d'une inscription côté participant.
 * Elle centralise les onglets de synthèse (général, options, questions, documents),
 * le changement de course et les actions de mise à jour associées.
 * @remarks Les vérifications de fermeture des inscriptions pilotent le verrouillage de l'interface.
 */
import { Icon } from '@iconify/vue';
import PopupChangementCourseParticipant from './PopupChangementCourseParticipant.vue';
import PopupConfirmation from './PopupConfirmation.vue';
import courseParticipantService from '../services/courseParticipantService';
import documentService from '../services/documentService';
import inscriptionService from '../services/inscriptionService';

export default {
  name: 'PopupInscriptionDetailParticipant',
  components: { Icon, PopupChangementCourseParticipant, PopupConfirmation },
  emits: ['close', 'modifier-inscription', 'ajouter-panier'],
  props: {
    inscription: { type: Object, required: true },
    participants: { type: Array, default: () => [] },
  },
  data() {
    return {
      activeTab: 'general',
      coursComplet: null,
      glisserDocument: false,
      chargementDocument: false,
      isEdit: false,
      inscriptionEdit: null,
      showChangementCourse: false,
      documentASupprimer: null,
      tabs: [
        { key: 'general',   label: 'Général',   icon: 'mdi:information-outline' },
        { key: 'options',   label: 'Options',   icon: 'mdi:cog-outline' },
        { key: 'questions', label: 'Questions', icon: 'mdi:comment-question-outline' },
      ],
    };
  },
  computed: {
    /**
     * Indique si les inscriptions sont fermées pour la course associée.
     * Quand la date limite est dépassée, les actions d'édition sont verrouillées.
     * @returns {boolean}
     */
    inscriptionsFermees() {
      if (!this.inscription?.course?.fin_inscription) return false;
      const fin = new Date(this.inscription.course.fin_inscription);
      fin.setHours(23, 59, 59, 999);
      return new Date() > fin;
    }
  },
  methods: {
    /**
     * Formate une date ISO en représentation lisible JJ.MM.AAAA.
     * @param {string} dateStr
     * @returns {string}
     */
    formatDate(dateStr) {
      if (!dateStr) return '—';
      const [y, m, d] = dateStr.split('-');
      return `${d}.${m}.${y}`;
    },

    /**
     * Charge la version complète de la course liée à l'inscription.
     * Utilisé pour afficher les informations détaillées et les étapes du flux.
     * @returns {Promise<void>}
     */
    async chargerCourseComplete() {
      try {
        const response = await courseParticipantService.getCourse(this.inscription.id_course);
        this.coursComplet = response.data;
      } catch (e) {
        console.error('Erreur lors du chargement de la course :', e);
      }
    },

    /**
     * Charge explicitement les documents liés à l'inscription.
     * Ce fallback évite les cas où la relation n'est pas incluse dans le payload initial.
     * @returns {Promise<void>}
     */
    async chargerDocumentsFournis() {
      if (!this.inscription?.id) return;
      try {
        const response = await documentService.getDocumentsByInscription(this.inscription.id);
        this.inscription.documents_fournis = Array.isArray(response.data) ? response.data : [];
      } catch (e) {
        console.error('Erreur lors du chargement des documents :', e);
      }
    },

    /**
     * Active le mode édition local des choix d'options.
     * Une copie profonde est créée pour éviter de modifier la donnée source avant validation.
     * @returns {void}
     */
    activerEdition() {
      this.isEdit = true;
      this.inscriptionEdit = {
        choix_options: JSON.parse(JSON.stringify(this.inscription.choix_options ?? [])),
      };
    },

    /**
     * Annule les modifications locales et quitte le mode édition.
     * @returns {void}
     */
    annulerEdition() {
      this.isEdit = false;
      this.inscriptionEdit = null;
    },

    /**
     * Enregistre les choix d'options modifiés sur l'inscription.
     * Les quantités sont normalisées en entiers avant l'envoi.
     * @returns {Promise<void>}
     */
    async sauvegarderEdition() {
      try {
        const choixOptions = this.inscriptionEdit.choix_options.map(choix => ({
          id_option: choix.id_option,
          quantite: choix.quantite !== null && choix.quantite !== undefined ? parseInt(choix.quantite) : null,
        }));

        await inscriptionService.updateInscription(this.inscription.id, {
          choix_options: choixOptions,
        });

        Object.assign(this.inscription, {
          choix_options: JSON.parse(JSON.stringify(choixOptions ?? [])),
        });

        this.isEdit = false;
        this.$emit('modifier-inscription', this.inscription);
      } catch (e) {
        console.error('Erreur sauvegarde :', e);
      }
    },

    /**
     * Retourne le choix d'option déjà enregistré pour un identifiant d'option donné.
     * @param {number} idOption
     * @returns {object|null}
     */
    optionSelectionnee(idOption) {
      return (this.inscription.choix_options ?? []).find(c => c.id_option === idOption) || null;
    },

    /**
     * Retourne le choix d'option affichable en tenant compte du mode édition.
     * @param {number} idOption
     * @returns {object|null}
     */
    optionSelectionneePourAffichage(idOption) {
      if (this.isEdit && this.inscriptionEdit) {
        return this.inscriptionEdit.choix_options.find(c => c.id_option === idOption) || null;
      }
      return this.optionSelectionnee(idOption);
    },

    /**
     * Retourne la quantité sélectionnée pour une option donnée.
     * @param {number} idOption
     * @returns {number}
     */
    getQuantiteOption(idOption) {
      if (this.isEdit && this.inscriptionEdit) {
        return this.inscriptionEdit.choix_options.find(c => c.id_option === idOption)?.quantite ?? 1;
      }
      return this.optionSelectionnee(idOption)?.quantite ?? 1;
    },

    /**
     * Ajoute ou retire une option de la sélection en mode édition.
     * @param {object} option
     * @returns {void}
     */
    toggleOption(option) {
      const idx = this.inscriptionEdit.choix_options.findIndex(c => c.id_option === option.id);
      if (idx > -1) {
        this.inscriptionEdit.choix_options.splice(idx, 1);
      } else {
        this.inscriptionEdit.choix_options.push({
          id_option: option.id,
          id_inscription: this.inscription.id,
            quantite: 1,
        });
      }
    },

    /**
     * Met à jour la quantité d'une option dans l'édition locale.
     * @param {number} idOption
     * @param {string|number} valeur
     * @returns {void}
     */
    mettreAJourQuantite(idOption, valeur) {
      const choix = this.inscriptionEdit.choix_options.find(c => c.id_option === idOption);
      if (choix) choix.quantite = parseInt(valeur) || 0;
    },

    /**
     * Télécharge un document fourni par le participant.
     * Le fichier est sauvegardé localement via un lien temporaire.
     * @param {object} doc
     * @returns {Promise<void>}
     */
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

    /**
     * Ouvre un document dans un nouvel onglet.
     * @param {object} doc
     * @returns {Promise<void>}
     */
    async ouvrirDocument(doc) {
      try {
        const response = await documentService.downloadDocument(doc.id);
        const url = window.URL.createObjectURL(new Blob([response.data]));
        window.open(url, '_blank');
      } catch (e) {
        console.error('Erreur ouverture :', e);
      }
    },

    /**
     * Prépare la suppression d'un document en affichant la confirmation.
     * @param {number} idDoc
     * @returns {Promise<void>}
     */
    async supprimerDocument(idDoc) {
      this.documentASupprimer = idDoc;
    },

    /**
     * Confirme la suppression du document sélectionné.
     * Retire ensuite le document de la liste locale si l'opération réussit.
     * @returns {Promise<void>}
     */
    async confirmerSuppressionDocument() {
      if (!this.documentASupprimer) return;
      try {
        await documentService.deleteDocument(this.documentASupprimer);
        const idx = (this.inscription.documents_fournis ?? []).findIndex(d => d.id === this.documentASupprimer);
        if (idx > -1) this.inscription.documents_fournis.splice(idx, 1);
        this.documentASupprimer = null;
      } catch (e) {
        console.error('Erreur suppression :', e);
      }
    },

    /**
     * Réagit à la sélection d'un fichier depuis un input natif.
     * @param {Event} event
     * @returns {Promise<void>}
     */
    async selectionnerDocument(event) {
      const fichier = event.target.files[0];
      if (fichier) await this.uploadDocument(fichier);
      event.target.value = '';
    },

    /**
     * Gère le dépôt d'un fichier par glisser-déposer.
     * @param {DragEvent} event
     * @returns {void}
     */
    deposerDocument(event) {
      this.glisserDocument = false;
      const fichier = event.dataTransfer.files[0];
      if (fichier) this.uploadDocument(fichier);
    },

    /**
     * Envoie un document au serveur et l'ajoute à la liste locale des pièces fournies.
     * @param {File} fichier
     * @returns {Promise<void>}
     */
    async uploadDocument(fichier) {
      try {
        this.chargementDocument = true;
        const formData = new FormData();
        formData.append('file', fichier);
        const response = await documentService.uploadDocument(this.inscription.id, formData);
        if (!this.inscription.documents_fournis) this.inscription.documents_fournis = [];
        this.inscription.documents_fournis.push(response.data.document);
      } catch (e) {
        console.error('Erreur upload :', e);
      } finally {
        this.chargementDocument = false;
      }
    },

    /**
     * Ouvre la modale de changement de course.
     * @returns {void}
     */
    ouvrirChangementCourse() {
      this.showChangementCourse = true;
    },

    /**
     * Ferme la modale de changement de course et transmet le panier à mettre à jour.
     * @param {object} data
     * @returns {void}
     */
    onChangementConfirme(data) {
      this.showChangementCourse = false;
      this.$emit('ajouter-panier', data);
    },

    /**
     * Ferme la modale de changement ET la modale parent d'inscription.
     * @returns {void}
     */
    fermerAvecPopup() {
      this.showChangementCourse = false;
      this.$emit('close');
    },

    /**
     * Vérifie si une réponse donnée correspond à l'affichage d'une question.
     * @param {number} idQuestion
     * @param {number} idAnswer
     * @returns {boolean}
     */
    reponseQuestionAffichage(idQuestion, idAnswer) {
      const reponse = (this.inscription.reponses_questions ?? []).find(r => r.id_question === idQuestion);
      return reponse && reponse.id_option_choisie === idAnswer;
    },
  },
  mounted() {
    this.chargerCourseComplete();
    this.chargerDocumentsFournis();
  },
};
</script>