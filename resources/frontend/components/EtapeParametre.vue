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
                        ? 'border-tertiary bg-tertiary text-primary'
                        : 'border-gray-200 text-primary hover:border-tertiary hover:text-tertiary-900 bg-white'
                ]"
            >
                <Icon :icon="type.icone" class="w-5 h-5" />
                {{ type.nom }}
            </button>
        </div>

        <div v-if="typeSelectionne" class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm text-gray-600">
            <p v-if="typeSelectionne.id === 'challenge'">
                Le <strong>Challenge</strong> permet de participer en compétition au nom de ton université ou entreprise. Tu seras inscrit individuellement mais rattaché à ton organisation.
            </p>
            <p v-else-if="typeSelectionne.id === 'relais'">
                Le <strong>Relais</strong> permet de constituer une équipe. Chaque membre effectue une partie du parcours avant de passer le relais au suivant.
            </p>
            <p v-else-if="typeSelectionne.id === 'groupe'">
                L'inscription en <strong>Groupe</strong> vous permet de participer avec d'autres personnes. Vous choisirez le nom du groupe et ses membres à l'étape suivante.
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
        course:     { type: Object, required: true },
        modelValue: { type: Object, default: null },
    },
    emits: ['update:modelValue'],
    computed: {
        typesCourse() {
            const types = [];

            // Individuel est toujours disponible (sauf si la course est exclusivement Groupe ou Relais)
            if (this.course.type !== 'Relais' && this.course.type !== 'Groupe') {
                types.push({ id: 'individuel', nom: 'Individuel', icone: 'mdi:account-outline' });
            }

            if (this.course.type === 'Groupe') types.push({ id: 'groupe',    nom: 'Groupe',    icone: 'mdi:account-group' });
            if (this.course.type === 'Relais') types.push({ id: 'relais',    nom: 'Relais',    icone: 'mdi:account-group-outline' });

            // Challenge disponible uniquement si l'organisateur l'a activé
            if (this.course.is_challenge)      types.push({ id: 'challenge', nom: 'Challenge', icone: 'mdi:trophy-outline' });

            if (types.length === 0) types.push({ id: 'individuel', nom: 'Individuel', icone: 'mdi:account-outline' });
            return types;
        },
        typeSelectionne: {
            get() { return this.modelValue; },
            set(val) { this.$emit('update:modelValue', val); }
        }
    },
    methods: {
        selectionner(type) { this.typeSelectionne = type; }
    },
    mounted() {
        // Si un seul choix → auto-sélection (pas de choix à faire)
        if (this.typesCourse.length === 1 && !this.typeSelectionne) {
            this.typeSelectionne = this.typesCourse[0];
            return;
        }
        // Sinon pré-sélectionner "Individuel" par défaut
        if (!this.typeSelectionne) {
            const defaut = this.typesCourse.find(t => t.id === 'individuel') ?? this.typesCourse[0];
            this.typeSelectionne = defaut;
        }
    }
}
</script>