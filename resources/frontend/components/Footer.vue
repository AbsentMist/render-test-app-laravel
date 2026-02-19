<template>
  <footer class="w-full pt-12 pb-6 mt-auto">
    <div class="w-3/4 max-w-3xl mx-auto border-t-[1.5px] border-primary opacity-20 mb-6"></div>

    <div class="flex flex-col items-center justify-center gap-4">
      <p class="text-primary font-semibold text-center text-[15px]">
        2025 @ Runningeneva Association
      </p>

      <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-16 text-primary text-sm font-medium text-center">
        <router-link to="/conditions-utilisation" class="hover:text-primary-300 hover:underline transition-all">
          Condition d'utilisation
        </router-link>
        
        <router-link to="/politique-confidentialite" class="hover:text-primary-300 hover:underline transition-all">
          Politique de<br class="hidden sm:block" /> confidentialité
        </router-link>
        
        <router-link to="/protection-donnees" class="hover:text-primary-300 hover:underline transition-all">
          Politique de protection<br class="hidden sm:block" /> des données
        </router-link>
      </div>
    </div>
  </footer>
</template>