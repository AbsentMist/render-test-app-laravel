<template>
    <!-- Popup avertissement -->
    <PopupAvertissementCourse
        v-if="modalAffichage == modals.AVERTISSEMENT && course.avertissement"
        :texte="course.avertissement.contenu"
        @confirmer="modalAffichage = modals.INSCRIPTION"
        @close="$emit('close')"
    />

    <!-- Popup inscription -->
    <div v-if="modalAffichage == modals.INSCRIPTION"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-4 flex flex-col overflow-hidden" style="height: 75vh">

            <!-- Header -->
            <div class="flex items-center justify-between px-6 pt-5 pb-2 border-b border-gray-100 bg-primary-300">
                <div>
                    <span class="px-6 text-subtitle font-medium text-secondary">Inscription</span>
                    <span class="text-subtitle font-medium text-secondary"> &nbsp; {{ course.nom_course }}</span>
                    <div class="h-1 w-24 ml-6 rounded-r-full mb-2" :style="{ backgroundColor: course.evenement?.couleur_secondaire }"></div>                                   
                    <span class="mx-6 px-2 py-0.5 text-base font-medium text-secondary rounded-full" :style="{color: course.evenement.couleur_secondaire, backgroundColor: course.evenement.couleur_primaire, borderColor: course.evenement.couleur_secondaire}">{{ course.evenement.nom }}</span>

                </div>
                <button @click="$emit('close')" class="text-secondary hover:text-gray-600 transition-colors mr-1">
                    <Icon icon="mdi:close" class="w-5 h-5" />
                </button>
            </div>

            <!-- Corps -->
            <div class="flex flex-1 overflow-hidden">

                <!-- Colonne gauche : étapes + contenu -->
                <div class="basis-4/6 flex flex-col overflow-hidden">
                    <!-- Indicateur étapes -->
                    <div class="px-6 pt-5">
                        <IndicateurEtapes
                            :steps="formulaireEtapesLabels"
                            :currentStep="etapesActives.indexOf(etape) + 1"
                        />
                    </div>

                    <!-- Contenu scrollable -->
                    <div class="flex-1 overflow-y-auto px-10 pb-6">

                        <!-- Étape 1 : Type -->
                        <EtapeParametre
                            v-show="etape === formulaireEtape.PARAMETRE"
                            :course="course"
                            v-model="inscription.type"
                        />

                        <!-- Étape 2 : Participant -->
                        <EtapeParticipant
                            v-show="etape === formulaireEtape.PARTICIPANTS"
                            :participants="tousLesParticipants"
                            :chargement="chargementParticipants"
                            :type-selectionne="inscription.type"
                            v-model="inscription.participant"
                            @creer-participant="ajouterParticipantSupplementaire"
                        />

                        <!-- Étape 3 : Options -->
                        <EtapeOptions
                            v-show="etape === formulaireEtape.OPTIONS"
                            :options="course.options"
                            v-model="inscription.options"
                        />

                        <!-- Étape 4 : Document -->
                        <EtapeDocument
                            v-show="etape === formulaireEtape.DOCUMENT"
                            v-model="inscription.documents"
                        />

                        <!-- Étape 5 : Questionnaire -->
                        <EtapeQuestionnaire
                            v-show="etape === formulaireEtape.QUESTIONNAIRE"
                            :questions="course.questionnaire"
                            v-model="inscription.reponses"
                        />

                        <!-- Étape 6 : Panier -->
                        <EtapePanier
                            v-show="etape === formulaireEtape.CONFIRMATION"
                            v-model:codeParticipation="inscription.codeParticipation"
                        />
                    </div>
                </div>

                <!-- Colonne droite : récapitulatif -->
                <div class="basis-2/6 border-l border-gray-100 px-5 py-5 flex flex-col justify-between bg-gray-50/60">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Votre inscription</h3>

                        <!-- Course de base -->
                        <div class="flex justify-between items-start text-sm">
                            <p class="font-medium text-gray-800 flex-1">{{ course.nom_course }}</p>
                            <span class="text-gray-700 ml-2 shrink-0">{{ course.tarif }}.-</span>
                        </div>

                        <!-- Sous-titre : type + catégorie -->
                        <p class="text-xs text-gray-400 mt-1">
                            <template v-if="inscription.type">{{ inscription.type.nom }} · </template>
                            {{ course.categorie }}<template v-if="course.sous_categorie"> · {{ course.sous_categorie }}</template>
                        </p>

                        <!-- Participant -->
                        <div v-if="inscription.participant.length > 0" class="mt-3 flex flex-col gap-1">
                            <div v-for="p in inscription.participant" :key="p.id" class="flex items-center gap-2 text-xs text-gray-500">
                                <Icon icon="mdi:account-outline" class="w-4 h-4 shrink-0" />
                                <span>{{ p.prenom }} {{ p.nom }}</span>
                            </div>
                        </div>

                        <!-- Options sélectionnées -->
                        <div v-if="optionsSelectionnees.length > 0" class="mt-3 border-t border-gray-100 pt-3 flex flex-col gap-1">
                            <div
                                v-for="(opt, i) in optionsSelectionnees"
                                :key="i"
                                class="flex justify-between text-xs text-gray-500"
                            >
                                <span class="flex-1">+ {{ opt.option.nom }}<template v-if="opt.option.type === 'Quantifiable'"> ×{{ opt.quantite }}</template></span>
                                <span class="shrink-0 ml-2">
                                    {{ (opt.option.tarif * (opt.option.type === 'Quantifiable' ? opt.quantite : 1)).toFixed(2) }}.-
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="border-t border-gray-200 pt-3 mt-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-bold text-gray-800">Total</span>
                            <span class="text-sm font-bold text-gray-800">{{ totalInscription.toFixed(2) }}.-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer navigation -->
            <div class="flex justify-between items-center px-6 py-4 border-t border-gray-100">
                <button
                    v-if="etapesActives.indexOf(etape) > 0"
                    @click="etapePrecedente"
                    class="btn-accent-300"
                >
                    Etape précédente
                </button>
                <div v-else></div>

                <button
                    @click="etapeSuivante"
                    :disabled="!peutContinuer"
                    :class="['btn-tertiary', !peutContinuer ? 'opacity-50 cursor-not-allowed' : '']"
                >
                    {{ estDerniereEtape ? 'Ajouter au panier' : 'Etape suivante' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { Icon } from '@iconify/vue';
import IndicateurEtapes from './IndicateurEtapes.vue';
import PopupAvertissementCourse from './PopupAvertissementCourse.vue';
import EtapeParametre from './EtapeParametre.vue';
import EtapeParticipant from './EtapeParticipant.vue';
import EtapeOptions from './EtapeOptions.vue';
import EtapeDocument from './EtapeDocument.vue';
import EtapeQuestionnaire from './EtapeQuestionnaire.vue';
import EtapePanier from './EtapePanier.vue';

const formulaireEtape = {
    PARAMETRE: 1,
    PARTICIPANTS: 2,
    OPTIONS: 3,
    DOCUMENT: 4,
    QUESTIONNAIRE: 5,
    CONFIRMATION: 6,
};

const modals = {
    AVERTISSEMENT: 1,
    INSCRIPTION: 2,
};

export default {
    name: 'PopupInscriptionCourse',
    components: {
        Icon,
        IndicateurEtapes,
        PopupAvertissementCourse,
        EtapeParametre,
        EtapeParticipant,
        EtapeOptions,
        EtapeDocument,
        EtapeQuestionnaire,
        EtapePanier,
    },
    emits: ['close', 'ajouter-panier'],
    props: {
        course: {
            type: Object,
            required: true,
            default: () => ({
                nom_course: 'Nocturne des Evaux',
                tarif: 45,
                is_challenge: true,
                type: 'Relais',
                categorie: 'Challenge étudiant',
                sous_categorie: 'Mixte',
                evenement: { couleur_secondaire: '#7c3aed', nom: 'Nocturne des Evaux' },
                options: [
                    { id: 1, nom: '1 Entrée + 1 pasta bolognaise', tarif: 15, type: 'Quantifiable', quantifiable: { quantite_min: 1, quantite_max: 5 } },
                ],
                avertissement: null,
                questionnaire: null,
                document: false,
            })
        },
        participants: {
            type: Array,
            default: () => [],
        },
        chargementParticipants: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            formulaireEtape,
            modals,
            etape: formulaireEtape.PARAMETRE,
            modalAffichage: null,
            formulaireEtapesLabels: [],
            participantsSupplementaires: [],
            inscription: {
                type: null,
                participant: [],
                options: {},
                documents: [],
                reponses: {},
                codeParticipation: '',
            },
        };
    },
    computed: {
        tousLesParticipants() {
            return [...this.participants, ...this.participantsSupplementaires];
        },
        etapesActives() {
            const etapes = [formulaireEtape.PARAMETRE, formulaireEtape.PARTICIPANTS];
            const labels = ['Participant', 'Participant'];

            // Paramètre = sélection type (challenge / relais)
            // Toujours présent
            const listeEtapes = [formulaireEtape.PARAMETRE];
            const listeLabels = ['Type'];

            // Participants
            listeEtapes.push(formulaireEtape.PARTICIPANTS);
            listeLabels.push('Participant');

            // Options
            if (this.course.options && this.course.options.length > 0) {
                listeEtapes.push(formulaireEtape.OPTIONS);
                listeLabels.push('Options');
            }

            // Document
            if (this.course.document) {
                listeEtapes.push(formulaireEtape.DOCUMENT);
                listeLabels.push('Documents');
            }

            // Questionnaire
            if (this.course.questionnaire && this.course.questionnaire.length > 0) {
                listeEtapes.push(formulaireEtape.QUESTIONNAIRE);
                listeLabels.push('Questionnaire');
            }

            // Panier (toujours en dernier)
            listeEtapes.push(formulaireEtape.CONFIRMATION);
            listeLabels.push('Panier');

            this.formulaireEtapesLabels = listeLabels;
            return listeEtapes;
        },

        estDerniereEtape() {
            return this.etapesActives.indexOf(this.etape) === this.etapesActives.length - 1;
        },

        peutContinuer() {
            if (this.etape === formulaireEtape.PARAMETRE) return !!this.inscription.type;
            if (this.etape === formulaireEtape.PARTICIPANTS) {
                if (this.inscription.participant.length === 0) return false;
                if (this.inscription.type?.id === 'relais') return this.inscription.participant.length >= 2;
                return true;
            }
            return true;
        },

        optionsSelectionnees() {
            return Object.values(this.inscription.options || {});
        },

        totalInscription() {
            const base = parseFloat(this.course.tarif) || 0;
            const extras = this.optionsSelectionnees.reduce((acc, { option, quantite }) => {
                return acc + option.tarif * (option.type === 'Quantifiable' ? quantite : 1);
            }, 0);
            return base + extras;
        },
    },
    methods: {
        etapeSuivante() {
            if (!this.peutContinuer) return;
            if (this.estDerniereEtape) {
                this.$emit('ajouter-panier', { ...this.inscription });
                return;
            }
            const idx = this.etapesActives.indexOf(this.etape);
            this.etape = this.etapesActives[idx + 1];
        },
        etapePrecedente() {
            const idx = this.etapesActives.indexOf(this.etape);
            if (idx > 0) this.etape = this.etapesActives[idx - 1];
        },
        creerParticipant(data) {
            this.$emit('creer-participant', data);
        },
        ajouterParticipantSupplementaire(data) {
            // Ajoute dans la liste locale pour qu'il reste visible si on revient en arrière
            if (!this.participantsSupplementaires.some(p => p.id === data.id)) {
                this.participantsSupplementaires.push(data);
            }
        },
    },
    mounted() {
        this.etape = this.etapesActives[0];
        this.modalAffichage = this.course.avertissement ? modals.AVERTISSEMENT : modals.INSCRIPTION;
    },
};
</script>