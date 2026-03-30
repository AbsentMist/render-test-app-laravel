import api from './api';

const reponseQuestionOrganisateurService = {
    getReponsesQuestion(id_question) {
        return api.get(`/organisateur/questions/${id_question}/reponses`);
    },
    saveReponses(data) {
        return api.post('/organisateur/reponses-questions', data);
    },
};

export default reponseQuestionOrganisateurService;
