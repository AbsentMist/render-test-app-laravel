import api from './api';

const choixOptionParticipantService = {
    getChoixInscription(id_inscription) {
        return api.get(`/participant/inscriptions/${id_inscription}/choix-options`);
    },
    saveChoix(data) {
        return api.post('/participant/choix-options', data);
    },
    modifyChoix(id_inscription, id_option, data) {
        return api.put(`/participant/choix-options/${id_inscription}/${id_option}`, data);
    },
    deleteChoix(id_inscription, id_option) {
        return api.delete(`/participant/choix-options/${id_inscription}/${id_option}`);
    },
};

export default choixOptionParticipantService;