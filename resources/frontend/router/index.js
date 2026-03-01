import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "../stores/auth";

const routes = [
  // ===== Route par défaut (Racine du site) =====
  {
    path: "/",
    redirect: "/accueil"
  },

  // ===== Routes d'authentification (sans navbar) =====
  {
    path: "/login",
    name: "login",
    component: () => import("../views/Connexion.vue"),
    meta: { guest: true } // TODO (3.1) : rediriger si déjà connecté
  },
  {
    path: "/inscription",
    name: "inscription",
    component: () => import("../views/CreationCompte.vue"),
    meta: { guest: true }
  },

  // ===== Routes de l'application (avec navbar) =====
  {
    path: "/accueil",
    name: "tableau-de-bord participant",
    component: () => import("../views/ParticipantTableauDeBord.vue"),
    meta: { requiresAuth: true }
  },
  {
    path: "/inscriptions",
    name: "Mes inscriptions",
    component: () => import("../views/Inscriptions.vue"),
    meta: { requiresAuth: true }
  },
  {
    path: "/resultats",
    name: "Mes résultats",
    component: () => import("../views/Resultats.vue"),
    meta: { requiresAuth: true }
  },
  {
    path: "/evenements",
    name: "Événements",
    component: () => import("../views/Evenements.vue"),
    meta: { requiresAuth: true }
  },
  {
    path: "/liste-courses",
    name: "ListeCourses",
    component: () => import("../views/ListeCourses.vue")
  },
  {
    path: "/membership",
    name: "Membership",
    component: () => import("../views/Membership.vue"),
    meta: { requiresAuth: true }
  },
  {
    path: "/course-collecte",
    name: "Course de collecte",
    component: () => import("../views/CourseCollecte.vue"),
    meta: { requiresAuth: true }
  },
  {
    path: "/echange-dossard",
    name: "Échange de dossard",
    component: () => import("../views/EchangeDossard.vue"),
    meta: { requiresAuth: true }
  },
  {
    path: "/conditions-utilisation",
    name: "Conditions d'utilisation",
    component: () => import("../views/ConditionsUtilisation.vue"),
    meta: { requiresAuth: true }
  },
  {
    path: "/politique-confidentialite",
    name: "Politique de confidentialité",
    component: () => import("../views/PolitiqueConfidentialite.vue"),
    meta: { requiresAuth: true }
  },
  {
    path: "/protection-donnees",
    name: "Protection des données",
    component: () => import("../views/ProtectionDonnees.vue"),
    meta: { requiresAuth: true }
  },
  {
    path: "/profil",
    name: "Profil",
    component: () => import("../views/ProfilUser.vue"),
    meta: { requiresAuth: true }
  },
  {
    path: "/organisateur/formulaires",
    name: "Formulaires",
    component: () => import("../views/OrganisateurFormulaires.vue"),
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
    path: "/organisateur/evenements",
    name: "OrganisateurEvenements",
    component: () => import("../views/OrganisateurEvenements.vue"),
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
    path: "/organisateur/evenements/:idEvenement/courses",
    name: "OrganisateurCourses",
    component: () => import("../views/OrganisateurCourses.vue"),
    meta: { requiresAuth: true, requiresAdmin: true }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  }
});

// TODO (3.1) : Ajouter ici le guard de navigation pour la gestion des rôles
// router.beforeEach((to, from, next) => { ... })
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  const isAuthenticated = authStore.isAuthenticated();
  const isAdmin = authStore.isAdmin;

  //Redirige si l'utilisateur est déjà connecté lors du /login ou /inscription
  if (to.meta.guest && isAuthenticated) {
    return next(isAdmin ? '/organisateur/evenements' : '/accueil');
  }

  //Blocage d'accès si non connecté à toutes les pages sauf Login/Inscription
  if (!to.meta.guest && !isAuthenticated) {
    return next('/login');
  }

  //Blocage sur les accès aux pages Admin si l'utilisateur n'a pas le rôle
  if (to.meta.requiresAdmin && !isAdmin) {
    return next('/accueil');
  }

  next();
});

export default router;
