import api from './api';

const courseParticipantService = {
    // Récupère toutes les courses liées à un évènement précis
    getAllCourses(idEvenement) {
        return api.get(`/participant/courses/${idEvenement}`);
    },
    
    // Récupère les détails d'une seule course (si besoin pour une page de détails)
    getCourse(id) {
        return api.get(`/participant/courses/course/${id}`);
    }
};

export default courseParticipantService;