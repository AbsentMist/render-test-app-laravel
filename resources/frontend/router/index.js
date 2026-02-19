import { createRouter, createWebHistory } from "vue-router";


const routes = [
  {
    path: "/accueil",
    name: "tableau-de-bord",
    component: () => import("../views/TableauDeBord.vue")
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
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  }
});

export default router;