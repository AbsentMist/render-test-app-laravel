<template>
    <div class="bg-secondary p-6 rounded-base">
        <IndicateurEtapes :steps="formulaireEtapesLabels" :currentStep="etapesActives.indexOf(etape) + 1"/>

        <div v-if="etape==formulaireEtape.GENERAL">
            <p class="text-subtitle my-4">Créer un évènement</p>
            <form>
                <div class="flex flex-row gap-4">
                    <div class="basis-3/4 flex flex-col gap-4">
                        <div>
                            <label for="name" class="block mb-2.5 text-sm font-medium text-heading">Nom de l'évènement</label>
                            <input type="text" id="name" v-model="eventData.name" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="" required />
                        </div>
                        <div>
                            <label for="url" class="block mb-2.5 text-sm font-medium text-heading">Lien du site web</label>
                            <input type="text" id="url" v-model="eventData.url" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" required />
                        </div>
                    </div>
                    <div class="basis-1/4">
                        <label for="logo" class="block mb-2.5 text-sm font-medium text-heading">Logo de l'évènement</label>
                        <div class="flex items-center justify-center w-full">
                            <label for="dropzone-file" class="flex flex-col items-center justify-center w-full bg-neutral-secondary-medium border border-dashed border-default-strong rounded-base cursor-pointer hover:bg-neutral-tertiary-medium">
                                <div class="flex flex-col items-center justify-center text-body pt-2 pb-5">
                                    <svg class="w-8 h-8 mb-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h3a3 3 0 0 0 0-6h-.025a5.56 5.56 0 0 0 .025-.5A5.5 5.5 0 0 0 7.207 9.021C7.137 9.017 7.071 9 7 9a4 4 0 1 0 0 8h2.167M12 19v-9m0 0-2 2m2-2 2 2"/></svg>
                                    <p class="mb-2 text-sm"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                    <p class="text-xs">SVG, PNG, JPG or GIF (MAX. 800x400px)</p>
                                </div>
                                <input id="dropzone-file" type="file" class="hidden" @change="eventData.logo = $event.target.files[0]" />
                            </label>
                        </div> 
                    </div>
    
                </div>
                <hr class="border-t border-gray-200 mt-6 mb-4 mx-4" />
                <div>
                    <p>Couleurs</p>
                    <div class="flex flex-col m-4 gap-2">
                        <div class="flex flex-row justify-between items-center">
                            <label class="text-sm font-medium text-heading">Primaire</label>
                            <div class="items-center flex gap-4 bg-neutral-secondary-medium p-2 rounded-base border border-default-medium">
                                <Icon icon="mdi:pipette" />
                                <label class="">{{ eventData.colors.primary }}</label>
                                <input type="color" id="EventColorPrimary" v-model="eventData.colors.primary" class="border rounded-base" />
                            </div>
                        </div>
                        <div class="flex flex-row justify-between items-center ">
                            <label class="text-sm font-medium text-heading">Secondaire</label>
                            <div class="items-center flex gap-4 bg-neutral-secondary-medium p-2 rounded-base border border-default-medium">
                                <Icon icon="mdi:pipette" />
                                <label class="">{{ eventData.colors.secondary }}</label>
                                <input type="color" id="EventColorSecondary" v-model="eventData.colors.secondary" class="border rounded-base" />
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="border-t border-gray-200 mt-6 mb-4 mx-4" />
                <div>
                    <p>Paramètres</p>
                    <div class="flex flex-col m-4 gap-2">
                        <div class="flex flex-row justify-between items-center">
                            <label class="text-sm font-medium text-heading">Actif</label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="eventData.parameters.actif" value="" class="sr-only peer">
                                <div class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"></div>
                            </label>
                        </div>
                        <div class="flex flex-row justify-between items-center">
                            <label class="text-sm font-medium text-heading">Avertissement</label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="eventData.parameters.avertissement" value="" class="sr-only peer">
                                <div class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"></div>
                            </label>
                        </div>
                        <div class="flex flex-row justify-between items-center">
                            <label class="text-sm font-medium text-heading">Document</label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="eventData.parameters.document" value="" class="sr-only peer">
                                <div class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"></div>
                            </label>
                        </div>
                        <div class="flex flex-row justify-between items-center">
                            <label class="text-sm font-medium text-heading">Questionnaire</label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="eventData.parameters.questionnaire" value="" class="sr-only peer">
                                <div class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"></div>
                            </label>
                        </div>
                        <div class="flex flex-row justify-between items-center">
                            <label class="text-sm font-medium text-heading">Rabais</label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="eventData.parameters.rabais" value="" class="sr-only peer">
                                <div class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div v-if="etape==formulaireEtape.OPTIONS">
            <p class="text-subtitle my-4">Options supplémentaires</p>
            <div v-for="(option, index) in eventData.options" class="my-4">
                <OptionTemplate :optionModel="option" @remove-option="eventData.options.splice(index, 1)"/>
            </div>
            <div class="flex pt-4 items-center">
                <div class="flex-grow border-t border-gray-700 "></div>
                <span class="mx-4">
                    <button type="button" @click="handleModalState" class="bg-tertiary border border-default-medium text-heading text-sm rounded-full focus:border-tertiary-900 px-2.5 py-2.5">
                        <Icon icon="mdi:plus" class="w-4 h-4" />
                    </button>
                </span>
                <div class="flex-grow border-t border-gray-700"></div>
            </div>
            <div v-if="modal==optionModal.SELECTION" class="flex items-center justify-center z-50">
                <OptionList :elements="this.optionElements" @select-item="handleOptionSelection"/>
            </div>
            <div v-if="modal==optionModal.EXISTANT" class="flex items-center justify-center z-50">
                <OptionList :elements="optionModels.map(o => o.nom)" @select-item="handleOptionSelection"/>
            </div>
        </div>

        <div v-if="etape==formulaireEtape.QUESTIONNAIRE">
            <p class="text-subtitle my-4">Questionnaires</p>
            <div v-for="(question, index) in eventData.questions" class="my-4">
                <QuestionTemplate :questionModel="question" @remove-question="eventData.questions.splice(index, 1)"/>
            </div>
            <div class="flex pt-4 items-center">
                <div class="flex-grow border-t border-gray-700 "></div>
                <span class="mx-4">
                    <button type="button" @click="handleModalState" class="bg-tertiary border border-default-medium text-heading text-sm rounded-full focus:border-tertiary-900 px-2.5 py-2.5">
                        <Icon icon="mdi:plus" class="w-4 h-4" />
                    </button>
                </span>
                <div class="flex-grow border-t border-gray-700"></div>
            </div>
            <div v-if="modal==optionModal.SELECTION" class="flex items-center justify-center z-50">
                <OptionList :elements="this.optionElements" @select-item="handleOptionSelection"/>
            </div>
            <div v-if="modal==optionModal.EXISTANT" class="flex items-center justify-center z-50">
                <OptionList :elements="questionModels.map(q => q.question)" @select-item="handleOptionSelection"/>
            </div>
        </div>

        <div v-if="etape==formulaireEtape.AVERTISSEMENT">
            <p class="text-subtitle mt-4">Avertissement</p>
            <p class="mb-4">Cette page apparaitra dès la sélection d'une course. Elle sert à avertir les participants de risques potentiels.</p>
            <div class="flex flex-col-2 gap-4 h-128">
                <textarea type="text" id="avertissement" v-model="eventData.avertissement.description" class="bg-neutral-secondary-medium basis-2/3 border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="" required />
                <div class="basis-1/3">
                    <h2 class="text-sm font-medium text-heading mb-2.5">Mes modèles</h2>
                    <button v-for="avertissementModel in avertissementModels" :key="avertissementModel.name" type="button" @click="eventData.avertissement.description = avertissementModel.description" class="btn-model">
                        {{ avertissementModel.name }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="etape==formulaireEtape.DOCUMENT">
            <p class="text-subtitle mt-4">Documents</p>
            <p class="mb-4">Décrivez quels documents doivent être fournis et quels type de personnes sont concernées.</p>
            <div class="flex flex-col-2 gap-4 h-128">
                <textarea type="text" id="documents" v-model="eventData.document.description" class="bg-neutral-secondary-medium basis-2/3 border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="" required />
                <div class="basis-1/3">
                    <h2 class="text-sm font-medium text-heading mb-2.5">Mes modèles</h2>
                    <button v-for="documentModel in documentModels" :key="documentModel.name" type="button" @click="eventData.document.description = documentModel.description" class="btn-model">
                        {{ documentModel.name }}
                    </button>
                </div>
            </div>
        </div>

        <div class="flex flex-row mt-6 gap-4"> 
            <button v-if="etapesActives.indexOf(etape) > 0" class="btn-accent-300" @click="etape = etapesActives[etapesActives.indexOf(etape) - 1]">
                Etape précédente
            </button>
            <button v-if="etapesActives.indexOf(etape) < etapesActives.length - 1" class="btn-tertiary ml-auto" @click="etape = etapesActives[etapesActives.indexOf(etape) + 1]">
                Etape suivante
            </button>
            <button v-else class="btn-tertiary ml-auto" @click="confirmationPopup=true">
                Créer l'évènement
            </button>
        </div>
        <PopupConfirmation v-if="confirmationPopup" @cancel="confirmationPopup = false" @confirm="insertData()"/>
        <PopupConfirmation v-if="dataInserted" :message="'L\'évènement a été créé avec succès !'" :icon="'mdi:check'" :showButtons="false"/>
    </div>
</template>

<script>
import { Icon } from "@iconify/vue";
import OptionList from "./OptionList.vue";
import OptionTemplate from "./OptionTemplate.vue";
import QuestionTemplate from "./QuestionTemplate.vue";
import PopupConfirmation from "./PopupConfirmation.vue";
import IndicateurEtapes from "./IndicateurEtapes.vue";
import evenementOrganisateurService from "../services/evenementOrganisateurService";
import optionOrganisateurService from "../services/optionOrganisateurService";

const formulaireEtape = {
    GENERAL: 1,
    OPTIONS: 2,
    AVERTISSEMENT: 3,
    DOCUMENT: 4,
    QUESTIONNAIRE: 5,
};

const optionModal = {
    FERMEE: 1,
    SELECTION: 2,
    EXISTANT: 3,
};

export default {
    components: {
        Icon,
        OptionList,
        OptionTemplate,
        QuestionTemplate,
        PopupConfirmation,
        IndicateurEtapes,
        evenementOrganisateurService,
        optionOrganisateurService,
    },
    data() {
        return {
            formulaireEtape,
            optionModal,
            etape: formulaireEtape.GENERAL,
            modal: optionModal.FERMEE,
            confirmationPopup: false,
            dataInserted: false,
            eventData: {
                name: '',
                url: '',
                logo: null,
                colors: {
                    primary: '#0e0f54',
                    secondary: '#d9f20b',
                },
                parameters: {
                    actif: false,
                    avertissement: false,
                    document: false,
                    questionnaire: false,
                    rabais: false,
                },
                options: [],
                questions: [],
                avertissement: {
                    description: '',
                },
                document: {
                    description: '',
                },
            },

            optionElements: [
                "Existant", "Nouveau"
            ],
            optionModels: [
                {
                    name: "1 Entrée + 1 pasta bolognaise",
                    description: "Réservation entrée + pasta non-participant CHF 19.00 / paiement à RUNNINGENEVA ASSOCIATION",
                    prix: "15",
                    quantifiable: {
                        quantiteMin: "1",
                        quantiteMax: "10"
                    }
                }
            ],
            questionModels: [
                {
                    question: "Comment avez-vous connu l'évènement ?",
                    answers: ["Réseaux sociaux", "Bouche à oreille", "Autre"]
                }
            ],
            avertissementModels: [
                {
                    name: "Course des ponts",
                    description: "Course urbaine de 5km avec de nombreux ponts à traverser, ce qui peut présenter un risque de chute en cas de pluie."
                }, 
                {
                    name: "Antigel",
                    description: "En raison de conditions météorologiques hivernales, du verglas peut être présent sur le parcours, ce qui peut rendre certaines sections glissantes."
                }
            ],
            documentModels: [
                {
                    name: "Etudiant",
                    description: "Si vous êtes étudiant, veuillez fournir l'attestation de scolarité."
                }, 
                {
                    name: "Etudiant + santé",
                    description: "Étudiant inscrit à un cours avec attestation de santé."
                }
            ],
            formulaireEtapesLabels: [
                "Général", 
                "Options supplémentaires", 
            ],
        };
    },
    computed: {
        etapesActives() {
            const etapes = [formulaireEtape.GENERAL, formulaireEtape.OPTIONS];
            const labels = ["Général", "Options supplémentaires"];
            if (this.eventData.parameters.avertissement){
                etapes.push(formulaireEtape.AVERTISSEMENT);
                labels.push("Avertissement");
            } 
            if (this.eventData.parameters.document) {
                etapes.push(formulaireEtape.DOCUMENT);
                labels.push("Documents");
            }
            if (this.eventData.parameters.questionnaire) {
                etapes.push(formulaireEtape.QUESTIONNAIRE);
                labels.push("Questionnaire");
            }
            this.formulaireEtapesLabels = labels;
            return etapes.sort((a, b) => a - b);
        }
    },
    watch: {
        'eventData.parameters.avertissement'(val) {
            if (!val) this.eventData.avertissement = { name: '', description: '' };
        },
        'eventData.parameters.document'(val) {
            if (!val) this.eventData.document = { name: '', description: '' };
        },
        'eventData.parameters.questionnaire'(val) {
            if (!val) this.eventData.questions = [];
        },
    },  
    methods: {
        handleModalState(){
            if(this.modal === optionModal.FERMEE) {
                this.modal = optionModal.SELECTION;
            }
            else {
                this.modal = optionModal.FERMEE;
            }
        },
        handleOptionSelection(option) {
            console.log("Option sélectionnée :", option);
            if(option === "Existant") {
                this.modal = optionModal.EXISTANT;
            }
            else if(option === "Nouveau") {
                if(this.etape === formulaireEtape.OPTIONS)
                    this.eventData.options.push({
                        name: '',
                        description: '',
                        prix: '',
                        quantifiable: {
                            quantiteMin: '',
                            quantiteMax: '',
                        }
                    });

                else if(this.etape === formulaireEtape.QUESTIONNAIRE) 
                    this.eventData.questions.push({
                        question: '',
                        answers: []
                    });

                this.modal = optionModal.FERMEE; 
            }
            else if(this.modal === optionModal.EXISTANT) {
                if(this.etape === formulaireEtape.OPTIONS)
                    this.eventData.options.push(this.optionModels.find(o => o.nom === option)); 
                
                else if(this.etape === formulaireEtape.QUESTIONNAIRE) 
                    this.eventData.questions.push(this.questionModels.find(q => q.question === option));

                this.modal = optionModal.FERMEE;
            }
        },
        async insertData() {
            try {
                const formData = new FormData();
                formData.append('nom',              this.eventData.name);
                formData.append('site',              this.eventData.url);
                formData.append('couleur_primaire', this.eventData.colors.primary);
                formData.append('couleur_secondaire', this.eventData.colors.secondary);
                formData.append('is_avertissement',    this.eventData.parameters.avertissement ? 1 : 0);
                formData.append('is_document',         this.eventData.parameters.document ? 1 : 0);
                formData.append('is_questionnaire',    this.eventData.parameters.questionnaire ? 1 : 0);
                formData.append('is_rabais',           this.eventData.parameters.rabais ? 1 : 0);
                formData.append("is_actif",         this.eventData.parameters.actif ? 1 : 0);

                if (this.eventData.logo) {
                    formData.append('logo', this.eventData.logo);
                }
                if (this.eventData.parameters.avertissement) {
                    formData.append('avertissement_description', this.eventData.avertissement.description);
                }
                if (this.eventData.parameters.document) {
                    formData.append('document_description', this.eventData.document.description);
                }
                if (this.eventData.parameters.questionnaire) {
                    formData.append('questions', JSON.stringify(this.eventData.questions));
                }
                if (this.eventData.options.length > 0) {
                    formData.append('options', JSON.stringify(this.eventData.options));
                }

                const response = await evenementOrganisateurService.createEvenement(formData);
                this.confirmPopup();
                console.log(response.data);
            } catch(e) {
                console.log("Erreur:", e.response?.data);
            }
        },
        confirmPopup() {
            this.confirmationPopup = false;
            this.dataInserted = true; 
            setTimeout(() => {
                this.dataInserted = false; 
            }, 2000); 
        }
    },
    async mounted() {
        try{
            let response = await optionOrganisateurService.getAllOptions();
            this.optionModels = response.data;
            console.log("Options chargées: ", this.optionModels);
        } catch(e) {
            console.error("Erreur lors de la récupération des options: ", e);
        }
    },
};
</script>