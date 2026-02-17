import { createRouter, createWebHistory } from "vue-router";


const routes = [
  {
    path: "/",
    name: "tableau-de-bord",
    component: () => import("../views/TableauDeBordLayout.vue")
  },
  {
    path: "/inscriptions",
    name: "Mes inscriptions",
    component: () => import("../views/InscriptionsLayout.vue")
  },
  {
    path: "/resultats",
    name: "Mes résultats",
    component: () => import("../views/ResultatsLayout.vue")
  },
  /* POUR LES ROUTES PARENTS-ENFANTS (ex: /evenement/accueil et /evenement/courses)
  {
  path: '/evenement',
  component: () => import('../views/EvenementLayout.vue'), // Le "cadre" avec une <router-view>
  children: [
    {
      path: 'accueil', // URL finale : /evenement/accueil
      name: 'evenement-accueil',
      component: () => import('../views/evenement/Accueil.vue')
    },
    {
      path: 'courses', // URL finale : /evenement/courses
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

export default router;