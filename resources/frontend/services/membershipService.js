import api from './api';

export default {
  /**
   * Soumet une demande de membership
   */
  soumettreDemande(data) {
    return api.post('/membership/demander', data);
  },

  /**
   * Récupère la dernière demande du participant connecté
   */
  maDemande() {
    return api.get('/participant/membership/ma-demande');
  },

  /**
   * Récupère le profil participant connecté
   */
  profilParticipant() {
    return api.get('/participant/profil');
  },

  /**
   * Vérifie qu'un utilisateur existant possède cet email
   */
  verifierEmail(email) {
    return api.get('/participant/membership/verifier-email', {
      params: { email },
    });
  },

  /**
   * Récupère toutes les demandes (admin)
   */
  listerDemandes() {
    return api.get('/organisateur/membership/demandes');
  },

  /**
   * Récupère les demandes en attente (admin)
   */
  listerDemandesEnAttente() {
    return api.get('/organisateur/membership/demandes/en-attente');
  },

  /**
   * Approuve une demande (admin)
   */
  approuverDemande(id) {
    return api.post(`/organisateur/membership/demandes/${id}/approuver`);
  },

  /**
   * Refuse une demande (admin)
   */
  refuserDemande(id, raison) {
    return api.post(`/organisateur/membership/demandes/${id}/refuser`, { raison });
  },
};
