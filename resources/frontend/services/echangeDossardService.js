import api from './api';

export default {

    /**
     * Initie une demande d'échange de dossard.
     * @param {number} id_inscription - ID de l'inscription de A (celui qui cède)
     * @param {string} email_destinataire - Email de B (celui qui reçoit)
     */
    initierEchange(id_inscription, email_destinataire) {
        return api.post('/participant/echange-dossard/initier', {
            id_inscription,
            email_destinataire,
        });
    },

    /**
     * B accepte la demande d'échange.
     * @param {number} id_inscription_b - ID de l'inscription en attente de B
     */
    accepterEchange(id_inscription_b) {
        return api.post(`/participant/echange-dossard/${id_inscription_b}/accepter`);
    },

    /**
     * B refuse la demande d'échange.
     * @param {number} id_inscription_b - ID de l'inscription en attente de B
     */
    refuserEchange(id_inscription_b) {
        return api.post(`/participant/echange-dossard/${id_inscription_b}/refuser`);
    },

    /**
     * Récupère les demandes d'échange reçues et en attente de réponse.
     */
    mesDemandesRecues() {
        return api.get('/participant/echange-dossard/mes-demandes-recues');
    },

    /**
     * Récupère les demandes d'échange envoyées et en attente de réponse.
     */
    mesDemandesEnvoyees() {
        return api.get('/participant/echange-dossard/mes-demandes-envoyees');
    },

    /**
     * A annule une demande d'échange envoyée (tant que B n'a pas répondu).
     * @param {number} id_inscription_b - ID de l'inscription en attente de B
     */
    annulerEchange(id_inscription_b) {
        return api.delete(`/participant/echange-dossard/${id_inscription_b}/annuler`);
    },
};