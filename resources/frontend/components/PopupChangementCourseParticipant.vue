<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-7xl mx-4 flex flex-col overflow-hidden" style="height: 90vh">
      
      <div class="flex items-center justify-between px-6 pt-5 pb-2 bg-primary-300">
            <div>
                <span class="px-6 text-subtitle font-medium text-secondary">Changement de course</span>
                <div class="h-1 w-24 ml-6 rounded-r-full mb-2 bg-accent-600"></div>
            </div>
            <button @click="$emit('close')" class="text-secondary hover:text-gray-600 transition-colors mr-1">
                <Icon icon="mdi:close" class="w-5 h-5" />
            </button>
        </div>
    
    <div class="px-8 py-6 flex flex-col gap-4" :style="{backgroundColor: inscription.course.evenement.couleur_secondaire + '26'}">
    <div class="flex items-center gap-2">
        <div class="bg-primary-900 p-1.5 rounded-lg">
            <Icon icon="mdi:ticket-confirmation" class="w-4 h-4 text-accent-600" />
        </div>
        <span class="text-body text-primary font-bold uppercase">Inscription actuelle</span>
    </div>

    <div class="flex flex-row items-center bg-white rounded-2xl border border-gray-200 shadow-sm py-2 gap-8 overflow-hidden">
    <div class="flex items-center gap-5 border-r border-gray-100 pr-8 min-w-[280px] -mx-4 -my-4 px-4 py-8 mr-0 rounded-l-2xl"
    :style="{backgroundColor: inscription.course.evenement.couleur_primaire}">
        <div class="rounded-xl p-2 shrink-0">
            <img :src="logoPreview" v-if="inscription.course.evenement.logo_base64"
                class="h-12 w-24 object-contain"
                alt="Logo évènement"
            />
        </div>
        <div class="flex flex-col" :style="{color: inscription.course.evenement.couleur_secondaire}">
            <span class="text-lg font-black leading-tight">{{ inscription.course.nom }}</span>
            <span class="text-xs font-bold uppercase tracking-tight">{{ inscription.course.evenement.nom }}</span>
        </div>
    </div>

        <div class="flex flex-col items-center border-r border-gray-100 px-8">
            <div class="flex items-center gap-1.5 text-gray-400 mb-1">
                <Icon icon="mdi:calendar-month" class="w-6 h-6" :style="{color: inscription.course.evenement.couleur_primaire}"/>
            </div>
            <div class="flex flex-col items-center">
                <span v-if="inscription.course.date_debut != inscription.course.date_fin" class="text-sm font-bold text-gray-700 text-center leading-tight">
                    Du {{ inscription.course.date_debut }}<br>au {{ inscription.course.date_fin }}
                </span>
                <span v-else class="text-sm font-bold text-gray-700 leading-tight">
                    {{ inscription.course.date_debut }}
                </span>
            </div>
        </div>

        <div class="flex flex-col items-center border-r border-gray-100 flex-1">
          <span v-if="inscription.groupe" class="-mt-2 self-start text-label">
            <b>Groupe:</b> {{ inscription.groupe.nom }}
          </span>
          <div class="flex flex-col items-center">
            <div class="flex flex-col items-center gap-1.5">
                <Icon icon="mdi:account-circle" class="w-6 h-6" :style="{color: inscription.course.evenement.couleur_primaire}"/>
                <span class="text-sm font-bold text-gray-700 leading-tight">{{ inscription.participant.prenom }} {{ inscription.participant.nom }}</span>
            </div>
          </div>
        </div>

        <div class="flex flex-col items-end pr-6 min-w-[150px]">
            <span class="text-label font-bold mb-1" :style="{color: inscription.course.evenement.couleur_primaire}">Total payé </span>
            <div class="flex items-baseline gap-1 text-green-600">
                <span class="text-2xl font-black text-green-600">- {{ inscription.tarif }}</span>
                <span class="text-xs font-bold text-green-600">CHF</span>
            </div>
            <span class="text-[10px] text-gray-400 font-medium italic mt-1 text-right leading-tight">Ce montant sera déduit<br>du total dans le panier.</span>
        </div>
    </div>

    <div class="flex justify-center -mb-9 z-10" :style="{color: inscription.course.evenement.couleur_primaire}">
        <div class="bg-white border border-gray-200 shadow-md rounded-full p-2">
            <Icon icon="mdi:swap-vertical" class="w-6 h-6" />
        </div>
    </div>
</div>
    
    <div class="flex items-center gap-2 px-6 mt-1">
      <span v-if="etape >= ETAPES.COURSE" 
            :class="etape > ETAPES.COURSE ? 'cursor-pointer underline' : 'font-semibold'"
            @click="etape > ETAPES.COURSE && retourCourses()">
        {{ inscription.course.evenement?.nom }}
      </span>
      <span v-if="etape >= ETAPES.INSCRIPTION">›</span>
      <span v-if="etape >= ETAPES.INSCRIPTION" class="font-semibold">
        {{ nouvelleInscription.course?.nom_course }}
      </span>
    </div>
    
      <div class="flex-1 overflow-y-auto">
        <ChangementCourse
          v-if="etape === ETAPES.COURSE"
          :evenement="inscription.course.evenement"
          @selectionner="choisirCourse"
        />

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
/**
 * @fileoverview Composant PopupChangementCourseParticipant.
 * @description Modale permettant à un participant de demander un changement de course via le panier.
 * @remarks Conserve l'historique de l'ancienne inscription pour permettre le recalcul du montant dans le panier.
 */
import { Icon } from '@iconify/vue';
import ChangementCourse from './ChangementCourse.vue';
import ChangementEvenement from './ChangementEvenement.vue';
import PopupInscriptionCourse from './PopupInscriptionCourse.vue';
import PopupConfirmation from './PopupConfirmation.vue';
import inscriptionService from '../services/inscriptionService';
import { useCartStore } from '../stores/cart';
const ETAPES = { COURSE: 1, INSCRIPTION: 2 };

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
      etape: ETAPES.COURSE,
      nouvelleInscription: {
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
    /**
     * Sélectionne la nouvelle course et passe à l'étape d'inscription.
     * @param {object} course
     * @returns {void}
     */
    choisirCourse(course) {
      this.nouvelleInscription.course = course;
      this.etape = ETAPES.INSCRIPTION;
    },
    /**
     * Revient à la liste des courses disponibles.
     * @returns {void}
     */
    retourCourses() {
      this.nouvelleInscription.course = null;
      this.etape = ETAPES.COURSE;
    },
    /**
     * Ouvre la confirmation finale avec les données du changement préparé.
     * @param {object} nouvelleInscription
     * @returns {void}
     */
    confirmerChangement(nouvelleInscription) {
      this.nouvelleInscriptionData = nouvelleInscription;
      this.confirmation = true;
    },
    /**
     * Recolore un logo avec la couleur secondaire de l'évènement.
     * @param {string} logoSrc
     * @param {string} couleur
     * @returns {Promise<string>}
     */
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
    /**
     * Valide le changement de course et l'ajoute au panier avec historique.
     * @returns {Promise<void>}
     */
    async confirmPopup() {
      const memeCourse    = this.nouvelleInscription.course?.id    === this.inscription.course.id;

      if (memeCourse) {
        this.confirmation = false;
        this.messageConfirmation = "Vous ne pouvez pas sélectionner la même course!"  
        this.dataInserted = true;
        setTimeout(() => {
          this.dataInserted = false;
          }, 2000);  
        return;
      }      
      try {
        const inscriptionAvecHistorique = {
          ...this.nouvelleInscriptionData, 
          ancienneInscriptionId: this.inscription.id
        };

        this.cartStore.ajouterInscription(inscriptionAvecHistorique, this.nouvelleInscription.course);

        this.messageConfirmation = 'Changement pris en compte ! Veuillez valider votre panier.';

        this.confirmation = false;
        this.dataInserted = true;
        setTimeout(() => {
            this.$emit('close');
        }, 1500); 

      } catch (error) {
          console.error('Erreur lors de la mise au panier du changement :', error);
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