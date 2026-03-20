<template>
    <!-- Popup avertissement -->
    <PopupAvertissementCourse
        v-if="modalAffichage == modals.AVERTISSEMENT && course.avertissement"
        :texte="course.avertissement.contenu"
        @confirmer="modalAffichage = modals.INSCRIPTION"
        @close="$emit('close')"
    />

    <!-- Popup inscription -->
    <div
        v-if="modalAffichage == modals.INSCRIPTION"
        :class="inline ? 'flex flex-col h-full' : 'fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm'"
    >
        <div
            :class="inline ? 'flex flex-col h-full w-full' : 'relative bg-white rounded-2xl shadow-2xl w-full max-w-7xl mx-4 flex flex-col overflow-hidden'"
            :style="inline ? '' : 'height: 90vh'"
        >
            <!-- Header -->
            <div v-if="!inline" class="flex items-center justify-between px-6 pt-5 pb-2 border-b border-gray-100 bg-primary-300">
                <div>
                    <span class="px-6 text-subtitle font-medium text-secondary">Inscription</span>
                    <span class="text-subtitle font-medium text-secondary"> &nbsp; {{ course.nom_course }}</span>
                    <div class="h-1 w-24 ml-6 rounded-r-full mb-2" :style="{ backgroundColor: course.evenement?.couleur_secondaire }"></div>
                    <span class="mx-6 px-2 py-0.5 text-base font-medium text-secondary rounded-full"
                        :style="{color: course.evenement.couleur_secondaire, backgroundColor: course.evenement.couleur_primaire, borderColor: course.evenement.couleur_secondaire}">
                        {{ course.evenement.nom }}
                    </span>
                </div>
                <button @click="$emit('close')" class="text-secondary hover:text-gray-600 transition-colors mr-1">
                    <Icon icon="mdi:close" class="w-5 h-5" />
                </button>
            </div>

            <!-- Corps -->
            <div class="flex flex-1 overflow-hidden">

                <!-- Colonne gauche -->
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

                        <!-- Étape Participant / Groupe (tâche 2.3 & 3.1) -->
                        <EtapeParticipant
                            v-show="etape === formulaireEtape.PARTICIPANTS"
                            :participants="tousLesParticipants"
                            :chargement="chargementParticipants"
                            :type-selectionne="inscription.type"
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
                    </div>
                </div>

                <!-- Colonne droite : récapitulatif -->
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

                        <!-- Récap groupe éphémère -->
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

                        <!-- Récap participants (individuel / relais) -->
                        <div v-if="inscription.participant.length > 0" class="mt-3 flex flex-col gap-1">
                            <div v-for="p in inscription.participant" :key="p.id" class="flex items-center gap-2 text-xs text-gray-500">
                                <Icon icon="mdi:account-outline" class="w-4 h-4 shrink-0" />
                                <span>{{ p.prenom }} {{ p.nom }}</span>
                            </div>
                        </div>

                        <!-- Options -->
                        <div v-if="optionsSelectionnees.length > 0" class="mt-3 border-t border-gray-100 pt-3 flex flex-col gap-1">
                            <div v-for="(opt, i) in optionsSelectionnees" :key="i" class="flex justify-between text-xs text-gray-500">
                                <span class="flex-1">+ {{ opt.option.nom }}<template v-if="opt.option.type === 'Quantifiable'"> ×{{ opt.quantite }}</template></span>
                                <span class="shrink-0 ml-2">{{ (opt.option.tarif * (opt.option.type === 'Quantifiable' ? opt.quantite : 1)).toFixed(2) }}.-</span>
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
            <div v-if="erreurGroupe" class="mx-6 mb-2 flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3">
    <Icon icon="mdi:alert-circle-outline" class="w-5 h-5 shrink-0" />
    <span>{{ erreurGroupe }}</span>
    <button type="button" @click="erreurGroupe = null" class="ml-auto text-red-400 hover:text-red-600">
        <Icon icon="mdi:close" class="w-4 h-4" />
    </button>
</div>
            <!-- Footer -->
            <div class="flex justify-between items-center px-6 py-4 border-t border-gray-100">
                <button v-if="etapesActives.indexOf(etape) > 0" @click="etapePrecedente" class="btn-accent-300">
                    Etape précédente
                </button>
                <div v-else></div>

                <button @click="etapeSuivante" :disabled="!peutContinuer || creationGroupe"
                    :class="['btn-tertiary', (!peutContinuer || creationGroupe) ? 'opacity-50 cursor-not-allowed' : '']">
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
    data() {
        return {
            formulaireEtape, modals,
            etape: formulaireEtape.PARAMETRE,
            modalAffichage: null,
            formulaireEtapesLabels: [],
            participantsSupplementaires: [],
            creationGroupe: false, // spinner pendant la création du groupe en DB
            inscription: {
                type: null,
                participant: [],
                groupeEphemere: null, // { nom, participants[] } — tâche 2.3 & 3.1
                options: {},
                documents: [],
                reponses: {},
                codeParticipation: '',
                nom_equipe: '', 
            },
            erreurGroupe: null,
        };
    },
    computed: {
        tousLesParticipants() {
            return [...this.participants, ...this.participantsSupplementaires];
        },
        estCourseGroupe() {
            return this.course.type === 'Groupe';
        },
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
        estDerniereEtape() {
            return this.etapesActives.indexOf(this.etape) === this.etapesActives.length - 1;
        },
        peutContinuer() {
            if (this.etape === formulaireEtape.PARAMETRE) return !!this.inscription.type;
            if (this.etape === formulaireEtape.PARTICIPANTS) {
                if (this.inscription.type?.id === 'groupe') {
                    // Groupe valide = nom non vide + au moins 1 membre
                    return !!(this.inscription.groupeEphemere?.nom?.trim()) &&
                           (this.inscription.groupeEphemere?.participants?.length ?? 0) > 0;
                }
                if (this.inscription.type?.id === 'relais') return this.inscription.participant.length >= 2;
                return this.inscription.participant.length > 0;
            }
            return true;
        },
        optionsSelectionnees() {
            return Object.values(this.inscription.options || {});
        },
        totalInscription() {
            const base = parseFloat(this.course.tarif) || 0;
            const extras = this.optionsSelectionnees.reduce((acc, { option, quantite }) =>
                acc + option.tarif * (option.type === 'Quantifiable' ? quantite : 1), 0);
            return base + extras;
        },
    },
    methods: {
        etapePrecedente() {
            const idx = this.etapesActives.indexOf(this.etape);
            if (idx > 0) this.etape = this.etapesActives[idx - 1];
        },

        async etapeSuivante() {
            this.erreurGroupe = null;
            if (!this.peutContinuer || this.creationGroupe) return;

            // ── Dernière étape : "Ajouter au panier" ──────────────────────
            if (this.estDerniereEtape) {
                let id_groupe = null;

                // Si mode groupe : créer le groupe en DB maintenant
                if (this.inscription.type?.id === 'groupe' && this.inscription.groupeEphemere) {
                    this.creationGroupe = true;
                    try {
                        // 1. Créer le groupe
                        const groupeResp = await groupeService.createGroupe({
    nom:       this.inscription.groupeEphemere.nom,
    type:      'Groupe',
    id_course: this.course.id, // ← nouveau
});
                        const groupeId = groupeResp.data.id;
                        id_groupe = groupeId;

                        // 2. Attacher chaque membre qui a un id réel (existant en DB)
                        //    Les participants créés à la volée (id = Date.now()) ne sont pas encore en DB,
                        //    on les ignore pour la liaison groupe (ils seront créés via l'inscription)
                        const membresBD = this.inscription.groupeEphemere.participants.filter(
                            p => typeof p.id === 'number' && p.id < 1e12 // id DB < id timestamp
                        );
                        for (const membre of membresBD) {
    try {
        await groupeService.addParticipant(groupeId, membre.id);
    } catch (e) {
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

                this.$emit('ajouter-panier', {
                    ...this.inscription,
                    id_groupe,
                    tarif: this.totalInscription,
                });
                return;
            }

            // ── Étapes intermédiaires ─────────────────────────────────────
            const idx = this.etapesActives.indexOf(this.etape);
            this.etape = this.etapesActives[idx + 1];
        },

        ajouterParticipantSupplementaire(data) {
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