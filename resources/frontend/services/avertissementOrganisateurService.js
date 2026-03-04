import api from './api';

const avertissementOrganisateurService = {
    getAllAvertissement() {
        return api.get('/organisateur/avertissements');
    },
    getAvertissement(id) {
        return api.get(`/organisateur/avertissements/${id}`);
    },
    createAvertissement(data) {
        return api.post('/organisateur/avertissements', data);
    },
    modifyAvertissement(id, data) {
        return api.put(`/organisateur/avertissements/${id}`, data);
    },
    deleteAvertissement(id) {
        return api.delete(`/organisateur/avertissements/${id}`);
    }
};

export default avertissementOrganisateurService;