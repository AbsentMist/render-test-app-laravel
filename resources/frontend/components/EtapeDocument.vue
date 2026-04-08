<template>
    <div class="flex flex-col gap-4 mt-4">
        <div>
            <h2 class="text-base font-semibold text-heading">Ajoutez vos documents</h2>
            <p class="text-sm text-gray-500 mt-1">
                Veuillez soumettre vos documents uniquement si un des éléments suivant s'applique à vous.
            </p>
        </div>

        <div
            class="border-2 border-dashed rounded-xl flex flex-col items-center justify-center py-10 gap-4 cursor-pointer transition-all"
            :class="glisser ? 'border-secondary bg-secondary/5' : 'border-gray-300 bg-gray-50 hover:border-gray-400'"
            @dragover.prevent="glisser = true"
            @dragleave="glisser = false"
            @drop.prevent="deposerFichier"
            @click="$refs.inputFichier.click()"
        >
            <Icon icon="mdi:upload-outline" class="w-10 h-10 text-gray-400" />
            <p class="text-sm text-gray-500 text-center">
                Glissez-déposez un fichier ici ou<br />
                <span class="text-secondary font-medium">cliquez pour parcourir</span>
            </p>
            <p class="text-xs text-gray-400">PDF, JPG, PNG — max 10 Mo</p>

            <input
                ref="inputFichier"
                type="file"
                class="hidden"
                accept=".pdf,.jpg,.jpeg,.png"
                multiple
                @change="selectionnerFichier"
            />

            <button
                type="button"
                @click.stop="$refs.inputFichier.click()"
                class="btn-tertiary text-sm px-5 py-2"
            >
                Upload
            </button>
        </div>

        <div v-if="fichiers.length > 0" class="flex flex-col gap-2">
            <div
                v-for="(fichier, index) in fichiers"
                :key="index"
                class="flex items-center justify-between bg-white border border-gray-200 rounded-xl px-4 py-2"
            >
                <div class="flex items-center gap-3">
                    <Icon icon="mdi:file-document-outline" class="w-5 h-5 text-secondary" />
                    <div>
                        <p class="text-sm font-medium text-heading">{{ fichier.name }}</p>
                        <p class="text-xs text-gray-400">{{ formaterTaille(fichier.size) }}</p>
                    </div>
                </div>
                <button type="button" @click="retirerFichier(index)" class="text-gray-400 hover:text-red-500 transition-colors">
                    <Icon icon="mdi:close-circle" class="w-5 h-5" />
                </button>
            </div>
        </div>
    </div>
</template>

<script>
/**
 * @fileoverview Composant EtapeDocument.
 * @description Étape de dépôt des justificatifs associés à l'inscription.
 * @remarks Le composant gère la sélection par clic ou glisser-déposer, maintient la liste locale
 * des fichiers et émet chaque mise à jour pour synchroniser l'état parent.
 */
import { Icon } from '@iconify/vue';

export default {
    name: 'EtapeDocument',
    components: { Icon },
    props: {
        modelValue: {
            type: Array,
            default: () => [],
        },
    },
    emits: ['update:modelValue'],
    /**
     * Initialise l'état d'interface du dépôt de fichiers.
     * @returns {{glisser: boolean, fichiers: File[]}} État local de drag-and-drop et fichiers sélectionnés.
     */
    data() {
        return {
            glisser: false,
            fichiers: [],
        };
    },
    watch: {
        /**
         * Aligne la liste locale si la valeur est modifiée depuis le parent.
         * @param {File[]} nouvelleValeur Nouvelle liste de fichiers côté parent.
         * @returns {void}
         */
        modelValue: {
            immediate: true,
            handler(nouvelleValeur) {
                this.fichiers = Array.isArray(nouvelleValeur) ? [...nouvelleValeur] : [];
            },
        },
    },
    methods: {
        /**
         * Traite la sélection de fichiers depuis l'input natif.
         * @param {Event} event Événement de changement de l'input file.
         * @returns {void}
         */
        selectionnerFichier(event) {
            const nouveaux = Array.from(event.target.files);
            this.ajouterFichiers(nouveaux);
            event.target.value = '';
        },
        /**
         * Traite les fichiers déposés par glisser-déposer.
         * @param {DragEvent} event Événement de dépôt contenant les fichiers.
         * @returns {void}
         */
        deposerFichier(event) {
            this.glisser = false;
            const nouveaux = Array.from(event.dataTransfer.files);
            this.ajouterFichiers(nouveaux);
        },
        /**
         * Ajoute des fichiers à la liste locale puis propage la nouvelle valeur.
         * @param {File[]} nouveaux Fichiers à ajouter.
         * @returns {void}
         */
        ajouterFichiers(nouveaux) {
            this.fichiers = [...this.fichiers, ...nouveaux];
            this.$emit('update:modelValue', this.fichiers);
        },
        /**
         * Retire un fichier de la liste locale selon son index.
         * @param {number} index Position du fichier à supprimer.
         * @returns {void}
         */
        retirerFichier(index) {
            this.fichiers.splice(index, 1);
            this.$emit('update:modelValue', [...this.fichiers]);
        },
        /**
         * Formate une taille en octets pour affichage lisible.
         * @param {number} octets Taille brute en octets.
         * @returns {string} Valeur formatée en o, Ko ou Mo.
         */
        formaterTaille(octets) {
            if (octets < 1024) return octets + ' o';
            if (octets < 1024 * 1024) return (octets / 1024).toFixed(1) + ' Ko';
            return (octets / (1024 * 1024)).toFixed(1) + ' Mo';
        }
    }
}
</script>
