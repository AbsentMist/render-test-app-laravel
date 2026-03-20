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
};

export default participantService;