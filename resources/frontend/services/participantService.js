import api from './api';

const participantService = {

    // Récupère tous les participants liés au compte connecté
    getMesParticipants() {
        return api.get('/participant/participants');
    },

    // Crée un nouveau participant lié au compte connecté
    creerParticipant(data) {
        return api.post('/participant/participants', data);
    },

    // Modifie un sous-profil lié au compte connecté
    majParticipant(id, data) {
        return api.put(`/participant/participants/${id}`, data);
    },

    // Supprime un sous-profil lié au compte connecté
    supprimerParticipant(id) {
        return api.delete(`/participant/participants/${id}`);
    },
};

export default participantService;