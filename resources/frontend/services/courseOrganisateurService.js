import api from './api';

const courseOrganisateurService = {
    getAllCourses(idEvent) {
        return api.get(`/organisateur/evenements/${idEvent}/courses`);
    },
    getCourse(id, idEvent) {
        return api.get(`/organisateur/evenements/${idEvent}/courses/${id}`);
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