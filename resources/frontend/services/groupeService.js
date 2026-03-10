import api from './api'; // 👈 On importe ton instance Axios configurée (qui gère le token 401 !)

export default {

  getGroupes() {
    return api.get('/participant/groupes');
  },

  createGroupe(data) {
    return api.post('/participant/groupes', data);
  },

  getGroupe(id) {
    return api.get(`/participant/groupes/${id}`);
  },

  updateGroupe(id, data) {
    return api.put(`/participant/groupes/${id}`, data);
  },

  deleteGroupe(id) {
    return api.delete(`/participant/groupes/${id}`);
  },

  addParticipant(idGroupe, idParticipant) {
    return api.post(`/participant/groupes/${idGroupe}/participants`, { 
        id_participant: idParticipant 
    });
  },

  removeParticipant(idGroupe, idParticipant) {
    return api.delete(`/participant/groupes/${idGroupe}/participants/${idParticipant}`);
  },

  verifierCode(code) {
    return api.post('/participant/groupes/verifier-code', { 
        code: code 
    });
  }
};