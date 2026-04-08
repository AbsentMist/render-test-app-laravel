<template>
    <Title :texte="`Tableau de bord : courses ${nomEvenement}`" />
    <div class="p-6">
        <button @click="$router.push('/organisateur/formulaires?onglet=Course')" class="btn-tertiary px-4 py-2 rounded-lg inline-block mb-6">
        Nouveau
        </button>

        <!-- Message d'erreur -->
        <p v-if="erreur" class="text-accent text-label mb-4">{{ erreur }}</p>

        <!-- Chargement -->
        <div v-if="chargement" class="text-body text-center py-8">
        Chargement des courses...
        </div>

        <!-- Tableau -->
        <div v-else class="overflow-x-auto rounded-xl border border-default-medium">
        <table class="w-full text-sm text-left text-body">
            <thead class="bg-neutral-secondary-medium text-heading text-xs uppercase">
            <tr>
                <th class="px-4 py-3">Nom</th>
                <th class="px-4 py-3">Date début</th>
                <th class="px-4 py-3">Date fin</th>
                <th class="px-4 py-3 text-center">Actif</th>
                <th class="px-4 py-3 text-center">Interne</th>
                <th class="px-4 py-3">Action</th>
            </tr>
            </thead>
            <tbody>
            <tr v-if="courses.length === 0">
                <td colspan="6" class="text-center px-4 py-6 text-body">
                Aucune course trouvée.
                </td>
            </tr>
            <tr
                v-for="course in courses"
                :key="course.id"
                class="border-t border-default-medium hover:bg-neutral-secondary-medium transition-colors"
            >
                <td class="px-4 py-3 font-medium text-heading">{{ course.nom }}</td>

                <!-- Date début (TODO 9.2) -->
                <td class="px-4 py-3">{{ formaterDate(course.debut_inscription) }}</td>

                <!-- Date fin (TODO 9.2) -->
                <td class="px-4 py-3">{{ formaterDate(course.fin_inscription) }}</td>

                <!-- Actif -->
                <td class="px-4 py-3 text-center">
                <Icon v-if="course.is_actif" icon="mdi:check" class="w-5 h-5 text-green-500 mx-auto" />
                <Icon v-else icon="mdi:close" class="w-5 h-5 text-accent mx-auto" />
                </td>

                <!-- Interne -->
                <td class="px-4 py-3 text-center">
                <Icon v-if="course.is_interne" icon="mdi:check" class="w-5 h-5 text-green-500 mx-auto" />
                <Icon v-else icon="mdi:close" class="w-5 h-5 text-accent mx-auto" />
                </td>

                <!-- Actions -->
                <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                    <button
                    @click="modifierCourse(course)"
                    class="p-1.5 rounded-lg text-primary hover:bg-tertiary transition-colors"
                    title="Modifier"
                    >
                    <Icon icon="lucide:square-pen" class="w-4 h-4" />
                    </button>

                    <button
                    @click="confirmerSuppression(course)"
                    class="p-1.5 rounded-lg text-accent hover:bg-red-50 transition-colors"
                    title="Supprimer"
                    >
                    <Icon icon="lucide:trash-2" class="w-4 h-4" />
                    </button>
                </div>
                </td>
            </tr>
            </tbody>
        </table>
        </div>

        <PopupConfirmation
            v-if="courseASupprimer"
            icon="mdi:alert-circle-outline"
            :message="`Voulez-vous vraiment supprimer la course ${courseASupprimer.nom} ? Cette action est irréversible.`"
            @confirm="supprimerCourse"
            @cancel="courseASupprimer = null"
        />
  </div>
</template>

<script>
/**
 * @fileoverview Vue OrganisateurCourses.
 * @description Liste des courses d'un évènement avec actions d'édition et de suppression.
 * @remarks Cette vue sert de tableau de gestion rapide avant ouverture du formulaire course.
 */
import Title from '../components/Title.vue';
import { Icon } from '@iconify/vue';
import PopupConfirmation from '../components/PopupConfirmation.vue';
import courseOrganisateurService from '../services/courseOrganisateurService'
import evenementOrganisateurService from '../services/evenementOrganisateurService';

export default {
    components: {
        Title,
        Icon,
        PopupConfirmation,
    },
    computed: {
        /**
         * Identifiant de l'évènement parent de la liste affichée.
         * @returns {string}
         */
        idEvenement() {
        return this.$route.params.idEvenement
        }
    },
    data() {
        return {
        courses: [],
        nomEvenement: "",
        chargement: true,
        erreur: '',
        courseASupprimer: null
        }
    },
    methods: {
        /**
         * Formate une date en locale suisse.
         * @param {string} dateString
         * @returns {string}
         */
        formaterDate(dateString) {
            if (!dateString) return '—'; // Si pas de date
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-CH', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        },
        /**
         * Charge toutes les courses de l'évènement sélectionné.
         * @returns {Promise<void>}
         */
        async chargerCourses() {
            this.chargement = true
            this.erreur = ''
            try {
                const response = await courseOrganisateurService.getAllCourses(this.idEvenement)
                this.courses = response.data?.courses ?? []
            } catch (e) {
                this.erreur = 'Impossible de charger les courses.'
            } finally {
                this.chargement = false
            }
        },
        /**
         * Redirige vers le formulaire course en mode édition.
         * @param {Object} course
         * @returns {void}
         */
        modifierCourse(course) {
        this.$router.push(`/organisateur/formulaires?onglet=Course&id=${course.id}&idEvenement=${this.idEvenement}`);
        },
        /**
         * Ouvre la confirmation de suppression d'une course.
         * @param {Object} course
         * @returns {void}
         */
        confirmerSuppression(course) {
        this.courseASupprimer = course
        },
        /**
         * Supprime la course confirmée puis met à jour la liste locale.
         * @returns {Promise<void>}
         */
        async supprimerCourse() {
        try {
            await courseOrganisateurService.deleteCourse(this.courseASupprimer.id)
            this.courses = this.courses.filter(c => c.id !== this.courseASupprimer.id)
            this.courseASupprimer = null
        } catch (e) {
            this.erreur = 'Impossible de supprimer cette course.'
            this.courseASupprimer = null
        }
        }
    },
    /**
     * Charge les courses et le nom de l'évènement au montage.
     * @returns {Promise<void>}
     */
    async mounted() {
        await this.chargerCourses()
        
        try{
            const response = await evenementOrganisateurService.getEvenement(this.idEvenement);
            this.nomEvenement = response.data.nom;
            console.log(response.data);
        } catch(e) {
            console.log("L'évènement n' a pas pu être récupéré: ", e);
        }
    }
}
</script>