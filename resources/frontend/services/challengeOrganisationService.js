import api from './api';

export default {
    // Participant : GET /api/participant/courses/{id}/challenge-organisations
    getOrganisations(idCourse) {
        return api.get(`/participant/courses/${idCourse}/challenge-organisations`);
    },

    // Organisateur : POST /api/organisateur/challenge-organisations
    createOrganisation(data) {
        return api.post('/organisateur/challenge-organisations', data);
    },

    // Organisateur : DELETE /api/organisateur/challenge-organisations/{id}
    deleteOrganisation(id) {
        return api.delete(`/organisateur/challenge-organisations/${id}`);
    },
};