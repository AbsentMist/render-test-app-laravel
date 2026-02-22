<template>
    <div class="border border-gray-300 rounded-base p-4">
        <div>
            <div class="flex flex-row justify-between items-center">
                    <label class="block mb-2.5 text-sm font-medium text-heading">Question</label>
                    <button type="button" @click="removeQuestion" class="mb-2.5 text-primary-900 hover:text-accent">
                        <Icon icon="mdi:close-circle" width="20" height="20" />
                    </button>
                </div>
            <input type="text" v-model="questionModel.question" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" required />
        </div>

        <label class="block my-2.5 text-sm font-medium text-heading">Réponses</label>

        <div v-for="(answer, index) in questionModel.answers" :key="index" class="my-2 flex flex-row gap-4 items-center">
            <input type="radio" disabled class="w-4 h-4 text-neutral-primary border-default-medium bg-neutral-secondary-medium rounded-full border border-default appearance-none">
            <input type="text" v-model="questionModel.answers[index]" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="Réponse..." />
            <button type="button" @click="questionModel.answers.splice(index, 1)" class="text-body hover:text-red-500">
                <Icon icon="mdi:close" class="w-4 h-4" />
            </button>
        </div>

        <button type="button" @click="questionModel.answers.push('')" class="mt-2 text-sm text-primary hover:underline flex items-center gap-1">
            <Icon icon="mdi:plus" class="w-4 h-4" /> Ajouter une réponse
        </button>
    </div>
</template>

<script>
import { Icon } from "@iconify/vue";

export default {
    components: { Icon },
    props: {
        questionModel: {
            type: Object,
            required: false,
            default: () => ({})
        },
    },
    methods: {
        removeQuestion() {
            this.$emit('remove-question', this.questionModel);
        }
    },
}
</script>