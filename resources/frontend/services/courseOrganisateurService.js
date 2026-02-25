import api from './api';

export const courseOrganisateurService = {
    getAllCourse() {
        return api.get('/organisateur/course');
    },
    getCourse(id) {
        return api.get(`/organisateur/course/${id}`);
    },
    createCourse(data) {
        return api.post('/organisateur/course', data);
    },
    modifyCourse(id, data) {
        return api.put(`/organisateur/course/${id}`, data);
    },
    deleteCourse(id) {
        return api.delete(`/organisateur/course/${id}`);
    },
}