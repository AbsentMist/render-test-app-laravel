<template>
    <div class="border border-gray-300 rounded-base p-4">
        <div>
            <div class="flex flex-row justify-between items-center">
                <label class="block mb-2.5 text-sm font-medium text-heading">Question</label>
                <button v-if="!removeButton" type="button" @click="removeQuestion" class="mb-2.5 text-primary-900 hover:text-accent">
                    <Icon icon="mdi:close-circle" width="20" height="20" />
                </button>
            </div>
            <input
                type="text"
                v-model="questionModel.enonce"
                class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body"
                placeholder="Énoncé de la question..."
                required
            />
        </div>

        <label class="block my-2.5 text-sm font-medium text-heading">Réponses</label>
        <div v-for="(choix, index) in questionModel.choix" :key="index" class="my-2 flex flex-row gap-4 items-center">
            <input type="radio" disabled class="w-4 h-4 text-neutral-primary bg-neutral-secondary-medium rounded-full border border-default-medium appearance-none">
            <input
                type="text"
                v-model="questionModel.choix[index].texte_option"
                class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body"
                placeholder="Réponse..."
            />
            <button type="button" @click="questionModel.choix.splice(index, 1)" class="text-body hover:text-red-500">
                <Icon icon="mdi:close" class="w-4 h-4" />
            </button>
        </div>

        <button type="button" @click="questionModel.choix.push({ texte_option: '' })" class="mt-2 text-sm text-primary hover:underline flex items-center gap-1">
            <Icon icon="mdi:plus" class="w-4 h-4" /> Ajouter une réponse
        </button>
    </div>
</template>

<script>
/**
 * @fileoverview Composant QuestionTemplate.
 * @description Gabarit d'édition d'une question et de ses choix pour les formulaires questionnaire.
 * @remarks Le composant édite en direct l'objet question reçu par référence et notifie
 * le parent lorsqu'une question doit être retirée de la liste.
 */
import { Icon } from "@iconify/vue";
export default {
    name: 'QuestionTemplate',
    components: { Icon },
    emits: ['remove-question'],
    props: {
        questionModel: {
            type: Object,
            required: false,
            default: () => ({ enonce: '', choix: [] }),
        },
        removeButton: {
            type: Boolean,
            required: false,
            default: false,
        },
    },
    methods: {
        /**
         * Demande la suppression de la question courante au composant parent.
         * @returns {void}
         */
        removeQuestion() {
            this.$emit('remove-question', this.questionModel);
        },
    },
};
</script>