<template>
    <div class="flex flex-col gap-6 mt-4">

        <div v-if="!questions || questions.length === 0" class="text-sm text-gray-400 text-center py-6">
            Aucune question pour cette course.
        </div>

        <div v-for="(question, index) in questions" :key="question.id" class="flex flex-col gap-3">
            <p class="text-sm font-semibold text-heading">{{ question.question }}</p>

            <div class="flex flex-col gap-2">
                <label
                    v-for="(reponse, ri) in question.answers"
                    :key="reponse.id"
                    class="flex items-center gap-3 cursor-pointer group"
                >
                    <div class="relative flex items-center justify-center">
                        <input
                            type="radio"
                            :name="'question-' + question.id"
                            :value="reponse"
                            v-model="reponses[question.id]"
                            @change="mettreAJour"
                            class="hidden"
                        />
                        <div
                            :class="[
                                'w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all',
                                reponses[question.id] === reponse
    ? 'border-tertiary bg-tertiary'
    : 'border-gray-300 bg-white group-hover:border-tertiary'
                            ]"
                        >
                            <div v-if="reponses[question.id] === reponse" class="w-2 h-2 rounded-full bg-white"></div>
                        </div>
                    </div>
                    <span class="text-sm text-gray-700">{{ reponse.texte }}</span>
                </label>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'EtapeQuestionnaire',
    props: {
        questions: {
            type: Array,
            default: () => [],
        },
        modelValue: {
            type: Object,
            default: () => ({}),
        },
    },
    emits: ['update:modelValue'],
    data() {
        return {
            reponses: {},
        };
    },
    watch: {
        questions: {
            immediate: true,
            handler(qs) {
                const rep = {};
                (qs || []).forEach((q) => { rep[q.id] = null; });
                this.reponses = rep;
            }
        }
    },
    methods: {
        mettreAJour() {
            const resultat = {};
            (this.questions || []).forEach((q) => {
                resultat[q.id] = { question: q.question, reponse: this.reponses[q.id] };
            });
            this.$emit('update:modelValue', resultat);
        }
    }
}
</script>
