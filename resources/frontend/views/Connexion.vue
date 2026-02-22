<template>
  <div class="auth-bg min-h-screen flex items-center justify-center px-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-8">

      <!-- Logo -->
      <div class="flex justify-center mb-6">
        <img src="/images/rgva-logo.png" alt="Running Geneva" class="h-24 object-contain" />
      </div>

      <!-- Titre -->
      <p class="text-center text-body mb-6">
        <strong>Bienvenue!</strong><br />
        Veuillez entrer votre adresse email et votre mot de passe pour vous connecter.
      </p>

      <!-- Formulaire -->
      <div class="space-y-4">
        <div>
          <label class="text-label block mb-1">Votre adresse <span class="text-accent">*</span></label>
          <div class="relative">
            <span class="absolute inset-y-0 left-3 flex items-center text-primary-300">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
            </span>
            <input
              v-model="email"
              type="email"
              placeholder=""
              class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-body"
            />
          </div>
        </div>

        <div>
          <label class="text-label block mb-1">Votre mot de passe <span class="text-accent">*</span></label>
          <div class="relative">
            <span class="absolute inset-y-0 left-3 flex items-center text-primary-300">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
            </span>
            <input
              v-model="password"
              type="password"
              placeholder=""
              class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-body"
            />
          </div>
        </div>

        <!-- Mot de passe oublié -->
        <div class="text-center">
          <router-link to="/mot-de-passe-oublie" class="text-label underline text-primary hover:text-primary-300">
            Mot de passe oublié?
          </router-link>
        </div>

        <!-- Message d'erreur -->
        <p v-if="erreur" class="text-accent text-label text-center">{{ erreur }}</p>

        <!-- Bouton connexion -->
        <button
          @click="handleLogin"
          :disabled="chargement"
          class="btn-tertiary w-full py-3 text-base rounded-xl"
          :class="{ 'opacity-50 cursor-not-allowed': chargement }"
        >
          {{ chargement ? 'Connexion en cours...' : 'Connexion' }}
        </button>

        <!-- Lien inscription -->
        <p class="text-center text-label">
          Vous n'avez pas de compte?
          <router-link to="/inscription" class="text-accent underline hover:text-accent-600 ml-1">
            Créer un compte
          </router-link>
        </p>
      </div>

      <!-- Footer links -->
      <div class="flex justify-center gap-12 mt-8">
        <router-link to="/mentions-legales" class="text-label underline hover:text-primary-300">
          Mentions légales
        </router-link>
        <router-link to="/contact" class="text-label underline hover:text-primary-300">
          Contact
        </router-link>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const erreur = ref('')
const chargement = ref(false)

async function handleLogin() {
  erreur.value = ''
  chargement.value = true
  try {
    await authStore.login(email.value, password.value)
    router.push('/accueil')
  } catch (e) {
    if (e.response?.data?.errors?.email) {
      erreur.value = e.response.data.errors.email[0]
    } else {
      erreur.value = 'Identifiants incorrects, veuillez réessayer.'
    }
  } finally {
    chargement.value = false
  }
}
</script>

<style scoped>
.auth-bg {
  background-image: url('/images/login-bg.png');
  background-size: cover;
  background-position: center;
}
</style>
