<template>
  <div>
    <div class="p-6 pb-0">
      <h2 class="text-2xl font-normal text-gray-900">Mon panier</h2>
      <div class="h-1 w-20 bg-pink-200 mt-2 rounded-full mb-6"></div>
    </div>

    <div class="p-6 pt-0">
      <div class="flex flex-col lg:flex-row gap-8">

        <div class="flex-1 bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-fit">

          <div class="flex justify-between items-center border-b border-gray-200 pb-3 mb-6">
            <span class="font-bold text-gray-800 text-sm">Article</span>
            <div class="flex gap-16 md:gap-32">
              <span class="font-bold text-gray-800 text-sm">Quantité</span>
              <span class="font-bold text-gray-800 text-sm">Prix</span>
            </div>
          </div>

          <div v-if="panier.length === 0" class="text-center py-10 text-gray-500 italic">
            Votre panier est actuellement vide.
          </div>

          <div v-else class="flex flex-col gap-6">
            <div v-for="article in panier" :key="article.id" class="border-b border-gray-100 pb-6 last:border-0 last:pb-0">

              <div class="flex flex-col md:flex-row gap-6">
                <div
                  class="w-48 h-28 rounded-xl p-2 flex flex-col items-center justify-center shrink-0 shadow-inner relative overflow-hidden"
                  :style="{ backgroundColor: article.couleur_primaire }"
                >
                  <span class="text-lg font-black tracking-widest text-center leading-tight uppercase relative z-10" :style="{ color: article.couleur_secondaire }" v-html="article.logoText"></span>
                  <span class="text-[9px] font-bold mt-1 tracking-wider relative z-10" :style="{ color: article.couleur_secondaire }">{{ article.logoSub }}</span>
                </div>

                <div class="flex-1 flex flex-col sm:flex-row justify-between">

                  <div class="flex flex-col gap-1 text-sm text-gray-900">
                    <h3 class="text-lg font-normal">{{ article.evenement }}</h3>
                    <p class="font-semibold text-xs">- {{ article.course }}</p>
                    <p class="font-bold mt-1">{{ article.participant }}</p>

                    <p v-for="(opt, index) in article.options" :key="index" class="font-bold">
                      {{ opt.nom }}
                    </p>

                    <p v-if="article.fichier" class="font-bold">
                      {{ article.fichier }}
                    </p>
                  </div>

                  <div class="flex items-start gap-16 md:gap-32 mt-4 sm:mt-0">
                    <div class="w-16">
                      <input
                        type="number"
                        v-model.number="article.quantite"
                        min="1"
                        class="w-full border border-gray-300 rounded-md p-1.5 text-center text-sm focus:ring-[#cddc39] focus:border-[#cddc39]"
                      >
                    </div>

                    <div class="flex flex-col items-end gap-1 font-bold text-sm min-w-[3rem]">
                      <p>{{ article.prixBase }}.-</p>
                      <p v-for="(opt, index) in article.options" :key="'p'+index">
                        {{ opt.prix }}.-
                      </p>
                      <p class="mt-4 pt-4 border-t border-gray-100 w-full text-right">
                        {{ calculerPrixLigne(article) }}.-
                      </p>
                    </div>
                  </div>

                </div>
              </div>

            </div>
          </div>

        </div>

        <div class="w-full lg:w-80 bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-fit">

          <div class="flex justify-between items-center mb-3 text-sm font-bold text-gray-800">
            <span>Sous-total</span>
            <span>{{ sousTotal }}.-</span>
          </div>
          <div class="flex justify-between items-center mb-6 text-sm font-bold text-gray-800">
            <span>Frais de service</span>
            <span>{{ fraisService }}.-</span>
          </div>

          <hr class="border-gray-200 mb-6">

          <div class="flex justify-between items-center mb-8">
            <span class="text-xl font-black text-gray-900">Total</span>
            <span class="text-xl font-black text-gray-900">{{ total }}.-</span>
          </div>

          <div class="flex flex-wrap justify-center gap-2 mb-6">
            <div class="w-12 h-8 bg-[#fcc900] rounded flex items-center justify-center text-[7px] font-bold text-gray-800">PostFinance</div>
            <div class="w-12 h-8 bg-[#fcc900] rounded flex items-center justify-center text-[6px] font-bold text-gray-800 text-center leading-tight">PostFinance<br>E-Finance</div>
            <div class="w-12 h-8 border border-gray-200 rounded flex items-center justify-center text-[10px] font-black text-black">TWINT</div>
            <div class="w-12 h-8 border border-gray-200 rounded flex items-center justify-center text-[10px] font-black text-blue-800 italic">VISA</div>
            <div class="w-12 h-8 border border-gray-200 rounded flex items-center justify-center relative overflow-hidden">
               <div class="w-5 h-5 bg-red-500 rounded-full absolute -left-1 opacity-80"></div>
               <div class="w-5 h-5 bg-yellow-400 rounded-full absolute -right-1 opacity-80"></div>
            </div>
          </div>

          <div class="flex gap-2 text-[#f44336] text-xs font-medium mb-6">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
            <p>Je ne possède aucun des moyens de paiement ci-dessus.</p>
          </div>

          <label class="flex items-start gap-3 cursor-pointer mb-6">
            <input
              type="checkbox"
              v-model="accepteConditions"
              class="mt-1 w-4 h-4 text-[#cddc39] border-gray-300 rounded focus:ring-[#cddc39]"
            >
            <span class="text-xs font-bold text-gray-800">
              J'accepte les conditions générales mentionnées sur le site de la course.
            </span>
          </label>

          <button
            @click="procederPaiement"
            :disabled="!accepteConditions"
            :class="[
              'w-full py-3 rounded-lg font-medium transition-colors text-gray-900',
              accepteConditions ? 'bg-[#cddc39] hover:bg-[#c0cf33] shadow-sm' : 'bg-gray-200 cursor-not-allowed opacity-70'
            ]"
          >
            Payer
          </button>

        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

// --- DONNÉES FICTIVES DU PANIER ---
const panier = ref([
  {
    id: 1,
    evenement: "Nocturne des Evaux",
    course: "Challenge etudiant - Mixte",
    participant: "Neris Alessandro",
    options: [
      { nom: "1 Entrée + 1 pasta bolognaise", prix: 15 }
    ],
    fichier: "Carte_étudiant_aless.pdf",
    prixBase: 45,
    quantite: 1,
    // Couleurs et Logo pour recréer la carte visuelle
    couleur_primaire: "#53687e",
    couleur_secondaire: "#44c8eb",
    logoText: 'n<span class="text-white">o</span>cturne<br/><span class="text-3xl">EVAUX</span>',
    logoSub: 'BB Switzerland'
  }
]);

// --- ÉTATS ---
const accepteConditions = ref(false);
const fraisServiceFixe = 2.50; // On peut rendre ça dynamique plus tard

// --- CALCULS ---
// Calculer le prix total d'une seule ligne (Prix de base + Prix des options) * Quantité
const calculerPrixLigne = (article) => {
  let totalOptions = article.options.reduce((sum, opt) => sum + opt.prix, 0);
  return (article.prixBase + totalOptions) * article.quantite;
};

// Sous-total de tout le panier
const sousTotal = computed(() => {
  return panier.value.reduce((sum, article) => sum + calculerPrixLigne(article), 0);
});

// Frais de service formatés
const fraisService = computed(() => {
  return panier.value.length > 0 ? fraisServiceFixe.toFixed(2) : "0.00";
});

// Total final
const total = computed(() => {
  const tot = panier.value.length > 0 ? sousTotal.value + fraisServiceFixe : 0;
  return tot.toFixed(2);
});

// --- ACTIONS ---
const procederPaiement = () => {
  if (!accepteConditions.value) return;

  console.log("Redirection vers le système de paiement avec le total de :", total.value);
  // Ici, tu pourras appeler ton service API (ex: panierParticipantService.checkout(panier.value))
  alert(`Paiement de CHF ${total.value} initié !`);
};
</script>
