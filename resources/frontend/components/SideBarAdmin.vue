<template>
  <div class="sm:w-64 flex-shrink-0">
    
    <button data-drawer-target="separator-sidebar" data-drawer-toggle="separator-sidebar" aria-controls="separator-sidebar" type="button" class="text-primary-900 bg-transparent box-border border border-transparent hover:bg-secondary focus:ring-4 focus:ring-primary-300 font-medium rounded-lg ms-3 mt-3 text-sm p-2 focus:outline-none inline-flex sm:hidden">
       <span class="sr-only">Ouvrir le menu</span>
       <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M5 7h14M5 12h14M5 17h10"/></svg>
    </button>

    <aside id="separator-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0 bg-primary-300 text-secondary shadow-lg" aria-label="Sidebar">
       
       <button type="button" data-drawer-hide="separator-sidebar" aria-controls="separator-sidebar" class="text-secondary bg-transparent hover:bg-tertiary hover:text-primary rounded-lg text-sm p-1.5 absolute top-3 right-3 inline-flex items-center sm:hidden transition-colors duration-200">
          <svg aria-hidden="true" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
          <span class="sr-only">Fermer le menu</span>
       </button>

       <div class="h-full px-4 pt-16 pb-4 overflow-y-auto">
          <h2 class="px-2 pb-2 font-bold text-secondary text-lg mb-2">Navigation</h2>
          
          <ul class="space-y-1 text-[1rem] leading-[1.5rem]">
             <li>
                <router-link to="/" class="flex items-center px-3 py-2.5 rounded-lg text-secondary hover:bg-tertiary hover:text-primary transition-colors duration-200" active-class="bg-tertiary !text-primary">
                   <svg class="w-5 h-5 opacity-90 transition duration-75" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16v10H4V6Zm0 0v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6M8 20h8M12 16v4"/></svg>
                   <span class="ms-3 font-medium">Tableau de bord</span>
                </router-link>
             </li>

             <li>
                <router-link to="/inscriptions" class="flex items-center px-3 py-2.5 rounded-lg text-secondary hover:bg-tertiary hover:text-primary transition-colors duration-200" active-class="bg-tertiary !text-primary">
                   <svg class="w-5 h-5 opacity-90 transition duration-75" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 4h3a1 1 0 0 1 1 1v15a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h3m0 3h6m-3 5h3m-6 0h.01M12 16h3m-6 0h.01M10 3v4h4V3h-4Z"/></svg>
                   <span class="ms-3 font-medium">Inscriptions</span>
                </router-link>
             </li>
             
             <li>
                <router-link to="/resultats" class="flex items-center px-3 py-2.5 rounded-lg text-secondary hover:bg-tertiary hover:text-primary transition-colors duration-200" active-class="bg-tertiary !text-primary">
                   <svg class="w-5 h-5 opacity-90 transition duration-75" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.083 5.104A4.966 4.966 0 0 1 16.5 6.5M19 14.5a2.5 2.5 0 0 1-2.5 2.5H16a2.5 2.5 0 0 1-2.5-2.5V11a2.5 2.5 0 0 1 2.5-2.5h.5a2.5 2.5 0 0 1 2.5 2.5v3.5Zm-14 0a2.5 2.5 0 0 0 2.5 2.5H8a2.5 2.5 0 0 0 2.5-2.5V11A2.5 2.5 0 0 0 8 8.5h-.5A2.5 2.5 0 0 0 5 11v3.5Z"/></svg>
                   <span class="ms-3 font-medium">Formulaires</span>
                </router-link>
             </li>
          </ul>
       </div>
    </aside>
  </div>
</template>