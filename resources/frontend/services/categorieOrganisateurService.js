import api from './api';

const categorieOrganisateurService = {
    getAllCategorie() {
        return api.get('/organisateur/categories');
    },
    getCategorie(id) {
        return api.get(`/organisateur/categories/${id}`);
    },
    createCategorie(data) {
        return api.post('/organisateur/categories', data);
    },
    modifyCategorie(id, data) {
        return api.put(`/organisateur/categories/${id}`, data);
    },
    deleteCategorie(id) {
        return api.delete(`/organisateur/categories/${id}`);
    }
};

export default categorieOrganisateurService;