<template>
  <div>
    <Title texte="Formulaire Membership" />

    <div class="p-6 space-y-8">
      <div v-if="chargementInitial" class="text-body text-center py-10">
        Chargement de vos informations...
      </div>

      <div v-else-if="!hasMembershipAccess" class="rounded-xl border border-amber-200 bg-amber-50 p-4">
        <p class="text-amber-800 font-semibold">Accès membership indisponible</p>
        <p class="text-amber-700 text-sm mt-1">
          Le formulaire membership devient accessible uniquement lorsqu'un organisateur vous invite à le compléter.
        </p>
      </div>

      <div v-else-if="messageErreur" class="rounded-xl border border-red-200 bg-red-50 p-4">
        <p class="text-red-700 font-semibold">{{ messageErreur }}</p>
      </div>

      <section
        v-else-if="membershipEnCoursDansPanier"
        class="rounded-2xl border border-blue-200 bg-blue-50 p-6 shadow-sm"
      >
        <h2 class="text-base font-semibold text-heading flex items-center gap-2">
          <Icon icon="mdi:cart-check" class="w-5 h-5 text-blue-600" />
          Inscription en cours
        </h2>
        <p class="text-sm text-body mt-2">
          Votre formulaire a déjà été ajouté au panier. Veuillez valider votre panier pour confirmer votre membership.
        </p>
        <div class="mt-4">
          <router-link
            to="/panier"
            class="inline-flex items-center justify-center rounded-xl btn-tertiary px-6 py-3 font-semibold"
          >
            Voir le panier
          </router-link>
        </div>
      </section>

      <section
        v-else-if="demandeExistante && demandeExistante.status === 'En attente de validation'"
        class="rounded-xl border border-blue-200 bg-blue-50 p-5"
      >
        <h2 class="text-sm font-semibold text-heading uppercase tracking-wider mb-3 flex items-center gap-2">
          <Icon icon="mdi:clock-outline" class="w-4 h-4 text-blue-500" />
          Demande en cours de traitement
        </h2>
        <p class="text-sm text-heading font-semibold">
          Votre demande de membership est bien enregistrée.
        </p>
        <p class="text-xs text-body mt-1">
          L'organisation traite votre demande directement. Vous recevrez une notification dès qu'un administrateur aura pris une décision.
        </p>
      </section>

      <section
        v-else-if="demandeExistante && demandeExistante.status === 'Approuvée'"
        class="rounded-xl border border-green-200 bg-green-50 p-5"
      >
        <h2 class="text-sm font-semibold text-heading uppercase tracking-wider mb-3 flex items-center gap-2">
          <Icon icon="mdi:check-circle-outline" class="w-4 h-4 text-green-600" />
          Membership approuvé
        </h2>
        <p class="text-sm text-heading font-semibold">
          Votre demande a déjà été approuvée.
        </p>
        <p class="text-xs text-body mt-1">
          Votre statut membre est actif, aucune nouvelle demande n'est nécessaire.
        </p>
      </section>

      <section v-else>
        <h2
          class="text-sm font-semibold text-heading uppercase tracking-wider mb-6 flex items-center gap-2"
        >
          <Icon icon="mdi:clipboard-text-outline" class="w-5 h-5 text-blue-500" />
          Formulaire membership
        </h2>

        <div
          v-if="demandeExistante && demandeExistante.status === 'À compléter'"
          class="rounded-xl border border-amber-200 bg-amber-50 p-4 mb-4"
        >
          <p class="text-amber-800 text-sm font-semibold">Votre formulaire nécessite des compléments.</p>
          <p class="text-amber-700 text-xs mt-1">
            Merci de compléter les informations demandées puis de soumettre à nouveau.
          </p>
        </div>

        <form @submit.prevent="soumettreDemande" class="space-y-6 bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-heading mb-2">Nom *</label>
              <input
                v-model="formulaire.nom"
                type="text"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="errors.nom ? 'border-red-300 bg-red-50' : 'border-gray-200'"
                placeholder="Dupont"
              />
              <p v-if="errors.nom" class="text-xs text-red-600 mt-1">{{ errors.nom }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-heading mb-2">Prénom *</label>
              <input
                v-model="formulaire.prenom"
                type="text"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="errors.prenom ? 'border-red-300 bg-red-50' : 'border-gray-200'"
                placeholder="Jean"
              />
              <p v-if="errors.prenom" class="text-xs text-red-600 mt-1">{{ errors.prenom }}</p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-heading mb-2">Email *</label>
            <input
              v-model="formulaire.email"
              type="email"
              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="errors.email ? 'border-red-300 bg-red-50' : 'border-gray-200'"
              placeholder="jean@example.com"
              @input="errors.email = ''"
            />
            <p v-if="errors.email" class="text-xs text-red-600 mt-1">{{ errors.email }}</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-heading mb-2">Téléphone *</label>
              <input
                v-model="formulaire.telephone"
                type="tel"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="errors.telephone ? 'border-red-300 bg-red-50' : 'border-gray-200'"
                placeholder="079 123 45 67"
                @input="formaterTelephone"
              />
              <p v-if="errors.telephone" class="text-xs text-red-600 mt-1">{{ errors.telephone }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-heading mb-2">Date de naissance *</label>
              <input
                v-model="formulaire.date_naissance"
                type="date"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="errors.date_naissance ? 'border-red-300 bg-red-50' : 'border-gray-200'"
              />
              <p v-if="errors.date_naissance" class="text-xs text-red-600 mt-1">{{ errors.date_naissance }}</p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-heading mb-2">Adresse *</label>
            <div class="relative" ref="adresseRef">
              <input
                v-model="formulaire.adresse"
                type="text"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="errors.adresse ? 'border-red-300 bg-red-50' : 'border-gray-200'"
                placeholder="Ex: Rue de Genève 1"
                @input="rechercherAdresse(formulaire.adresse)"
              />
              <div
                v-if="showAdresseDropdown && adresseSuggestions.length > 0"
                class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto"
              >
                <button
                  v-for="(suggestion, index) in adresseSuggestions"
                  :key="index"
                  type="button"
                  @mousedown.prevent="selectionnerAdresse(suggestion)"
                  class="w-full text-left px-4 py-2 text-sm text-heading hover:bg-neutral-secondary-medium transition-colors"
                >
                  {{ suggestion.attrs.label.replace(/<[^>]*>/g, '') }}
                </button>
              </div>
            </div>
            <p v-if="errors.adresse" class="text-xs text-red-600 mt-1">{{ errors.adresse }}</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-heading mb-2">Code postal *</label>
              <input
                v-model="formulaire.code_postal"
                type="text"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="errors.code_postal ? 'border-red-300 bg-red-50' : 'border-gray-200'"
                placeholder="1200"
              />
              <p v-if="errors.code_postal" class="text-xs text-red-600 mt-1">{{ errors.code_postal }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-heading mb-2">Ville *</label>
              <input
                v-model="formulaire.ville"
                type="text"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="errors.ville ? 'border-red-300 bg-red-50' : 'border-gray-200'"
                placeholder="Genève"
              />
              <p v-if="errors.ville" class="text-xs text-red-600 mt-1">{{ errors.ville }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-heading mb-2">Pays *</label>
              <input
                v-model="formulaire.pays"
                type="text"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="errors.pays ? 'border-red-300 bg-red-50' : 'border-gray-200'"
                placeholder="Suisse"
              />
              <p v-if="errors.pays" class="text-xs text-red-600 mt-1">{{ errors.pays }}</p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-heading mb-2">
              Votre motivation pour rejoindre notre association *
            </label>
            <textarea
              v-model="formulaire.description"
              class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="errors.description ? 'border-red-300 bg-red-50' : 'border-gray-200'"
              placeholder="Dites-nous pourquoi vous souhaitez devenir membre..."
              rows="5"
            ></textarea>
            <p class="text-xs text-gray-500 mt-1">Minimum 10 caractères.</p>
            <p v-if="errors.description" class="text-xs text-red-600 mt-1">{{ errors.description }}</p>
          </div>

          <div class="flex justify-end pt-6 border-t border-gray-100">
            <button
              type="submit"
              :disabled="chargement"
              class="btn-tertiary px-8 py-3 rounded-xl font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="!chargement">Ajouter au panier</span>
              <span v-else>Ajout en cours...</span>
            </button>
          </div>
        </form>
      </section>
    </div>
  </div>
</template>

<script>
import Title from '../components/Title.vue';
import { Icon } from '@iconify/vue';
import membershipService from '../services/membershipService';
import { useCartStore } from '../stores/cart';

export default {
  name: 'Membership',
  components: { Title, Icon },

  data() {
    return {
      formulaire: {
        nom: '',
        prenom: '',
        email: '',
        adresse: '',
        code_postal: '',
        ville: '',
        pays: '',
        telephone: '',
        date_naissance: '',
        description: '',
      },
      errors: {},
      chargement: false,
      chargementInitial: true,
      demandeExistante: null,
      hasMembershipAccess: false,
      messageErreur: '',
      adresseSuggestions: [],
      showAdresseDropdown: false,
      adresseTimeout: null,
      cartStore: null,
    };
  },

  mounted() {
    this.initialiserVue();
    document.addEventListener('mousedown', this.handleAdresseClickOutside);
  },

  beforeUnmount() {
    document.removeEventListener('mousedown', this.handleAdresseClickOutside);
    if (this.adresseTimeout) {
      clearTimeout(this.adresseTimeout);
    }
  },

  computed: {
    membershipEnCoursDansPanier() {
      return this.getCart().inscriptions.some((article) => article.type_article === 'membership');
    },
  },

  methods: {
    /**
     * Obtient l'instance du panier. L'instancie si nécessaire.
     * @author Ngoie Steven
     * @returns {object} - L'instance du panier (useCartStore)
     */
    getCart() {
      if (!this.cartStore) this.cartStore = useCartStore();
      return this.cartStore;
    },
    /**
     * Initialise la vue en vérifiant l'accès membership et en chargeant les données du profil et de la demande.
     * @author Ngoie Steven
     * @returns {Promise<void>}
     */
    async initialiserVue() {
      this.chargementInitial = true;
      this.messageErreur = '';

      try {
        const accesRes = await membershipService.accesMembershipParticipant();
        this.hasMembershipAccess = !!accesRes?.data?.has_access;

        if (!this.hasMembershipAccess) {
          this.demandeExistante = null;
          return;
        }

        const [profilRes, demandeRes] = await Promise.all([
          membershipService.profilParticipant(),
          membershipService.maDemande(),
        ]);

        this.prefillDepuisProfil(profilRes.data || {});
        this.demandeExistante = demandeRes.data || null;

        if (this.demandeExistante && this.demandeExistante.status === 'À compléter') {
          this.prefillDepuisDemande(this.demandeExistante);
        }
      } catch (error) {
        console.error('Erreur initialisation membership:', error);
        this.messageErreur = 'Impossible de charger vos informations pour le moment.';
      } finally {
        this.chargementInitial = false;
      }
    },

    /**
     * Remplit le formulaire avec les données du profil participant actuel.
     * @author Ngoie Steven
     * @param {object} profil - Les données du profil participant
     * @returns {void}
     */
    prefillDepuisProfil(profil) {
      const adresseComplete = [profil.adresse, profil.numero].filter(Boolean).join(' ').trim();

      this.formulaire.nom = profil.nom || this.formulaire.nom;
      this.formulaire.prenom = profil.prenom || this.formulaire.prenom;
      this.formulaire.email = profil.email || this.formulaire.email;
      this.formulaire.adresse = adresseComplete || this.formulaire.adresse;
      this.formulaire.code_postal = profil.npa || this.formulaire.code_postal;
      this.formulaire.ville = profil.commune || this.formulaire.ville;
      this.formulaire.pays = profil.nationalite || this.formulaire.pays || 'Suisse';
      this.formulaire.telephone = profil.telephone || this.formulaire.telephone;

      if (profil.dateNaissance && /^\d{2}\/\d{2}\/\d{4}$/.test(profil.dateNaissance)) {
        const [jour, mois, annee] = profil.dateNaissance.split('/');
        this.formulaire.date_naissance = `${annee}-${mois}-${jour}`;
      }

      if (this.formulaire.telephone) {
        this.formulaire.telephone = this.normaliserTelephone(this.formulaire.telephone);
      }
    },

    /**
     * Remplit le formulaire avec les données d'une demande de membership existante.
     * @author Ngoie Steven
     * @param {object} demande - L'objet demande membership avec les données précédemment saisies
     * @returns {void}
     */
    prefillDepuisDemande(demande) {
      this.formulaire.nom = demande.nom || this.formulaire.nom;
      this.formulaire.prenom = demande.prenom || this.formulaire.prenom;
      this.formulaire.email = demande.email || this.formulaire.email;
      this.formulaire.adresse = demande.adresse || this.formulaire.adresse;
      this.formulaire.code_postal = demande.code_postal || this.formulaire.code_postal;
      this.formulaire.ville = demande.ville || this.formulaire.ville;
      this.formulaire.pays = demande.pays || this.formulaire.pays;
      this.formulaire.telephone = this.normaliserTelephone(demande.telephone || this.formulaire.telephone);
      this.formulaire.description = demande.description || this.formulaire.description;

      if (demande.date_naissance) {
        const d = new Date(demande.date_naissance);
        if (!Number.isNaN(d.getTime())) {
          const mm = String(d.getMonth() + 1).padStart(2, '0');
          const dd = String(d.getDate()).padStart(2, '0');
          this.formulaire.date_naissance = `${d.getFullYear()}-${mm}-${dd}`;
        }
      }
    },

    /**
     * Normalise un numéro de téléphone au format XXX XXX XX XX.
     * @author Ngoie Steven
     * @param {string} value - Le numéro de téléphone à normaliser
     * @returns {string} - Le numéro formaté
     */
    normaliserTelephone(value) {
      const chiffres = String(value || '').replace(/\D/g, '').slice(0, 10);
      if (chiffres.length <= 3) return chiffres;
      if (chiffres.length <= 6) return `${chiffres.slice(0, 3)} ${chiffres.slice(3)}`;
      if (chiffres.length <= 8) return `${chiffres.slice(0, 3)} ${chiffres.slice(3, 6)} ${chiffres.slice(6)}`;
      return `${chiffres.slice(0, 3)} ${chiffres.slice(3, 6)} ${chiffres.slice(6, 8)} ${chiffres.slice(8, 10)}`;
    },

    /**
     * Formate le numéro de téléphone au fur et à mesure que l'utilisateur tape.
     * @author Ngoie Steven
     * @param {Event} event - L'événement de modification du champ input
     * @returns {void}
     */
    formaterTelephone(event) {
      this.formulaire.telephone = this.normaliserTelephone(event.target.value);
    },

    /**
     * Recherche les suggestions d'adresses via l'API geo.admin.ch de la Confédération Suisse.
     * @author Ngoie Steven
     * @param {string} valeur - La valeur de recherche d'adresse
     * @returns {Promise<void>}
     */
    async rechercherAdresse(valeur) {
      if (this.adresseTimeout) {
        clearTimeout(this.adresseTimeout);
      }

      if (!valeur || valeur.length < 3) {
        this.adresseSuggestions = [];
        this.showAdresseDropdown = false;
        return;
      }

      this.adresseTimeout = setTimeout(async () => {
        try {
          const response = await fetch(
            `https://api3.geo.admin.ch/rest/services/api/SearchServer?searchText=${encodeURIComponent(valeur)}&type=locations&lang=fr&limit=6&origins=address`,
          );
          const data = await response.json();
          this.adresseSuggestions = data.results || [];
          this.showAdresseDropdown = this.adresseSuggestions.length > 0;
        } catch (error) {
          console.error('Erreur lors de la recherche d\'adresses:', error);
          this.adresseSuggestions = [];
          this.showAdresseDropdown = false;
        }
      }, 250);
    },

    /**
     * Traite la sélection d'une adresse et remplit les champs adresse, code postal et ville.
     * @author Ngoie Steven
     * @param {object} suggestion - L'objet suggestion retourné par l'API geo.admin.ch
     * @returns {void}
     */
    selectionnerAdresse(suggestion) {
      const attrs = suggestion.attrs;
      const labelPropre = attrs.label.replace(/<[^>]*>/g, '').trim();
      const parties = labelPropre.split(' ');
      const indexNpa = parties.findIndex((p) => /^\d{4}$/.test(p));

      this.formulaire.adresse = labelPropre;
      this.formulaire.code_postal = indexNpa >= 0 ? parties[indexNpa] : this.formulaire.code_postal;
      this.formulaire.ville = indexNpa >= 0 ? parties.slice(indexNpa + 1).join(' ') : this.formulaire.ville;

      this.adresseSuggestions = [];
      this.showAdresseDropdown = false;
    },

    /**
     * Ferme le dropdown des suggestions d'adresses lors d'un clic en dehors du champ.
     * @author Ngoie Steven
     * @param {MouseEvent} event - L'événement de clic
     * @returns {void}
     */
    handleAdresseClickOutside(event) {
      const conteneur = this.$refs.adresseRef;
      if (conteneur && !conteneur.contains(event.target)) {
        this.showAdresseDropdown = false;
      }
    },

    /**
     * Valide tous les champs obligatoires du formulaire membership.
     * @author Ngoie Steven
     * @returns {boolean} - true si le formulaire est valide, false sinon
     */
    validerFormulaire() {
      this.errors = {};
      let valide = true;

      const champsRequis = [
        ['nom', 'Le nom est requis.'],
        ['prenom', 'Le prénom est requis.'],
        ['email', 'L\'email est requis.'],
        ['adresse', 'L\'adresse est requise.'],
        ['code_postal', 'Le code postal est requis.'],
        ['ville', 'La ville est requise.'],
        ['pays', 'Le pays est requis.'],
        ['telephone', 'Le numéro de téléphone est requis.'],
        ['date_naissance', 'La date de naissance est requise.'],
        ['description', 'La motivation est requise.'],
      ];

      champsRequis.forEach(([champ, message]) => {
        if (!String(this.formulaire[champ] || '').trim()) {
          this.errors[champ] = message;
          valide = false;
        }
      });

      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (this.formulaire.email && !emailRegex.test(this.formulaire.email)) {
        this.errors.email = 'Le format de l\'email est invalide.';
        valide = false;
      }

      if (this.formulaire.telephone) {
        const phoneRegex = /^\d{3}\s\d{3}\s\d{2}\s\d{2}$/;
        if (!phoneRegex.test(this.formulaire.telephone)) {
          this.errors.telephone = 'Format attendu: 079 123 45 67';
          valide = false;
        }
      }

      if (this.formulaire.description && this.formulaire.description.trim().length < 10) {
        this.errors.description = 'La motivation doit contenir au moins 10 caractères.';
        valide = false;
      }

      return valide;
    },

    /**
     * Soumet la demande de membership en l'ajoutant au panier et redirige vers la page paiement.
     * @author Ngoie Steven
     * @returns {Promise<void>}
     */
    async soumettreDemande() {
      // Désormais: on ajoute l'inscription membership au panier (tarif fixe 25 CHF)
      if (!this.validerFormulaire()) {
        return;
      }

      if (this.membershipEnCoursDansPanier) {
        this.messageErreur = 'Votre membership est déjà dans le panier. Validez-le avant d\'en ajouter un nouveau.';
        return;
      }

      this.chargement = true;
      this.messageErreur = '';

      try {
        const cart = this.getCart();

        const uniqueId = `membership_${Date.now()}`;

        const donneesInscription = {
          id_groupe: null,
          participant: [{
            nom: this.formulaire.nom,
            prenom: this.formulaire.prenom,
            email: this.formulaire.email,
          }],
          tarif: 25,
          tarif_base: 25,
          formulaire_membership: { ...this.formulaire },
          type_article: 'membership',
          id_unique: uniqueId,
        };

        const courseDetails = {
          nom_course: 'Inscription à Membership',
          evenement: { nom: 'Membership', couleur_primaire: '#0e0f54' },
          tarif: 25,
        };

        cart.ajouterInscription(donneesInscription, courseDetails);

        // Redirecter vers la page panier pour finaliser le paiement
        this.$router.push({ name: 'Panier' });
      } catch (error) {
        console.error("Erreur lors de l'ajout au panier :", error);
        this.messageErreur = 'Impossible d\'ajouter au panier pour le moment.';
      } finally {
        this.chargement = false;
      }
    },
  },
};
</script>

<style scoped>
input,
textarea {
  transition: all 0.2s ease;
}

input:focus,
textarea:focus {
  border-color: transparent;
}
</style>
