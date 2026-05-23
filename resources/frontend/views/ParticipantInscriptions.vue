<template>
    <Title :texte="`Mes inscriptions`" />
    <div class="p-6">
        <p v-if="erreur" class="text-accent text-label mb-4">{{ erreur }}</p>
        <div v-if="chargement" class="text-body text-center py-8">
            Chargement des inscriptions...
        </div>
        <div
            v-else
            class="overflow-x-auto rounded-xl border border-default-medium"
        >
            <table class="w-full text-sm text-left text-body">
                <thead
                    class="bg-neutral-secondary-medium text-heading text-xs uppercase"
                >
                    <tr>
                        <th class="px-4 py-3 text-center cursor-pointer hover:bg-neutral-secondary-dark transition-colors" @click="changerTri('evenement')">
                            Evènement
                            <span v-if="tri.colonne === 'evenement'" class="ml-1">{{ tri.direction === 'asc' ? '▲' : '▼' }}</span>
                        </th>
                        <th class="px-4 py-3 text-center cursor-pointer hover:bg-neutral-secondary-dark transition-colors" @click="changerTri('groupe')">
                            Groupe
                            <span v-if="tri.colonne === 'groupe'" class="ml-1">{{ tri.direction === 'asc' ? '▲' : '▼' }}</span>
                        </th>
                        <th class="px-4 py-3 text-center cursor-pointer hover:bg-neutral-secondary-dark transition-colors" @click="changerTri('equipe')">
                            Equipe/club
                            <span v-if="tri.colonne === 'equipe'" class="ml-1">{{ tri.direction === 'asc' ? '▲' : '▼' }}</span>
                        </th>
                        <th class="px-4 py-3 text-center cursor-pointer hover:bg-neutral-secondary-dark transition-colors" @click="changerTri('tarif')">
                            Tarif
                            <span v-if="tri.colonne === 'tarif'" class="ml-1">{{ tri.direction === 'asc' ? '▲' : '▼' }}</span>
                        </th>
                        <th class="px-4 py-3 text-center cursor-pointer hover:bg-neutral-secondary-dark transition-colors" @click="changerTri('status')">
                            Status
                            <span v-if="tri.colonne === 'status'" class="ml-1">{{ tri.direction === 'asc' ? '▲' : '▼' }}</span>
                        </th>
                        <th class="px-4 py-3 text-center cursor-pointer hover:bg-neutral-secondary-dark transition-colors" @click="changerTri('dossard')">
                            N° Dossard
                            <span v-if="tri.colonne === 'dossard'" class="ml-1">{{ tri.direction === 'asc' ? '▲' : '▼' }}</span>
                        </th>
                        <th class="px-4 py-3 text-center cursor-pointer hover:bg-neutral-secondary-dark transition-colors" @click="changerTri('date')">
                            Date inscription
                            <span v-if="tri.colonne === 'date'" class="ml-1">{{ tri.direction === 'asc' ? '▲' : '▼' }}</span>
                        </th>
                        <th class="px-4 py-3 text-center cursor-pointer hover:bg-neutral-secondary-dark transition-colors" @click="changerTri('participant')">
                            Participant
                            <span v-if="tri.colonne === 'participant'" class="ml-1">{{ tri.direction === 'asc' ? '▲' : '▼' }}</span>
                        </th>
                        <th class="px-4 py-3 w-8"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="inscriptionsFiltrees.length === 0">
                        <td colspan="9" class="text-center px-4 py-6 text-body">
                            Aucune inscription trouvé.
                        </td>
                    </tr>

                    <template
                        v-for="inscription in inscriptionsFiltrees"
                        :key="inscription.id"
                    >
                        <tr
                            class="border-t border-default-medium hover:bg-neutral-secondary-medium transition-colors"
                            :class="
                                inscription.status_paiement === 'Annulé'
                                    ? 'bg-accent-600'
                                    : '',
                                inscription.status_paiement === 'Transféré'
                                    ? 'bg-yellow-200'
                                    : ''
                            "
                        >
                            <td class="px-4 py-3 font-medium text-heading">
                                {{ inscription.course.evenement.nom }} -
                                {{ inscription.course.nom }}
                            </td>
                            <td class="px-4 py-3">
                                {{ inscription.groupe?.nom ?? "—" }}
                            </td>
                            <td class="px-4 py-3">
                                {{ inscription.equipe ?? "—" }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                CHF {{ inscription.tarif }}
                            </td>

                            <!-- Colonne statut avec badge échange en cours -->
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1 items-center">
                                    <!-- Statut principal -->
                                    <span
                                        class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold"
                                        :class="{
                                            'bg-green-100 text-green-700':
                                                inscription.status_paiement ===
                                                'Validé',
                                            'bg-gray-100 text-body':
                                                inscription.status_paiement ===
                                                'En attente',
                                            'bg-red-100 text-red-600':
                                                inscription.status_paiement ===
                                                'Annulé',
                                            'bg-yellow-300 text-yellow-800':
                                                inscription.status_paiement ===
                                                'Transféré',
                                            'bg-blue-300 text-blue-600':
                                                inscription.status_paiement ===
                                                'Echangé',
                                        }"
                                    >
                                        {{ inscription.status_paiement ?? "—" }}
                                    </span>

                                    <span v-if="inscription.ancienne_inscription">
                                        <Icon icon="lucide:arrow-right-left" class="text-primary" />
                                    </span>

                                    <!-- Badge échange en cours -->
                                    <span v-if="aUnEchangeEnCours(inscription.id)" class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-600">
                                        Échange en cours
                                    </span>
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                {{ inscription.dossard?.numero ?? "—" }}
                            </td>
                            <td class="px-4 py-3">{{ inscription.date_paiement?.slice(0, 10) || '—' }}</td>
                            <td class="px-4 py-3">
                                {{ inscription.participant.nom }}
                                {{ inscription.participant.prenom }}
                            </td>
                            <td class="px-4 py-3">
                                <button
                                    @click="detailInscription(inscription)"
                                    class="ml-auto items-center gap-1.5 px-4 my-2 py-1.5 rounded-lg btn-tertiary text-xs font-medium transition-colors"
                                >
                                    Détail
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <PopupInscriptionDetailParticipant
        v-if="popupDetail"
        :inscription="inscription.actuel"
        :participants="participants"
        @close="popupDetail = false"
        @ajouter-panier="onChangementConfirme"
    />
    <PopupAvertissementCourse
        v-if="popupAvertissement"
        :texte="texteInfo"
        @confirmer="afficherPopupChangement"
        @close="popupAvertissement = false"
    />
    <PopupChangementCourseParticipant
        v-if="popupChangement"
        :inscription="inscription.actuel"
        :participants="participants"
        @close="fermerPopupChangement"
    />
</template>

<script>
/**
 * @fileoverview Vue ParticipantInscriptions.
 * @description Historique des inscriptions du participant avec accès au détail et changement de course.
 * @remarks Affiche un badge "Échange en cours" lorsqu'une demande d'échange de dossard
 *          est en attente de réponse pour une inscription donnée.
 */
import { Icon } from "@iconify/vue";
import Title from "../components/Title.vue";
import inscriptionService from "../services/inscriptionService.js";
import echangeDossardService from "../services/echangeDossardService.js";
import { useCartStore } from "../stores/cart";
import PopupAvertissementCourse from "../components/PopupAvertissementCourse.vue";
import PopupChangementCourseParticipant from "../components/PopupChangementCourseParticipant.vue";
import PopupInscriptionDetailParticipant from "../components/PopupInscriptionDetailParticipant.vue";

export default {
    components: {
        Title,
        Icon,
        PopupAvertissementCourse,
        PopupChangementCourseParticipant,
        PopupInscriptionDetailParticipant,
    },
    emits: ["close"],
    setup() {
        const cartStore = useCartStore();
        return { cartStore };
    },
    data() {
        return {
            inscriptions: [],
            participants: [],
            demandesEnvoyees: [],
            chargement: true,
            erreur: "",
            evenementASupprimer: null,
            expandedRows: [],
            popupDetail: false,
            popupAvertissement: false,
            popupChangement: false,
            inscription: {
                actuel: null,
            },
            texteInfo:
                "En cas de sélection de course où le montant est supérieur à la course actuel, la différence devra être réglée.",
            tri: { colonne: 'date', direction: 'desc' },
        };
    },
    computed: {
        /**
         * Applique le tri sur les inscriptions chargées.
         * @returns {Array<Object>} Liste d'inscriptions triée selon la colonne et direction actuelle.
         */
        inscriptionsFiltrees() {
            const resultats = [...this.inscriptions];

            resultats.sort((a, b) => {
                let valeurA, valeurB;
                switch (this.tri.colonne) {
                    case 'evenement':
                        valeurA = `${a.course?.evenement?.nom ?? ''} ${a.course?.nom ?? ''}`.toLowerCase();
                        valeurB = `${b.course?.evenement?.nom ?? ''} ${b.course?.nom ?? ''}`.toLowerCase();
                        break;
                    case 'groupe':
                        valeurA = a.groupe?.nom ?? '';
                        valeurB = b.groupe?.nom ?? '';
                        break;
                    case 'equipe':
                        valeurA = a.equipe ?? '';
                        valeurB = b.equipe ?? '';
                        break;
                    case 'tarif':
                        valeurA = Number.parseFloat(a.tarif ?? 0);
                        valeurB = Number.parseFloat(b.tarif ?? 0);
                        break;
                    case 'status':
                        valeurA = a.status_paiement ?? '';
                        valeurB = b.status_paiement ?? '';
                        break;
                    case 'dossard':
                        valeurA = a.dossard?.numero ?? 0;
                        valeurB = b.dossard?.numero ?? 0;
                        break;
                    case 'participant':
                        valeurA = `${a.participant?.nom ?? ''} ${a.participant?.prenom ?? ''}`.toLowerCase();
                        valeurB = `${b.participant?.nom ?? ''} ${b.participant?.prenom ?? ''}`.toLowerCase();
                        break;
                    case 'date':
                    default:
                        valeurA = a.date_paiement ?? '';
                        valeurB = b.date_paiement ?? '';
                }
                if (typeof valeurA === 'string') {
                    valeurA = valeurA.toLowerCase();
                    valeurB = valeurB.toLowerCase();
                }
                if (valeurA < valeurB) return this.tri.direction === 'asc' ? -1 : 1;
                if (valeurA > valeurB) return this.tri.direction === 'asc' ? 1 : -1;
                return 0;
            });

            return resultats;
        },
    },
    methods: {
        /**
         * Change la colonne de tri active ou inverse sa direction.
         * @param {string} colonne Nom de la colonne triée.
         * @returns {void}
         */
        changerTri(colonne) {
            if (this.tri.colonne === colonne) {
                this.tri.direction = this.tri.direction === 'asc' ? 'desc' : 'asc';
            } else {
                this.tri.colonne = colonne;
                this.tri.direction = 'asc';
            }
        },
        /**
         * Charge les inscriptions et les demandes d'échange envoyées en parallèle.
         * @returns {Promise<void>}
         */
        async chargerInscriptions() {
            this.chargement = true;
            this.erreur = "";
            try {
                const [inscriptionsRes, demandesRes] = await Promise.all([
                    inscriptionService.getMesInscriptions(),
                    echangeDossardService.mesDemandesEnvoyees(),
                ]);
                this.inscriptions = inscriptionsRes.data;
                this.demandesEnvoyees = demandesRes.data;

                // Dédoublonner les participants
                const tousParticipants = this.inscriptions.map(
                    (i) => i.participant,
                );
                this.participants = tousParticipants.filter(
                    (p, index, self) =>
                        self.findIndex((x) => x.id === p.id) === index,
                );
            } catch (e) {
                console.error(e);
                this.erreur = "Impossible de charger les inscriptions.";
            } finally {
                this.chargement = false;
            }
        },

        /**
         * Vérifie si une inscription a une demande d'échange en cours.
         * @param {number} idInscription
         * @returns {boolean}
         */
        aUnEchangeEnCours(idInscription) {
            return this.demandesEnvoyees.some(
                (d) => d.ancienne_inscription?.id === idInscription,
            );
        },

        async fermerPopupChangement() {
            this.popupChangement = false;
            await this.chargerInscriptions();
        },

        toggleExpand(id) {
            const index = this.expandedRows.indexOf(id);
            if (index === -1) {
                this.expandedRows.push(id);
            } else {
                this.expandedRows.splice(index, 1);
            }
        },

        detailInscription(inscription) {
            this.inscription.actuel = inscription;
            this.popupDetail = true;
        },

        changerInscription(inscription) {
            this.inscription.actuel = inscription;
            this.popupAvertissement = true;
        },

        afficherPopupChangement() {
            this.popupAvertissement = false;
            this.popupChangement = true;
        },

        onChangementConfirme(data) {
            this.popupDetail = false;
            this.cartStore.ajouterInscription(data, data.course);
        },
    },

    async mounted() {
        await this.chargerInscriptions();
    },
};
</script>
