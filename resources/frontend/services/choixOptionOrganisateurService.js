import api from './api';

const choixOptionOrganisateurService = {
    getChoixOption(id_option) {
        return api.get(`/organisateur/options/${id_option}/choix`);
    },
    getChoixInscription(id_inscription) {
        return api.get(`/organisateur/inscriptions/${id_inscription}/choix-options`);
    },
    saveChoix(data) {
        return api.post('/organisateur/choix-options', data);
    },
    modifyChoix(id_inscription, id_option, data) {
        return api.put(`/organisateur/choix-options/${id_inscription}/${id_option}`, data);
    },
    deleteChoix(id_inscription, id_option) {
        return api.delete(`/organisateur/choix-options/${id_inscription}/${id_option}`);
    },
};

export default choixOptionOrganisateurService;