import api from './api';

export default {

    /**
     * Valide un code de rabais pour une course et retourne le montant de réduction.
     * @param {string} code
     * @param {number} id_course
     * @param {number} tarif
     */
    validerCode(code, id_course, tarif) {
        return api.post('/participant/codes-rabais/valider', {
            code: code.toUpperCase(),
            id_course,
            tarif,
        });
    },

    // ===== ORGANISATEUR =====

    /**
     * Récupère tous les codes de rabais d'une course.
     * @param {number} id_course
     */
    getCodesParCourse(id_course) {
        return api.get(`/organisateur/courses/${id_course}/codes-rabais`);
    },

    /**
     * Crée un nouveau code de rabais pour une course.
     * @param {number} id_course
     * @param {Object} data
     */
    creerCode(id_course, data) {
        return api.post(`/organisateur/courses/${id_course}/codes-rabais`, data);
    },

    /**
     * Met à jour un code de rabais.
     * @param {number} id
     * @param {Object} data
     */
    modifierCode(id, data) {
        return api.put(`/organisateur/codes-rabais/${id}`, data);
    },

    /**
     * Supprime un code de rabais.
     * @param {number} id
     */
    supprimerCode(id) {
        return api.delete(`/organisateur/codes-rabais/${id}`);
    },
};
