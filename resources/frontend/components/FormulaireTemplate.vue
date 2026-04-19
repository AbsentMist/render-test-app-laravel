<template>
    <div class="bg-secondary rounded-b-base rounded-tl-base p-6">
        <div>
            <h1 class="text-subtitle my-4">Template texte</h1>

        </div>
        <p class="mb-4 text-body text-sm">
            Les templates texte permettent de sauvegarder des modèles de texte à recopier pour des annonces via les plateformes de communication.
        </p>

        <div class="flex flex-row gap-4">
            <div class="basis-2/3 flex flex-col justify-between h-[450px]">
                <div class="space-y-4 flex-grow">
                    <div>
                        <label for="nom" class="block mb-2 text-sm font-medium text-heading">Nom du modèle</label>
                        <input type="text" id="nom" v-model="templateData.nom" 
                            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" 
                            placeholder="Ex: Annonce changement de course" required />
                    </div>     
                    <div class="flex flex-col">
                        <label for="template" class="block mb-2 text-sm font-medium text-heading">Contenu du template</label>
                        <textarea id="template" v-model="templateData.contenu" 
                            class="resize-none h-[280px] bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" 
                            placeholder="Saisissez le contenu du template..." required />
                    </div>
                </div>

                <div class="flex flex-row justify-end mt-4">
                    <div class="flex justify-between gap-4">
                        <div class="relative">
                            <span
                                v-if="copieConfirmee"
                                class="absolute -top-8 left-1/2 -translate-x-1/2 whitespace-nowrap text-xs text-primary bg-tertiary p-2 rounded-xl"
                            >
                                Copié
                            </span>
                            <button type="button" @click="copyTemplateContent" class="btn-accent-300">
                                <Icon icon="lucide:copy" width="20" height="20" class="text-body hover:text-heading" />
                            </button>
                        </div>
                        <button
                            v-if="canShowCancelButton"
                            type="button"
                            @click="resetForm"
                            class="btn-accent-300"
                        >
                            Annuler
                        </button>
                        <button type="button" @click="handleSubmit" class="btn-tertiary">
                            {{ submitButtonLabel }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="basis-1/3 min-w-0 border-l border-default-medium pl-4">
                <h2 class="text-sm font-medium text-heading mb-2.5">Mes modèles</h2>
                <div class="flex flex-col gap-2 h-[415px] overflow-y-auto overflow-x-hidden pr-2 scrollbar-thin">
                    <button v-for="(template, index) in templateModels" 
                        :key="index" 
                        type="button" 
                        @click="copyDatas(template)" 
                        class="btn-model flex flex-row items-center justify-between w-full min-w-0 text-left min-h-[42px]">
                        <span class="block flex-1 min-w-0 truncate pr-2">{{ template.nom }}</span>
                        <Icon icon="lucide:trash-2" width="20" height="20" 
                            class="text-accent hover:bg-accent-300 rounded-lg flex-shrink-0" 
                            @click.stop="removeTemplate(index)"/>
                    </button>

                    <p v-if="templateModels.length === 0" class="text-xs text-body italic">
                        Aucun modèle enregistré.
                    </p>
                </div>
            </div>
        </div>

        <PopupConfirmation
            v-if="templateASupprimer"
            :message="`Supprimer le template ${templateASupprimer.nom} ?`"
            icon="mdi:alert-circle-outline"
            @confirm="confirmerSuppressionTemplate"
            @cancel="templateASupprimer = null"
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
import templateOrganisateurService from '../services/templateOrganisateurService';

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
            templateData: {
                id: null,
                nom: "",
                contenu: "",
            },
            originalTemplateData: {
                nom: "",
                contenu: "",
            },
            templateModels: [],
            dataInserted: false,
            templateASupprimer: null,
            copieConfirmee: false,
            copieConfirmationTimeout: null,
        }
    },
    computed: {
        /**
         * Indique si le formulaire est en mode édition.
         * @returns {boolean}
         */
        isEditing() {
            return this.templateData.id !== null;
        },
        /**
         * Libellé dynamique du bouton principal selon le mode du formulaire.
         * @returns {string}
         */
        submitButtonLabel() {
            return this.isEditing ? 'Modifier le template' : 'Ajouter le template';
        },
        /**
         * Contrôle l'affichage du bouton Annuler uniquement si des changements existent.
         * @returns {boolean}
         */
        canShowCancelButton() {
            if (!this.isEditing) {
                return false;
            }

            return (
                this.templateData.nom !== this.originalTemplateData.nom
                || this.templateData.contenu !== this.originalTemplateData.contenu
            );
        },
    },
    methods: {
        /**
         * Copie le contenu actuel du template dans le presse-papiers.
         * @returns {Promise<void>}
         */
        async copyTemplateContent() {
            const contenu = this.templateData.contenu || "";
            if (!contenu) return;

            try {
                let copied = false;

                if (navigator?.clipboard?.writeText) {
                    await navigator.clipboard.writeText(contenu);
                    copied = true;
                } else {
                    // Fallback pour les contextes où l'API Clipboard n'est pas disponible.
                    const tempTextarea = document.createElement('textarea');
                    tempTextarea.value = contenu;
                    tempTextarea.setAttribute('readonly', '');
                    tempTextarea.style.position = 'absolute';
                    tempTextarea.style.left = '-9999px';

                    document.body.appendChild(tempTextarea);
                    tempTextarea.select();
                    copied = document.execCommand('copy');
                    document.body.removeChild(tempTextarea);
                }

                if (copied) {
                    this.showCopyConfirmation();
                }
            } catch (error) {
                console.error("Erreur lors de la copie :", error);
            }
        },
        /**
         * Affiche temporairement la confirmation visuelle de copie.
         * @returns {void}
         */
        showCopyConfirmation() {
            this.copieConfirmee = true;

            if (this.copieConfirmationTimeout) {
                clearTimeout(this.copieConfirmationTimeout);
            }

            this.copieConfirmationTimeout = setTimeout(() => {
                this.copieConfirmee = false;
                this.copieConfirmationTimeout = null;
            }, 1500);
        },
        /**
         * Copie un modèle existant dans le formulaire courant.
         * @param {Object} template Modèle source sélectionné.
         * @returns {void}
         */
        copyDatas(template) {
            this.templateData.id = template.id;
            this.templateData.nom = template.nom;
            this.templateData.contenu = template.contenu;
            this.originalTemplateData.nom = template.nom || "";
            this.originalTemplateData.contenu = template.contenu || "";
        },
        /**
         * Réinitialise le formulaire et quitte le mode édition.
         * @returns {void}
         */
        resetForm() {
            this.templateData.id = null;
            this.templateData.nom = "";
            this.templateData.contenu = "";
            this.originalTemplateData.nom = "";
            this.originalTemplateData.contenu = "";
        },
        /**
         * Prépare la suppression d'un modèle de template.
         * @param {number} index Position du modèle dans la liste.
         * @returns {void}
         */
        async removeTemplate(index) {
            const template = this.templateModels[index];
            if (!template) return;
            this.templateASupprimer = { ...template, index };
        },
        /**
         * Supprime le modèle confirmé puis met à jour la liste locale.
         * @returns {Promise<void>}
         */
        async confirmerSuppressionTemplate() {
            if (!this.templateASupprimer) return;
            try {
                await templateOrganisateurService.deleteTemplate(this.templateASupprimer.id);
                this.templateModels.splice(this.templateASupprimer.index, 1);
                if (this.templateData.id === this.templateASupprimer.id) {
                    this.resetForm();
                }
                this.templateASupprimer = null;
            } catch (error) {
                 console.error("Erreur lors de la suppression :", error.response?.data || error);
            }
        },
        /**
         * Enregistre un nouvel avertissement modèle puis recharge la liste disponible.
         * @returns {Promise<void>}
         */
        async handleSubmit() {
            try {
                const formData = new FormData();
                formData.append('nom', this.templateData.nom);
                formData.append('contenu', this.templateData.contenu);

                const response = this.isEditing
                    ? await templateOrganisateurService.modifyTemplate(this.templateData.id, formData)
                    : await templateOrganisateurService.createTemplate(formData);
                
                if (response.status === 201 || response.status === 200) {
                    this.dataInserted = true;
                    const updatedList = await templateOrganisateurService.getAllTemplates();
                    this.templateModels = updatedList.data;

                    this.resetForm();

                    setTimeout(() => { this.dataInserted = false; }, 2000);
                }
            } catch (error) {
                console.error("Erreur lors de l'envoi :", error.response?.data || error.message);
            }
        },
    },
    /**
     * Charge les modèles d'avertissement au montage du composant.
     * @returns {Promise<void>}
     */
    async mounted() {
        try {
            const response = await templateOrganisateurService.getAllTemplates();
            this.templateModels = response.data;
        } catch (error) {
            console.error("Erreur au chargement :", error);
        }
    },
    beforeUnmount() {
        if (this.copieConfirmationTimeout) {
            clearTimeout(this.copieConfirmationTimeout);
            this.copieConfirmationTimeout = null;
        }
    }
}
</script>