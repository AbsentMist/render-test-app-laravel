<template>
  <div :class="inline ? 'flex flex-col h-full' : 'fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm'">
    <div
      :class="inline ? 'flex flex-col h-full w-full' : 'relative bg-white rounded-2xl shadow-2xl w-full max-w-6xl mx-4 flex flex-col overflow-hidden'"
      :style="inline ? '' : 'height: 80vh'"
    >
      <div v-if="!inline" class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-tertiary-900">
        <div>
          <span class="text-subtitle font-medium text-secondary">Résultats du questionnaire</span>
          <p class="mt-1 text-sm text-secondary/80">
            {{ course?.nom || 'Course' }}
          </p>
        </div>
        <div class="flex items-center gap-2">
          <button
            v-if="questionnaire.length > 0 && !chargement && !erreur"
            @click="exporterCsv"
            class="inline-flex items-center gap-2 rounded-lg bg-white px-3 py-2 text-xs font-semibold text-primary hover:bg-gray-50 transition-colors"
          >
            <Icon icon="mdi:file-export-outline" class="w-4 h-4" />
            Export CSV
          </button>
          <button @click="$emit('close')" class="text-secondary hover:text-gray-600 transition-colors">
            <Icon icon="mdi:close" class="w-5 h-5" />
          </button>
        </div>
      </div>

      <div class="flex-1 overflow-y-auto p-6">
        <div v-if="chargement" class="flex items-center justify-center py-16 text-gray-400">
          <p class="text-sm">Chargement des résultats du questionnaire...</p>
        </div>

        <div v-else-if="erreur" class="flex flex-col items-center justify-center py-16 text-accent">
          <Icon icon="mdi:alert-circle-outline" class="w-12 h-12 mb-3 opacity-50" />
          <p class="text-sm text-center">{{ erreur }}</p>
        </div>

        <div v-else-if="questionnaire.length > 0" class="space-y-4">
          <div v-for="question in questionnaire" :key="'q-' + question.id" class="bg-gray-50 rounded-xl p-4 border border-gray-200">
            <div class="flex items-start justify-between gap-4 mb-3">
              <p class="font-medium text-gray-800">{{ question.question }}</p>
              <span class="shrink-0 rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-500 border border-gray-200">
                {{ getTotalSelections(question.id) }} sélection{{ getTotalSelections(question.id) > 1 ? 's' : '' }}
              </span>
            </div>

            <div class="space-y-2">
              <p class="text-xs text-gray-500 font-semibold">Réponses et statistiques :</p>
              <div v-for="answer in question.answers" :key="answer.id" class="flex items-center gap-3 rounded-lg bg-white px-3 py-2 border border-gray-100">
                <span class="min-w-10 rounded-full bg-tertiary-100 px-2.5 py-1 text-center text-sm font-bold text-tertiary-900">
                  {{ getAnswerCount(question.id, answer.id) }}
                </span>
                <span class="text-sm text-gray-700">{{ answer.texte }}</span>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="flex flex-col items-center justify-center py-16 text-gray-400">
          <Icon icon="mdi:comment-question-outline" class="w-12 h-12 mb-3 opacity-40" />
          <p class="text-sm text-center">Aucune question associée à cette course.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Icon } from '@iconify/vue';
import courseOrganisateurService from '../services/courseOrganisateurService';
import reponseQuestionOrganisateurService from '../services/reponseQuestionOrganisateurService';

/**
 * @fileoverview PopupQuestionnaireResultat.
 * @description Affiche les questions d'une course avec les statistiques de sélection par réponse.
 * @remarks Les totaux sont calculés à partir des réponses enregistrées par les participants.
 */
export default {
    name: 'PopupQuestionnaireResultat',
    components: {
        Icon,
    },
    props: {
        course: {
            type: Object,
            default: null,
        },
        inline: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['close'],
    data() {
        return {
            chargement: false,
            erreur: '',
            statistiquesReponses: {},
        coursComplet: null,
        };
    },
    computed: {
        /**
         * Questionnaire normalisé pour l'affichage.
         * @returns {Array}
         */
        questionnaire() {
        return this.normaliserQuestionnaire(this.coursComplet?.questionnaire ?? this.coursComplet?.questions ?? this.course?.questionnaire ?? this.course?.questions ?? []);
        },
    },
    watch: {
        course: {
            immediate: true,
            handler() {
          this.chargerQuestionnaireEtStatistiques();
            },
        },
    },
    methods: {
      /**
       * Charge la course complète puis ses statistiques de réponses.
       * @returns {Promise<void>}
       */
      async chargerQuestionnaireEtStatistiques() {
        this.chargement = true;
        this.erreur = '';
        this.statistiquesReponses = {};
        this.coursComplet = null;

        try {
          if (this.course?.id) {
            const responseCourse = await courseOrganisateurService.getCourse(this.course.id);
            this.coursComplet = responseCourse.data ?? this.course;
          } else {
            this.coursComplet = this.course;
          }

          const questionnaire = this.questionnaire;
          const statistiques = {};

          await Promise.all(questionnaire.map(async (question) => {
            try {
              const response = await reponseQuestionOrganisateurService.getReponsesQuestion(question.id);
              statistiques[question.id] = this.compterReponses(response.data ?? []);
            } catch (e) {
              statistiques[question.id] = {};
            }
          }));

          this.statistiquesReponses = statistiques;
        } catch (e) {
          this.erreur = 'Impossible de charger les résultats du questionnaire.';
        } finally {
          this.chargement = false;
        }
      },
        /**
         * Transforme le questionnaire reçu en structure homogène pour le rendu.
         * @param {Array} questions
         * @returns {Array}
         */
        normaliserQuestionnaire(questions) {
            return (questions ?? []).map((question) => ({
                id: question.id,
                question: question.question || question.enonce || question.texte || '—',
          answers: (question.answers || question.choix || question.options || []).map((answer) => ({
                    id: answer.id,
                    texte: answer.option || answer.texte || answer.texte_option || answer.libelle || '—',
                })),
            }));
        },
        /**
         * Compte le nombre de sélections par réponse pour une question donnée.
         * @param {Array} reponses
         * @returns {Object}
         */
        compterReponses(reponses) {
            return (reponses ?? []).reduce((accumulateur, reponse) => {
                const idOption = reponse.id_option_choisie ?? reponse.option?.id ?? null;

                if (idOption === null || idOption === undefined) {
                    return accumulateur;
                }

                accumulateur[idOption] = (accumulateur[idOption] ?? 0) + 1;
                return accumulateur;
            }, {});
        },
        /**
         * Retourne le nombre de sélections pour une réponse.
         * @param {number|string} idQuestion
         * @param {number|string} idAnswer
         * @returns {number}
         */
        getAnswerCount(idQuestion, idAnswer) {
            return this.statistiquesReponses[idQuestion]?.[idAnswer] ?? 0;
        },
        /**
         * Retourne le total des réponses enregistrées pour une question.
         * @param {number|string} idQuestion
         * @returns {number}
         */
        getTotalSelections(idQuestion) {
            return Object.values(this.statistiquesReponses[idQuestion] ?? {}).reduce((total, count) => total + count, 0);
        },
        /**
         * Échappe une valeur CSV pour un séparateur `;`.
         * @param {unknown} valeur
         * @returns {string}
         */
        preparerCSV(valeur) {
          const texte = String(valeur ?? '');
          return `"${texte.replaceAll('"', '""')}"`;
        },
        /**
         * Génère et télécharge un CSV des résultats du questionnaire.
         * @returns {void}
         */
        exporterCsv() {
          const lignes = [
            ['Question', 'Réponse', 'Sélections', 'Total question', 'Pourcentage'].join(';'),
          ];

          for (const question of this.questionnaire) {
            const totalQuestion = this.getTotalSelections(question.id);

            for (const answer of question.answers) {
              const totalSelections = this.getAnswerCount(question.id, answer.id);
              const pourcentage = totalQuestion > 0
                ? ((totalSelections / totalQuestion) * 100).toFixed(2)
                : '0.00';

              lignes.push([
                this.preparerCSV(question.question),
                this.preparerCSV(answer.texte),
                totalSelections,
                totalQuestion,
                pourcentage,
              ].join(';'));
            }
          }

          const contenu = `\ufeffsep=;\n${lignes.join('\n')}`;
          const blob = new Blob([contenu], { type: 'text/csv;charset=utf-8;' });
          const url = window.URL.createObjectURL(blob);
          const lien = document.createElement('a');
          lien.href = url;
          lien.setAttribute('download', `questionnaire_${this.course?.nom || 'course'}.csv`);
          document.body.appendChild(lien);
          lien.click();
          lien.remove();
          window.URL.revokeObjectURL(url);
        },
    },
};
</script>