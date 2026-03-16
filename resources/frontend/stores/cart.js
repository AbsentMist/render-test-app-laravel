import { defineStore } from 'pinia';

export const useCartStore = defineStore('cart', {
  state: () => ({
    inscriptions: JSON.parse(localStorage.getItem('running_cart')) || [],
    isDropdownOpen: false, 
  }),

  getters: {
    cartCount: (state) => state.inscriptions.length,
    cartTotal: (state) => state.inscriptions.reduce((total, item) => total + parseFloat(item.tarif || 0), 0)
  },

  actions: {
    //Récupération des informations de la course pour les afficher dans le panier
    ajouterInscription(donneesInscription, courseDetails) {
      this.inscriptions.push({ 
        ...donneesInscription, 
        courseDetails: courseDetails 
      });
      this.sauvegarderPanier();
      
      // Ouverture automatique de la liste déroulante lors de l'ajout d'une inscription
      this.isDropdownOpen = true;
      
      // Ferme automatiquement après 5 secondes
      setTimeout(() => {
        this.isDropdownOpen = false;
      }, 5000);
    },
    
    supprimerInscription(index) {
      this.inscriptions.splice(index, 1);
      this.sauvegarderPanier();
    },

    toggleDropdown() {
      this.isDropdownOpen = !this.isDropdownOpen;
    },

    fermerDropdown() {
      this.isDropdownOpen = false;
    },

    viderPanier() {
      this.inscriptions = [];
      this.sauvegarderPanier();
    },

    sauvegarderPanier() {
      localStorage.setItem('running_cart', JSON.stringify(this.inscriptions));
    }
  }
});