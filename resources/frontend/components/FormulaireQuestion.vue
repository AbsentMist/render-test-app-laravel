<template>
    <div class="bg-secondary rounded-base p-6">
        <h1 class="text-subtitle my-4">Questionnaire</h1>
        <p class="mb-4 text-body text-sm">
            Créez des modèles de questions réutilisables pour l'étape questionnaire des courses.
        </p>

        <div class="flex flex-col-2 gap-4">
            <div class="basis-2/3">
                <QuestionTemplate :questionModel="questionData" :removeButton="true" />
                <div class="flex flex-row justify-end mt-4 gap-4">
                    <button v-if="isEditing" type="button" @click="resetForm" class="btn-accent-300">
                        Annuler
                    </button>
                    <button v-if="isEditing" type="button" @click="modifyQuestion" class="btn-tertiary-300 text-secondary">
                        Modifier la question
                    </button>
                    <button type="button" @click="createQuestion" class="btn-tertiary">
                        Ajouter la question
                    </button>
                </div>
            </div>
            <div class="basis-1/3 min-w-0 border-l border-default-medium pl-4">
                <h2 class="text-sm font-medium text-heading mb-2.5">Mes modèles</h2>
                <div class="flex flex-col gap-2 h-103.75 overflow-y-auto overflow-x-hidden pr-2 scrollbar-thin">
                    <button
                        v-for="(question, index) in questionModels"
                        :key="question.id ?? index"
                        type="button"
                        @click="copyDatas(question)"
                        :class="[
                            'btn-model flex flex-row items-center justify-between w-full min-w-0 text-left min-h-10.5',
                            isModelHighlighted(question) ? 'bg-accent-300 text-heading ring-1 ring-accent' : ''
                        ]"
                    >
                        <span class="block flex-1 min-w-0 truncate pr-2">{{ question.enonce }}</span>
                        <Icon
                            icon="lucide:trash-2"
                            width="20"
                            height="20"
                            class="text-accent hover:bg-accent-300 rounded-lg shrink-0"
                            @click.stop="removeQuestion(index)"
                        />
                    </button>

                    <p v-if="questionModels.length === 0" class="text-xs text-body italic">
                        Aucun modèle enregistré.
                    </p>
                </div>
            </div>
        </div>

        <PopupConfirmation
            v-if="questionASupprimer"
            :message="`Supprimer la question ${questionASupprimer.enonce} ?`"
            icon="mdi:alert-circle-outline"
            @confirm="confirmerSuppressionQuestion"
            @cancel="questionASupprimer = null"
        />
    </div>
</template>

<script>
/**
 * @fileoverview Composant FormulaireQuestion.
 * @description Gestion des modèles de questionnaire organisateur.
 * Permet de créer, modifier, préremplir depuis un modèle existant et supprimer
 * une question avec ses choix de réponse.
 * @remarks Le composant garde la même ergonomie que FormulaireOption: édition à gauche,
 * bibliothèque de modèles à droite et confirmation avant suppression.
 */
import { Icon } from '@iconify/vue';
import QuestionTemplate from './QuestionTemplate.vue';
import PopupConfirmation from './PopupConfirmation.vue';
import questionOrganisateurService from '../services/questionOrganisateurService';
import optionQuestionOrganisateurService from '../services/optionQuestionOrganisateurService';

const createEmptyQuestion = () => ({
    id: null,
    enonce: '',
    modele: true,
    choix: [{ texte_option: '' }],
});

export default {
    components: {
        Icon,
        QuestionTemplate,
        PopupConfirmation,
    },
    /**
     * Initialise le formulaire de question et la collection de modèles existants.
     * @returns {Object} État local de l'éditeur de questionnaires.
     */
    data() {
        return {
            questionData: createEmptyQuestion(),
            questionModels: [],
            questionASupprimer: null,
            originalChoixIds: [],
            selectedQuestionModelId: null,
            dataInserted: false,
        };
    },
    computed: {
        /**
         * Indique si le formulaire est en mode édition.
         * @returns {boolean}
         */
        isEditing() {
            return this.questionData.id !== null;
        },
    },
    methods: {
        /**
         * Convertit une question API vers le format local du formulaire.
         * @param {Object} question Donnée question brute.
         * @returns {Object} Donnée question normalisée.
         */
        normalizeQuestion(question) {
            const choix = Array.isArray(question?.choix)
                ? question.choix.map((item) => ({
                    id: item.id ?? null,
                    texte_option: item.texte_option ?? '',
                }))
                : [];

            return {
                id: question?.id ?? null,
                enonce: question?.enonce ?? '',
                modele: true,
                choix: choix.length > 0 ? choix : [{ texte_option: '' }],
            };
        },
        /**
         * Copie un modèle existant dans le formulaire courant.
         * @param {Object} question Modèle source sélectionné.
         * @returns {void}
         */
        copyDatas(question) {
            const normalizedQuestion = this.normalizeQuestion(question);
            this.questionData = normalizedQuestion;
            this.selectedQuestionModelId = question?.id ?? null;
            this.originalChoixIds = normalizedQuestion.choix
                .map((choix) => choix.id)
                .filter((id) => id !== null);
        },
        /**
         * Réinitialise le formulaire et quitte le mode édition.
         * @returns {void}
         */
        resetForm() {
            this.questionData = createEmptyQuestion();
            this.originalChoixIds = [];
            this.selectedQuestionModelId = null;
        },
        /**
         * Prépare une question pour comparaison de contenu.
         * @param {Object} question Donnée question à comparer.
         * @returns {Object} Version simplifiée de la question.
         */
        buildComparableQuestion(question) {
            const normalizedQuestion = this.normalizeQuestion(question);
            const enonce = (normalizedQuestion.enonce || '').trim();
            const choix = (normalizedQuestion.choix || [])
                .map((item) => (item.texte_option || '').trim())
                .filter((texte) => texte !== '');

            return { enonce, choix };
        },
        /**
         * Indique si un modèle doit être affiché en surbrillance.
         * @param {Object} question Modèle de question de la liste.
         * @returns {boolean}
         */
        isModelHighlighted(question) {
            if (!question?.id || question.id !== this.selectedQuestionModelId) {
                return false;
            }

            const modelQuestion = this.buildComparableQuestion(question);
            const formQuestion = this.buildComparableQuestion(this.questionData);

            if (modelQuestion.enonce !== formQuestion.enonce) {
                return false;
            }

            if (modelQuestion.choix.length !== formQuestion.choix.length) {
                return false;
            }

            return modelQuestion.choix.every((choix, index) => choix === formQuestion.choix[index]);
        },
        /**
         * Prépare la suppression d'un modèle de question.
         * @param {number} index Position du modèle dans la liste.
         * @returns {void}
         */
        removeQuestion(index) {
            const question = this.questionModels[index];
            if (!question) return;
            this.questionASupprimer = { ...question, index };
        },
        /**
         * Supprime la question confirmée puis met à jour la liste locale.
         * @returns {Promise<void>}
         */
        async confirmerSuppressionQuestion() {
            if (!this.questionASupprimer) return;
            try {
                await questionOrganisateurService.deleteQuestion(this.questionASupprimer.id);
                this.questionModels.splice(this.questionASupprimer.index, 1);
                if (this.questionData.id === this.questionASupprimer.id) {
                    this.resetForm();
                }
                if (this.selectedQuestionModelId === this.questionASupprimer.id) {
                    this.selectedQuestionModelId = null;
                }
                this.questionASupprimer = null;
            } catch (error) {
                 console.error("Erreur lors de la suppression :", error.response?.data || error);
            }
        },
        /**
         * Synchronise les choix d'une question après sa création ou sa modification.
         * @param {number} questionId Identifiant de la question cible.
         * @param {boolean} updateMode Active le mode mise à jour des choix existants.
         * @returns {Promise<void>}
         */
        async synchroniserChoix(questionId, updateMode = false) {
            const choixValides = (this.questionData.choix || [])
                .map((choix) => ({
                    id: choix.id ?? null,
                    texte_option: (choix.texte_option || '').trim(),
                }))
                .filter((choix) => choix.texte_option !== '');

            if (!updateMode) {
                for (const choix of choixValides) {
                    await optionQuestionOrganisateurService.createChoix(questionId, {
                        texte_option: choix.texte_option,
                    });
                }
                return;
            }

            const idsConserves = [];
            for (const choix of choixValides) {
                if (choix.id) {
                    idsConserves.push(choix.id);
                    await optionQuestionOrganisateurService.modifyChoix(choix.id, {
                        texte_option: choix.texte_option,
                    });
                } else {
                    await optionQuestionOrganisateurService.createChoix(questionId, {
                        texte_option: choix.texte_option,
                    });
                }
            }

            const idsSupprimes = this.originalChoixIds.filter((id) => !idsConserves.includes(id));
            for (const idChoix of idsSupprimes) {
                await optionQuestionOrganisateurService.deleteChoix(idChoix);
            }
        },
        /**
         * Recharge la liste des modèles de questions depuis l'API.
         * @returns {Promise<void>}
         */
        async chargerModeles() {
            const response = await questionOrganisateurService.getAllQuestions();
            this.questionModels = response.data;
        },
        /**
         * Applique les actions communes après création/modification réussie.
         * @returns {Promise<void>}
         */
        async handleSuccessSubmit() {
            this.dataInserted = true;
            await this.chargerModeles();

            setTimeout(() => { this.dataInserted = false; }, 2000);
        },
        /**
         * Crée un nouveau modèle de question à partir du formulaire courant.
         * @returns {Promise<void>}
         */
        async createQuestion() {
            const enonce = (this.questionData.enonce || '').trim();
            if (!enonce) return;

            try {
                const response = await questionOrganisateurService.createQuestion({
                    enonce,
                    modele: true,
                });

                if (response.status === 201 || response.status === 200) {
                    const questionId = response.data?.question?.id;
                    if (questionId) {
                        await this.synchroniserChoix(questionId, false);
                    }
                    await this.handleSuccessSubmit();
                }
            } catch (error) {
                console.error("Erreur lors de l'envoi :", error.response?.data || error.message);
            }
        },
        /**
         * Modifie le modèle de question actuellement sélectionné.
         * @returns {Promise<void>}
         */
        async modifyQuestion() {
            if (!this.isEditing) return;

            const enonce = (this.questionData.enonce || '').trim();
            if (!enonce) return;

            try {
                const response = await questionOrganisateurService.modifyQuestion(this.questionData.id, {
                    enonce,
                    modele: true,
                });

                if (response.status === 201 || response.status === 200) {
                    await this.synchroniserChoix(this.questionData.id, true);
                    await this.handleSuccessSubmit();
                }
            } catch (error) {
                console.error("Erreur lors de la modification :", error.response?.data || error.message);
            }
        },
    },
    /**
     * Charge les modèles de question au montage du composant.
     * @returns {Promise<void>}
     */
    async mounted() {
        try {
            await this.chargerModeles();
        } catch (error) {
            console.error("Erreur au chargement :", error);
        }
    },
}
</script>
