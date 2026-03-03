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

            <div id="datepicker" date-rangepicker class="flex items-center gap-4">
                <div class="w-full">
                    <label for="datepicker" class="block mb-2.5 text-sm font-medium text-heading">Date de début</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                            <Icon icon="mdi:calendar" class="w-4 h-4 text-body" />
                        </div>
                        <input id="datepicker-start" name="dateStart" type="text" class="block w-full ps-9 pe-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 py-2.5 shadow-xs placeholder:text-body" placeholder="Select date start">
                    </div>
                </div>
                <div class="w-full">
                    <label for="datepicker-end" class="block mb-2.5 text-sm font-medium text-heading">Date de fin</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                            <Icon icon="mdi:calendar" class="w-4 h-4 text-body" />
                        </div>
                        <input id="datepicker-end" name="dateEnd" type="text" class="block w-full ps-9 pe-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 py-2.5 shadow-xs placeholder:text-body" placeholder="Select date end">
                    </div>
                </div>
            </div>
            <div id="inscriptionpicker" date-rangepicker class="flex justify-between items-center gap-4 my-4">
                <label for="inscriptionpicker" class="block mb-2.5 text-sm font-medium text-heading">Interval d'inscription</label>
                <div class="flex row gap-4 basis-1/2">
                    <div class="relative">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                            <Icon icon="mdi:calendar" class="w-4 h-4 text-body" />
                        </div>
                        <input id="inscriptionpicker-start" name="inscriptionStart" type="text" class="block w-full ps-9 pe-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 py-2.5 shadow-xs placeholder:text-body" placeholder="Select date start">
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                            <Icon icon="mdi:calendar" class="w-4 h-4 text-body" />
                        </div>
                        <input id="inscriptionpicker-end" name="inscriptionEnd" type="text" class="block w-full ps-9 pe-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 py-2.5 shadow-xs placeholder:text-body" placeholder="Select date end">
                    </div>
                </div>
            </div>

            <hr class="border-t border-gray-200 mt-6 mb-4 mx-4" />

            <div class="flex gap-4 mb-4">
                <div class="basis-1/2">
                    <label for="startTime" class="block mb-2 text-sm font-medium text-heading">Heure départ</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 end-0 top-0 flex items-center pe-3.5 pointer-events-none">
                        <Icon icon="mdi:access-time" class="w-4 h-4 text-body" />
                        </div>
                        <input type="time" id="startTime" v-model="courseData.time.start" class="block w-full p-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body" min="00:00" max="24:00" required />
                    </div>
                </div>
                <div class="basis-1/2">
                    <label for="endTime" class="block mb-2 text-sm font-medium text-heading">Heure fin</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 end-0 top-0 flex items-center pe-3.5 pointer-events-none">
                            <Icon icon="mdi:access-time" class="w-4 h-4 text-body" />
                        </div>
                        <input type="time" id="endTime" v-model="courseData.time.end" class="block w-full p-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand shadow-xs placeholder:text-body" min="00:00" max="24:00" required />
                    </div>
                </div>
            </div>
            <div class="flex flex-col-3 gap-4 mb-4">
                <div class="w-full">
                    <label for="distance" class="block mb-2.5 text-sm font-medium text-heading">Distance</label>
                    <input type="text" id="distance" v-model="courseData.distance" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="" required />
                </div>
                <div class="w-full">
                    <label for="tarif" class="block mb-2.5 text-sm font-medium text-heading">Tarif</label>
                    <input type="text" id="tarif" v-model="courseData.tarif" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" required />
                </div>
                <div class="w-full">
                    <label for="tarifInfo" class="block mb-2.5 text-sm font-medium text-heading">Information tarif</label>
                    <input type="text" id="tarifInfo" v-model="courseData.tarifInfo" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" required />
                </div>
            </div>
            <div>
                <label for="popupInfo" class="block mb-2.5 text-sm font-medium text-heading">Popup info course</label>
                <textarea id="popupInfo" v-model="courseData.popupInfo" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" required></textarea>
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
                <input type="text" id="maxRunners" v-model="courseData.maxRunners" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" required />
            </div>
            <div class="flex flex-col-2 gap-4 mb-4">
                <div class="w-full">
                    <label for="firstDossard" class="block mb-2.5 text-sm font-medium text-heading">Premier dossard</label>
                    <input type="text" id="firstDossard" v-model="courseData.dossard.first" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="" required />
                </div>
                <div class="w-full">
                    <label for="lastDossard" class="block mb-2.5 text-sm font-medium text-heading">Dernier dossard</label>
                    <input type="text" id="lastDossard" v-model="courseData.dossard.last" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" required />
                </div>
            </div>
            <div class="flex flex-col-3 gap-4 mb-4">
                <div class="w-full">
                    <label for="ageMin" class="block mb-2.5 text-sm font-medium text-heading">Limite âge min</label>
                    <input type="text" id="ageMin" v-model="courseData.age.min" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="" required />
                </div>
                <div class="w-full">
                    <label for="ageMax" class="block mb-2.5 text-sm font-medium text-heading">Limite âge max</label>
                    <input type="text" id="ageMax" v-model="courseData.age.max" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" required />
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
            </div>

            <hr class="border-t border-gray-200 mt-6 mb-4 mx-4" />

            <div class="flex flex-col-2 gap-4">
                <div class="flex justify-between items-center gap-4 my-4 w-full">
                    <label for="dropdown" class="text-sm font-medium text-heading">Catégorie</label>
                    <div class="relative">
                        <button data-dropdown-toggle="dropdownCategory" class="inline-flex items-center justify-center border hover:bg-primary-300 text-white bg-primary-900 shadow-xs font-medium rounded-base text-sm px-4 py-2.5" type="button">
                            {{courseData.category.name || "Sélectionner une catégorie"}}
                            <Icon icon="mdi:chevron-down" class="ml-2 w-6 h-6" />
                        </button>
                        <div id="dropdownCategory" class="hidden absolute left-0 mt-1 z-10 bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44">
                            <ul class="p-2 text-sm text-body font-medium">
                                <li v-for="category in categories" :key="category.id">
                                    <button type="button" @click="selectCategory(category);" 
                                        class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">
                                        {{ category.name }}
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
                            {{courseData.subCategory.name || "Sélectionner une sous-catégorie"}}
                            <Icon icon="mdi:chevron-down" class="ml-2 w-6 h-6" />
                        </button>
                        <div id="dropdownSubcategory" class="hidden absolute left-0 mt-1 z-10 bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44">
                            <ul class="p-2 text-sm text-body font-medium">
                                <li v-for="subCategory in subCategories" :key="subCategory.id">
                                    <button type="button" @click="selectSubCategory(subCategory);" 
                                        class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">
                                        {{ subCategory.name }}
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
                <OptionTemplate :optionModel="option" @remove-option="courseData.options.splice(index, 1)"/>
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
                <textarea type="text" id="avertissement" v-model="courseData.avertissement.description" class="bg-neutral-secondary-medium basis-2/3 border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="" required />
                <div class="basis-1/3">
                    <h2 class="text-sm font-medium text-heading mb-2.5">Mes modèles</h2>
                    <button v-for="avertissementModel in avertissementModels" :key="avertissementModel.name" type="button" @click="courseData.avertissement.description = avertissementModel.description" class="btn-model">
                        {{ avertissementModel.name }}
                    </button>
                </div>
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
import { initDatepickers, initDropdowns } from 'flowbite';
import PopupConfirmation from './PopupConfirmation.vue';
import OptionList from './OptionList.vue';
import OptionTemplate from './OptionTemplate.vue';
import evenementOrganisateurService from '../services/evenementOrganisateurService';
import courseOrganisateurService from '../services/courseOrganisateurService';
import optionOrganisateurService from '../services/optionOrganisateurService';
import IndicateurEtapes from './IndicateurEtapes.vue';

const formulaireEtape = {
    GENERAL: 1,
    OPTIONS: 2,
    AVERTISSEMENT: 3,
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
                { name: "Type de course 1", id: 1 },
                { name: "Type de course 2", id: 2 },
            ],
            categories: [
                { name: "Catégorie 1", id: 1 },
                { name: "Catégorie 2", id: 2 },
            ],
            subCategories: [
                { name: "Sous-catégorie 1", id: 1 },
                { name: "Sous-catégorie 2", id: 2 },
            ],
            optionElements: ["Existant", "Nouveau"],
            optionModels: [],
            avertissementModels: [
                {
                    name: "Course des ponts",
                    description: "Course urbaine avec de nombreux ponts à traverser, ce qui peut présenter un risque de chute en cas de pluie."
                },
                {
                    name: "Antigel",
                    description: "En raison de conditions météorologiques hivernales, du verglas peut être présent sur le parcours, ce qui peut rendre certaines sections glissantes."
                }
            ],
            courseData: {
                name: "",
                event: { name: "", id: "" },
                date: { start: "", end: "", inscriptionStart: "", inscriptionEnd: "" },
                time: { start: "", end: "" },
                distance: "",
                tarif: "",
                tarifInfo: "",
                popupInfo: "",
                type: { name: "", id: "" },
                maxRunners: "",
                dossard: { first: "", last: "" },
                age: { min: "", max: "", conditionMineur: "" },
                tempsMoyen: "",
                parameters: {
                    actif: false,
                    dossardPersonalise: false,
                    challenge: false,
                    avertissement: false,
                },
                category: { name: "", id: "" },
                subCategory: { name: "", id: "" },
                options: [],
                avertissement: { description: "" },
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
            this.formulaireEtapesLabels = labels;
            return etapes.sort((a, b) => a - b);
        }
    },
    watch: {
        'courseData.parameters.avertissement'(val) {
            if (!val) this.courseData.avertissement = { description: '' };
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
                time: { start: "", end: "" },
                distance: "", tarif: "", tarifInfo: "", popupInfo: "",
                type: { name: "", id: "" }, maxRunners: "",
                dossard: { first: "", last: "" },
                age: { min: "", max: "", conditionMineur: "" },
                tempsMoyen: "",
                parameters: { actif: false, dossardPersonalise: false, challenge: false, avertissement: false },
                category: { name: "", id: "" }, subCategory: { name: "", id: "" },
                options: [],
                avertissement: { description: "" },
            };
            this.etape = formulaireEtape.GENERAL;
            ['datepicker-start', 'datepicker-end', 'inscriptionpicker-start', 'inscriptionpicker-end'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
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
                this.courseData.dossard.first = course.premier_dossard || "";
                this.courseData.dossard.last = course.dernier_dossard || "";
                this.courseData.age.min = course.age_minimum || "";
                this.courseData.age.max = course.age_maximum || "";
                this.courseData.distance = course.distance || "";
                this.courseData.popupInfo = course.pop_info || "";

                if (course.type) {
                    this.courseData.type = { name: course.type, id: "" };
                }
                if (course.heure_depart) {
                    this.courseData.time.start = course.heure_depart.substring(0, 5);
                }
                if (course.heure_fin) {
                    this.courseData.time.end = course.heure_fin.substring(0, 5);
                }
                if (course.date_debut) {
                    this.courseData.date.start = new Date(course.date_debut);
                    const el = document.getElementById('datepicker-start');
                    if (el) el.value = course.date_debut;
                }
                if (course.date_fin) {
                    this.courseData.date.end = new Date(course.date_fin);
                    const el = document.getElementById('datepicker-end');
                    if (el) el.value = course.date_fin;
                }
                if (course.debut_inscription) {
                    this.courseData.date.inscriptionStart = new Date(course.debut_inscription);
                    const el = document.getElementById('inscriptionpicker-start');
                    if (el) el.value = course.debut_inscription;
                }
                if (course.fin_inscription) {
                    this.courseData.date.inscriptionEnd = new Date(course.fin_inscription);
                    const el = document.getElementById('inscriptionpicker-end');
                    if (el) el.value = course.fin_inscription;
                }

                this.courseData.parameters.actif = (course.is_actif == 1 || course.is_actif === true);
                this.courseData.parameters.challenge = (course.challenge == 1 || course.challenge === true);
                this.courseData.parameters.avertissement = (course.is_avertissement == 1 || course.is_avertissement === true);

                if (course.is_avertissement && course.avertissement_description) {
                    this.courseData.avertissement.description = course.avertissement_description;
                }

                if (course.id_evenement && this.evenements.length > 0) {
                    this.courseData.event = this.evenements.find(e => e.id === course.id_evenement) || { name: "", id: "" };
                }

                console.log("Course chargée avec succès :", course);
            } catch (e) {
                console.error("Erreur chargement course:", e);
            }
        },

        handleModalState() {
            this.modal = this.modal === optionModal.FERMEE ? optionModal.SELECTION : optionModal.FERMEE;
        },
        handleOptionSelection(option) {
            if (option === "Existant") {
                this.modal = optionModal.EXISTANT;
            } else if (option === "Nouveau") {
                this.courseData.options.push({
                    name: '',
                    description: '',
                    prix: '',
                    quantifiable: { quantiteMin: '', quantiteMax: '' }
                });
                this.modal = optionModal.FERMEE;
            } else if (this.modal === optionModal.EXISTANT) {
                this.courseData.options.push(this.optionModels.find(o => o.nom === option));
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

        async insertCourse() {
            try {
                const payload = {
                    id_evenement:           this.courseData.event.id,
                    nom:                    this.courseData.name,
                    date_debut:             this.courseData.date.start?.toISOString ? this.courseData.date.start.toISOString().split('T')[0] : null,
                    date_fin:               this.courseData.date.end?.toISOString ? this.courseData.date.end.toISOString().split('T')[0] : null,
                    debut_inscription:      this.courseData.date.inscriptionStart?.toISOString ? this.courseData.date.inscriptionStart.toISOString().split('T')[0] : null,
                    fin_inscription:        this.courseData.date.inscriptionEnd?.toISOString ? this.courseData.date.inscriptionEnd.toISOString().split('T')[0] : null,
                    tarif:                  this.courseData.tarif,
                    max_inscription:        this.courseData.maxRunners,
                    premier_dossard:        this.courseData.dossard.first,
                    dernier_dossard:        this.courseData.dossard.last,
                    age_minimum:            this.courseData.age.min,
                    age_maximum:            this.courseData.age.max,
                    distance:               this.courseData.distance,
                    heure_depart:           this.courseData.time.start,
                    heure_fin:              this.courseData.time.end,
                    challenge:              Boolean(this.courseData.parameters.challenge),
                    status:                 "actif",
                    type:                   this.courseData.type.name,
                    is_actif:               Boolean(this.courseData.parameters.actif),
                    is_avertissement:       this.courseData.parameters.avertissement ? 1 : 0,
                };

                if (this.courseData.parameters.avertissement) {
                    payload.avertissement_description = this.courseData.avertissement.description;
                }
                if (this.courseData.options.length > 0) {
                    payload.options = JSON.stringify(this.courseData.options);
                }

                let response;
                if (this.isEditMode) {
                    response = await courseOrganisateurService.modifyCourse(this.courseId, payload);
                } else {
                    response = await courseOrganisateurService.createCourse(payload);
                }

                if (response) {
                    this.confirmPopup();
                }
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
        initDatepickers();
        initDropdowns();

        const datepickerFields = [
            { id: 'datepicker-start',        key: 'start' },
            { id: 'datepicker-end',          key: 'end' },
            { id: 'inscriptionpicker-start', key: 'inscriptionStart' },
            { id: 'inscriptionpicker-end',   key: 'inscriptionEnd' },
        ];
        datepickerFields.forEach(({ id, key }) => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('changeDate', (e) => {
                    this.courseData.date[key] = e.detail.date;
                });
            }
        });

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

        if (this.isEditMode) {
            await this.chargerDonneesCourse();
        }
    }
}
</script>