import api from './api';

const optionOrganisateurService = {
    getAllOptions() {
        return api.get('/organisateur/options');
    },
    getOption(id) {
        return api.get(`/organisateur/options/${id}`);
    },
    createOption(data) {
        return api.post('/organisateur/options', data);
    },
    modifyOption(id, data){
        return api.put(`/organisateur/options/${id}`, data);
    },
    deleteOption(id) {
        return api.delete(`/organisateur/options/${id}`);
    }
};

export default optionOrganisateurService;