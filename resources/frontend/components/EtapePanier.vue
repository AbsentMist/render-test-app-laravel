<template>
    <div class="flex flex-col gap-5 mt-4">
        <h2 class="text-base font-semibold text-heading">
            Ajoutez votre inscription dans le panier
        </h2>

        <!-- Champ unique code -->
        <div class="flex flex-col gap-2">
            <label class="text-sm font-medium text-gray-700">
                {{ estGroupeOuRelais ? "Code de rabais" : "Code promotionnel" }}
            </label>
            <div class="flex gap-2">
                <input
                    v-model="codeUnique"
                    type="text"
                    :placeholder="
                        estGroupeOuRelais
                            ? 'Code de rabais'
                            : 'Code dossard ou code rabais'
                    "
                    :disabled="chargement"
                    class="flex-1 border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 bg-white transition-colors"
                    :class="
                        erreurCode
                            ? 'border-red-400 focus:ring-red-200'
                            : 'border-gray-300 focus:ring-secondary/40'
                    "
                    @keyup.enter="appliquerCode"
                    @input="erreurCode = null"
                />
                <button
                    @click="appliquerCode"
                    :disabled="!codeUnique.trim() || chargement"
                    class="btn-tertiary px-4 py-2 text-sm disabled:opacity-50 disabled:cursor-not-allowed shrink-0"
                >
                    <span v-if="chargement">...</span>
                    <span v-else>Appliquer</span>
                </button>
            </div>

            <!-- Erreur -->
            <p
                v-if="erreurCode"
                class="text-xs text-red-600 flex items-center gap-1"
            >
                <Icon
                    icon="mdi:alert-circle-outline"
                    class="w-3.5 h-3.5 shrink-0"
                />
                {{ erreurCode }}
            </p>

            <!-- Codes appliqués -->
            <div class="flex flex-col gap-2 mt-1">
                <!-- Badge dossard -->
                <div
                    v-if="codeDossardValide"
                    class="flex items-center justify-between bg-blue-50 border border-blue-200 rounded-xl px-3 py-2"
                >
                    <div class="flex items-center gap-2">
                        <Icon
                            icon="mdi:ticket-check-outline"
                            class="w-4 h-4 text-blue-600 shrink-0"
                        />
                        <p class="text-xs text-blue-700 font-semibold">
                            {{ codeDossardValide.message }}
                        </p>
                    </div>
                    <button
                        @click="retirerDossard"
                        class="text-xs text-blue-400 hover:text-blue-600 ml-3 shrink-0"
                    >
                        Retirer
                    </button>
                </div>

                <!-- Badge rabais -->
                <div
                    v-if="rabaisApplique"
                    class="flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-3 py-2"
                >
                    <div class="flex items-center gap-2">
                        <Icon
                            icon="mdi:tag-check-outline"
                            class="w-4 h-4 text-green-600 shrink-0"
                        />
                        <div class="text-xs text-green-700">
                            <span class="font-semibold">{{
                                rabaisApplique.message
                            }}</span>
                            <span class="ml-1"
                                >→ vous économisez
                                <strong
                                    >{{
                                        rabaisApplique.montant_rabais.toFixed(2)
                                    }}
                                    CHF</strong
                                ></span
                            >
                        </div>
                    </div>
                    <button
                        @click="retirerRabais"
                        class="text-xs text-green-400 hover:text-green-600 ml-3 shrink-0"
                    >
                        Retirer
                    </button>
                </div>
            </div>
        </div>

        <!-- Message d'info -->
        <div class="flex flex-col gap-3 text-sm text-gray-600">
            <p>
                Pour valider le processus d'inscription, vous devez cliquer sur
                le bouton <strong>"ajouter au panier"</strong>.
            </p>
            <div
                class="flex items-start gap-3 bg-orange-50 border border-orange-200 rounded-xl p-4"
            >
                <Icon
                    icon="mdi:alert-outline"
                    class="w-5 h-5 text-orange-400 shrink-0 mt-0.5"
                />
                <p class="text-orange-700 text-xs leading-relaxed">
                    Veuillez noter que vous ne serez pas officiellement inscrit
                    tant que vous n'aurez pas payé votre panier.
                </p>
            </div>
        </div>
    </div>
</template>

<script>
import { Icon } from "@iconify/vue";
import codeRabaisService from "../services/codeRabaisService";
import codeDossardService from "../services/codeDossardService";

export default {
    name: "EtapePanier",
    components: { Icon },
    props: {
        codeParticipation: { type: String, default: "" },
        idCourse: { type: Number, default: null },
        tarif: { type: Number, default: 0 },
        typeInscription: { type: String, default: null },
    },
    emits: [
        "update:codeParticipation",
        "rabais-applique",
        "rabais-retire",
        "dossard-valide",
        "dossard-retire",
    ],
    data() {
        return {
            codeUnique: "",
            rabaisApplique: null,
            codeDossardValide: null,
            erreurCode: null,
            chargement: false,
        };
    },
    computed: {
        estGroupeOuRelais() {
            return (
                this.typeInscription === "groupe" ||
                this.typeInscription === "relais"
            );
        },
    },
    methods: {
        async appliquerCode() {
            const code = this.codeUnique.trim();
            if (!code || !this.idCourse) return;

            this.chargement = true;
            this.erreurCode = null;

            // Pour groupe/relais : uniquement le code rabais
            if (!this.estGroupeOuRelais) {
                // 1. Essayer comme code dossard
                try {
                    const res = await codeDossardService.validerCode(
                        code,
                        this.idCourse,
                    );
                    if (res.data.valide) {
                        this.codeDossardValide = res.data;
                        this.codeUnique = "";
                        this.$emit("dossard-valide", res.data);
                        this.$emit("update:codeParticipation", res.data.code);
                        this.chargement = false;
                        return;
                    }
                } catch (_) {
                    // Pas un code dossard, on continue
                }
            }

            // 2. Essayer comme code rabais
            try {
                const res = await codeRabaisService.validerCode(
                    code,
                    this.idCourse,
                    this.tarif,
                );
                if (res.data.valide) {
                    this.rabaisApplique = res.data;
                    this.codeUnique = "";
                    this.$emit("rabais-applique", res.data);
                    this.chargement = false;
                    return;
                }
            } catch (_) {
                // Pas un code rabais non plus
            }

            // 3. Aucun code valide
            this.erreurCode = "Code invalide ou non applicable à cette course.";
            this.chargement = false;
        },

        retirerDossard() {
            this.codeDossardValide = null;
            this.$emit("update:codeParticipation", "");
            this.$emit("dossard-retire");
        },

        retirerRabais() {
            this.rabaisApplique = null;
            this.$emit("rabais-retire");
        },
    },
};
</script>
