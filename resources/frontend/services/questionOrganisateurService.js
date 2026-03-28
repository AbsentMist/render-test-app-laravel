import api from './api';

const questionOrganisateurService = {
    getAllQuestions() {
        return api.get('/organisateur/questions');
    },
    getQuestion(id) {
        return api.get(`/organisateur/questions/${id}`);
    },
    createQuestion(data) {
        return api.post('/organisateur/questions', data);
    },
    modifyQuestion(id, data) {
        return api.put(`/organisateur/questions/${id}`, data);
    },
    deleteQuestion(id) {
        return api.delete(`/organisateur/questions/${id}`);
    },
};

export default questionOrganisateurService;