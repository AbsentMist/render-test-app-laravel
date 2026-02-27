import axios from 'axios';

console.log(import.meta.env.VITE_API_URL);

const api = axios.create({
    baseURL: 'http://127.0.0.1:8888/api',
    withCredentials: true, 
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json'
    }
});

export default api;