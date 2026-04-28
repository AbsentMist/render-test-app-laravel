import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../services/api'
import { useCartStore } from './cart'

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null)
    const token = ref(localStorage.getItem('token') || null)
    const cartStore = useCartStore()
    
    // Mémorise la vue de l'admin (true = vue admin, false = vue participant)
    const activeAdminMode = ref(localStorage.getItem('adminMode') === 'true')

    const isAdmin = computed(() => {
        if (!user.value || !user.value.roles) return false;
        return user.value.roles.some(role => role.type === 'Administrateur');
    })

    const showAdminLayout = computed(() => isAdmin.value && activeAdminMode.value)

    function getCartOwnerId(currentUser) {
        return currentUser?.id ?? currentUser?.participant?.id ?? null
    }

    function toggleAdminMode() {
        if (isAdmin.value) {
            activeAdminMode.value = !activeAdminMode.value;
            localStorage.setItem('adminMode', activeAdminMode.value);
        }
    }

    // Récupération du profil dans le serveur
    async function fetchUser() {
        if (!token.value) return;
        
        try {
            const response = await api.get('/me') 
            user.value = response.data
            cartStore.setOwner(getCartOwnerId(user.value))
            
            if (isAdmin.value && localStorage.getItem('adminMode') === null) {
                activeAdminMode.value = true;
                localStorage.setItem('adminMode', 'true');
            }
        } catch (error) {
            logout(); 
        }
    }

    // Configure axios avec le token si déjà connecté
    if (token.value) {
        fetchUser(); 
    }

    async function login(email, password) {
        const response = await api.post('/login', { email, password })
        token.value = response.data.token
        user.value = response.data.user
        cartStore.setOwner(getCartOwnerId(user.value))
        
        localStorage.setItem('token', token.value)
        
        if (isAdmin.value) {
            activeAdminMode.value = true;
            localStorage.setItem('adminMode', 'true');
        }
    }

    async function register(formData) {
        await api.post('/register', formData)
    }

    async function logout() {
        try {
            await api.post('/logout')
        } catch (e) {
            console.error('Erreur lors de la déconnexion :', e)
        }
        
        token.value = null
        user.value = null
        cartStore.setOwner(null)
        localStorage.removeItem('token')
        localStorage.removeItem('adminMode') 
        activeAdminMode.value = false
    }

    const isAuthenticated = () => !!token.value

    return { 
        user, token, login, register, logout, isAuthenticated, fetchUser,
        isAdmin, showAdminLayout, toggleAdminMode 
    }
})