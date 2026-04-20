<template>
    <div class="bg-secondary rounded-b-base rounded-tl-base p-6">
        <div>
            <h1 class="text-subtitle my-4">Template texte</h1>

        </div>
        <p class="mb-4 text-body text-sm">
            Les templates texte permettent de sauvegarder des modèles de texte à recopier pour des annonces via les plateformes de communication.
        </p>

        <div class="flex flex-row gap-4">
            <div class="basis-2/3 flex flex-col justify-between h-112.5">
                <div class="space-y-4 grow">
                    <div>
                        <label for="nom" class="block mb-2 text-sm font-medium text-heading">Nom du modèle</label>
                        <input type="text" id="nom" v-model="templateData.nom" 
                            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" 
                            placeholder="Ex: Annonce changement de course" required />
                    </div>     
                    <div class="flex flex-col">
                        <label for="template" class="block mb-2 text-sm font-medium text-heading">Contenu du template</label>
                        <textarea id="template" v-model="templateData.contenu" 
                            class="resize-none h-70 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" 
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
                            v-if="isEditing"
                            type="button"
                            @click="resetForm"
                            class="btn-accent-300"
                        >
                            Annuler
                        </button>
                        <button v-if="isEditing" type="button" @click="modifyTemplate" class="btn-tertiary-300 text-secondary">
                            Modifier le template
                        </button>
                        <button type="button" @click="createTemplate" class="btn-tertiary">
                            Ajouter le template
                        </button>
                    </div>
                </div>
            </div>

            <div class="basis-1/3 min-w-0 border-l border-default-medium pl-4">
                <h2 class="text-sm font-medium text-heading mb-2.5">Mes modèles</h2>
                <div class="flex flex-col gap-2 h-103.75 overflow-y-auto overflow-x-hidden pr-2 scrollbar-thin">
                    <button v-for="(template, index) in templateModels" 
                        :key="template.id ?? index" 
                        type="button" 
                        @click="copyDatas(template)" 
                        :class="[
                            'btn-model flex flex-row items-center justify-between w-full min-w-0 text-left min-h-10.5',
                            isModelHighlighted(template) ? 'bg-accent-300 text-heading ring-1 ring-accent' : ''
                        ]">
                        <span class="block flex-1 min-w-0 truncate pr-2">{{ template.nom }}</span>
                        <Icon icon="lucide:trash-2" width="20" height="20" 
                            class="text-accent hover:bg-accent-300 rounded-lg shrink-0" 
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
            templateModels: [],
            dataInserted: false,
            templateASupprimer: null,
            selectedTemplateModelId: null,
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
    },
    methods: {
        /**
         * Normalise un template API vers le format local du formulaire.
         * @param {Object} template Donnée template brute.
         * @returns {Object} Template normalisé.
         */
        normalizeTemplate(template) {
            return {
                id: template?.id ?? null,
                nom: template?.nom ?? '',
                contenu: template?.contenu ?? '',
            };
        },
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
            this.templateData = this.normalizeTemplate(template);
            this.selectedTemplateModelId = template?.id ?? null;
        },
        /**
         * Réinitialise le formulaire et quitte le mode édition.
         * @returns {void}
         */
        resetForm() {
            this.templateData = this.normalizeTemplate(null);
            this.selectedTemplateModelId = null;
        },
        /**
         * Prépare un template pour comparaison de contenu.
         * @param {Object} template Donnée template à comparer.
         * @returns {Object} Version simplifiée du template.
         */
        buildComparableTemplate(template) {
            const normalizedTemplate = this.normalizeTemplate(template);
            return {
                nom: (normalizedTemplate.nom || '').trim(),
                contenu: (normalizedTemplate.contenu || '').trim(),
            };
        },
        /**
         * Indique si un modèle doit être affiché en surbrillance.
         * @param {Object} template Modèle de la liste.
         * @returns {boolean}
         */
        isModelHighlighted(template) {
            if (!template?.id || template.id !== this.selectedTemplateModelId) {
                return false;
            }

            const modelTemplate = this.buildComparableTemplate(template);
            const formTemplate = this.buildComparableTemplate(this.templateData);

            return (
                modelTemplate.nom === formTemplate.nom
                && modelTemplate.contenu === formTemplate.contenu
            );
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
                if (this.selectedTemplateModelId === this.templateASupprimer.id) {
                    this.selectedTemplateModelId = null;
                }
                this.templateASupprimer = null;
            } catch (error) {
                 console.error("Erreur lors de la suppression :", error.response?.data || error);
            }
        },
        /**
         * Recharge la liste des templates depuis l'API.
         * @returns {Promise<void>}
         */
        async chargerModeles() {
            const response = await templateOrganisateurService.getAllTemplates();
            this.templateModels = response.data;
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
         * Crée un nouveau template modèle à partir du formulaire courant.
         * @returns {Promise<void>}
         */
        async createTemplate() {
            try {
                const formData = new FormData();
                formData.append('nom', this.templateData.nom);
                formData.append('contenu', this.templateData.contenu);

                const response = await templateOrganisateurService.createTemplate(formData);

                if (response.status === 201 || response.status === 200) {
                    await this.handleSuccessSubmit();
                }
            } catch (error) {
                console.error("Erreur lors de l'envoi :", error.response?.data || error.message);
            }
        },
        /**
         * Modifie le template modèle actuellement sélectionné.
         * @returns {Promise<void>}
         */
        async modifyTemplate() {
            if (!this.isEditing) return;

            try {
                const formData = new FormData();
                formData.append('nom', this.templateData.nom);
                formData.append('contenu', this.templateData.contenu);

                const response = await templateOrganisateurService.modifyTemplate(this.templateData.id, formData);
                
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
    },
    beforeUnmount() {
        if (this.copieConfirmationTimeout) {
            clearTimeout(this.copieConfirmationTimeout);
            this.copieConfirmationTimeout = null;
        }
    }
}
</script>