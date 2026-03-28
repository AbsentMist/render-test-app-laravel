import api from './api';

const optionQuestionOrganisateurService = {
    getAllChoixQuestion(id_question) {
        return api.get(`/organisateur/questions/${id_question}/choix`);
    },
    getChoix(id) {
        return api.get(`/organisateur/choix/${id}`);
    },
    createChoix(id_question, data) {
        return api.post(`/organisateur/questions/${id_question}/choix`, data);
    },
    modifyChoix(id, data) {
        return api.put(`/organisateur/choix/${id}`, data);
    },
    deleteChoix(id) {
        return api.delete(`/organisateur/choix/${id}`);
    },
};

export default optionQuestionOrganisateurService;