<template>
    <div class="bg-secondary p-6 rounded-base">
        <div>
            <p class="text-subtitle my-4">{{ isEditMode ? 'Modifier l\'évènement' : 'Créer un évènement' }}</p>
            <p v-if="formError" class="text-sm text-accent mb-4">{{ formError }}</p>
            <form>
                <div class="flex flex-row gap-4">
                    <div class="basis-3/4 flex flex-col gap-4">
                        <div>
                            <label for="name" class="block mb-2.5 text-sm font-medium text-heading">Nom de l'évènement</label>
                            <input type="text" id="name" v-model="eventData.name" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="" required />
                        </div>
                        <div>
                            <label for="url" class="block mb-2.5 text-sm font-medium text-heading">Lien du site web</label>
                            <input type="text" id="url" v-model="eventData.url" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" required />
                        </div>
                    </div>
                    <div class="basis-1/4">
                        <label for="logo" class="block mb-2.5 text-sm font-medium text-heading">Logo de l'évènement</label>
                        <div class="flex items-center justify-center w-full">
                            <label for="dropzone-file" class="flex flex-col items-center justify-center w-full bg-neutral-secondary-medium border border-dashed border-default-strong rounded-base cursor-pointer hover:bg-neutral-tertiary-medium">
                                <div v-if="!logoSrc" class="flex flex-col items-center justify-center text-body pt-2 pb-5">
                                    <svg class="w-8 h-8 mb-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h3a3 3 0 0 0 0-6h-.025a5.56 5.56 0 0 0 .025-.5A5.5 5.5 0 0 0 7.207 9.021C7.137 9.017 7.071 9 7 9a4 4 0 1 0 0 8h2.167M12 19v-9m0 0-2 2m2-2 2 2"/></svg>
                                    <p class="mb-2 text-sm"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                    <p class="text-xs">SVG, PNG, JPG or GIF (MAX. 800x400px)</p>
                                </div>
                                <div v-else>
                                    <img :src="logoSrc" class="max-h-16 max-w-36 h-64 object-contain" alt="Logo évènement" />
                                </div>
                                <input id="dropzone-file" type="file" class="hidden" @change="eventData.logo = $event.target.files[0]" />
                            </label>
                        </div> 
                    </div>
    
                </div>
                <hr class="border-t border-gray-200 mt-6 mb-4 mx-4" />
                <div>
                    <p>Couleurs</p>
                    <div class="flex flex-col m-4 gap-2">
                        <div class="flex flex-row justify-between items-center">
                            <label class="text-sm font-medium text-heading">Primaire</label>
                            <div class="items-center flex gap-4 bg-neutral-secondary-medium p-2 rounded-base border border-default-medium">
                                <Icon icon="mdi:pipette" />
                                <input
                                    v-model="colorLabels.primary"
                                    type="text"
                                    class="w-28 bg-white border border-default-medium text-heading text-sm rounded-base px-2 py-1"
                                    placeholder="#0E0F54"
                                    @input="onColorLabelInput('primary')"
                                    @blur="onColorLabelBlur('primary')"
                                >
                                <input
                                    type="color"
                                    id="EventColorPrimary"
                                    v-model="eventData.colors.primary"
                                    class="border rounded-base"
                                    @input="onColorPickerChange('primary')"
                                />
                            </div>
                        </div>
                        <div class="flex flex-row justify-between items-center ">
                            <label class="text-sm font-medium text-heading">Secondaire</label>
                            <div class="items-center flex gap-4 bg-neutral-secondary-medium p-2 rounded-base border border-default-medium">
                                <Icon icon="mdi:pipette" />
                                <input
                                    v-model="colorLabels.secondary"
                                    type="text"
                                    class="w-28 bg-white border border-default-medium text-heading text-sm rounded-base px-2 py-1"
                                    placeholder="#D9F20B"
                                    @input="onColorLabelInput('secondary')"
                                    @blur="onColorLabelBlur('secondary')"
                                >
                                <input
                                    type="color"
                                    id="EventColorSecondary"
                                    v-model="eventData.colors.secondary"
                                    class="border rounded-base"
                                    @input="onColorPickerChange('secondary')"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="border-t border-gray-200 mt-6 mb-4 mx-4" />
                <div>
                    <p>Paramètres</p>
                    <div class="flex flex-col m-4 gap-2">
                        <div class="flex flex-row justify-between items-center">
                            <label class="text-sm font-medium text-heading">Actif</label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="eventData.parameters.actif" value="" class="sr-only peer">
                                <div class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"></div>
                            </label>
                        </div>
                        <div class="flex flex-row justify-between items-center">
                            <label class="text-sm font-medium text-heading">Interne</label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="eventData.parameters.interne" value="" class="sr-only peer">
                                <div class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"></div>
                            </label>
                        </div>
                        <div class="flex flex-row justify-between items-center">
                            <label class="text-sm font-medium text-heading">Rabais</label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="eventData.parameters.rabais" value="" class="sr-only peer">
                                <div class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="flex flex-row mt-6 gap-4"> 
            <button class="btn-tertiary ml-auto disabled:opacity-50 disabled:cursor-not-allowed" :disabled="isEditMode && evenementIntrouvable" @click="confirmationPopup=true">
                {{ isEditMode ? 'Enregistrer les modifications' : 'Créer l\'évènement' }}
            </button>
        </div>
        <PopupConfirmation v-if="confirmationPopup" @cancel="confirmationPopup = false" @confirm="insertData()"/>
        <PopupConfirmation v-if="dataInserted" :message="isEditMode ? 'L\'évènement a été modifié avec succès !' : 'L\'évènement a été créé avec succès !'" :icon="'mdi:check'" :showButtons="false"/>
    </div>
</template>

<script>
/**
 * @fileoverview Composant FormulaireEvenement.
 * @description Formulaire organisateur pour créer ou modifier un événement.
 * Il couvre les informations générales, le logo, les couleurs, les paramètres
 * de publication et le flux de confirmation de sauvegarde.
 * @remarks Le composant adapte son comportement selon le mode création ou édition.
 */
import { Icon } from "@iconify/vue";
import OptionList from "./OptionList.vue";
import OptionTemplate from "./OptionTemplate.vue";
import PopupConfirmation from "./PopupConfirmation.vue";
import evenementOrganisateurService from "../services/evenementOrganisateurService";

const optionModal = {
    FERMEE: 1,
    SELECTION: 2,
    EXISTANT: 3,
};

export default {
    components: {
        Icon,
        OptionList,
        OptionTemplate,
        PopupConfirmation,
        evenementOrganisateurService,
    },
    data() {
        return {
            optionModal,
            modal: optionModal.FERMEE,
            confirmationPopup: false,
            dataInserted: false,
            formError: '',
            evenementIntrouvable: false,
            eventData: {
                name: '',
                url: '',
                logo: null,
                logoPreview: null,
                colors: {
                    primary: '#0e0f54',
                    secondary: '#d9f20b',
                },
                parameters: {
                    actif: false,
                    interne: false,
                    rabais: false,
                },
            },
            colorLabels: {
                primary: '#0E0F54',
                secondary: '#D9F20B',
            },
            optionElements: [
                "Existant", "Nouveau"
            ],
            formulaireEtapesLabels: [
                "Général", 
            ],
        };
    },
    computed: {
        /**
         * Indique si le composant est en mode édition à partir de l'URL.
         * @returns {boolean}
         */
        isEditMode() {
            return !!this.$route.query.id;
        },
        /**
         * Identifiant de l'évènement à charger ou modifier.
         * @returns {string|undefined}
         */
        eventId() {
            return this.$route.query.id;
        },
        /**
         * Source d'image du logo, en priorité à partir du fichier sélectionné.
         * @returns {string|null}
         */
        logoSrc() {
            if (this.eventData.logo) {
                return URL.createObjectURL(this.eventData.logo);
            }
            return this.eventData.logoPreview || null;
        },
    },
    watch: {
        eventId(newId) {
            if (newId) {
                // S'il y a un nouvel ID, on charge les données correspondantes
                this.chargerDonneesEvenement();
            } else {
                // Si l'ID a disparu (clic sur "Formulaires" dans la sidebar), on vide tout !
                this.resetFormulaire();
            }
        }
    },  
    methods: {
        /**
         * Réinitialise l'intégralité du formulaire à son état initial.
         * @returns {void}
         */
        resetFormulaire() {
            this.eventData = {
                name: '',
                url: '',
                logo: null,
                colors: { primary: '#0e0f54', secondary: '#d9f20b' },
                parameters: {
                    actif: false, interne:false, rabais: false,
                },
            };
            this.colorLabels = {
                primary: '#0E0F54',
                secondary: '#D9F20B',
            };
            this.etape = this.formulaireEtape.GENERAL; 
        },
        /**
         * Normalise une couleur hexadécimale vers le format #RRGGBB.
         * @param {string} value
         * @returns {string|null}
         */
        normalizeHexColor(value) {
            const cleaned = String(value ?? '').trim().replace(/^#/, '');
            if (!/^[0-9a-fA-F]{6}$/.test(cleaned)) {
                return null;
            }

            return `#${cleaned.toUpperCase()}`;
        },
        /**
         * Synchronise le champ texte d'une couleur avec la couleur retenue.
         * @param {string} key
         * @returns {void}
         */
        onColorLabelInput(key) {
            const normalized = this.normalizeHexColor(this.colorLabels[key]);
            if (normalized) {
                this.eventData.colors[key] = normalized.toLowerCase();
                this.colorLabels[key] = normalized;
            }
        },
        /**
         * Corrige la couleur saisie si nécessaire lors de la perte de focus.
         * @param {string} key
         * @returns {void}
         */
        onColorLabelBlur(key) {
            const normalized = this.normalizeHexColor(this.colorLabels[key]);
            const fallback = this.eventData.colors[key]?.toUpperCase() || '#000000';
            this.colorLabels[key] = normalized || fallback;
        },
        /**
         * Recalibre le champ texte à partir du sélecteur de couleur natif.
         * @param {string} key
         * @returns {void}
         */
        onColorPickerChange(key) {
            this.colorLabels[key] = (this.eventData.colors[key] || '').toUpperCase();
        },
        /**
         * Charge les données de l'évènement pour le mode édition.
         * @returns {Promise<void>}
         */
        async chargerDonneesEvenement() {
            try {
                this.formError = '';
                this.evenementIntrouvable = false;
                const response = await evenementOrganisateurService.getEvenement(this.eventId);
                const ev = response.data;

                /** Pré-remplit le formulaire avec les données récupérées. */
                this.eventData.name = ev.nom;
                this.eventData.url = ev.site || '';
                this.eventData.colors.primary = ev.couleur_primaire || '#0e0f54';
                this.eventData.colors.secondary = ev.couleur_secondaire || '#d9f20b';
                this.colorLabels.primary = this.eventData.colors.primary.toUpperCase();
                this.colorLabels.secondary = this.eventData.colors.secondary.toUpperCase();
                this.eventData.logoPreview = ev.logo_base64 
                    ? atob(ev.logo_base64.split(',')[1]) 
                    : null;

                this.eventData.parameters.actif = (ev.is_actif == 1 || ev.is_actif === true);
                this.eventData.parameters.interne = (ev.is_interne == 1 || ev.is_interne === true);
                this.eventData.parameters.rabais = (ev.is_rabais == 1 || ev.is_rabais === true);

                
            } catch(e) {
                if (e?.response?.status === 404) {
                    this.evenementIntrouvable = true;
                    this.formError = `L'évènement #${this.eventId} n'existe plus (ou a été supprimé).`;
                    return;
                }
                console.error("Erreur lors du chargement de l'événement: ", e);
            }
        },

        /**
         * Envoie le formulaire au service de création ou de mise à jour.
         * @returns {Promise<void>}
         */
        async insertData() {
            try {
                this.formError = '';
                if (this.isEditMode && this.evenementIntrouvable) {
                    this.formError = "Impossible d'enregistrer: cet évènement n'existe plus.";
                    return;
                }

                const formData = new FormData();
                formData.append('nom',              this.eventData.name);
                formData.append('site',              this.eventData.url);
                formData.append('couleur_primaire', this.eventData.colors.primary);
                formData.append('couleur_secondaire', this.eventData.colors.secondary);
                formData.append('is_rabais',           this.eventData.parameters.rabais ? 1 : 0);
                formData.append("is_actif",         this.eventData.parameters.actif ? 1 : 0);
                formData.append("is_interne",         this.eventData.parameters.interne ? 1 : 0);

                if (this.eventData.logo) {
                    formData.append('logo', this.eventData.logo);
                }

                let response;
                if (this.isEditMode) {
                    formData.append('_method', 'PUT'); 
                    response = await evenementOrganisateurService.modifyEvenement(this.eventId, formData);
                } else {
                    response = await evenementOrganisateurService.createEvenement(formData);
                }

                this.confirmPopup();
            } catch(e) {
                if (e?.response?.status === 404) {
                    this.evenementIntrouvable = true;
                    this.formError = `L'évènement #${this.eventId} est introuvable.`;
                    this.confirmationPopup = false;
                    return;
                }
                console.log("Erreur:", e.response?.data);
            }
        },
        /**
         * Affiche la popup de succès puis redirige en mode édition après délai.
         * @returns {void}
         */
        confirmPopup() {
            this.confirmationPopup = false;
            this.dataInserted = true; 
            setTimeout(() => {
                this.dataInserted = false; 
                this.$router.push('/organisateur/evenements');
            }, 2000); 
        }
    },
    async mounted() {
        if (this.isEditMode) {
            await this.chargerDonneesEvenement();
        }
    },
};
</script>