import api from './api';

const templateOrganisateurService = {
    getAllTemplates() {
        return api.get('/organisateur/templates');
    },
    getTemplate(id) {
        return api.get(`/organisateur/templates/${id}`);
    },
    createTemplate(data) {
        return api.post('/organisateur/templates', data);
    },
    modifyTemplate(id, data) {
        return api.put(`/organisateur/templates/${id}`, data);
    },
    deleteTemplate(id) {
        return api.delete(`/organisateur/templates/${id}`);
    }
};

export default templateOrganisateurService;