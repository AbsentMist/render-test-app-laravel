<template>
    <div class="bg-secondary rounded-b-base rounded-tr-base p-6">
        <h1 class="text-subtitle my-4">Créer une option</h1>
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
                    <Icon icon="mdi:delete" width="20" height="20" class="text-primary-900 hover:text-accent ml-2" @click.stop="removeOption(index)"/>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { Icon } from '@iconify/vue';
import OptionTemplate from './OptionTemplate.vue';
import optionOrganisateurService from '../services/optionOrganisateurService';

export default {
    components: {
        Icon,
        OptionTemplate,
        optionOrganisateurService
    },
    data() {
        return {
            optionData:
            {
                nom: "",
                description: "",
                tarif: "",
                quantifiable: {
                    quantiteMin: "",
                    quantiteMax: ""
                }
            },
            optionModels: [],

        };
    },
     methods: {
        copyDatas(option) {
            this.optionData.nom = option.nom;
            this.optionData.description = option.description;
            this.optionData.tarif = option.tarif;
            this.optionData.quantifiable.quantiteMin = option.quantifiable.quantiteMin;
            this.optionData.quantifiable.quantiteMax = option.quantifiable.quantiteMax;
        },
        async removeOption(index) {
            try {
                const option = this.optionModels[index];
                await optionOrganisateurService.deleteOption(option.id);
                this.optionModels.splice(index, 1);
            } catch (e) {
                 console.error("Erreur lors de la suppression :", error.response?.data || error);
            }
        },
        async handleSubmit() {
            try {
                const formData = new FormData();
                
                // 1. Données pour la table 'Option'
                formData.append('nom', this.optionData.nom);
                formData.append('description', this.optionData.description);
                formData.append('tarif', this.optionData.tarif);
                
                // On détermine le type
                const isQuantifiable = parseInt(this.optionData.quantifiable.quantiteMax) > 0;
                formData.append('type', isQuantifiable ? 'Quantifiable' : 'Cochable');

                // 2. Données pour la table 'OptionQuantifiable'
                if (isQuantifiable) {
                    formData.append('quantiteMin', this.optionData.quantifiable.quantiteMin || 0);
                    formData.append('quantiteMax', this.optionData.quantifiable.quantiteMax);
                }

                const response = await optionOrganisateurService.createOption(formData);
                
                if (response.status === 201) {
                    this.dataInserted = true;
                    // On rafraîchit la liste des modèles pour voir la nouvelle option
                    const updatedList = await optionOrganisateurService.getAllOptions();
                    this.optionModels = updatedList.data;
                    
                    setTimeout(() => { this.dataInserted = false; }, 2000);
                }
                console.log(response.data);
            } catch (error) {
                console.error("Erreur détaillée :", error.response?.data || error);
            }
        },
    },
    async mounted(){
        try{
            const response = await optionOrganisateurService.getAllOptions();
            this.optionModels = response.data;
            console.log(response.data);
        } catch (error) {
            console.error("Erreur lors de la récupération des options :", error);
        }
    }
}
</script>
