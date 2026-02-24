<template>
  <div class="auth-bg min-h-screen flex items-center justify-center px-4 py-8">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-8">

      <!-- Logo -->
      <div class="flex justify-center mb-4">
        <img src="/images/rgva-logo.png" alt="Running Geneva" class="h-20 object-contain" />
      </div>

      <!-- Titre -->
      <p class="text-center text-body mb-6">
        <strong>Bienvenue!</strong><br />
        Veuillez entrer ces informations :
        <span v-if="currentStep === 3"><br /><strong>Vous y êtes presque !</strong></span>
      </p>

      <!-- Indicateur des étapes -->
      <IndicateurEtapes :steps="steps" :currentStep="currentStep" />

      <!-- ETAPE 1 : Identifiants -->
      <div v-if="currentStep === 1" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="text-label block mb-1">Prénom : <span class="text-accent">*</span></label>
            <input v-model="form.prenom" type="text" class="input-field w-full"
              :class="{ 'border-accent': errors.prenom }" />
            <p v-if="errors.prenom" class="text-accent text-label mt-1">{{ errors.prenom }}</p>
          </div>
          <div>
            <label class="text-label block mb-1">Nom : <span class="text-accent">*</span></label>
            <input v-model="form.nom" type="text" class="input-field w-full"
              :class="{ 'border-accent': errors.nom }" />
            <p v-if="errors.nom" class="text-accent text-label mt-1">{{ errors.nom }}</p>
          </div>
        </div>

        <div>
          <label class="text-label block mb-1">Votre adresse mail : <span class="text-accent">*</span></label>
          <div class="relative">
            <span class="absolute inset-y-0 left-3 flex items-center text-primary-300">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
            </span>
            <input v-model="form.email" type="email" class="input-field w-full pl-10"
              :class="{ 'border-accent': errors.email }" />
          </div>
          <p v-if="errors.email" class="text-accent text-label mt-1">{{ errors.email }}</p>
        </div>

        <div>
          <label class="text-label block mb-1">Votre mot de passe : <span class="text-accent">*</span></label>
          <div class="relative">
  <span class="absolute inset-y-0 left-3 flex items-center text-primary-300">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
    </svg>
  </span>
  <input v-model="form.password" :type="showPassword ? 'text' : 'password'"
    class="input-field w-full pl-10 pr-10" :class="{ 'border-accent': errors.password }" />
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
          <p v-if="errors.password" class="text-accent text-label mt-1">{{ errors.password }}</p>
        </div>

        <div>
          <label class="text-label block mb-1">Répétez votre mot de passe : <span class="text-accent">*</span></label>
          <div class="relative">
  <span class="absolute inset-y-0 left-3 flex items-center text-primary-300">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
    </svg>
  </span>
  <input v-model="form.passwordConfirm" :type="showPasswordConfirm ? 'text' : 'password'"
    class="input-field w-full pl-10 pr-10" :class="{ 'border-accent': errors.passwordConfirm }" />
  <button type="button" @click="showPasswordConfirm = !showPasswordConfirm"
    class="absolute inset-y-0 right-3 flex items-center text-primary-300 hover:text-primary">
    <svg v-if="!showPasswordConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
          <p v-if="errors.passwordConfirm" class="text-accent text-label mt-1">{{ errors.passwordConfirm }}</p>
        </div>
      </div>

      <!-- ETAPE 2 : Profil -->
      <div v-if="currentStep === 2" class="space-y-4">
        <div>
          <label class="text-label block mb-1">Genre : <span class="text-accent">*</span></label>
          <div class="grid grid-cols-2 gap-4">
            <button @click="form.genre = 'Femme'"
              class="py-3 rounded-xl border-2 text-body font-medium transition-all"
              :class="form.genre === 'Femme' ? 'border-primary bg-primary text-white' : 'border-gray-300 bg-white text-primary hover:border-primary'">
              Femme
            </button>
            <button @click="form.genre = 'Homme'"
              class="py-3 rounded-xl border-2 text-body font-medium transition-all"
              :class="form.genre === 'Homme' ? 'border-primary bg-primary text-white' : 'border-gray-300 bg-white text-primary hover:border-primary'">
              Homme
            </button>
          </div>
          <p v-if="errors.genre" class="text-accent text-label mt-1">{{ errors.genre }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
<label class="block mb-1 text-label text-primary font-normal">Date de naissance : <span class="text-accent">*</span></label>  <div class="relative">
    <input
      v-model="form.dateNaissance"
      type="text"
      placeholder="JJ/MM/AAAA"
      maxlength="10"
      class="input-field w-full pr-10"
      :class="{ 'border-accent': errors.dateNaissance }"
      @input="formaterDate"
    />
    <!-- Icône calendrier -->
    <button
      type="button"
      @click="datePickerRef.showPicker()"
      class="absolute inset-y-0 right-3 flex items-center text-primary-300 hover:text-primary"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
    </button>

    <!-- Calendrier natif caché -->
    <input
      ref="datePickerRef"
      type="date"
      class="absolute opacity-0 w-0 h-0 top-0 right-0"
      @change="dateDepuisCalendrier"
    />
  </div>
  <p v-if="errors.dateNaissance" class="text-accent text-label mt-1">{{ errors.dateNaissance }}</p>
</div>
          <div>
            <label class="text-label block mb-1">N° téléphone : <span class="text-accent">*</span></label>
            <input v-model="form.telephone" type="tel"
  class="input-field w-full" :class="{ 'border-accent': errors.telephone }"
  @input="formaterTelephone" />
            <p v-if="errors.telephone" class="text-accent text-label mt-1">{{ errors.telephone }}</p>
          </div>
        </div>

        <div>
          <label class="text-label block mb-1">Équipe, Club, Entreprise :</label>
          <input v-model="form.club" type="text" class="input-field w-full" />
        </div>

        <!-- Combobox Nationalité -->
        <div>
          <label class="text-label block mb-1">Votre nationalité : <span class="text-accent">*</span></label>
          <div class="relative" ref="nationaliteRef">
            <input
              v-model="nationaliteSearch"
              type="text"
              placeholder="Rechercher un pays..."
              class="input-field w-full pr-8"
              :class="{ 'border-accent': errors.nationalite }"
              @focus="showCountryDropdown = true"
              @input="showCountryDropdown = true"
            />
            <span class="absolute inset-y-0 right-3 flex items-center text-primary-300 pointer-events-none">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </span>
            <div v-if="showCountryDropdown && filteredCountries.length > 0"
              class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
              <button
                v-for="country in filteredCountries"
                :key="country"
                @mousedown.prevent="selectCountry(country)"
                class="w-full text-left px-4 py-2 text-body hover:bg-secondary-600 transition-colors">
                {{ country }}
              </button>
            </div>
          </div>
          <p v-if="errors.nationalite" class="text-accent text-label mt-1">{{ errors.nationalite }}</p>
        </div>
      </div>

      <!-- ETAPE 3 : Coordonnées -->
      <div v-if="currentStep === 3" class="space-y-4">
        <div class="grid grid-cols-3 gap-4">
          <div class="col-span-2">
  <label class="text-label block mb-1">Adresse : <span class="text-accent">*</span></label>
  <div class="relative" ref="adresseRef">
    <input
      v-model="form.adresse"
      type="text"
      class="input-field w-full"
      :class="{ 'border-accent': errors.adresse }"
      @input="rechercherAdresse(form.adresse)"
      placeholder="Ex: Rue du Rhône"
    />
    <!-- Suggestions -->
    <div v-if="showAdresseDropdown && adresseSuggestions.length > 0"
      class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
      <button
        v-for="(suggestion, index) in adresseSuggestions"
        :key="index"
        @mousedown.prevent="selectionnerAdresse(suggestion)"
        class="w-full text-left px-4 py-2 text-body hover:bg-secondary-600 transition-colors">
        {{ suggestion.attrs.label.replace(/<[^>]*>/g, '') }}
      </button>
    </div>
  </div>
  <p v-if="errors.adresse" class="text-accent text-label mt-1">{{ errors.adresse }}</p>
</div>
          <div>
            <label class="text-label block mb-1">N° <span class="text-accent">*</span></label>
            <input v-model="form.numeroRue" type="text" class="input-field w-full"
              :class="{ 'border-accent': errors.numeroRue }" />
            <p v-if="errors.numeroRue" class="text-accent text-label mt-1">{{ errors.numeroRue }}</p>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="text-label block mb-1">NPA : <span class="text-accent">*</span></label>
            <input v-model="form.npa" type="text" class="input-field w-full"
              :class="{ 'border-accent': errors.npa }" />
            <p v-if="errors.npa" class="text-accent text-label mt-1">{{ errors.npa }}</p>
          </div>
          <div>
            <label class="text-label block mb-1">Commune : <span class="text-accent">*</span></label>
            <input v-model="form.commune" type="text" class="input-field w-full"
              :class="{ 'border-accent': errors.commune }" />
            <p v-if="errors.commune" class="text-accent text-label mt-1">{{ errors.commune }}</p>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4 items-start">
          <div>
            <label class="text-label block mb-1">Taille T-Shirt : <span class="text-accent">*</span></label>
            <select v-model="form.tailleTshirt" class="input-field w-full pr-8 appearance-none cursor-pointer">
              <option value="XS">XS</option>
              <option value="S">S</option>
              <option value="M">M</option>
              <option value="L">L</option>
              <option value="XL">XL</option>
              <option value="XXL">XXL</option>
            </select>
          </div>
          <div>
            <label class="text-label block mb-1">Photo de profil :</label>
            <div class="relative w-16 h-16">
              <div class="w-16 h-16 rounded-full bg-tertiary flex items-center justify-center cursor-pointer overflow-hidden"
                @click="triggerFileInput">
                <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" alt="Photo profil" />
                <svg v-else class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <button v-if="photoPreview" @click="photoPreview = null; form.photo = null"
                class="absolute -top-1 -right-1 w-5 h-5 bg-white rounded-full flex items-center justify-center shadow text-primary-300 hover:text-accent">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
              <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="handlePhotoChange" />
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <div class="flex justify-between mt-8">
        <button @click="previousStep" class="btn-accent-300 px-8 py-3 rounded-xl">Précédent</button>
        <button v-if="currentStep < 3" @click="nextStep" class="btn-tertiary px-8 py-3 rounded-xl">Suivant</button>
        <button
    v-else
    @click="handleRegister"
    :disabled="chargement"
    class="btn-tertiary px-8 py-3 rounded-xl"
    :class="{ 'opacity-50 cursor-not-allowed': chargement }"
  >
    {{ chargement ? 'Création en cours...' : 'Terminé' }}
  </button>
</div>

<!-- Message d'erreur global (en dehors du flex) -->
<p v-if="erreurGlobale" class="text-accent text-label text-center mt-2">
  {{ erreurGlobale }}
</p>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import IndicateurEtapes from '../components/IndicateurEtapes.vue'

const router = useRouter()
const authStore = useAuthStore()
const currentStep = ref(1)
const steps = ['Identifiants', 'Profil', 'Coordonnées']
const fileInput = ref(null)
const photoPreview = ref(null)
const nationaliteRef = ref(null)
const showCountryDropdown = ref(false)
const nationaliteSearch = ref('')
const errors = reactive({})
const showCalendrier = ref(false)
const datePickerRef = ref(null)
// Autocomplétion adresse API Confédération Suisse
const adresseRef = ref(null)
const adresseSuggestions = ref([])
const showAdresseDropdown = ref(false)
let adresseTimeout = null

const erreurGlobale = ref('')
const chargement = ref(false)
const showPassword = ref(false)
const showPasswordConfirm = ref(false)

const form = reactive({
  prenom: '', nom: '', email: '', password: '', passwordConfirm: '',
  genre: '', dateNaissance: '', telephone: '', club: '', nationalite: '',
  adresse: '', numeroRue: '', npa: '', commune: '', tailleTshirt: 'L', photo: null,
})

const countries = [
  'Afghanistan', 'Afrique du Sud', 'Albanie', 'Algérie', 'Allemagne', 'Andorre', 'Angola',
  'Arabie Saoudite', 'Argentine', 'Arménie', 'Australie', 'Autriche', 'Azerbaïdjan',
  'Bahreïn', 'Bangladesh', 'Belgique', 'Bénin', 'Biélorussie', 'Bolivie', 'Bosnie-Herzégovine',
  'Botswana', 'Brésil', 'Bulgarie', 'Burkina Faso', 'Burundi',
  'Cambodge', 'Cameroun', 'Canada', 'Chili', 'Chine', 'Chypre', 'Colombie', 'Congo',
  'Corée du Nord', 'Corée du Sud', 'Costa Rica', "Côte d'Ivoire", 'Croatie', 'Cuba',
  'Danemark', 'Djibouti', 'Égypte', 'Émirats Arabes Unis', 'Équateur', 'Érythrée', 'Espagne',
  'Estonie', 'États-Unis', 'Éthiopie', 'Finlande', 'France',
  'Gabon', 'Gambie', 'Géorgie', 'Ghana', 'Grèce', 'Guatemala', 'Guinée',
  'Haïti', 'Honduras', 'Hongrie', 'Inde', 'Indonésie', 'Irak', 'Iran', 'Irlande',
  'Islande', 'Israël', 'Italie', 'Jamaïque', 'Japon', 'Jordanie',
  'Kazakhstan', 'Kenya', 'Kirghizistan', 'Kosovo', 'Koweït',
  'Laos', 'Liban', 'Libye', 'Liechtenstein', 'Lituanie', 'Luxembourg',
  'Macédoine du Nord', 'Madagascar', 'Malaisie', 'Mali', 'Malte', 'Maroc', 'Mauritanie',
  'Mexique', 'Moldavie', 'Monaco', 'Mongolie', 'Monténégro', 'Mozambique',
  'Namibie', 'Népal', 'Nicaragua', 'Niger', 'Nigéria', 'Norvège', 'Nouvelle-Zélande',
  'Oman', 'Ouganda', 'Ouzbékistan', 'Pakistan', 'Panama', 'Paraguay', 'Pays-Bas',
  'Pérou', 'Philippines', 'Pologne', 'Portugal', 'Qatar',
  'République Centrafricaine', 'République Démocratique du Congo', 'République Dominicaine',
  'République Tchèque', 'Roumanie', 'Royaume-Uni', 'Russie', 'Rwanda',
  'Salvador', 'Sénégal', 'Serbie', 'Sierra Leone', 'Singapour', 'Slovaquie', 'Slovénie',
  'Somalie', 'Soudan', 'Sri Lanka', 'Suède', 'Suisse', 'Syrie',
  'Tadjikistan', 'Tanzanie', 'Thaïlande', 'Togo', 'Tunisie', 'Turkménistan', 'Turquie',
  'Ukraine', 'Uruguay', 'Venezuela', 'Vietnam', 'Yémen', 'Zambie', 'Zimbabwe'
]

const filteredCountries = computed(() => {
  if (!nationaliteSearch.value) return countries
  const q = nationaliteSearch.value.toLowerCase()
  return countries.filter(c => c.toLowerCase().includes(q))
})

function formaterTelephone(event) {
  let valeur = event.target.value.replace(/\D/g, '') // garde uniquement les chiffres
  if (valeur.length <= 3) {
    form.telephone = valeur
  } else if (valeur.length <= 6) {
    form.telephone = valeur.slice(0, 3) + ' ' + valeur.slice(3)
  } else if (valeur.length <= 8) {
    form.telephone = valeur.slice(0, 3) + ' ' + valeur.slice(3, 6) + ' ' + valeur.slice(6)
  } else {
    form.telephone = valeur.slice(0, 3) + ' ' + valeur.slice(3, 6) + ' ' + valeur.slice(6, 8) + ' ' + valeur.slice(8, 10)
  }
}

function selectCountry(country) {
  form.nationalite = country
  nationaliteSearch.value = country
  showCountryDropdown.value = false
}

function handleClickOutside(e) {
  if (nationaliteRef.value && !nationaliteRef.value.contains(e.target)) {
    showCountryDropdown.value = false
  }
}
onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside)
  document.addEventListener('mousedown', handleAdresseClickOutside)
})
onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleClickOutside)
  document.removeEventListener('mousedown', handleAdresseClickOutside)
})

async function rechercherAdresse(valeur) {
  clearTimeout(adresseTimeout)
  if (!valeur || valeur.length < 3) {
    adresseSuggestions.value = []
    showAdresseDropdown.value = false
    return
  }
  adresseTimeout = setTimeout(async () => {
    try {
      const response = await fetch(
        `https://api3.geo.admin.ch/rest/services/api/SearchServer?searchText=${encodeURIComponent(valeur)}&type=locations&lang=fr&limit=6&origins=address`
      )
      const data = await response.json()
      adresseSuggestions.value = data.results || []
      showAdresseDropdown.value = adresseSuggestions.value.length > 0
    } catch (e) {
      adresseSuggestions.value = []
    }
  }, 300)
}

function selectionnerAdresse(suggestion) {
  const attrs = suggestion.attrs

  // Nettoie le label HTML : ex:"Rue ROTHSCHILD 11 <b>1202 Genève</b>"
  const labelPropre = attrs.label.replace(/<[^>]*>/g, '').trim()
  // Résultat : "Rue ROTHSCHILD 11 1202 Genève"
  const parties = labelPropre.split(' ')

  // Trouve le NPA (4 chiffres consécutifs)
  const indexNpa = parties.findIndex(p => /^\d{4}$/.test(p))

  // Nom de rue = tout avant le premier chiffre
  const indexPremierChiffre = parties.findIndex(p => /^\d/.test(p))
  form.adresse = indexPremierChiffre > 0
    ? parties.slice(0, indexPremierChiffre).join(' ')
    : parties[0]

  // Numéro = le champ num retourné par l'API
  form.numeroRue = attrs.num ? String(attrs.num) : ''

  // NPA = la partie à 4 chiffres
  form.npa = indexNpa >= 0 ? parties[indexNpa] : ''

  // Commune = tout ce qui suit le NPA
  form.commune = indexNpa >= 0 ? parties.slice(indexNpa + 1).join(' ') : ''

  adresseSuggestions.value = []
  showAdresseDropdown.value = false
}

function handleAdresseClickOutside(e) {
  if (adresseRef.value && !adresseRef.value.contains(e.target)) {
    showAdresseDropdown.value = false
  }
}
function formaterDate(event) {
  let valeur = event.target.value.replace(/\D/g, '') // garde uniquement les chiffres
  if (valeur.length >= 3 && valeur.length <= 4) {
    valeur = valeur.slice(0, 2) + '/' + valeur.slice(2)
  } else if (valeur.length >= 5) {
    valeur = valeur.slice(0, 2) + '/' + valeur.slice(2, 4) + '/' + valeur.slice(4, 8)
  }
  form.dateNaissance = valeur
}

function dateDepuisCalendrier(event) {
  const date = new Date(event.target.value)
  if (!isNaN(date)) {
    const jour = String(date.getDate()).padStart(2, '0')
    const mois = String(date.getMonth() + 1).padStart(2, '0')
    const annee = date.getFullYear()
    form.dateNaissance = `${jour}/${mois}/${annee}`
  }
  showCalendrier.value = false
  // Ouvre le sélecteur natif du navigateur
  datePickerRef.value.showPicker?.()
}

function validateStep1() {
  ['prenom','nom','email','password','passwordConfirm'].forEach(k => delete errors[k])
  let valid = true
  if (!form.prenom.trim()) { errors.prenom = 'Le prénom est requis.'; valid = false }
  if (!form.nom.trim()) { errors.nom = 'Le nom est requis.'; valid = false }
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!form.email.trim()) { errors.email = "L'email est requis."; valid = false }
  else if (!emailRegex.test(form.email)) { errors.email = "L'email n'est pas valide."; valid = false }
  if (!form.password) { 
  errors.password = 'Le mot de passe est requis.'; valid = false 
} else {
  const majuscule = /[A-Z]/.test(form.password)
  const minuscule = /[a-z]/.test(form.password)
  const chiffre = /[0-9]/.test(form.password)
  const special = /[^A-Za-z0-9]/.test(form.password)
  const longueur = form.password.length >= 8

  if (!longueur || !majuscule || !minuscule || !chiffre || !special) {
    errors.password = 'Le mot de passe doit contenir minimum 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.'
    valid = false
  }
}
  if (!form.passwordConfirm) { errors.passwordConfirm = 'Veuillez répéter le mot de passe.'; valid = false }
  else if (form.password !== form.passwordConfirm) { errors.passwordConfirm = 'Les mots de passe ne correspondent pas.'; valid = false }
  return valid
}

function validateStep2() {
  ['genre','dateNaissance','telephone','nationalite'].forEach(k => delete errors[k])
  let valid = true
  if (!form.genre) { errors.genre = 'Veuillez sélectionner un genre.'; valid = false }
  const dateRegex = /^(0[1-9]|[12]\d|3[01])\/(0[1-9]|1[0-2])\/\d{4}$/
  if (!form.dateNaissance.trim()) { errors.dateNaissance = 'La date de naissance est requise.'; valid = false }
  else if (!dateRegex.test(form.dateNaissance)) { errors.dateNaissance = 'Format attendu : JJ/MM/AAAA.'; valid = false }
  if (!form.telephone.trim()) { errors.telephone = 'Le numéro de téléphone est requis.'; valid = false }
  if (!form.nationalite) { errors.nationalite = 'Veuillez sélectionner une nationalité.'; valid = false }
  return valid
}

function validateStep3() {
  ['adresse','npa','commune'].forEach(k => delete errors[k])
  delete errors.numeroRue
  let valid = true
  if (!form.adresse.trim()) { errors.adresse = "L'adresse est requise."; valid = false }
  if (!form.numeroRue.trim()) { errors.numeroRue = 'Le numéro est requis.'; valid = false }
  if (!form.npa.trim()) { errors.npa = 'Le NPA est requis.'; valid = false }
  if (!form.commune.trim()) { errors.commune = 'La commune est requise.'; valid = false }
  return valid
}

function nextStep() {
  const validators = { 1: validateStep1, 2: validateStep2 }
  if (validators[currentStep.value]()) currentStep.value++
}

function previousStep() {
  if (currentStep.value > 1) currentStep.value--
  else router.push('/login')
}

function triggerFileInput() { fileInput.value?.click() }

function handlePhotoChange(event) {
  const file = event.target.files[0]
  if (file) {
    form.photo = file
    const reader = new FileReader()
    reader.onload = (e) => { photoPreview.value = e.target.result }
    reader.readAsDataURL(file)
  }
}

async function handleRegister() {
  if (!validateStep3()) return

  erreurGlobale.value = ''
  chargement.value = true

  try {
    const payload = {
      email:           form.email,
      password:        form.password,
      password_confirmation: form.passwordConfirm,
      nom:             form.nom,
      prenom:          form.prenom,
      date_naissance:  form.dateNaissance,
      telephone:       form.telephone,
      nationalite:     form.nationalite,
      adresse:         form.adresse,
      code_postal:     form.npa,
      ville:           form.commune,
      pays:            form.nationalite,
      taille_tshirt:   form.tailleTshirt,
      sexe:            form.genre,
      equipe_nom:      form.club,
    }

    await authStore.register(payload)
    router.push('/accueil')

  } catch (e) {
    if (e.response?.data?.errors) {
      const apiErrors = e.response.data.errors
      if (apiErrors.email) errors.email = apiErrors.email[0]
      if (apiErrors.telephone) errors.telephone = apiErrors.telephone[0]
      if (apiErrors.password) errors.password = apiErrors.password[0]
      // Retourne à l'étape concernée
      if (apiErrors.email || apiErrors.password) currentStep.value = 1
    } else {
      erreurGlobale.value = 'Une erreur est survenue, veuillez réessayer.'
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