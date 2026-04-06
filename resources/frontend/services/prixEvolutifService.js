import api from './api';

export default {
    // Récupère tous les paliers d'une course
    getPaliers(idCourse) {
        return api.get(`/participant/courses/${idCourse}/prix-evolutif`);
    },

    // Récupère le tarif actuel pour une course (calcul automatique selon palier actif)
    getTarifActuel(idCourse) {
        return api.get(`/participant/courses/${idCourse}/tarif-actuel`);
    },

    // Crée un palier (organisateur)
    createPalier(data) {
        return api.post('/organisateur/prix-evolutif', data);
    },

    // Modifie un palier (organisateur)
    updatePalier(id, data) {
        return api.put(`/organisateur/prix-evolutif/${id}`, data);
    },

    // Supprime un palier (organisateur)
    deletePalier(id) {
        return api.delete(`/organisateur/prix-evolutif/${id}`);
    },

    // Supprime tous les paliers d'une course (changement de mode)
    deleteAllPaliers(idCourse) {
        return api.delete(`/organisateur/courses/${idCourse}/prix-evolutif`);
    },
};
