import api from './api';

export default {
    // Participant
    getDocumentsByInscription(id_inscription) {
        return api.get(`/participant/inscriptions/${id_inscription}/documents`);
    },

    uploadDocument(id_inscription, formData) {
        return api.post(`/participant/inscriptions/${id_inscription}/documents`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
    },

    downloadDocument(id) {
        return api.get(`/participant/documents/${id}/download`, {
            responseType: 'blob',
        });
    },

    deleteDocument(id) {
        return api.delete(`/participant/documents/${id}`);
    },

    // Admin
    getDocumentsByInscriptionAdmin(id_inscription) {
        return api.get(`/organisateur/inscriptions/${id_inscription}/documents`);
    },

    deleteDocumentAdmin(id) {
        return api.delete(`/organisateur/documents/${id}`);
    }
};
