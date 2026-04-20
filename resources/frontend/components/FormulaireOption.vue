<template>
    <div class="bg-secondary rounded-base p-6">
        <h1 class="text-subtitle my-4">Option</h1>
        <div class="flex flex-col-2 gap-4">
            <div class="basis-2/3">
                <OptionTemplate :optionModel="optionData" :border="false" :removeButton="true" />
                <div class="flex flex-row justify-end mt-4 gap-4">
                    <button v-if="isEditing" type="button" @click="resetForm" class="btn-accent-300">
                        Annuler
                    </button>
                    <button v-if="isEditing" type="button" @click="modifyOption" class="btn-tertiary-300 text-secondary">
                        Modifier l'option
                    </button>
                    <button type="button" @click="createOption" class="btn-tertiary">
                        Ajouter l'option
                    </button>
                </div>
            </div>
            <div class="basis-1/3 border-l border-default-medium pl-4">
                <h2 class="text-sm font-medium text-heading mb-2.5">Mes modèles</h2>
                <button
                    v-for="(option, index) in optionModels"
                    :key="option.id ?? index"
                    @click="copyDatas(option)"
                    :class="[
                        'btn-model flex flex-row items-center justify-between',
                        isModelHighlighted(option) ? 'bg-accent-300 text-heading ring-1 ring-accent' : ''
                    ]"
                >
                    {{ option.nom }}
                    <Icon icon="lucide:trash-2" width="20" height="20" class="text-accent hover:bg-accent-300 rounded-lg shrink-0" @click.stop="removeOption(index)"/>
                </button>
            </div>
        </div>

        <PopupConfirmation
            v-if="optionASupprimer"
            :message="`Supprimer l'option ${optionASupprimer.nom} ?`"
            icon="mdi:alert-circle-outline"
            @confirm="confirmerSuppressionOption"
            @cancel="optionASupprimer = null"
        />
    </div>
</template>

<script>
/**
 * @fileoverview Composant FormulaireOption.
 * @description Gestion des modèles d'options organisateur.
 * Permet la création d'options, la réutilisation d'un modèle existant et la
 * suppression avec confirmation.
 * @remarks Le composant orchestre la création, la suppression et la reprise de modèles
 * d'options tout en maintenant une liste locale cohérente avec les données API.
 */
import { Icon } from '@iconify/vue';
import OptionTemplate from './OptionTemplate.vue';
import PopupConfirmation from './PopupConfirmation.vue';
import optionOrganisateurService from '../services/optionOrganisateurService';

const createEmptyOption = () => ({
    id: null,
    nom: "",
    description: "",
    tarif: "",
    type: "Quantifiable",
    modele: true,
    quantifiable: {
        quantiteMin: 0,
        quantiteMax: 1,
    }
});

export default {
    components: {
        Icon,
        OptionTemplate,
        PopupConfirmation,
    },
    /**
     * Initialise le formulaire et la collection de modèles disponibles.
     * @returns {Object} État local de l'éditeur d'options.
     */
    data() {
        return {
            optionData: createEmptyOption(),
            optionModels: [],
            optionASupprimer: null,
            selectedOptionModelId: null,
            dataInserted: false,
        };
    },
    computed: {
        /**
         * Indique si le formulaire est en mode édition.
         * @returns {boolean}
         */
        isEditing() {
            return this.optionData.id !== null;
        },
    },
    methods: {
        /**
         * Normalise une option API vers le format du formulaire.
         * @param {Object} option Donnée option brute.
         * @returns {Object} Option normalisée.
         */
        normalizeOption(option) {
            const type = option?.type === 'Cochable' ? 'Cochable' : 'Quantifiable';
            return {
                id: option?.id ?? null,
                nom: option?.nom ?? '',
                description: option?.description ?? '',
                tarif: option?.tarif ?? '',
                type,
                modele: true,
                quantifiable: {
                    quantiteMin: option?.quantifiable?.quantiteMin ?? 0,
                    quantiteMax: option?.quantifiable?.quantiteMax ?? 1,
                },
            };
        },
        /**
         * Copie un modèle existant dans le formulaire courant.
         * @param {Object} option Option source sélectionnée dans la liste des modèles.
         * @returns {void}
         */
        copyDatas(option) {
            this.optionData = this.normalizeOption(option);
            this.selectedOptionModelId = option?.id ?? null;
        },
        /**
         * Réinitialise le formulaire et quitte le mode édition.
         * @returns {void}
         */
        resetForm() {
            this.optionData = createEmptyOption();
            this.selectedOptionModelId = null;
        },
        /**
         * Prépare une option pour comparaison de contenu.
         * @param {Object} option Donnée option à comparer.
         * @returns {Object} Version simplifiée de l'option.
         */
        buildComparableOption(option) {
            const normalizedOption = this.normalizeOption(option);
            return {
                nom: (normalizedOption.nom || '').trim(),
                description: (normalizedOption.description || '').trim(),
                tarif: normalizedOption.tarif === null || normalizedOption.tarif === undefined ? '' : String(normalizedOption.tarif),
                type: normalizedOption.type,
                quantiteMin: String(normalizedOption.quantifiable?.quantiteMin ?? 0),
                quantiteMax: String(normalizedOption.quantifiable?.quantiteMax ?? 1),
            };
        },
        /**
         * Indique si un modèle doit être affiché en surbrillance.
         * @param {Object} option Modèle de la liste.
         * @returns {boolean}
         */
        isModelHighlighted(option) {
            if (!option?.id || option.id !== this.selectedOptionModelId) {
                return false;
            }

            const modelOption = this.buildComparableOption(option);
            const formOption = this.buildComparableOption(this.optionData);

            if (
                modelOption.nom !== formOption.nom
                || modelOption.description !== formOption.description
                || modelOption.tarif !== formOption.tarif
                || modelOption.type !== formOption.type
            ) {
                return false;
            }

            if (modelOption.type !== 'Quantifiable') {
                return true;
            }

            return (
                modelOption.quantiteMin === formOption.quantiteMin
                && modelOption.quantiteMax === formOption.quantiteMax
            );
        },
        /**
         * Prépare le payload d'option pour l'API.
         * @returns {FormData}
         */
        buildOptionFormData() {
            const formData = new FormData();
            formData.append('nom', this.optionData.nom);
            formData.append('description', this.optionData.description);
            formData.append('tarif', this.optionData.tarif);
            formData.append('type', this.optionData.type);
            formData.append('modele', this.optionData.modele ? 1 : 0);

            if (this.optionData.type === 'Quantifiable') {
                formData.append('quantiteMin', this.optionData.quantifiable.quantiteMin);
                formData.append('quantiteMax', this.optionData.quantifiable.quantiteMax);
            }

            formData.append('courses[]', 1);
            return formData;
        },
        /**
         * Recharge la liste des options modèles.
         * @returns {Promise<void>}
         */
        async chargerModeles() {
            const updatedList = await optionOrganisateurService.getAllOptions();
            this.optionModels = updatedList.data;
        },
        /**
         * Prépare la suppression d'un modèle en ouvrant la confirmation.
         * @param {number} index Position du modèle dans la liste.
         * @returns {void}
         */
        removeOption(index) {
            const option = this.optionModels[index];
            if (!option) return;
            this.optionASupprimer = { ...option, index };
        },
        /**
         * Supprime le modèle confirmé puis met à jour la liste locale.
         * @returns {Promise<void>}
         */
        async confirmerSuppressionOption() {
            if (!this.optionASupprimer) return;
            try {
                await optionOrganisateurService.deleteOption(this.optionASupprimer.id);
                this.optionModels.splice(this.optionASupprimer.index, 1);
                if (this.optionData.id === this.optionASupprimer.id) {
                    this.resetForm();
                }
                if (this.selectedOptionModelId === this.optionASupprimer.id) {
                    this.selectedOptionModelId = null;
                }
                this.optionASupprimer = null;
            } catch (error) {
                console.error("Erreur lors de la suppression :", error.response?.data || error);
            }
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
         * Crée une nouvelle option modèle à partir du formulaire courant.
         * @returns {Promise<void>}
         */
        async createOption() {
            try {
                const response = await optionOrganisateurService.createOption(this.buildOptionFormData());
                
                if (response.status === 201) {
                    await this.handleSuccessSubmit();
                }
            } catch (error) {
                console.error("Erreur détaillée :", error.response?.data || error.message || error);
            }
        },
        /**
         * Modifie l'option modèle actuellement sélectionnée.
         * @returns {Promise<void>}
         */
        async modifyOption() {
            if (!this.isEditing) return;

            try {
                const response = await optionOrganisateurService.modifyOption(this.optionData.id, this.buildOptionFormData());

                if (response.status === 201 || response.status === 200) {
                    await this.handleSuccessSubmit();
                }
            } catch (error) {
                console.error("Erreur lors de la modification :", error.response?.data || error.message || error);
            }
        },
    },
    /**
     * Charge les modèles d'options à l'ouverture du composant.
     * @returns {Promise<void>}
     */
    async mounted(){
        try{
            await this.chargerModeles();
        } catch (error) {
            console.error("Erreur lors de la récupération des options :", error);
        }
    }
}
</script>
