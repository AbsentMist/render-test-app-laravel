import api from './api';

const questionParticipantService = {
    getQuestionsCourse(id_course) {
        return api.get(`/participant/questions/${id_course}`);
    },
};

export default questionParticipantService;