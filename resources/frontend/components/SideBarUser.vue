<template>
    <aside
        id="separator-sidebar"
        class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-all duration-300 -translate-x-full sm:translate-x-0 shadow-lg border-r"
        aria-label="Sidebar"
        :class="
            themeStore.primaryColor
                ? ''
                : 'bg-primary-300 text-secondary border-primary-900/20'
        "
        :style="
            themeStore.primaryColor
                ? {
                      backgroundColor: themeStore.primaryColor + '1A',
                      color: '#0e0f54',
                      borderColor: themeStore.primaryColor + '33',
                  }
                : {}
        "
    >
        <div class="h-full px-4 pb-4 overflow-y-auto">
            <ul class="space-y-1 text-[1rem] leading-[1.5rem] mt-4">
                <li>
                    <router-link
                        to="/accueil"
                        class="flex items-center px-3 py-2.5 rounded-lg transition-all duration-200"
                        :class="
                            themeStore.primaryColor
                                ? 'hover:bg-white/50 text-[#0e0f54]'
                                : 'text-secondary hover:bg-tertiary hover:text-primary'
                        "
                        :active-class="
                            themeStore.primaryColor
                                ? 'bg-white shadow-sm font-bold'
                                : 'bg-tertiary !text-primary'
                        "
                    >
                        <Icon
                            icon="lucide:monitor"
                            class="w-5 h-5 opacity-90 transition duration-75"
                        />
                        <span class="ms-3 font-medium">Tableau de bord</span>
                    </router-link>
                </li>

                <li>
                    <router-link
                        to="/evenements"
                        class="flex items-center px-3 py-2.5 rounded-lg transition-all duration-200"
                        :class="
                            themeStore.primaryColor
                                ? 'hover:bg-white/50 text-[#0e0f54]'
                                : 'text-secondary hover:bg-tertiary hover:text-primary'
                        "
                        :active-class="
                            themeStore.primaryColor
                                ? 'bg-white shadow-sm font-bold'
                                : 'bg-tertiary !text-primary'
                        "
                        :style="getLienEvenementsStyle"
                    >
                        <Icon
                            icon="lucide:calendar-days"
                            class="w-5 h-5 opacity-90 transition duration-75"
                        />
                        <span class="ms-3 font-medium"
                            >Liste des évènements</span
                        >
                    </router-link>
                </li>

                <li>
                    <router-link
                        to="/inscriptions"
                        class="flex items-center px-3 py-2.5 rounded-lg transition-all duration-200"
                        :class="
                            themeStore.primaryColor
                                ? 'hover:bg-white/50 text-[#0e0f54]'
                                : 'text-secondary hover:bg-tertiary hover:text-primary'
                        "
                        :active-class="
                            themeStore.primaryColor
                                ? 'bg-white shadow-sm font-bold'
                                : 'bg-tertiary !text-primary'
                        "
                    >
                        <Icon
                            icon="lucide:clipboard-list"
                            class="w-5 h-5 opacity-90 transition duration-75"
                        />
                        <span class="ms-3 font-medium">Mes inscriptions</span>
                    </router-link>
                </li>

                <li>
                    <router-link
                        to="/resultats"
                        class="flex items-center px-3 py-2.5 rounded-lg transition-all duration-200"
                        :class="
                            themeStore.primaryColor
                                ? 'hover:bg-white/50 text-[#0e0f54]'
                                : 'text-secondary hover:bg-tertiary hover:text-primary'
                        "
                        :active-class="
                            themeStore.primaryColor
                                ? 'bg-white shadow-sm font-bold'
                                : 'bg-tertiary !text-primary'
                        "
                    >
                        <Icon
                            icon="lucide:medal"
                            class="w-5 h-5 opacity-90 transition duration-75"
                        />
                        <span class="ms-3 font-medium">Mes résultats</span>
                    </router-link>
                </li>

                <li v-if="hasMembershipAccess">
                    <router-link
                        to="/membership"
                        class="flex items-center px-3 py-2.5 rounded-lg transition-all duration-200"
                        :class="
                            themeStore.primaryColor
                                ? 'hover:bg-white/50 text-[#0e0f54]'
                                : 'text-secondary hover:bg-tertiary hover:text-primary'
                        "
                        :active-class="
                            themeStore.primaryColor
                                ? 'bg-white shadow-sm font-bold'
                                : 'bg-tertiary !text-primary'
                        "
                    >
                        <Icon
                            icon="lucide:id-card"
                            class="w-5 h-5 opacity-90 transition duration-75"
                        />
                        <span class="ms-3 font-medium">Membership</span>
                    </router-link>
                </li>

                <li>
                    <router-link
                        to="/course-collecte"
                        class="flex items-center px-3 py-2.5 rounded-lg transition-all duration-200"
                        :class="
                            themeStore.primaryColor
                                ? 'hover:bg-white/50 text-[#0e0f54]'
                                : 'text-secondary hover:bg-tertiary hover:text-primary'
                        "
                        :active-class="
                            themeStore.primaryColor
                                ? 'bg-white shadow-sm font-bold'
                                : 'bg-tertiary !text-primary'
                        "
                    >
                        <Icon
                            icon="lucide:hand-coins"
                            class="w-5 h-5 opacity-90 transition duration-75"
                        />
                        <span class="ms-3 font-medium">Course de collecte</span>
                    </router-link>
                </li>

                <li>
                    <router-link
                        to="/echange-dossard"
                        class="flex items-center px-3 py-2.5 rounded-lg transition-all duration-200"
                        :class="
                            themeStore.primaryColor
                                ? 'hover:bg-white/50 text-[#0e0f54]'
                                : 'text-secondary hover:bg-tertiary hover:text-primary'
                        "
                        :active-class="
                            themeStore.primaryColor
                                ? 'bg-white shadow-sm font-bold'
                                : 'bg-tertiary !text-primary'
                        "
                    >
                        <Icon
                            icon="lucide:arrow-right-left"
                            class="w-5 h-5 opacity-90 transition duration-75"
                        />
                        <span class="ms-3 font-medium">Céder mon dossard</span>
                    </router-link>
                </li>
            </ul>
        </div>
    </aside>
</template>

<script setup>
/**
 * @fileoverview Composant SideBarUser.
 * @description Barre latérale de navigation dédiée aux utilisateurs participants.
 * @remarks La navigation expose les parcours participants et conserve la cohérence visuelle
 * avec le thème dynamique appliqué par l'utilisateur.
 * @author Ngoie Steven
 */
import { Icon } from "@iconify/vue";
import { useAuthStore } from "../stores/auth";
import { useThemeStore } from "../stores/theme";
import { useRoute } from "vue-router";
import { onMounted, ref, computed } from "vue";
import membershipService from "../services/membershipService";

const authStore = useAuthStore();
const themeStore = useThemeStore();
const route = useRoute();
const hasMembershipAccess = ref(false);

/**
 * Retourne le style du lien "Liste des évènements" en fonction de si on est sur ListeCourses
 * @returns {Object}
 */
const getLienEvenementsStyle = computed(() => {
    // Si on est sur la page ListeCourses et qu'il y a une couleur secondaire d'événement
    if (route.name === "ListeCourses" && themeStore.secondaryColor) {
        return {
            backgroundColor: themeStore.secondaryColor,
        };
    }
    return {};
});

onMounted(async () => {
    if (!authStore.user?.participant) {
        hasMembershipAccess.value = false;
        return;
    }

    try {
        const response = await membershipService.accesMembershipParticipant();
        hasMembershipAccess.value = !!response?.data?.has_access;
    } catch (_error) {
        hasMembershipAccess.value = false;
    }
});
</script>
