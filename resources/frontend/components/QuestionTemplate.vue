<template>
    <div class="border border-gray-300 rounded-base p-4">
        <div>
            <label class="block mb-2.5 text-sm font-medium text-heading">Question</label>
            <input type="text" id="question" v-model="question" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="" required />
        </div>

        <label class="block my-2.5 text-sm font-medium text-heading">Réponses</label>
        <div v-for="answer in answers" class="my-4 flex flex-row gap-4">
           <input id="default-radio-1" type="radio" value="" name="default-radio" class="w-4 h-4 text-neutral-primary border-default-medium bg-neutral-secondary-medium rounded-full checked:border-brand focus:ring-2 focus:outline-none focus:ring-brand-subtle border border-default appearance-none">
            <label for="default-radio-1" class="select-none ms-2 text-sm font-medium text-heading">{{ answer }}</label>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        existingQuestions: {
            type: Object,
            required: false,
            default: () => ({})
        },
    },
    data() {
        return {
            question: this.existingQuestions.question || '',
            answers: this.existingQuestions.answers || [''],
        }
    }
}
</script>