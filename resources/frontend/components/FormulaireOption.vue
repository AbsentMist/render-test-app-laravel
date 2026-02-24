<template>
    <div class="bg-secondary rounded-b-base rounded-tr-base p-6">
        <h1 class="text-subtitle my-4">Créer une option</h1>
        <div class="flex flex-col-2 gap-4">
            <div class="basis-2/3">
                <OptionTemplate :optionModel="optionData" :border="false" :removeButton="true" />
                <div class="flex flex-row justify-end mt-4">
                    <button type="button" @click="handleSubmit" class="btn-tertiary">
                        Ajouter une option
                    </button>
                </div>
            </div>
            <div class="basis-1/3 border-l border-default-medium pl-4">
                <h2 class="text-sm font-medium text-heading mb-2.5">Mes modèles</h2>
                <button v-for="(option, index) in optionModels" :key="index" @click="copyDatas(option)" class="btn-model flex flex-row items-center justify-between">
                    {{ option.name }}
                    <Icon icon="mdi:delete" width="20" height="20" class="text-primary-900 hover:text-accent ml-2" @click.stop="removeOption(index)"/>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { Icon } from '@iconify/vue';
import OptionTemplate from './OptionTemplate.vue';

export default {
    components: {
        Icon,
        OptionTemplate

    },
    data() {
        return {
            optionData:
                {
                    name: "",
                    description: "",
                    prix: "",
                    quantiteMin: "",
                    quantiteMax: ""
                },
            optionModels: [
                {
                    name: "1 Entrée + 1 pasta bolognaise",
                    description: "Réservation entrée + pasta non-participant CHF 19.00 / paiement à RUNNINGENEVA ASSOCIATION",
                    prix: "15",
                    quantiteMin: "1",
                    quantiteMax: "10"
                },
                {
                    name: "1 Entrée + 1 fondue",
                    description: "Réservation entrée + fondue non-participant CHF 19.00 / paiement à RUNNINGENEVA ASSOCIATION",
                    prix: "15",
                    quantiteMin: "1",
                    quantiteMax: "10"
                }
            ],
        };
    },
     methods: {
        copyDatas(option) {
            this.optionData.name = option.name;
            this.optionData.description = option.description;
            this.optionData.prix = option.prix;
            this.optionData.quantiteMin = option.quantiteMin;
            this.optionData.quantiteMax = option.quantiteMax;
        },
        removeOption(index) {
            this.optionModels.splice(index, 1);
        },
        handleSubmit() {
            console.log("Option ajoutée :", this.optionData);
             // Ajouter l'option à la liste des options
        }
    },
}
</script>
