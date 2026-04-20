<template>
    <div class="bg-secondary rounded-b-base rounded-tl-base p-6">
        <h1 class="text-subtitle my-4">Avertissement</h1>
        <p class="mb-4 text-body text-sm">
            Cette page apparaîtra dès la sélection de la course. Elle sert à avertir les participants de risques potentiels.
        </p>

        <div class="flex flex-row gap-4">
            <div class="basis-2/3 flex flex-col justify-between h-112.5">
                <div class="space-y-4 grow">
                    <div>
                        <label for="titre" class="block mb-2 text-sm font-medium text-heading">Nom du modèle</label>
                        <input type="text" id="titre" v-model="avertissementData.titre" 
                            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" 
                            placeholder="Ex: Risques météo" required />
                    </div>     
                    <div class="flex flex-col">
                        <label for="avertissement" class="block mb-2 text-sm font-medium text-heading">Contenu de l'avertissement</label>
                        <textarea id="avertissement" v-model="avertissementData.contenu" 
                            class="resize-none h-70 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" 
                            placeholder="Décrivez les risques..." required />
                    </div>
                </div>

                <div class="flex flex-row justify-end mt-4 gap-4">
                    <button v-if="isEditing" type="button" @click="resetForm" class="btn-accent-300">
                        Annuler
                    </button>
                    <button v-if="isEditing" type="button" @click="modifyAvertissement" class="btn-tertiary-300 text-secondary">
                        Modifier l'avertissement
                    </button>
                    <button type="button" @click="createAvertissement" class="btn-tertiary">
                        Ajouter l'avertissement
                    </button>
                </div>
            </div>

            <div class="basis-1/3 border-l border-default-medium pl-4">
                <h2 class="text-sm font-medium text-heading mb-2.5">Mes modèles</h2>
                <div class="flex flex-col gap-2 h-103.75 overflow-y-auto pr-2 scrollbar-thin">
                    <button v-for="(avertissement, index) in avertissementModels" 
                        :key="avertissement.id ?? index" 
                        type="button" 
                        @click="copyDatas(avertissement)" 
                        :class="[
                            'btn-model flex flex-row items-center justify-between w-full text-left min-h-10.5',
                            isModelHighlighted(avertissement) ? 'bg-accent-300 text-heading ring-1 ring-accent' : ''
                        ]">
                        <span class="truncate pr-2">{{ avertissement.titre }}</span>
                        <Icon icon="lucide:trash-2" width="20" height="20" 
                            class="text-accent hover:bg-accent-300 rounded-lg shrink-0" 
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
/**
 * @fileoverview Composant FormulaireAvertissement.
 * @description Gestion des modèles d'avertissement utilisés dans les inscriptions.
 * Permet de créer, préremplir depuis un modèle existant et supprimer un
 * avertissement avec confirmation utilisateur.
 * @remarks Le composant pilote le cycle complet des modèles (chargement, création,
 * reprise et suppression) avec mise à jour locale immédiate après chaque action.
 */
import { Icon } from '@iconify/vue';
import PopupConfirmation from './PopupConfirmation.vue';
import avertissementOrganisateurService from '../services/avertissementOrganisateurService';

const createEmptyAvertissement = () => ({
    id: null,
    titre: "",
    contenu: "",
    modele: true,
});

export default {
    components: {
        Icon,
        PopupConfirmation,
    },
    /**
     * Initialise les données du formulaire et la liste des modèles existants.
     * @returns {Object} État local de l'éditeur d'avertissements.
     */
    data() {
        return {
            avertissementData: createEmptyAvertissement(),
            avertissementModels: [],
            dataInserted: false,
            avertissementASupprimer: null,
            selectedAvertissementModelId: null,
        }
    },
    computed: {
        /**
         * Indique si le formulaire est en mode édition.
         * @returns {boolean}
         */
        isEditing() {
            return this.avertissementData.id !== null;
        },
    },
    methods: {
        /**
         * Normalise un avertissement API vers le format local du formulaire.
         * @param {Object} avertissement Donnée avertissement brute.
         * @returns {Object} Avertissement normalisé.
         */
        normalizeAvertissement(avertissement) {
            return {
                id: avertissement?.id ?? null,
                titre: avertissement?.titre ?? '',
                contenu: avertissement?.contenu ?? '',
                modele: true,
            };
        },
        /**
         * Copie un modèle existant dans le formulaire courant.
         * @param {Object} avertissement Modèle source sélectionné.
         * @returns {void}
         */
        copyDatas(avertissement) {
            this.avertissementData = this.normalizeAvertissement(avertissement);
            this.selectedAvertissementModelId = avertissement?.id ?? null;
        },
        /**
         * Réinitialise le formulaire et quitte le mode édition.
         * @returns {void}
         */
        resetForm() {
            this.avertissementData = createEmptyAvertissement();
            this.selectedAvertissementModelId = null;
        },
        /**
         * Prépare un avertissement pour comparaison de contenu.
         * @param {Object} avertissement Donnée avertissement à comparer.
         * @returns {Object} Version simplifiée de l'avertissement.
         */
        buildComparableAvertissement(avertissement) {
            const normalizedAvertissement = this.normalizeAvertissement(avertissement);
            return {
                titre: (normalizedAvertissement.titre || '').trim(),
                contenu: (normalizedAvertissement.contenu || '').trim(),
            };
        },
        /**
         * Indique si un modèle doit être affiché en surbrillance.
         * @param {Object} avertissement Modèle de la liste.
         * @returns {boolean}
         */
        isModelHighlighted(avertissement) {
            if (!avertissement?.id || avertissement.id !== this.selectedAvertissementModelId) {
                return false;
            }

            const modelAvertissement = this.buildComparableAvertissement(avertissement);
            const formAvertissement = this.buildComparableAvertissement(this.avertissementData);

            return (
                modelAvertissement.titre === formAvertissement.titre
                && modelAvertissement.contenu === formAvertissement.contenu
            );
        },
        /**
         * Recharge la liste des avertissements modèles.
         * @returns {Promise<void>}
         */
        async chargerModeles() {
            const updatedList = await avertissementOrganisateurService.getAllAvertissement();
            this.avertissementModels = updatedList.data;
        },
        /**
         * Prépare le payload avertissement pour l'API.
         * @returns {FormData}
         */
        buildAvertissementFormData() {
            const formData = new FormData();
            formData.append('titre', this.avertissementData.titre);
            formData.append('contenu', this.avertissementData.contenu);
            formData.append('modele', this.avertissementData.modele ? 1 : 0);
            formData.append('courses[]', 1);
            return formData;
        },
        /**
         * Prépare la suppression d'un modèle d'avertissement.
         * @param {number} index Position du modèle dans la liste.
         * @returns {void}
         */
        async removeAvertissement(index) {
            const avertissement = this.avertissementModels[index];
            if (!avertissement) return;
            this.avertissementASupprimer = { ...avertissement, index };
        },
        /**
         * Supprime le modèle confirmé puis met à jour la liste locale.
         * @returns {Promise<void>}
         */
        async confirmerSuppressionAvertissement() {
            if (!this.avertissementASupprimer) return;
            try {
                await avertissementOrganisateurService.deleteAvertissement(this.avertissementASupprimer.id);
                this.avertissementModels.splice(this.avertissementASupprimer.index, 1);
                if (this.avertissementData.id === this.avertissementASupprimer.id) {
                    this.resetForm();
                }
                if (this.selectedAvertissementModelId === this.avertissementASupprimer.id) {
                    this.selectedAvertissementModelId = null;
                }
                this.avertissementASupprimer = null;
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
            this.resetForm();

            setTimeout(() => { this.dataInserted = false; }, 2000);
        },
        /**
         * Crée un nouvel avertissement modèle à partir du formulaire courant.
         * @returns {Promise<void>}
         */
        async createAvertissement() {
            try {
                const response = await avertissementOrganisateurService.createAvertissement(this.buildAvertissementFormData());
                
                if (response.status === 201 || response.status === 200) {
                    await this.handleSuccessSubmit();
                }
            } catch (error) {
                console.error("Erreur lors de l'envoi :", error.response?.data || error.message);
            }
        },
        /**
         * Modifie l'avertissement modèle actuellement sélectionné.
         * @returns {Promise<void>}
         */
        async modifyAvertissement() {
            if (!this.isEditing) return;

            try {
                const response = await avertissementOrganisateurService.modifyAvertissement(
                    this.avertissementData.id,
                    this.buildAvertissementFormData(),
                );

                if (response.status === 201 || response.status === 200) {
                    await this.handleSuccessSubmit();
                }
            } catch (error) {
                console.error("Erreur lors de la modification :", error.response?.data || error.message);
            }
        },
    },
    /**
     * Charge les modèles d'avertissement au montage du composant.
     * @returns {Promise<void>}
     */
    async mounted() {
        try {
            await this.chargerModeles();
        } catch (error) {
            console.error("Erreur au chargement :", error);
        }
    }
}
</script>