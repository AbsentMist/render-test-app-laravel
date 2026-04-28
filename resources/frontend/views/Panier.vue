<
<template>
    <div>
        <div class="p-6 pb-0">
            <h2 class="text-2xl font-normal text-gray-900">Mon panier</h2>
            <div class="h-1 w-20 bg-pink-200 mt-2 rounded-full mb-6"></div>
        </div>

        <div class="p-6 pt-0">
            <div class="flex flex-col lg:flex-row gap-8">
                <div
                    class="flex-1 bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-fit"
                >
                    <div
                        class="flex justify-between items-center border-b border-gray-200 pb-3 mb-6"
                    >
                        <span class="font-bold text-gray-800 text-sm"
                            >Article</span
                        >
                        <div class="flex gap-16 md:gap-32">
                            <span class="font-bold text-gray-800 text-sm"
                                >Prix</span
                            >
                        </div>
                    </div>

                    <div
                        v-if="panier.length === 0"
                        class="text-center py-10 text-gray-500 flex flex-col items-center"
                    >
                        <svg
                            class="w-16 h-16 text-gray-300 mb-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
                            ></path>
                        </svg>
                        <p class="italic">
                            Votre panier est actuellement vide.
                        </p>
                        <router-link
                            to="/evenements"
                            class="mt-4 px-6 py-2 bg-[#0e0f54] text-white rounded-lg font-medium hover:bg-[#0e0f54]/90 transition-colors"
                        >
                            Découvrir les courses
                        </router-link>
                    </div>

                    <div v-else class="flex flex-col gap-6">
                        <div
                            v-for="(article, index) in panier"
                            :key="index"
                            class="border-b border-gray-100 pb-6 last:border-0 last:pb-0"
                        >
                            <div class="flex flex-col sm:flex-row gap-6">
                                <div
                                    class="w-full sm:w-48 h-28 rounded-xl flex items-center justify-center overflow-hidden shrink-0 shadow-inner relative"
                                    :style="{
                                        backgroundColor:
                                            article.courseDetails?.evenement
                                                ?.couleur_primaire || '#5C8E9A',
                                    }"
                                >
                                    <img
                                        v-if="getLogoSource(article.courseDetails?.evenement)"
                                        :src="getLogoSource(article.courseDetails?.evenement)"
                                        class="absolute inset-0 w-full h-full object-contain p-2"
                                    />
                                    <span
                                        v-else
                                        class="text-lg text-white font-bold text-center px-2 leading-tight relative z-10"
                                        >{{
                                            article.courseDetails?.evenement
                                                ?.nom || "Évènement"
                                        }}</span
                                    >
                                </div>

                                    <div
                                        class=" w-full flex flex-col gap-1 text-sm text-gray-900"
                                    >
                                        <h3 class="text-lg font-normal">
                                            {{
                                                article.courseDetails?.evenement
                                                    ?.nom
                                            }}
                                        </h3>
                                        <div class="flex flex-row justify-between">
                                            <p class="font-semibold text-xs">-
                                                {{article.courseDetails?.nom_course}}
                                            </p>
                                            <p>
                                                {{ parseFloat(article.courseDetails?.tarif,).toFixed(2)}}.-
                                            </p>
                                        </div>

                                        <p class="font-bold mt-1 text-gray-700">
                                            {{(article.participant?.length? article.participant: article.groupeEphemere?.participants || [])
                                                .map((p) =>p.prenom + " " + p.nom,).join(", ")
                                            }}
                                        </p>

                                        <div v-if="article.options && Object.keys(article.options).length > 0" class="mt-1">
                                            <div v-for="(opt, key) in article.options" :key="key" class="font-medium text-xs text-gray-600 flex flex-row justify-between">
                                                <span>
                                                    {{opt.quantite? opt.quantite + "x " : ""}}{{ opt.option?.nom }}
                                                </span>
                                                <span>
                                                    + {{ parseFloat(opt.option?.tarif,).toFixed(2)}}.-
                                                </span>
                                            </div>
                                        </div>

                                        <p v-if="article.documents && article.documents.length > 0"
                                            class="font-medium text-xs text-blue-600 mt-1 flex items-center gap-1">
                                            <svg
                                                class="w-3 h-3"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"
                                                ></path>
                                            </svg>
                                            Document(s) joint(s)
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="flex justify-end mt-4 sm:mt-0"
                                >
                                    <div class="max-w-sm mt-3 pt-3 border-t border-gray-100 w-full flex flex-col items-end gap-1">
                                        <div v-if="getDeductionArticle(index) > 0" class="flex flex-col justify-between gap-1 w-full">
                                            <div class="flex justify-end ">
                                                <span> {{ parseFloat(article.tarif).toFixed(2) }}.-</span>
                                            </div>
                                            <div 
                                                class="w-full flex justify-between items-center text-sm font-bold text-green-600"
                                                >
                                                <span>Déduction changement</span>
                                                <span>- {{ getDeductionArticle(index).toFixed(2) }}.-</span>
                                            </div>
                                        </div>
                                        
                                        <div
                                            class="w-full flex justify-end items-center text-lg text-[#0e0f54]"
                                        >
                                            <span class="font-bold">
                                                {{ getTotalLigneArticle(article, index).toFixed(2) }}.-
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end mt-2">
                                <button
                                    @click="
                                        cartStore.supprimerInscription(index)
                                    "
                                    class="text-xs text-red-400 hover:text-red-600 font-medium transition-colors"
                                >
                                    Retirer du panier
                                </button>
                            </div>
                    </div>
                </div>

                <div
                    class="w-full lg:w-80 bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-fit sticky top-28"
                >
                    <div
                        class="flex justify-between items-center mb-3 text-sm font-bold text-gray-800"
                    >
                        <span>Sous-total</span>
                        <span>{{ sousTotal.toFixed(2) }}.-</span>
                    </div>
                    <div
                        class="flex justify-between items-center mb-6 text-sm font-bold text-gray-800"
                    >
                        <span>Frais de service</span>
                        <span>{{ fraisService }}.-</span>
                    </div>

                    <hr class="border-gray-200 mb-6" />

                    <div class="flex justify-between items-center mb-8">
                        <span class="text-xl font-black text-gray-900"
                            >Total</span
                        >
                        <span class="text-xl font-black text-[#0e0f54]"
                            >{{ total }}.-</span
                        >
                    </div>

                    <div class="flex flex-wrap justify-center gap-2 mb-6">
                        <div
                            class="w-12 h-8 bg-[#fcc900] rounded flex items-center justify-center text-[7px] font-bold text-gray-800"
                        >
                            PostFinance
                        </div>
                        <div
                            class="w-12 h-8 bg-[#fcc900] rounded flex items-center justify-center text-[6px] font-bold text-gray-800 text-center leading-tight"
                        >
                            PostFinance<br />E-Finance
                        </div>
                        <div
                            class="w-12 h-8 border border-gray-200 rounded flex items-center justify-center text-[10px] font-black text-black"
                        >
                            TWINT
                        </div>
                        <div
                            class="w-12 h-8 border border-gray-200 rounded flex items-center justify-center text-[10px] font-black text-blue-800 italic"
                        >
                            VISA
                        </div>
                        <div
                            class="w-12 h-8 border border-gray-200 rounded flex items-center justify-center relative overflow-hidden"
                        >
                            <div
                                class="w-5 h-5 bg-red-500 rounded-full absolute -left-1 opacity-80"
                            ></div>
                            <div
                                class="w-5 h-5 bg-yellow-400 rounded-full absolute -right-1 opacity-80"
                            ></div>
                        </div>
                    </div>

                    <div
                        class="flex gap-2 text-[#f44336] text-xs font-medium mb-6"
                    >
                        <svg
                            class="w-4 h-4 shrink-0 mt-0.5"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"
                            ></path>
                        </svg>
                        <p>
                            Je ne possède aucun des moyens de paiement
                            ci-dessus.
                        </p>
                    </div>

                    <label class="flex items-start gap-3 cursor-pointer mb-6">
                        <input
                            type="checkbox"
                            v-model="accepteConditions"
                            class="mt-1 w-4 h-4 text-[#d9f20b] border-gray-300 rounded focus:ring-[#cddc39]"
                        />
                        <span class="text-xs font-bold text-gray-800">
                            J'accepte les conditions générales mentionnées sur
                            le site de la course.
                        </span>
                    </label>

                    <button
                        @click="procederPaiement"
                        :disabled="
                            !accepteConditions ||
                            isProcessing ||
                            panier.length === 0
                        "
                        :class="[
                            'w-full py-3 rounded-lg font-bold transition-colors shadow-sm flex items-center justify-center gap-2',
                            accepteConditions &&
                            panier.length > 0 &&
                            !isProcessing
                                ? 'bg-[#d9f20b] hover:bg-[#c4da0a] text-[#0e0f54]'
                                : 'bg-gray-200 cursor-not-allowed opacity-70 text-gray-500',
                        ]"
                    >
                        <svg
                            v-if="isProcessing"
                            class="animate-spin h-5 w-5 text-gray-500"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                        {{ isProcessing ? "Traitement en cours..." : "Payer" }}
                    </button>
                </div>
            </div>
        </div>

        <PopupConfirmation
            v-if="popupInscriptionVisible"
            :message="popupInscriptionMessage"
            icon="mdi:check-circle-outline"
            @confirm="confirmerPopupInscription"
            @cancel="fermerPopupInscription"
        />
    </div>
</template>

<script setup>
/**
 * @fileoverview Vue Panier.
 * @description Vue de validation finale des inscriptions avant paiement.
 * @remarks Calcule les montants (déductions, frais, total), crée les inscriptions associées
 * puis redirige vers la passerelle de paiement ou finalise un changement gratuit.
 */
import { ref, computed, watch } from "vue";
import { useRouter } from "vue-router";
import { useCartStore } from "../stores/cart";
import { useAuthStore } from "../stores/auth";
import inscriptionService from "../services/inscriptionService";
import groupeService from "../services/groupeService";
import choixOptionParticipantService from "../services/choixOptionParticipantService";
import reponseQuestionParticipantService from "../services/reponseQuestionParticipantService";
import documentService from "../services/documentService";
import api from "../services/api";
import prixEvolutifService from "../services/prixEvolutifService";
import PopupConfirmation from "../components/PopupConfirmation.vue";

const router = useRouter();
const cartStore = useCartStore();
const authStore = useAuthStore();

// Données du panier via le store Pinia
const panier = computed(() => cartStore.inscriptions);

const accepteConditions = ref(false);
const isProcessing = ref(false);
const fraisServiceFixe = 2.5;
const popupInscriptionVisible = ref(false);
const popupInscriptionMessage = ref("");
const redirectionApresPopup = ref(null);

/**
 * Ouvre la popup de confirmation d'inscription avec le message fourni.
 * @param {string} message
 * @param {string|null} [redirection='/inscriptions']
 */
function ouvrirPopupInscription(message, redirection = "/inscriptions") {
    popupInscriptionMessage.value = message;
    redirectionApresPopup.value = redirection;
    popupInscriptionVisible.value = true;
}

/**
 * Ferme la popup de confirmation sans redirection.
 */
function fermerPopupInscription() {
    popupInscriptionVisible.value = false;
}

/**
 * Confirme la popup puis effectue la redirection cible si définie.
 */
function confirmerPopupInscription() {
    popupInscriptionVisible.value = false;

    if (redirectionApresPopup.value) {
        router.push(redirectionApresPopup.value);
    }
}

/**
 * Normalise la source du logo évènement pour l'affichage.
 * Supporte `logo_base64` ou `logo`, avec ou sans préfixe data URI.
 * @param {Object} evenement
 * @returns {string|null}
 */
function getLogoSource(evenement) {
    const logo = evenement?.logo_base64 || evenement?.logo;
    if (!logo) return null;
    return logo.startsWith("data:") ? logo : `data:image/png;base64,${logo}`;
}

/**
 * Vérifie si une valeur correspond à un fichier uploadable côté navigateur.
 * @param {unknown} valeur
 * @returns {boolean}
 */
function estFichierNavigateur(valeur) {
    return (
        (typeof File !== "undefined" && valeur instanceof File) ||
        (typeof Blob !== "undefined" && valeur instanceof Blob)
    );
}

/**
 * Extrait un fichier exploitable depuis différents formats possibles.
 * @param {unknown} document
 * @returns {File|Blob|null}
 */
function extraireFichierDocument(document) {
    if (estFichierNavigateur(document)) {
        return document;
    }

    if (document && typeof document === "object") {
        const candidats = [
            document.file,
            document.fichier,
            document.raw,
            document.originFileObj,
        ];

        for (const candidat of candidats) {
            if (estFichierNavigateur(candidat)) {
                return candidat;
            }
        }
    }

    return null;
}

// Déduction du prix pour le Changement de course
const deductionsParArticle = ref({});

/**
 * Retourne la déduction applicable à une ligne panier.
 * @param {number} index
 * @returns {number}
 */
const getDeductionArticle = (index) => {
    return deductionsParArticle.value[index] ?? 0;
};

/**
 * Retourne le tarif final d'une ligne après déduction de changement.
 * @param {Object} article
 * @param {number} index
 * @returns {number}
 */
const getTotalLigneArticle = (article, index) => {
    const tarif = parseFloat(article?.tarif || 0);
    const deduction = getDeductionArticle(index);
    return Math.max(tarif - deduction, 0);
};

/**
 * Calcule le supplément dû aux options pour un article du panier.
 * @param {Object} article
 * @returns {number}
 */
function calculerSupplementOptions(article) {
    const options = Object.values(article?.options || {});

    return options.reduce((total, optionSelectionnee) => {
        const option = optionSelectionnee?.option || {};
        const tarifOption = parseFloat(option.tarif || 0);

        if (!Number.isFinite(tarifOption)) {
            return total;
        }

        const quantite =
            option.type === "Quantifiable"
                ? parseFloat(optionSelectionnee?.quantite || 0)
                : 1;

        const quantiteValide = Number.isFinite(quantite)
            ? Math.max(quantite, 0)
            : 0;

        return total + tarifOption * quantiteValide;
    }, 0);
}

/**
 * Surveille le panier pour recalculer la déduction liée aux anciennes inscriptions.
 */
watch(
    panier,
    async (nouveauPanier) => {
        const mapDeductions = {};

        for (const [index, article] of nouveauPanier.entries()) {
            let deduction = 0;
            if (article.ancienneInscriptionId) {
                try {
                    const res = await api.get(
                        `/participant/inscriptions/${article.ancienneInscriptionId}`,
                    );
                    if (res.data && res.data.tarif) {
                        deduction = parseFloat(res.data.tarif);
                    }
                } catch (e) {
                    console.error(
                        "Erreur récupération ancienne inscription",
                        e,
                    );
                }
            }

            mapDeductions[index] = deduction;
        }

        deductionsParArticle.value = mapDeductions;
    },
    { immediate: true },
);

/**
 * Somme de toutes les déductions de changement dans le panier.
 * @type {import('vue').ComputedRef<number>}
 */
const deductionTotale = computed(() => {
    return Object.values(deductionsParArticle.value).reduce(
        (sum, value) => sum + parseFloat(value || 0),
        0,
    );
});

/**
 * Surveille le panier pour rafraîchir les tarifs évolutifs.
 */
watch(
    panier,
    async (nouveauPanier) => {
        for (const article of nouveauPanier) {
            if (
                article.courseDetails?.is_prix_evolutif &&
                !article.codeParticipation?.trim()
            ) {
                try {
                    const prixResp = await prixEvolutifService.getTarifActuel(
                        article.courseDetails.id,
                    );
                    if (prixResp.data?.tarif !== undefined) {
                        const tarifBase = parseFloat(prixResp.data.tarif);
                        if (Number.isFinite(tarifBase)) {
                            article.tarif =
                                tarifBase + calculerSupplementOptions(article);
                        }
                    }
                } catch (e) {
                    console.error("Erreur tarif évolutif:", e);
                }
            }
        }
    },
    { immediate: true },
);

/**
 * Sous-total après déduction éventuelle.
 * @type {import('vue').ComputedRef<number>}
 */
const sousTotal = computed(() => {
    let st = cartStore.cartTotal - deductionTotale.value;
    return st > 0 ? st : 0;
});

/**
 * Frais de service appliqués uniquement si un montant positif est dû.
 * @type {import('vue').ComputedRef<string>}
 */
const fraisService = computed(() => {
    return panier.value.length > 0 && sousTotal.value > 0
        ? fraisServiceFixe.toFixed(2)
        : "0.00";
});

/**
 * Total final à payer, frais inclus.
 * @type {import('vue').ComputedRef<string>}
 */
const total = computed(() => {
    const tot =
        panier.value.length > 0 && sousTotal.value > 0
            ? sousTotal.value + fraisServiceFixe
            : 0;
    return tot.toFixed(2);
});

/**
 * Valide le panier: crée inscriptions/options/réponses/documents puis lance le paiement.
 * @returns {Promise<void>}
 */
const procederPaiement = async () => {
    if (!accepteConditions.value || panier.value.length === 0) return;

    isProcessing.value = true;

    try {
        // On boucle sur chaque article du panier
        const promessesInscriptions = panier.value.map(
            async (article, articleIndex) => {
            // On vérifie si un groupe a déjà été créé
            let idGroupeFinal = article.id_groupe || null;

            const listeMembres =
                article.groupeEphemere?.participants &&
                article.groupeEphemere.participants.length > 0
                    ? article.groupeEphemere.participants
                    : article.participants || article.participant || [];

            const typeEstRelais =
                article.type?.id === "relais" ||
                article.type?.nom?.toLowerCase() === "relais";

            // LOGIQUE DE CRÉATION DU GROUPE
            if (!idGroupeFinal && typeEstRelais && listeMembres.length > 1) {
                console.log("🛠️ Création du groupe au moment du paiement...");
                const groupeReponse = await groupeService.createGroupe({
                    nom:
                        article.nom_equipe ||
                        article.groupeEphemere?.nom ||
                        `Équipe de ${listeMembres[0].prenom}`,
                    type: "Relais",
                    id_course: article.courseDetails.id,
                });

                idGroupeFinal =
                    groupeReponse.data?.groupe?.id ||
                    groupeReponse.data?.id ||
                    groupeReponse.data?.data?.id;

                // On ajoute les autres membres au groupe
                if (idGroupeFinal) {
                    for (let i = 1; i < listeMembres.length; i++) {
                        await groupeService.addParticipant(
                            idGroupeFinal,
                            listeMembres[i].id,
                        );
                    }
                }
            }

            // ENREGISTREMENT DES INSCRIPTIONS POUR TOUS LES MEMBRES
            const finaliserInscription = async (id_inscription, article) => {
                const choixValides = (article.choix_options ?? []).filter(
                    (c) => c.quantite > 0,
                );
                const reponses = article.reponses_questions ?? [];
                await Promise.all([
                    choixValides.length
                        ? choixOptionParticipantService.saveChoix({
                              choix: choixValides.map((c) => ({
                                  id_inscription,
                                  id_option: c.id_option,
                                  quantite: c.quantite,
                              })),
                          })
                        : Promise.resolve(),
                    reponses.length
                        ? reponseQuestionParticipantService.saveReponses({
                              reponses: reponses.map((r) => ({
                                  id_inscription,
                                  id_question: r.id_question,
                                  id_option_choisie: r.id_option_choisie,
                              })),
                          })
                        : Promise.resolve(),
                ]);

                // Upload des documents s'il y en a
                if (article.documents && article.documents.length > 0) {
                    for (const docFile of article.documents) {
                        const fichier = extraireFichierDocument(docFile);
                        if (!fichier) {
                            console.warn(
                                "Document ignoré: valeur non supportée pour l'upload.",
                                docFile,
                            );
                            continue;
                        }

                        const formData = new FormData();
                        const nomFichier =
                            typeof fichier.name === "string" && fichier.name
                                ? fichier.name
                                : "document";
                        formData.append("file", fichier, nomFichier);
                        try {
                            await documentService.uploadDocument(
                                id_inscription,
                                formData,
                            );
                        } catch (e) {
                            console.error(
                                "Erreur lors de l'upload du document:",
                                e,
                            );
                        }
                    }
                }
            };

            let deductionArticle = parseFloat(
                deductionsParArticle.value[articleIndex] ?? 0,
            );

            if (
                article.ancienneInscriptionId &&
                !Object.prototype.hasOwnProperty.call(
                    deductionsParArticle.value,
                    articleIndex,
                )
            ) {
                try {
                    const res = await api.get(
                        `/participant/inscriptions/${article.ancienneInscriptionId}`,
                    );
                    deductionArticle = parseFloat(res.data?.tarif || 0);
                } catch (e) {
                    console.error(
                        "Erreur récupération déduction changement inscription:",
                        e,
                    );
                }
            }

            const promessesParticipants = listeMembres.map(async (p) => {
                console.log("courseDetails:", article.courseDetails);
                console.log(
                    "is_prix_evolutif:",
                    article.courseDetails?.is_prix_evolutif,
                );
                // Récupérer le tarif évolutif si la course l'utilise
                let tarifFinal = article.tarif;
                if (
                    article.courseDetails?.is_prix_evolutif &&
                    !article.codeParticipation?.trim()
                ) {
                    try {
                        const prixResp =
                            await prixEvolutifService.getTarifActuel(
                                article.courseDetails.id,
                            );
                        console.log("Réponse tarif actuel:", prixResp.data);
                        if (prixResp.data?.tarif !== undefined) {
                            const tarifBase = parseFloat(prixResp.data.tarif);
                            if (Number.isFinite(tarifBase)) {
                                tarifFinal =
                                    tarifBase +
                                    calculerSupplementOptions(article);
                            }
                        }
                    } catch (e) {
                        console.error(
                            "Erreur récupération tarif évolutif, tarif de base utilisé:",
                            e,
                        );
                    }
                }
                console.log("tarifFinal:", tarifFinal);

                const tarifApresChangement = article.ancienneInscriptionId
                    ? Math.max(
                          parseFloat(tarifFinal || 0) -
                              (Number.isFinite(deductionArticle)
                                  ? deductionArticle
                                  : 0),
                          0,
                      )
                    : parseFloat(tarifFinal || 0);

                const response = await inscriptionService.createInscription({
                    id_course: article.courseDetails.id,
                    id_participant: p.id,
                    tarif: tarifApresChangement,
                    avertissement_valide: accepteConditions.value,
                    id_groupe: idGroupeFinal,
                    id_ancienne_inscription:
                        article.ancienneInscriptionId || null,
                    code_participant: article.codeParticipation || null,
                    participe_challenge: article.type?.id === "challenge",
                    type_challenge:
                        article.type?.id === "challenge"
                            ? article.groupeEphemere?.type_groupe
                            : null,
                    equipe_challenge:
                        article.type?.id === "challenge"
                            ? article.nom_equipe
                            : null,
                });
                const id_inscription =
                    response.data.inscription?.id ?? response.data.id;
                await finaliserInscription(id_inscription, article);
            });

            await Promise.all(promessesParticipants);

            // Annulation de l'ancienne inscription en cas de changement de course
            if (article.ancienneInscriptionId) {
                const updateInscriptionMethod = authStore.isAdmin
                    ? inscriptionService.updateInscriptionAdmin
                    : inscriptionService.updateInscription;

                await updateInscriptionMethod(
                    article.ancienneInscriptionId,
                    { status_paiement: 'Transféré' },
                );
            }
        },
        );

        // On attend que tout soit en base de données
        await Promise.all(promessesInscriptions);
        const montantTotal = parseFloat(total.value);

        // Si le total est à 0 (Downgrade gratuit), on valide sans passer par Payrexx
        if (montantTotal <= 0) {
            cartStore.viderPanier();
            ouvrirPopupInscription(
                "Inscription confirmée : votre changement de course a bien été enregistré.",
            );
            return;
        }

        try {
            const gatewayResponse = await api.post("/paiement/gateway", {
                montant: montantTotal,
            });

            if (gatewayResponse.data.url) {
                cartStore.viderPanier();
                window.location.href = gatewayResponse.data.url;
            }
        } catch (payrexxError) {
            console.warn(
                "Payrexx indisponible, simulation de paiement réussie !",
            );
            cartStore.viderPanier();
            ouvrirPopupInscription(
                "Inscription confirmée : vos inscriptions ont bien été enregistrées.",
            );
        }
    } catch (error) {
        console.error("Erreur globale :", error);
        ouvrirPopupInscription(
            "Une erreur est survenue lors de l'enregistrement. Merci de réessayer.",
            null,
        );
    } finally {
        isProcessing.value = false;
    }
};
</script>
>
