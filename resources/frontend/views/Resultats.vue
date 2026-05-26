<template>
    <Title texte="Mes résultats" />

    <div class="p-6 space-y-8">
        <!-- Chargement -->
        <div v-if="chargement" class="text-body text-center py-12">
            Chargement de vos résultats...
        </div>

        <!-- Aucun résultat -->
        <div
            v-else-if="resultats.length === 0"
            class="text-center py-16 text-gray-400 rounded-xl border border-default-medium"
        >
            <Icon
                icon="mdi:medal-outline"
                class="w-12 h-12 mx-auto mb-3 opacity-40"
            />
            <p class="text-sm font-medium">
                Aucun résultat disponible pour le moment.
            </p>
            <p class="text-xs mt-1">
                Vos résultats apparaîtront ici après chaque course.
            </p>
        </div>

        <template v-else>
            <!-- ===== SECTION : Mes résultats ===== -->
            <section>
                <h2
                    class="text-sm font-semibold text-heading uppercase tracking-wider mb-4 flex items-center gap-2"
                >
                    <Icon icon="mdi:medal-outline" class="w-4 h-4" />
                    Mes résultats
                </h2>

                <div class="space-y-3">
                    <div
                        v-for="resultat in resultatsAffiches"
                        :key="resultat.id"
                        class="rounded-xl overflow-hidden border border-default-medium flex flex-col sm:flex-row shadow-sm hover:shadow-md transition-shadow"
                    >
                        <!-- Vignette gauche colorée (couleur de l'événement) -->
                        <div
                            class="w-full sm:w-44 h-24 sm:h-auto flex flex-col items-center justify-center shrink-0 px-4 gap-1"
                            :style="{
                                backgroundColor:
                                    resultat.couleur_primaire || '#0e0f54',
                            }"
                        >
                            <!-- Logo ou nom événement -->
                            <img
                                v-if="resultat.logo"
                                :src="getLogoSource(resultat.logo)"
                                class="w-16 h-10 object-contain"
                            />
                            <p
                                v-else
                                class="text-xs font-bold text-center leading-tight"
                                :style="{
                                    color:
                                        resultat.couleur_secondaire ||
                                        '#ffffff',
                                }"
                            >
                                {{ resultat.evenement_nom }}
                            </p>
                        </div>

                        <!-- Contenu principal -->
                        <div
                            class="flex-1 bg-white p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                        >
                            <div>
                                <p class="font-semibold text-heading text-sm">
                                    {{ resultat.course_nom }}
                                </p>
                                <p class="text-body text-xs mt-0.5">
                                    {{ resultat.evenement_nom }}
                                    <span class="mx-1 text-gray-300">·</span>
                                    {{
                                        resultat.date_course
                                            ? formatDate(resultat.date_course)
                                            : "—"
                                    }}
                                </p>

                                <!-- Badge progression -->
                                <p
                                    v-if="getProgression(resultat)"
                                    class="font-semibold text-xs flex items-center gap-1 mt-1"
                                    :class="
                                        getProgression(resultat).positif
                                            ? 'text-green-600'
                                            : 'text-red-500'
                                    "
                                >
                                    <Icon
                                        :icon="
                                            getProgression(resultat).positif
                                                ? 'mdi:arrow-up'
                                                : 'mdi:arrow-down'
                                        "
                                        class="w-3.5 h-3.5"
                                    />
                                    {{ getProgression(resultat).texte }}
                                </p>
                            </div>

                            <!-- Stats -->
                            <div
                                class="flex items-center gap-5 text-sm shrink-0"
                            >
                                <!-- Badge position -->
                                <!-- Médaille pour top 3, sinon rien -->
                                <div
                                    class="w-11 h-11 flex items-center justify-center shrink-0 text-3xl"
                                >
                                    <template v-if="resultat.position === 1"
                                        >🥇</template
                                    >
                                    <template
                                        v-else-if="resultat.position === 2"
                                        >🥈</template
                                    >
                                    <template
                                        v-else-if="resultat.position === 3"
                                        >🥉</template
                                    >
                                    <template v-else-if="!resultat.position">
                                        <Icon
                                            icon="mdi:flag-off-outline"
                                            class="w-4 h-4 text-gray-400"
                                        />
                                    </template>
                                </div>

                                <div class="text-center">
                                    <p class="text-xs text-body">Dossard</p>
                                    <p class="font-semibold text-heading">
                                        {{ resultat.dossard ?? "—" }}
                                    </p>
                                </div>

                                <div class="text-center">
                                    <p class="text-xs text-body">Temps</p>
                                    <p
                                        class="font-semibold text-heading font-mono text-sm"
                                    >
                                        {{
                                            resultat.temps_course
                                                ? formatTemps(
                                                      resultat.temps_course,
                                                  )
                                                : "DNS/DNF"
                                        }}
                                    </p>
                                </div>

                                <div class="text-center">
                                    <p class="text-xs text-body">Position</p>
                                    <p class="font-semibold text-heading">
                                        {{
                                            resultat.position
                                                ? `${resultat.position}e`
                                                : "—"
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="resultats.length > 4" class="text-center mt-3">
                    <button
                        @click="afficherTout = !afficherTout"
                        class="text-xs font-semibold text-primary hover:underline flex items-center gap-1 mx-auto"
                    >
                        <Icon
                            :icon="
                                afficherTout
                                    ? 'mdi:chevron-up'
                                    : 'mdi:chevron-down'
                            "
                            class="w-4 h-4"
                        />
                        {{
                            afficherTout
                                ? "Afficher moins"
                                : `Afficher les ${resultats.length - 4} autres résultats`
                        }}
                    </button>
                </div>
            </section>

            <!-- ===== SECTION : Évolution des performances ===== -->
            <section v-if="coursesAvecEvolution.length > 0">
                <h2
                    class="text-sm font-semibold text-heading uppercase tracking-wider mb-4 flex items-center gap-2"
                >
                    <Icon icon="mdi:chart-line" class="w-4 h-4" />
                    Évolution de vos performances
                </h2>

                <!-- Dropdown événement + boutons courses sur la même ligne -->
                <div class="flex flex-wrap items-center gap-3 mb-5">
                    <select
                        v-model="evenementSelectionne"
                        @change="
                            courseSelectionnee = coursesDeEvenement[0] ?? null
                        "
                        class="rounded-xl border border-default-medium px-3 py-2 text-sm text-heading bg-white focus:outline-none focus:ring-2 focus:ring-tertiary shrink-0"
                    >
                        <option
                            v-for="ev in evenements"
                            :key="ev.nom"
                            :value="ev.nom"
                        >
                            {{ ev.nom }}
                        </option>
                    </select>

                    <div class="flex gap-2 flex-wrap">
                        <button
                            v-for="course in coursesDeEvenement"
                            :key="course"
                            @click="courseSelectionnee = course"
                            class="px-3 py-1.5 rounded-full text-xs font-semibold transition-colors"
                            :class="
                                courseSelectionnee === course
                                    ? 'bg-primary text-white'
                                    : 'bg-neutral-secondary-medium text-heading hover:bg-gray-200'
                            "
                        >
                            {{ course }}
                        </button>
                    </div>
                </div>

                <!-- Graphique -->
                <div
                    v-if="donneesGraphique.length > 0"
                    class="bg-white rounded-xl border border-default-medium p-5"
                >
                    <!-- Meilleure perf badge -->
                    <div
                        v-if="meilleurePerf"
                        class="flex items-center gap-2 mb-4 bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-2.5"
                    >
                        <Icon
                            icon="mdi:trophy-outline"
                            class="w-4 h-4 text-yellow-500 shrink-0"
                        />
                        <p class="text-xs text-yellow-700">
                            <span class="font-semibold"
                                >Meilleure performance :</span
                            >
                            {{ meilleurePerf.temps }} le
                            {{ meilleurePerf.date }}
                            <span
                                v-if="meilleurePerf.progression"
                                class="ml-2 text-green-600 font-semibold"
                            >
                                🔥 {{ meilleurePerf.progression }} de mieux que
                                l'édition précédente !
                            </span>
                        </p>
                    </div>

                    <!-- SVG pleine largeur -->
                    <div class="w-full" ref="graphiqueContainer">
                        <svg
                            :viewBox="`0 0 ${SVG_FIXED_WIDTH} ${svgHeight}`"
                            preserveAspectRatio="xMidYMid meet"
                            class="w-full"
                            :style="`height: ${svgHeight}px`"
                        >
                            <!-- Grille horizontale -->
                            <line
                                v-for="i in 5"
                                :key="'grid-' + i"
                                :x1="paddingLeft"
                                :y1="
                                    paddingTop +
                                    ((svgHeight - paddingTop - paddingBottom) /
                                        4) *
                                        (i - 1)
                                "
                                :x2="SVG_FIXED_WIDTH - paddingRight"
                                :y2="
                                    paddingTop +
                                    ((svgHeight - paddingTop - paddingBottom) /
                                        4) *
                                        (i - 1)
                                "
                                stroke="#e5e7eb"
                                stroke-width="1"
                            />

                            <!-- Labels temps (axe Y) -->
                            <text
                                v-for="(label, i) in labelsY"
                                :key="'labely-' + i"
                                :x="paddingLeft - 8"
                                :y="
                                    paddingTop +
                                    ((svgHeight - paddingTop - paddingBottom) /
                                        4) *
                                        i +
                                    4
                                "
                                text-anchor="end"
                                font-size="11"
                                fill="#9ca3af"
                            >
                                {{ label }}
                            </text>

                            <!-- Ligne du graphique -->
                            <polyline
                                :points="pointsLigne"
                                fill="none"
                                stroke="#0e0f54"
                                stroke-width="2.5"
                                stroke-linejoin="round"
                                stroke-linecap="round"
                            />

                            <!-- Zone sous la courbe -->
                            <polygon
                                :points="pointsZone"
                                fill="#0e0f54"
                                fill-opacity="0.06"
                            />

                            <!-- Points -->
                            <g
                                v-for="(point, i) in pointsGraphique"
                                :key="'point-' + i"
                            >
                                <circle
                                    :cx="point.x"
                                    :cy="point.y"
                                    r="4"
                                    fill="white"
                                    stroke="#0e0f54"
                                    stroke-width="2"
                                />
                                <text
                                    :x="point.x"
                                    :y="point.y - 12"
                                    text-anchor="middle"
                                    font-size="11"
                                    font-weight="bold"
                                    fill="#0e0f54"
                                >
                                    {{ point.temps }}
                                </text>
                                <text
                                    :x="point.x"
                                    :y="svgHeight - paddingBottom + 16"
                                    text-anchor="middle"
                                    font-size="11"
                                    fill="#9ca3af"
                                >
                                    {{ point.date }}
                                </text>
                            </g>
                        </svg>
                    </div>

                    <p class="text-xs text-gray-400 text-center mt-2">
                        {{ donneesGraphique.length }} participation(s) sur cette
                        course
                    </p>
                </div>

                <!-- Pas assez de données -->
                <div
                    v-else
                    class="text-center py-8 text-gray-400 rounded-xl border border-default-medium"
                >
                    <Icon
                        icon="mdi:chart-timeline-variant"
                        class="w-8 h-8 mx-auto mb-2 opacity-40"
                    />
                    <p class="text-sm">
                        Participez à cette course plusieurs fois pour voir votre
                        évolution.
                    </p>
                </div>
            </section>
        </template>
    </div>
</template>

<script>
import Title from "../components/Title.vue";
import { Icon } from "@iconify/vue";
import resultatService from "../services/resultatService";

const PADDING_LEFT = 45;
const PADDING_RIGHT = 15;
const PADDING_TOP = 30;
const PADDING_BOTTOM = 25;
const SVG_HEIGHT = 160;
const SVG_FIXED_WIDTH = 800;

export default {
    name: "Resultats",
    components: { Title, Icon },

    data() {
        return {
            resultats: [],
            chargement: true,
            paddingLeft: PADDING_LEFT,
            paddingRight: PADDING_RIGHT,
            paddingTop: PADDING_TOP,
            paddingBottom: PADDING_BOTTOM,
            svgHeight: SVG_HEIGHT,
            SVG_FIXED_WIDTH,
            evenementSelectionne: null,
            courseSelectionnee: null,
            afficherTout: false,
        };
    },

    computed: {
        /**
         * Retourne les courses groupées avec au moins une progression disponible pour la comparaison.
         * @author Guillermet Jean-Daniel
         * @returns {Array} Liste des courses avec évolution
         */
        coursesAvecEvolution() {
            const map = {};
            for (const r of this.resultats) {
                if (!r.temps_course) continue;
                const key =
                    this.nomSansAnnee(r.evenement_nom) + " — " + r.course_nom;
                if (!map[key]) map[key] = { nom: key, resultats: [] };
                map[key].resultats.push(r);
            }
            return Object.values(map).filter((c) => c.resultats.length >= 1);
        },

        /**
         * Retourne la liste des événements uniques avec leurs courses associées.
         * @author Guillermet Jean-Daniel
         * @returns {Array} Tableau des événements avec leurs courses
         */
        evenements() {
            const map = {};
            for (const r of this.resultats) {
                if (!r.temps_course) continue;
                const nom = this.nomSansAnnee(r.evenement_nom);
                if (!map[nom]) map[nom] = { nom, courses: new Set() };
                map[nom].courses.add(r.course_nom);
            }
            return Object.values(map).map((e) => ({
                nom: e.nom,
                courses: [...e.courses],
            }));
        },

        /**
         * Retourne les courses de l'événement sélectionné.
         * @author Guillermet Jean-Daniel
         * @returns {Array} Liste des noms de courses
         */
        coursesDeEvenement() {
            if (!this.evenementSelectionne) return [];
            const ev = this.evenements.find(
                (e) => e.nom === this.evenementSelectionne,
            );
            return ev ? ev.courses : [];
        },

        /**
         * Retourne les données du graphique (résultats filtrés par événement et course).
         * @author Guillermet Jean-Daniel
         * @returns {Array} Résultats triés par date de course
         */
        donneesGraphique() {
            if (!this.evenementSelectionne || !this.courseSelectionnee)
                return [];
            return this.resultats
                .filter(
                    (r) =>
                        this.nomSansAnnee(r.evenement_nom) ===
                            this.evenementSelectionne &&
                        r.course_nom === this.courseSelectionnee &&
                        r.temps_course,
                )
                .sort(
                    (a, b) => new Date(a.date_course) - new Date(b.date_course),
                );
        },

        /**
         * Convertit les temps du graphique en secondes pour les calculs.
         * @author Guillermet Jean-Daniel
         * @returns {Array<number>} Tableau des temps en secondes
         */
        tempsEnSecondes() {
            return this.donneesGraphique.map((r) =>
                this.tempsToSecondes(r.temps_course),
            );
        },

        /**
         * Calcule les positions x,y des points du graphique SVG avec les temps et dates.
         * @author Guillermet Jean-Daniel
         * @returns {Array<Object>} Points du graphique avec x, y, temps et date
         */
        pointsGraphique() {
            if (this.donneesGraphique.length === 0) return [];
            const secondes = this.tempsEnSecondes;
            const min = Math.min(...secondes);
            const max = Math.max(...secondes);
            const range = max - min || 1;
            const w = SVG_FIXED_WIDTH - PADDING_LEFT - PADDING_RIGHT;
            const h = SVG_HEIGHT - PADDING_TOP - PADDING_BOTTOM;
            const step =
                this.donneesGraphique.length > 1
                    ? w / (this.donneesGraphique.length - 1)
                    : w / 2;

            return this.donneesGraphique.map((r, i) => ({
                x:
                    PADDING_LEFT +
                    (this.donneesGraphique.length > 1 ? i * step : w / 2),
                y: PADDING_TOP + ((secondes[i] - min) / range) * h,
                temps: this.formatTemps(r.temps_course),
                date: r.date_course
                    ? new Date(r.date_course).getFullYear().toString()
                    : "—",
            }));
        },

        /**
         * Génère la chaîne de points pour la polyline du graphique SVG.
         * @author Guillermet Jean-Daniel
         * @returns {string} Coordonnées au format SVG "x,y x,y ..."
         */
        pointsLigne() {
            return this.pointsGraphique.map((p) => `${p.x},${p.y}`).join(" ");
        },

        /**
         * Génère la zone sous la courbe pour remplir le graphique SVG.
         * @author Guillermet Jean-Daniel
         * @returns {string} Coordonnées au format SVG polygon
         */
        pointsZone() {
            if (this.pointsGraphique.length === 0) return "";
            const first = this.pointsGraphique[0];
            const last = this.pointsGraphique[this.pointsGraphique.length - 1];
            const base = SVG_HEIGHT - PADDING_BOTTOM;
            return `${first.x},${base} ${this.pointsLigne} ${last.x},${base}`;
        },

        /**
         * Retourne les labels de temps pour l'axe Y du graphique.
         * @author Guillermet Jean-Daniel
         * @returns {Array<string>} Labels formatés au format XX:XX
         */
        labelsY() {
            if (this.tempsEnSecondes.length === 0) return [];
            const min = Math.min(...this.tempsEnSecondes);
            const max = Math.max(...this.tempsEnSecondes);
            const range = max - min || 60;
            return [0, 1, 2, 3, 4].map((i) =>
                this.secondesToTemps(Math.round(min + (range / 4) * i)),
            );
        },

        /**
         * Retourne la meilleure performance avec sa progression par rapport à l'édition précédente.
         * @author Guillermet Jean-Daniel
         * @returns {Object|null} Objet avec temps, date et progression, ou null si pas de données
         */
        meilleurePerf() {
            if (this.donneesGraphique.length === 0) return null;
            const meilleur = this.donneesGraphique.reduce((best, r) =>
                this.tempsToSecondes(r.temps_course) <
                this.tempsToSecondes(best.temps_course)
                    ? r
                    : best,
            );
            const idx = this.donneesGraphique.indexOf(meilleur);
            let progression = null;
            if (idx > 0) {
                const diff =
                    this.tempsToSecondes(
                        this.donneesGraphique[idx - 1].temps_course,
                    ) - this.tempsToSecondes(meilleur.temps_course);
                if (diff > 0) progression = this.secondesToTemps(diff);
            }
            return {
                temps: this.formatTemps(meilleur.temps_course),
                date: meilleur.date_course
                    ? this.formatDate(meilleur.date_course)
                    : "—",
                progression,
            };
        },
        /**
         * Retourne les résultats affichés selon le paramètre d'affichage (tout ou premiers 4).
         * @author Guillermet Jean-Daniel
         * @returns {Array} Résultats à afficher
         */
        resultatsAffiches() {
            return this.afficherTout
                ? this.resultats
                : this.resultats.slice(0, 4);
        },
    },

    async mounted() {
        await this.chargerResultats();
    },

    methods: {
        /**
         * Charge la liste des résultats depuis l'API.
         * @author Guillermet Jean-Daniel
         * @async
         * @returns {Promise<void>}
         */
        async chargerResultats() {
            this.chargement = true;
            try {
                const res = await resultatService.mesResultats();
                this.resultats = res.data;
                if (this.evenements.length > 0) {
                    this.evenementSelectionne = this.evenements[0].nom;
                    this.courseSelectionnee =
                        this.evenements[0].courses[0] ?? null;
                }
            } catch (e) {
                console.error("Erreur chargement résultats:", e);
            } finally {
                this.chargement = false;
            }
        },

        /**
         * Convertit le logo en URL d'image valide (data URI ou base64).
         * @author Guillermet Jean-Daniel
         * @param {string} logo - Le logo en base64 ou data URI
         * @returns {string|null} URL valide ou null
         */
        getLogoSource(logo) {
            if (!logo) return null;
            return logo.startsWith("data:")
                ? logo
                : `data:image/png;base64,${logo}`;
        },

        /**
         * Calcule la progression entre deux résultats successifs de la même course.
         * @author Guillermet Jean-Daniel
         * @param {Object} resultat - Le résultat à analyser
         * @returns {Object|null} Objet avec positif et texte, ou null si pas de progression
         */
        getProgression(resultat) {
            if (!resultat.temps_course) return null;
            const memesCourses = this.resultats
                .filter(
                    (r) =>
                        r.course_nom === resultat.course_nom && r.temps_course,
                )
                .sort(
                    (a, b) => new Date(a.date_course) - new Date(b.date_course),
                );
            const idx = memesCourses.findIndex((r) => r.id === resultat.id);
            if (idx <= 0) return null;
            const diff =
                this.tempsToSecondes(memesCourses[idx - 1].temps_course) -
                this.tempsToSecondes(resultat.temps_course);
            if (diff === 0) return null;
            return {
                positif: diff > 0,
                texte: `${this.secondesToTemps(Math.abs(diff))} ${diff > 0 ? "de mieux" : "de moins bien"}`,
            };
        },

        /**
         * Convertit un temps au format HH:MM:SS ou MM:SS en secondes.
         * @author Guillermet Jean-Daniel
         * @param {string} temps - Le temps au format HH:MM:SS ou MM:SS
         * @returns {number} Nombre de secondes
         */
        tempsToSecondes(temps) {
            if (!temps) return 0;
            const p = temps.split(":").map(Number);
            return p.length === 3
                ? p[0] * 3600 + p[1] * 60 + p[2]
                : p[0] * 60 + p[1];
        },

        /**
         * Convertit un nombre de secondes en format XhYYmZZs.
         * @author Guillermet Jean-Daniel
         * @param {number} sec - Nombre de secondes
         * @returns {string} Temps formaté
         */
        secondesToTemps(sec) {
            const h = Math.floor(sec / 3600);
            const m = Math.floor((sec % 3600) / 60);
            const s = sec % 60;
            if (h > 0) return `${h}h${String(m).padStart(2, "0")}m`;
            return `${m}m${String(s).padStart(2, "0")}s`;
        },

        /**
         * Formate un temps pour l'affichage lisible (YYmZZs ou YhZZmZZs).
         * @author Guillermet Jean-Daniel
         * @param {string} temps - Le temps au format HH:MM:SS ou MM:SS
         * @returns {string} Temps formaté pour affichage
         */
        formatTemps(temps) {
            if (!temps) return "—";
            const p = temps.split(":");
            if (p.length === 3) {
                const h = parseInt(p[0]);
                return h > 0
                    ? `${h}h${p[1]}m${p[2]}s`
                    : `${parseInt(p[1])}m${p[2]}s`;
            }
            return temps;
        },

        /**
         * Formate une date au format JJ.MM.AAAA (format suisse).
         * @author Guillermet Jean-Daniel
         * @param {string} dateStr - La date ISO à formater
         * @returns {string} Date formatée
         */
        formatDate(dateStr) {
            return new Date(dateStr).toLocaleDateString("fr-CH", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
            });
        },

        /**
         * Retourne les classes CSS du badge pour une position donnée.
         * @author Guillermet Jean-Daniel
         * @param {number} position - La position à formatter
         * @returns {string} Classes CSS pour le badge
         */
        badgePosition(position) {
            if (!position) return "bg-gray-100 text-gray-400";
            if (position === 1) return "bg-yellow-100 text-yellow-600";
            if (position === 2) return "bg-gray-200 text-gray-600";
            if (position === 3) return "bg-orange-100 text-orange-600";
            return "bg-neutral-secondary-medium text-heading";
        },

        /**
         * Supprime l'année à la fin d'un nom d'événement.
         * @author Guillermet Jean-Daniel
         * @param {string} nom - Le nom de l'événement
         * @returns {string} Nom sans l'année
         */
        nomSansAnnee(nom) {
            return nom ? nom.replace(/\s*\d{4}\s*$/, "").trim() : nom;
        },
    },
};
</script>
