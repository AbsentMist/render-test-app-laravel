import { createRouter, createWebHistory } from "vue-router";

const routes = [
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
    component: () => import("../views/ParticipantTableauDeBord.vue")
  },
  {
    path: "/inscriptions",
    name: "Mes inscriptions",
    component: () => import("../views/Inscriptions.vue")
  },
  {
    path: "/resultats",
    name: "Mes résultats",
    component: () => import("../views/Resultats.vue")
  },
  {
    path: "/evenements",
    name: "Événements",
    component: () => import("../views/Evenements.vue")
  },
  {
    path: "/membership",
    name: "Membership",
    component: () => import("../views/Membership.vue")
  },
  {
    path: "/course-collecte",
    name: "Course de collecte",
    component: () => import("../views/CourseCollecte.vue")
  },
  {
    path: "/echange-dossard",
    name: "Échange de dossard",
    component: () => import("../views/EchangeDossard.vue")
  },
  {
    path: "/conditions-utilisation",
    name: "Conditions d'utilisation",
    component: () => import("../views/ConditionsUtilisation.vue")
  },
  {
    path: "/politique-confidentialite",
    name: "Politique de confidentialité",
    component: () => import("../views/PolitiqueConfidentialite.vue")
  },
  {
    path: "/protection-donnees",
    name: "Protection des données",
    component: () => import("../views/ProtectionDonnees.vue")
  },
  {
    path: "/profil",
    name: "Profil",
    component: () => import("../views/ProfilUser.vue")
  },
  {
    path: "/organisateur/formulaires",
    name: "Formulaires",
    component: () => import("../views/OrganisateurFormulaires.vue")
  },
  {
  path: "/organisateur/evenements",
  name: "OrganisateurEvenements",
  component: () => import("../views/OrganisateurEvenements.vue")
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

export default router;