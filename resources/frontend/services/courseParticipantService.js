import api from './api';

const courseParticipantService = {
    // Récupère toutes les courses liées à un évènement précis
    getAllCourses(idEvenement) {
        return api.get(`/participant/evenements/${idEvenement}/courses`);
    },
    
    // Récupère les détails d'une seule course (si besoin pour une page de détails)
    getCourse(id) {
        return api.get(`/participant/courses/${id}`);
    }
};

export default courseParticipantService;