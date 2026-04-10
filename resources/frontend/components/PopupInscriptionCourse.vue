<template>
    <PopupAvertissementCourse
        v-if="modalAffichage == modals.AVERTISSEMENT && course.avertissement"
        :texte="course.avertissement.contenu"
        @confirmer="modalAffichage = modals.INSCRIPTION"
        @close="$emit('close')"
    />

    <div
        v-if="modalAffichage == modals.INSCRIPTION"
        :class="inline ? 'flex flex-col h-full' : 'fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm'"
    >
        <div
            :class="inline ? 'flex flex-col h-full w-full' : 'relative bg-white rounded-2xl shadow-2xl w-full max-w-7xl mx-4 flex flex-col overflow-hidden'"
            :style="inline ? '' : 'height: 90vh'"
        >
            <div v-if="!inline" class="flex items-center justify-between px-6 pt-5 pb-2 border-b border-gray-100 bg-primary-300">
                <div>
                    <span class="px-6 text-subtitle font-medium text-secondary">Inscription</span>
                    <span class="text-subtitle font-medium text-secondary"> &nbsp; {{ course.nom_course }}</span>
                    <div v-if="course.evenement" class="h-1 w-24 ml-6 rounded-r-full mb-2" :style="{ backgroundColor: course.evenement.couleur_secondaire }"></div>
                    <span v-if="course.evenement" class="mx-6 px-2 py-0.5 text-base font-medium text-secondary rounded-full"
                        :style="{color: course.evenement.couleur_secondaire, backgroundColor: course.evenement.couleur_primaire, borderColor: course.evenement.couleur_secondaire}">
                        {{ course.evenement.nom }}
                    </span>
                </div>
                <button @click="$emit('close')" class="text-secondary hover:text-gray-600 transition-colors mr-1">
                    <Icon icon="mdi:close" class="w-5 h-5" />
                </button>
            </div>

            <div class="flex flex-1 overflow-hidden">

                <div class="basis-4/6 flex flex-col overflow-hidden">
                    <div class="px-6 pt-5">
                        <IndicateurEtapes :steps="formulaireEtapesLabels" :currentStep="etapesActives.indexOf(etape) + 1" />
                    </div>

                    <div class="flex-1 overflow-y-auto px-10 pb-6">

                        <EtapeParametre
                            v-show="etape === formulaireEtape.PARAMETRE"
                            :course="course"
                            v-model="inscription.type"
                        />

                        <EtapeParticipant
                            v-show="etape === formulaireEtape.PARTICIPANTS"
                            :participants="tousLesParticipants"
                            :chargement="chargementParticipants"
                            :type-selectionne="inscription.type"
                            :course-id="course.id"
                            v-model="inscription.participant"
                            v-model:groupeValue="inscription.groupeEphemere"
                            @creer-participant="ajouterParticipantSupplementaire"
                            @update:nomEquipe="inscription.nom_equipe = $event"
                        />

                        <EtapeOptions
                            v-show="etape === formulaireEtape.OPTIONS"
                            :options="course.options"
                            v-model="inscription.options"
                        />

                        <EtapeDocument
                            v-show="etape === formulaireEtape.DOCUMENT"
                            :description_document="course.document_description"
                            v-model="inscription.documents"
                        />

                        <EtapeQuestionnaire
                            v-show="etape === formulaireEtape.QUESTIONNAIRE"
                            :questions="course.questionnaire"
                            v-model="inscription.reponses"
                        />

                        <EtapePanier
                            v-show="etape === formulaireEtape.CONFIRMATION"
                            v-model:codeParticipation="inscription.codeParticipation"
                        />
                        
                        <div v-show="etape === formulaireEtape.CONFIRMATION" class="mt-4">
                            <div v-if="entrepriseValidee" class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">
                                <Icon icon="mdi:check-circle-outline" class="w-5 h-5 shrink-0" />
                                <span>Code appliqué ! La course vous est offerte par <strong>{{ entrepriseValidee.nom }}</strong>.</span>
                            </div>
                            
                            <div v-if="erreurCode && !entrepriseValidee" class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3">
                                <Icon icon="mdi:alert-circle-outline" class="w-5 h-5 shrink-0" />
                                <span>{{ erreurCode }}</span>
                            </div>
                        </div>

                    </div> 
                </div> 

                <div class="basis-2/6 border-l border-gray-100 px-5 py-5 flex flex-col justify-between bg-gray-50/60">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Votre inscription</h3>

                        <div class="flex justify-between items-start text-sm">
                            <p class="font-medium text-gray-800 flex-1">{{ course.nom_course }}</p>
                            <span class="text-gray-700 ml-2 shrink-0">{{ course.tarif }}.-</span>
                        </div>

                        <p class="text-xs text-gray-400 mt-1">
                            <template v-if="inscription.type">{{ inscription.type.nom }} · </template>
                            {{ course.categorie }}<template v-if="course.sous_categorie"> · {{ course.sous_categorie }}</template>
                        </p>

                        <div v-if="inscription.groupeEphemere?.nom" class="mt-3">
                            <div class="flex items-center gap-2 text-xs text-gray-600 font-semibold">
                                <Icon icon="mdi:account-group" class="w-4 h-4 text-tertiary-900 shrink-0" />
                                {{ inscription.groupeEphemere.nom }}
                            </div>
                            <div v-for="m in inscription.groupeEphemere.participants" :key="m.id"
                                class="flex items-center gap-2 text-xs text-gray-400 mt-0.5 ml-6">
                                <Icon icon="mdi:account-outline" class="w-3 h-3 shrink-0" />
                                {{ m.prenom }} {{ m.nom }}
                            </div>
                        </div>

                        <div v-if="inscription.participant.length > 0 && inscription.type?.id !== 'challenge'" class="mt-3 flex flex-col gap-1">
                            <div v-for="p in inscription.participant" :key="p.id" class="flex items-center gap-2 text-xs text-gray-500">
                                <Icon icon="mdi:account-outline" class="w-4 h-4 shrink-0" />
                                <span>{{ p.prenom }} {{ p.nom }}</span>
                            </div>
                        </div>

                        <div v-if="optionsSelectionnees.length > 0" class="mt-3 border-t border-gray-100 pt-3 flex flex-col gap-1">
                            <div v-for="(opt, i) in optionsSelectionnees" :key="i" class="flex justify-between text-xs text-gray-500">
                                <span class="flex-1">+ {{ opt.option.nom }}<template v-if="opt.option.type === 'Quantifiable'"> ×{{ opt.quantite }}</template></span>
                                <span class="shrink-0 ml-2">{{ (opt.option.tarif * (opt.option.type === 'Quantifiable' ? opt.quantite : 1)).toFixed(2) }}.-</span>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-3 mt-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-bold text-gray-800">Total</span>
                            <span class="text-sm font-bold text-gray-800">{{ totalInscription.toFixed(2) }}.-</span>
                        </div>
                    </div>
                </div> 
            </div> 
            
            <div v-if="erreurGroupe" class="mx-6 mb-2 flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3">
                <Icon icon="mdi:alert-circle-outline" class="w-5 h-5 shrink-0" />
                <span>{{ erreurGroupe }}</span>
                <button type="button" @click="erreurGroupe = null" class="ml-auto text-red-400 hover:text-red-600">
                    <Icon icon="mdi:close" class="w-4 h-4" />
                </button>
            </div>
            
            <div class="flex justify-between items-center px-6 py-4 border-t border-gray-100">
                <button v-if="etapesActives.indexOf(etape) > 0" @click="etapePrecedente" class="btn-accent-300">
                    Etape précédente
                </button>
                <div v-else></div>

                <button @click="etapeSuivante" :disabled="!peutContinuer || creationGroupe || codeBloquant"
                    :class="['btn-tertiary', (!peutContinuer || creationGroupe || codeBloquant) ? 'opacity-50 cursor-not-allowed' : '']">
                    <span v-if="creationGroupe" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Création du groupe...
                    </span>
                    <span v-else>{{ estDerniereEtape ? 'Ajouter au panier' : 'Etape suivante' }}</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
/**
 * @fileoverview Composant PopupInscriptionCourse.
 * @description Modale de parcours d'inscription à une course, avec navigation par étapes et validation finale.
 * @remarks Ce composant orchestre l'ensemble du tunnel d'inscription: choix du type,
 * sélection des participants, options, documents, questionnaire, puis émission vers le panier.
 */
import { Icon } from '@iconify/vue';
import IndicateurEtapes from './IndicateurEtapes.vue';
import PopupAvertissementCourse from './PopupAvertissementCourse.vue';
import EtapeParametre from './EtapeParametre.vue';
import EtapeParticipant from './EtapeParticipant.vue';
import EtapeOptions from './EtapeOptions.vue';
import EtapeDocument from './EtapeDocument.vue';
import EtapeQuestionnaire from './EtapeQuestionnaire.vue';
import EtapePanier from './EtapePanier.vue';
import groupeService from '../services/groupeService';

const formulaireEtape = {
    PARAMETRE: 1, PARTICIPANTS: 2, OPTIONS: 3,
    DOCUMENT: 4, QUESTIONNAIRE: 5, CONFIRMATION: 6,
};
const modals = { AVERTISSEMENT: 1, INSCRIPTION: 2 };

export default {
    name: 'PopupInscriptionCourse',
    components: { Icon, IndicateurEtapes, PopupAvertissementCourse, EtapeParametre, EtapeParticipant, EtapeOptions, EtapeDocument, EtapeQuestionnaire, EtapePanier },
    emits: ['close', 'ajouter-panier'],
    props: {
        course: { type: Object, required: true },
        participants: { type: Array, default: () => [] },
        chargementParticipants: { type: Boolean, default: false },
        inline: { type: Boolean, default: false },
    },
    /**
     * Initialise l'état complet du tunnel d'inscription.
     * @returns {Object} Données locales de progression, sélection et validation.
     */
    data() {
        return {
            formulaireEtape, modals,
            etape: formulaireEtape.PARAMETRE,
            modalAffichage: null,
            formulaireEtapesLabels: [],
            participantsSupplementaires: [],
            creationGroupe: false, 
            inscription: {
                type: null,
                participant: [],
                groupeEphemere: null,
                options: {},
                documents: [],
                reponses: {},
                codeParticipation: '',
                nom_equipe: '',
            },
            erreurGroupe: null,
            entrepriseValidee: null,
            erreurCode: null,
        };
    },
    watch: {
        /**
         * Réinitialise l'état de validation entreprise si le code de participation change.
         * @param {string} newVal Nouvelle valeur saisie.
         * @returns {void}
         */
        'inscription.codeParticipation'(newVal) {
            this.entrepriseValidee = null;
            this.erreurCode = null;
        }
    },
    computed: {
        /**
         * Fusionne les participants reçus avec ceux ajoutés temporairement au groupe éphémère.
         * @returns {Array<object>}
         */
        tousLesParticipants() {
            const ids = new Set(this.participants.map(p => p.id));
            const extras = (this.inscription.groupeEphemere?.participants ?? []).filter(p => !ids.has(p.id));
            return [...this.participants, ...extras];
        },
        /**
         * Indique si la course demande un mode groupe.
         * @returns {boolean}
         */
        estCourseGroupe() {
            return this.course.type === 'Groupe';
        },
        /**
         * Détermine la liste active d'étapes selon la configuration de la course.
         * @returns {Array<number>}
         */
        etapesActives() {
            const listeEtapes = [formulaireEtape.PARAMETRE];
            const listeLabels = ['Type'];
            listeEtapes.push(formulaireEtape.PARTICIPANTS);
            listeLabels.push(this.estCourseGroupe ? 'Groupe' : 'Participant');
            if (this.course.options?.length > 0) { listeEtapes.push(formulaireEtape.OPTIONS); listeLabels.push('Options'); }
            if (this.course.document)             { listeEtapes.push(formulaireEtape.DOCUMENT); listeLabels.push('Documents'); }
            if (this.course.questionnaire?.length > 0) { listeEtapes.push(formulaireEtape.QUESTIONNAIRE); listeLabels.push('Questionnaire'); }
            listeEtapes.push(formulaireEtape.CONFIRMATION);
            listeLabels.push('Panier');
            this.formulaireEtapesLabels = listeLabels;
            return listeEtapes;
        },
        /**
         * Indique si l'utilisateur est à la dernière étape du parcours.
         * @returns {boolean}
         */
        estDerniereEtape() {
            return this.etapesActives.indexOf(this.etape) === this.etapesActives.length - 1;
        },
        /**
         * Vérifie si la progression peut continuer depuis l'étape courante.
         * @returns {boolean}
         */
        peutContinuer() {
            if (this.etape === formulaireEtape.PARAMETRE) {
                return !!this.inscription.type;
            }
            if (this.etape === formulaireEtape.PARTICIPANTS) {
                if (this.inscription.type?.id === 'challenge') {
                    const orgOk = !!(this.inscription.groupeEphemere?.nom?.trim());
                    const participantOk = this.inscription.participant.length > 0;
                    return orgOk && participantOk;
                }
                if (this.inscription.type?.id === 'groupe' || this.inscription.type?.id === 'relais') {
                    const nom = !!(this.inscription.groupeEphemere?.nom?.trim());
                    const membres = this.inscription.groupeEphemere?.participants?.length ?? 0;
                    if (this.inscription.type?.id === 'relais') {
                        return nom && membres >= 2 && membres <= 2;
                    }
                    const match = this.inscription.type?.nom?.match(/\((\d+)-(\d+)\)/);
                    if (match) {
                        const min = parseInt(match[1]);
                        const max = parseInt(match[2]);
                        return nom && membres >= min && membres <= max;
                    }
                    return nom && membres >= 2;
                }
                return this.inscription.participant.length > 0;
            }
            return true;
        },
        /**
         * Renvoie les options actuellement sélectionnées.
         * @returns {Array<object>}
         */
        optionsSelectionnees() {
            return Object.values(this.inscription.options || {});
        },
        /**
         * Calcule le montant total de l'inscription en fonction de la sélection.
         * @returns {number}
         */
        totalInscription() {
            const base = this.entrepriseValidee ? 0 : (parseFloat(this.course.tarif) || 0);
            const extras = this.optionsSelectionnees.reduce((acc, { option, quantite }) =>
                acc + option.tarif * (option.type === 'Quantifiable' ? quantite : 1), 0);
            return base + extras;
        },
        /**
         * Prépare les choix d'options au format attendu par le panier.
         * @returns {Array<object>}
         */
        choixOptionsPourPanier() {
            return this.optionsSelectionnees.map(({ option, quantite }) => ({
                id_option: option.id,
                quantite:  option.type === 'Quantifiable' ? quantite
                        : option.type === 'Cochable'     ? 1
                        : 0,
            }));
        },
                    /**
                     * Prépare les réponses aux questions au format panier.
                     * @returns {Array<object>}
                     */
        reponsesPourPanier() {
            return Object.entries(this.inscription.reponses || {}).map(([id_question, valeur]) => ({
                id_question:       parseInt(id_question),
                id_option_choisie: valeur?.reponse?.id ?? null,
            }));
        },
        /**
         * Indique si le code entreprise bloque encore la progression.
         * @returns {boolean}
         */
        codeBloquant() {
            const code = this.inscription.codeParticipation?.trim();
            if (!code) return false; 
            if (this.entrepriseValidee) return false; 
            return true; 
        },
    },
    methods: {
        /**
         * Revient à l'étape précédente si elle existe.
         * @returns {void}
         */
        etapePrecedente() {
            const idx = this.etapesActives.indexOf(this.etape);
            if (idx > 0) this.etape = this.etapesActives[idx - 1];
        },

        /**
         * Passe à l'étape suivante ou finalise l'inscription selon le contexte.
         * Crée les groupes nécessaires et émet les données pour le panier.
         * @returns {Promise<void>}
         */
        async etapeSuivante() {
            this.erreurGroupe = null;
            if (!this.peutContinuer || this.creationGroupe) return;

            if (this.estDerniereEtape) {
                let id_groupe = null;

                if ((this.inscription.type?.id === 'groupe' || this.inscription.type?.id === 'relais') 
                && this.inscription.groupeEphemere) {
                    this.creationGroupe = true;
                    try {
                        const groupeResp = await groupeService.createGroupe({
                            nom:       this.inscription.groupeEphemere.nom,
                            type:      'Groupe',
                            id_course: this.course.id,
                        });
                        id_groupe = groupeResp.data.id;

                        const membresBD = this.inscription.groupeEphemere.participants.filter(
                            p => typeof p.id === 'number' && p.id < 1e12 
                        );
                        for (const membre of membresBD) {
                            try {
                                await groupeService.addParticipant(id_groupe, membre.id);
                            } 
                            catch (e) {
                                if (e.response?.status === 409) {
                                    console.info('Membre déjà dans le groupe, ignoré.');
                                } else {
                                    throw e;
                                }
                            }
                        }
                    } catch (e) {
                        console.error('Erreur lors de la création du groupe :', e);
                        if (e.response?.status === 500 && e.response?.data?.message?.includes('UNIQUE')) {
                            this.erreurGroupe = 'Un groupe avec ce nom existe déjà pour cette course. Choisissez un autre nom.';
                        } else {
                            this.erreurGroupe = 'Impossible de créer le groupe. Veuillez réessayer.';
                        }
                        this.creationGroupe = false;
                        return;
                    } finally {
                        this.creationGroupe = false;
                    }
                }
                
                if (this.inscription.type?.id === 'challenge' && this.inscription.groupeEphemere) {
                    this.creationGroupe = true;
                    try {
                        const groupeResp = await groupeService.createGroupe({
                            nom:       this.inscription.groupeEphemere.nom,
                            type:      this.inscription.groupeEphemere.type_groupe ?? 'Groupe',
                            id_course: this.course.id,
                        });
                        id_groupe = groupeResp.data.id;
                    } catch (e) {
                        if (e.response?.status === 500 && e.response?.data?.message?.includes('UNIQUE')) {
                            console.info('Groupe challenge déjà existant, on y rattache le participant.');
                        } else {
                            this.erreurGroupe = 'Impossible de créer le groupe challenge. Veuillez réessayer.';
                            this.creationGroupe = false;
                            return;
                        }
                    } finally {
                        this.creationGroupe = false;
                    }
                }

                let id_groupe_final = id_groupe;
                if (this.entrepriseValidee) {
                    id_groupe_final = this.entrepriseValidee.id; 
                }

                this.$emit('ajouter-panier', {
                    ...this.inscription,
                    id_groupe:          id_groupe_final,
                    nom_equipe:         this.inscription.groupeEphemere?.nom ?? null,
                    tarif:              this.totalInscription,
                    choix_options:      this.choixOptionsPourPanier,
                    reponses_questions: this.reponsesPourPanier,
                });
                return;
            }

            const idx = this.etapesActives.indexOf(this.etape);
            this.etape = this.etapesActives[idx + 1];
        },

        /**
         * Ajoute un participant supplémentaire s'il n'existe pas déjà dans la liste locale.
         * @param {object} data
         * @returns {void}
         */
        ajouterParticipantSupplementaire(data) {
            if (!this.participantsSupplementaires.some(p => p.id === data.id)) {
                this.participantsSupplementaires.push(data);
            }
        },
        
        /**
         * Vérifie un code entreprise et conserve le groupe validé si le code est correct.
         * @returns {Promise<void>}
         */
        async verifierCodeEntreprise() {
            const codeSaisi = this.inscription.codeParticipation?.trim();
            if (!codeSaisi) {
                this.entrepriseValidee = null;
                this.erreurCode = null;
                return;
            }
            try {
                const res = await groupeService.verifierCode(codeSaisi);
                this.entrepriseValidee = res.data.groupe;
                this.erreurCode = null;
            } catch (e) {
                this.entrepriseValidee = null;
                this.erreurCode = e.response?.data?.message || 'Code invalide.';
            }
        },
    },
    /**
     * Positionne l'étape initiale active et choisit l'écran d'ouverture selon l'avertissement course.
     * @returns {void}
     */
    mounted() {
        this.etape = this.etapesActives[0];
        this.modalAffichage = this.course.avertissement ? modals.AVERTISSEMENT : modals.INSCRIPTION;
    },
};
</script>