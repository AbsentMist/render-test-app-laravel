<template>
    <div class="flex flex-col gap-4 mt-4">
        <h2 class="text-base font-semibold text-heading">Sélectionnez une personne à inscrire</h2>

        <div v-if="chargement" class="text-sm text-gray-400 text-center py-2">Chargement...</div>

        <!-- Grille participants + bouton nouvelle personne -->
        <div class="grid grid-cols-2 gap-3">
            <button
                v-for="participant in participants"
                :key="participant.id"
                type="button"
                @click="selectionner(participant)"
                @mouseenter="hoveredId = participant.id"
                @mouseleave="hoveredId = null"
                :class="[
                    'flex items-center gap-3 px-4 py-3 rounded-xl border-2 text-sm font-medium transition-all text-left bg-white',
                    estSelectionne(participant.id)
                        ? 'border-tertiary text-tertiary-900'
                        : 'border-gray-200 text-primary hover:border-tertiary hover:text-tertiary-900'
                ]"
            >
                <Icon
                    icon="mdi:account-outline"
                    class="w-5 h-5 shrink-0 transition-colors"
                    :class="estSelectionne(participant.id) || hoveredId === participant.id ? 'text-tertiary-900' : 'text-gray-400'"
                />
                <span>{{ participant.prenom }} {{ participant.nom }}</span>
            </button>

            <!-- Bouton nouvelle personne -->
            <button
                type="button"
                @click="ouvrirFormulaire"
                @mouseenter="hoveredNouveau = true"
                @mouseleave="hoveredNouveau = false"
                class="flex items-center gap-3 px-4 py-3 rounded-xl border-2 border-dashed border-gray-300 text-sm font-medium text-gray-500 hover:border-tertiary hover:text-tertiary-900 transition-all text-left bg-white"
            >
                <Icon
                    icon="mdi:account-plus-outline"
                    class="w-5 h-5 shrink-0 transition-colors"
                    :class="hoveredNouveau ? 'text-tertiary-900' : 'text-gray-400'"
                />
                <span>Nouvelle personne</span>
            </button>
        </div>

        <p class="text-sm text-gray-400 text-center">La personne que vous recherchez n'existe pas ?</p>

        <!-- Sous-popup création participant -->
        <Teleport to="body">
            <div
                v-if="formulaireOuvert"
                class="fixed inset-0 z-[60] flex items-center justify-center bg-black/30"
                @click.self="fermerFormulaire"
            >
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 flex flex-col overflow-hidden max-h-[90vh]">

                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                        <h3 class="text-base font-semibold text-heading">Rechercher une personne</h3>
                        <button type="button" @click="fermerFormulaire" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <Icon icon="mdi:close" class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Contenu scrollable -->
                    <div class="overflow-y-auto px-6 py-5 flex flex-col gap-4">

                        <!-- Recherche par email -->
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-gray-700">Adresse email</label>
                            <input
                                v-model="emailRecherche"
                                type="email"
                                placeholder="email@exemple.com"
                                @input="rechercherParEmail"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40"
                            />
                            <p v-if="emailRecherche && !emailTrouve" class="flex items-center gap-2 text-xs text-orange-600">
                                <Icon icon="mdi:information-outline" class="w-4 h-4 shrink-0" />
                                Aucune personne avec cette adresse existe. Veuillez réessayer ou en créer une nouvelle.
                            </p>
                        </div>

                        <!-- Formulaire nouvelle personne -->
                        <div class="flex flex-col gap-3">
                            <h4 class="text-sm font-semibold text-heading border-t border-gray-100 pt-3">Nouvelle personne</h4>

                            <!-- Nom / Prénom -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-medium text-gray-600">Nom</label>
                                    <input v-model="form.nom" type="text" placeholder="Nom"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-gray-50" />
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-medium text-gray-600">Prénom</label>
                                    <input v-model="form.prenom" type="text" placeholder="Prénom"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-gray-50" />
                                </div>
                            </div>

                            <!-- Date de naissance -->
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-medium text-gray-600">Date de naissance (JJ/MM/AAAA)</label>
                                <input v-model="form.date_naissance" type="text" placeholder="JJ/MM/AAAA"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-gray-50" />
                            </div>

                            <!-- Adresse -->
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-medium text-gray-600">Adresse</label>
                                <input v-model="form.adresse" type="text" placeholder="Rue et numéro"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-gray-50" />
                            </div>

                            <!-- Code postal / Ville / Pays -->
                            <div class="grid grid-cols-3 gap-3">
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-medium text-gray-600">Code postal</label>
                                    <input v-model="form.code_postal" type="text" placeholder="1000"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-gray-50" />
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-medium text-gray-600">Ville</label>
                                    <input v-model="form.ville" type="text" placeholder="Lausanne"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-gray-50" />
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-medium text-gray-600">Pays</label>
                                    <select v-model="form.pays"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-gray-50">
                                        <option value="Suisse">Suisse</option>
                                        <option value="France">France</option>
                                        <option value="Belgique">Belgique</option>
                                        <option value="Allemagne">Allemagne</option>
                                        <option value="Italie">Italie</option>
                                        <option value="Autre">Autre</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Téléphone / Email -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-medium text-gray-600">Téléphone mobile</label>
                                    <input v-model="form.telephone" type="tel" placeholder="+41 79 000 00 00"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-gray-50" />
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-medium text-gray-600">Email</label>
                                    <input v-model="form.email" type="email" placeholder="email@exemple.com"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-gray-50" />
                                </div>
                            </div>

                            <!-- Taille t-shirt / Genre -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-medium text-gray-600">Taille t-shirt</label>
                                    <select v-model="form.taille_tshirt"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-gray-50">
                                        <option value="XS">XS</option>
                                        <option value="S">S</option>
                                        <option value="M">M</option>
                                        <option value="L">L</option>
                                        <option value="XL">XL</option>
                                        <option value="XXL">XXL</option>
                                    </select>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-medium text-gray-600">Genre</label>
                                    <select v-model="form.sexe"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-gray-50">
                                        <option value="M">Homme</option>
                                        <option value="F">Femme</option>
                                        <option value="A">Autre</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer sous-popup -->
                    <div class="flex justify-end px-6 py-4 border-t border-gray-100">
                        <button
                            type="button"
                            @click="valider"
                            :disabled="!formulaireValide"
                            :class="['btn-tertiary', !formulaireValide ? 'opacity-50 cursor-not-allowed' : '']"
                        >
                            Ajouter la personne
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script>
import { Icon } from '@iconify/vue';

const formVide = () => ({
    nom: '',
    prenom: '',
    date_naissance: '',
    adresse: '',
    code_postal: '',
    ville: '',
    pays: 'Suisse',
    telephone: '',
    email: '',
    taille_tshirt: 'L',
    sexe: 'M',
});

export default {
    name: 'EtapeParticipant',
    components: { Icon },
    props: {
        participants: {
            type: Array,
            default: () => [],
        },
        chargement: {
            type: Boolean,
            default: false,
        },
        modelValue: {
            type: Object,
            default: null,
        },
    },
    emits: ['update:modelValue', 'creer-participant'],
    data() {
        return {
            formulaireOuvert: false,
            emailRecherche: '',
            emailTrouve: false,
            hoveredId: null,
            hoveredNouveau: false,
            form: formVide(),
        };
    },
    computed: {
        participantSelectionne: {
            get() { return this.modelValue ?? null; },
            set(val) { this.$emit('update:modelValue', val); }
        },
        formulaireValide() {
            return this.form.nom.trim() !== '' && this.form.prenom.trim() !== '';
        },
    },
    methods: {
        estSelectionne(id) {
            return this.participantSelectionne?.id != null && this.participantSelectionne.id === id;
        },
        selectionner(participant) {
            this.participantSelectionne = participant;
        },
        ouvrirFormulaire() {
            this.formulaireOuvert = true;
        },
        fermerFormulaire() {
            this.formulaireOuvert = false;
            this.emailRecherche = '';
            this.emailTrouve = false;
            this.form = formVide();
        },
        rechercherParEmail() {
            this.emailTrouve = this.participants.some(
                p => p.email && p.email.toLowerCase() === this.emailRecherche.toLowerCase()
            );
        },
        valider() {
            if (!this.formulaireValide) return;
            const nouveau = { ...this.form, id: Date.now() };
            this.$emit('creer-participant', nouveau);
            this.participantSelectionne = nouveau;
            this.fermerFormulaire();
        },
    },
};
</script>