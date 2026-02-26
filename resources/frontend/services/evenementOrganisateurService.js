import api from './api';

const evenementOrganisateurService = {
    getAllEvenements() {
        return api.get('/organisateur/evenements');
    },
    getEvenement(id) {
        return api.get(`/organisateur/evenements/${id}`);
    },
    createEvenement(data) {
        return api.post('/organisateur/evenements', data);
    },
    modifyEvenement(id, data) {
        return api.put(`/organisateur/evenements/${id}`, data);
    },
    deleteEvenement(id) {
        return api.delete(`/organisateur/evenements/${id}`);
    },
}

export default evenementOrganisateurService;