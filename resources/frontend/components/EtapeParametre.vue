<template>
    <div class="flex flex-col gap-6 mt-4">
        <h2 class="text-base font-semibold text-heading">Sélectionnez le type de course</h2>

        <div class="flex flex-wrap gap-4">
            <button
                v-for="type in typesCourse"
                :key="type.id"
                type="button"
                @click="selectionner(type)"
                :class="[
                    'flex items-center gap-3 px-6 py-4 rounded-xl border-2 text-sm font-medium transition-all flex-1 min-w-[140px]',
                    typeSelectionne?.id === type.id
                        ? 'border-secondary bg-secondary/10 text-secondary'
                        : 'border-gray-200 text-heading hover:border-gray-400 bg-white'
                ]"
            >
                <Icon :icon="type.icone" class="w-5 h-5" />
                {{ type.nom }}
            </button>
        </div>

        <!-- Infos type sélectionné -->
        <div v-if="typeSelectionne" class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm text-gray-600">
            <p v-if="typeSelectionne.id === 'challenge'">
                Le <strong>Challenge</strong> permet à chaque participant de s'inscrire individuellement et de disputer la course en compétition directe.
            </p>
            <p v-else-if="typeSelectionne.id === 'relais'">
                Le <strong>Relais</strong> permet de constituer une équipe. Chaque membre effectue une partie du parcours avant de passer le relais au suivant.
            </p>
            <p v-else>
                L'inscription <strong>Individuelle</strong> vous permet de participer seul à la course.
            </p>
        </div>
    </div>
</template>

<script>
import { Icon } from '@iconify/vue';

export default {
    name: 'EtapeParametre',
    components: { Icon },
    props: {
        course: {
            type: Object,
            required: true,
        },
        modelValue: {
            type: Object,
            default: null,
        },
    },
    emits: ['update:modelValue'],
    computed: {
        typesCourse() {
            const types = [];
            if (this.course.is_challenge) {
                types.push({ id: 'challenge', nom: 'Challenge', icone: 'mdi:trophy-outline' });
            }
            if (this.course.type === 'Relais') {
                types.push({ id: 'relais', nom: 'Relais', icone: 'mdi:account-group-outline' });
            }
            if (!this.course.is_challenge && this.course.type !== 'Relais') {
                types.push({ id: 'individuel', nom: 'Individuel', icone: 'mdi:account-outline' });
            }
            // Si la course propose à la fois challenge ET relais
            if (types.length === 0) {
                types.push({ id: 'individuel', nom: 'Individuel', icone: 'mdi:account-outline' });
            }
            return types;
        },
        typeSelectionne: {
            get() { return this.modelValue; },
            set(val) { this.$emit('update:modelValue', val); }
        }
    },
    methods: {
        selectionner(type) {
            this.typeSelectionne = type;
        }
    },
    mounted() {
        // Pré-sélectionner automatiquement si un seul type
        if (this.typesCourse.length === 1 && !this.typeSelectionne) {
            this.typeSelectionne = this.typesCourse[0];
        }
    }
}
</script>
