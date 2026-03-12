<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-7xl mx-4 flex flex-col overflow-hidden" style="height: 90vh">
      
      <!-- Header -->
        <div class="flex items-center justify-between px-6 pt-5 pb-2 border-b border-gray-100 bg-primary-300">
            <div>
                <span class="px-6 text-subtitle font-medium text-secondary">Changement de course</span>
                <!-- Bandeau inscription actuelle -->
                <div class="h-1 w-24 ml-6 rounded-r-full mb-2 bg-accent-600"></div>
            </div>
            <button @click="$emit('close')" class="text-secondary hover:text-gray-600 transition-colors mr-1">
                <Icon icon="mdi:close" class="w-5 h-5" />
            </button>
        </div>
    
    <!-- Inscription actuelle -->
    <div class="px-6 py-3 border-b border-gray-300 flex gap-4 flex-col">
        <div>
            <span class="font-semibold">Course actuel</span>
            <div class="flex flex-row gap-4 items-center justify-center">
                <div class="flex flex-col">
                    <span>{{ inscription.course.nom }}</span>
                    <span>{{ inscription.course.evenement.nom }}</span>
                </div>
                <span>{{ inscription.course.date_debut }}</span>
                <span>{{ inscription.course.date_fin }}</span>
                <span>{{ inscription.tarif }}</span>
                <span>{{ inscription.groupe }}</span>
                <div class="flex flex-col items-center">
                    <Icon icon="mdi:person" class="w-5 h-5"/>
                    <span>{{ inscription.participant.nom }} {{ inscription.participant.prenom }}</span>
                </div>
            </div>
        </div>
        <div class="flex justify-center items-center">
            <Icon icon="mdi:swap-horizontal" class="w-8 h-8 text-gray-400 shrink-0" />
        </div>
    </div>
    
    <!-- Fil d'Ariane-->
    <div class="flex items-center gap-2 px-6 mt-1">
      <span 
      :class="[
          etape <= ETAPES.EVENEMENT ? 'font-semibold' : '',
          etape > ETAPES.EVENEMENT ? 'cursor-pointer underline' : ''
      ]"
      @click="etape > ETAPES.EVENEMENT && retourEvenements()"
      >
      Évènement
      </span>
      <span v-if="etape >= ETAPES.COURSE">›</span>
      <span v-if="etape >= ETAPES.COURSE" 
            :class="etape > ETAPES.COURSE ? 'cursor-pointer underline' : 'font-semibold'"
            @click="etape > ETAPES.COURSE && retourCourses()">
        {{ evenementSelectionne?.nom }}
      </span>
      <span v-if="etape >= ETAPES.INSCRIPTION">›</span>
      <span v-if="etape >= ETAPES.INSCRIPTION" class="font-semibold">
        {{ courseSelectionnee?.nom_course }}
      </span>
    </div>
    
      <!-- Corps scrollable -->
      <div class="flex-1 overflow-y-auto">

        <!-- Étape 1 : Sélection évènement -->
        <ChangementEvenement
          v-if="etape === ETAPES.EVENEMENT"
          @selectionner="choisirEvenement"
        />

        <!-- Étape 2 : Sélection course -->
        <ChangementCourse
          v-else-if="etape === ETAPES.COURSE"
          :evenement="evenementSelectionne"
          @selectionner="choisirCourse"
        />

        <!-- Étape 3 : Formulaire inscription (réutilise ta popup existante en mode inline) -->
                
        <PopupInscriptionCourse
        v-else-if="etape === ETAPES.INSCRIPTION"
        :course="courseSelectionnee"
        :participants="participants"
        :inline="true"
        @close="retourCourses"
        @ajouter-panier="confirmerChangement"
        />

      </div>
    </div>
  </div>
</template>

<script>
import { Icon } from '@iconify/vue';
import ChangementCourse from './ChangementCourse.vue';
import ChangementEvenement from './ChangementEvenement.vue';
import PopupInscriptionCourse from './PopupInscriptionCourse.vue';

const ETAPES = { EVENEMENT: 1, COURSE: 2, INSCRIPTION: 3 };

export default {
  name: 'PopupChangementCourse',
  components: { Icon, ChangementEvenement, ChangementCourse, PopupInscriptionCourse },
  props: {
    inscription: { required: true },
    participants: { type: Array, default: () => [] },
  },
  emits: ['close', 'confirmer'],
  data() {
    return {
      ETAPES,
      etape: ETAPES.EVENEMENT,
      evenementSelectionne: null,
      courseSelectionnee: null,
    };
  },
  methods: {
    choisirEvenement(evenement) {
      this.evenementSelectionne = evenement;
      this.etape = ETAPES.COURSE;
    },
    choisirCourse(course) {
      this.courseSelectionnee = course;
      this.etape = ETAPES.INSCRIPTION;
    },
    retourEvenements() {
      this.evenementSelectionne = null;
      this.courseSelectionnee = null;
      this.etape = ETAPES.EVENEMENT;
    },
    retourCourses() {
      this.courseSelectionnee = null;
      this.etape = ETAPES.COURSE;
    },
    confirmerChangement(nouvelleInscription) {
      this.$emit('confirmer', {
        inscriptionOriginale: this.inscription,
        nouvelleCourse: this.courseSelectionnee,
        nouvelleInscription,
      });
    },
  },
};
</script>