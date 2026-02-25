import api from './api';

const courseOrganisateurService = {
    getAllCourse() {
        return api.get('/organisateur/courses');
    },
    getCourse(id) {
        return api.get(`/organisateur/courses/${id}`);
    },
    createCourse(data) {
        return api.post('/organisateur/courses', data);
    },
    modifyCourse(id, data) {
        return api.put(`/organisateur/courses/${id}`, data);
    },
    deleteCourse(id) {
        return api.delete(`/organisateur/courses/${id}`);
    },
}

export default courseOrganisateurService;