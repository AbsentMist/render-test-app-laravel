<template>
    <Title :texte="`Tableau de bord : courses ${nomEvenement}`" />
    <div class="p-6 relative">
        <button
            @click="$router.push(`/organisateur/formulaires?onglet=Course&idEvenement=${idEvenement}`)"
            class="btn-tertiary px-4 py-2 rounded-lg inline-block mb-6"
        >
            Nouveau
        </button>
        <p v-if="erreur" class="text-accent text-label mb-4">{{ erreur }}</p>
        <div v-if="chargement" class="text-body text-center py-8">
            Chargement des courses...
        </div>
        <div
            v-else
            class="overflow-x-auto rounded-xl border border-default-medium"
        >
            <table class="w-full text-sm text-left text-body">
                <thead
                    class="bg-neutral-secondary-medium text-heading text-xs uppercase"
                >
                    <tr>
                        <th class="px-4 py-3">Nom</th>
                        <th class="px-4 py-3">Date evenement</th>
                        <th class="px-4 py-3">Date inscription début</th>
                        <th class="px-4 py-3">Date inscription fin</th>
                        <th class="px-4 py-3 text-center">Actif</th>
                        <th class="px-4 py-3 text-center">Interne</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="courses.length === 0">
                        <td colspan="6" class="text-center px-4 py-6 text-body">
                            Aucune course trouvée.
                        </td>
                    </tr>
                    <tr
                        v-for="course in courses"
                        :key="course.id"
                        class="border-t border-default-medium hover:bg-neutral-secondary-medium transition-colors"
                    >
                        <td class="px-4 py-3 font-medium text-heading">
                            {{ course.nom }}
                        </td>
                        <td v-if="course.date_debut === course.date_fin" class="px-4 py-3">
                            {{ formaterDate(course.date_debut) }}
                        </td>
                        <td v-else class="px-4 py-3">
                            {{ formaterDate(course.date_debut).split('.')[0] }} - {{ formaterDate(course.date_fin) }}
                        </td>
                        <td class="px-4 py-3">
                            {{ formaterDate(course.debut_inscription) }}
                        </td>
                        <td class="px-4 py-3">
                            {{ formaterDate(course.fin_inscription) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <Icon
                                v-if="course.is_actif"
                                icon="mdi:check"
                                class="w-5 h-5 text-green-500 mx-auto"
                            />
                            <Icon
                                v-else
                                icon="mdi:close"
                                class="w-5 h-5 text-accent mx-auto"
                            />
                        </td>
                        <td class="px-4 py-3 text-center">
                            <Icon
                                v-if="course.is_interne"
                                icon="mdi:check"
                                class="w-5 h-5 text-green-500 mx-auto"
                            />
                            <Icon
                                v-else
                                icon="mdi:close"
                                class="w-5 h-5 text-accent mx-auto"
                            />
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <button
                                        v-if="aQuestionnaire(course)"
                                        @click="afficherQuestion(course)"
                                        class="p-1.5 rounded-lg text-primary hover:bg-tertiary transition-colors"
                                        title="Voir les résultats du questionnaire"
                                    >
                                        <Icon
                                            icon="lucide:circle-question-mark"
                                            class="w-4 h-4"
                                        />
                                    </button>
                                    <button
                                        @click="modifierCourse(course)"
                                        class="p-1.5 rounded-lg text-primary hover:bg-tertiary transition-colors"
                                        title="Modifier"
                                    >
                                        <Icon
                                            icon="lucide:square-pen"
                                            class="w-4 h-4"
                                        />
                                    </button>
                                    <!-- Bouton codes de rabais -->
                                    <button
                                        @click="ouvrirCodesRabais(course)"
                                        class="p-1.5 rounded-lg text-green-600 hover:text-primary hover:bg-tertiary transition-colors"
                                        title="Codes de rabais"
                                    >
                                        <Icon
                                            icon="mdi:tag-multiple-outline"
                                            class="w-4 h-4"
                                        />
                                    </button>
                                    <!-- Bouton codes dossard -->
                                    <button
                                        @click="ouvrirCodesDossard(course)"
                                        class="p-1.5 rounded-lg text-blue-500 hover:text-primary hover:bg-tertiary transition-colors"
                                        title="Codes dossard personnalisés"
                                    >
                                        <Icon
                                            icon="mdi:badge-account-outline"
                                            class="w-4 h-4"
                                        />
                                    </button>
                                    <button
                                        @click="ouvrirResultats(course)"
                                        class="p-1.5 rounded-lg text-yellow-500 hover:text-primary hover:bg-tertiary transition-colors"
                                        title="Résultats"
                                    >
                                        <Icon
                                            icon="mdi:medal-outline"
                                            class="w-4 h-4"
                                        />
                                    </button>
                                </div>
                                <div class="relative inline-block">
                                    <button
                                        :ref="
                                            (el) => {
                                                optionButtonRefs[course.id] =
                                                    el;
                                            }
                                        "
                                        @click="toggleOptionMenu(course.id)"
                                        class="p-1.5 ml-1 rounded-lg text-primary hover:bg-tertiary transition-colors"
                                        title="Afficher les actions supplémentaires"
                                    >
                                        <Icon
                                            icon="lucide:ellipsis-vertical"
                                            class="w-4 h-4"
                                        />
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <OptionList
            v-if="activeOptionCourseId !== null"
            :style="optionListStyle"
            placement="none"
            class="fixed z-50"
            :elements="optionElements"
            @select-item="
                (option) =>
                    handleOptionSelection(
                        findCourseById(activeOptionCourseId),
                        option,
                    )
            "
        />

        <PopupConfirmation
            v-if="courseASupprimer"
            icon="mdi:alert-circle-outline"
            :message="`Voulez-vous vraiment supprimer la course ${courseASupprimer.nom} ? Cette action est irréversible.`"
            @confirm="supprimerCourse"
            @cancel="courseASupprimer = null"
        />

        <PopupQuestionnaireResultat
            v-if="courseQuestionnaireSelectionnee"
            :course="courseQuestionnaireSelectionnee"
            @close="fermerQuestionnaire"
        />

        <!-- Popup codes de rabais -->
        <div
            v-if="courseCodesRabais"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
        >
            <div
                class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 flex flex-col overflow-hidden max-h-[80vh]"
            >
                <div
                    class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-neutral-secondary-medium"
                >
                    <div>
                        <p class="text-sm font-semibold text-heading">
                            Codes de rabais
                        </p>
                        <p class="text-xs text-body mt-0.5">
                            {{ courseCodesRabais.nom }}
                        </p>
                    </div>
                    <button
                        @click="courseCodesRabais = null"
                        class="text-body hover:text-heading transition-colors"
                    >
                        <Icon icon="mdi:close" class="w-5 h-5" />
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-6">
                    <GestionCodesRabais :idCourse="courseCodesRabais.id" />
                </div>
            </div>
        </div>

        <!-- Popup codes dossard -->
        <div
            v-if="courseCodesDossard"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
        >
            <div
                class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 flex flex-col overflow-hidden max-h-[80vh]"
            >
                <div
                    class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-neutral-secondary-medium"
                >
                    <div>
                        <p class="text-sm font-semibold text-heading">
                            Codes dossard personnalisés
                        </p>
                        <p class="text-xs text-body mt-0.5">
                            {{ courseCodesDossard.nom }}
                        </p>
                    </div>
                    <button
                        @click="courseCodesDossard = null"
                        class="text-body hover:text-heading transition-colors"
                    >
                        <Icon icon="mdi:close" class="w-5 h-5" />
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-6">
                    <GestionCodesDossard :idCourse="courseCodesDossard.id" />
                </div>
            </div>
        </div>
        <!-- Popup résultats -->
        <div
            v-if="courseResultats"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
        >
            <div
                class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl mx-4 flex flex-col overflow-hidden max-h-[85vh]"
            >
                <div
                    class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-neutral-secondary-medium"
                >
                    <div>
                        <p class="text-sm font-semibold text-heading">
                            Résultats
                        </p>
                        <p class="text-xs text-body mt-0.5">
                            {{ courseResultats.nom }}
                        </p>
                    </div>
                    <button
                        @click="courseResultats = null"
                        class="text-body hover:text-heading transition-colors"
                    >
                        <Icon icon="mdi:close" class="w-5 h-5" />
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-6">
                    <ImportResultats :idCourse="courseResultats.id" />
                </div>
            </div>
        </div>

        <!-- Popup duplication événement -->
        <div
            v-if="popupDuplication.visible"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
        >
            <div
                class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 flex flex-col overflow-hidden max-h-[80vh]"
            >
                <div
                    class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-neutral-secondary-medium"
                >
                    <div>
                        <p class="text-sm font-semibold text-heading">
                            Dupliquer l'événement
                        </p>
                    </div>
                    <button
                        @click="popupDuplication.visible = false"
                        class="text-body hover:text-heading transition-colors"
                    >
                        <Icon icon="mdi:close" class="w-5 h-5" />
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-6">
                    <div class="flex flex-row items-center justify-between mb-4">
                        <label class="flex items-center">
                            <span class="text-sm font-medium text-heading">
                                Dupliquer dans l'événement actuel
                            </span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="popupDuplication.evenementActuel"
                            class="sr-only peer"
                        />
                        <div
                            class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"
                        ></div>
                    </label>
                    </div>
                    <div v-if="!popupDuplication.evenementActuel" class="flex flex-row gap-4 w-full">
                        <div class="basis-3/4">
                            <label class="text-sm font-medium text-heading block mb-2">Nom de l'événement</label>
                            <div class="relative w-full">
                                <button
                                    ref="buttonNomRef"
                                    @click="updateDropdownPosition('nom'); popupDuplication.dropdownNomOpen = !popupDuplication.dropdownNomOpen"
                                    class="w-full inline-flex items-center justify-between shadow-xs font-medium text-sm px-4 py-2.5"
                                    :class="[
                                        popupDuplication.nomEvenement
                                            ? 'border-b-2 text-primary border-tertiary hover:bg-gray-100 rounded-t-base'
                                            : 'hover:bg-primary-300 text-white bg-primary-900 shadow-xs font-medium rounded-base text-sm px-4 py-2.5',
                                    ]"
                                    type="button"
                                >
                                    <span>{{
                                        popupDuplication.nomEvenement ||
                                        "Sélectionner un événement"
                                    }}</span>
                                    <Icon
                                        icon="mdi:chevron-down"
                                        class="ml-2 w-6 h-6 flex-shrink-0"
                                    />
                                </button>
                                <div
                                    v-if="popupDuplication.dropdownNomOpen"
                                    class="fixed z-[60] bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg max-h-64 overflow-y-auto"
                                    :style="popupDuplication.dropdownNomStyle"
                                >
                                    <ul class="p-2 text-sm text-body font-medium">
                                        <li v-for="nom in nomsEvenements" :key="nom">
                                            <button
                                                type="button"
                                                @click="popupDuplication.nomEvenement = nom; popupDuplication.dropdownNomOpen = false; popupDuplication.annee = ''"
                                                class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded"
                                            >
                                                {{ nom }}
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="basis-1/4">
                            <label class="text-sm font-medium text-heading block mb-2">Année</label>
                            <div class="relative w-full">
                                <button
                                    ref="buttonAnneeRef"
                                    @click="updateDropdownPosition('annee'); popupDuplication.dropdownAnneeOpen = !popupDuplication.dropdownAnneeOpen"
                                    :disabled="!popupDuplication.nomEvenement"
                                    class="w-full inline-flex items-center justify-between shadow-xs font-medium text-sm px-4 py-2.5 disabled:opacity-50 disabled:cursor-not-allowed"
                                    :class="[
                                        popupDuplication.annee
                                            ? 'border-b-2 text-primary border-tertiary hover:bg-gray-100 rounded-t-base'
                                            : 'hover:bg-primary-300 text-white bg-primary-900 shadow-xs font-medium rounded-base text-sm px-4 py-2.5',
                                    ]"
                                    type="button"
                                >
                                    <span>{{
                                        popupDuplication.annee ||
                                        "Sélectionner une année"
                                    }}</span>
                                    <Icon
                                        icon="mdi:chevron-down"
                                        class="ml-2 w-6 h-6 flex-shrink-0"
                                    />
                                </button>
                                <div
                                    v-if="popupDuplication.dropdownAnneeOpen && popupDuplication.nomEvenement"
                                    class="fixed z-[60] bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg max-h-64 overflow-y-auto"
                                    :style="popupDuplication.dropdownAnneeStyle"
                                >
                                    <ul class="p-2 text-sm text-body font-medium">
                                        <li v-for="year in anneesEvenements" :key="year">
                                            <button
                                                type="button"
                                                @click="popupDuplication.annee = year; popupDuplication.dropdownAnneeOpen = false"
                                                class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded"
                                            >
                                                {{ year }}
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 px-6 py-4 border-t border-gray-100 bg-neutral-secondary-medium">
                    <button
                        @click="popupDuplication.visible = false"
                        class="btn-accent-300 px-4 py-2 rounded-lg"
                    >
                        Annuler
                    </button>
                    <button
                        @click="confirmerDuplication"
                        class="btn-tertiary px-4 py-2 rounded-lg ml-auto"
                    >
                        Confirmer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Title from "../components/Title.vue";
import { Icon } from "@iconify/vue";
import PopupConfirmation from "../components/PopupConfirmation.vue";
import PopupQuestionnaireResultat from "../components/PopupQuestionnaireResultat.vue";
import courseOrganisateurService from "../services/courseOrganisateurService";
import evenementOrganisateurService from "../services/evenementOrganisateurService";
import OptionList from "../components/OptionList.vue";
import optionOrganisateurService from "../services/optionOrganisateurService";
import optionCourseService from "../services/optionCourseService";
import questionOrganisateurService from "../services/questionOrganisateurService";
import optionQuestionOrganisateurService from "../services/optionQuestionOrganisateurService";
import courseQuestionOrganisateurService from "../services/courseQuestionOrganisateurService";
import avertissementOrganisateurService from "../services/avertissementOrganisateurService";
import GestionCodesRabais from "../components/GestionCodesRabais.vue";
import GestionCodesDossard from "../components/GestionCodesDossard.vue";
import ImportResultats from "../components/ImportResultats.vue";

const optionModal = {
    FERMEE: 1,
    DUPLIQUER: 2,
    SUPPRIMER: 3,
};

export default {
    components: {
        Title,
        Icon,
        PopupConfirmation,
        PopupQuestionnaireResultat,
        OptionList,
        GestionCodesRabais,
        GestionCodesDossard,
        ImportResultats,
    },
    computed: {
        idEvenement() {
            return this.$route.params.idEvenement;
        },
        nomsEvenements() {
            return [...new Set(this.tousLesEvenements.map(e => {
                // Extraire le nom sans l'année (tout sauf les 4 derniers chiffres)
                return e.nom.replace(/\s*\d{4}\s*$/, '').trim();
            }))].sort();
        },
        anneesEvenements() {
            if (!this.popupDuplication.nomEvenement) return [];
            const baseNom = this.popupDuplication.nomEvenement;
            return [...new Set(
                this.tousLesEvenements
                    .filter(e => {
                        const nomSansAnnee = e.nom.replace(/\s*\d{4}\s*$/, '').trim();
                        return nomSansAnnee === baseNom;
                    })
                    .map(e => {
                        // Extraire l'année du nom
                        const match = e.nom.match(/(\d{4})\s*$/);
                        return match ? parseInt(match[1]) : null;
                    })
                    .filter(year => year !== null)
            )].sort((a, b) => b - a);
        }
    },
    data() {
        return {
            courses: [],
            nomEvenement: "",
            chargement: true,
            erreur: "",
            courseASupprimer: null,
            courseQuestionnaireSelectionnee: null,
            activeOptionCourseId: null,
            optionElements: ["Dupliquer", "Supprimer"],
            optionButtonRefs: {},
            optionListStyle: {},
            handleClickOutsideBound: null,
            handleEscapeKeyBound: null,
            courseCodesRabais: null,
            courseCodesDossard: null,
            courseResultats: null,
            evenementADupliquer: null,
            tousLesEvenements: [],
            popupDuplication: {
                visible: false,
                evenementActuel: true,
                nomEvenement: '',
                annee: '',
                dropdownNomOpen: false,
                dropdownAnneeOpen: false,
                dropdownNomStyle: {},
                dropdownAnneeStyle: {}
            },
            buttonNomRef: null,
            buttonAnneeRef: null
        };
    },
    methods: {
        /**
         * Bascule le menu d'options pour une course donnée.
         * @param {number} courseId - L'identifiant de la course
         * @returns {void}
         */
        toggleOptionMenu(courseId) {
            this.activeOptionCourseId =
                this.activeOptionCourseId === courseId ? null : courseId;
            if (this.activeOptionCourseId !== null) {
                this.$nextTick(() => this.updateOptionListPosition());
            }
        },
        /**
         * Met à jour la position du menu d'options en fonction de la position du bouton.
         * @returns {void}
         */
        updateOptionListPosition() {
            const button = this.optionButtonRefs[this.activeOptionCourseId];
            if (!button) return;
            const rect = button.getBoundingClientRect();
            this.optionListStyle = {
                top: `${rect.top + rect.height / 2}px`,
                right: `${window.innerWidth - rect.left + 4}px`,
                transform: "translateY(-50%)",
            };
        },
        /**
         * Recherche et retourne une course par son identifiant.
         * @param {number} courseId - L'identifiant de la course à trouver
         * @returns {object|null} - La course correspondante ou null si non trouvée
         */
        findCourseById(courseId) {
            return this.courses.find((c) => c.id === courseId) || null;
        },
        /**
         * Traite la sélection d'une option (Dupliquer ou Supprimer) pour une course.
         * @param {object} course - La course concernée par l'option
         * @param {string} option - L'option sélectionnée ("Dupliquer" ou "Supprimer")
         * @returns {void}
         */
        handleOptionSelection(course, option) {
            this.activeOptionCourseId = null;
            switch (option) {
                case "Dupliquer":
                    this.popupDuplication = {
                        visible: true,
                        evenementActuel: true,
                        nomEvenement: '',
                        annee: '',
                        dropdownNomOpen: false,
                        dropdownAnneeOpen: false,
                        course: course,
                    };
                    break;
                case "Supprimer":
                    this.confirmerSuppression(course);
                    break;
            }
        },
        /**
         * Formate une date au format JJ.MM.AAAA selon la locale fr-CH.
         * @param {string} dateString - La chaîne de date à formater
         * @returns {string} - La date formatée ou "—" si la date est invalide
         */
        formaterDate(dateString) {
            if (!dateString) return "—";
            const date = new Date(dateString);
            return date.toLocaleDateString("fr-CH", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
            });
        },
        /**
         * Charge la liste des courses pour l'événement actuel depuis l'API.
         * @returns {Promise<void>}
         */
        async chargerCourses() {
            this.chargement = true;
            this.erreur = "";
            try {
                const response = await courseOrganisateurService.getAllCourses(
                    this.idEvenement,
                );
                this.courses = response.data?.courses ?? [];
            } catch (e) {
                this.erreur = "Impossible de charger les courses.";
            } finally {
                this.chargement = false;
            }
        },
        /**
         * Redirige vers le formulaire de modification pour une course.
         * @param {object} course - La course à modifier
         * @returns {void}
         */
        modifierCourse(course) {
            this.$router.push(
                `/organisateur/formulaires?onglet=Course&id=${course.id}&idEvenement=${this.idEvenement}`,
            );
        },
        /**
         * Ouvre la popup de gestion des codes de rabais pour une course.
         * @param {object} course - La course concernée
         * @returns {void}
         */
        ouvrirCodesRabais(course) {
            this.courseCodesRabais = course;
        },
        /**
         * Ouvre la popup de gestion des codes dossard personnalisés pour une course.
         * @param {object} course - La course concernée
         * @returns {void}
         */
        ouvrirCodesDossard(course) {
            this.courseCodesDossard = course;
        },
        /**
         * Prépare la suppression d'une course en l'assignant à courseASupprimer et affiche la popup de confirmation.
         * @param {object} course - La course à supprimer
         * @returns {void}
         */
        confirmerSuppression(course) {
            this.courseASupprimer = course;
        },
        /**
         * Affiche la popup des résultats du questionnaire pour une course.
         * @param {object} course - La course dont afficher les résultats
         * @returns {void}
         */
        afficherQuestion(course) {
            this.courseQuestionnaireSelectionnee = course;
        },
        /**
         * Ferme la popup des résultats du questionnaire.
         * @returns {void}
         */
        fermerQuestionnaire() {
            this.courseQuestionnaireSelectionnee = null;
        },
        /**
         * Vérifie si une course possède un questionnaire associé.
         * @param {object} course - La course à vérifier
         * @returns {boolean} - true si la course a un questionnaire, false sinon
         */
        aQuestionnaire(course) {
            return Boolean(
                course?.is_questionnaire ||
                (course?.questionnaire?.length ?? 0) > 0 ||
                (course?.questions?.length ?? 0) > 0,
            );
        },
        /**
         * Supprime une course et la retire de la liste après confirmation.
         * @returns {Promise<void>}
         */
        async supprimerCourse() {
            try {
                await courseOrganisateurService.deleteCourse(
                    this.courseASupprimer.id,
                );
                this.courses = this.courses.filter(
                    (c) => c.id !== this.courseASupprimer.id,
                );
                this.courseASupprimer = null;
            } catch (e) {
                this.erreur = "Impossible de supprimer cette course.";
                this.courseASupprimer = null;
            }
        },
        /**
         * Génère un nom unique pour une course dupliquée en ajoutant un compteur.
         * @param {string} nomOriginal - Le nom original de la course
         * @returns {string} - Le nouveau nom avec le compteur (ex: "Course (2)")
         */
        genererNomDuplique(nomOriginal) {
            const baseName = nomOriginal.replace(/\s*\(\d+\)$/, '');
            const count = this.courses.filter((c) =>
                c.nom.startsWith(`${baseName} (`)
            ).length;
            return `${baseName} (${count + 1})`;
        },
        /**
         * Duplique une course complète avec ses options, avertissements et questionnaires.
         * @param {object} course - La course à dupliquer
         * @returns {Promise<void>}
         */
        async dupliquerCourse(course) {
            try {
                const courseComplete = await courseOrganisateurService.getCourse(course.id, this.idEvenement);
                const nomDuplique = this.genererNomDuplique(courseComplete.data.nom);
                
                if (!this.popupDuplication.evenementActuel) {
                    courseComplete.data.id_evenement = this.evenementADupliquer;
                }
                
                if(courseComplete.data.date_debut < new Date()) {
                    courseComplete.data.date_debut = new Date();
                }
                if(courseComplete.data.date_fin < new Date()) {
                    courseComplete.data.date_fin = new Date();
                }

                if(courseComplete.data.debut_inscription < new Date()) {
                    courseComplete.data.debut_inscription = new Date();
                }
                if(courseComplete.data.fin_inscription < new Date()) {
                    courseComplete.data.fin_inscription = new Date();
                }

                const payload = {
                    id_evenement: courseComplete.data.id_evenement,
                    id_categorie: courseComplete.data.id_categorie,
                    id_sous_categorie: courseComplete.data.id_sous_categorie,
                    nom: nomDuplique,
                    date_debut: courseComplete.data.date_debut,
                    date_fin: courseComplete.data.date_fin,
                    debut_inscription: courseComplete.data.debut_inscription,
                    fin_inscription: courseComplete.data.fin_inscription,
                    tarif: courseComplete.data.tarif,
                    status: courseComplete.data.status,
                    type: courseComplete.data.type,
                    is_challenge: courseComplete.data.is_challenge,
                    is_actif: courseComplete.data.is_actif,
                    is_dossard: courseComplete.data.is_dossard,
                    is_avertissement: courseComplete.data.is_avertissement,
                    is_document: courseComplete.data.is_document,
                    is_questionnaire: courseComplete.data.is_questionnaire,
                    max_inscription: courseComplete.data.max_inscription,
                    max_nb_personne: courseComplete.data.max_nb_personne,
                    premier_dossard: courseComplete.data.premier_dossard,
                    dernier_dossard: courseComplete.data.dernier_dossard,
                    distance: courseComplete.data.distance,
                    age_minimum: courseComplete.data.age_minimum,
                    age_maximum: courseComplete.data.age_maximum,
                    is_prix_evolutif: courseComplete.data.is_prix_evolutif,
                    document_description:
                        courseComplete.data.document_description,
                };
                const newCourseResponse =
                    await courseOrganisateurService.createCourse(payload);
                const newCourseId = newCourseResponse.data.course.id;
                let newAvertissementId = null;
                if (
                    courseComplete.data.is_avertissement &&
                    courseComplete.data.avertissement
                ) {
                    const avertissementPayload = {
                        titre: courseComplete.data.avertissement.titre,
                        contenu: courseComplete.data.avertissement.contenu,
                    };
                    const newAvertissementResponse =
                        await avertissementOrganisateurService.createAvertissement(
                            avertissementPayload,
                        );
                    newAvertissementId =
                        newAvertissementResponse.data.avertissement.id;
                    await courseOrganisateurService.modifyCourse(newCourseId, {
                        id_avertissement: newAvertissementId,
                    });
                }
                if (
                    courseComplete.data.options &&
                    courseComplete.data.options.length > 0
                ) {
                    for (const option of courseComplete.data.options) {
                        const optionPayload = {
                            nom: option.nom,
                            description: option.description,
                            tarif: option.tarif,
                            type: option.type,
                            modele: false,
                        };
                        if (option.quantifiable) {
                            optionPayload.quantiteMin =
                                option.quantifiable.quantiteMin;
                            optionPayload.quantiteMax =
                                option.quantifiable.quantiteMax;
                        }
                        if (option.cochable) {
                            optionPayload.tailleMin = option.cochable.tailleMin;
                            optionPayload.tailleMax = option.cochable.tailleMax;
                        }
                        const newOptionResponse =
                            await optionOrganisateurService.createOption(
                                optionPayload,
                            );
                        const newOptionId = newOptionResponse.data.option.id;
                        await optionCourseService.createOptionCourse({
                            id_course: newCourseId,
                            id_option: newOptionId,
                        });
                    }
                }
                if (
                    courseComplete.data.questions &&
                    courseComplete.data.questions.length > 0
                ) {
                    const nouvellesQuestions = [];
                    for (const question of courseComplete.data.questions) {
                        const questionPayload = {
                            enonce: question.enonce,
                            modele: false,
                            ids_courses: [newCourseId],
                        };
                        const newQuestionResponse =
                            await questionOrganisateurService.createQuestion(
                                questionPayload,
                            );
                        const newQuestionId =
                            newQuestionResponse.data.question.id;
                        if (question.choix && question.choix.length > 0) {
                            for (const choix of question.choix) {
                                await optionQuestionOrganisateurService.createChoix(
                                    newQuestionId,
                                    { texte_option: choix.texte_option },
                                );
                            }
                        }
                        nouvellesQuestions.push({
                            id_question: newQuestionId,
                            ordre:
                                courseComplete.data.questions.indexOf(
                                    question,
                                ) + 1,
                        });
                    }
                    if (nouvellesQuestions.length > 0) {
                        await courseQuestionOrganisateurService.reordonnerQuestions(
                            newCourseId,
                            { questions: nouvellesQuestions },
                        );
                    }
                }
                await this.chargerCourses();
                this.erreur = "";
            } catch (e) {
                console.error("Erreur lors de la duplication:", e);
                // Extraire le vrai message d'erreur du serveur
                let messageErreur = "Impossible de dupliquer cette course.";
                if (e.response?.data?.message) {
                    messageErreur = e.response.data.message;
                } else if (e.response?.data?.errors) {
                    // Si c'est une erreur de validation, récupérer le premier message
                    const firstErrorKey = Object.keys(e.response.data.errors)[0];
                    if (firstErrorKey && Array.isArray(e.response.data.errors[firstErrorKey])) {
                        messageErreur = e.response.data.errors[firstErrorKey][0];
                    }
                }
                this.erreur = messageErreur;
            }
        },
        /**
         * Ferme le menu d'options lorsque l'utilisateur clique en dehors.
         * @param {Event} event - L'événement de clic
         * @returns {void}
         */
        handleClickOutside(event) {
            const isClickOnEllipsisButton = event.target.closest(
                'button[title="Afficher les actions supplémentaires"]',
            );
            const isClickInsideMenu = event.target.closest(".absolute.z-50");
            if (!isClickOnEllipsisButton && !isClickInsideMenu) {
                this.activeOptionCourseId = null;
            }
        },
        /**
         * Ferme le menu d'options quand la touche Échap est pressée.
         * @param {KeyboardEvent} event - L'événement clavier
         * @returns {void}
         */
        handleEscapeKey(event) {
            if (event.key === "Escape") {
                this.activeOptionCourseId = null;
            }
        },
        /**
         * Met à jour la position du dropdown (nom ou année) en fonction du bouton.
         * @param {string} dropdown - Le dropdown à positionner ("nom" ou "annee")
         * @returns {void}
         */
        updateDropdownPosition(dropdown) {
            this.$nextTick(() => {
                let buttonRef, styleProp;
                
                if (dropdown === 'nom') {
                    buttonRef = this.$refs.buttonNomRef;
                    styleProp = 'dropdownNomStyle';
                } else {
                    buttonRef = this.$refs.buttonAnneeRef;
                    styleProp = 'dropdownAnneeStyle';
                }
                
                if (!buttonRef) return;
                
                const rect = buttonRef.getBoundingClientRect();
                this.popupDuplication[styleProp] = {
                    top: `${rect.top + rect.height}px`,
                    left: `${rect.left}px`,
                    width: `${rect.width}px`
                };
            });
        },
        /**
         * Confirme et exécute la duplication de la course vers l'événement sélectionné.
         * @returns {void}
         */
        confirmerDuplication() {
            if (this.popupDuplication.evenementActuel) {
                // Dupliquer dans l'événement actuel
                console.log('Duplication dans l\'événement actuel');
            } else {
                // Dupliquer dans un autre événement
                if (!this.popupDuplication.nomEvenement || !this.popupDuplication.annee) {
                    alert('Veuillez sélectionner un événement et une année');
                    return;
                }
                // Construire le nom complet avec l'année
                const nomComplet = `${this.popupDuplication.nomEvenement} ${this.popupDuplication.annee}`;
                const evenement = this.tousLesEvenements.find(e => e.nom === nomComplet);
                if (evenement) {
                    this.evenementADupliquer = evenement.id;
                    console.log('Duplication vers l\'événement:', evenement.id);
                } else {
                    alert('Événement non trouvé');
                    return;
                }
            }
            this.dupliquerCourse(this.popupDuplication.course);
            this.popupDuplication.visible = false;
        },
        /**
         * Ouvre la popup d'affichage et d'import des résultats pour une course.
         * @param {object} course - La course dont afficher les résultats
         * @returns {void}
         */
        ouvrirResultats(course) {
            this.courseResultats = course;
        },
        /**
         * Charge la liste de tous les événements organisateur depuis l'API.
         * @returns {Promise<void>}
         */
        async chargerEvenements() {
            try {
                const response = await evenementOrganisateurService.getAllEvenements();
                console.log('Réponse getAllEvenements:', response);
                this.tousLesEvenements = response.data?.data ?? response.data ?? [];
                console.log('tousLesEvenements après assignation:', this.tousLesEvenements);
            } catch (e) {
                console.error('Erreur lors du chargement des événements:', e);
            }
        },
    },
    async mounted() {
        await this.chargerCourses();
        await this.chargerEvenements();
        try {
            const response = await evenementOrganisateurService.getEvenement(
                this.idEvenement,
            );
            this.nomEvenement = response.data.nom;
        } catch (e) {
            console.log("L'évènement n'a pas pu être récupéré: ", e);
        }
        this.handleClickOutsideBound = (event) =>
            this.handleClickOutside(event);
        this.handleEscapeKeyBound = (event) => this.handleEscapeKey(event);
        document.addEventListener("click", this.handleClickOutsideBound);
        document.addEventListener("keydown", this.handleEscapeKeyBound);
    },
    beforeUnmount() {
        document.removeEventListener("click", this.handleClickOutsideBound);
        document.removeEventListener("keydown", this.handleEscapeKeyBound);
    },
};
</script>
