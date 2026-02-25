import api from './api';

export const optionService = {
    getAllOptions() {
        return api.get('/options');
    },
    createOption(data) {
        return api.post('/options', data);
    },
    updateOption(id, data) {
        return api.put(`/options/${id}`, data);
    },
    deleteOption(id) {
        return api.delete(`/options/${id}`);
    }
};