import axios from 'axios';

console.log(import.meta.env.VITE_API_URL);

const api = axios.create({
    baseURL: 'https://render-test-app-laravel.onrender.com/api',
    withCredentials: true, 
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json'
    }
});

export default api;