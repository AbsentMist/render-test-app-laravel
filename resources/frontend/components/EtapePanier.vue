<template>
    <div class="flex flex-col gap-5 mt-4">
        <h2 class="text-base font-semibold text-heading">
            Ajoutez votre inscription dans le panier
        </h2>

        <!-- Code de participation -->
        <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700"
                >Code de participation</label
            >
            <div class="flex gap-2">
                <input
                    v-model="codeInterne"
                    type="text"
                    placeholder="Code de participation"
                    @input="emettreCodeParticipation"
                    :disabled="codeDossardValide !== null"
                    class="flex-1 border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 bg-white transition-colors"
                    :class="
                        codeDossardValide
                            ? 'border-blue-400 bg-blue-50'
                            : 'border-gray-300 focus:ring-secondary/40'
                    "
                    @keyup.enter="validerCodeDossard"
                />
                <button
                    v-if="!codeDossardValide"
                    @click="validerCodeDossard"
                    :disabled="!codeInterne.trim() || chargementCodeDossard"
                    class="btn-tertiary px-4 py-2 text-sm disabled:opacity-50 disabled:cursor-not-allowed shrink-0"
                >
                    <span v-if="chargementCodeDossard">...</span>
                    <span v-else>Appliquer</span>
                </button>
                <button
                    v-else
                    @click="retirerCodeDossard"
                    class="btn-accent-300 px-4 py-2 text-sm shrink-0"
                >
                    Retirer
                </button>
            </div>
            <!-- Badge confirmation -->
            <div
                v-if="codeDossardValide"
                class="flex items-center gap-2 mt-1 bg-blue-50 border border-blue-200 rounded-xl px-3 py-2"
            >
                <Icon
                    icon="mdi:ticket-check-outline"
                    class="w-4 h-4 text-blue-600 shrink-0"
                />
                <p class="text-xs text-blue-700 font-semibold">
                    {{ codeDossardValide.message }}
                </p>
            </div>
            <p v-if="erreurCodeDossard" class="text-xs text-red-600 mt-1">
                {{ erreurCodeDossard }}
            </p>
        </div>

        <!-- Code de rabais -->
        <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700"
                >Code de rabais</label
            >
            <div class="flex gap-2">
                <input
                    v-model="codeRabaisInterne"
                    type="text"
                    placeholder="Ex: ETUDIANT2026"
                    :disabled="rabaisApplique !== null"
                    class="flex-1 border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 bg-white transition-colors"
                    :class="[
                        rabaisApplique
                            ? 'border-green-400 bg-green-50'
                            : 'border-gray-300 focus:ring-secondary/40',
                        erreurCode ? 'border-red-400' : '',
                    ]"
                    @keyup.enter="appliquerCode"
                />
                <button
                    v-if="!rabaisApplique"
                    @click="appliquerCode"
                    :disabled="!codeRabaisInterne.trim() || chargementCode"
                    class="btn-tertiary px-4 py-2 text-sm disabled:opacity-50 disabled:cursor-not-allowed shrink-0"
                >
                    <span v-if="chargementCode">...</span>
                    <span v-else>Appliquer</span>
                </button>
                <button
                    v-else
                    @click="supprimerCode"
                    class="btn-accent-300 px-4 py-2 text-sm shrink-0"
                >
                    Retirer
                </button>
            </div>
            <p
                v-if="erreurCode"
                class="text-xs text-red-600 mt-1 flex items-center gap-1"
            >
                <Icon icon="mdi:alert-circle-outline" class="w-3.5 h-3.5" />
                {{ erreurCode }}
            </p>
            <div
                v-if="rabaisApplique"
                class="flex items-center gap-2 mt-1 bg-green-50 border border-green-200 rounded-xl px-3 py-2"
            >
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
            codeInterne: this.codeParticipation || "",
            codeRabaisInterne: "",
            rabaisApplique: null,
            erreurCode: null,
            chargementCode: false,
            codeDossardValide: null,
            chargementCodeDossard: false,
        };
    },
    watch: {
        codeParticipation(valeur) {
            this.codeInterne = valeur || "";
        },
        codeInterne(valeur) {
            if (!valeur?.trim()) this.codeDossardValide = null;
        },
    },
    methods: {
        emettreCodeParticipation() {
            this.$emit("update:codeParticipation", this.codeInterne);
        },
        async validerCodeDossard() {
            const code = this.codeInterne?.trim();
            if (!code || !this.idCourse) {
                this.codeDossardValide = null;
                return;
            }

            this.chargementCodeDossard = true;
            this.codeDossardValide = null;
            this.erreurCodeDossard = null;

            try {
                const res = await codeDossardService.validerCode(
                    code,
                    this.idCourse,
                );
                if (res.data.valide) {
                    this.codeDossardValide = res.data;
                    this.$emit("dossard-valide", res.data);
                    // Code dossard valide → pas besoin de vérifier entreprise
                    return;
                }
            } catch (e) {
                // Pas un code dossard → essayer comme code entreprise
                this.$parent?.verifierCodeEntreprise?.();
            } finally {
                this.chargementCodeDossard = false;
            }
        },
        async appliquerCode() {
            if (!this.codeRabaisInterne.trim() || !this.idCourse) return;
            this.chargementCode = true;
            this.erreurCode = null;
            try {
                const response = await codeRabaisService.validerCode(
                    this.codeRabaisInterne,
                    this.idCourse,
                    this.tarif,
                );
                this.rabaisApplique = response.data;
                this.$emit("rabais-applique", response.data);
            } catch (e) {
                this.erreurCode =
                    e.response?.data?.message ??
                    "Code invalide ou non applicable.";
            } finally {
                this.chargementCode = false;
            }
        },
        supprimerCode() {
            this.rabaisApplique = null;
            this.codeRabaisInterne = "";
            this.erreurCode = null;
            this.$emit("rabais-retire");
        },
        retirerCodeDossard() {
            this.codeDossardValide = null;
            this.codeInterne = "";
            this.$emit("update:codeParticipation", "");
            this.$emit("dossard-retire");
        },
    },
};
</script>
