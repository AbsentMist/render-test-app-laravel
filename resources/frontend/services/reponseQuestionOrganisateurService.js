import api from './api';

const reponseQuestionOrganisateurService = {
    getReponsesQuestion(id_question) {
        return api.get(`/organisateur/questions/${id_question}/reponses`);
    },
};

export default reponseQuestionOrganisateurService;