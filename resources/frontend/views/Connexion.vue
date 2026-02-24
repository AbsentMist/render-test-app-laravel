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
  <input v-model="password" :type="showPassword ? 'text' : 'password'"
    class="input-field w-full pl-10 pr-10" />
  <button type="button" @click="showPassword = !showPassword"
    class="absolute inset-y-0 right-3 flex items-center text-primary-300 hover:text-primary">
    <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
    </svg>
    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21" />
    </svg>
  </button>
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

const showPassword = ref(false)

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
