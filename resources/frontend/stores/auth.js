import { defineStore } from 'pinia'
import { ref, computed } from 'vue' // Ajout de computed
import axios from 'axios'

export const useAuthStore = defineStore('auth', () => {
    // NOUVEAU : On récupère l'user depuis le localStorage s'il existe
    const storedUser = localStorage.getItem('user');
    const user = ref(storedUser ? JSON.parse(storedUser) : null);
    const token = ref(localStorage.getItem('token') || null)

    
    // Mémorise la vue de l'admin (true = vue admin, false = vue participant)
    const activeAdminMode = ref(localStorage.getItem('adminMode') === 'true')

    const isAdmin = computed(() => {
        if (!user.value || !user.value.roles) return false;
        return user.value.roles.some(role => role.type === 'Administrateur');
    })

    const showAdminLayout = computed(() => isAdmin.value && activeAdminMode.value)

    function toggleAdminMode() {
        if (isAdmin.value) {
            activeAdminMode.value = !activeAdminMode.value;
            localStorage.setItem('adminMode', activeAdminMode.value);
        }
    }
    // -------------------------------

    // Configure axios avec le token si déjà connecté
    if (token.value) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
    }

    async function login(email, password) {
        const response = await axios.post('/api/login', { email, password })
        token.value = response.data.token
        user.value = response.data.user
        
        // NOUVEAU : On sauvegarde l'objet user en texte (JSON)
        localStorage.setItem('token', token.value)
        localStorage.setItem('user', JSON.stringify(user.value))
        
        
        if (user.value.roles && user.value.roles.some(r => r.type === 'Administrateur')) {
            activeAdminMode.value = true;
            localStorage.setItem('adminMode', 'true');
        }

        axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
    }

    async function register(formData) {
        const response = await axios.post('/api/register', formData)
        token.value = response.data.token
        user.value = response.data.user
        localStorage.setItem('token', token.value)
        axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
    }

    async function logout() {
        await axios.post('/api/logout')
        token.value = null
        user.value = null
        
        
        localStorage.removeItem('token')
        localStorage.removeItem('user') 
        localStorage.removeItem('adminMode') 
        
        activeAdminMode.value = false
        delete axios.defaults.headers.common['Authorization']
    }

    const isAuthenticated = () => !!token.value

    
    return { 
        user, token, login, register, logout, isAuthenticated,
        isAdmin, showAdminLayout, toggleAdminMode 
    }
})