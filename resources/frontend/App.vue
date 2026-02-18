<template>
  <div class="min-h-screen">
    <nav class="p-4 bg-white shadow-sm flex gap-4">
      <router-link to="/" class="text-blue-600 font-bold">Tableau de bord</router-link>
      <router-link to="/inscriptions" class="text-gray-600 hover:text-blue-600">Mes inscriptions</router-link>
      <router-link to="/resultats" class="text-gray-600 hover:text-blue-600">Mes résultats</router-link>
    </nav>

    <main>
      <router-view />
    </main>
  </div>
</template>