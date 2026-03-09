import api from './api';

const sousCategorieOrganisateurService = {
    getAllSousCategorie() {
        return api.get('/organisateur/sous-categories');
    },
    getSousCategorie(id) {
        return api.get(`/organisateur/sous-categories/${id}`);
    },
    createSousCategorie(data) {
        return api.post('/organisateur/sous-categories', data);
    },
    modifySousCategorie(id, data) {
        return api.put(`/organisateur/sous-categories/${id}`, data);
    },
    deleteSousCategorie(id) {
        return api.delete(`/organisateur/sous-categories/${id}`);
    }
};

export default sousCategorieOrganisateurService;