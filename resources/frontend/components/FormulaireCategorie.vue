<template>
    <div class="bg-secondary rounded-base p-6">
        <div class="flex flex-row gap-8 min-h-112.5">
            
            <div class="basis-1/2">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-subtitle my-4">Catégories</h1>
                    <button @click="openModal('categorie')" class="btn-tertiary py-1 px-4">
                        Créer
                    </button>
                </div>

                <div class="flex flex-col gap-3 max-h-125 overflow-y-auto pr-2 scrollbar-thin">
                    <template v-for="(cat, index) in categories" :key="cat?.id ?? `cat-${index}`">
                        <div v-if="cat" class="btn-model  flex justify-between">
                            <span class="text-heading font-medium">{{ cat.nom }}</span>
                            <div class="flex gap-4">
                                <button @click="modifyCategorie(index)" class="p-1 rounded-lg text-primary hover:bg-tertiary transition-colors">
                                    <Icon icon="mdi:pencil" width="20" height="20" />
                                </button>
                                <button @click="removeCategorie(index)" class="p-1 rounded-lg text-accent hover:bg-red-50 transition-colors">
                                    <Icon icon="lucide:trash-2" width="20" height="20" />
                                </button>
                            </div>
                        </div>
                    </template>
                    
                    <p v-if="categories.length === 0" class="text-xs text-body italic text-center mt-4">
                        Aucune catégorie.
                    </p>
                </div>
            </div>

            <div class="w-px bg-default-medium self-stretch"></div>

            <div class="basis-1/2">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-subtitle my-4">Sous-catégories</h1>
                    <button @click="openModal('sous-categorie')" class="btn-tertiary py-1 px-4">
                        Créer
                    </button>
                </div>

                <div class="flex flex-col gap-3 max-h-125 overflow-y-auto pr-2 scrollbar-thin">
                    <template v-for="(sub, index) in sousCategories" :key="sub?.id ?? `sub-${index}`">
                        <div v-if="sub" class="btn-model  flex justify-between">
                            <span class="text-heading font-medium">{{ sub.nom }}</span>
                            <div class="flex gap-4">
                                <button @click="modifySousCategorie(index)" class="p-1 rounded-lg text-primary hover:bg-tertiary transition-colors">
                                    <Icon icon="mdi:pencil" width="20" height="20" />
                                </button>
                                <button @click="removeSousCategorie(index)" class="p-1 rounded-lg text-accent hover:bg-red-50 transition-colors">
                                    <Icon icon="lucide:trash-2" width="20" height="20" />
                                </button>
                            </div>
                        </div>
                    </template>

                    <p v-if="sousCategories.length === 0" class="text-xs text-body italic text-center mt-4">
                        Aucune sous-catégorie.
                    </p>
                </div>
            </div>
        </div>

        <!-- Modale -->
        <Transition name="fade">
            <div v-if="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="closeModal">
                <div class="bg-secondary rounded-base p-6 w-full max-w-md shadow-xl">
                    <h2 class="text-subtitle mb-4">
                        {{ modalAction === 'create' ? 'Créer' : 'Modifier' }} {{ modalType === 'categorie' ? 'une catégorie' : 'une sous-catégorie' }}
                    </h2>

                    <input
                        ref="modalInput"
                        v-model="newNom"
                        type="text"
                        :placeholder="modalType === 'categorie' ? 'Nom de la catégorie...' : 'Nom de la sous-catégorie...'"
                        class="w-full bg-neutral-secondary-medium border border-default-medium rounded-2xl text-heading outline-none focus:border-primary-900 transition-colors"
                        @keyup.enter="confirmModal"
                        @keyup.escape="closeModal"
                    />

                    <div class="flex justify-end gap-3 mt-5">
                        <button @click="closeModal" class="btn-accent-300 py-2 px-5 text-sm">
                            Annuler
                        </button>
                        <button @click="confirmModal" :disabled="isSubmitting || !newNom.trim()" class="btn-tertiary py-2 px-5 text-sm disabled:opacity-50">
                            {{ isSubmitting ? (modalAction === 'create' ? 'Création...' : 'Enregistrement...') : (modalAction === 'create' ? 'Créer' : 'Enregistrer') }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <PopupConfirmation
            v-if="actionModalOpen"
            icon="mdi:alert-circle-outline"
            :message="actionDescription"
            @confirm="confirmAction"
            @cancel="closeActionModal"
        />
    </div>
</template>

<script>
/**
 * @fileoverview Composant FormulaireCategorie.
 * @description Gestion des catégories et sous-catégories de course avec création, édition et suppression.
 * @remarks Ce composant fournit un écran d'administration complet pour maintenir
 * les taxonomies utilisées dans les formulaires de création de courses.
 */
import { Icon } from '@iconify/vue';
import PopupConfirmation from './PopupConfirmation.vue';
import sousCategorieOrganisateurService from '../services/sousCategorieOrganisateurService';
import categorieOrganisateurService from '../services/categorieOrganisateurService';

export default {
    name: 'FormulaireCategorie',
    components: { Icon, PopupConfirmation },
    /**
     * Initialise les listes et états de modales pour les actions sur catégories.
     * @returns {Object} État local de la vue de gestion.
     */
    data() {
        return {
            categories: [],
            sousCategories: [],
            modalOpen: false,
            modalType: null,
            modalAction: 'create',
            modalIndex: null,
            newNom: '',
            isSubmitting: false,
            actionModalOpen: false,
            actionType: null,
            actionIndex: null,
            actionNom: '',
            isActionSubmitting: false,
        };
    },
    computed: {
        /**
         * Indique si l'action en cours correspond à une édition.
         * @returns {boolean}
         */
        isEditAction() {
            return this.actionType === 'edit-categorie' || this.actionType === 'edit-sous-categorie';
        },
        /**
         * Indique si l'action en cours correspond à une suppression.
         * @returns {boolean}
         */
        isDeleteAction() {
            return this.actionType === 'delete-categorie' || this.actionType === 'delete-sous-categorie';
        },
        /**
         * Génère le texte de confirmation pour les suppressions.
         * @returns {string}
         */
        actionDescription() {
            if (this.actionType === 'delete-categorie') return `La catégorie "${this.categories[this.actionIndex]?.nom ?? ''}" va être supprimée.`;
            if (this.actionType === 'delete-sous-categorie') return `La sous-catégorie "${this.sousCategories[this.actionIndex]?.nom ?? ''}" va être supprimée.`;
            return '';
        },
    },
    methods: {
        /**
         * Nettoie une liste brute en conservant uniquement les entrées valides.
         * @param {Array} payload Données reçues de l'API.
         * @returns {Array<Object>}
         */
        normalizeList(payload) {
            if (!Array.isArray(payload)) return [];
            return payload.filter(item => item && typeof item === 'object' && item.nom);
        },
        /**
         * Charge les catégories et sous-catégories depuis l'API.
         * @returns {Promise<void>}
         */
        async fetchDatas() {
            try {
                const [resCat, resSub] = await Promise.all([
                    categorieOrganisateurService.getAllCategorie(),
                    sousCategorieOrganisateurService.getAllSousCategorie()
                ]);
                this.categories = this.normalizeList(resCat.data);
                this.sousCategories = this.normalizeList(resSub.data);
            } catch (error) {
                console.error("Erreur chargement catégories :", error);
            }
        },

        /**
         * Ouvre la modale de création/édition avec préremplissage éventuel.
         * @param {'categorie'|'sous-categorie'} type Type d'entité concernée.
         * @param {'create'|'edit'} action Action visée.
         * @param {?number} index Index de l'élément ciblé.
         * @param {string} nom Valeur initiale.
         * @returns {void}
         */
        openModal(type, action = 'create', index = null, nom = '') {
            this.modalType = type;
            this.modalAction = action;
            this.modalIndex = index;
            this.newNom = nom;
            this.modalOpen = true;
            this.$nextTick(() => this.$refs.modalInput?.focus());
        },

        /**
         * Ferme et réinitialise la modale de création/édition.
         * @returns {void}
         */
        closeModal() {
            this.modalOpen = false;
            this.modalType = null;
            this.modalAction = 'create';
            this.modalIndex = null;
            this.newNom = '';
        },

        /**
         * Ouvre la modale de confirmation d'action destructive.
         * @param {string} type Type d'action à confirmer.
         * @param {number} index Index de l'élément ciblé.
         * @returns {void}
         */
        openActionModal(type, index) {
            this.actionType = type;
            this.actionIndex = index;
            this.actionModalOpen = true;
            this.actionNom = '';
        },

        /**
         * Ferme la modale de confirmation et réinitialise son état.
         * @returns {void}
         */
        closeActionModal() {
            this.actionModalOpen = false;
            this.actionType = null;
            this.actionIndex = null;
            this.actionNom = '';
            this.isActionSubmitting = false;
        },

        /**
         * Exécute la suppression confirmée d'une catégorie ou sous-catégorie.
         * @returns {Promise<void>}
         */
        async confirmAction() {
            if (this.isActionSubmitting) return;

            const index = this.actionIndex;
            if (index === null || index === undefined) return;

            if (this.actionType === 'edit-categorie' || this.actionType === 'edit-sous-categorie') return;

            this.isActionSubmitting = true;
            try {
                if (this.actionType === 'delete-categorie') {
                    const categorie = this.categories[index];
                    await categorieOrganisateurService.deleteCategorie(categorie.id);
                    this.categories.splice(index, 1);
                }

                if (this.actionType === 'delete-sous-categorie') {
                    const sousCategorie = this.sousCategories[index];
                    await sousCategorieOrganisateurService.deleteSousCategorie(sousCategorie.id);
                    this.sousCategories.splice(index, 1);
                }

                this.closeActionModal();
            } catch (error) {
                console.error('Erreur action catégorie/sous-catégorie :', error);
                this.isActionSubmitting = false;
            }
        },

        /**
         * Valide la création ou l'édition depuis la modale principale.
         * @returns {Promise<void>}
         */
        async confirmModal() {
            const nom = this.newNom.trim();
            if (!nom || this.isSubmitting) return;

            this.isSubmitting = true;
            try {
                if (this.modalAction === 'create') {
                    if (this.modalType === 'categorie') {
                        const res = await categorieOrganisateurService.createCategorie({ nom, modele: true });
                        const createdCategorie = res.data?.categorie ?? res.data?.data ?? res.data;
                        if (createdCategorie?.nom) {
                            this.categories.push(createdCategorie);
                        } else {
                            await this.fetchDatas();
                        }
                    } else {
                        const res = await sousCategorieOrganisateurService.createSousCategorie({ nom, modele: true });
                        const createdSousCategorie = res.data?.sousCategorie ?? res.data?.categorie ?? res.data?.data ?? res.data;
                        if (createdSousCategorie?.nom) {
                            this.sousCategories.push(createdSousCategorie);
                        } else {
                            await this.fetchDatas();
                        }
                    }
                } else {
                    if (this.modalType === 'categorie') {
                        const categorie = this.categories[this.modalIndex];
                        if (categorie && nom !== categorie.nom) {
                            await categorieOrganisateurService.modifyCategorie(categorie.id, { nom });
                            this.categories[this.modalIndex].nom = nom;
                        }
                    } else {
                        const sousCategorie = this.sousCategories[this.modalIndex];
                        if (sousCategorie && nom !== sousCategorie.nom) {
                            await sousCategorieOrganisateurService.modifySousCategorie(sousCategorie.id, { nom });
                            this.sousCategories[this.modalIndex].nom = nom;
                        }
                    }
                }
                this.closeModal();
            } catch (error) {
                console.error("Erreur création :", error);
            } finally {
                this.isSubmitting = false;
            }
        },
        /**
         * Ouvre la modale d'édition d'une catégorie.
         * @param {number} index Index de la catégorie ciblée.
         * @returns {Promise<void>}
         */
        async modifyCategorie(index) {
            const categorie = this.categories[index];
            if (!categorie) return;

            this.openModal('categorie', 'edit', index, categorie.nom);
        },
        /**
         * Ouvre la confirmation de suppression d'une catégorie.
         * @param {number} index Index de la catégorie ciblée.
         * @returns {Promise<void>}
         */
        async removeCategorie(index) {
            this.openActionModal('delete-categorie', index);
        },
        /**
         * Ouvre la modale d'édition d'une sous-catégorie.
         * @param {number} index Index de la sous-catégorie ciblée.
         * @returns {Promise<void>}
         */
        async modifySousCategorie(index) {
            const sousCategorie = this.sousCategories[index];
            if (!sousCategorie) return;

            this.openModal('sous-categorie', 'edit', index, sousCategorie.nom);
        },
        /**
         * Ouvre la confirmation de suppression d'une sous-catégorie.
         * @param {number} index Index de la sous-catégorie ciblée.
         * @returns {Promise<void>}
         */
        async removeSousCategorie(index) {
            this.openActionModal('delete-sous-categorie', index);
        },
    },
    /**
     * Charge les données initiales au montage.
     * @returns {Promise<void>}
     */
    async mounted() {
        await this.fetchDatas();
    }
}
</script>
