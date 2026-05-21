import api from './api';

export default {
  /**
   * Vérifie si le participant connecté a accès au module membership
   */
  accesMembershipParticipant() {
    return api.get('/participant/membership/acces');
  },

  /**
   * Soumet un formulaire membership (participant invité)
   */
  soumettreDemande(data) {
    return api.post('/participant/membership/demander', data);
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
   * Recherche un participant existant via email (admin)
   */
  rechercherParticipantParEmail(email) {
    return api.get('/organisateur/membership/participants/rechercher', {
      params: { email },
    });
  },

  /**
   * Crée une invitation membership pour un participant (admin)
   */
  inviterParticipant(payload) {
    return api.post('/organisateur/membership/invitations', payload);
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
   * Remet une demande à compléter (admin)
   */
  remettreACompleterDemande(id, commentaire_admin) {
    return api.post(`/organisateur/membership/demandes/${id}/a-completer`, { commentaire_admin });
  },

  /**
   * Annule une invitation membership (admin)
   */
  annulerInvitation(id, commentaire_annulation = '') {
    return api.post(`/organisateur/membership/invitations/${id}/annuler`, {
      commentaire_annulation,
    });
  },

  /**
   * Finalise le membership au moment du paiement (création du formulaire + attribution rôle)
   */
  checkoutMembership(payload) {
    return api.post('/participant/membership/checkout', payload);
  },
};
