import api from './api';

export const optionOrganisateurService = {
    getAllOptions() {
        return api.get('/organisateur/options');
    },
    getOption(id) {
        return api.get(`/organisateur/options/${id}`);
    },
    createOption(data) {
        return api.post('/organisateur/options', data);
    },
    deleteOption(id) {
        return api.delete(`/organisateur/options/${id}`);
    }
};