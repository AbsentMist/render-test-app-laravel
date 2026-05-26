<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 flex flex-col overflow-hidden" style="height: auto; max-height: 90vh">
      
      <!-- Header -->
      <div class="flex items-center justify-between px-6 pt-5 pb-2 border-b border-gray-100 bg-primary-300">
        <div>
          <span class="px-6 text-subtitle font-medium text-secondary">Invitation</span>
          <span class="text-subtitle font-medium text-secondary"> {{ invitation.course.nom}}</span>
          <div
            v-if="invitation.course?.evenement"
            class="h-1 w-24 ml-6 rounded-r-full mb-2"
            :style="{ backgroundColor: invitation.course.evenement.couleur_secondaire }"
          ></div>
          <span
            v-if="invitation.course?.evenement"
            class="mx-6 px-2 py-0.5 text-base font-medium text-secondary rounded-full"
            :style="{
              color: invitation.course.evenement.couleur_secondaire,
              backgroundColor: invitation.course.evenement.couleur_primaire,
              borderColor: invitation.course.evenement.couleur_secondaire,
            }"
          >
            {{ invitation.course.evenement.nom }}
          </span>
          <span class="text-secondary align-middle ml-6">
              <Icon icon="mdi:account-group" class="w-4 h-4 inline-block mr-1 text-secondary" />
                {{ invitation.nom }}
          </span>
        </div>
        <button
          @click="emit('close')"
          class="text-secondary hover:text-gray-600 transition-colors mr-1"
        >
          <Icon icon="mdi:close" class="w-5 h-5" />
        </button>
      </div>

      <!-- Contenu -->
      <div class="flex-1 overflow-y-auto px-10 py-6">
        <!-- Si pas de questions -->
        <div v-if="!aDesQuestions" class="text-center py-8">
          <Icon icon="lucide:check-circle" class="w-12 h-12 text-green-500 mx-auto mb-4" />
          <p class="text-lg font-semibold text-gray-800 mb-2">Pas de questionnaire</p>
          <p class="text-sm text-gray-500">
            Il n'y a pas de questionnaire à compléter pour cette course.
          </p>
        </div>

        <!-- Questionnaire -->
        <div v-else class="flex flex-col gap-6">
          <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-1">Questionnaire</h2>
            <p class="text-sm text-gray-500">
              Veuillez répondre aux questions ci-dessous avant d'accepter votre invitation.
            </p>
          </div>

          <EtapeQuestionnaire
            :questions="questionnaire"
            v-model="reponses"
          />
        </div>

        <!-- Messages d'erreur -->
        <div
          v-if="erreur"
          class="mt-6 flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3"
        >
          <Icon icon="mdi:alert-circle-outline" class="w-5 h-5 shrink-0" />
          <span>{{ erreur }}</span>
          <button
            type="button"
            @click="erreur = null"
            class="ml-auto text-red-400 hover:text-red-600"
          >
            <Icon icon="mdi:close" class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex justify-between items-center px-6 py-4 border-t border-gray-100">
        <button
          @click="emit('close')"
          class="btn-accent-300"
        >
          Annuler
        </button>

        <button
          @click="accepterInvitation"
          :disabled="chargement"
          :class="[
            'btn-tertiary',
            chargement ? 'opacity-50 cursor-not-allowed' : '',
          ]"
        >
          <span v-if="chargement" class="flex items-center gap-2">
            <svg
              class="animate-spin h-4 w-4"
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
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
              ></path>
            </svg>
            Acceptation en cours...
          </span>
          <span v-else>Accepter l'invitation</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
/**
 * @fileoverview Composant PopupAccepterInvitationCourse.
 * @description Popup pour accepter une invitation de groupe avec remplissage du questionnaire de la course.
 * @remarks Permet à l'utilisateur de répondre aux questions avant d'accepter son invitation à un groupe.
 */
import { ref, computed } from 'vue';
import { Icon } from '@iconify/vue';
import EtapeQuestionnaire from './EtapeQuestionnaire.vue';
import groupeService from '../services/groupeService';

const props = defineProps({
  invitation: {
    type: Object,
    required: true,
    description: 'Objet invitation contenant { id, nom, course, ... }'
  }
});

const emit = defineEmits(['close', 'accepte']);

const reponses = ref({});
const chargement = ref(false);
const erreur = ref(null);

/**
 * Calcul du questionnaire de la course associée à l'invitation.
 * @author Neris Alessandro
 * @returns {Array}
 */
const questionnaire = computed(() => {
  return props.invitation?.course?.questionnaire || [];
});

/**
 * Indique s'il y a des questions à répondre.
 * @author Neris Alessandro
 * @returns {boolean}
 */
const aDesQuestions = computed(() => {
  return questionnaire.value && questionnaire.value.length > 0;
});

/**
 * Formate les réponses pour l'API (structure compatible avec ReponseQuestion).
 * @author Neris Alessandro
 * @returns {Array}
 */
const reponsesPourApi = computed(() => {
  return Object.entries(reponses.value).map(([id_question, valeur]) => ({
    id_question: parseInt(id_question),
    id_option_choisie: valeur?.reponse?.id ?? null,
  }));
});

/**
 * Accepte l'invitation avec les réponses au questionnaire.
 * @author Ngoie Steven, Neris Alessandro
 * @returns {Promise<void>}
 */
const accepterInvitation = async () => {
  erreur.value = null;
  chargement.value = true;

  try {
    const payload = aDesQuestions.value ? { reponses: reponsesPourApi.value } : {};
    
    await groupeService.accepterInvitation(props.invitation.id, payload);
    
    emit('accepte', {
      idGroupe: props.invitation.id,
      reponses: reponsesPourApi.value
    });
    
    emit('close');
  } catch (e) {
    console.error('Erreur lors de l\'acceptation de l\'invitation', e);
    erreur.value = e.response?.data?.message || 'Une erreur est survenue lors de l\'acceptation de l\'invitation.';
  } finally {
    chargement.value = false;
  }
};
</script>
