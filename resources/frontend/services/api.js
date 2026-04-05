import axios from 'axios';

console.log(import.meta.env.VITE_API_URL);

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL || '/api',
    withCredentials: true, 
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
    }
});

api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Ajout de l'intercepteur de réponse pour gérer les erreurs globales (Inscriptions fermées)
api.interceptors.response.use(
    (response) => {
        return response;
    },
    (error) => {
        // Si le backend renvoie une erreur 403 (Forbidden)
        if (error.response && error.response.status === 403) {
            // On affiche le message renvoyé par le backend
            const message = error.response.data.message || "Action non autorisée.";
            alert(message);
        }
        
        // On rejette l'erreur pour que le .catch() du composant puisse la gérer si nécessaire
        return Promise.reject(error);
    }
);

export default api;