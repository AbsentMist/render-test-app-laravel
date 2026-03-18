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
            <div class="flex flex-row gap-8 items-center justify-center">
              <div class="flex items-center gap-4">
                <img :src="logoPreview" v-if="inscription.course.evenement.logo_base64"
                  class="max-h-24 max-w-48 object-contain"
                  alt="Logo évènement"
                />
                <div class="flex flex-col">
                    <span>{{ inscription.course.nom }}</span>
                    <span>{{ inscription.course.evenement.nom }}</span>
                </div>
              </div>
                <div v-if="inscription.course.date_debut != inscription.course.date_fin">
                  <div class="flex flex-col items-center">
                    <span class="">Date début</span>
                    <span>{{ inscription.course.date_debut }}</span>
                  </div>
                  <div class="flex flex-col items-center">
                    <span>Date fin</span>
                    <span>{{ inscription.course.date_fin }}</span>
                  </div>
                </div>
                <div v-else class="flex flex-col items-center">
                    <Icon icon="mdi:calendar" class="w-6 h-6"/>
                    <span>{{ inscription.course.date_debut }}</span>
                </div>
                <span v-if="inscription.groupe">{{ inscription.groupe }}</span>
                <div class="flex flex-col items-center">
                  <Icon icon="mdi:person" class="w-6 h-6"/>
                  <span>{{ inscription.participant.nom }} {{ inscription.participant.prenom }}</span>
                </div>
                <span>{{ inscription.tarif }} Chf</span>
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
        {{ nouvelleInscription.evenement?.nom }}
      </span>
      <span v-if="etape >= ETAPES.INSCRIPTION">›</span>
      <span v-if="etape >= ETAPES.INSCRIPTION" class="font-semibold">
        {{ nouvelleInscription.course?.nom_course }}
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
          :evenement="nouvelleInscription.evenement"
          @selectionner="choisirCourse"
        />

        <!-- Étape 3 : Formulaire inscription (réutilise ta popup existante en mode inline) -->
                
        <PopupInscriptionCourse
        v-else-if="etape === ETAPES.INSCRIPTION"
        :course="nouvelleInscription.course"
        :participants="participants"
        :inline="true"
        @close="retourCourses"
        @ajouter-panier="confirmerChangement"
        />

      </div>
      <PopupConfirmation v-if="confirmation" message="Etes-vous sûr de vouloir changer votre course?" @confirm="confirmPopup"/>
      <PopupConfirmation v-if="dataInserted" :showButtons="false" :message="messageConfirmation"/>
    </div>
  </div>
</template>

<script>
import { Icon } from '@iconify/vue';
import ChangementCourse from './ChangementCourse.vue';
import ChangementEvenement from './ChangementEvenement.vue';
import PopupInscriptionCourse from './PopupInscriptionCourse.vue';
import PopupConfirmation from './PopupConfirmation.vue';
import inscriptionService from '../services/inscriptionService';
import { useCartStore } from '../stores/cart';
const ETAPES = { EVENEMENT: 1, COURSE: 2, INSCRIPTION: 3 };

export default {
  name: 'PopupChangementCourse',
  components: { 
    Icon, 
    ChangementEvenement, 
    ChangementCourse, 
    PopupInscriptionCourse,
    PopupConfirmation
  },
  props: {
    inscription: { required: true },
    participants: { type: Array, default: () => [] },
  },
  emits: ['close', 'confirmer'],
  data() {
    return {
      ETAPES,
      etape: ETAPES.EVENEMENT,
      nouvelleInscription: {
        evenement: null,
        course: null,
      },
      logoPreview: null,
      confirmation: false,
      dataInserted: false,
      nouvelleInscriptionData: null,
      messageConfirmation: '',
      cartStore: null,
    };
  },
  methods: {
    choisirEvenement(evenement) {
      this.nouvelleInscription.evenement = evenement;
      this.etape = ETAPES.COURSE;
    },
    choisirCourse(course) {
      this.nouvelleInscription.course = course;
      this.etape = ETAPES.INSCRIPTION;
    },
    retourEvenements() {
      this.nouvelleInscription.evenement = null;
      this.nouvelleInscription.course = null;
      this.etape = ETAPES.EVENEMENT;
    },
    retourCourses() {
      this.nouvelleInscription.course = null;
      this.etape = ETAPES.COURSE;
    },
    confirmerChangement(nouvelleInscription) {
      this.nouvelleInscriptionData = nouvelleInscription;
      this.confirmation = true;
      console.log(nouvelleInscription)
    },
    async coloriserLogo(logoSrc, couleur) {
      return new Promise((resolve) => {
        const img = new Image();
        img.onload = () => {
          const canvas = document.createElement('canvas');
          canvas.width = img.width;
          canvas.height = img.height;
          const ctx = canvas.getContext('2d');
          ctx.drawImage(img, 0, 0);
          ctx.globalCompositeOperation = 'source-atop';
          ctx.fillStyle = couleur;
          ctx.fillRect(0, 0, canvas.width, canvas.height);
          resolve(canvas.toDataURL());
        };
        img.src = logoSrc;
      });
    },
    async confirmPopup() {
      const memeEvenement = this.nouvelleInscription.evenement?.id === this.inscription.course.evenement.id;
      const memeCourse    = this.nouvelleInscription.course?.id    === this.inscription.course.id;

      if (memeEvenement && memeCourse) {
        this.confirmation = false;
        this.messageConfirmation = "Vous ne pouvez pas sélectionner la même course!"  
        this.dataInserted = true;
        setTimeout(() => {
          this.dataInserted = false;
          }, 2000);  
        return;
      }      
      try {
        if(this.nouvelleInscriptionData.tarif <= this.inscription.tarif){
          // 1. Créer la nouvelle inscription
          await inscriptionService.createInscription({
              id_course:            this.nouvelleInscription.course.id,
              id_participant:       this.nouvelleInscriptionData.participant[0].id,
              tarif:                this.nouvelleInscriptionData.tarif,
              status_paiement:      'Validé',
              montant_rabais:       0,
              avertissement_valide: this.nouvelleInscription.course.avertissement ? true : false,
              id_groupe:            null,
              id_document:          this.nouvelleInscriptionData.documents[0]?.id ?? null,
              code_participant:     this.nouvelleInscriptionData.codeParticipation || null,
          });

          // 2. Annuler l'ancienne inscription
          await inscriptionService.cancelInscription(this.inscription.id);
          this.messageConfirmation = 'Votre inscription a été confirmée ! L\'ancienne course est annulée.';
        }
        else if(this.nouvelleInscriptionData.tarif > this.inscription.tarif){
          this.cartStore.ajouterInscription(this.nouvelleInscriptionData, this.nouvelleInscription.course);
          this.messageConfirmation = 'La nouvelle course a été ajoutée au panier.';
        }
          // 3. Afficher confirmation et recharger
          this.confirmation = false;
          this.dataInserted = true;
          setTimeout(() => {
              window.location.reload();
          }, 2000);

      } catch (error) {
          console.error('Erreur lors du changement de course :', error);
      }
    },
  },
  async mounted() {
    this.cartStore =  useCartStore();
    const logo = this.inscription.course.evenement.logo_base64;
    if (logo) {
      const src = logo.startsWith('data:') ? logo : `data:image/png;base64,${logo}`;
      this.logoPreview = await this.coloriserLogo(src, this.inscription.course.evenement.couleur_secondaire);
    }
}
};
</script>