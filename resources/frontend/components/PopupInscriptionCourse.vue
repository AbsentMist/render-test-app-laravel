<template>
    <PopupAvertissementCourse v-if="modalAffichage == modals.AVERTISSEMENT && course.avertissement" :texte="course.avertissement.contenu"
        @confirmer="modalAffichage = modals.INSCRIPTION" @close="$emit('close')"/>
    <div v-if="modalAffichage == modals.INSCRIPTION" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-4 flex flex-col overflow-hidden" style="height: 70vh">

            <!-- Header -->
            <div class="flex items-center justify-between px-6 pt-5 pb-2 border-b border-gray-100 bg-primary-300">
                <div>                   
                    <span class="px-6 text-subtitle font-medium text-secondary">{{ course.nom_course }}</span>
                    <div class="h-1 w-24 ml-6 rounded-r-full mb-2" :style="{ backgroundColor: course.evenement.couleur_secondaire }"></div>
                    <span class="mx-6 px-2 py-0.5 text-base font-medium text-secondary rounded-full" :style="{color: course.evenement.couleur_secondaire, backgroundColor: course.evenement.couleur_primaire, borderColor: course.evenement.couleur_secondaire}">{{ course.evenement.nom }}</span>
                </div>
                <button @click="$emit('close')" class="text-secondary hover:text-gray-600 transition-colors">
                    <Icon icon="mdi:close" class="w-5 h-5" />
                </button>
            </div>

            <div class="flex flex-1 overflow-hidden">
                <div class="basis-4/6">
                    <!-- Colonne gauche : étapes + contenu -->
                    <div class="flex-1 px-6 pt-5">
                        <!-- Indicateur étapes -->
                        <IndicateurEtapes :steps="formulaireEtapesLabels" :currentStep="etapesActives.indexOf(etape) + 1"/>
                    </div>
                    <div class="px-10 ">
                        <span class="text-subtitle">{{ formulaireEtapesLabels[etapesActives.indexOf(etape)]  }}</span>
                        
                        <!-- Composants des pages de l'inscription à mettre avec des conditions-->
                         
                    
                    </div>
                </div>

                <div class="basis-2/6 border-l border-gray-100 px-5 py-5 flex flex-col justify-between bg-gray-50/60">
                    <!-- Colonne droite : récapitulatif -->
                    <div class="border-l border-gray-100 px-5 py-5 flex flex-col justify-between bg-gray-50/60">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-3">Votre inscription</h3>
                            <p class="text-sm font-medium text-gray-800">{{ course.nom }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ course.sousTitre }}</p>
                            <div class="flex justify-between items-center mt-3 text-sm">
                                <span class="text-gray-600">{{ course.tarif }}.-</span>
                            </div>
                        </div>
                        <div class="border-t border-gray-200 pt-3 mt-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-bold text-gray-800">Total</span>
                                <span class="text-sm font-bold text-gray-800">{{ course.tarif }}.-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Contenu principal -->

            <!-- Footer navigation -->
            <div class="flex justify-between items-center px-6 py-4 border-t border-gray-100">
                <button
                    v-if="etapesActives.indexOf(etape) > 0"
                    @click="etape = etapesActives[etapesActives.indexOf(etape) - 1]"
                    class="btn-accent-300"
                >
                    Étape précédente
                </button>
                <div v-else></div>
                <button
                    @click="etapesActives.indexOf(etape) < etapesActives.length - 1 ? etape = etapesActives[etapesActives.indexOf(etape) + 1] : $emit('terminer')"
                    class="btn-tertiary"
                >
                    {{ etapesActives.indexOf(etape) < etapesActives.length - 1 ? 'Étape suivante' : 'Confirmer' }}
                </button>

            </div>
        </div>
    </div>
</template>

<script>
import { Icon } from '@iconify/vue';
import IndicateurEtapes from './IndicateurEtapes.vue';
import Title from './Title.vue';
import OptionTemplate from './OptionTemplate.vue'
import PopupAvertissementCourse from './PopupAvertissementCourse.vue';

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
}

export default {
    components: { 
        Icon,
        IndicateurEtapes,
        Title,
        OptionTemplate,
        PopupAvertissementCourse
     },
    emits: ['close', 'terminer'],
    props: {
        course: {
            type: Object,
            default: () => ({
                nom: 'Nocturne des Evaux',
                sousTitre: 'Challenge étudiant · Mixte',
                tarif: 45,
            })
        },
        couleurPrimaire: {
            type: String,
            default: '#a3c639'
        },
    },
    data() {
        return {
            formulaireEtape,
            modals,
            etape: formulaireEtape.PARAMETRE,
            modalAffichage: null,
            formulaireEtapesLabels: ["Paramètres", "Participants", "Options", "Confirmation"],
            etapeActive: 0,
            typeSelectionne: null,
            typesCourse: [
                { id: 1, nom: 'Challenge' },
                { id: 2, nom: 'Relais' },
            ],
        };
    },
    computed: {
        etapesActives() {
            const etapes = [formulaireEtape.PARAMETRE, formulaireEtape.PARTICIPANTS, formulaireEtape.CONFIRMATION];
            const labels = ["Paramètres", "Participants", "Confirmation"];

            if (this.course.options && this.course.options.length > 0) {
                const i = etapes.indexOf(formulaireEtape.CONFIRMATION);
                etapes.splice(i, 0, formulaireEtape.OPTIONS);
                labels.splice(i, 0, "Options");
            }
            if (this.course.document) {
                const i = etapes.indexOf(formulaireEtape.CONFIRMATION);
                etapes.splice(i, 0, formulaireEtape.DOCUMENT);
                labels.splice(i, 0, "Document");
            }
            if (this.course.questionnaire) {
                const i = etapes.indexOf(formulaireEtape.CONFIRMATION);
                etapes.splice(i, 0, formulaireEtape.QUESTIONNAIRE);
                labels.splice(i, 0, "Questionnaire");
            }

            this.formulaireEtapesLabels = labels;
            return etapes;
        }
    },
    mounted() {
        this.etape = this.etapesActives[0];
        this.modalAffichage = this.course.avertissement ? modals.AVERTISSEMENT : modals.INSCRIPTION;
        this.etape = this.etapesActives[0];
    }
}
</script>