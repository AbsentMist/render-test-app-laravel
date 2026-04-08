<template>
    <div class="flex flex-col gap-4 mt-4">
        <h2 class="text-base font-semibold text-heading">Sélectionnez vos options</h2>

        <div v-if="!options || options.length === 0" class="text-sm text-gray-400 text-center py-6">
            Aucune option disponible pour cette course.
        </div>

        <div v-for="option in options" :key="option.id"
            class="flex items-center justify-between border border-gray-200 rounded-xl px-4 py-3 bg-white gap-4">

            <div class="flex-1">
                <p class="text-sm font-medium text-heading">{{ option.nom }}</p>
                <p v-if="option.description" class="text-xs text-gray-400 mt-0.5">{{ option.description }}</p>

                <!-- Quantifiable : sélecteur de quantité -->
                <div v-if="option.type === 'Quantifiable'" class="mt-2">
                    <select
                        v-model="optionsSelectionnees[option.id].quantite"
                        @change="mettreAJour"
                        class="border border-gray-300 rounded-lg text-sm px-2 py-1 focus:outline-none focus:ring-2 focus:ring-secondary/40"
                    >
                        <option
                            v-for="q in quantitesDisponibles(option)"
                            :key="q"
                            :value="q"
                        >{{ q }}</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <span class="text-sm font-semibold text-gray-700">{{ option.tarif }}.-</span>

                <!-- Cochable : bouton ajouter / retirer -->
                <template v-if="option.type === 'Cochable'">
                    <button
                        v-if="!optionsSelectionnees[option.id]?.selectionne"
                        type="button"
                        @click="ajouterCochable(option)"
                        class="btn-tertiary text-xs px-3 py-1"
                    >
                        Ajouter l'option
                    </button>
                    <button
                        v-else
                        type="button"
                        @click="retirerCochable(option)"
                        class="btn-accent-300 text-xs px-3 py-1"
                    >
                        Retirer
                    </button>
                </template>

                <!-- Quantifiable : bouton ajouter si quantité > 0 -->
                <template v-else>
                    <button
                        v-if="!optionsSelectionnees[option.id]?.selectionne"
                        type="button"
                        @click="ajouterQuantifiable(option)"
                        class="btn-tertiary text-xs px-3 py-1"
                    >
                        Ajouter l'option
                    </button>
                    <button
                        v-else
                        type="button"
                        @click="retirerCochable(option)"
                        class="btn-accent-300 text-xs px-3 py-1"
                    >
                        Retirer
                    </button>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
/**
 * @fileoverview Composant EtapeOptions.
 * @description Étape d'inscription consacrée aux options complémentaires sélectionnables pour une course.
 * @remarks Ce composant normalise l'état local des options cochables et quantifiables,
 * puis émet une structure homogène vers le parent pour calcul du total et persistance panier.
 */
export default {
    name: 'EtapeOptions',
    props: {
        options: {
            type: Array,
            default: () => [],
        },
        modelValue: {
            type: Object,
            default: () => ({}),
        },
    },
    emits: ['update:modelValue'],
    /**
     * Initialise l'état local des options en cours de sélection.
     * @returns {{optionsSelectionnees: Object}} Dictionnaire indexé par identifiant d'option.
     */
    data() {
        return {
            optionsSelectionnees: {},
        };
    },
    watch: {
        options: {
            immediate: true,
            /**
             * Réinitialise l'état local à chaque changement de liste d'options.
             * @param {Array} opts Liste des options renvoyées pour la course sélectionnée.
             * @returns {void}
             */
            handler(opts) {
                const etat = {};
                (opts || []).forEach(opt => {
                    etat[opt.id] = {
                        option: opt,
                        selectionne: false,
                        quantite: opt.quantifiable?.quantite_min ?? 1,
                    };
                });
                this.optionsSelectionnees = etat;
            }
        }
    },
    methods: {
        /**
         * Génère les quantités autorisées pour une option quantifiable.
         * @param {Object} option Option quantifiable contenant ses bornes min/max.
         * @returns {number[]} Tableau de quantités utilisables dans le sélecteur.
         */
        quantitesDisponibles(option) {
            const min = option.quantifiable?.quantite_min ?? 1;
            const max = option.quantifiable?.quantite_max ?? 10;
            return Array.from({ length: max - min + 1 }, (_, i) => i + min);
        },
        /**
         * Active une option cochable et propage la nouvelle sélection.
         * @param {Object} option Option cochable à activer.
         * @returns {void}
         */
        ajouterCochable(option) {
            this.optionsSelectionnees[option.id].selectionne = true;
            this.mettreAJour();
        },
        /**
         * Désactive une option actuellement sélectionnée.
         * @param {Object} option Option à retirer de la sélection.
         * @returns {void}
         */
        retirerCochable(option) {
            this.optionsSelectionnees[option.id].selectionne = false;
            this.mettreAJour();
        },
        /**
         * Active une option quantifiable avec la quantité courante choisie.
         * @param {Object} option Option quantifiable à ajouter.
         * @returns {void}
         */
        ajouterQuantifiable(option) {
            this.optionsSelectionnees[option.id].selectionne = true;
            this.mettreAJour();
        },
        /**
         * Construit et émet la charge utile des options effectivement retenues.
         * @returns {void}
         */
        mettreAJour() {
            const resultat = {};
            Object.values(this.optionsSelectionnees).forEach(({ option, selectionne, quantite }) => {
                if (selectionne) {
                    resultat[option.id] = { option, quantite };
                }
            });
            this.$emit('update:modelValue', resultat);
        }
    },
    computed: {
        /**
         * Calcule le total courant des options sélectionnées.
         * @returns {number} Montant cumulé des options actives.
         */
        totalOptions() {
            return Object.values(this.optionsSelectionnees)
                .filter(o => o.selectionne)
                .reduce((acc, { option, quantite }) => {
                    return acc + option.tarif * (option.type === 'Quantifiable' ? quantite : 1);
                }, 0);
        }
    }
}
</script>
