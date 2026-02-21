<template>
    <div class="bg-secondary m-4 p-6 rounded-base">
        <div class="w-full bg-neutral-quaternary rounded-full h-2">
            <div class="bg-brand h-2 rounded-full" style="width: 45%"></div>
        </div>

        <div v-if="etape==formulaireEtape.GENERAL">
            <p class="text-subtitle my-4">Créer un évènement</p>
            <form>
                <div class="flex flex-row gap-4">
                    <div class="basis-3/4 flex flex-col gap-4">
                        <div>
                            <label for="eventName" class="block mb-2.5 text-sm font-medium text-heading">Nom de l'évènement</label>
                            <input type="text" id="eventName" v-model="eventName" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="" required />
                        </div>
                        <div>
                            <label for="eventUrl" class="block mb-2.5 text-sm font-medium text-heading">Lien du site web</label>
                            <input type="text" id="eventUrl" v-model="eventUrl" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" required />
                        </div>
                    </div>
                    <div class="basis-1/4">
                        <label for="eventLogo" class="block mb-2.5 text-sm font-medium text-heading">Logo de l'évènement</label>
                        <div class="flex items-center justify-center w-full">
                            <label for="dropzone-file" class="flex flex-col items-center justify-center w-full bg-neutral-secondary-medium border border-dashed border-default-strong rounded-base cursor-pointer hover:bg-neutral-tertiary-medium">
                                <div class="flex flex-col items-center justify-center text-body pt-2 pb-5">
                                    <svg class="w-8 h-8 mb-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h3a3 3 0 0 0 0-6h-.025a5.56 5.56 0 0 0 .025-.5A5.5 5.5 0 0 0 7.207 9.021C7.137 9.017 7.071 9 7 9a4 4 0 1 0 0 8h2.167M12 19v-9m0 0-2 2m2-2 2 2"/></svg>
                                    <p class="mb-2 text-sm"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                    <p class="text-xs">SVG, PNG, JPG or GIF (MAX. 800x400px)</p>
                                </div>
                                <input id="dropzone-file" type="file" class="hidden" />
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
                                <label class="">{{ eventColorPrimary }}</label>
                                <input type="color" id="EventColorPrimary" v-model="eventColorPrimary" class="border rounded-base" />
                            </div>
                        </div>
                        <div class="flex flex-row justify-between items-center ">
                            <label class="text-sm font-medium text-heading">Secondaire</label>
                            <div class="items-center flex gap-4 bg-neutral-secondary-medium p-2 rounded-base border border-default-medium">
                                <Icon icon="mdi:pipette" />
                                <label class="">{{ eventColorSecondary }}</label>
                                <input type="color" id="EventColorSecondary" v-model="eventColorSecondary" class="border rounded-base" />
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="border-t border-gray-200 mt-6 mb-4 mx-4" />
                <div>
                    <p>Paramètres</p>
                    <div class="flex flex-col m-4 gap-2">
                        <div class="flex flex-row justify-between items-center">
                            <label class="text-sm font-medium text-heading">Avertissement</label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" value="" class="sr-only peer">
                                <div class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"></div>
                            </label>
                        </div>
                        <div class="flex flex-row justify-between items-center">
                            <label class="text-sm font-medium text-heading">Document</label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" value="" class="sr-only peer">
                                <div class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"></div>
                            </label>
                        </div>
                        <div class="flex flex-row justify-between items-center">
                            <label class="text-sm font-medium text-heading">Questionnaire</label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" value="" class="sr-only peer">
                                <div class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"></div>
                            </label>
                        </div>
                        <div class="flex flex-row justify-between items-center">
                            <label class="text-sm font-medium text-heading">Rabais</label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" value="" class="sr-only peer">
                                <div class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div v-if="etape==formulaireEtape.RESSOURCES">
            <p class="text-subtitle my-4">Ressources supplémentaires</p>
            <div v-if="this.emptyOptionNumber > 0 " v-for="emptyOptionNumber in emptyOptionNumber" class="my-4">
                <OptionTemplate/>
            </div>
            <div v-if="this.selectedOptions.length > 0 " v-for="selectedOption in selectedOptions" class="my-4">
                <OptionTemplate :existingOptions="selectedOption"/>
            </div>
            <div class="flex pt-4 items-center">
                <div class="flex-grow border-t border-gray-700 "></div>
                <span class="mx-4">
                    <button type="button" @click="handleModalState  " class="bg-tertiary border border-default-medium text-heading text-sm rounded-full focus:border-tertiary-900 px-2.5 py-2.5">
                        <Icon icon="mdi:plus" class="w-4 h-4" />
                    </button>
                </span>
                <div class="flex-grow border-t border-gray-700"></div>
            </div>
            <div v-if="modal==optionModal.SELECTION" class="flex items-center justify-center z-50">
                <OptionList :elements="this.optionElements" @select-item="handleOptionSelection"/>
            </div>
            <div v-if="modal==optionModal.EXISTANT" class="flex items-center justify-center z-50">
                <OptionList :elements="this.existingOptionElements.map(o => o.name)" @select-item="handleOptionSelection"/>
            </div>
        </div>

        <div v-if="etape==formulaireEtape.QUESTIONNAIRE">
            <p class="text-subtitle my-4">Questionnaires</p>
            <div v-if="this.emptyQuestionNumber > 0 " v-for="emptyQuestionNumber in emptyQuestionNumber" class="my-4">
                <QuestionTemplate/>
            </div>
            <div v-if="this.selectedQuestions.length > 0 " v-for="selectedQuestion in selectedQuestions" class="my-4">
                <QuestionTemplate :existingQuestions="selectedQuestion"/>
            </div>
            <div class="flex pt-4 items-center">
                <div class="flex-grow border-t border-gray-700 "></div>
                <span class="mx-4">
                    <button type="button" @click="handleModalState  " class="bg-tertiary border border-default-medium text-heading text-sm rounded-full focus:border-tertiary-900 px-2.5 py-2.5">
                        <Icon icon="mdi:plus" class="w-4 h-4" />
                    </button>
                </span>
                <div class="flex-grow border-t border-gray-700"></div>
            </div>
            <div v-if="modal==optionModal.SELECTION" class="flex items-center justify-center z-50">
                <OptionList :elements="this.optionElements" @select-item="handleOptionSelection"/>
            </div>
            <div v-if="modal==optionModal.EXISTANT" class="flex items-center justify-center z-50">
                <OptionList :elements="this.existingQuestionElements.map(q => q.question)" @select-item="handleOptionSelection"/>
            </div>
        </div>

        <div class="flex flex-row mt-6 gap-4"> 
            <button v-if="etape > formulaireEtape.GENERAL" class="btn-accent-300" @click="etape--">
                Etape précédente
            </button>
            <button v-if="etape < Math.max(...Object.values(formulaireEtape))" class="btn-tertiary ml-auto" @click="etape++">
                Etape suivante
            </button>
        </div>
    </div>
</template>

<script>
import { Icon } from "@iconify/vue";
import OptionList from "./OptionList.vue";
import OptionTemplate from "./OptionTemplate.vue";
import QuestionTemplate from "./QuestionTemplate.vue";

const formulaireEtape = {
    GENERAL: 1,
    RESSOURCES: 2,
    QUESTIONNAIRE: 3,
    AVERTISSEMENT: 4,
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
    },
    data() {
        return {
            formulaireEtape,
            optionModal,
            etape: formulaireEtape.GENERAL,
            modal: optionModal.FERMEE,
            optionElements: ["Existant", "Nouveau"],
            existingOptionElements: [
                {name: "1 Entrée + 1 pasta bolognaise",
                description: "Réservation entrée + pasta non-participant CHF 19.00 / paiement à RUNNINGENEVA ASSOCIATION",
                prix: "15",
                quantiteMin: "1",
                quantiteMax: "10"}],
            emptyOptionNumber: 0,
            selectedOptions: [],
            existingQuestionElements: [{
                question: "Comment avez-vous connu l'évènement ?",
                answers: ["Réseaux sociaux", "Bouche à oreille", "Autre"]
            }],
            emptyQuestionNumber: 0,
            selectedQuestions: [],
            eventName: '',
            eventUrl: '',
            eventLogo: null,
            eventColorPrimary: '#0e0f54',
            eventColorSecondary: '#d9f20b'
        };
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
                // Logique pour ajouter une nouvelle option
                if(this.etape === formulaireEtape.RESSOURCES)
                    this.emptyOptionNumber++;

                else if(this.etape === formulaireEtape.QUESTIONNAIRE) 
                    this.emptyQuestionNumber++;
                
                this.modal = optionModal.FERMEE; 
            }
            else if(this.modal === optionModal.EXISTANT) {
                if(this.etape === formulaireEtape.RESSOURCES)
                    this.selectedOptions.push(this.existingOptionElements.find(o => o.name === option)); 
                
                else if(this.etape === formulaireEtape.QUESTIONNAIRE) 
                    this.selectedQuestions.push(this.existingQuestionElements.find(q => q.question === option));
                
                this.modal = optionModal.FERMEE;
            }
        },
    }
};
</script>