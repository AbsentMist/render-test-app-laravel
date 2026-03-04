import api from './api';

const optionCourseService = {
    getAllOptionCourse() {
        return api.get('/organisateur/optionCourse');
    },
    getOptionCourse(id_course) {
        return api.get(`/organisateur/optionCourse/${id_course}`);
    },
    createOptionCourse(data) {
        return api.post('/organisateur/optionCourse', data);
    },
    deleteOptionCourse(id_course, id_option) {
        return api.delete(`/organisateur/optionCourse/${id_course}/${id_option}`);
    },
    deleteOptionByCourse(id_course){
        return api.delete(`/organisateur/optionCourse/${id_course}`);
    }
};

export default optionCourseService;