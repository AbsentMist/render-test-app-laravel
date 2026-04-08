<template>
    <div class="bg-secondary rounded-base p-6">
        <h1 class="text-subtitle my-4">Option</h1>
        <div class="flex flex-col-2 gap-4">
            <div class="basis-2/3">
                <OptionTemplate :optionModel="optionData" :border="false" :removeButton="true" />
                <div class="flex flex-row justify-end mt-4">
                    <button type="button" @click="handleSubmit()" class="btn-tertiary">
                        Ajouter une option
                    </button>
                </div>
            </div>
            <div class="basis-1/3 border-l border-default-medium pl-4">
                <h2 class="text-sm font-medium text-heading mb-2.5">Mes modèles</h2>
                <button v-for="(option, index) in optionModels" :key="index" @click="copyDatas(option)" class="btn-model flex flex-row items-center justify-between">
                    {{ option.nom }}
                    <Icon icon="lucide:trash-2" width="20" height="20" class="text-accent hover:bg-accent-300 rounded-lg flex-shrink-0" @click.stop="removeOption(index)"/>
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
            optionData:
            {
                nom: "",
                description: "",
                tarif: "",
                type: "Quantifiable",
                modele: true,
                quantifiable: {
                    quantiteMin: 0,
                    quantiteMax: 1,
                }
            },
            optionModels: [],
            optionASupprimer: null,
            dataInserted: false,
        };
    },
    methods: {
        /**
         * Copie un modèle existant dans le formulaire courant.
         * @param {Object} option Option source sélectionnée dans la liste des modèles.
         * @returns {void}
         */
        copyDatas(option) {
            this.optionData.nom = option.nom;
            this.optionData.description = option.description;
            this.optionData.tarif = option.tarif;
            this.optionData.type = option.type;
            this.optionData.quantifiable.quantiteMin = option.quantifiable.quantiteMin;
            this.optionData.quantifiable.quantiteMax = option.quantifiable.quantiteMax;
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
                this.optionASupprimer = null;
            } catch (error) {
                console.error("Erreur lors de la suppression :", error.response?.data || error);
            }
        },
        /**
         * Envoie une nouvelle option modèle à l'API puis recharge la liste des modèles.
         * @returns {Promise<void>}
         */
        async handleSubmit() {
            try {
                const formData = new FormData();
                formData.append('nom', this.optionData.nom);
                formData.append('description', this.optionData.description);
                formData.append('tarif', this.optionData.tarif);
                formData.append('type', this.optionData.type);
                formData.append('modele', this.optionData.modele? 1 : 0);

                if (this.optionData.type === 'Quantifiable') {
                    formData.append('quantiteMin', this.optionData.quantifiable.quantiteMin);
                    formData.append('quantiteMax', this.optionData.quantifiable.quantiteMax);
                }

                formData.append('courses[]', 1); 

                const response = await optionOrganisateurService.createOption(formData);
                
                if (response.status === 201) {
                    this.dataInserted = true;
                    const updatedList = await optionOrganisateurService.getAllOptions();
                    this.optionModels = updatedList.data;
                    
                    setTimeout(() => { this.dataInserted = false; }, 2000);
                }
            } catch (error) {
                console.error("Erreur détaillée :", error.response?.data || error.message || error);
            }
        },
    },
    /**
     * Charge les modèles d'options à l'ouverture du composant.
     * @returns {Promise<void>}
     */
    async mounted(){
        try{
            const response = await optionOrganisateurService.getAllOptions();
            this.optionModels = response.data;
        } catch (error) {
            console.error("Erreur lors de la récupération des options :", error);
        }
    }
}
</script>
