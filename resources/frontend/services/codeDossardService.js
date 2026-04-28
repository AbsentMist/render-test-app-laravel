import api from './api';

export default {

    /**
     * Valide un code dossard pour une course.
     * @param {string} code
     * @param {number} id_course
     */
    validerCode(code, id_course) {
        return api.post('/participant/codes-dossard/valider', {
            code: code.toUpperCase(),
            id_course,
        });
    },

    // ===== ORGANISATEUR =====

    /**
     * Récupère tous les codes dossard d'une course.
     * @param {number} id_course
     */
    getCodesParCourse(id_course) {
        return api.get(`/organisateur/courses/${id_course}/codes-dossard`);
    },

    /**
     * Crée un nouveau code dossard.
     * @param {number} id_course
     * @param {Object} data - { code, nom_personnalise, utilisations_max }
     */
    creerCode(id_course, data) {
        return api.post(`/organisateur/courses/${id_course}/codes-dossard`, data);
    },

    /**
     * Modifie un code dossard.
     * @param {number} id
     * @param {Object} data
     */
    modifierCode(id, data) {
        return api.put(`/organisateur/codes-dossard/${id}`, data);
    },

    /**
     * Supprime un code dossard.
     * @param {number} id
     */
    supprimerCode(id) {
        return api.delete(`/organisateur/codes-dossard/${id}`);
    },
};
