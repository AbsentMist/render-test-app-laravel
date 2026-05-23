<template>
    <Title :texte="`Tableau de bord : évènements`" />
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <button
                @click="
                    $router.push('/organisateur/formulaires?onglet=Evènement')
                "
                class="btn-tertiary px-4 py-2 rounded-lg"
            >
                Nouveau
            </button>
            <div class="flex items-center gap-2">
                <span v-if="sauvegarde" class="text-xs text-gray-400"
                    >Sauvegarde...</span
                >
                <span
                    v-if="sauvegardeOrdre"
                    class="text-xs text-green-600 flex items-center gap-1"
                >
                    <Icon icon="mdi:check-circle-outline" class="w-4 h-4" />
                    Ordre sauvegardé
                </span>
            </div>
        </div>

        <p v-if="erreur" class="text-accent text-label mb-4">{{ erreur }}</p>

        <div v-if="chargement" class="text-body text-center py-8">
            Chargement des évènements...
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
                        <th class="px-4 py-3 w-20 text-center">En avant</th>
                        <th class="px-4 py-3">Nom</th>
                        <th class="px-4 py-3">Date début</th>
                        <th class="px-4 py-3">Date fin</th>
                        <th class="px-4 py-3 text-center">Actif</th>
                        <th class="px-4 py-3 text-center">Interne</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="evenements.length === 0">
                        <td colspan="7" class="text-center px-4 py-6 text-body">
                            Aucun évènement trouvé.
                        </td>
                    </tr>
                    <tr
                        v-for="(evenement, index) in evenements"
                        :key="evenement.id"
                        class="border-t border-default-medium hover:bg-neutral-secondary-medium transition-colors cursor-pointer"
                        :class="
                            evenement.ordre !== null ? 'bg-tertiary/10' : ''
                        "
                        @click.stop="
                            $router.push(
                                `/organisateur/evenements/${evenement.id}/courses`,
                            )
                        "
                    >
                        <!-- Épingler + flèches (uniquement pour les épinglés) -->
                        <td class="px-4 py-3" @click.stop>
                            <div class="flex flex-col items-center gap-1">
                                <!-- Bouton épingler/désépingler -->
                                <button
                                    @click.stop="toggleEpingler(evenement)"
                                    :title="
                                        evenement.ordre !== null
                                            ? 'Désépingler'
                                            : 'Mettre en avant'
                                    "
                                    class="transition-colors"
                                    :class="
                                        evenement.ordre !== null
                                            ? 'text-yellow-500 hover:text-gray-400'
                                            : 'text-gray-300 hover:text-yellow-500'
                                    "
                                >
                                    <Icon icon="mdi:star" class="w-5 h-5" />
                                </button>
                                <!-- Flèches uniquement pour les épinglés -->
                                <div
                                    v-if="evenement.ordre !== null"
                                    class="flex flex-col items-center gap-0.5"
                                >
                                    <button
                                        @click.stop="monterEvenement(index)"
                                        :disabled="
                                            indexParmiEpingles(index) === 0
                                        "
                                        class="p-0.5 rounded text-gray-400 hover:text-primary hover:bg-gray-100 disabled:opacity-20 disabled:cursor-not-allowed transition-colors"
                                        title="Monter"
                                    >
                                        <Icon
                                            icon="mdi:chevron-up"
                                            class="w-4 h-4"
                                        />
                                    </button>
                                    <button
                                        @click.stop="descendreEvenement(index)"
                                        :disabled="
                                            indexParmiEpingles(index) ===
                                            epingles.length - 1
                                        "
                                        class="p-0.5 rounded text-gray-400 hover:text-primary hover:bg-gray-100 disabled:opacity-20 disabled:cursor-not-allowed transition-colors"
                                        title="Descendre"
                                    >
                                        <Icon
                                            icon="mdi:chevron-down"
                                            class="w-4 h-4"
                                        />
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 font-medium text-heading">
                            <div class="flex items-center gap-2">
                                {{ evenement.nom }}
                                <span
                                    v-if="evenement.ordre !== null"
                                    class="text-xs px-1.5 py-0.5 rounded bg-yellow-100 text-yellow-700 font-medium"
                                >
                                    ⭐ #{{ evenement.ordre }}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            {{ getDateDebutEvenement(evenement) }}
                        </td>
                        <td class="px-4 py-3">
                            {{ getDateFinEvenement(evenement) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <svg
                                v-if="evenement.is_actif"
                                class="w-5 h-5 text-green-500 mx-auto"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                            <svg
                                v-else
                                class="w-5 h-5 text-accent mx-auto"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <svg
                                v-if="evenement.is_interne"
                                class="w-5 h-5 text-green-500 mx-auto"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                            <svg
                                v-else
                                class="w-5 h-5 text-accent mx-auto"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <button
                                    @click.stop="modifierEvenement(evenement)"
                                    class="p-1.5 rounded-lg text-primary hover:bg-tertiary transition-colors"
                                    title="Modifier"
                                >
                                    <Icon
                                        icon="lucide:square-pen"
                                        class="w-4 h-4"
                                    />
                                </button>
                                <button
                                    @click.stop="
                                        confirmerSuppression(evenement)
                                    "
                                    class="p-1.5 rounded-lg text-accent hover:bg-red-50 transition-colors"
                                    title="Supprimer"
                                >
                                    <Icon
                                        icon="lucide:trash-2"
                                        class="w-4 h-4"
                                    />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="text-xs text-gray-400 px-4 py-2">
                ⭐ Les événements épinglés apparaissent en premier pour les
                participants.
            </p>
        </div>

        <PopupConfirmation
            v-if="evenementASupprimer"
            icon="mdi:alert-circle-outline"
            :message="`Voulez-vous vraiment supprimer l'évènement ${evenementASupprimer.nom} ? Cette action est irréversible.`"
            @confirm="supprimerEvenement"
            @cancel="evenementASupprimer = null"
        />
    </div>
</template>

<script setup>
/**
 * @fileoverview Vue OrganisateurEvenements.
 * @description Tableau de gestion des évènements organisateur.
 * @remarks Affiche les périodes d'inscription calculées à partir des courses,
 * et gère les opérations d'édition/suppression.
 */
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import api from "../services/api.js";
import Title from "../components/Title.vue";
import PopupConfirmation from "../components/PopupConfirmation.vue";
import { Icon } from "@iconify/vue";

const router = useRouter();
const evenements = ref([]);
const chargement = ref(true);
const erreur = ref("");
const evenementASupprimer = ref(null);
const sauvegarde = ref(false);
const sauvegardeOrdre = ref(false);

// Ensemble des IDs déplacés manuellement
// Computed : liste des événements épinglés dans l'ordre
const epingles = computed(() =>
    evenements.value
        .filter((e) => e.ordre !== null)
        .sort((a, b) => a.ordre - b.ordre),
);

// Index d'un événement parmi les épinglés uniquement
function indexParmiEpingles(indexGlobal) {
    const e = evenements.value[indexGlobal];
    return epingles.value.findIndex((x) => x.id === e.id);
}

// Épingler ou désépingler un événement
async function toggleEpingler(evenement) {
    if (evenement.ordre !== null) {
        // Désépingler → ordre = null
        await api.post("/organisateur/evenements/ordre", {
            evenements: [{ id: evenement.id, ordre: null }],
        });
        evenement.ordre = null;
    } else {
        // Épingler → ordre = dernier épinglé + 1
        const maxOrdre =
            epingles.value.length > 0
                ? Math.max(...epingles.value.map((e) => e.ordre))
                : 0;
        const nouvelOrdre = maxOrdre + 1;
        await api.post("/organisateur/evenements/ordre", {
            evenements: [{ id: evenement.id, ordre: nouvelOrdre }],
        });
        evenement.ordre = nouvelOrdre;
    }
}

// Monter un événement épinglé
async function monterEvenement(indexGlobal) {
    const idx = indexParmiEpingles(indexGlobal);
    if (idx <= 0) return;
    const a = epingles.value[idx - 1];
    const b = epingles.value[idx];
    const tmpOrdre = a.ordre;
    a.ordre = b.ordre;
    b.ordre = tmpOrdre;
    await api.post("/organisateur/evenements/ordre", {
        evenements: [
            { id: a.id, ordre: a.ordre },
            { id: b.id, ordre: b.ordre },
        ],
    });
    sauvegardeOrdre.value = true;
    setTimeout(() => (sauvegardeOrdre.value = false), 2000);
}

// Descendre un événement épinglé
async function descendreEvenement(indexGlobal) {
    const idx = indexParmiEpingles(indexGlobal);
    if (idx >= epingles.value.length - 1) return;
    const a = epingles.value[idx];
    const b = epingles.value[idx + 1];
    const tmpOrdre = a.ordre;
    a.ordre = b.ordre;
    b.ordre = tmpOrdre;
    await api.post("/organisateur/evenements/ordre", {
        evenements: [
            { id: a.id, ordre: a.ordre },
            { id: b.id, ordre: b.ordre },
        ],
    });
    sauvegardeOrdre.value = true;
    setTimeout(() => (sauvegardeOrdre.value = false), 2000);
}

/**
 * Formate une date brute en locale suisse.
 * @param {string} dateString
 * @returns {string}
 */
function formaterDate(dateString) {
    if (!dateString) return "—";
    const date = new Date(dateString);
    return date.toLocaleDateString("fr-CH", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    });
}

/**
 * Retourne la première date d'inscription parmi les courses d'un évènement.
 * @param {Object} evenement
 * @returns {string}
 */
function getDateDebutEvenement(evenement) {
    if (!evenement.courses || evenement.courses.length === 0) return "—";

    const dates = evenement.courses
        .map((c) => c.debut_inscription)
        .filter((d) => d)
        .map((d) => new Date(d).getTime());

    if (dates.length === 0) return "—";

    return formaterDate(new Date(Math.min(...dates)));
}

/**
 * Retourne la dernière date d'inscription parmi les courses d'un évènement.
 * @param {Object} evenement
 * @returns {string}
 */
function getDateFinEvenement(evenement) {
    if (!evenement.courses || evenement.courses.length === 0) return "—";

    const dates = evenement.courses
        .map((c) => c.fin_inscription)
        .filter((d) => d)
        .map((d) => new Date(d).getTime());

    if (dates.length === 0) return "—";

    return formaterDate(new Date(Math.max(...dates)));
}

/**
 * Charge les évènements organisateur depuis l'API.
 * @returns {Promise<void>}
 */
async function chargerEvenements() {
    chargement.value = true;
    erreur.value = "";
    try {
        const response = await api.get("/organisateur/evenements");
        evenements.value = response.data;
    } catch (e) {
        erreur.value = "Impossible de charger les évènements.";
    } finally {
        chargement.value = false;
    }
}

/**
 * Redirige vers le formulaire de modification d'évènement.
 * @param {Object} evenement
 * @returns {void}
 */
function modifierEvenement(evenement) {
    router.push(
        `/organisateur/formulaires?onglet=Evènement&id=${evenement.id}`,
    );
}

/**
 * Ouvre la confirmation de suppression d'un évènement.
 * @param {Object} evenement
 * @returns {void}
 */
function confirmerSuppression(evenement) {
    evenementASupprimer.value = evenement;
}

/**
 * Supprime l'évènement confirmé puis met à jour la liste locale.
 * @returns {Promise<void>}
 */
async function supprimerEvenement() {
    try {
        await api.delete(
            `/organisateur/evenements/${evenementASupprimer.value.id}`,
        );
        evenements.value = evenements.value.filter(
            (e) => e.id !== evenementASupprimer.value.id,
        );
        evenementASupprimer.value = null;
    } catch (e) {
        erreur.value = "Impossible de supprimer cet évènement.";
        evenementASupprimer.value = null;
    }
}

onMounted(() => chargerEvenements());
</script>
