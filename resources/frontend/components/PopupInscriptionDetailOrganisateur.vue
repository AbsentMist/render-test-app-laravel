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
            <div class="flex items-center gap-3">
              <div>
                <span class="px-6 text-subtitle font-medium text-secondary">Detail inscription</span>
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
      <div class="flex-1 overflow-y-auto pb-20">

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

          <!-- Inscription -->
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

              <!-- Statut paiement -->
              <div>
                <p class="text-xs text-gray-400">Statut paiement</p>
                <select v-if="isEdit" v-model="inscriptionEdit.status_paiement"
                  class="mt-1 text-xs border border-gray-300 rounded px-2 py-1 bg-white w-full">
                  <option>Validé</option>
                  <option>En attente</option>
                  <option>Annulé</option>
                </select>
                <span v-else class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold mt-1"
                  :class="inscription.status_paiement === 'Validé' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'">
                  {{ inscription.status_paiement }}
                </span>
              </div>

              <!-- Tarif -->
              <div>
                <p class="text-xs text-gray-400">Tarif payé</p>
                <div v-if="isEdit" class="flex items-center gap-1 mt-1">
                  <input v-model="inscriptionEdit.tarif" type="number"
                    class="text-sm border border-gray-300 rounded px-2 py-1 w-24 bg-white" />
                  <span class="text-xs text-gray-400">CHF</span>
                </div>
                <p v-else class="font-medium text-gray-800">{{ inscription.tarif }} CHF</p>
              </div>

              <!-- Date paiement -->
              <div>
                <p class="text-xs text-gray-400">Date de paiement</p>
                <input v-if="isEdit" v-model="inscriptionEdit.date_paiement" type="datetime-local"
                  class="mt-1 text-xs border border-gray-300 rounded px-2 py-1 bg-white w-full " />
                <p v-else class="font-medium text-gray-800">{{ inscription.date_paiement ?? '—' }}</p>
              </div>

              <!-- Rabais -->
              <div>
                <p class="text-xs text-gray-400">Rabais</p>
                <div v-if="isEdit" class="flex items-center gap-1 mt-1">
                  <input v-model="inscriptionEdit.montant_rabais" type="number"
                    class="text-sm border border-gray-300 rounded px-2 py-1 w-24 bg-white " />
                  <span class="text-xs text-gray-400">CHF</span>
                </div>
                <p v-else class="font-medium text-gray-800">{{ inscription.montant_rabais }} CHF</p>
              </div>

              <!-- Dossard -->
              <div>
                <p class="text-xs text-gray-400">Dossard</p>
                <p class="font-medium text-gray-800">{{ inscription.dossard?.numero ?? '—' }}</p>
              </div>

              <!-- N° inscription -->
              <div>
                <p class="text-xs text-gray-400">N° inscription</p>
                <input v-if="isEdit" v-model="inscriptionEdit.numero_inscription" type="text"
                  class="mt-1 text-sm rounded px-2 py-1 w-full bg-white border border-gray-300" />                
                <p v-else class="font-medium text-gray-800">{{ inscription.numero_inscription ?? '—' }}</p>
              </div>

              <!-- Ref groupage -->
              <div>
                <p class="text-xs text-gray-400">Ref. Groupage</p>
                <input v-if="isEdit" v-model="inscriptionEdit.ref_groupage" type="text"
                  class="mt-1 text-sm rounded px-2 py-1 w-full bg-white border border-gray-300" />
                <p v-else class="font-medium text-gray-800">{{ inscription.ref_groupage ?? '—' }}</p>
              </div>

              <!-- Challenge -->
              <div>
                <p class="text-xs text-gray-400">Participe au challenge ?</p>
                <select v-if="isEdit" v-model="inscriptionEdit.participe_challenge"
                  class="mt-1 text-xs border border-gray-300 rounded px-2 py-1 bg-white ">
                  <option :value="true">Oui</option>
                  <option :value="false">Non</option>
                </select>
                <p v-else class="font-medium text-gray-800">{{ inscription.participe_challenge ? 'Oui' : 'Non' }}</p>
              </div>

              <!-- Type challenge -->
              <div>
                <p class="text-xs text-gray-400">Challenge</p>
                <input v-if="isEdit" v-model="inscriptionEdit.type_challenge" type="text"
                  class="mt-1 text-sm border border-gray-300 rounded px-2 py-1 w-full bg-white " />
                <p v-else class="font-medium text-gray-800">
    {{ inscription.type_challenge === 'Groupe' ? 'Université' : (inscription.type_challenge ?? '—') }}
</p>
              </div>

              <!-- Equipe challenge -->
              <div>
                <p class="text-xs text-gray-400">Équipe challenge</p>
                <input v-if="isEdit" v-model="inscriptionEdit.equipe_challenge" type="text"
                  class="mt-1 text-sm border border-gray-300 rounded px-2 py-1 w-full bg-white " />
                <p v-else class="font-medium text-gray-800">{{ inscription.groupe?.nom ?? '—' }}</p>
              </div>

              <!-- Code participation -->
              <div>
                <p class="text-xs text-gray-400">Code de participation</p>
                <input v-if="isEdit" v-model="inscriptionEdit.code_participant" type="text"
                  class="mt-1 text-sm border border-gray-300 rounded px-2 py-1 w-full bg-white " />
                <p v-else class="font-medium text-gray-800">{{ inscription.code_participant ?? '—' }}</p>
              </div>

              <!-- Avertissement validé -->
              <div>
                <p class="text-xs text-gray-400">Avertissement validé</p>
                <select v-if="isEdit" v-model="inscriptionEdit.avertissement_valide"
                  class="mt-1 text-xs border border-gray-300 rounded px-2 py-1 bg-white ">
                  <option :value="true">Oui</option>
                  <option :value="false">Non</option>
                </select>
                <p v-else class="font-medium text-gray-800">{{ inscription.avertissement_valide ? 'Oui' : 'Non' }}</p>
              </div>

            </div>
          </section>

          <!-- Ancienne inscription -->
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
                  <p class="font-medium text-gray-800">{{ inscription.ancienne_inscription.groupe?.nom }}</p>
                </div>
                <div v-if="inscription.ancienne_inscription.participant?.equipe_nom">
                  <p class="text-xs text-gray-400 mb-1">Équipe</p>
                  <p class="font-medium text-gray-800">{{ inscription.ancienne_inscription.participant?.equipe_nom }}</p>
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

          <!-- Documents -->
          <section>
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
              <Icon icon="mdi:file-document-outline" class="w-4 h-4" /> Documents
            </h3>
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

          <!-- Upload document -->
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

        <!-- Onglet Options -->
        <div v-if="activeTab === 'options'" class="p-6">
          <div class="space-y-4">
            <div v-if="coursComplet?.options && coursComplet.options.length > 0">
              <h4 class="text-sm font-semibold text-gray-700 mb-3">
                {{ isEdit ? 'Cliquez sur une option pour la sélectionner / désélectionner' : 'Options disponibles' }}
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
                        <p class="text-xs text-gray-400 mt-0.5">Type : {{ option.type }}</p>
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

        <!-- Onglet Questions -->
        <div v-if="activeTab === 'questions'" class="p-6">
          <div v-if="coursComplet?.questionnaire && coursComplet.questionnaire.length > 0" class="space-y-4">
            <p v-if="isEdit" class="text-xs text-accent-600 font-medium flex items-center gap-1">
              <Icon icon="mdi:information-outline" class="w-4 h-4" />
              Cliquez sur une réponse pour la sélectionner
            </p>
            <div v-for="question in coursComplet.questionnaire" :key="'q-' + question.id"
              class="bg-gray-50 rounded-xl p-4 border transition-colors"
              :class="isEdit ? 'border-accent-200' : 'border-gray-200'">
              <p class="font-medium text-gray-800 mb-3">{{ question.question || question.enonce || question.texte || '—' }}</p>

              <div class="space-y-2">
                <p class="text-xs text-gray-500 font-semibold">Réponses possibles :</p>
                <div v-for="(answer, idx) in (question.answers || question.choix || [])" :key="idx"
                  class="flex items-center gap-2 p-2 rounded text-sm transition-colors"
                  :class="[
                    reponseQuestionEdit(question.id, answer.id) ? 'bg-tertiary-300 text-primary' : 'text-gray-700',
                    isEdit ? 'cursor-pointer hover:bg-accent-50 hover:border hover:border-accent-600' : ''
                  ]"
                  @click="isEdit && selectionnerReponse(question.id, answer.id)"
                >
                  <Icon
                    :icon="reponseQuestionEdit(question.id, answer.id) ? 'mdi:radiobox-marked' : 'mdi:radiobox-blank'"
                    class="w-4 h-4 flex-shrink-0"
                    :class="reponseQuestionEdit(question.id, answer.id) ? 'text-accent-600' : 'text-gray-300'"
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

      <!-- Barre d'actions sticky en bas -->
      <div class="absolute bottom-0 left-0 right-0 border-t px-6 py-3 flex items-center justify-between transition-colors z-10"
        :class="isEdit ? 'bg-amber-50 border-gray-300' : 'bg-white border-gray-100'">

        <div class="flex items-center gap-2">
          <Icon v-if="isEdit" icon="mdi:pencil-circle" class="w-5 h-5 text-accent" />
          <span v-if="isEdit" class="text-sm font-medium text-amber-700">Mode modification actif</span>
        </div>

        <div class="flex gap-3">
          <button
            class="flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors"
            @click="ouvrirChangementCourse"
          >
            <Icon icon="mdi:swap-horizontal" class="w-4 h-4" />
            Changer de course
          </button>

          <template v-if="!isEdit">
            <button
              class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors"
              :style="{ backgroundColor: inscription.course.evenement.couleur_primaire, color: inscription.course.evenement.couleur_secondaire }"
              @click="activerEdition"
            >
              <Icon icon="mdi:pencil" class="w-4 h-4" />
              Modifier l'inscription
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
 * @fileoverview Composant PopupInscriptionDetailOrganisateur.
 * @description Modale de consultation et d'édition complète d'une inscription côté organisateur.
 * @remarks Couvre la mise à jour des champs d'inscription, des options, des réponses et des documents.
 */
import { Icon } from '@iconify/vue';
import PopupChangementCourseOrganisateur      from './PopupChangementCourseOrganisateur.vue';
import PopupConfirmation                       from './PopupConfirmation.vue';
import courseOrganisateurService              from '../services/courseOrganisateurService';
import documentService                        from '../services/documentService';
import inscriptionService                     from '../services/inscriptionService';
import choixOptionOrganisateurService         from '../services/choixOptionOrganisateurService';
import reponseQuestionOrganisateurService     from '../services/reponseQuestionOrganisateurService';

export default {
  name: 'PopupInscriptionDetailOrganisateur',
  components: { Icon, PopupChangementCourseOrganisateur, PopupConfirmation },
  emits: ['close', 'ajouter-panier', 'modifier-inscription'],
  props: {
    inscription: { type: Object, required: true },
    participants: { type: Array, default: () => [] },
    inline:       { type: Boolean, default: false },
  },
  data() {
    return {
      activeTab: 'general',
      showChangementCourse: false,
      coursComplet: null,
      glisserDocument: false,
      chargementDocument: false,
      isEdit: false,
      inscriptionEdit: null,
      documentASupprimer: null,
      tabs: [
        { key: 'general',   label: 'Général',   icon: 'mdi:information-outline' },
        { key: 'options',   label: 'Options',   icon: 'mdi:cog-outline' },
        { key: 'questions', label: 'Questions', icon: 'mdi:comment-question-outline' },
      ],
    };
  },
  methods: {
    /**
     * Formate une date ISO en JJ.MM.AAAA.
     * @param {string} dateStr
     * @returns {string}
     */
    formatDate(dateStr) {
      if (!dateStr) return '—';
      const [y, m, d] = dateStr.split('-');
      return `${d}.${m}.${y}`;
    },

    /**
     * Charge la course complète liée à l'inscription pour afficher options et questionnaire.
     * @returns {Promise<void>}
     */
    async chargerCourseComplete() {
      try {
        const response = await courseOrganisateurService.getCourse(this.inscription.id_course);
        this.coursComplet = response.data;
      } catch (e) {
        console.error('Erreur lors du chargement de la course :', e);
      }
    },

    /**
     * Active le mode édition avec une copie locale des données modifiables.
     * @returns {void}
     */
    activerEdition() {
      this.isEdit = true;
      this.inscriptionEdit = {
        tarif:               this.inscription.tarif,
        status_paiement:     this.inscription.status_paiement,
        montant_rabais:      this.inscription.montant_rabais,
        code_participant:    this.inscription.code_participant,
        date_paiement:       this.inscription.date_paiement,
        avertissement_valide: this.inscription.avertissement_valide,
        choix_options:       JSON.parse(JSON.stringify(this.inscription.choix_options      ?? [])),
        reponses_questions:  JSON.parse(JSON.stringify(this.inscription.reponses_questions ?? [])),
      };
    },

    /**
     * Annule l'édition locale et restaure l'affichage en mode consultation.
     * @returns {void}
     */
    annulerEdition() {
      this.isEdit          = false;
      this.inscriptionEdit = null;
    },

    /**
     * Sauvegarde l'édition de l'inscription, des choix d'options et des réponses.
     * @returns {Promise<void>}
     */
    async sauvegarderEdition() {
      try {
        await inscriptionService.updateInscriptionAdmin(this.inscription.id, {
          tarif:               this.inscriptionEdit.tarif,
          status_paiement:     this.inscriptionEdit.status_paiement,
          montant_rabais:      this.inscriptionEdit.montant_rabais,
          code_participant:    this.inscriptionEdit.code_participant,
          date_paiement:       this.inscriptionEdit.date_paiement,
          avertissement_valide: this.inscriptionEdit.avertissement_valide,
        });

        const choixInitiaux = this.inscription.choix_options ?? [];
        const choixEdites = this.inscriptionEdit.choix_options ?? [];
        const mapInitiaux = new Map(choixInitiaux.map((c) => [c.id_option, c]));
        const mapEdites = new Map(choixEdites.map((c) => [c.id_option, c]));

        for (const choix of choixEdites) {
          const existant = mapInitiaux.get(choix.id_option);

          if (!existant) {
            await choixOptionOrganisateurService.saveChoix({
              choix: [
                {
                  id_inscription: this.inscription.id,
                  id_option: choix.id_option,
                  quantite: choix.quantite,
                },
              ],
            });
            continue;
          }

          if ((existant.quantite ?? 1) !== (choix.quantite ?? 1)) {
            await choixOptionOrganisateurService.modifyChoix(
              this.inscription.id,
              choix.id_option,
              { quantite: choix.quantite }
            );
          }
        }

        for (const choix of choixInitiaux) {
          if (!mapEdites.has(choix.id_option)) {
            await choixOptionOrganisateurService.deleteChoix(this.inscription.id, choix.id_option);
          }
        }

        if (this.inscriptionEdit.reponses_questions.length > 0) {
          await reponseQuestionOrganisateurService.saveReponses({
            reponses: this.inscriptionEdit.reponses_questions.map(r => ({
              id_inscription:    this.inscription.id,
              id_question:       r.id_question,
              id_option_choisie: r.id_option_choisie,
            })),
          });
        }

        Object.assign(this.inscription, {
          tarif: this.inscriptionEdit.tarif,
          status_paiement: this.inscriptionEdit.status_paiement,
          montant_rabais: this.inscriptionEdit.montant_rabais,
          code_participant: this.inscriptionEdit.code_participant,
          date_paiement: this.inscriptionEdit.date_paiement,
          avertissement_valide: this.inscriptionEdit.avertissement_valide,
          choix_options: JSON.parse(JSON.stringify(this.inscriptionEdit.choix_options ?? [])),
          reponses_questions: JSON.parse(JSON.stringify(this.inscriptionEdit.reponses_questions ?? [])),
        });

        this.isEdit = false;
        this.$emit('modifier-inscription', this.inscription);
      } catch (e) {
        console.error('Erreur sauvegarde :', e);
      }
    },

    /**
     * Retourne le choix existant pour une option donnée.
     * @param {number} idOption
     * @returns {object|null}
     */
    optionSelectionnee(idOption) {
      return (this.inscription.choix_options ?? []).find(c => c.id_option === idOption) || null;
    },

    /**
     * Retourne le choix d'option à afficher selon le mode courant (édition/lecture).
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
     * Retourne la quantité associée à une option.
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
     * Active ou retire une option dans la sélection en mode édition.
     * @param {object} option
     * @returns {void}
     */
    toggleOption(option) {
      const idx = this.inscriptionEdit.choix_options.findIndex(c => c.id_option === option.id);
      if (idx > -1) {
        this.inscriptionEdit.choix_options.splice(idx, 1);
      } else {
        this.inscriptionEdit.choix_options.push({
          id_option:    option.id,
          id_inscription: this.inscription.id,
          quantite:     option.type === 'Cochable' ? 1 : 1,
        });
      }
    },

    /**
     * Met à jour la quantité d'une option sélectionnée.
     * @param {number} idOption
     * @param {string|number} valeur
     * @returns {void}
     */
    mettreAJourQuantite(idOption, valeur) {
      const choix = this.inscriptionEdit.choix_options.find(c => c.id_option === idOption);
      if (choix) choix.quantite = parseInt(valeur) || 0;
    },

    /**
     * Retourne l'identifiant de réponse choisi pour une question en mode lecture.
     * @param {number} idQuestion
     * @returns {number|null}
     */
    reponseQuestion(idQuestion) {
      const reponse = (this.inscription.reponses_questions ?? []).find(r => r.id_question === idQuestion);
      return reponse?.id_option_choisie || null;
    },

    /**
     * Vérifie si une option est sélectionnée pour une question donnée.
     * @param {number} idQuestion
     * @param {number} idOption
     * @returns {boolean}
     */
    reponseQuestionEdit(idQuestion, idOption) {
      if (this.isEdit && this.inscriptionEdit) {
        const reponse = this.inscriptionEdit.reponses_questions.find(r => r.id_question === idQuestion);
        return reponse?.id_option_choisie === idOption;
      }
      return this.reponseQuestion(idQuestion) === idOption;
    },

    /**
     * Affecte une réponse à une question en mode édition.
     * @param {number} idQuestion
     * @param {number} idOption
     * @returns {void}
     */
    selectionnerReponse(idQuestion, idOption) {
      const idx = this.inscriptionEdit.reponses_questions.findIndex(r => r.id_question === idQuestion);
      if (idx > -1) {
        this.inscriptionEdit.reponses_questions[idx].id_option_choisie = idOption;
      } else {
        this.inscriptionEdit.reponses_questions.push({
          id_inscription:    this.inscription.id,
          id_question:       idQuestion,
          id_option_choisie: idOption,
        });
      }
    },

    /**
     * Télécharge un document sur le poste utilisateur.
     * @param {object} doc
     * @returns {Promise<void>}
     */
    async telechargerDocument(doc) {
      try {
        const response = await documentService.downloadDocument(doc.id);
        const url  = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href  = url;
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
     * Ouvre un document dans un nouvel onglet navigateur.
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
     * Prépare la suppression d'un document en ouvrant la confirmation.
     * @param {number} idDoc
     * @returns {Promise<void>}
     */
    async supprimerDocument(idDoc) {
      this.documentASupprimer = idDoc;
    },

    /**
     * Confirme la suppression d'un document et met à jour la liste locale.
     * @returns {Promise<void>}
     */
    async confirmerSuppressionDocument() {
      if (!this.documentASupprimer) return;
      try {
        await documentService.deleteDocumentAdmin(this.documentASupprimer);
        const idx = (this.inscription.documents_fournis ?? []).findIndex(d => d.id === this.documentASupprimer);
        if (idx > -1) this.inscription.documents_fournis.splice(idx, 1);
        this.documentASupprimer = null;
      } catch (e) {
        console.error('Erreur suppression :', e);
      }
    },

    /**
     * Réagit à la sélection d'un fichier via input natif.
     * @param {Event} event
     * @returns {Promise<void>}
     */
    async selectionnerDocument(event) {
      const fichier = event.target.files[0];
      if (fichier) await this.uploadDocument(fichier);
      event.target.value = '';
    },

    /**
     * Gère le dépôt d'un document par glisser-déposer.
     * @param {DragEvent} event
     * @returns {void}
     */
    deposerDocument(event) {
      this.glisserDocument = false;
      const fichier = event.dataTransfer.files[0];
      if (fichier) this.uploadDocument(fichier);
    },

    /**
     * Envoie un document au serveur et l'ajoute à la liste locale.
     * @param {File} fichier
     * @returns {Promise<void>}
     */
    async uploadDocument(fichier) {
      try {
        this.chargementDocument = true;
        const formData = new FormData();
        formData.append('file', fichier);
        const response = await documentService.uploadDocumentAdmin(this.inscription.id, formData);
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
     * Ferme la modale de changement et transmet les données au parent.
     * @param {object} data
     * @returns {void}
     */
    onChangementConfirme(data) {
      this.showChangementCourse = false;
      this.$emit('ajouter-panier', data);
    },
  },
  mounted() {
    this.chargerCourseComplete();
  },
};
</script>