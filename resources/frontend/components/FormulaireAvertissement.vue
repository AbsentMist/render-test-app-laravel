<template>
    <div class="bg-secondary rounded-b-base rounded-tl-base p-6">
        <h1 class="text-subtitle my-4">Avertissement</h1>
        <p class="mb-4 text-body text-sm">
            Cette page apparaîtra dès la sélection de la course. Elle sert à avertir les participants de risques potentiels.
        </p>

        <div class="flex flex-row gap-4">
            <div class="basis-2/3 flex flex-col justify-between h-[450px]">
                <div class="space-y-4 flex-grow">
                    <div>
                        <label for="titre" class="block mb-2 text-sm font-medium text-heading">Nom du modèle</label>
                        <input type="text" id="titre" v-model="avertissementData.titre" 
                            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" 
                            placeholder="Ex: Risques météo" required />
                    </div>     
                    <div class="flex flex-col">
                        <label for="avertissement" class="block mb-2 text-sm font-medium text-heading">Contenu de l'avertissement</label>
                        <textarea id="avertissement" v-model="avertissementData.contenu" 
                            class="resize-none h-[280px] bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" 
                            placeholder="Décrivez les risques..." required />
                    </div>
                </div>

                <div class="flex flex-row justify-end mt-4">
                    <button type="button" @click="handleSubmit" class="btn-tertiary">
                        Ajouter l'avertissement
                    </button>
                </div>
            </div>

            <div class="basis-1/3 border-l border-default-medium pl-4">
                <h2 class="text-sm font-medium text-heading mb-2.5">Mes modèles</h2>
                <div class="flex flex-col gap-2 h-[415px] overflow-y-auto pr-2 scrollbar-thin">
                    <button v-for="(avertissement, index) in avertissementModels" 
                        :key="index" 
                        type="button" 
                        @click="copyDatas(avertissement)" 
                        class="btn-model flex flex-row items-center justify-between w-full text-left min-h-[42px]">
                        <span class="truncate pr-2">{{ avertissement.titre }}</span>
                        <Icon icon="lucide:trash-2" width="20" height="20" 
                            class="text-accent hover:bg-accent-300 rounded-lg flex-shrink-0" 
                            @click.stop="removeAvertissement(index)"/>
                    </button>

                    <p v-if="avertissementModels.length === 0" class="text-xs text-body italic">
                        Aucun modèle enregistré.
                    </p>
                </div>
            </div>
        </div>

        <PopupConfirmation
            v-if="avertissementASupprimer"
            :message="`Supprimer l'avertissement ${avertissementASupprimer.titre} ?`"
            icon="mdi:alert-circle-outline"
            @confirm="confirmerSuppressionAvertissement"
            @cancel="avertissementASupprimer = null"
        />
    </div>
</template>

<script>
import { Icon } from '@iconify/vue';
import PopupConfirmation from './PopupConfirmation.vue';
import avertissementOrganisateurService from '../services/avertissementOrganisateurService';

export default {
    components: {
        Icon,
        PopupConfirmation,
    },
    data() {
        return {
            avertissementData: {
                titre: "",
                contenu: "",
                modele: true,
            },
            avertissementModels: [],
            dataInserted: false, // Pour gérer l'état de succès
            avertissementASupprimer: null,
        }
    },
    methods: {
        copyDatas(avertissement) {
            this.avertissementData.titre = avertissement.titre;
            this.avertissementData.contenu = avertissement.contenu;
        },
        async removeAvertissement(index) {
            const avertissement = this.avertissementModels[index];
            if (!avertissement) return;
            this.avertissementASupprimer = { ...avertissement, index };
        },
        async confirmerSuppressionAvertissement() {
            if (!this.avertissementASupprimer) return;
            try {
                await avertissementOrganisateurService.deleteAvertissement(this.avertissementASupprimer.id);
                this.avertissementModels.splice(this.avertissementASupprimer.index, 1);
                this.avertissementASupprimer = null;
            } catch (error) {
                 console.error("Erreur lors de la suppression :", error.response?.data || error);
            }
        },
        async handleSubmit() {
            try {
                const formData = new FormData();
                formData.append('titre', this.avertissementData.titre);
                formData.append('contenu', this.avertissementData.contenu);
                formData.append('modele', this.avertissementData.modele ? 1 : 0);
                formData.append('courses[]', 1); 

                const response = await avertissementOrganisateurService.createAvertissement(formData);
                
                if (response.status === 201 || response.status === 200) {
                    this.dataInserted = true;
                    // Rafraîchissement de la liste
                    const updatedList = await avertissementOrganisateurService.getAllAvertissement();
                    this.avertissementModels = updatedList.data;
                    
                    // Reset du formulaire après succès (optionnel)
                    this.avertissementData.titre = "";
                    this.avertissementData.contenu = "";

                    setTimeout(() => { this.dataInserted = false; }, 2000);
                }
            } catch (error) {
                console.error("Erreur lors de l'envoi :", error.response?.data || error.message);
            }
        },
    },
    async mounted() {
        try {
            const response = await avertissementOrganisateurService.getAllAvertissement();
            this.avertissementModels = response.data;
        } catch (error) {
            console.error("Erreur au chargement :", error);
        }
    }
}
</script>