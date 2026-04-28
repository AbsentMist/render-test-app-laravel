<template>
    <Title :texte="`Tableau de bord : courses ${nomEvenement}`" />
    <div class="p-6 relative">
        <button @click="$router.push('/organisateur/formulaires?onglet=Course')" class="btn-tertiary px-4 py-2 rounded-lg inline-block mb-6">
        Nouveau
        </button>

        <!-- Message d'erreur -->
        <p v-if="erreur" class="text-accent text-label mb-4">{{ erreur }}</p>

        <!-- Chargement -->
        <div v-if="chargement" class="text-body text-center py-8">
        Chargement des courses...
        </div>

        <!-- Tableau -->
        <div v-else class="overflow-x-auto rounded-xl border border-default-medium">
        <table class="w-full text-sm text-left text-body">
            <thead class="bg-neutral-secondary-medium text-heading text-xs uppercase">
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
                <td class="px-4 py-3 font-medium text-heading">{{ course.nom }}</td>

                <!-- Date début (TODO 9.2) -->
                <td class="px-4 py-3">{{ formaterDate(course.debut_inscription) }}</td>

                <!-- Date fin (TODO 9.2) -->
                <td class="px-4 py-3">{{ formaterDate(course.fin_inscription) }}</td>

                <!-- Actif -->
                <td class="px-4 py-3 text-center">
                <Icon v-if="course.is_actif" icon="mdi:check" class="w-5 h-5 text-green-500 mx-auto" />
                <Icon v-else icon="mdi:close" class="w-5 h-5 text-accent mx-auto" />
                </td>

                <!-- Interne -->
                <td class="px-4 py-3 text-center">
                <Icon v-if="course.is_interne" icon="mdi:check" class="w-5 h-5 text-green-500 mx-auto" />
                <Icon v-else icon="mdi:close" class="w-5 h-5 text-accent mx-auto" />
                </td>

                <!-- Actions -->
                <td class="px-4 py-3">
                <div class="flex items-center justify-between">
                    <div>
                        <button
                        v-if="aQuestionnaire(course)"
                        @click="afficherQuestion(course)"
                        class="p-1.5 rounded-lg text-primary hover:bg-tertiary transition-colors"
                        title="Voir les résultats du questionnaire"
                        >
                        <Icon icon="lucide:circle-question-mark" class="w-4 h-4" />
                        </button>
                        <button
                        @click="modifierCourse(course)"
                        class="p-1.5 rounded-lg text-primary hover:bg-tertiary transition-colors"
                        title="Modifier"
                        >
                        <Icon icon="lucide:square-pen" class="w-4 h-4" />
                        </button>
                    </div>
                    <div class="relative inline-block">
                        <button
                        :ref="(el) => { optionButtonRefs[course.id] = el }"
                        @click="toggleOptionMenu(course.id)"
                        class="p-1.5 ml-1 rounded-lg text-primary hover:bg-tertiary transition-colors"
                        title="Afficher les actions supplémentaires"
                        >
                        <Icon icon="lucide:ellipsis-vertical" class="w-4 h-4" />
                        </button>
                    </div>

                </div>
                </td>
            </tr>
            </tbody>
        </table>
        </div>

        <!-- OptionList en dehors du tableau, à droite du bouton -->
        <OptionList
            v-if="activeOptionCourseId !== null"
            :style="optionListStyle"
            placement="none"
            class="fixed z-50"
            :elements="optionElements"
            @select-item="(option) => handleOptionSelection(findCourseById(activeOptionCourseId), option)"
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
  </div>
</template>

<script>
/**
 * @fileoverview Vue OrganisateurCourses.
 * @description Liste des courses d'un évènement avec actions d'édition et de suppression.
 * @remarks Cette vue sert de tableau de gestion rapide avant ouverture du formulaire course.
 */
import Title from '../components/Title.vue';
import { Icon } from '@iconify/vue';
import PopupConfirmation from '../components/PopupConfirmation.vue';
import PopupQuestionnaireResultat from '../components/PopupQuestionnaireResultat.vue';
import courseOrganisateurService from '../services/courseOrganisateurService'
import evenementOrganisateurService from '../services/evenementOrganisateurService';
import OptionList from '../components/OptionList.vue';
import optionOrganisateurService from '../services/optionOrganisateurService';
import optionCourseService from '../services/optionCourseService';
import questionOrganisateurService from '../services/questionOrganisateurService';
import optionQuestionOrganisateurService from '../services/optionQuestionOrganisateurService';
import courseQuestionOrganisateurService from '../services/courseQuestionOrganisateurService';
import avertissementOrganisateurService from '../services/avertissementOrganisateurService';

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
        OptionList
    },
    computed: {
        /**
         * Identifiant de l'évènement parent de la liste affichée.
         * @returns {string}
         */
        idEvenement() {
        return this.$route.params.idEvenement
        }
    },
    data() {
        return {
            courses: [],
            nomEvenement: "",
            chargement: true,
            erreur: '',
            courseASupprimer: null,
            courseQuestionnaireSelectionnee: null,
            activeOptionCourseId: null,
            optionElements: ["Dupliquer", "Supprimer"],
            optionButtonRefs: {},
            optionListStyle: {},
            handleClickOutsideBound: null,
            handleEscapeKeyBound: null,
        }
    },
    methods: {
        /**
         * Fait apparaitre ou disparaître le menu d'options pour une course donnée.
         * @returns {void}
         */
        toggleOptionMenu(courseId) {
            this.activeOptionCourseId = this.activeOptionCourseId === courseId ? null : courseId;
            if (this.activeOptionCourseId !== null) {
                this.$nextTick(() => this.updateOptionListPosition());
            }
        },
        /**
         * Met à jour la position du OptionList basée sur le bouton cliqué.
         * @returns {void}
         */
        updateOptionListPosition() {
            const button = this.optionButtonRefs[this.activeOptionCourseId];
            if (!button) return;
            
            const rect = button.getBoundingClientRect();
            this.optionListStyle = {
                top: `${rect.top + rect.height / 2}px`,
                right: `${window.innerWidth - rect.left + 4}px`,
                transform: 'translateY(-50%)',
            };
        },
        /**
         * Trouve une course par son ID.
         * @param {number} courseId
         * @returns {Object|null}
         */
        findCourseById(courseId) {
            return this.courses.find(c => c.id === courseId) || null;
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
        /**
         * Formate une date en locale suisse.
         * @param {string} dateString
         * @returns {string}
         */
        formaterDate(dateString) {
            if (!dateString) return '—'; // Si pas de date
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-CH', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        },
        /**
         * Charge toutes les courses de l'évènement sélectionné.
         * @returns {Promise<void>}
         */
        async chargerCourses() {
            this.chargement = true
            this.erreur = ''
            try {
                const response = await courseOrganisateurService.getAllCourses(this.idEvenement)
                this.courses = response.data?.courses ?? []
            } catch (e) {
                this.erreur = 'Impossible de charger les courses.'
            } finally {
                this.chargement = false
            }
        },
        /**
         * Redirige vers le formulaire course en mode édition.
         * @param {Object} course
         * @returns {void}
         */
        modifierCourse(course) {
        this.$router.push(`/organisateur/formulaires?onglet=Course&id=${course.id}&idEvenement=${this.idEvenement}`);
        },
        /**
         * Ouvre la confirmation de suppression d'une course.
         * @param {Object} course
         * @returns {void}
         */
        confirmerSuppression(course) {
        this.courseASupprimer = course
        },
        /**
         * Ouvre le popup de résultats du questionnaire pour une course.
         * @param {Object} course
         * @returns {void}
         */
        afficherQuestion(course) {
        this.courseQuestionnaireSelectionnee = course
        },
        /**
         * Ferme le popup de résultats du questionnaire.
         * @returns {void}
         */
        fermerQuestionnaire() {
        this.courseQuestionnaireSelectionnee = null
        },
        /**
         * Détermine si une course expose un questionnaire à consulter.
         * @param {Object} course
         * @returns {boolean}
         */
        aQuestionnaire(course) {
        return Boolean(
            course?.is_questionnaire
            || (course?.questionnaire?.length ?? 0) > 0
            || (course?.questions?.length ?? 0) > 0
        )
        },
        /**
         * Supprime la course confirmée puis met à jour la liste locale.
         * @returns {Promise<void>}
         */
        async supprimerCourse() {
        try {
            await courseOrganisateurService.deleteCourse(this.courseASupprimer.id)
            this.courses = this.courses.filter(c => c.id !== this.courseASupprimer.id)
            this.courseASupprimer = null
        } catch (e) {
            this.erreur = 'Impossible de supprimer cette course.'
            this.courseASupprimer = null
        }
        },
        /**
         * Génère un nom dupliqué avec numérotation incrémentale (1), (2), (3), etc.
         * @param {string} nomOriginal
         * @returns {string}
         */
        genererNomDuplique(nomOriginal) {
            // Extraire le nom de base (sans le (n))
            const baseNameMatch = nomOriginal.match(/^(.+?)\s*(?:\(\d+\))?$/);
            const baseName = baseNameMatch ? baseNameMatch[1].trim() : nomOriginal;

            // Chercher tous les numéros existants pour ce nom
            const pattern = new RegExp(`^${baseName.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\s*\\((\\d+)\\)$`);
            const existingNumbers = this.courses
                .map(c => {
                    const match = c.nom.match(pattern);
                    return match ? parseInt(match[1]) : null;
                })
                .filter(n => n !== null);

            // Trouver le prochain numéro disponible
            let nextNumber = 1;
            if (existingNumbers.length > 0) {
                nextNumber = Math.max(...existingNumbers) + 1;
            }

            return `${baseName} (${nextNumber})`;
        },
        /**
         * Duplique une course complète avec toutes ses options, questions et avertissements.
         * @param {Object} course
         * @returns {Promise<void>}
         */
        async dupliquerCourse(course) {
            try {
                // Récupérer les données complètes de la course
                const courseComplete = await courseOrganisateurService.getCourse(course.id, this.idEvenement);
                
                // Générer le nom dupliqué avec numérotation
                const nomDuplique = this.genererNomDuplique(courseComplete.data.nom);
                
                // Créer le payload pour la nouvelle course
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
                    document_description: courseComplete.data.document_description,
                };

                // Créer la nouvelle course
                const newCourseResponse = await courseOrganisateurService.createCourse(payload);
                const newCourseId = newCourseResponse.data.course.id;

                // Dupliquer l'avertissement si présent
                let newAvertissementId = null;
                if (courseComplete.data.is_avertissement && courseComplete.data.avertissement) {
                    const avertissementPayload = {
                        titre: courseComplete.data.avertissement.titre,
                        contenu: courseComplete.data.avertissement.contenu,
                    };
                    const newAvertissementResponse = await avertissementOrganisateurService.createAvertissement(avertissementPayload);
                    newAvertissementId = newAvertissementResponse.data.avertissement.id;
                    
                    // Mettre à jour la course avec le nouvel avertissement
                    await courseOrganisateurService.modifyCourse(newCourseId, {
                        id_avertissement: newAvertissementId
                    });
                }

                // Dupliquer les options
                if (courseComplete.data.options && courseComplete.data.options.length > 0) {
                    for (const option of courseComplete.data.options) {
                        const optionPayload = {
                            nom: option.nom,
                            description: option.description,
                            tarif: option.tarif,
                            type: option.type,
                            modele: false,
                        };

                        if (option.quantifiable) {
                            optionPayload.quantiteMin = option.quantifiable.quantiteMin;
                            optionPayload.quantiteMax = option.quantifiable.quantiteMax;
                        }

                        if (option.cochable) {
                            optionPayload.tailleMin = option.cochable.tailleMin;
                            optionPayload.tailleMax = option.cochable.tailleMax;
                        }

                        const newOptionResponse = await optionOrganisateurService.createOption(optionPayload);
                        const newOptionId = newOptionResponse.data.option.id;

                        // Créer la liaison option-course
                        await optionCourseService.createOptionCourse({
                            id_course: newCourseId,
                            id_option: newOptionId,
                        });
                    }
                }

                // Dupliquer les questions
                if (courseComplete.data.questions && courseComplete.data.questions.length > 0) {
                    const nouvellesQuestions = [];
                    
                    for (const question of courseComplete.data.questions) {
                        const questionPayload = {
                            enonce: question.enonce,
                            modele: false,
                            ids_courses: [newCourseId],
                        };

                        const newQuestionResponse = await questionOrganisateurService.createQuestion(questionPayload);
                        const newQuestionId = newQuestionResponse.data.question.id;

                        // Dupliquer les choix de la question
                        if (question.choix && question.choix.length > 0) {
                            for (const choix of question.choix) {
                                await optionQuestionOrganisateurService.createChoix(
                                    newQuestionId,
                                    { texte_option: choix.texte_option }
                                );
                            }
                        }

                        nouvellesQuestions.push({
                            id_question: newQuestionId,
                            ordre: courseComplete.data.questions.indexOf(question) + 1,
                        });
                    }

                    // Reordonner les questions
                    if (nouvellesQuestions.length > 0) {
                        await courseQuestionOrganisateurService.reordonnerQuestions(newCourseId, {
                            questions: nouvellesQuestions,
                        });
                    }
                }

                // Ajouter la nouvelle course à la liste
                await this.chargerCourses();
                this.erreur = '';
            } catch (e) {
                console.error('Erreur lors de la duplication:', e);
                this.erreur = 'Impossible de dupliquer cette course.';
            }
        },
        /**
         * Ferme le menu d'options lors d'un clic en dehors.
         * @param {Event} event
         * @returns {void}
         */
        handleClickOutside(event) {
            // Vérifier si le clic est sur le bouton ellipsis ou le menu
            const isClickOnEllipsisButton = event.target.closest('button[title="Afficher les actions supplémentaires"]');
            const isClickInsideMenu = event.target.closest('.absolute.z-50');
            
            if (!isClickOnEllipsisButton && !isClickInsideMenu) {
                this.activeOptionCourseId = null;
            }
        },
        /**
         * Ferme le menu d'options lors de la pression sur Échap.
         * @param {KeyboardEvent} event
         * @returns {void}
         */
        handleEscapeKey(event) {
            if (event.key === 'Escape') {
                this.activeOptionCourseId = null;
            }
        }
    },
    /**
     * Charge les courses et le nom de l'évènement au montage.
     * @returns {Promise<void>}
     */
    async mounted() {
        await this.chargerCourses()
        
        try{
            const response = await evenementOrganisateurService.getEvenement(this.idEvenement);
            this.nomEvenement = response.data.nom;
            console.log(response.data);
        } catch(e) {
            console.log("L'évènement n' a pas pu être récupéré: ", e);
        }

        // Créer des références boundées pour les listeners
        this.handleClickOutsideBound = (event) => this.handleClickOutside(event);
        this.handleEscapeKeyBound = (event) => this.handleEscapeKey(event);

        // Ajouter les listeners
        document.addEventListener('click', this.handleClickOutsideBound);
        document.addEventListener('keydown', this.handleEscapeKeyBound);
    },
    /**
     * Nettoie les event listeners au démontage.
     * @returns {void}
     */
    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutsideBound);
        document.removeEventListener('keydown', this.handleEscapeKeyBound);
    }
}
</script>