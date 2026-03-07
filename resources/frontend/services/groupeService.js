import api from './api'; // 👈 On importe ton instance Axios configurée (qui gère le token 401 !)

export default {
  // ==========================================
  // CRUD CLASSIQUE (GROUPES)
  // ==========================================

  /**
   * Récupère la liste de tous les groupes liés au participant connecté.
   */
  getGroupes() {
    return api.get('/participant/groupes');
  },

  /**
   * Crée un nouveau groupe (le créateur devient automatiquement 'fondateur').
   * Le code entreprise est généré automatiquement par le backend si le type est 'Entreprise'.
   * @param {Object} data - { nom: 'Team Rocket', type: 'Entreprise' }
   */
  createGroupe(data) {
    return api.post('/participant/groupes', data);
  },

  /**
   * Récupère les informations d'un groupe spécifique et la liste de ses membres.
   * @param {Number} id - L'ID du groupe
   */
  getGroupe(id) {
    return api.get(`/participant/groupes/${id}`);
  },

  /**
   * Modifie les informations d'un groupe (réservé au fondateur).
   * @param {Number} id - L'ID du groupe
   * @param {Object} data - Les nouvelles données { nom: 'Nouveau nom' }
   */
  updateGroupe(id, data) {
    return api.put(`/participant/groupes/${id}`, data);
  },

  /**
   * Supprime un groupe définitivement (réservé au fondateur).
   * @param {Number} id - L'ID du groupe
   */
  deleteGroupe(id) {
    return api.delete(`/participant/groupes/${id}`);
  },

  // ==========================================
  // GESTION DES MEMBRES (INVITATIONS)
  // ==========================================

  /**
   * Ajoute (invite) un participant existant dans le groupe.
   * @param {Number} idGroupe - L'ID du groupe
   * @param {Number} idParticipant - L'ID du participant à inviter
   */
  addParticipant(idGroupe, idParticipant) {
    return api.post(`/participant/groupes/${idGroupe}/participants`, { 
        id_participant: idParticipant 
    });
  },

  /**
   * Retire un participant du groupe.
   * @param {Number} idGroupe - L'ID du groupe
   * @param {Number} idParticipant - L'ID du participant à retirer
   */
  removeParticipant(idGroupe, idParticipant) {
    return api.delete(`/participant/groupes/${idGroupe}/participants/${idParticipant}`);
  },

  // ==========================================
  // VÉRIFICATION CODE (PANIER / INSCRIPTION)
  // ==========================================

  /**
   * Vérifie si un code entreprise est valide et si l'utilisateur fait bien partie du groupe.
   * À utiliser au moment de valider le panier d'inscription.
   * @param {String} code - Le code à vérifier (ex: 'E-A1B2C3D')
   */
  verifierCode(code) {
    return api.post('/participant/groupes/verifier-code', { 
        code: code 
    });
  }
};