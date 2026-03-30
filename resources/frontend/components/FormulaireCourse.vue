<template>
    <div class="bg-secondary rounded-b-base rounded-tr-base p-6">
        <IndicateurEtapes :steps="formulaireEtapesLabels" :currentStep="etapesActives.indexOf(etape) + 1"/>

        <div v-if="etape==formulaireEtape.GENERAL">
            <h1 class="text-subtitle my-4">{{ isEditMode ? 'Modifier la course' : 'Créer une course' }}</h1>
            
            <div class="flex justify-between items-center">
                <label for="dropdown" class="text-sm font-medium text-heading">Evènement</label>
                <div class="relative">
                    <button data-dropdown-toggle="dropdownEvent" class="inline-flex items-center justify-center border hover:bg-primary-300 text-white bg-primary-900 shadow-xs font-medium rounded-base text-sm px-4 py-2.5" type="button">
                        {{courseData.event.nom || courseData.event.name || "Sélectionner un évènement"}}
                        <Icon icon="mdi:chevron-down" class="ml-2 w-6 h-6" />
                    </button>
                    <div id="dropdownEvent" class="hidden absolute left-0 mt-1 z-10 bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44">
                        <ul class="p-2 text-sm text-body font-medium">
                            <li v-for="evenement in evenements" :key="evenement.id">
                                <button type="button" @click="selectEvent(evenement);" 
                                    class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">
                                    {{ evenement.nom }}
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="w-full">
                <label for="name" class="block mb-2.5 text-sm font-medium text-heading">Nom</label>
                <input type="text" id="name" v-model="courseData.name" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="" required />
            </div>

            <hr class="border-t border-gray-200 mt-6 mb-4 mx-4" />

            <div class="flex items-center gap-4">
                <div class="w-full">
                    <label for="datepicker-start" class="block mb-2.5 text-sm font-medium text-heading">Date de début</label>
                    <input
                        id="datepicker-start"
                        v-model="courseData.date.start"
                        name="dateStart"
                        type="date"
                        class="block w-full bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 py-2.5 shadow-xs placeholder:text-body"
                    >
                </div>
                <div class="w-full">
                    <label for="datepicker-end" class="block mb-2.5 text-sm font-medium text-heading">Date de fin</label>
                    <input
                        id="datepicker-end"
                        v-model="courseData.date.end"
                        :min="courseData.date.start || undefined"
                        name="dateEnd"
                        type="date"
                        class="block w-full bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 py-2.5 shadow-xs placeholder:text-body"
                    >
                </div>
            </div>
            <div class="flex justify-between items-center gap-4 my-4">
                <label for="inscriptionpicker-start" class="block mb-2.5 text-sm font-medium text-heading">Interval d'inscription</label>
                <div class="flex row gap-4 basis-1/2">
                    <input
                        id="inscriptionpicker-start"
                        v-model="courseData.date.inscriptionStart"
                        name="inscriptionStart"
                        type="date"
                        class="block w-full bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 py-2.5 shadow-xs placeholder:text-body"
                    >
                    <input
                        id="inscriptionpicker-end"
                        v-model="courseData.date.inscriptionEnd"
                        :min="courseData.date.inscriptionStart || undefined"
                        name="inscriptionEnd"
                        type="date"
                        class="block w-full bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 py-2.5 shadow-xs placeholder:text-body"
                    >
                </div>
            </div>

            <hr class="border-t border-gray-200 mt-6 mb-4 mx-4" />

            
            <div class="flex flex-col-3 gap-4 mb-4">
                <div class="w-full">
                    <label for="distance" class="block mb-2.5 text-sm font-medium text-heading">Distance</label>
                    <input type="number" id="distance" v-model="courseData.distance" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="" required />
                </div>
                <div class="w-full">
                    <label for="tarif" class="block mb-2.5 text-sm font-medium text-heading">Tarif</label>
                    <input type="number" id="tarif" v-model="courseData.tarif" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" required />
                </div>
                <div class="w-full">
                    <label for="tarifInfo" class="block mb-2.5 text-sm font-medium text-heading">Information tarif</label>
                    <input type="text" id="tarifInfo" v-model="courseData.tarifInfo" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" required />
                </div>
            </div>
            <div class="flex justify-between items-center gap-4 my-4">
                <label for="dropdown" class="text-sm font-medium text-heading">Type de course</label>
                <div class="relative">
                    <button data-dropdown-toggle="dropdownType" class="inline-flex items-center justify-center border hover:bg-primary-300 text-white bg-primary-900 shadow-xs font-medium rounded-base text-sm px-4 py-2.5" type="button">
                        {{courseData.type.name || "Sélectionner un type de course"}}
                        <Icon icon="mdi:chevron-down" class="ml-2 w-6 h-6" />
                    </button>
                    <div id="dropdownType" class="hidden absolute left-0 mt-1 z-10 bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44">
                        <ul class="p-2 text-sm text-body font-medium">
                            <li v-for="type in typesCourse" :key="type.id">
                                <button type="button" @click="selectType(type);" 
                                    class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">
                                    {{ type.name }}
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <hr class="border-t border-gray-200 mt-6 mb-4 mx-4" />

            <div class="flex justify-between items-center gap-4 my-4">
                <label for="maxRunners" class="basis-1/3 block mb-2.5 text-sm font-medium text-heading">Nombre de coureur maximum</label>
                <input type="number" id="maxRunners" v-model="courseData.maxRunners" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" required />
            </div>
            <div class="flex justify-between items-center gap-4 my-4">
                <label for="maxNbPersonne" class="basis-1/3 block mb-2.5 text-sm font-medium text-heading">
                    Nombre de personnes max par groupe
                </label>
                <input type="number" id="maxNbPersonne" v-model="courseData.maxNbPersonne" min="1"
                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base
                        focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs" />
            </div>
            <div class="flex flex-col-2 gap-4 mb-4">
                <div class="w-full">
                    <label for="firstDossard" class="block mb-2.5 text-sm font-medium text-heading">Premier dossard</label>
                    <input type="number" id="firstDossard" v-model="courseData.dossard.first" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="" required />
                </div>
                <div class="w-full">
                    <label for="lastDossard" class="block mb-2.5 text-sm font-medium text-heading">Dernier dossard</label>
                    <input type="number" id="lastDossard" v-model="courseData.dossard.last" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" required />
                </div>
            </div>
            <div class="flex flex-col-3 gap-4 mb-4">
                <div class="w-full">
                    <label for="ageMin" class="block mb-2.5 text-sm font-medium text-heading">Limite âge min</label>
                    <input type="number" id="ageMin" v-model="courseData.age.min" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="" required />
                </div>
                <div class="w-full">
                    <label for="ageMax" class="block mb-2.5 text-sm font-medium text-heading">Limite âge max</label>
                    <input type="number" id="ageMax" v-model="courseData.age.max" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" required />
                </div>
                <div class="w-full">
                    <label for="conditionMineur" class="block mb-2.5 text-sm font-medium text-heading">Condition participant mineur</label>
                    <input type="text" id="conditionMineur" v-model="courseData.age.conditionMineur" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" required />
                </div>
            </div>
            <div class="w-full">
                <label for="tempsMoyen" class="block mb-2.5 text-sm font-medium text-heading">Temps moyen pour X km</label>
                <input type="text" id="tempsMoyen" v-model="courseData.tempsMoyen" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" required />
            </div>
            
            <hr class="border-t border-gray-200 mt-6 mb-4 mx-4" />

            <!-- PARAMÈTRES -->
            <div class="flex flex-col m-4 gap-2">
                <div class="flex flex-row justify-between items-center mb-4">
                    <label class="text-sm font-medium text-heading">Actif</label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="courseData.parameters.actif" class="sr-only peer">
                        <div class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"></div>
                    </label>
                </div>
                <div class="flex flex-row justify-between items-center mb-4">
                    <label class="text-sm font-medium text-heading">Dossard personnalisé</label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="courseData.parameters.dossardPersonalise" class="sr-only peer">
                        <div class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"></div>
                    </label>
                </div>
                <div class="flex flex-row justify-between items-center mb-4">
                    <label class="text-sm font-medium text-heading">Challenge</label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="courseData.parameters.challenge" class="sr-only peer">
                        <div class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"></div>
                    </label>
                </div>
                <div class="flex flex-row justify-between items-center mb-4">
                    <label class="text-sm font-medium text-heading">Avertissement</label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="courseData.parameters.avertissement" class="sr-only peer">
                        <div class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"></div>
                    </label>
                </div>
                <div class="flex flex-row justify-between items-center mb-4">
                    <label class="text-sm font-medium text-heading">Documents</label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="courseData.parameters.document" class="sr-only peer">
                        <div class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"></div>
                    </label>
                </div>
                <div class="flex flex-row justify-between items-center mb-4">
                    <label class="text-sm font-medium text-heading">Questionnaire</label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="courseData.parameters.questionnaire" class="sr-only peer">
                        <div class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"></div>
                    </label>
                </div>
            </div>

            <hr class="border-t border-gray-200 mt-6 mb-4 mx-4" />

            <div class="flex flex-col-2 gap-4">
                <div class="flex justify-between items-center gap-4 my-4 w-full">
                    <label for="dropdown" class="text-sm font-medium text-heading">Catégorie</label>
                    <div class="relative">
                        <button data-dropdown-toggle="dropdownCategory" class="inline-flex items-center justify-center border hover:bg-primary-300 text-white bg-primary-900 shadow-xs font-medium rounded-base text-sm px-4 py-2.5" type="button">
                            {{courseData.category.nom || "Sélectionner une catégorie"}}
                            <Icon icon="mdi:chevron-down" class="ml-2 w-6 h-6" />
                        </button>
                        <div id="dropdownCategory" class="hidden absolute left-0 mt-1 z-10 bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44">
                            <ul class="p-2 text-sm text-body font-medium">
                                <li v-for="category in categories" :key="category.id">
                                    <button type="button" @click="selectCategory(category);" 
                                        class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">
                                        {{ category.nom }}
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="flex justify-between items-center gap-4 my-4 w-full">
                    <label for="dropdownSubcategory" class="text-sm font-medium text-heading">Sous-catégorie</label>
                    <div class="relative">
                        <button data-dropdown-toggle="dropdownSubcategory" class="inline-flex items-center justify-center border hover:bg-primary-300 text-white bg-primary-900 shadow-xs font-medium rounded-base text-sm px-4 py-2.5" type="button">
                            {{courseData.subCategory.nom || "Sélectionner une sous-catégorie"}}
                            <Icon icon="mdi:chevron-down" class="ml-2 w-6 h-6" />
                        </button>
                        <div id="dropdownSubcategory" class="hidden absolute left-0 mt-1 z-10 bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44">
                            <ul class="p-2 text-sm text-body font-medium">
                                <li v-for="subCategory in subCategories" :key="subCategory.id">
                                    <button type="button" @click="selectSubCategory(subCategory);" 
                                        class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">
                                        {{ subCategory.nom }}
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ETAPE OPTIONS -->
        <div v-if="etape==formulaireEtape.OPTIONS">
            <p class="text-subtitle my-4">Options supplémentaires</p>
            <div v-for="(option, index) in courseData.options" class="my-4">
                <OptionTemplate :optionModel="option" @remove-option="removeOption(index)"/>
            </div>
            <div class="flex pt-4 items-center">
                <div class="flex-grow border-t border-gray-700"></div>
                <span class="mx-4">
                    <button type="button" @click="handleModalState" class="bg-tertiary border border-default-medium text-heading text-sm rounded-full focus:border-tertiary-900 px-2.5 py-2.5">
                        <Icon icon="mdi:plus" class="w-4 h-4" />
                    </button>
                </span>
                <div class="flex-grow border-t border-gray-700"></div>
            </div>
            <div v-if="modal==optionModal.SELECTION" class="flex items-center justify-center z-50">
                <OptionList :elements="optionElements" @select-item="handleOptionSelection"/>
            </div>
            <div v-if="modal==optionModal.EXISTANT" class="flex items-center justify-center z-50">
                <OptionList :elements="optionModels.map(o => o.nom)" @select-item="handleOptionSelection"/>
            </div>
        </div>

        <!-- ETAPE AVERTISSEMENT -->
        <div v-if="etape==formulaireEtape.AVERTISSEMENT">
            <p class="text-subtitle mt-4">Avertissement</p>
            <p class="mb-4">Cette page apparaitra dès la sélection de la course. Elle sert à avertir les participants de risques potentiels.</p>
            <div class="flex flex-col-2 gap-4 h-128">
                <textarea type="text" id="avertissement" v-model="courseData.avertissement.contenu" class="bg-neutral-secondary-medium basis-2/3 border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="" required />
                <div class="basis-1/3">
                    <h2 class="text-sm font-medium text-heading mb-2.5">Mes modèles</h2>
                    <button v-for="avertissementModel in avertissementModels" :key="avertissementModel.name" type="button" @click="courseData.avertissement.contenu = avertissementModel.contenu" class="btn-model">
                        {{ avertissementModel.titre }}
                    </button>
                </div>
            </div>
        </div>

            <!-- ETAPE DOCUMENT -->
        <div v-if="etape==formulaireEtape.DOCUMENT">
            <p class="text-subtitle mt-4">Documents</p>
            <p class="mb-4">Décrivez quels documents doivent être fournis et quels type de personnes sont concernées.</p>
            <div class="flex flex-col-2 gap-4 h-128">
                <textarea type="text" id="documents" v-model="courseData.document.description" class="bg-neutral-secondary-medium basis-2/3 border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="" required />
                <div class="basis-1/3">
                    <h2 class="text-sm font-medium text-heading mb-2.5">Mes modèles</h2>
                    <button v-for="documentModel in documentModels" :key="documentModel.name" type="button" @click="courseData.document.description = documentModel.description" class="btn-model">
                        {{ documentModel.name }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ETAPE QUESTIONNAIRE -->
        <div v-if="etape==formulaireEtape.QUESTIONNAIRE">
            <p class="text-subtitle my-4">Questionnaires</p>
            <div v-for="(question, index) in courseData.questions" class="my-4">
                <QuestionTemplate :questionModel="question" @remove-question="courseData.questions.splice(index, 1)"/>
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
                <OptionList :elements="questionModels.map(q => q.enonce)" @select-item="handleOptionSelection"/>
            </div>
        </div>


        <!-- NAVIGATION ETAPES -->
        <div class="flex flex-row mt-6 gap-4">
            <button v-if="etapesActives.indexOf(etape) > 0" class="btn-accent-300" @click="etape = etapesActives[etapesActives.indexOf(etape) - 1]">
                Etape précédente
            </button>
            <button v-if="etapesActives.indexOf(etape) < etapesActives.length - 1" class="btn-tertiary ml-auto" @click="etape = etapesActives[etapesActives.indexOf(etape) + 1]">
                Etape suivante
            </button>
            <button v-else class="btn-tertiary ml-auto" @click="confirmationPopup=true">
                {{ isEditMode ? 'Enregistrer les modifications' : 'Créer la course' }}
            </button>
        </div>

        <PopupConfirmation v-if="confirmationPopup" @cancel="confirmationPopup = false" @confirm="insertCourse()"/>
        <PopupConfirmation v-if="dataInserted" :message="isEditMode ? 'La course a été modifiée avec succès !' : 'La course a été créée avec succès !'" :icon="'mdi:check'" :showButtons="false"/>
    </div>
</template>

<script>
import { Icon } from '@iconify/vue';
import { initDropdowns } from 'flowbite';
import PopupConfirmation from './PopupConfirmation.vue';
import OptionList from './OptionList.vue';
import OptionTemplate from './OptionTemplate.vue';
import QuestionTemplate from "./QuestionTemplate.vue";
import evenementOrganisateurService from '../services/evenementOrganisateurService';
import courseOrganisateurService from '../services/courseOrganisateurService';
import optionOrganisateurService from '../services/optionOrganisateurService';
import IndicateurEtapes from './IndicateurEtapes.vue';
import avertissementOrganisateurService from '../services/avertissementOrganisateurService';
import optionCourseService from '../services/optionCourseService';
import categorieOrganisateurService from '../services/categorieOrganisateurService';
import sousCategorieOrganisateurService from '../services/sousCategorieOrganisateurService';
import questionOrganisateurService from '../services/questionOrganisateurService';
import optionQuestionOrganisateurService from '../services/optionQuestionOrganisateurService';
import courseQuestionOrganisateurService from '../services/courseQuestionOrganisateurService';

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
        PopupConfirmation,
        OptionList,
        OptionTemplate,
        QuestionTemplate,
        IndicateurEtapes,
    },
    data() {
        return {
            formulaireEtape,
            optionModal,
            etape: formulaireEtape.GENERAL,
            modal: optionModal.FERMEE,
            confirmationPopup: false,
            dataInserted: false,
            evenements: [],
            formulaireEtapesLabels: ["Général", "Options supplémentaires"],
            typesCourse: [
                { name: "Individuel", id: 1 },
                { name: "Relais",     id: 2 },
                { name: "Groupe",     id: 3 },
            ],
            categories: [],
            subCategories: [],
            questionModels: [
                {
                    enonce: "Comment avez-vous connu l'évènement ?",
                    choix:  [
                        { texte_option: "Réseaux sociaux" },
                        { texte_option: "Bouche à oreille" },
                        { texte_option: "Autre" },
                    ],
                },
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
            optionElements: ["Existant", "Nouveau"],
            optionModels: [],
            avertissementModels: [],
            courseData: {
                name: "",
                event: { name: "", id: "" },
                date: { start: "", end: "", inscriptionStart: "", inscriptionEnd: "" },
                distance: "",
                tarif: "",
                tarifInfo: "",
                popupInfo: "",
                type: { name: "", id: "" },
                maxRunners: "",
                maxNbPersonne: "",
                dossard: { first: "", last: "" },
                age: { min: "", max: "", conditionMineur: "" },
                tempsMoyen: "",
                parameters: {
                    actif: false,
                    dossardPersonalise: false,
                    challenge: false,
                    avertissement: false,
                    document: false,
                    questionnaire: false,
                },
                category: { nom: "", id: "" },
                subCategory: { nom: "", id: "" },
                options: [],
                avertissement: { contenu: "", id:"" },
                questions: [],
                document: {
                    description: '',
                },
            },
        };
    },
    computed: {
        isEditMode() {
            return !!this.$route.query.id;
        },
        courseId() {
            return this.$route.query.id;
        },
        eventIdFromUrl() {
            return this.$route.query.idEvenement;
        },
        etapesActives() {
            const etapes = [formulaireEtape.GENERAL, formulaireEtape.OPTIONS];
            const labels = ["Général", "Options supplémentaires"];
            if (this.courseData.parameters.avertissement) {
                etapes.push(formulaireEtape.AVERTISSEMENT);
                labels.push("Avertissement");
            }
            if (this.courseData.parameters.document) {
                etapes.push(formulaireEtape.DOCUMENT);
                labels.push("Documents");
            }
            if (this.courseData.parameters.questionnaire) {
                etapes.push(formulaireEtape.QUESTIONNAIRE);
                labels.push("Questionnaire");
            }
            this.formulaireEtapesLabels = labels;
            return etapes.sort((a, b) => a - b);
        }
    },
    watch: {
        'courseData.date.start'(newStart) {
            if (!newStart) return;
            if (!this.courseData.date.end || this.courseData.date.end < newStart) {
                this.courseData.date.end = newStart;
            }
        },
        'courseData.date.inscriptionStart'(newStart) {
            if (!newStart) return;
            if (!this.courseData.date.inscriptionEnd || this.courseData.date.inscriptionEnd < newStart) {
                this.courseData.date.inscriptionEnd = newStart;
            }
        },
        'courseData.parameters.avertissement'(val) {
            if (!val) this.courseData.avertissement = { contenu: '' };
        },
           'courseData.parameters.document'(val) {
            if (!val) this.courseData.document = { name: '', description: '' };
        },
        'courseData.parameters.questionnaire'(val) {
            if (!val) this.courseData.questions = [];
        },
        courseId(newId) {
            if (newId) {
                this.chargerDonneesCourse();
            } else {
                this.resetFormulaire();
            }
        }
    },
    methods: {
        resetFormulaire() {
            this.courseData = {
                name: "", event: { name: "", id: "" },
                date: { start: "", end: "", inscriptionStart: "", inscriptionEnd: "" },
                distance: "", tarif: "", tarifInfo: "", popupInfo: "",
                type: { name: "", id: "" }, maxRunners: "",
                dossard: { first: "", last: "" },
                age: { min: "", max: "", conditionMineur: "" },
                tempsMoyen: "",
                parameters: { actif: false, dossardPersonalise: false, challenge: false, avertissement: false, document: false, questionnaire: false, },
                category: { nom: "", id: "" }, subCategory: { nom: "", id: "" },
                options: [],
                avertissement: { contenu: "", id: ""},
                document: { description: '' },
            };
            this.etape = formulaireEtape.GENERAL;
        },

        async chargerDonneesCourse() {
            try {
                const response = await courseOrganisateurService.getCourse(this.courseId);

                if (typeof response.data === 'string' && response.data.includes('<!DOCTYPE html>')) {
                    console.error("ERREUR : L'API a renvoyé une page HTML. La route Backend n'existe pas.");
                    return;
                }

                const course = response.data.course || response.data;

                this.courseData.name = course.nom || "";
                this.courseData.tarif = course.tarif || "";
                this.courseData.maxRunners = course.max_inscription || "";
                this.courseData.maxNbPersonne = course.max_nb_personne || "";
                this.courseData.dossard.first = course.premier_dossard || "";
                this.courseData.dossard.last = course.dernier_dossard || "";
                this.courseData.age.min = course.age_minimum || "";
                this.courseData.age.max = course.age_maximum || "";
                this.courseData.distance = course.distance || "";
                this.courseData.popupInfo = course.pop_info || "";
                this.courseData.category = course.categorie || "";
                this.courseData.subCategory = course.sous_categorie || "";

                if (course.type) {
                    this.courseData.type = { name: course.type, id: "" };
                }
                if (course.date_debut) {
                    this.courseData.date.start = String(course.date_debut).split('T')[0];
                }
                if (course.date_fin) {
                    this.courseData.date.end = String(course.date_fin).split('T')[0];
                }
                if (course.debut_inscription) {
                    this.courseData.date.inscriptionStart = String(course.debut_inscription).split('T')[0];
                }
                if (course.fin_inscription) {
                    this.courseData.date.inscriptionEnd = String(course.fin_inscription).split('T')[0];
                }

                this.courseData.parameters.actif = (course.is_actif == 1 || course.is_actif === true);
                this.courseData.parameters.challenge = (course.is_challenge == 1 || course.challenge === true);
                this.courseData.parameters.avertissement = (course.is_avertissement == 1 || course.is_avertissement === true);
                this.courseData.parameters.document = (course.is_document == 1 || course.is_document === true);
                this.courseData.parameters.questionnaire = (course.is_questionnaire == 1 || course.is_questionnaire === true);

                if (course.is_avertissement && course.avertissement) {
                    this.courseData.avertissement.contenu = course.avertissement.contenu;
                    this.courseData.avertissement.id = course.avertissement.id;
                }

                if (course.id_evenement && this.evenements.length > 0) {
                    this.courseData.event = this.evenements.find(e => e.id === course.id_evenement) || { name: "", id: "" };
                }
                if (course.options && Array.isArray(course.options)) {
                    this.courseData.options = course.options.map(opt => {
                        return {
                            id: opt.id,
                            nom: opt.nom,
                            description: opt.description,
                            tarif: opt.tarif,
                            type: opt.type,
                            // Reconstitution de l'objet pour OptionTemplate
                            quantifiable: opt.quantifiable ? {
                                quantiteMin: opt.quantifiable.quantiteMin,
                                quantiteMax: opt.quantifiable.quantiteMax
                            } : { quantiteMin: 0, quantiteMax: 0 }
                        };
                    });
                    console.log("Options injectées dans le formulaire :", this.courseData.options);
                }

                if (course.is_document === 1 && course.document_description) {
                    this.courseData.document.description = course.document_description;
                }

                console.log("Course chargée avec succès :", course);
            } catch (e) {
                console.error("Erreur globale chargement course:", e);
            }
        },
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
                    this.courseData.options.push({
                        nom: '',
                        description: '',
                        tarif: '',
                        quantifiable: {
                            quantiteMin: 0,
                            quantiteMax: 0,
                        }
                    });
                else if(this.etape === formulaireEtape.QUESTIONNAIRE) 
                    this.courseData.questions.push({
                        enonce: '',
                        choix: []
                    });
                this.modal = optionModal.FERMEE; 
            }
            else if(this.modal === optionModal.EXISTANT) {
                if(this.etape === formulaireEtape.OPTIONS) {
                    const { id, ...optionSansId } = this.optionModels.find(o => o.nom === option);
                    this.courseData.options.push(optionSansId);
                }
                else if(this.etape === formulaireEtape.QUESTIONNAIRE) 
                    this.courseData.questions.push(this.questionModels.find(q => q.enonce === option));
                this.modal = optionModal.FERMEE;
            }
        },
        selectEvent(event) {
            this.courseData.event = event;
            FlowbiteInstances.getInstance('Dropdown', 'dropdownEvent').hide();
        },
        selectType(type) {
            this.courseData.type = type;
            FlowbiteInstances.getInstance('Dropdown', 'dropdownType').hide();
        },
        selectCategory(category) {
            this.courseData.category = category;
            FlowbiteInstances.getInstance('Dropdown', 'dropdownCategory').hide();
        },
        selectSubCategory(subCategory) {
            this.courseData.subCategory = subCategory;
            FlowbiteInstances.getInstance('Dropdown', 'dropdownSubcategory').hide();
        },
        async removeOption(index) {
            const option = this.courseData.options[index];
            if (option.id) {
                await optionCourseService.deleteOptionCourse({ 
                    id_course: this.courseId, 
                    id_option: option.id 
                });
                await optionOrganisateurService.deleteOption(option.id);
            }
            this.courseData.options.splice(index, 1);
        },
        buildOptionPayload(option) {
            const payload = {
                nom: option.nom,
                description: option.description,
                tarif: option.tarif,
                type: option.type,
                modele: false,
            };
            if (option.type === 'Quantifiable') {
                payload.quantiteMin = parseInt(option.quantifiable?.quantiteMin) || 0;
                payload.quantiteMax = parseInt(option.quantifiable?.quantiteMax) || 0;
            }
            return payload;
        },
        async insertCourse() {
            try {
                // 1. Gestion de l'avertissement
                let id_avertissement = this.courseData.avertissement.id || null;

                if (this.courseData.parameters.avertissement && this.courseData.avertissement.contenu) {
                    const avertissementPayload = {
                        titre: this.courseData.name,
                        contenu: this.courseData.avertissement.contenu
                    };
                    if (this.isEditMode && id_avertissement) {
                        await avertissementOrganisateurService.modifyAvertissement(id_avertissement, avertissementPayload);
                    } else {
                        const avertissementResponse = await avertissementOrganisateurService.createAvertissement(avertissementPayload);
                        id_avertissement = avertissementResponse.data.avertissement.id;
                    }
                }

                // 2. Payload course
                const payload = {
                    id_evenement:      this.courseData.event.id,
                    nom:               this.courseData.name,
                    date_debut:        this.courseData.date.start || null,
                    date_fin:          this.courseData.date.end || null,
                    debut_inscription: this.courseData.date.inscriptionStart || null,
                    fin_inscription:   this.courseData.date.inscriptionEnd || null,
                    tarif:             this.courseData.tarif,
                    max_inscription:   this.courseData.maxRunners,
                    max_nb_personne:   this.courseData.maxNbPersonne || null,
                    premier_dossard:   this.courseData.dossard.first,
                    dernier_dossard:   this.courseData.dossard.last,
                    age_minimum:       this.courseData.age.min,
                    age_maximum:       this.courseData.age.max,
                    distance:          this.courseData.distance,
                    status:            "actif",
                    type:              this.courseData.type.name,
                    is_actif:          Boolean(this.courseData.parameters.actif),
                    is_dossard:        Boolean(this.courseData.parameters.dossardPersonalise),
                    is_avertissement:  Boolean(this.courseData.parameters.avertissement),
                    is_challenge:      Boolean(this.courseData.parameters.challenge),
                    is_document:       Boolean(this.courseData.parameters.document),
                    is_questionnaire:  Boolean(this.courseData.parameters.questionnaire),
                    id_avertissement:  id_avertissement,
                    id_categorie:      this.courseData.category.id,
                    id_sous_categorie: this.courseData.subCategory.id,
                };

                // 3. Création ou modification de la course
                let response;
                if (this.isEditMode) {
                    response = await courseOrganisateurService.modifyCourse(this.courseId, payload);
                } else {
                    response = await courseOrganisateurService.createCourse(payload);
                }

                const courseId = this.isEditMode ? this.courseId : response.data.course.id;

                // 4a. Options existantes → modification
                const optionsExistantes = this.courseData.options.filter(o => o.id);
                for (const option of optionsExistantes) {
                    await optionOrganisateurService.modifyOption(option.id, this.buildOptionPayload(option));
                }

                // 4b. Nouvelles options → création + association
                const optionsNouvelles = this.courseData.options.filter(o => !o.id);
                for (const option of optionsNouvelles) {
                    const optionResponse = await optionOrganisateurService.createOption(this.buildOptionPayload(option));
                    await optionCourseService.createOptionCourse({
                        id_course: courseId,
                        id_option: optionResponse.data.option.id
                    });
                }

                // 5a. Questions existantes → modification enonce + choix
                const questionsExistantes = this.courseData.questions.filter(q => q.id);
                for (const question of questionsExistantes) {
                    // Mise à jour de la table Question
                    await questionOrganisateurService.modifyQuestion(question.id, {
                        enonce: question.enonce,
                        modele: false,
                    });

                    // Mise à jour de la table OptionQuestion
                    for (const choix of question.choix ?? []) {
                        if (choix.id) {
                            await optionQuestionOrganisateurService.modifyChoix(choix.id, {
                                texte_option: choix.texte_option,
                            });
                        } else {
                            await optionQuestionOrganisateurService.createChoix(question.id, {
                                texte_option: choix.texte_option,
                            });
                        }
                    }
                }

                // 5b. Nouvelles questions → Question + OptionQuestion + CourseQuestion
                const questionsNouvelles = this.courseData.questions.filter(q => !q.id);
                const nouvellesAvecId = []; // pour mémoriser les ids créés

                for (const question of questionsNouvelles) {

                    // 1. Insérer dans la table Question
                    const questionResponse = await questionOrganisateurService.createQuestion({
                        enonce:      question.enonce,
                        modele:      false,
                        ids_courses: [courseId], // ← liaison CourseQuestion faite ici dans le controller
                    });
                    const questionId = questionResponse.data.question.id;
                    nouvellesAvecId.push({ ...question, id: questionId });

                    // 2. Insérer dans la table OptionQuestion (choix de réponse)
                    for (const choix of question.choix ?? []) {
                        await optionQuestionOrganisateurService.createChoix(questionId, {
                            texte_option: choix.texte_option,
                        });
                    }
                }

                // 5c. Réordonner toutes les questions (existantes + nouvelles) dans CourseQuestion
                const toutesLesQuestions = [
                    ...this.courseData.questions.filter(q => q.id),
                    ...nouvellesAvecId,
                ];

                if (toutesLesQuestions.length > 0) {
                    await courseQuestionOrganisateurService.reordonnerQuestions(courseId, {
                        questions: toutesLesQuestions.map((q, index) => ({
                            id_question: q.id,
                            ordre:       index + 1,
                        })),
                    });
                }
                this.confirmPopup();
                console.log(response.data);

            } catch (e) {
                console.log("Erreur:", e.response?.data);
            }
        },

        confirmPopup() {
            this.confirmationPopup = false;
            this.dataInserted = true;
            setTimeout(() => {
                this.dataInserted = false;
                if (this.isEditMode) {
                    if (this.courseData.event.id) {
                        this.$router.push(`/organisateur/evenements/${this.courseData.event.id}/courses`);
                    } else {
                        this.$router.push(`/organisateur/evenements`);
                    }
                }
            }, 2000);
        }
    },
    async mounted() {
        initDropdowns();

        try {
            const response = await evenementOrganisateurService.getAllEvenements();
            this.evenements = response.data;
        } catch (e) {
            console.log("Erreur lors de la récupération de l'évènement ", e);
        }

        try {
            const response = await optionOrganisateurService.getAllOptions();
            this.optionModels = response.data;
        } catch (e) {
            console.error("Erreur lors de la récupération des options: ", e);
        }

        try {
            const response = await categorieOrganisateurService.getAllCategorie();
            this.categories = response.data;
        } catch (e) {
            console.error("Erreur lors de la récupération des categories: ", e);
        }
        try {
            const response = await sousCategorieOrganisateurService.getAllSousCategorie();
            this.subCategories = response.data;
        } catch (e) {
            console.error("Erreur lors de la récupération des sous categories: ", e);
        }

        try{
            const response = await avertissementOrganisateurService.getAllAvertissement();
            this.avertissementModels = response.data;
            console.log(response.data);
        } catch (e) {
            console.error("Erreur lors de la récupération des avertissements: ", e);
        }

        if (this.isEditMode) {
            await this.chargerDonneesCourse();
        }
    }
}
</script>