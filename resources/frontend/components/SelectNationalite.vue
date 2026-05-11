<template>
    <div class="relative" ref="containerRef">
        <input
            v-model="recherche"
            type="text"
            :placeholder="placeholder"
            :class="inputClass"
            :disabled="disabled"
            @focus="ouvert = true"
            @input="ouvert = true"
        />
        <span
            class="absolute inset-y-0 right-3 flex items-center text-gray-400 pointer-events-none"
        >
            <svg
                class="w-4 h-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                />
            </svg>
        </span>
        <div
            v-if="ouvert && paysFiltres.length > 0"
            class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto"
        >
            <button
                v-for="pays in paysFiltres"
                :key="pays"
                type="button"
                @mousedown.prevent="selectionner(pays)"
                class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 transition-colors"
            >
                {{ pays }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from "vue";

const props = defineProps({
    modelValue: { type: String, default: "" },
    placeholder: { type: String, default: "Rechercher un pays..." },
    inputClass: {
        type: String,
        default:
            "w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-white pr-8",
    },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(["update:modelValue"]);

const pays = [
    "Afghanistan",
    "Afrique du Sud",
    "Albanie",
    "Algérie",
    "Allemagne",
    "Andorre",
    "Angola",
    "Arabie Saoudite",
    "Argentine",
    "Arménie",
    "Australie",
    "Autriche",
    "Azerbaïdjan",
    "Bahreïn",
    "Bangladesh",
    "Belgique",
    "Bénin",
    "Biélorussie",
    "Bolivie",
    "Bosnie-Herzégovine",
    "Botswana",
    "Brésil",
    "Bulgarie",
    "Burkina Faso",
    "Burundi",
    "Cambodge",
    "Cameroun",
    "Canada",
    "Chili",
    "Chine",
    "Chypre",
    "Colombie",
    "Congo",
    "Corée du Nord",
    "Corée du Sud",
    "Costa Rica",
    "Côte d'Ivoire",
    "Croatie",
    "Cuba",
    "Danemark",
    "Djibouti",
    "Égypte",
    "Émirats Arabes Unis",
    "Équateur",
    "Érythrée",
    "Espagne",
    "Estonie",
    "États-Unis",
    "Éthiopie",
    "Finlande",
    "France",
    "Gabon",
    "Gambie",
    "Géorgie",
    "Ghana",
    "Grèce",
    "Guatemala",
    "Guinée",
    "Haïti",
    "Honduras",
    "Hongrie",
    "Inde",
    "Indonésie",
    "Irak",
    "Iran",
    "Irlande",
    "Islande",
    "Israël",
    "Italie",
    "Jamaïque",
    "Japon",
    "Jordanie",
    "Kazakhstan",
    "Kenya",
    "Kirghizistan",
    "Kosovo",
    "Koweït",
    "Laos",
    "Liban",
    "Libye",
    "Liechtenstein",
    "Lituanie",
    "Luxembourg",
    "Macédoine du Nord",
    "Madagascar",
    "Malaisie",
    "Mali",
    "Malte",
    "Maroc",
    "Mauritanie",
    "Mexique",
    "Moldavie",
    "Monaco",
    "Mongolie",
    "Monténégro",
    "Mozambique",
    "Namibie",
    "Népal",
    "Nicaragua",
    "Niger",
    "Nigéria",
    "Norvège",
    "Nouvelle-Zélande",
    "Oman",
    "Ouganda",
    "Ouzbékistan",
    "Pakistan",
    "Panama",
    "Paraguay",
    "Pays-Bas",
    "Pérou",
    "Philippines",
    "Pologne",
    "Portugal",
    "Qatar",
    "République Centrafricaine",
    "République Démocratique du Congo",
    "République Dominicaine",
    "République Tchèque",
    "Roumanie",
    "Royaume-Uni",
    "Russie",
    "Rwanda",
    "Salvador",
    "Sénégal",
    "Serbie",
    "Sierra Leone",
    "Singapour",
    "Slovaquie",
    "Slovénie",
    "Somalie",
    "Soudan",
    "Sri Lanka",
    "Suède",
    "Suisse",
    "Syrie",
    "Tadjikistan",
    "Tanzanie",
    "Thaïlande",
    "Togo",
    "Tunisie",
    "Turkménistan",
    "Turquie",
    "Ukraine",
    "Uruguay",
    "Venezuela",
    "Vietnam",
    "Yémen",
    "Zambie",
    "Zimbabwe",
];

const recherche = ref(props.modelValue || "");
const ouvert = ref(false);
const containerRef = ref(null);

const paysFiltres = computed(() => {
    if (!recherche.value) return pays;
    const q = recherche.value.toLowerCase();
    return pays.filter((p) => p.toLowerCase().includes(q));
});

function selectionner(p) {
    recherche.value = p;
    ouvert.value = false;
    emit("update:modelValue", p);
}

function handleClickOutside(e) {
    if (containerRef.value && !containerRef.value.contains(e.target)) {
        ouvert.value = false;
        // Si la valeur saisie ne correspond à aucun pays, on remet la valeur précédente
        if (!pays.includes(recherche.value)) {
            recherche.value = props.modelValue || "";
        }
    }
}

watch(
    () => props.modelValue,
    (val) => {
        recherche.value = val || "";
    },
);

onMounted(() => document.addEventListener("mousedown", handleClickOutside));
onBeforeUnmount(() =>
    document.removeEventListener("mousedown", handleClickOutside),
);
</script>
