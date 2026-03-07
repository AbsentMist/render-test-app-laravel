<template>
    <div class="flex flex-col gap-4 mt-4">
        <h2 class="text-base font-semibold text-heading">
            {{ estRelais ? 'Sélectionnez 2 personnes à inscrire' : 'Sélectionnez une personne à inscrire' }}
        </h2>

        <div v-if="chargement" class="text-sm text-gray-400 text-center py-2">Chargement...</div>

        <!-- Grille participants + bouton nouvelle personne -->
        <div class="grid grid-cols-2 gap-3">
            <button
                v-for="participant in tousLesParticipants"
                :key="participant.id"
                type="button"
                @click="toggleSelectionner(participant)"
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
                <!-- Badge numéro si relais -->
                <span v-if="estRelais && estSelectionne(participant.id)"
                    class="ml-auto text-xs bg-tertiary text-primary font-bold rounded-full w-5 h-5 flex items-center justify-center shrink-0">
                    {{ numeroSelectionne(participant.id) }}
                </span>
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

        <!-- Sous-popup création / recherche participant -->
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
                        <button type="button" @click="fermerFormulaire" class="text-gray-400 hover:text-gray-600">
                            <Icon icon="mdi:close" class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="overflow-y-auto px-6 py-5 flex flex-col gap-4">

                        <!-- Recherche par email -->
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-gray-700">Adresse email</label>
                            <div class="flex gap-2">
                                <input
                                    v-model="emailRecherche"
                                    type="email"
                                    placeholder="email@exemple.com"
                                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40"
                                    @keyup.enter="lancerRecherche"
                                />
                                <button type="button" @click="lancerRecherche" class="btn-tertiary text-sm px-3">
                                    Rechercher
                                </button>
                            </div>

                            <!-- Résultat trouvé -->
                            <div v-if="participantTrouve" class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-xl px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <Icon icon="mdi:account-check-outline" class="w-5 h-5 text-tertiary-900" />
                                    <span class="text-sm font-medium">{{ participantTrouve.prenom }} {{ participantTrouve.nom }}</span>
                                </div>
                                <button type="button" @click="selectionnerTrouve" class="btn-tertiary text-xs px-3 py-1">
                                    Sélectionner
                                </button>
                            </div>

                            <p v-if="erreurRecherche" class="flex items-center gap-2 text-xs text-orange-600">
                                <Icon icon="mdi:information-outline" class="w-4 h-4 shrink-0" />
                                {{ erreurRecherche }}
                            </p>
                        </div>

                        <!-- Formulaire nouvelle personne -->
                        <div class="flex flex-col gap-3">
                            <h4 class="text-sm font-semibold text-heading border-t border-gray-100 pt-3">Nouvelle personne</h4>

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

                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-medium text-gray-600">Date de naissance (JJ/MM/AAAA)</label>
                                <input v-model="form.date_naissance" type="text" placeholder="JJ/MM/AAAA"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-gray-50" />
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-medium text-gray-600">Adresse</label>
                                <input v-model="form.adresse" type="text" placeholder="Rue et numéro"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-gray-50" />
                            </div>

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
import api from '../services/api';

const formVide = () => ({
    nom: '', prenom: '', date_naissance: '', adresse: '',
    code_postal: '', ville: '', pays: 'Suisse',
    telephone: '', email: '', taille_tshirt: 'M', sexe: 'M',
});

export default {
    name: 'EtapeParticipant',
    components: { Icon },
    props: {
        participants:     { type: Array, default: () => [] },
        chargement:       { type: Boolean, default: false },
        typeSelectionne:  { type: Object, default: null },
        modelValue:       { type: Array, default: () => [] },
    },
    emits: ['update:modelValue', 'creer-participant'],
    data() {
        return {
            formulaireOuvert: false,
            emailRecherche: '',
            participantTrouve: null,
            erreurRecherche: null,
            hoveredId: null,
            hoveredNouveau: false,
            form: formVide(),
        };
    },
    computed: {
        estRelais() {
            return this.typeSelectionne?.id === 'relais';
        },
        // Tous les participants : ceux passés en props (déjà fusionnés dans le parent)
        tousLesParticipants() {
            return this.participants;
        },
        selectionnes: {
            get() { return this.modelValue ?? []; },
            set(val) { this.$emit('update:modelValue', val); }
        },
        formulaireValide() {
            return this.form.nom.trim() !== '' && this.form.prenom.trim() !== '';
        },
    },
    methods: {
        estSelectionne(id) {
            return this.selectionnes.some(p => p.id === id);
        },
        numeroSelectionne(id) {
            const idx = this.selectionnes.findIndex(p => p.id === id);
            return idx >= 0 ? idx + 1 : null;
        },
        toggleSelectionner(participant) {
            const idx = this.selectionnes.findIndex(p => p.id === participant.id);
            if (idx >= 0) {
                // Déselectionner
                const nouveau = [...this.selectionnes];
                nouveau.splice(idx, 1);
                this.selectionnes = nouveau;
            } else {
                // Sélectionner — max 1 si individuel, max 2 si relais
                const max = this.estRelais ? 2 : 1;
                if (this.selectionnes.length < max) {
                    this.selectionnes = [...this.selectionnes, participant];
                }
            }
        },
        ouvrirFormulaire() {
            this.formulaireOuvert = true;
        },
        fermerFormulaire() {
            this.formulaireOuvert = false;
            this.emailRecherche = '';
            this.participantTrouve = null;
            this.erreurRecherche = null;
            this.form = formVide();
        },
        async lancerRecherche() {
            if (!this.emailRecherche) return;
            this.participantTrouve = null;
            this.erreurRecherche = null;
            try {
                const response = await api.get('/participant/rechercher-participant', {
                    params: { email: this.emailRecherche }
                });
                this.participantTrouve = response.data;
            } catch {
                this.erreurRecherche = 'Aucun participant trouvé avec cette adresse email.';
            }
        },
        selectionnerTrouve() {
            this.$emit('creer-participant', this.participantTrouve);
            this.toggleSelectionner(this.participantTrouve);
            this.fermerFormulaire();
        },
        valider() {
            if (!this.formulaireValide) return;
            const nouveau = { ...this.form, id: Date.now() };
            this.$emit('creer-participant', nouveau);
            this.toggleSelectionner(nouveau);
            this.fermerFormulaire();
        },
    },
};
</script>