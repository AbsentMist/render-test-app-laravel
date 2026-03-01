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
        // Laravel n'arrive pas à gérer les requêtes PUT avec des données multipart/form-data (pour les images)    
        // POST à la place de PUT pour la modification d'un événement
        return api.post(`/organisateur/evenements/${id}`, data);
    },
    deleteEvenement(id) {
        return api.delete(`/organisateur/evenements/${id}`);
    },
}

export default evenementOrganisateurService;