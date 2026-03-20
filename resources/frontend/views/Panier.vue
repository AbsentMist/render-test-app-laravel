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
              <span class="font-bold text-gray-800 text-sm hidden sm:block">Quantité</span>
              <span class="font-bold text-gray-800 text-sm">Prix</span>
            </div>
          </div>

          <div v-if="panier.length === 0" class="text-center py-10 text-gray-500 flex flex-col items-center">
            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <p class="italic">Votre panier est actuellement vide.</p>
            <router-link to="/evenements" class="mt-4 px-6 py-2 bg-[#0e0f54] text-white rounded-lg font-medium hover:bg-[#0e0f54]/90 transition-colors">
              Découvrir les courses
            </router-link>
          </div>

          <div v-else class="flex flex-col gap-6">
            <div v-for="(article, index) in panier" :key="index" class="border-b border-gray-100 pb-6 last:border-0 last:pb-0">

              <div class="flex flex-col sm:flex-row gap-6">
                
                <div 
                  class="w-full sm:w-48 h-28 rounded-xl flex items-center justify-center overflow-hidden shrink-0 shadow-inner relative"
                  :style="{ backgroundColor: article.courseDetails?.evenement?.couleur_primaire || '#5C8E9A' }"
                >
                  <img v-if="article.courseDetails?.evenement?.logo_base64" :src="'data:image/png;base64,' + article.courseDetails.evenement.logo_base64" class="absolute inset-0 w-full h-full object-contain p-2" />
                  <span v-else class="text-lg text-white font-bold text-center px-2 leading-tight relative z-10">{{ article.courseDetails?.evenement?.nom || 'Évènement' }}</span>
                </div>

                <div class="flex-1 flex flex-col sm:flex-row justify-between">

                  <div class="flex flex-col gap-1 text-sm text-gray-900">
                    <h3 class="text-lg font-normal">{{ article.courseDetails?.evenement?.nom }}</h3>
                    <p class="font-semibold text-xs">- {{ article.courseDetails?.nom_course }} <template v-if="article.courseDetails?.sous_categorie">• {{ article.courseDetails?.sous_categorie }}</template></p>
                    
                    <p class="font-bold mt-1 text-gray-700">
                      {{ article.participant.map(p => p.prenom + ' ' + p.nom).join(', ') }}
                    </p>

                    <div v-if="article.options && Object.keys(article.options).length > 0" class="mt-1">
                      <p v-for="(opt, key) in article.options" :key="key" class="font-medium text-xs text-gray-600">
                        {{ opt.quantite ? opt.quantite + 'x ' : '' }}{{ opt.option?.nom }}
                      </p>
                    </div>

                    <p v-if="article.documents && article.documents.length > 0" class="font-medium text-xs text-blue-600 mt-1 flex items-center gap-1">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                      Document(s) joint(s)
                    </p>
                  </div>

                  <div class="flex items-start gap-16 md:gap-32 mt-4 sm:mt-0">
                    <div class="w-16 hidden sm:block">
                      <div class="w-full border border-gray-200 bg-gray-50 rounded-md p-1.5 text-center text-sm font-medium text-gray-500">
                        1 </div>
                    </div>

                    <div class="flex flex-col items-end gap-1 font-bold text-sm min-w-[4rem]">
                      <p class="text-gray-600">{{ article.courseDetails?.tarif }}.-</p>
                      
                      <p v-for="(opt, key) in article.options" :key="'opt'+key" class="text-xs text-gray-400">
                        + {{ (opt.option?.tarif * (opt.quantite || 1)).toFixed(2) }}.-
                      </p>
                      
                      <p class="mt-4 pt-4 border-t border-gray-100 w-full text-right text-lg text-[#0e0f54]">
                        {{ parseFloat(article.tarif).toFixed(2) }}.-
                      </p>
                    </div>
                  </div>

                </div>
              </div>

              <div class="flex justify-end mt-2">
                <button @click="cartStore.supprimerInscription(index)" class="text-xs text-red-400 hover:text-red-600 font-medium transition-colors">
                  Retirer du panier
                </button>
              </div>

            </div>
          </div>

        </div>

        <div class="w-full lg:w-80 bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-fit sticky top-28">

          <div class="flex justify-between items-center mb-3 text-sm font-bold text-gray-800">
            <span>Sous-total</span>
            <span>{{ sousTotal.toFixed(2) }}.-</span>
          </div>
          <div class="flex justify-between items-center mb-6 text-sm font-bold text-gray-800">
            <span>Frais de service</span>
            <span>{{ fraisService }}.-</span>
          </div>

          <hr class="border-gray-200 mb-6">

          <div class="flex justify-between items-center mb-8">
            <span class="text-xl font-black text-gray-900">Total</span>
            <span class="text-xl font-black text-[#0e0f54]">{{ total }}.-</span>
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
              class="mt-1 w-4 h-4 text-[#d9f20b] border-gray-300 rounded focus:ring-[#cddc39]"
            >
            <span class="text-xs font-bold text-gray-800">
              J'accepte les conditions générales mentionnées sur le site de la course.
            </span>
          </label>

          <button
            @click="procederPaiement"
            :disabled="!accepteConditions || isProcessing || panier.length === 0"
            :class="[
              'w-full py-3 rounded-lg font-bold transition-colors shadow-sm flex items-center justify-center gap-2',
              accepteConditions && panier.length > 0 && !isProcessing ? 'bg-[#d9f20b] hover:bg-[#c4da0a] text-[#0e0f54]' : 'bg-gray-200 cursor-not-allowed opacity-70 text-gray-500'
            ]"
          >
            <svg v-if="isProcessing" class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ isProcessing ? 'Traitement en cours...' : 'Payer' }}
          </button>

        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useCartStore } from '../stores/cart';
import inscriptionService from '../services/inscriptionService'; 
import groupeService from '../services/groupeService'; 
import api from '../services/api';

const router = useRouter();
const cartStore = useCartStore();

// Données du panier via le store Pinia
const panier = computed(() => cartStore.inscriptions);

const accepteConditions = ref(false);
const isProcessing = ref(false); 
const fraisServiceFixe = 2.50;

const sousTotal = computed(() => cartStore.cartTotal);

const fraisService = computed(() => {
  return panier.value.length > 0 ? fraisServiceFixe.toFixed(2) : "0.00";
});

const total = computed(() => {
  const tot = panier.value.length > 0 ? sousTotal.value + fraisServiceFixe : 0;
  return tot.toFixed(2);
});

// LIAISON BACKEND (Tâche 3.9) 
const procederPaiement = async () => {
  if (!accepteConditions.value || panier.value.length === 0) return;

  isProcessing.value = true;

  try {
    console.log('Contenu panier:', JSON.stringify(panier.value));
    
    // Enregistrer les inscriptions en backend (challenge / relais)
    const promessesInscriptions = panier.value.map(async (article) => {
      
      let idGroupe = article.id_groupe || null;

      // LOGIQUE RELAIS & ENTREPRISES :
      // On force la création si on a un nom d'équipe OU si on détecte le mot "relais"
      const typeEstRelais = article.type?.id === 'relais' || article.type?.nom?.toLowerCase() === 'relais';
      
      if (!idGroupe && (article.nom_equipe || typeEstRelais) && article.participant && article.participant.length > 1) {
        
        // Si l'user n'a pas mis de nom de groupe --> génération automatique
        const nomEquipeFinal = article.nom_equipe || `Équipe de ${article.participant[0].prenom}`;
        
        // Création du groupe (le backend ajoute le fondateur automatiquement)
        const typeGroupe = typeEstRelais ? 'Relais' : 'Entreprise';
        const groupeReponse = await groupeService.createGroupe({
          nom: nomEquipeFinal,
          type: typeGroupe,
          id_course: article.courseDetails.id
        });
        
        // On récupère l'ID de manière ultra-sécurisée peu importe la forme de la réponse de l'API
        idGroupe = groupeReponse.data?.groupe?.id || groupeReponse.data?.id || groupeReponse.data?.data?.id;

        // Ajout des autres membres au groupe
        if (idGroupe) {
          for (let i = 1; i < article.participant.length; i++) {
            await groupeService.addParticipant(idGroupe, article.participant[i].id);
          }
        } else {
          console.error("Erreur critique : l'API n'a pas retourné l'ID du groupe !", groupeReponse);
        }
      }

      //ENREGISTREMENT DES INSCRIPTIONS POUR TOUS LES PARTICIPANTS DU PANIER
      const promessesParticipants = article.participant.map(p => {
        const donneesAEnvoyer = {
          id_course:            article.courseDetails.id,
          id_participant:       p.id, // ID du participant en cours dans la boucle
          avertissement_valide: accepteConditions.value,
          id_groupe:            idGroupe,
          id_document:          article.documents?.length > 0 ? article.documents[0].id : null,
          code_participant:     article.codeParticipation || null,
        };
        return inscriptionService.createInscription(donneesAEnvoyer);
      });

      
      return Promise.all(promessesParticipants);
    });
    
    
    await Promise.all(promessesInscriptions);
    
    console.log("Id: ", article.ancienneInscriptionId);

    if(article.ancienneInscriptionId)
      await inscriptionService.cancelInscription(article.ancienneInscriptionId)

    // Créer la Gateway Payrexx avec le montant total
    const montantTotal = parseFloat(total.value);
    const gatewayResponse = await api.post('/paiement/gateway', {
      montant: montantTotal,
    });

    const urlPaiement = gatewayResponse.data.url;

    if (!urlPaiement) {
      throw new Error('URL de paiement manquante');
    }

    // Vider le panier et rediriger vers Payrexx
    cartStore.viderPanier();
    window.location.href = urlPaiement;

  } catch (error) {
    console.error('Erreur lors du paiement :', error);
    if (error.response?.data?.message) {
      alert('Erreur : ' + error.response.data.message);
    } else {
      alert('Une erreur inattendue est survenue.');
    }
  } finally {
    isProcessing.value = false;
  }
};
</script>