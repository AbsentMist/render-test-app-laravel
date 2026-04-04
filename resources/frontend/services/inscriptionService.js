import api from './api';

export default {

    //Participant routes
  getMesInscriptions() {
    return api.get('/participant/inscriptions');
  },


  getInscription(id) {
    return api.get(`/participant/inscriptions/${id}`);
  },

  createInscription(data) {
    return api.post('/participant/inscriptions', data);
  },

  updateInscription(id, data) {
    return api.put(`/participant/inscriptions/${id}`, data);
  },

  cancelInscription(id) {
    return api.delete(`/participant/inscriptions/${id}`);
  },

  //Organisateur routes
  getAllInscriptionsAdmin() {
    return api.get('/organisateur/inscriptions');
  },

  updateInscriptionAdmin(id, data) {
    return api.put(`/organisateur/inscriptions/${id}`, data);
  },

  deleteInscriptionAdmin(id) {
    return api.delete(`/organisateur/inscriptions/${id}`);
  },
  exportInscriptionsAdmin(format = 'xlsx') {
    return api.get(`/organisateur/inscriptions/export?format=${format}`, {
      responseType: 'blob'
    });
  }
};