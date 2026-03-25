<template>
    <div class="bg-secondary rounded-base p-6">
        <div class="flex flex-row gap-8 min-h-[450px]">
            
            <div class="basis-1/2">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-subtitle my-4">Catégories</h1>
                    <button @click="openModal('categorie')" class="btn-tertiary py-1 px-4">
                        Créer
                    </button>
                </div>

                <div class="flex flex-col gap-3 max-h-[500px] overflow-y-auto pr-2 scrollbar-thin">
                    <div v-for="(cat, index) in categories" :key="index" 
                        class="btn-model  flex justify-between">
                        <span class="text-heading font-medium">{{ cat.nom }}</span>
                        <button @click="removeCategorie(index)" class="text-primary-900 hover:text-accent transition-colors">
                            <Icon icon="mdi:delete" width="20" height="20" />
                        </button>
                    </div>
                    
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

                <div class="flex flex-col gap-3 max-h-[500px] overflow-y-auto pr-2 scrollbar-thin">
                    <div v-for="(sub, index) in sousCategories" :key="index" 
                        class="btn-model  flex justify-between">
                        <span class="text-heading font-medium">{{ sub.nom }}</span>
                        <button @click="removeSousCategorie(index)" class="text-primary-900 hover:text-accent transition-colors">
                            <Icon icon="mdi:delete" width="20" height="20" />
                        </button>
                    </div>

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
                        Créer {{ modalType === 'categorie' ? 'une catégorie' : 'une sous-catégorie' }}
                    </h2>

                    <input
                        ref="modalInput"
                        v-model="newNom"
                        type="text"
                        :placeholder="modalType === 'categorie' ? 'Nom de la catégorie...' : 'Nom de la sous-catégorie...'"
                        class="w-full bg-neutral-secondary-medium border border-default-medium rounded-2xl text-heading outline-none focus:border-primary-900 transition-colors"
                        @keyup.enter="confirmCreate"
                        @keyup.escape="closeModal"
                    />

                    <div class="flex justify-end gap-3 mt-5">
                        <button @click="closeModal" class="btn-accent-300 py-2 px-5 text-sm">
                            Annuler
                        </button>
                        <button @click="confirmCreate" :disabled="isSubmitting || !newNom.trim()" class="btn-tertiary py-2 px-5 text-sm disabled:opacity-50">
                            {{ isSubmitting ? 'Création...' : 'Créer' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script>
import { Icon } from '@iconify/vue';
import sousCategorieOrganisateurService from '../services/sousCategorieOrganisateurService';
import categorieOrganisateurService from '../services/categorieOrganisateurService';

export default {
    components: { Icon },
    data() {
        return {
            categories: [],
            sousCategories: [],
            modalOpen: false,
            modalType: null,   // 'categorie' | 'sous-categorie'
            newNom: '',
            isSubmitting: false,
        };
    },
    methods: {
        async fetchDatas() {
            try {
                const [resCat, resSub] = await Promise.all([
                    categorieOrganisateurService.getAllCategorie(),
                    sousCategorieOrganisateurService.getAllSousCategorie()
                ]);
                this.categories = resCat.data;
                this.sousCategories = resSub.data;
            } catch (error) {
                console.error("Erreur chargement catégories :", error);
            }
        },

        openModal(type) {
            this.modalType = type;
            this.newNom = '';
            this.modalOpen = true;
            this.$nextTick(() => this.$refs.modalInput?.focus());
        },

        closeModal() {
            this.modalOpen = false;
            this.modalType = null;
            this.newNom = '';
        },

        async confirmCreate() {
            const nom = this.newNom.trim();
            if (!nom || this.isSubmitting) return;

            this.isSubmitting = true;
            try {
                if (this.modalType === 'categorie') {
                    const res = await categorieOrganisateurService.createCategorie({ nom, modele: true });
                    this.categories.push(res.data.categorie);
                } else {
                    const res = await sousCategorieOrganisateurService.createSousCategorie({ nom, modele: true });
                    this.sousCategories.push(res.data.categorie);
                }
                this.closeModal();
            } catch (error) {
                console.error("Erreur création :", error);
            } finally {
                this.isSubmitting = false;
            }
        },

        async removeCategorie(index) {
            const categorie = this.categories[index];
            if (confirm(`Supprimer la catégorie ${categorie.nom} ?`)) {
                try {
                    await categorieOrganisateurService.deleteCategorie(item.id);
                    this.categories.splice(index, 1);
                } catch (error) {
                    console.error("Erreur suppression :", error);
                }
            }
        },

        async removeSousCategorie(index) {
            const sousCategorie = this.sousCategories[index];
            if (confirm(`Supprimer la sous-catégorie ${sousCategorie.nom} ?`)) {
                try {
                    await sousCategorieOrganisateurService.deleteSousCategorie(item.id);
                    this.sousCategories.splice(index, 1);
                } catch (error) {
                    console.error("Erreur suppression :", error);
                }
            }
        },
    },
    async mounted() {
        await this.fetchDatas();
    }
}
</script>
