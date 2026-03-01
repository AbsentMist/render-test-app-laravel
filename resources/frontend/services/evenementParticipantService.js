import api from './api';

const evenementParticipantService = {
    getAllEvenements() {
        return api.get('/participant/evenements');
    }
};

export default evenementParticipantService;