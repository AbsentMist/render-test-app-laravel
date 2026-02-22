import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null)
    const token = ref(localStorage.getItem('token') || null)

    // Configure axios avec le token si déjà connecté
    if (token.value) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
    }

    async function login(email, password) {
        const response = await axios.post('/api/login', { email, password })
        token.value = response.data.token
        user.value = response.data.user
        localStorage.setItem('token', token.value)
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
        delete axios.defaults.headers.common['Authorization']
    }

    const isAuthenticated = () => !!token.value

    return { user, token, login, register, logout, isAuthenticated }
})