<template>
    <Title texte="Échange de dossard" />

    <div class="p-6 space-y-8">
        <!-- ===== SECTION : Demandes reçues ===== -->
        <section v-if="demandesRecues.length > 0">
            <h2
                class="text-sm font-semibold text-heading uppercase tracking-wider mb-3 flex items-center gap-2"
            >
                <Icon icon="mdi:bell-outline" class="w-4 h-4 text-yellow-500" />
                Demandes d'échange reçues
                <span
                    class="bg-yellow-100 text-yellow-700 text-xs font-bold px-2 py-0.5 rounded-full"
                >
                    {{ demandesRecues.length }}
                </span>
            </h2>

            <div class="space-y-3">
                <div
                    v-for="demande in demandesRecues"
                    :key="demande.id"
                    class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                >
                    <div class="text-sm text-heading space-y-0.5">
                        <p class="font-semibold">
                            {{
                                demande.ancienne_inscription?.participant?.user
                                    ?.email ?? "—"
                            }}
                            souhaite vous céder son dossard
                        </p>
                        <p class="text-body text-xs">
                            {{ demande.course?.evenement?.nom }} ·
                            {{ demande.course?.nom }} · Dossard n°{{
                                demande.ancienne_inscription?.dossard?.numero ??
                                "—"
                            }}
                        </p>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <button
                            @click="ouvrirPopupAccepter(demande)"
                            :disabled="chargementAction"
                            class="btn-tertiary px-4 py-1.5 text-xs disabled:opacity-50"
                        >
                            Accepter
                        </button>
                        <button
                            @click="ouvrirPopupRefuser(demande)"
                            :disabled="chargementAction"
                            class="btn-accent-300 px-4 py-1.5 text-xs disabled:opacity-50"
                        >
                            Refuser
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== SECTION : Demandes envoyées en attente ===== -->
        <section v-if="demandesEnvoyees.length > 0">
            <h2
                class="text-sm font-semibold text-heading uppercase tracking-wider mb-3 flex items-center gap-2"
            >
                <Icon icon="mdi:clock-outline" class="w-4 h-4 text-blue-400" />
                Demandes envoyées en attente
            </h2>

            <div class="space-y-3">
                <div
                    v-for="demande in demandesEnvoyees"
                    :key="demande.id"
                    class="rounded-xl border border-blue-200 bg-blue-50 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                >
                    <div class="text-sm text-heading space-y-0.5">
                        <p class="font-semibold">
                            En attente de réponse de
                            {{ demande.participant?.user?.email ?? "—" }}
                        </p>
                        <p class="text-body text-xs">
                            {{ demande.course?.evenement?.nom }} ·
                            {{ demande.course?.nom }} · Dossard n°{{
                                demande.ancienne_inscription?.dossard?.numero ??
                                "—"
                            }}
                        </p>
                    </div>
                    <button
                        @click="ouvrirPopupAnnuler(demande)"
                        :disabled="chargementAction"
                        class="btn-accent-300 px-4 py-1.5 text-xs shrink-0 disabled:opacity-50"
                    >
                        Annuler
                    </button>
                </div>
            </div>
        </section>

        <!-- ===== SECTION : Initier un échange ===== -->
        <section>
            <h2
                class="text-sm font-semibold text-heading uppercase tracking-wider mb-3 flex items-center gap-2"
            >
                <Icon icon="mdi:swap-horizontal" class="w-4 h-4" />
                Céder mon dossard
            </h2>

            <!-- Chargement -->
            <div v-if="chargement" class="text-body text-center py-8">
                Chargement de vos inscriptions...
            </div>

            <!-- Aucune inscription éligible -->
            <div
                v-else-if="inscriptionsEligibles.length === 0"
                class="text-center py-12 text-gray-400 rounded-xl border border-default-medium"
            >
                <Icon
                    icon="mdi:ticket-off-outline"
                    class="w-10 h-10 mx-auto mb-3 opacity-40"
                />
                <p class="text-sm">
                    Vous n'avez aucune inscription validée avec un dossard à
                    échanger.
                </p>
            </div>

            <!-- Formulaire -->
            <div v-else class="max-w-xl space-y-5">
                <!-- Étape 1 : Choix de l'inscription -->
                <div>
                    <label class="block text-sm font-medium text-heading mb-2">
                        Choisissez l'inscription à céder
                    </label>
                    <div class="space-y-2">
                        <label
                            v-for="inscription in inscriptionsEligibles"
                            :key="inscription.id"
                            class="flex items-center gap-3 rounded-xl border p-3 cursor-pointer transition-colors"
                            :class="[
                                inscriptionSelectionnee?.id === inscription.id
                                    ? 'border-tertiary bg-tertiary/10'
                                    : 'border-default-medium hover:bg-neutral-secondary-medium',
                                echangeEnCoursPour(inscription.id)
                                    ? 'opacity-50 cursor-not-allowed'
                                    : '',
                            ]"
                        >
                            <input
                                type="radio"
                                :value="inscription"
                                v-model="inscriptionSelectionnee"
                                :disabled="echangeEnCoursPour(inscription.id)"
                                class="accent-primary"
                            />
                            <div class="text-sm flex-1">
                                <p class="font-semibold text-heading">
                                    {{ inscription.course?.evenement?.nom }} ·
                                    {{ inscription.course?.nom }}
                                </p>
                                <p class="text-body text-xs">
                                    Dossard n°<strong>{{
                                        inscription.dossard?.numero
                                    }}</strong>
                                    · Payé {{ inscription.tarif }} CHF ·
                                    {{
                                        inscription.course?.date_debut
                                            ? formatDate(
                                                  inscription.course.date_debut,
                                              )
                                            : "—"
                                    }}
                                </p>
                            </div>
                            <!-- Badge si échange déjà en cours -->
                            <span
                                v-if="echangeEnCoursPour(inscription.id)"
                                class="text-xs bg-blue-100 text-blue-600 font-semibold px-2 py-0.5 rounded-full shrink-0"
                            >
                                Échange en cours
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Étape 2 : Email du destinataire -->
                <div
                    v-if="
                        inscriptionSelectionnee &&
                        !echangeEnCoursPour(inscriptionSelectionnee.id)
                    "
                    class="space-y-4 border-t border-default-medium pt-5"
                >
                    <div>
                        <label
                            class="block text-sm font-medium text-heading mb-1"
                        >
                            Email du destinataire
                        </label>
                        <p class="text-xs text-body mb-2">
                            La personne doit déjà posséder un compte sur la
                            plateforme.
                        </p>
                        <input
                            v-model="emailDestinataire"
                            type="email"
                            placeholder="exemple@email.com"
                            class="w-full rounded-lg border border-default-medium px-3 py-2 text-sm text-heading bg-white focus:outline-none focus:ring-2 focus:ring-tertiary"
                        />
                    </div>

                    <!-- Récapitulatif -->
                    <div
                        v-if="emailDestinataire"
                        class="rounded-xl bg-neutral-secondary-medium border border-default-medium p-4 text-sm space-y-1"
                    >
                        <p class="font-semibold text-heading mb-2">
                            Récapitulatif de l'échange
                        </p>
                        <p class="text-body">
                            <span class="font-medium">Course :</span>
                            {{
                                inscriptionSelectionnee.course?.evenement?.nom
                            }}
                            — {{ inscriptionSelectionnee.course?.nom }}
                        </p>
                        <p class="text-body">
                            <span class="font-medium">Dossard :</span> n°{{
                                inscriptionSelectionnee.dossard?.numero
                            }}
                        </p>
                        <p class="text-body">
                            <span class="font-medium">Destinataire :</span>
                            {{ emailDestinataire }}
                        </p>
                        <p class="text-xs text-gray-400 mt-2">
                            Le destinataire recevra un email pour accepter ou
                            refuser. Votre inscription reste active jusqu'à son
                            acceptation.
                        </p>
                    </div>

                    <!-- Messages erreur / succès -->
                    <p v-if="erreur" class="text-sm text-red-600 font-medium">
                        {{ erreur }}
                    </p>
                    <p v-if="succes" class="text-sm text-green-600 font-medium">
                        {{ succes }}
                    </p>

                    <!-- Bouton envoi -->
                    <button
                        @click="initierEchange"
                        :disabled="!emailDestinataire || chargementAction"
                        class="btn-tertiary w-full py-2 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="chargementAction">Envoi en cours...</span>
                        <span v-else>Envoyer la demande d'échange</span>
                    </button>
                </div>
            </div>
        </section>
    </div>

    <!-- Popup accepter -->
    <PopupConfirmation
        v-if="popupAccepter"
        message="Êtes-vous sûr de vouloir accepter cet échange ? Le dossard vous sera transféré et l'inscription originale sera clôturée."
        @confirm="confirmerAcceptation"
        @cancel="
            popupAccepter = false;
            demandeEnCours = null;
        "
    />

    <!-- Popup refuser -->
    <PopupConfirmation
        v-if="popupRefuser"
        message="Êtes-vous sûr de vouloir refuser cet échange ?"
        @confirm="confirmerRefus"
        @cancel="
            popupRefuser = false;
            demandeEnCours = null;
        "
    />

    <!-- Popup annuler demande envoyée -->
    <PopupConfirmation
        v-if="popupAnnuler"
        message="Êtes-vous sûr de vouloir annuler cette demande d'échange ? Votre inscription restera active."
        @confirm="confirmerAnnulation"
        @cancel="
            popupAnnuler = false;
            demandeEnCours = null;
        "
    />
</template>

<script>
/**
 * @fileoverview Vue EchangeDossard
 * @description Permet à un participant de céder son dossard à une autre personne
 *              et de répondre aux demandes d'échange reçues.
 *
 * Flow :
 *   A (cédant) → sélectionne son inscription + entre l'email de B → demande envoyée
 *   B (recevant) → voit la demande ici → accepte ou refuse
 *   A → peut annuler une demande envoyée tant que B n'a pas répondu
 *
 * Règle métier : seules les inscriptions au status 'Validé' avec un dossard sont échangeables.
 * Un échange en cours bloque l'envoi d'une nouvelle demande pour la même inscription.
 */
import Title from "../components/Title.vue";
import PopupConfirmation from "../components/PopupConfirmation.vue";
import { Icon } from "@iconify/vue";
import inscriptionService from "../services/inscriptionService";
import echangeDossardService from "../services/echangeDossardService";

export default {
    name: "EchangeDossard",
    components: { Title, PopupConfirmation, Icon },

    data() {
        return {
            // Données
            toutesLesInscriptions: [],
            demandesRecues: [],
            demandesEnvoyees: [],

            // Sélection formulaire
            inscriptionSelectionnee: null,
            emailDestinataire: "",

            // États UI
            chargement: true,
            chargementAction: false,
            erreur: null,
            succes: null,

            // Popups
            popupAccepter: false,
            popupRefuser: false,
            popupAnnuler: false,
            demandeEnCours: null,
        };
    },

    computed: {
        /**
         * Filtre uniquement les inscriptions Validées avec un dossard.
         */
        inscriptionsEligibles() {
            return this.toutesLesInscriptions.filter(
                (i) => i.status_paiement === "Validé" && i.dossard,
            );
        },
    },

    async mounted() {
        await this.chargerDonnees();
    },

    methods: {
        async chargerDonnees() {
            this.chargement = true;
            try {
                const [
                    inscriptionsRes,
                    demandesRecuesRes,
                    demandesEnvoyeesRes,
                ] = await Promise.all([
                    inscriptionService.getMesInscriptions(),
                    echangeDossardService.mesDemandesRecues(),
                    echangeDossardService.mesDemandesEnvoyees(),
                ]);
                this.toutesLesInscriptions = inscriptionsRes.data;
                this.demandesRecues = demandesRecuesRes.data;
                this.demandesEnvoyees = demandesEnvoyeesRes.data;
            } catch (e) {
                this.erreur =
                    "Impossible de charger vos données. Veuillez réessayer.";
            } finally {
                this.chargement = false;
            }
        },

        /**
         * Vérifie si une inscription a déjà un échange en cours (demande envoyée non répondue).
         */
        echangeEnCoursPour(idInscription) {
            return this.demandesEnvoyees.some(
                (d) => d.ancienne_inscription?.id === idInscription,
            );
        },

        async initierEchange() {
            if (!this.inscriptionSelectionnee || !this.emailDestinataire)
                return;

            this.chargementAction = true;
            this.erreur = null;
            this.succes = null;

            try {
                await echangeDossardService.initierEchange(
                    this.inscriptionSelectionnee.id,
                    this.emailDestinataire,
                );
                this.succes = `Demande envoyée à ${this.emailDestinataire}. En attente de sa réponse.`;
                this.emailDestinataire = "";
                this.inscriptionSelectionnee = null;
                await this.chargerDonnees();
            } catch (e) {
                this.erreur =
                    e.response?.data?.message ??
                    "Une erreur est survenue lors de l'envoi.";
            } finally {
                this.chargementAction = false;
            }
        },

        ouvrirPopupAccepter(demande) {
            this.demandeEnCours = demande;
            this.popupAccepter = true;
        },

        ouvrirPopupRefuser(demande) {
            this.demandeEnCours = demande;
            this.popupRefuser = true;
        },

        ouvrirPopupAnnuler(demande) {
            this.demandeEnCours = demande;
            this.popupAnnuler = true;
        },

        async confirmerAcceptation() {
            this.popupAccepter = false;
            this.chargementAction = true;
            try {
                await echangeDossardService.accepterEchange(
                    this.demandeEnCours.id,
                );
                await this.chargerDonnees();
            } catch (e) {
                this.erreur =
                    e.response?.data?.message ??
                    "Erreur lors de l'acceptation.";
            } finally {
                this.chargementAction = false;
                this.demandeEnCours = null;
            }
        },

        async confirmerRefus() {
            this.popupRefuser = false;
            this.chargementAction = true;
            try {
                await echangeDossardService.refuserEchange(
                    this.demandeEnCours.id,
                );
                await this.chargerDonnees();
            } catch (e) {
                this.erreur =
                    e.response?.data?.message ?? "Erreur lors du refus.";
            } finally {
                this.chargementAction = false;
                this.demandeEnCours = null;
            }
        },

        async confirmerAnnulation() {
            this.popupAnnuler = false;
            this.chargementAction = true;
            try {
                await echangeDossardService.annulerEchange(
                    this.demandeEnCours.id,
                );
                await this.chargerDonnees();
            } catch (e) {
                this.erreur =
                    e.response?.data?.message ?? "Erreur lors de l'annulation.";
            } finally {
                this.chargementAction = false;
                this.demandeEnCours = null;
            }
        },

        formatDate(dateStr) {
            return new Date(dateStr).toLocaleDateString("fr-CH", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
            });
        },
    },
};
</script>
