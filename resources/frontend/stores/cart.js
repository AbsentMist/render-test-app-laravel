import { defineStore } from 'pinia';

function getCartStorageKey(ownerId) {
  return ownerId ? `running_cart_user_${ownerId}` : null;
}

function loadCartFromStorage(storageKey) {
  if (!storageKey) {
    return [];
  }

  try {
    const raw = localStorage.getItem(storageKey);
    const parsed = raw ? JSON.parse(raw) : [];

    return Array.isArray(parsed) ? parsed : [];
  } catch (error) {
    console.error('Erreur lors du chargement du panier', error);
    return [];
  }
}

export const useCartStore = defineStore('cart', {
  state: () => ({
    inscriptions: [],
    isDropdownOpen: false,
    currentOwnerId: null,
    storageKey: null,
  }),

  getters: {
    cartCount: (state) => state.inscriptions.length,
    cartTotal: (state) => state.inscriptions.reduce((total, item) => total + parseFloat(item.tarif || 0), 0)
  },

  actions: {
    setOwner(ownerId = null) {
      const nextStorageKey = getCartStorageKey(ownerId);

      if (this.storageKey && this.storageKey !== nextStorageKey) {
        localStorage.setItem(
          this.storageKey,
          JSON.stringify(this.inscriptions),
        );
      }

      this.currentOwnerId = ownerId;
      this.storageKey = nextStorageKey;
      this.inscriptions = loadCartFromStorage(nextStorageKey);
    },

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
      if (!this.storageKey) {
        return;
      }

      localStorage.setItem(this.storageKey, JSON.stringify(this.inscriptions));
    }
  }
});