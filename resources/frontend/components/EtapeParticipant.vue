<template>
    <div class="flex flex-col gap-4 mt-4">

        <template v-if="estGroupe">
            <h2 class="text-base font-semibold text-heading">Constituez votre groupe</h2>

            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-700">Nom du groupe</label>
                <input
                    v-model="groupeData.nom"
                    type="text"
                    placeholder="Ex : Les Gazelles, Team HEG..."
                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-white"
                    @input="emitGroupe"
                />
            </div>

            <div class="flex flex-col gap-2">
                <label class="text-sm font-medium text-gray-700">
                    Membres du groupe
                    <span class="text-xs text-gray-400 ml-1">({{ groupeData.participants.length }} personne(s))</span>
                </label>

                <div v-if="groupeData.participants.length > 0" class="flex flex-col gap-2">
                    <div
                        v-for="(p, index) in groupeData.participants"
                        :key="p.id"
                        class="flex items-center justify-between px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm"
                    >
                        <div class="flex items-center gap-2">
                            <Icon icon="mdi:account-outline" class="w-4 h-4 text-gray-400 shrink-0" />
                            <span class="font-medium text-gray-800">{{ p.prenom }} {{ p.nom }}</span>
                        </div>
                        <button type="button" @click="retirerMembreGroupe(index)" class="text-red-400 hover:text-red-600 transition-colors ml-3">
                            <Icon icon="mdi:close" class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <button
                    type="button"
                    @click="ouvrirFormulaire"
                    @mouseenter="hoveredNouveau = true"
                    @mouseleave="hoveredNouveau = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl border-2 border-dashed border-gray-300 text-sm font-medium text-gray-500 hover:border-tertiary hover:text-tertiary-900 transition-all text-left bg-white"
                >
                    <Icon icon="mdi:account-plus-outline" class="w-5 h-5 shrink-0 transition-colors"
                        :class="hoveredNouveau ? 'text-tertiary-900' : 'text-gray-400'" />
                    <span>Ajouter un membre</span>
                </button>
            </div>

            <div class="flex items-start gap-2 text-xs text-gray-400 bg-gray-50 rounded-xl px-3 py-2">
                <Icon icon="mdi:information-outline" class="w-4 h-4 shrink-0 mt-0.5" />
                <p>Ce groupe est créé uniquement pour cette inscription. Pour une prochaine course, vous devrez constituer un nouveau groupe.</p>
            </div>
        </template>

        <template v-else>
            <div class="flex justify-between items-end">
                <h2 class="text-base font-semibold text-heading">
                    {{ estRelais ? 'Sélectionnez 2 personnes à inscrire' : 'Sélectionnez une personne à inscrire' }}
                </h2>
                <span v-if="estRelais && nomEquipeFinal" class="text-xs font-bold text-tertiary-900 bg-tertiary/20 px-2 py-1 rounded-md">
                    Équipe : {{ nomEquipeFinal }}
                </span>
            </div>

            <div v-if="chargement" class="text-sm text-gray-400 text-center py-2">Chargement...</div>

            <div class="grid grid-cols-2 gap-3 mt-2">
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
                    <Icon icon="mdi:account-outline" class="w-5 h-5 shrink-0 transition-colors"
                        :class="estSelectionne(participant.id) || hoveredId === participant.id ? 'text-tertiary-900' : 'text-gray-400'" />
                    <span>{{ participant.prenom }} {{ participant.nom }}</span>
                    <span v-if="estRelais && estSelectionne(participant.id)"
                        class="ml-auto text-xs bg-tertiary text-primary font-bold rounded-full w-5 h-5 flex items-center justify-center shrink-0">
                        {{ numeroSelectionne(participant.id) }}
                    </span>
                </button>

                <button
                    type="button"
                    @click="ouvrirFormulaire"
                    @mouseenter="hoveredNouveau = true"
                    @mouseleave="hoveredNouveau = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl border-2 border-dashed border-gray-300 text-sm font-medium text-gray-500 hover:border-tertiary hover:text-tertiary-900 transition-all text-left bg-white"
                >
                    <Icon icon="mdi:account-plus-outline" class="w-5 h-5 shrink-0 transition-colors"
                        :class="hoveredNouveau ? 'text-tertiary-900' : 'text-gray-400'" />
                    <span>Nouvelle personne</span>
                </button>
            </div>

            <p class="text-sm text-gray-400 text-center mt-2">La personne que vous recherchez n'existe pas ?</p>
        </template>

        <Teleport to="body">
            <div v-if="modalNomEquipeOuvert" class="fixed inset-0 z-[70] flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="validerNomEquipe">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6 flex flex-col gap-4">
                    <h3 class="text-lg font-semibold text-[#0e0f54]">Nom de l'équipe</h3>
                    <p class="text-sm text-gray-600">Voulez-vous donner un nom à votre équipe de relais ?</p>
                    
                    <div class="flex gap-6 mb-2">
                        <label class="flex items-center gap-2 cursor-pointer text-sm font-medium">
                            <input type="radio" v-model="veutNomEquipe" :value="true" class="text-[#d9f20b] focus:ring-[#c4da0a] w-4 h-4">
                            Oui
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm font-medium">
                            <input type="radio" v-model="veutNomEquipe" :value="false" class="text-[#d9f20b] focus:ring-[#c4da0a] w-4 h-4">
                            Non
                        </label>
                    </div>

                    <div v-if="veutNomEquipe" class="flex flex-col gap-1">
                        <input v-model="nomEquipeSaisi" type="text" placeholder="Ex: Les Éclairs, Team GVA..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40" />
                    </div>
                    <div v-else class="text-xs text-gray-400 italic">
                        Le nom par défaut sera : "Équipe de {{ selectionnes.length > 0 ? selectionnes[0].prenom : '' }}"
                    </div>

                    <div class="flex justify-end gap-3 mt-2 border-t border-gray-100 pt-4">
                        <button type="button" @click="validerNomEquipe" class="bg-[#d9f20b] text-[#0e0f54] px-4 py-2 rounded-xl text-sm font-bold hover:bg-[#c4da0a] transition-colors">
                            Valider
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <Teleport to="body">
            <div v-if="formulaireOuvert" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/30" @click.self="fermerFormulaire">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 flex flex-col overflow-hidden max-h-[90vh]">

                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                        <h3 class="text-base font-semibold text-heading">
                            {{ estGroupe ? 'Ajouter un membre au groupe' : 'Rechercher une personne' }}
                        </h3>
                        <button type="button" @click="fermerFormulaire" class="text-gray-400 hover:text-gray-600">
                            <Icon icon="mdi:close" class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="overflow-y-auto px-6 py-5 flex flex-col gap-4">

                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-gray-700">Adresse email</label>
                            <div class="flex gap-2">
                                <input v-model="emailRecherche" type="email" placeholder="email@exemple.com"
                                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40"
                                    @keyup.enter="lancerRecherche" />
                                <button type="button" @click="lancerRecherche" class="btn-tertiary text-sm px-3">Rechercher</button>
                            </div>
                            <div v-if="participantTrouve" class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-xl px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <Icon icon="mdi:account-check-outline" class="w-5 h-5 text-tertiary-900" />
                                    <span class="text-sm font-medium">{{ participantTrouve.prenom }} {{ participantTrouve.nom }}</span>
                                </div>
                                <button type="button" @click="selectionnerTrouve" class="btn-tertiary text-xs px-3 py-1">Sélectionner</button>
                            </div>
                            <p v-if="erreurRecherche" class="flex items-center gap-2 text-xs text-orange-600">
                                <Icon icon="mdi:information-outline" class="w-4 h-4 shrink-0" />
                                {{ erreurRecherche }}
                            </p>
                        </div>

                        <div class="flex flex-col gap-3">
                            <h4 class="text-sm font-semibold text-heading border-t border-gray-100 pt-3">Nouvelle personne</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-medium text-gray-600">Nom</label>
                                    <input v-model="form.nom" type="text" placeholder="Nom" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-gray-50" />
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-medium text-gray-600">Prénom</label>
                                    <input v-model="form.prenom" type="text" placeholder="Prénom" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-gray-50" />
                                </div>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-medium text-gray-600">Date de naissance (JJ/MM/AAAA)</label>
                                <input v-model="form.date_naissance" type="text" placeholder="JJ/MM/AAAA" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-gray-50" />
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-medium text-gray-600">Adresse</label>
                                <input v-model="form.adresse" type="text" placeholder="Rue et numéro" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-gray-50" />
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-medium text-gray-600">NPA</label>
                                    <input v-model="form.code_postal" type="text" placeholder="1000" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-gray-50" />
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-medium text-gray-600">Ville</label>
                                    <input v-model="form.ville" type="text" placeholder="Lausanne" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-gray-50" />
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-medium text-gray-600">Pays</label>
                                    <select v-model="form.pays" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-gray-50">
                                        <option value="Suisse">Suisse</option>
                                        <option value="France">France</option>
                                        <option value="Autre">Autre</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-medium text-gray-600">Téléphone</label>
                                <input v-model="form.telephone" type="text" placeholder="+41 79 000 00 00" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-gray-50" />
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-medium text-gray-600">Email</label>
                                <input v-model="form.email" type="email" placeholder="email@exemple.com" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-gray-50" />
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-medium text-gray-600">Taille t-shirt</label>
                                    <select v-model="form.taille_tshirt" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-gray-50">
                                        <option value="XS">XS</option><option value="S">S</option><option value="M">M</option>
                                        <option value="L">L</option><option value="XL">XL</option><option value="XXL">XXL</option>
                                    </select>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-medium text-gray-600">Genre</label>
                                    <select v-model="form.sexe" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-gray-50">
                                        <option value="M">Homme</option>
                                        <option value="F">Femme</option>
                                        <option value="A">Autre</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end px-6 py-4 border-t border-gray-100">
                        <button type="button" @click="valider" :disabled="!formulaireValide"
                            :class="['btn-tertiary', !formulaireValide ? 'opacity-50 cursor-not-allowed' : '']">
                            {{ estGroupe ? 'Ajouter au groupe' : 'Ajouter la personne' }}
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
        participants:    { type: Array,   default: () => [] },
        chargement:      { type: Boolean, default: false },
        typeSelectionne: { type: Object,  default: null },
        modelValue:      { type: Array,   default: () => [] },  // individuel/relais
        groupeValue:     { type: Object,  default: null },      // groupe éphémère
    },
    emits: ['update:modelValue', 'update:groupeValue', 'creer-participant', 'update:nomEquipe'],
    data() {
        return {
            groupeData: { nom: '', participants: [] },
            formulaireOuvert: false,
            emailRecherche: '',
            participantTrouve: null,
            erreurRecherche: null,
            hoveredId: null,
            hoveredNouveau: false,
            form: formVide(),

            modalNomEquipeOuvert: false,
            veutNomEquipe: false,
            nomEquipeSaisi: '',
            nomEquipeFinal: '',
        };
    },
    computed: {
        estRelais() { return this.typeSelectionne?.id === 'relais'; },
        estGroupe()  { return this.typeSelectionne?.id === 'groupe'; },
        tousLesParticipants() { return this.participants; },
        selectionnes: {
            get() { return this.modelValue ?? []; },
            set(val) { this.$emit('update:modelValue', val); }
        },
        formulaireValide() {
            return this.form.nom.trim() !== '' && this.form.prenom.trim() !== '';
        },
    },
    methods: {
        // ─── Mode groupe ─────────────────────────────────────────────────
        emitGroupe() {
            this.$emit('update:groupeValue', { ...this.groupeData, participants: [...this.groupeData.participants] });
        },
        retirerMembreGroupe(index) {
            this.groupeData.participants.splice(index, 1);
            this.emitGroupe();
        },

        // ─── Mode individuel / relais ─────────────────────────────────────
        estSelectionne(id) { return this.selectionnes.some(p => p.id === id); },
        numeroSelectionne(id) {
            const idx = this.selectionnes.findIndex(p => p.id === id);
            return idx >= 0 ? idx + 1 : null;
        },
        toggleSelectionner(participant) {
            const idx = this.selectionnes.findIndex(p => p.id === participant.id);
            if (idx >= 0) {
                const n = [...this.selectionnes]; 
                n.splice(idx, 1); 
                this.selectionnes = n;
                // Si on passe sous la barre des 2 en relais, on annule le nom d'équipe
                if (this.estRelais && n.length < 2) {
                    this.nomEquipeFinal = '';
                    this.$emit('update:nomEquipe', '');
                }
            } else {
                const max = this.estRelais ? 2 : 1;
                if (this.selectionnes.length < max) {
                    // On stocke la nouvelle sélection dans une variable temporaire
                    const nouvelleSelection = [...this.selectionnes, participant];
                    
                    // On met à jour (ce qui déclenche l'émission vers le parent)
                    this.selectionnes = nouvelleSelection;
                    
                    // On vérifie la taille de la VARIABLE TEMPORAIRE, pas de this.selectionnes !
                    if (this.estRelais && nouvelleSelection.length === 2) {
                        this.veutNomEquipe = false;
                        this.nomEquipeSaisi = '';
                        this.modalNomEquipeOuvert = true;
                    }
                }
            }
        },

        validerNomEquipe() {
            if (this.veutNomEquipe && this.nomEquipeSaisi.trim()) {
                this.nomEquipeFinal = this.nomEquipeSaisi.trim();
            } else {
                // nom de groupe par défaut "Équipe de Prénom1"
                if (this.selectionnes && this.selectionnes.length > 0) {
                    this.nomEquipeFinal = `Équipe de ${this.selectionnes[0].prenom}`;
                } else {
                    this.nomEquipeFinal = '';
                }
            }
            this.$emit('update:nomEquipe', this.nomEquipeFinal);
            this.modalNomEquipeOuvert = false;
        },

        // ─── Sous-popup partagée ──────────────────────────────────────────
        ouvrirFormulaire() { this.formulaireOuvert = true; },
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
                const response = await api.get('/participant/rechercher-participant', { params: { email: this.emailRecherche } });
                this.participantTrouve = response.data;
            } catch {
                this.erreurRecherche = 'Aucun participant trouvé avec cette adresse email.';
            }
        },
        selectionnerTrouve() {
            if (this.estGroupe) {
                if (!this.groupeData.participants.some(p => p.id === this.participantTrouve.id)) {
                    this.groupeData.participants.push(this.participantTrouve);
                    this.emitGroupe();
                }
            } else {
                this.$emit('creer-participant', this.participantTrouve);
                this.toggleSelectionner(this.participantTrouve);
            }
            this.fermerFormulaire();
        },
        async valider() {
            if (!this.formulaireValide) return;
            
            try {
                // Si ce n'est pas un groupe, inviter le participant via l'API pour qu'il soit créé en base
                if (!this.estGroupe) {
                    const response = await api.post('/participant/inviter-participant', this.form);
                    const nouveauParticipantFantome = response.data.participant;
                    
                    this.$emit('creer-participant', nouveauParticipantFantome);
                    this.toggleSelectionner(nouveauParticipantFantome);
                } else {
                    // Logique groupe normale
                    const nouveau = { ...this.form, id: Date.now() };
                    this.groupeData.participants.push(nouveau);
                    this.emitGroupe();
                }
                this.fermerFormulaire();
            } catch (error) {
                console.error("Erreur lors de la création du participant invité", error);
                alert("Erreur lors de la création de la personne. Vérifiez les informations (email ou téléphone déjà utilisés).");
            }
        },
    },
    watch: {
    typeSelectionne: {
        immediate: true, 
        handler(newType, oldType) {
            if (newType?.id === 'groupe' && oldType?.id !== 'groupe') {
                // Pré-remplir avec les participants de l'utilisateur
                this.groupeData.participants = [...this.participants];
                this.emitGroupe();
            }
            if (oldType?.id === 'groupe' && newType?.id !== 'groupe') {
                this.groupeData = { nom: '', participants: [] };
                this.$emit('update:groupeValue', null);
            }
        }
    },
},
};
</script>