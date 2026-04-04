<template>
  <div>
    <Title :texte="`Tableau de bord`" />

    <div class="p-6">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <button @click="goToInscriptions" class="bg-white border-2 border-pink-200 rounded-xl py-4 flex flex-col items-center justify-center shadow-sm hover:bg-pink-50 transition-colors">
          <svg class="w-6 h-6 mb-2 text-[#1e1b4b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
          <span class="text-[#1e1b4b] font-medium">Mes inscriptions</span>
        </button>

        <button @click="goToResultats" class="bg-white border border-gray-200 rounded-xl py-4 flex flex-col items-center justify-center shadow-sm hover:bg-gray-50 transition-colors">
          <svg class="w-6 h-6 mb-2 text-[#1e1b4b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v8l9-11h-7z"></path></svg>
          <span class="text-[#1e1b4b] font-medium">Mes résultats</span>
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 flex flex-col">
          <div class="bg-[#cddc39] rounded-t-xl p-3 flex items-center justify-center gap-2">
            <svg class="w-5 h-5 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
            <h3 class="font-medium text-gray-900 text-lg">Liste des évènements</h3>
          </div>

          <div class="bg-white border border-gray-100 border-t-0 rounded-b-xl p-6 shadow-sm flex flex-col h-full">

            <div v-if="chargement" class="text-center py-10 text-body">
              Chargement des évènements...
            </div>

            
            <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
              <MiniatureEvenement :evenements="evenements.slice(0, 3)" />
            </div>

            <div class="mt-auto pt-6 flex justify-end">
              <button @click="goToAllEvenements" class="bg-[#cddc39] hover:bg-[#c0cf33] text-white font-medium px-6 py-2 rounded-lg transition-colors shadow-sm">
                Voir plus
              </button>
            </div>

          </div>
        </div>

        <div class="lg:col-span-1 flex flex-col gap-6">

          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-center mb-6">
              <h3 class="font-normal text-lg text-gray-900">Mes participants</h3>
              <button class="text-[#1e1b4b] hover:bg-gray-100 rounded-full p-1">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path></svg>
              </button>
            </div>
            <ul class="space-y-4">
              <li v-for="participant in participants" :key="participant.id" class="text-sm font-semibold text-gray-800">
    {{ participant.prenom }} {{ participant.nom }}
</li>
            </ul>
          </div>

          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-center mb-6">
          <h3 @click="router.push('/mes-groupes')" 
              class="font-normal text-lg text-gray-900 cursor-pointer hover:text-tertiary-900 transition-colors">
              Mes groupes
          </h3>              
          <div class="relative">
            <button @click="menuGroupesOuvert = !menuGroupesOuvert"
                class="text-[#1e1b4b] hover:bg-gray-100 rounded-full p-1">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                </svg>
            </button>
            <div v-if="menuGroupesOuvert"
                class="absolute right-0 mt-1 w-44 bg-white border border-gray-200 rounded-xl shadow-lg z-10">
                <button @click="router.push('/mes-groupes'); menuGroupesOuvert = false"
                    class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 rounded-xl">
                    Gérer mes groupes
                </button>
            </div>
        </div>
            </div>
             <div v-if="chargementGroupes" class="text-sm text-gray-400 text-center py-2">Chargement...</div>
            <ul v-else class="space-y-3">
              <li v-for="groupe in groupes" :key="groupe.id"
                class="flex items-center justify-between text-sm font-semibold text-gray-800">
                <button @click="ouvrirGestionGroupe(groupe)" 
    class="text-left hover:text-tertiary-900 transition-colors">
    {{ groupe.nom }}
</button>
                <span class="text-xs text-gray-400">{{ groupe.participants?.length ?? 0 }} membres</span>
              </li>
              <li v-if="groupes.length === 0" class="text-sm text-gray-400 italic">Aucun groupe</li>
            </ul>
            
          </div>

        </div>

      </div>
    </div>
    <PopupGestionGroupe
    v-if="groupeSelectionne"
    :groupe="groupeSelectionne"
    :mes-participants="participants"
    @close="groupeSelectionne = null"
    @mis-a-jour="g => { const i = groupes.findIndex(x => x.id === g.id); if(i > -1) groupes.splice(i, 1, g); groupeSelectionne = null; }"
/>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import evenementParticipantService from '../services/evenementParticipantService';
import Title from '../components/Title.vue'; // Assure-toi que le chemin est correct
import MiniatureEvenement from '../components/MiniatureEvenement.vue';
import PopupGestionGroupe from '../components/PopupGestionGroupe.vue';

const router = useRouter();
const evenements = ref([]);
const chargement = ref(true);
const menuGroupesOuvert = ref(false);
const groupeSelectionne = ref(null);

// chargemnt dynamiques des participants
import participantService from '../services/participantService';
import groupeService from '../services/groupeService';
const participants = ref([]);

async function chargerParticipants() {
  try {
    const response = await participantService.getMesParticipants();
    participants.value = response.data;
  } catch (e) {
    console.error('Impossible de charger les participants :', e);
  }
}

const groupes = ref([]);
const chargementGroupes = ref(false);
 
async function chargerGroupes() {
  chargementGroupes.value = true;
  try {
    const response = await groupeService.getGroupes();
    // Garder uniquement Relais et Groupe, pas les challenges
    groupes.value = response.data.filter(g =>
      g.course?.type === 'Relais' || g.course?.type === 'Groupe'
    );
  } catch (e) {
    console.error('Impossible de charger les groupes:', e);
  } finally {
    chargementGroupes.value = false;
  }
}

// CHARGEMENT DES DONNÉES DE L'API
async function chargerEvenements() {
  try {
    const response = await evenementParticipantService.getAllEvenements();
    const data = response.data.data || response.data;

    // MiniatureEvenement attend logo_base64 décodé
    evenements.value = data.map(evt => ({
      ...evt,
      logo_base64: evt.logo_base64 ? atob(evt.logo_base64.split(',')[1]) : null
    }));
  } catch (error) {
    console.error("Erreur lors du chargement des évènements :", error);
  } finally {
    chargement.value = false;
  }
}


function ouvrirGestionGroupe(groupe) {
    groupeSelectionne.value = groupe;
}

// NAVIGATION
function goToListeCourses(idEvenement) {
  router.push({ name: 'ListeCourses', params: { idEvenement } });
}

function goToAllEvenements() {
  router.push('/evenements');
}

function goToInscriptions() {
  router.push('/inscriptions');
}

function goToResultats() {
  router.push('/resultats');
}

onMounted(() => {
  chargerParticipants();
  chargerEvenements();
  chargerGroupes();
});
</script>
