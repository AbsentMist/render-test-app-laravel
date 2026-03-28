import api from './api';

const courseQuestionOrganisateurService = {
    getQuestionsCourse(id_course) {
        return api.get(`/organisateur/courses/${id_course}/questions`);
    },
    reordonnerQuestions(id_course, data) {
        return api.put(`/organisateur/courses/${id_course}/questions/ordre`, data);
    },
};

export default courseQuestionOrganisateurService;