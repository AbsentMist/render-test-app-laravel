<template>
    <Title :texte="`Tableau de bord : courses ${nomEvenement}`" />
    <div class="p-6 relative">
        <button
            @click="$router.push('/organisateur/formulaires?onglet=Course')"
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
                        <th class="px-4 py-3">Date début</th>
                        <th class="px-4 py-3">Date fin</th>
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
    },
    computed: {
        idEvenement() {
            return this.$route.params.idEvenement;
        },
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
        };
    },
    methods: {
        toggleOptionMenu(courseId) {
            this.activeOptionCourseId =
                this.activeOptionCourseId === courseId ? null : courseId;
            if (this.activeOptionCourseId !== null) {
                this.$nextTick(() => this.updateOptionListPosition());
            }
        },
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
        findCourseById(courseId) {
            return this.courses.find((c) => c.id === courseId) || null;
        },
        handleOptionSelection(course, option) {
            this.activeOptionCourseId = null;
            switch (option) {
                case "Dupliquer":
                    this.dupliquerCourse(course);
                    break;
                case "Supprimer":
                    this.confirmerSuppression(course);
                    break;
            }
        },
        formaterDate(dateString) {
            if (!dateString) return "—";
            const date = new Date(dateString);
            return date.toLocaleDateString("fr-CH", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
            });
        },
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
        modifierCourse(course) {
            this.$router.push(
                `/organisateur/formulaires?onglet=Course&id=${course.id}&idEvenement=${this.idEvenement}`,
            );
        },
        ouvrirCodesRabais(course) {
            this.courseCodesRabais = course;
        },
        ouvrirCodesDossard(course) {
            this.courseCodesDossard = course;
        },
        confirmerSuppression(course) {
            this.courseASupprimer = course;
        },
        afficherQuestion(course) {
            this.courseQuestionnaireSelectionnee = course;
        },
        fermerQuestionnaire() {
            this.courseQuestionnaireSelectionnee = null;
        },
        aQuestionnaire(course) {
            return Boolean(
                course?.is_questionnaire ||
                (course?.questionnaire?.length ?? 0) > 0 ||
                (course?.questions?.length ?? 0) > 0,
            );
        },
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
        genererNomDuplique(nomOriginal) {
            const baseNameMatch = nomOriginal.match(/^(.+?)\s*(?:\(\d+\))?$/);
            const baseName = baseNameMatch
                ? baseNameMatch[1].trim()
                : nomOriginal;
            const pattern = new RegExp(
                `^${baseName.replace(/[.*+?^${}()|[\]\\]/g, "\\$&")}\\s*\\((\\d+)\\)$`,
            );
            const existingNumbers = this.courses
                .map((c) => {
                    const match = c.nom.match(pattern);
                    return match ? parseInt(match[1]) : null;
                })
                .filter((n) => n !== null);
            let nextNumber = 1;
            if (existingNumbers.length > 0) {
                nextNumber = Math.max(...existingNumbers) + 1;
            }
            return `${baseName} (${nextNumber})`;
        },
        async dupliquerCourse(course) {
            try {
                const courseComplete =
                    await courseOrganisateurService.getCourse(
                        course.id,
                        this.idEvenement,
                    );
                const nomDuplique = this.genererNomDuplique(
                    courseComplete.data.nom,
                );
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
                    heure_depart: courseComplete.data.heure_depart,
                    heure_fin: courseComplete.data.heure_fin,
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
                this.erreur = "Impossible de dupliquer cette course.";
            }
        },
        handleClickOutside(event) {
            const isClickOnEllipsisButton = event.target.closest(
                'button[title="Afficher les actions supplémentaires"]',
            );
            const isClickInsideMenu = event.target.closest(".absolute.z-50");
            if (!isClickOnEllipsisButton && !isClickInsideMenu) {
                this.activeOptionCourseId = null;
            }
        },
        handleEscapeKey(event) {
            if (event.key === "Escape") {
                this.activeOptionCourseId = null;
            }
        },
    },
    async mounted() {
        await this.chargerCourses();
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
