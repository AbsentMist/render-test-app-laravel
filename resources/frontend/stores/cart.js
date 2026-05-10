import { defineStore } from 'pinia';
import api from '../services/api';

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

      // Charger les inscriptions depuis le localStorage
      const depuisStorage = loadCartFromStorage(nextStorageKey);

      // Si des inscriptions étaient en mémoire sans clé (ajoutées avant fetchUser),
      // les fusionner avec celles du localStorage pour ne rien perdre
      if (this.inscriptions.length > 0 && depuisStorage.length === 0) {
        this.inscriptions = this.inscriptions;
        this.sauvegarderPanier();
      } else {
        this.inscriptions = depuisStorage;
      }
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
    
    async supprimerInscription(index, idGroupe = null) {
      // Supprimer le groupe en base si un id_groupe est fourni
      // et qu'aucun autre article du panier ne le partage
      if (idGroupe) {
        const autresUtilisent = this.inscriptions.some(
          (insc, i) => i !== index && insc.id_groupe === idGroupe
        );
        if (!autresUtilisent) {
          try {
            console.log('[Cart] Suppression groupe orphelin id:', idGroupe);
            await api.delete(`/participant/groupes/${idGroupe}`);
            console.log('[Cart] Groupe supprimé avec succès');
          } catch (e) {
            console.warn('[Cart] Impossible de supprimer le groupe :', e);
          }
        }
      }

      this.inscriptions.splice(index, 1);
      this.sauvegarderPanier();
    },

    toggleDropdown() {
      this.isDropdownOpen = !this.isDropdownOpen;
    },

    fermerDropdown() {
      this.isDropdownOpen = false;
    },

    async viderPanier() {
      // Collecter tous les id_groupe uniques à supprimer
      const idsGroupes = [
        ...new Set(
          this.inscriptions
            .filter(i => i.id_groupe)
            .map(i => i.id_groupe)
        )
      ];

      for (const id of idsGroupes) {
        try {
          console.log('[Cart] Suppression groupe orphelin id:', id);
          await api.delete(`/participant/groupes/${id}`);
        } catch (e) {
          console.warn(`[Cart] Impossible de supprimer le groupe ${id} :`, e);
        }
      }

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