import api from './api';

export default {

    /**
     * Récupère les résultats du participant connecté.
     */
    mesResultats() {
        return api.get('/participant/resultats');
    },

    /**
     * Récupère tous les résultats d'une course (organisateur).
     * @param {number} id_course
     */
    getResultatsParCourse(id_course) {
        return api.get(`/organisateur/courses/${id_course}/resultats`);
    },

    /**
     * Importe les résultats depuis un fichier Excel.
     * @param {number} id_course
     * @param {File} fichier
     */
    importerResultats(id_course, fichier) {
        const formData = new FormData();
        formData.append('fichier', fichier);
        return api.post(`/organisateur/courses/${id_course}/resultats/import`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },

    /**
     * Supprime tous les résultats d'une course (pour réimporter).
     * @param {number} id_course
     */
    supprimerResultats(id_course) {
        return api.delete(`/organisateur/courses/${id_course}/resultats`);
    },
};
