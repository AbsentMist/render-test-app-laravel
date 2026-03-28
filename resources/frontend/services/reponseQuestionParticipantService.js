import api from './api';

const reponseQuestionParticipantService = {
    getReponsesInscription(id_inscription) {
        return api.get(`/participant/inscriptions/${id_inscription}/reponses`);
    },
    saveReponses(data) {
        return api.post('/participant/reponses-questions', data);
    },
    deleteReponse(id_inscription, id_question) {
        return api.delete(`/participant/reponses-questions/${id_inscription}/${id_question}`);
    },
};

export default reponseQuestionParticipantService;