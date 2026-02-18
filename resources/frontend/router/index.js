import { createRouter, createWebHistory } from "vue-router";

const routes = [
  // ===== Routes d'authentification (sans navbar) =====
  {
    path: "/login",
    name: "login",
    component: () => import("../views/LoginView.vue"),
    meta: { guest: true } // TODO (3.1) : rediriger si déjà connecté
  },
  {
    path: "/inscription",
    name: "inscription",
    component: () => import("../views/RegisterView.vue"),
    meta: { guest: true }
  },

  // ===== Routes de l'application (avec navbar) =====
  {
    path: "/",
    name: "tableau-de-bord",
    component: () => import("../views/TableauDeBordLayout.vue"),
    // meta: { requiresAuth: true } // TODO (3.1) : décommenter quand l'auth sera faite
  },
  {
    path: "/inscriptions",
    name: "Mes inscriptions",
    component: () => import("../views/InscriptionsLayout.vue"),
  },
  {
    path: "/resultats",
    name: "Mes résultats",
    component: () => import("../views/ResultatsLayout.vue"),
  },

  /* POUR LES ROUTES PARENTS-ENFANTS (ex: /evenement/accueil et /evenement/courses)
  {
  path: '/evenement',
  component: () => import('../views/EvenementLayout.vue'),
  children: [
    {
      path: 'accueil',
      name: 'evenement-accueil',
      component: () => import('../views/evenement/Accueil.vue')
    },
    {
      path: 'courses',
      name: 'evenement-courses',
      component: () => import('../views/evenement/Courses.vue')
    }
  ]
  }*/
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