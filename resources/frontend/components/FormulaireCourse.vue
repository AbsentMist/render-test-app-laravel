<template>
    <div class="bg-secondary rounded-b-base rounded-tr-base p-6">
        <IndicateurEtapes
            :steps="formulaireEtapesLabels"
            :currentStep="etapesActives.indexOf(etape) + 1"
        />

        <div v-if="etape == formulaireEtape.GENERAL">
            <h1 class="text-subtitle my-4">
                {{ isEditMode ? "Modifier la course" : "Créer une course" }}
            </h1>

            <div class="flex justify-between items-center">
                <label for="dropdown" class="text-sm font-medium text-heading"
                    >Evènement <span class="text-accent">*</span></label
                >
                <div class="relative">
                    <button
                        data-dropdown-toggle="dropdownEvent"
                        class="inline-flex items-center justify-center shadow-xs font-medium text-sm px-4 py-2.5"
                        :class="[
                            courseData.event.nom || courseData.event.name
                                ? 'border-b-2 text-primary border-tertiary hover:bg-gray-100 rounded-t-base'
                                : 'hover:bg-primary-300 text-white bg-primary-900 shadow-xs font-medium rounded-base text-sm px-4 py-2.5',
                        ]"
                        type="button"
                    >
                        <span>
                            {{
                                courseData.event.nom ||
                                courseData.event.name ||
                                "Sélectionner un évènement"
                            }}
                        </span>
                        <Icon icon="mdi:chevron-down" class="ml-2 w-6 h-6" />
                    </button>
                    <div
                        id="dropdownEvent"
                        class="hidden absolute left-0 mt-1 z-10 bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44"
                    >
                        <ul class="p-2 text-sm text-body font-medium">
                            <li
                                v-for="evenement in evenements"
                                :key="evenement.id"
                            >
                                <button
                                    type="button"
                                    @click="selectEvent(evenement)"
                                    class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded"
                                >
                                    {{ evenement.nom }}
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <p v-if="errors.event" class="text-sm text-accent mt-1">
                {{ errors.event }}
            </p>

            <div class="w-full">
                <label
                    for="name"
                    class="block mb-2.5 text-sm font-medium text-heading"
                    >Nom <span class="text-accent">*</span></label
                >
                <input
                    type="text"
                    id="name"
                    v-model="courseData.name"
                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body"
                    placeholder=""
                    required
                />
                <p v-if="errors.name" class="text-sm text-accent mt-1">
                    {{ errors.name }}
                </p>
            </div>

            <hr class="border-t border-gray-200 mt-6 mb-4 mx-4" />

            <div class="flex items-center gap-4">
                <div class="w-full">
                    <label
                        for="datepicker-start"
                        class="block mb-2.5 text-sm font-medium text-heading"
                        >Date de début <span class="text-accent">*</span></label
                    >
                    <input
                        id="datepicker-start"
                        v-model="courseData.date.start"
                        name="dateStart"
                        type="date"
                        :min="new Date().toISOString().split('T')[0]"
                        class="block w-full bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 py-2.5 shadow-xs placeholder:text-body"
                    />
                    <p v-if="errors.dateStart" class="text-sm text-accent mt-1">
                        {{ errors.dateStart }}
                    </p>
                </div>
                <div class="w-full">
                    <label
                        for="datepicker-end"
                        class="block mb-2.5 text-sm font-medium text-heading"
                        >Date de fin <span class="text-accent">*</span></label
                    >
                    <input
                        id="datepicker-end"
                        v-model="courseData.date.end"
                        :min="courseData.date.start || undefined"
                        name="dateEnd"
                        type="date"
                        class="block w-full bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 py-2.5 shadow-xs placeholder:text-body"
                    />
                    <p v-if="errors.dateEnd" class="text-sm text-accent mt-1">
                        {{ errors.dateEnd }}
                    </p>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 my-4">
                <label
                    for="inscriptionpicker-start"
                    class="block mb-2.5 text-sm font-medium text-heading"
                    >Interval d'inscription
                    <span class="text-accent">*</span></label
                >
                <div class="flex flex-col sm:flex-row gap-4 w-full lg:basis-1/2">
                    <div class="w-full">
                        <input
                            id="inscriptionpicker-start"
                            v-model="courseData.date.inscriptionStart"
                            name="inscriptionStart"
                            type="date"
                            :min="new Date().toISOString().split('T')[0]"
                            class="block w-full bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 py-2.5 shadow-xs placeholder:text-body"
                        />
                        <p
                            v-if="errors.inscriptionStart"
                            class="text-sm text-accent mt-1"
                        >
                            {{ errors.inscriptionStart }}
                        </p>
                    </div>
                    <div class="w-full">
                        <input
                            id="inscriptionpicker-end"
                            v-model="courseData.date.inscriptionEnd"
                            :min="courseData.date.inscriptionStart || undefined"
                            name="inscriptionEnd"
                            type="date"
                            class="block w-full bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 py-2.5 shadow-xs placeholder:text-body"
                        />
                        <p
                            v-if="errors.inscriptionEnd"
                            class="text-sm text-accent mt-1"
                        >
                            {{ errors.inscriptionEnd }}
                        </p>
                    </div>
                </div>
            </div>

            <hr class="border-t border-gray-200 mt-6 mb-4 mx-4" />

            <!-- Distance + Nb coureurs max -->
            <div class="flex flex-col sm:flex-row gap-4 mb-4">
                <div class="w-full sm:w-48">
                    <label
                        for="distance"
                        class="block mb-2.5 text-sm font-medium text-heading"
                        >Distance (km) <span class="text-accent">*</span></label
                    >
                    <input
                        type="number"
                        id="distance"
                        v-model="courseData.distance"
                        class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs"
                    />
                    <p v-if="errors.distance" class="text-sm text-accent mt-1">
                        {{ errors.distance }}
                    </p>
                </div>
                <div class="flex-1">
                    <label
                        for="maxRunners"
                        class="block mb-2.5 text-sm font-medium text-heading"
                        >Nombre de coureurs maximum
                        <span class="text-accent">*</span></label
                    >
                    <input
                        type="number"
                        id="maxRunners"
                        v-model="courseData.maxRunners"
                        class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs"
                    />
                    <p
                        v-if="errors.maxRunners"
                        class="text-sm text-accent mt-1"
                    >
                        {{ errors.maxRunners }}
                    </p>
                </div>
            </div>

            <!-- Premier / Dernier dossard -->
            <div class="flex gap-4 mb-4">
                <div class="w-full">
                    <label
                        for="firstDossard"
                        class="block mb-2.5 text-sm font-medium text-heading"
                        >Premier dossard
                        <span class="text-accent">*</span></label
                    >
                    <input
                        type="number"
                        id="firstDossard"
                        v-model="courseData.dossard.first"
                        class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs"
                    />
                    <p
                        v-if="errors.firstDossard"
                        class="text-sm text-accent mt-1"
                    >
                        {{ errors.firstDossard }}
                    </p>
                </div>
                <div class="w-full">
                    <label
                        for="lastDossard"
                        class="block mb-2.5 text-sm font-medium text-heading"
                        >Dernier dossard
                        <span class="text-accent">*</span></label
                    >
                    <input
                        type="number"
                        id="lastDossard"
                        v-model="courseData.dossard.last"
                        class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs"
                    />
                    <p
                        v-if="errors.lastDossard"
                        class="text-sm text-accent mt-1"
                    >
                        {{ errors.lastDossard }}
                    </p>
                </div>
            </div>

            <hr class="border-t border-gray-200 mt-2 mb-4" />

            <!-- Toggle Prix évolutif -->
            <div class="flex items-center gap-3 mb-4">
                <label class="text-sm font-medium text-heading"
                    >Prix évolutif</label
                >
                <label class="inline-flex items-center cursor-pointer">
                    <input
                        type="checkbox"
                        v-model="courseData.parameters.prixEvolutif"
                        class="sr-only peer"
                        @change="initialiserPaliers"
                    />
                    <div
                        class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"
                    ></div>
                </label>
            </div>

            <!-- Tarif simple (prix évolutif désactivé) -->
            <div
                v-if="!courseData.parameters.prixEvolutif"
                class="flex gap-4 mb-4"
            >
                <div class="w-full">
                    <label
                        for="tarif"
                        class="block mb-2.5 text-sm font-medium text-heading"
                        >Tarif <span class="text-accent">*</span></label
                    >
                    <input
                        type="number"
                        id="tarif"
                        v-model="courseData.tarif"
                        class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs"
                    />
                    <p v-if="errors.tarif" class="text-sm text-accent mt-1">
                        {{ errors.tarif }}
                    </p>
                </div>
                <div class="w-full">
                    <label
                        for="tarifInfo"
                        class="block mb-2.5 text-sm font-medium text-heading"
                        >Information tarif</label
                    >
                    <input
                        type="text"
                        id="tarifInfo"
                        v-model="courseData.tarifInfo"
                        class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs"
                    />
                </div>
            </div>

            <!-- Section paliers (prix évolutif activé) -->
            <div
                v-if="courseData.parameters.prixEvolutif"
                class="border-l-2 border-tertiary/30 pl-4 flex flex-col gap-4 mb-4"
            >
                <!-- Mode dossards/dates -->
                <div class="flex gap-3">
                    <button
                        type="button"
                        @click="changerModePrixEvolutif('dossards')"
                        :class="[
                            'flex-1 px-4 py-2.5 rounded-base border text-sm font-medium transition-all',
                            courseData.prixEvolutif.type === 'dossards'
                                ? 'border-tertiary bg-tertiary text-primary'
                                : 'border-default-medium bg-neutral-secondary-medium text-heading hover:border-tertiary',
                        ]"
                    >
                        Par nombre de dossards
                    </button>
                    <button
                        type="button"
                        @click="changerModePrixEvolutif('dates')"
                        :class="[
                            'flex-1 px-4 py-2.5 rounded-base border text-sm font-medium transition-all',
                            courseData.prixEvolutif.type === 'dates'
                                ? 'border-tertiary bg-tertiary text-primary'
                                : 'border-default-medium bg-neutral-secondary-medium text-heading hover:border-tertiary',
                        ]"
                    >
                        Par dates
                    </button>
                </div>

                <!-- Paliers -->
                <div class="flex flex-col gap-2">
                    <div
                        v-for="(palier, index) in courseData.prixEvolutif
                            .paliers"
                        :key="index"
                        class="flex flex-col sm:flex-row items-start sm:items-center gap-2 bg-neutral-secondary-medium rounded-base px-3 py-2"
                    >
                        <template
                            v-if="courseData.prixEvolutif.type === 'dossards'"
                        >
                            <input
                                type="number"
                                v-model="palier.valeur_debut"
                                :disabled="index === 0"
                                :class="[
                                    'w-full sm:w-28 border border-default-medium text-heading text-sm rounded-base px-2 py-1.5 shadow-xs',
                                    index === 0
                                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                        : 'bg-white',
                                ]"
                            />
                            <span class="text-body text-xs hidden sm:inline">→</span>
                            <input
                                type="number"
                                v-model="palier.valeur_fin"
                                :disabled="
                                    index ===
                                    courseData.prixEvolutif.paliers.length - 1
                                "
                                :class="[
                                    'w-full sm:w-28 border border-default-medium text-heading text-sm rounded-base px-2 py-1.5 shadow-xs',
                                    index ===
                                    courseData.prixEvolutif.paliers.length - 1
                                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                        : 'bg-white',
                                ]"
                            />
                        </template>

                        <template v-else>
                            <input
                                type="date"
                                v-model="palier.valeur_debut"
                                :disabled="index === 0"
                                :class="[
                                    'w-full sm:w-36 border border-default-medium text-heading text-sm rounded-base px-2 py-1.5 shadow-xs',
                                    index === 0
                                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                        : 'bg-white',
                                ]"
                            />
                            <span class="text-body text-xs">→</span>
                            <input
                                type="date"
                                v-model="palier.valeur_fin"
                                :disabled="
                                    index ===
                                    courseData.prixEvolutif.paliers.length - 1
                                "
                                :class="[
                                    'w-full sm:w-36 border border-default-medium text-heading text-sm rounded-base px-2 py-1.5 shadow-xs',
                                    index ===
                                    courseData.prixEvolutif.paliers.length - 1
                                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                        : 'bg-white',
                                ]"
                            />
                        </template>

                        <span class="text-body text-xs hidden sm:inline ml-1">CHF</span>
                        <input
                            type="number"
                            v-model="palier.tarif"
                            placeholder="Tarif"
                            class="w-full sm:w-24 bg-white border border-default-medium text-heading text-sm rounded-base px-2 py-1.5 shadow-xs"
                        />

                        <!-- Supprimer uniquement les paliers intermédiaires -->
                        <button
                            v-if="
                                index !== 0 &&
                                index !==
                                    courseData.prixEvolutif.paliers.length - 1
                            "
                            type="button"
                            @click="supprimerPalier(index, palier)"
                            class="text-accent hover:text-red-700 ml-auto"
                        >
                            <Icon icon="mdi:close" class="w-4 h-4" />
                        </button>
                        <div v-else class="ml-auto w-4"></div>
                    </div>
                </div>

                <button
                    type="button"
                    @click="ajouterPalierIntermediaire"
                    class="flex items-center gap-2 text-sm text-heading border border-default-medium bg-neutral-secondary-medium rounded-base px-3 py-2 hover:border-tertiary transition-colors w-fit"
                >
                    <Icon icon="mdi:plus" class="w-4 h-4" />
                    Ajouter un palier intermédiaire
                </button>

                <p class="text-xs text-body">
                    <template
                        v-if="courseData.prixEvolutif.type === 'dossards'"
                    >
                        Le tarif s'applique selon le nombre de dossards vendus
                        au moment de l'inscription.
                    </template>
                    <template v-else>
                        Le tarif s'applique selon la date d'inscription.
                    </template>
                </p>
            </div>

            <hr class="border-t border-gray-200 mt-2 mb-4 mx-4" />

            <!-- Type de course -->
            <div class="flex justify-between items-center gap-4 my-4">
                <label for="dropdown" class="text-sm font-medium text-heading"
                    >Type de course <span class="text-accent">*</span></label
                >
                <div class="relative">
                    <button
                        data-dropdown-toggle="dropdownType"
                        class="inline-flex items-center justify-center shadow-xs font-medium text-sm px-4 py-2.5"
                        :class="[
                            courseData.type.name
                                ? 'border-b-2 text-primary border-tertiary hover:bg-gray-100 rounded-t-base'
                                : 'hover:bg-primary-300 text-white bg-primary-900 shadow-xs font-medium rounded-base text-sm px-4 py-2.5',
                        ]"
                        type="button"
                    >
                        {{
                            courseData.type.name ||
                            "Sélectionner un type de course"
                        }}
                        <Icon icon="mdi:chevron-down" class="ml-2 w-6 h-6" />
                    </button>
                    <div
                        id="dropdownType"
                        class="hidden absolute left-0 mt-1 z-10 bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44"
                    >
                        <ul class="p-2 text-sm text-body font-medium">
                            <li v-for="type in typesCourse" :key="type.id">
                                <button
                                    type="button"
                                    @click="selectType(type)"
                                    class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded"
                                >
                                    {{ type.name }}
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <p v-if="errors.type" class="text-sm text-accent mt-1">
                {{ errors.type }}
            </p>

            <div
                v-if="
                    courseData.type.name === 'Relais' ||
                    courseData.type.name === 'Groupe'
                "
                class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 my-4"
            >
                <label
                    for="maxNbPersonne"
                    class="w-full lg:basis-1/3 block mb-2.5 text-sm font-medium text-heading"
                >
                    {{
                        courseData.type.name === "Relais"
                            ? "Nombre de coureurs par équipe"
                            : "Nombre maximum de personnes par groupe"
                    }}
                    <span class="text-accent">*</span>
                </label>
                <div class="w-full lg:flex-1 flex flex-col gap-1">
                    <input
                        type="number"
                        id="maxNbPersonne"
                        v-model="courseData.maxNbPersonne"
                        min="2"
                        class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs"
                        :placeholder="
                            courseData.type.name === 'Relais'
                                ? 'Ex: 4'
                                : 'Ex: 10'
                        "
                    />
                    <p v-if="errors.maxNbPersonne" class="text-sm text-accent">
                        {{ errors.maxNbPersonne }}
                    </p>
                    <p
                        v-if="
                            courseData.type.name === 'Groupe' &&
                            !errors.maxNbPersonne
                        "
                        class="text-xs text-body"
                    >
                        Minimum 2 personnes par groupe.
                    </p>
                </div>
            </div>

            <hr class="border-t border-gray-200 mt-6 mb-4 mx-4" />

            <div class="flex items-center gap-3 mb-4">
                <label class="text-sm font-medium text-heading"
                    >Challenge</label
                >
                <label class="inline-flex items-center cursor-pointer">
                    <input
                        type="checkbox"
                        v-model="courseData.parameters.challenge"
                        class="sr-only peer"
                    />
                    <div
                        class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"
                    ></div>
                </label>
            </div>
            <div
                v-if="courseData.parameters.challenge"
                class="ml-4 mt-2 border-l-2 border-tertiary/30 pl-4 flex flex-col gap-3"
            >
                <p class="text-xs text-body font-medium">
                    Organisations du challenge
                </p>
                <div
                    v-if="courseData.challengeOrganisations.length > 0"
                    class="flex flex-col gap-1"
                >
                    <div
                        v-for="(
                            org, index
                        ) in courseData.challengeOrganisations"
                        :key="index"
                        class="flex items-center justify-between bg-neutral-secondary-medium rounded-base px-3 py-1.5 text-sm"
                    >
                        <span class="text-body">
                            <span
                                class="text-xs px-1.5 py-0.5 rounded mr-2"
                                :class="
                                    org.type === 'Groupe'
                                        ? 'bg-blue-100 text-blue-700'
                                        : 'bg-orange-100 text-orange-700'
                                "
                            >
                                {{
                                    org.type === "Groupe"
                                        ? "Université"
                                        : "Entreprise"
                                }}
                            </span>
                            {{ org.nom }}
                        </span>
                        <button
                            type="button"
                            @click="supprimerOrganisation(index, org)"
                            class="text-accent hover:text-red-700 ml-2"
                        >
                            <Icon icon="mdi:close" class="w-4 h-4" />
                        </button>
                    </div>
                </div>
                <p v-else class="text-xs text-body italic">
                    Aucune organisation ajoutée.
                </p>
                <div class="flex gap-2 items-end">
                    <div class="flex flex-col gap-1 flex-1">
                        <label class="text-xs text-body">Nom</label>
                        <input
                            v-model="nouvelleOrg.nom"
                            type="text"
                            placeholder="Ex: UNIGE, Nestlé..."
                            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base px-2.5 py-2 shadow-xs"
                        />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-body">Type</label>
                        <select
                            v-model="nouvelleOrg.type"
                            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base px-2.5 py-2 shadow-xs"
                        >
                            <option value="Groupe">Université</option>
                            <option value="Entreprise">Entreprise</option>
                        </select>
                    </div>
                    <button
                        type="button"
                        @click="ajouterOrganisation"
                        :disabled="!nouvelleOrg.nom.trim()"
                        class="bg-tertiary border border-default-medium text-heading text-sm rounded-base px-3 py-2 disabled:opacity-50"
                    >
                        <Icon icon="mdi:plus" class="w-4 h-4" />
                    </button>
                </div>
            </div>
            <hr class="border-t border-gray-200 mt-6 mb-4 mx-4" />

            <!-- Ages + temps moyen -->
            <div class="flex flex-col md:flex-row gap-4 mb-4">
                <div class="w-full">
                    <label
                        for="ageMin"
                        class="block mb-2.5 text-sm font-medium text-heading"
                        >Limite âge min
                        <span class="text-accent">*</span></label
                    >
                    <input
                        type="number"
                        id="ageMin"
                        v-model="courseData.age.min"
                        class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body"
                        required
                    />
                    <p v-if="errors.ageMin" class="text-sm text-accent mt-1">
                        {{ errors.ageMin }}
                    </p>
                </div>
                <div class="w-full">
                    <label
                        for="ageMax"
                        class="block mb-2.5 text-sm font-medium text-heading"
                        >Limite âge max</label
                    >
                    <input
                        type="number"
                        id="ageMax"
                        v-model="courseData.age.max"
                        class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body"
                        required
                    />
                    <p v-if="errors.ageMax" class="text-sm text-accent mt-1">
                        {{ errors.ageMax }}
                    </p>
                </div>
                <div class="w-full">
                    <label
                        for="conditionMineur"
                        class="block mb-2.5 text-sm font-medium text-heading"
                        >Condition participant mineur</label
                    >
                    <input
                        type="text"
                        id="conditionMineur"
                        v-model="courseData.age.conditionMineur"
                        class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body"
                        required
                    />
                </div>
            </div>
            <div class="w-full">
                <label
                    for="tempsMoyen"
                    class="block mb-2.5 text-sm font-medium text-heading"
                    >Temps moyen pour X km</label
                >
                <input
                    type="text"
                    id="tempsMoyen"
                    v-model="courseData.tempsMoyen"
                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body"
                    required
                />
            </div>

            <hr class="border-t border-gray-200 mt-6 mb-4 mx-4" />

            <!-- PARAMÈTRES -->
            <div class="flex flex-col m-4 gap-2">
                <div class="flex flex-row justify-between items-center mb-4">
                    <label class="text-sm font-medium text-heading"
                        >Actif</label
                    >
                    <label class="inline-flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="courseData.parameters.actif"
                            class="sr-only peer"
                        />
                        <div
                            class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"
                        ></div>
                    </label>
                </div>
                <div class="flex flex-row justify-between items-center mb-4">
                    <label class="text-sm font-medium text-heading"
                        >N° Dossard manuel</label
                    >
                    <label class="inline-flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="courseData.parameters.dossardPersonalise"
                            class="sr-only peer"
                        />
                        <div
                            class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"
                        ></div>
                    </label>
                </div>

                <div class="flex flex-row justify-between items-center mb-4">
                    <label class="text-sm font-medium text-heading"
                        >Avertissement</label
                    >
                    <label class="inline-flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="courseData.parameters.avertissement"
                            class="sr-only peer"
                        />
                        <div
                            class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"
                        ></div>
                    </label>
                </div>
                <div class="flex flex-row justify-between items-center mb-4">
                    <label class="text-sm font-medium text-heading"
                        >Documents</label
                    >
                    <label class="inline-flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="courseData.parameters.document"
                            class="sr-only peer"
                        />
                        <div
                            class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"
                        ></div>
                    </label>
                </div>
                <div class="flex flex-row justify-between items-center mb-4">
                    <label class="text-sm font-medium text-heading"
                        >Questionnaire</label
                    >
                    <label class="inline-flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="courseData.parameters.questionnaire"
                            class="sr-only peer"
                        />
                        <div
                            class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"
                        ></div>
                    </label>
                </div>
            </div>

            <hr class="border-t border-gray-200 mt-6 mb-4 mx-4" />

            <div class="flex flex-col lg:flex-row gap-4">
                <div
                    class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 my-4 w-full lg:basis-1/2"
                >
                    <label
                        for="dropdown"
                        class="text-sm font-medium text-heading"
                        >Catégorie</label
                    >
                    <div class="relative w-full sm:flex-1 lg:w-auto">
                        <button
                            data-dropdown-toggle="dropdownCategory"
                            class="inline-flex items-center justify-center shadow-xs font-medium text-sm px-4 py-2.5 w-full"
                            :class="[
                                courseData.category.nom
                                    ? 'border-b-2 text-primary border-tertiary hover:bg-gray-100 rounded-t-base'
                                    : 'hover:bg-primary-300 text-white bg-primary-900 shadow-xs font-medium rounded-base text-sm px-4 py-2.5',
                            ]"
                            type="button"
                        >
                            {{
                                courseData.category.nom ||
                                "Sélectionner une catégorie"
                            }}
                            <Icon
                                icon="mdi:chevron-down"
                                class="ml-2 w-6 h-6"
                            />
                        </button>
                        <div
                            id="dropdownCategory"
                            class="hidden absolute left-0 mt-1 z-10 bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44"
                        >
                            <ul class="p-2 text-sm text-body font-medium">
                                <li
                                    v-for="category in categories"
                                    :key="category.id"
                                >
                                    <button
                                        type="button"
                                        @click="selectCategory(category)"
                                        class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded"
                                    >
                                        {{ category.nom }}
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div
                    class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 my-4 w-full lg:basis-1/2"
                >
                    <label
                        for="dropdownSubcategory"
                        class="text-sm font-medium text-heading"
                        >Sous-catégorie</label
                    >
                    <div class="relative w-full sm:flex-1 lg:w-auto">
                        <button
                            data-dropdown-toggle="dropdownSubcategory"
                            class="inline-flex items-center justify-center shadow-xs font-medium text-sm px-4 py-2.5 w-full"
                            :class="[
                                courseData.subCategory.nom
                                    ? 'border-b-2 text-primary border-tertiary hover:bg-gray-100 rounded-t-base'
                                    : 'hover:bg-primary-300 text-white bg-primary-900 shadow-xs font-medium rounded-base text-sm px-4 py-2.5',
                            ]"
                            type="button"
                        >
                            {{
                                courseData.subCategory.nom ||
                                "Sélectionner une sous-catégorie"
                            }}
                            <Icon
                                icon="mdi:chevron-down"
                                class="ml-2 w-6 h-6"
                            />
                        </button>
                        <div
                            id="dropdownSubcategory"
                            class="hidden absolute left-0 mt-1 z-10 bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44"
                        >
                            <ul class="p-2 text-sm text-body font-medium">
                                <li
                                    v-for="subCategory in subCategories"
                                    :key="subCategory.id"
                                >
                                    <button
                                        type="button"
                                        @click="selectSubCategory(subCategory)"
                                        class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded"
                                    >
                                        {{ subCategory.nom }}
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ETAPE OPTIONS -->
        <div v-if="etape == formulaireEtape.OPTIONS">
            <p class="text-subtitle my-4">Options supplémentaires</p>
            <div v-for="(option, index) in courseData.options" class="my-4">
                <OptionTemplate
                    :optionModel="option"
                    @remove-option="removeOption(index)"
                />
            </div>
            <div class="flex pt-4 items-center">
                <div class="flex-grow border-t border-gray-700"></div>
                <span class="mx-4 relative inline-flex items-center">
                    <button
                        type="button"
                        @click="handleModalState"
                        class="bg-tertiary border border-default-medium text-heading text-sm rounded-full focus:border-tertiary-900 px-2.5 py-2.5"
                    >
                        <Icon icon="mdi:plus" class="w-4 h-4" />
                    </button>
                    <OptionList
                        v-if="modal == optionModal.SELECTION"
                        placement="right"
                        :elements="optionElements"
                        @select-item="handleOptionSelection"
                    />
                </span>
                <div class="flex-grow border-t border-gray-700"></div>
            </div>
            <SelectionModal
                v-if="modal == optionModal.EXISTANT"
                :titre="'Sélectionner des options existantes'"
                sous-titre="Cliquez sur plusieurs éléments, l'ordre affiché correspondra à l'ordre de clic."
                :elements="optionModels.map((option) => option.nom)"
                :preSelectedElements="preSelectedOptionsNames"
                @select-item="handleOptionSelection"
                @deselect-item="handleOptionDeselection"
                @cancel="modal = optionModal.FERMEE"
            />
        </div>

        <!-- ETAPE AVERTISSEMENT -->
        <div v-if="etape == formulaireEtape.AVERTISSEMENT">
            <p class="text-subtitle mt-4">Avertissement</p>
            <p class="mb-4">
                Cette page apparaitra dès la sélection de la course. Elle sert à
                avertir les participants de risques potentiels.
            </p>
            <div class="flex flex-col lg:flex-row gap-4 h-auto lg:h-128">
                <textarea
                    type="text"
                    id="avertissement"
                    v-model="courseData.avertissement.contenu"
                    class="bg-neutral-secondary-medium w-full lg:basis-2/3 border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block px-2.5 py-2 shadow-xs placeholder:text-body"
                    placeholder=""
                    required
                />
                <div class="w-full lg:basis-1/3">
                    <h2 class="text-sm font-medium text-heading mb-2.5">
                        Mes modèles
                    </h2>
                    <button
                        v-for="avertissementModel in avertissementModels"
                        :key="avertissementModel.name"
                        type="button"
                        @click="
                            courseData.avertissement.contenu =
                                avertissementModel.contenu
                        "
                        class="btn-model"
                    >
                        {{ avertissementModel.titre }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ETAPE DOCUMENT -->
        <div v-if="etape == formulaireEtape.DOCUMENT">
            <p class="text-subtitle mt-4">Documents</p>
            <p class="mb-4">
                Décrivez quels documents doivent être fournis et quels type de
                personnes sont concernées.
            </p>
            <div class="flex flex-col lg:flex-row gap-4 h-auto lg:h-128">
                <textarea
                    type="text"
                    id="documents"
                    v-model="courseData.document.description"
                    class="bg-neutral-secondary-medium w-full lg:basis-2/3 border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block px-2.5 py-2 shadow-xs placeholder:text-body"
                    placeholder=""
                    required
                />
                <div class="w-full lg:basis-1/3">
                    <h2 class="text-sm font-medium text-heading mb-2.5">
                        Mes modèles
                    </h2>
                    <button
                        v-for="documentModel in documentModels"
                        :key="documentModel.name"
                        type="button"
                        @click="
                            courseData.document.description =
                                documentModel.description
                        "
                        class="btn-model"
                    >
                        {{ documentModel.name }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ETAPE QUESTIONNAIRE -->
        <div v-if="etape == formulaireEtape.QUESTIONNAIRE">
            <p class="text-subtitle my-4">Questionnaires</p>
            <div v-for="(question, index) in courseData.questions" class="my-4">
                <QuestionTemplate
                    :questionModel="question"
                    @remove-question="courseData.questions.splice(index, 1)"
                />
            </div>
            <div class="flex pt-4 items-center">
                <div class="flex-grow border-t border-gray-700"></div>
                <span class="mx-4 relative inline-flex items-center">
                    <button
                        type="button"
                        @click="handleModalState"
                        class="bg-tertiary border border-default-medium text-heading text-sm rounded-full focus:border-tertiary-900 px-2.5 py-2.5"
                    >
                        <Icon icon="mdi:plus" class="w-4 h-4" />
                    </button>
                    <OptionList
                        v-if="modal == optionModal.SELECTION"
                        placement="right"
                        :elements="optionElements"
                        @select-item="handleOptionSelection"
                    />
                </span>
                <div class="flex-grow border-t border-gray-700"></div>
            </div>
            <SelectionModal
                v-if="modal == optionModal.EXISTANT"
                :titre="'Sélectionner des questions existantes'"
                sous-titre="Cliquez sur plusieurs éléments, l'ordre affiché correspondra à l'ordre de clic."
                :elements="questionModels.map((question) => question.enonce)"
                :preSelectedElements="preSelectedQuestionsEnonces"
                @select-item="handleOptionSelection"
                @deselect-item="handleOptionDeselection"
                @cancel="modal = optionModal.FERMEE"
            />
        </div>

        <!-- NAVIGATION ETAPES -->
        <div class="flex flex-row mt-6 gap-4">
            <button
                v-if="etapesActives.indexOf(etape) > 0"
                class="btn-accent-300"
                @click="etape = etapesActives[etapesActives.indexOf(etape) - 1]"
            >
                Etape précédente
            </button>
            <button
                v-if="etapesActives.indexOf(etape) < etapesActives.length - 1"
                class="btn-tertiary ml-auto"
                @click="handleNextStep()"
            >
                Etape suivante
            </button>
            <button
                v-else
                class="btn-tertiary ml-auto"
                @click="confirmationPopup = true"
            >
                {{
                    isEditMode
                        ? "Enregistrer les modifications"
                        : "Créer la course"
                }}
            </button>
        </div>

        <PopupConfirmation
            v-if="confirmationPopup"
            @cancel="confirmationPopup = false"
            @confirm="insertCourse()"
        />
        <PopupConfirmation
            v-if="dataInserted"
            :message="
                isEditMode
                    ? 'La course a été modifiée avec succès !'
                    : 'La course a été créée avec succès !'
            "
            :icon="'mdi:check'"
            :showButtons="false"
        />
        <PopupConfirmation
            v-if="confirmationChangementModePrix"
            message="Changer de mode supprimera tous les paliers existants. Continuer ?"
            icon="mdi:alert-circle-outline"
            @confirm="confirmerChangementModePrixEvolutif"
            @cancel="annulerChangementModePrixEvolutif"
        />
    </div>
</template>

<script>
/**
 * @fileoverview Composant FormulaireCourse.
 * @description Formulaire de création et de modification d'une course organisateur.
 * Gère les étapes (général, options, avertissement, documents, questionnaire),
 * la synchronisation des données liées et la soumission des changements.
 * @remarks Le composant agrège plusieurs services métier et orchestre la persistance multi-entités.
 */
import { Icon } from "@iconify/vue";
import { initDropdowns } from "flowbite";
import PopupConfirmation from "./PopupConfirmation.vue";
import OptionList from "./OptionList.vue";
import SelectionModal from "./SelectionModal.vue";
import OptionTemplate from "./OptionTemplate.vue";
import QuestionTemplate from "./QuestionTemplate.vue";
import evenementOrganisateurService from "../services/evenementOrganisateurService";
import courseOrganisateurService from "../services/courseOrganisateurService";
import optionOrganisateurService from "../services/optionOrganisateurService";
import IndicateurEtapes from "./IndicateurEtapes.vue";
import avertissementOrganisateurService from "../services/avertissementOrganisateurService";
import optionCourseService from "../services/optionCourseService";
import categorieOrganisateurService from "../services/categorieOrganisateurService";
import sousCategorieOrganisateurService from "../services/sousCategorieOrganisateurService";
import questionOrganisateurService from "../services/questionOrganisateurService";
import optionQuestionOrganisateurService from "../services/optionQuestionOrganisateurService";
import courseQuestionOrganisateurService from "../services/courseQuestionOrganisateurService";
import challengeOrganisationService from "../services/challengeOrganisationService";
import prixEvolutifService from "../services/prixEvolutifService";

const formulaireEtape = {
    GENERAL: 1,
    OPTIONS: 2,
    AVERTISSEMENT: 3,
    DOCUMENT: 4,
    QUESTIONNAIRE: 5,
};

const optionModal = {
    FERMEE: 1,
    SELECTION: 2,
    EXISTANT: 3,
};

export default {
    components: {
        Icon,
        PopupConfirmation,
        OptionList,
        SelectionModal,
        OptionTemplate,
        QuestionTemplate,
        IndicateurEtapes,
    },
    data() {
        return {
            formulaireEtape,
            optionModal,
            etape: formulaireEtape.GENERAL,
            modal: optionModal.FERMEE,
            confirmationPopup: false,
            dataInserted: false,
            confirmationChangementModePrix: false,
            modePrixEvolutifEnAttente: null,
            errors: {},
            formError: "",
            evenements: [],
            nouvelleOrg: { nom: "", type: "Entreprise" },
            formulaireEtapesLabels: ["Général", "Options supplémentaires"],
            typesCourse: [
                { name: "Individuel", id: 1 },
                { name: "Relais", id: 2 },
                { name: "Groupe", id: 3 },
            ],
            categories: [],
            subCategories: [],
            questionModels: [],
            documentModels: [
                {
                    name: "Justificatif Étudiant",
                    description:
                        "Pour valider votre tarif préférentiel, veuillez joindre une copie de votre carte d'étudiant ou un certificat de scolarité valide pour la saison actuelle.",
                },
                {
                    name: "Étudiant + Certificat Médical",
                    description:
                        "En complément de votre justificatif étudiant, vous devez fournir un certificat médical de non-contre-indication à la course à pied en compétition, datant de moins d'un an.",
                },
            ],
            optionElements: ["Existant", "Nouveau"],
            optionModels: [],
            avertissementModels: [],
            courseData: {
                name: "",
                event: { name: "", id: "" },
                date: {
                    start: "",
                    end: "",
                    inscriptionStart: "",
                    inscriptionEnd: "",
                },
                distance: "",
                tarif: "",
                tarifInfo: "",
                popupInfo: "",
                type: { name: "", id: "" },
                maxRunners: "",
                maxNbPersonne: "",
                dossard: { first: "", last: "" },
                age: { min: "", max: "", conditionMineur: "" },
                tempsMoyen: "",
                parameters: {
                    actif: false,
                    dossardPersonalise: false,
                    challenge: false,
                    avertissement: false,
                    document: false,
                    questionnaire: false,
                    prixEvolutif: false,
                },
                category: { nom: "", id: "" },
                subCategory: { nom: "", id: "" },
                options: [],
                avertissement: { contenu: "", id: "" },
                questions: [],
                document: { description: "" },
                challengeOrganisations: [],
                prixEvolutif: {
                    type: "dossards",
                    paliers: [],
                },
            },
        };
    },
    computed: {
        /**
         * Indique si le formulaire est ouvert en mode édition.
         * @returns {boolean}
         */
        isEditMode() {
            return !!this.$route.query.id;
        },
        /**
         * Identifiant de la course à charger ou modifier.
         * @returns {string|undefined}
         */
        courseId() {
            return this.$route.query.id;
        },
        /**
         * Identifiant de l'évènement associé passé dans l'URL.
         * @returns {string|undefined}
         */
        eventIdFromUrl() {
            return this.$route.query.idEvenement;
        },
        /**
         * Étapes actives du formulaire selon les options de la course.
         * @author Neris Alessandro
         * @returns {Array<number>}
         */
        etapesActives() {
            const etapes = [formulaireEtape.GENERAL, formulaireEtape.OPTIONS];
            const labels = ["Général", "Options supplémentaires"];
            if (this.courseData.parameters.avertissement) {
                etapes.push(formulaireEtape.AVERTISSEMENT);
                labels.push("Avertissement");
            }
            if (this.courseData.parameters.document) {
                etapes.push(formulaireEtape.DOCUMENT);
                labels.push("Documents");
            }
            if (this.courseData.parameters.questionnaire) {
                etapes.push(formulaireEtape.QUESTIONNAIRE);
                labels.push("Questionnaire");
            }
            this.formulaireEtapesLabels = labels;
            return etapes.sort((a, b) => a - b);
        },
        /**
         * Noms des options déjà présentes dans la course.
         * @returns {Array<string>}
         */
        preSelectedOptionsNames() {
            return this.courseData.options.map((o) => o.nom);
        },
        /**
         * Énoncés des questions déjà présentes dans la course.
         * @returns {Array<string>}
         */
        preSelectedQuestionsEnonces() {
            return this.courseData.questions.map((q) => q.enonce);
        },
    },
    watch: {
        // Sync les champs date début → date fin et date fin inscription
        "courseData.date.start"(newStart) {
            if (!newStart) return;
            this.courseData.date.end = newStart;
            this.courseData.date.inscriptionEnd = newStart;
        },
        // Sync les champs date fin → date début et date début inscription
        "courseData.date.inscriptionStart"(newStart) {
            if (!newStart) return;
            if (this.courseData.date.inscriptionEnd < newStart)
                this.courseData.date.inscriptionEnd = newStart;
        },
        // Supprime le contenu de l'avertissement si l'option est désactivée
        "courseData.parameters.avertissement"(val) {
            if (!val) this.courseData.avertissement = { contenu: "" };
        },
        // Supprime le contenu du document si l'option est désactivée
        "courseData.parameters.document"(val) {
            if (!val) this.courseData.document = { name: "", description: "" };
        },
        // Supprime les questions si l'option est désactivée
        "courseData.parameters.questionnaire"(val) {
            if (!val) this.courseData.questions = [];
        },
        courseId(newId) {
            if (newId) {
                this.chargerDonneesCourse();
            } else {
                this.resetFormulaire();
            }
        },
        // Sync premier dossard → premier palier valeur_debut
        "courseData.dossard.first"(newVal) {
            if (
                this.courseData.parameters.prixEvolutif &&
                this.courseData.prixEvolutif.type === "dossards" &&
                this.courseData.prixEvolutif.paliers.length > 0
            ) {
                this.courseData.prixEvolutif.paliers[0].valeur_debut =
                    String(newVal);
            }
        },

        // Sync dernier dossard → dernier palier valeur_fin
        "courseData.dossard.last"(newVal) {
            if (
                this.courseData.parameters.prixEvolutif &&
                this.courseData.prixEvolutif.type === "dossards" &&
                this.courseData.prixEvolutif.paliers.length > 0
            ) {
                const dernierIndex =
                    this.courseData.prixEvolutif.paliers.length - 1;
                this.courseData.prixEvolutif.paliers[dernierIndex].valeur_fin =
                    String(newVal);
            }
        },

        // Sync date début inscription → premier palier valeur_debut
        "courseData.date.inscriptionStart"(newVal) {
            if (
                this.courseData.parameters.prixEvolutif &&
                this.courseData.prixEvolutif.type === "dates" &&
                this.courseData.prixEvolutif.paliers.length > 0
            ) {
                this.courseData.prixEvolutif.paliers[0].valeur_debut = newVal;
            }
        },

        // Sync date fin inscription → dernier palier valeur_fin
        "courseData.date.inscriptionEnd"(newVal) {
            if (
                this.courseData.parameters.prixEvolutif &&
                this.courseData.prixEvolutif.type === "dates" &&
                this.courseData.prixEvolutif.paliers.length > 0
            ) {
                const dernierIndex =
                    this.courseData.prixEvolutif.paliers.length - 1;
                this.courseData.prixEvolutif.paliers[dernierIndex].valeur_fin =
                    newVal;
            }
        },
    },
    methods: {
        /**
         * Valide les champs obligatoires de l'étape "Général".
         * @author Neris Alessandro
         * @returns {boolean}
         */
        validateGeneralStep() {
            this.errors = {};
            this.formError = "";

            // Vérifier l'évènement
            if (!this.courseData.event.id) {
                this.errors.event = "Le champ n'est pas valide";
            }

            // Vérifier le nom
            if (!this.courseData.name || this.courseData.name.trim() === "" || this.courseData.name.length > 120) {
                this.errors.name = "Le champ n'est pas valide";
            }

            // Vérifier les dates de course
            if (!this.courseData.date.start) {
                this.errors.dateStart = "Le champ n'est pas valide";
            }
            if (!this.courseData.date.end) {
                this.errors.dateEnd = "Le champ n'est pas valide";
            }
            if (this.courseData.date.start && this.courseData.date.end) {
                if (
                    new Date(this.courseData.date.end) <
                    new Date(this.courseData.date.start)
                ) {
                    this.errors.dateEnd =
                        "La date de fin doit être après la date de début";
                }
            }

            // Vérifier les dates d'inscription
            if (!this.courseData.date.inscriptionStart) {
                this.errors.inscriptionStart = "Le champ n'est pas valide";
            }
            if (!this.courseData.date.inscriptionEnd) {
                this.errors.inscriptionEnd = "Le champ n'est pas valide";
            }
            if (
                this.courseData.date.inscriptionStart &&
                this.courseData.date.inscriptionEnd
            ) {
                if (
                    new Date(this.courseData.date.inscriptionEnd) <
                    new Date(this.courseData.date.inscriptionStart)
                ) {
                    this.errors.inscriptionEnd =
                        "La date de fin d'inscription doit être après la date de début";
                }
            }

            if (
                this.courseData.distance === "" ||
                this.courseData.distance === null
            ) {
                this.errors.distance = "Le champ n'est pas valide";
            } else if (Number(this.courseData.distance) < 0) {
                this.errors.distance =
                    "La distance doit être un nombre positif";
            }

            if (
                this.courseData.maxRunners === "" ||
                this.courseData.maxRunners === null
            ) {
                this.errors.maxRunners = "Le champ n'est pas valide";
            } else if (Number(this.courseData.maxRunners) < 1) {
                this.errors.maxRunners =
                    "Le nombre maximum de coureurs doit être un nombre positif";
            }

            // Vérifier le tarif (si pas de prix évolutif)
            if (!this.courseData.parameters.prixEvolutif) {
                if (
                    this.courseData.tarif === "" ||
                    this.courseData.tarif === null
                ) {
                    this.errors.tarif = "Le champ n'est pas valide";
                } else if (Number(this.courseData.tarif) < 0) {
                    this.errors.tarif = "Le tarif doit être un nombre positif";
                }
            }

            // Vérifier le type de course
            if (!this.courseData.type.name) {
                this.errors.type = "Le champ n'est pas valide";
            }

            // Vérifier les dossards
            if (
                this.courseData.dossard.first === "" ||
                this.courseData.dossard.first === null
            ) {
                this.errors.firstDossard = "Le champ n'est pas valide";
            } else if (Number(this.courseData.dossard.first) < 1) {
                this.errors.firstDossard =
                    "Le premier dossard doit être supérieur à 0";
            }

            if (
                this.courseData.dossard.last === "" ||
                this.courseData.dossard.last === null
            ) {
                this.errors.lastDossard = "Le champ n'est pas valide";
            } else if (
                Number(this.courseData.dossard.last) <
                Number(this.courseData.dossard.first)
            ) {
                this.errors.lastDossard =
                    "Le dernier dossard doit être supérieur ou égal au premier dossard";
            } else if (
                Number(this.courseData.dossard.last) -
                    Number(this.courseData.dossard.first) +
                    1 <
                Number(this.courseData.maxRunners)
            ) {
                this.errors.lastDossard =
                    "L'intervalle de dossards doit pouvoir accueillir tous les coureurs";
            }

            // Vérifier les âges
            if (
                this.courseData.age.min === "" ||
                this.courseData.age.min === null
            ) {
                this.errors.ageMin = "Le champ n'est pas valide";
            } else if (Number(this.courseData.age.min) < 1) {
                this.errors.ageMin = "L'âge minimum doit être supérieur à 0";
            }

            if (
                this.courseData.age.max !== "" &&
                this.courseData.age.max !== null
            ) {
                if (
                    Number(this.courseData.age.max) <
                    Number(this.courseData.age.min)
                ) {
                    this.errors.ageMax =
                        "L'âge maximum doit être supérieur à l'âge minimum";
                }
            }

            // Vérifier le nombre de personnes pour les types Relais et Groupe
            if (
                this.courseData.type.name === "Relais" ||
                this.courseData.type.name === "Groupe"
            ) {
                if (
                    this.courseData.maxNbPersonne === "" ||
                    this.courseData.maxNbPersonne === null
                ) {
                    this.errors.maxNbPersonne = "Le champ n'est pas valide";
                } else if (Number(this.courseData.maxNbPersonne) < 1) {
                    this.errors.maxNbPersonne =
                        "Le nombre de personnes doit être supérieur à 0";
                }
            }

            return Object.keys(this.errors).length === 0;
        },

        /**
         * Valide et passe à l'étape suivante.
         * @author Neris Alessandro
         * @returns {void}
         */
        handleNextStep() {
            if (this.etape === formulaireEtape.GENERAL) {
                if (!this.validateGeneralStep()) {
                    return;
                }
            }
            const currentIndex = this.etapesActives.indexOf(this.etape);
            if (currentIndex < this.etapesActives.length - 1) {
                this.etape = this.etapesActives[currentIndex + 1];
            }
        },

        /**
         * Remet toutes les données du formulaire à leur état initial.
         * @author Neris Alessandro
         * @returns {void}
         */
        resetFormulaire() {
            this.courseData = {
                name: "",
                event: { name: "", id: "" },
                date: {
                    start: "",
                    end: "",
                    inscriptionStart: "",
                    inscriptionEnd: "",
                },
                distance: "",
                tarif: "",
                tarifInfo: "",
                popupInfo: "",
                type: { name: "", id: "" },
                maxRunners: "",
                maxNbPersonne: "",
                dossard: { first: "", last: "" },
                age: { min: "", max: "", conditionMineur: "" },
                tempsMoyen: "",
                parameters: {
                    actif: false,
                    dossardPersonalise: false,
                    challenge: false,
                    avertissement: false,
                    document: false,
                    questionnaire: false,
                    prixEvolutif: false,
                },
                category: { nom: "", id: "" },
                subCategory: { nom: "", id: "" },
                options: [],
                avertissement: { contenu: "", id: "" },
                document: { description: "" },
                challengeOrganisations: [],
                prixEvolutif: { type: "dossards", paliers: [] },
            };
            this.etape = formulaireEtape.GENERAL;
        },

        /**
         * Charge une course existante puis remplit les sections liées du formulaire.
         * @author Guillermet Jean-Daniel
         * @returns {Promise<void>}
         */
        async chargerDonneesCourse() {
            try {
                const response = await courseOrganisateurService.getCourse(
                    this.courseId,
                );
                if (
                    typeof response.data === "string" &&
                    response.data.includes("<!DOCTYPE html>")
                ) {
                    console.error("ERREUR : L'API a renvoyé une page HTML.");
                    return;
                }
                const course = response.data.course || response.data;
                this.courseData.name = course.nom || "";
                this.courseData.tarif = course.tarif || "";
                this.courseData.maxRunners = course.max_inscription || "";
                this.courseData.maxNbPersonne = course.max_nb_personne || "";
                console.log(
                    "maxNbPersonne chargé:",
                    this.courseData.maxNbPersonne,
                );
                this.courseData.dossard.first = course.premier_dossard || "";
                this.courseData.dossard.last = course.dernier_dossard || "";
                this.courseData.age.min = course.age_minimum || "";
                this.courseData.age.max = course.age_maximum || "";
                this.courseData.distance = course.distance || "";
                this.courseData.popupInfo = course.pop_info || "";
                this.courseData.category = course.categorie || "";
                this.courseData.subCategory = course.sous_categorie || "";
                if (course.type)
                    this.courseData.type = { name: course.type, id: "" };
                if (course.date_debut)
                    this.courseData.date.start = String(
                        course.date_debut,
                    ).split("T")[0];
                if (course.date_fin)
                    this.courseData.date.end = String(course.date_fin).split(
                        "T",
                    )[0];
                if (course.debut_inscription)
                    this.courseData.date.inscriptionStart = String(
                        course.debut_inscription,
                    ).split("T")[0];
                if (course.fin_inscription)
                    this.courseData.date.inscriptionEnd = String(
                        course.fin_inscription,
                    ).split("T")[0];
                this.courseData.parameters.actif =
                    course.is_actif == 1 || course.is_actif === true;
                this.courseData.parameters.dossardPersonalise =
                    course.is_dossard == 1 || course.is_dossard === true;
                this.courseData.parameters.challenge =
                    course.is_challenge == 1 || course.challenge === true;
                this.courseData.parameters.avertissement =
                    course.is_avertissement == 1 ||
                    course.is_avertissement === true;
                this.courseData.parameters.document =
                    course.is_document == 1 || course.is_document === true;
                this.courseData.parameters.questionnaire =
                    course.is_questionnaire == 1 ||
                    course.is_questionnaire === true;
                if (course.is_avertissement && course.avertissement) {
                    this.courseData.avertissement.contenu =
                        course.avertissement.contenu;
                    this.courseData.avertissement.id = course.avertissement.id;
                }
                if (course.id_evenement && this.evenements.length > 0) {
                    this.courseData.event = this.evenements.find(
                        (e) => e.id === course.id_evenement,
                    ) || { name: "", id: "" };
                }
                if (course.options && Array.isArray(course.options)) {
                    this.courseData.options = course.options.map((opt) => ({
                        id: opt.id,
                        nom: opt.nom,
                        description: opt.description,
                        tarif: opt.tarif,
                        type: opt.type,
                        quantifiable: opt.quantifiable
                            ? {
                                  quantiteMin: opt.quantifiable.quantiteMin,
                                  quantiteMax: opt.quantifiable.quantiteMax,
                              }
                            : { quantiteMin: 0, quantiteMax: 0 },
                    }));
                }

                // Charger la description des documents si présente
                if (
                    this.courseData.parameters.document &&
                    course.document_description
                ) {
                    this.courseData.document.description =
                        course.document_description;
                }

                // Charger les questions liées à la course
                if (
                    this.courseData.parameters.questionnaire &&
                    course.questions &&
                    Array.isArray(course.questions)
                ) {
                    this.courseData.questions = course.questions.map((q) => ({
                        id: q.id,
                        enonce: q.enonce,
                        choix: q.choix || [],
                    }));
                }

                console.log("Course chargée avec succès :", course);
            } catch (e) {
                console.error("Erreur globale chargement course:", e);
            }

            if (this.courseData.parameters.challenge) {
                try {
                    const orgsResp =
                        await challengeOrganisationService.getOrganisations(
                            this.courseId,
                        );
                    this.courseData.challengeOrganisations = orgsResp.data;
                } catch (e) {
                    console.error(
                        "Erreur chargement organisations challenge:",
                        e,
                    );
                }
            }

            try {
                const prixResp = await prixEvolutifService.getPaliers(
                    this.courseId,
                );
                if (prixResp.data && prixResp.data.length > 0) {
                    this.courseData.parameters.prixEvolutif = true;
                    this.courseData.prixEvolutif.type = prixResp.data[0].type;
                    this.courseData.prixEvolutif.paliers = prixResp.data;
                }
            } catch (e) {
                console.error("Erreur chargement prix évolutif:", e);
            }
        },

        // ── Prix évolutif ────────────────────────────────────────────────────
        /**
         * Initialise les paliers de prix évolutif selon le mode choisi.
         * @author Guillermet Jean-Daniel 
         * @returns {void}
         */
        initialiserPaliers() {
            if (!this.courseData.parameters.prixEvolutif) return;
            if (this.courseData.prixEvolutif.paliers.length === 0) {
                if (this.courseData.prixEvolutif.type === "dossards") {
                    const premier = Number(this.courseData.dossard.first) || 1;
                    const dernier = Number(this.courseData.dossard.last) || 999;
                    const milieu = Math.floor((premier + dernier) / 2);
                    this.courseData.prixEvolutif.paliers = [
                        {
                            valeur_debut: String(premier),
                            valeur_fin: String(milieu),
                            tarif: "",
                            ordre: 1,
                        },
                        {
                            valeur_debut: String(milieu + 1),
                            valeur_fin: String(dernier),
                            tarif: "",
                            ordre: 2,
                        },
                    ];
                } else {
                    // Dates : utiliser les dates d'inscription de la course
                    const dateDebut =
                        this.courseData.date.inscriptionStart || "";
                    const dateFin = this.courseData.date.inscriptionEnd || "";
                    this.courseData.prixEvolutif.paliers = [
                        {
                            valeur_debut: dateDebut,
                            valeur_fin: "",
                            tarif: "",
                            ordre: 1,
                        },
                        {
                            valeur_debut: "",
                            valeur_fin: dateFin,
                            tarif: "",
                            ordre: 2,
                        },
                    ];
                }
            }
        },

        /**
         * Change le mode de prix évolutif et réinitialise les paliers si nécessaire.
         * @author Guillermet Jean-Daniel
         * @param {string} nouveauMode
         * @returns {void}
         */
        changerModePrixEvolutif(nouveauMode) {
            if (this.courseData.prixEvolutif.type === nouveauMode) return;
            const palierModifie =
                this.courseData.prixEvolutif.paliers.length > 2 ||
                this.courseData.prixEvolutif.paliers.some(
                    (p) => p.tarif !== "" && p.tarif !== null,
                );
            if (palierModifie) {
                if (
                    !confirm(
                        "Changer de mode supprimera tous les paliers existants. Continuer ?",
                    )
                )
                    return;
            }
            this.courseData.prixEvolutif.type = nouveauMode;
            this.courseData.prixEvolutif.paliers = [];
            this.$nextTick(() => this.initialiserPaliers());
        },

        /**
         * Ajoute un palier intermédiaire entre le premier et le dernier.
         * @author Guillermet Jean-Daniel
         * @returns {void}
         */
        ajouterPalierIntermediaire() {
            const paliers = this.courseData.prixEvolutif.paliers;
            if (paliers.length < 2) {
                this.initialiserPaliers();
                return;
            }
            const dernier = paliers[paliers.length - 1];
            const nouveauPalier = {
                valeur_debut: "",
                valeur_fin: "",
                tarif: "",
                ordre: paliers.length,
            };
            dernier.ordre = paliers.length + 1;
            paliers.splice(- 1, 0, nouveauPalier);
        },

        /**
         * Supprime un palier de prix évolutif et réordonne la liste restante.
         * @author Guillermet Jean-Daniel
         * @param {number} index
         * @param {object} palier
         * @returns {Promise<void>}
         */
        async supprimerPalier(index, palier) {
            if (palier.id && this.isEditMode)
                await prixEvolutifService.deletePalier(palier.id);
            this.courseData.prixEvolutif.paliers.splice(index, 1);
            this.courseData.prixEvolutif.paliers.forEach((p, i) => {
                p.ordre = i + 1;
            });
        },

        /**
         * Ouvre ou ferme le menu de sélection d'élément.
         * @author Neris Alessandro
         * @returns {void}
         */
        handleModalState() {
            this.modal =
                this.modal === optionModal.FERMEE
                    ? optionModal.SELECTION
                    : optionModal.FERMEE;
        },

        /**
         * Traite le choix effectué dans le menu d'ajout d'option ou de question.
         * @author Neris Alessandro
         * @param {string} option
         * @returns {void}
         */
        handleOptionSelection(option) {
            if (option === "Existant") {
                this.modal = optionModal.EXISTANT;
            } else if (option === "Nouveau") {
                if (this.etape === formulaireEtape.OPTIONS)
                    this.courseData.options.push({
                        nom: "",
                        description: "",
                        tarif: "",
                        quantifiable: { quantiteMin: 0, quantiteMax: 0 },
                    });
                else if (this.etape === formulaireEtape.QUESTIONNAIRE)
                    this.courseData.questions.push({ enonce: "", choix: [] });
                this.modal = optionModal.FERMEE;
            } else if (this.modal === optionModal.EXISTANT) {
                if (this.etape === formulaireEtape.OPTIONS) {
                    const { id, ...optionSansId } = this.optionModels.find(
                        (o) => o.nom === option,
                    );
                    this.courseData.options.push(optionSansId);
                } else if (this.etape === formulaireEtape.QUESTIONNAIRE) {
                    const questionModele = this.questionModels.find(
                        (q) => q.enonce === option,
                    );
                    if (questionModele) {
                        this.courseData.questions.push({
                            enonce: questionModele.enonce,
                            choix: (questionModele.choix || []).map(
                                (choix) => ({
                                    texte_option: choix.texte_option,
                                }),
                            ),
                        });
                    }
                }
            }
        },

        /**
         * Retire une option ou une question du formulaire lors de la déselection.
         * @author Neris Alessandro
         * @param {string|object} element - Le nom de l'option ou l'énoncé de la question
         * @returns {void}
         */
        handleOptionDeselection(element) {
            if (this.etape === formulaireEtape.OPTIONS) {
                // Retirer l'option par son nom
                const index = this.courseData.options.findIndex(
                    (o) => o.nom === element,
                );
                if (index !== -1) {
                    this.courseData.options.splice(index, 1);
                }
            } else if (this.etape === formulaireEtape.QUESTIONNAIRE) {
                // Retirer la question par son énoncé
                const index = this.courseData.questions.findIndex(
                    (q) => q.enonce === element,
                );
                if (index !== -1) {
                    this.courseData.questions.splice(index, 1);
                }
            }
        },

        /**
         * Affecte l'évènement sélectionné à la course courante.
         * @author Neris Alessandro
         * @param {object} event
         * @returns {void}
         */
        selectEvent(event) {
            this.courseData.event = event;
            FlowbiteInstances.getInstance("Dropdown", "dropdownEvent").hide();
        },
        /**
         * Affecte le type de course choisi.
         * @author Neris Alessandro
         * @param {object} type
         * @returns {void}
         */
        selectType(type) {
            this.courseData.type = type;
            FlowbiteInstances.getInstance("Dropdown", "dropdownType").hide();
        },
        /**
         * Affecte la catégorie choisie.
         * @author Neris Alessandro
         * @param {object} category
         * @returns {void}
         */
        selectCategory(category) {
            this.courseData.category = category;
            FlowbiteInstances.getInstance(
                "Dropdown",
                "dropdownCategory",
            ).hide();
        },
        /**
         * Affecte la sous-catégorie choisie.
         * @author Neris Alessandro
         * @param {object} subCategory
         * @returns {void}
         */
        selectSubCategory(subCategory) {
            this.courseData.subCategory = subCategory;
            FlowbiteInstances.getInstance(
                "Dropdown",
                "dropdownSubcategory",
            ).hide();
        },

        /**
         * Retire une option de la course et supprime la liaison existante si besoin.
         * @author Neris Alessandro
         * @param {number} index
         * @returns {Promise<void>}
         */
        async removeOption(index) {
            const option = this.courseData.options[index];
            if (option.id) {
                await optionOrganisateurService.deleteOption(option.id);
            }
            this.courseData.options.splice(index, 1);
        },

        /**
         * Prépare le payload API d'une option de course.
         * @param {object} option
         * @returns {object}
         * @author Neris Alessandro
         */
        buildOptionPayload(option) {
            const payload = {
                nom: option.nom,
                description: option.description,
                tarif: option.tarif,
                type: option.type,
                modele: false,
            };
            if (option.type === "Quantifiable") {
                payload.quantiteMin =
                    Number.parseInt(option.quantifiable?.quantiteMin) || 0;
                payload.quantiteMax =
                    Number.parseInt(option.quantifiable?.quantiteMax) || 0;
            }
            return payload;
        },
        /**
         * Change le mode de prix évolutif et déclenche une confirmation si nécessaire.
         * @author Guillermet Jean-Daniel
         * @param {string} nouveauMode
         * @returns {void}
         */
        changerModePrixEvolutif(nouveauMode) {
            if (this.courseData.prixEvolutif.type === nouveauMode) return;
            if (this.courseData.prixEvolutif.paliers.length > 0) {
                this.modePrixEvolutifEnAttente = nouveauMode;
                this.confirmationChangementModePrix = true;
                return;
            }
            this.courseData.prixEvolutif.type = nouveauMode;
            this.courseData.prixEvolutif.paliers = [];
        },
        /**
         * Valide le changement de mode de prix évolutif en attente.
         * @author Guillermet Jean-Daniel
         * @returns {void}
         */
        confirmerChangementModePrixEvolutif() {
            if (!this.modePrixEvolutifEnAttente) return;
            this.courseData.prixEvolutif.type = this.modePrixEvolutifEnAttente;
            this.courseData.prixEvolutif.paliers = [];
            this.modePrixEvolutifEnAttente = null;
            this.confirmationChangementModePrix = false;
        },
        /**
         * Annule le changement de mode de prix évolutif en attente.
         * @author Guillermet Jean-Daniel
         * @returns {void}
         */
        annulerChangementModePrixEvolutif() {
            this.modePrixEvolutifEnAttente = null;
            this.confirmationChangementModePrix = false;
        },

        /**
         * Ajoute un nouveau palier vide à la configuration de prix évolutif.
         * @author Guillermet Jean-Daniel
         * @returns {void}
         */
        ajouterPalier() {
            const ordre = this.courseData.prixEvolutif.paliers.length + 1;
            this.courseData.prixEvolutif.paliers.push({
                valeur_debut: "",
                valeur_fin: "",
                tarif: "",
                ordre,
            });
        },

        /**
         * Crée ou met à jour la course puis synchronise les entités associées.
         * @author Neris Alessandro, Guillermet Jean-Daniel
         * @returns {Promise<void>}
         */
        async insertCourse() {
            try {
                let id_avertissement = this.courseData.avertissement.id || null;
                if (
                    this.courseData.parameters.avertissement &&
                    this.courseData.avertissement.contenu
                ) {
                    const avertissementPayload = {
                        titre: this.courseData.name,
                        contenu: this.courseData.avertissement.contenu,
                    };
                    if (this.isEditMode && id_avertissement) {
                        await avertissementOrganisateurService.modifyAvertissement(
                            id_avertissement,
                            avertissementPayload,
                        );
                    } else {
                        const avertissementResponse =
                            await avertissementOrganisateurService.createAvertissement(
                                avertissementPayload,
                            );
                        id_avertissement =
                            avertissementResponse.data.avertissement.id;
                    }
                }

                const payload = {
                    id_evenement: this.courseData.event.id,
                    nom: this.courseData.name,
                    date_debut: this.courseData.date.start || null,
                    date_fin: this.courseData.date.end || null,
                    debut_inscription:
                        this.courseData.date.inscriptionStart || null,
                    fin_inscription:
                        this.courseData.date.inscriptionEnd || null,
                    tarif: this.courseData.parameters.prixEvolutif
                        ? 0
                        : this.courseData.tarif,
                    max_inscription: this.courseData.maxRunners,
                    max_nb_personne: this.courseData.maxNbPersonne || null,
                    premier_dossard: this.courseData.dossard.first,
                    dernier_dossard: this.courseData.dossard.last,
                    age_minimum: this.courseData.age.min,
                    age_maximum: this.courseData.age.max,
                    distance: this.courseData.distance,
                    status: "actif",
                    type: this.courseData.type.name,
                    is_actif: Boolean(this.courseData.parameters.actif),
                    is_dossard: Boolean(
                        this.courseData.parameters.dossardPersonalise,
                    ),
                    is_avertissement: Boolean(
                        this.courseData.parameters.avertissement,
                    ),
                    is_challenge: Boolean(this.courseData.parameters.challenge),
                    is_document: Boolean(this.courseData.parameters.document),
                    is_questionnaire: Boolean(
                        this.courseData.parameters.questionnaire,
                    ),
                    document_description: this.courseData.parameters.document
                        ? this.courseData.document.description
                        : null,
                    id_avertissement: id_avertissement,
                    id_categorie: this.courseData.category.id,
                    id_sous_categorie: this.courseData.subCategory.id,
                    is_prix_evolutif: Boolean(
                        this.courseData.parameters.prixEvolutif,
                    ),
                };
                console.log(
                    "maxNbPersonne envoyé:",
                    this.courseData.maxNbPersonne,
                );
                console.log(
                    "payload max_nb_personne:",
                    payload.max_nb_personne,
                );
                let response;
                if (this.isEditMode) {
                    response = await courseOrganisateurService.modifyCourse(
                        this.courseId,
                        payload,
                    );
                } else {
                    response =
                        await courseOrganisateurService.createCourse(payload);
                }

                const courseId = this.isEditMode
                    ? this.courseId
                    : response.data.course.id;

                if (this.courseData.parameters.prixEvolutif) {
                    const nouveauxPaliers =
                        this.courseData.prixEvolutif.paliers.filter(
                            (p) => !p.id,
                        );
                    for (const palier of nouveauxPaliers) {
                        if (
                            palier.valeur_debut !== "" &&
                            palier.valeur_debut !== null &&
                            palier.tarif
                        ) {
                            await prixEvolutifService.createPalier({
                                id_course: courseId,
                                type: this.courseData.prixEvolutif.type,
                                valeur_debut: String(palier.valeur_debut),
                                valeur_fin:
                                    palier.valeur_fin !== "" &&
                                    palier.valeur_fin !== null
                                        ? String(palier.valeur_fin)
                                        : null,
                                tarif: Number.parseFloat(palier.tarif),
                                ordre: palier.ordre,
                            });
                        }
                    }
                } else if (this.isEditMode) {
                    await prixEvolutifService.deleteAllPaliers(courseId);
                }

                if (this.courseData.parameters.challenge) {
                    const nouvellesOrgs =
                        this.courseData.challengeOrganisations.filter(
                            (o) => !o.id,
                        );
                    for (const org of nouvellesOrgs) {
                        await challengeOrganisationService.createOrganisation({
                            id_course: courseId,
                            nom: org.nom,
                            type: org.type,
                        });
                    }
                }

                const optionsExistantes = this.courseData.options.filter(
                    (o) => o.id,
                );
                for (const option of optionsExistantes)
                    await optionOrganisateurService.modifyOption(
                        option.id,
                        this.buildOptionPayload(option),
                    );
                const optionsNouvelles = this.courseData.options.filter(
                    (o) => !o.id,
                );
                for (const option of optionsNouvelles) {
                    const optionResponse =
                        await optionOrganisateurService.createOption(
                            this.buildOptionPayload(option),
                        );
                    await optionCourseService.createOptionCourse({
                        id_course: courseId,
                        id_option: optionResponse.data.option.id,
                    });
                }

                const questionsExistantes = this.courseData.questions.filter(
                    (q) => q.id,
                );
                for (const question of questionsExistantes) {
                    await questionOrganisateurService.modifyQuestion(
                        question.id,
                        { enonce: question.enonce, modele: false },
                    );
                    for (const choix of question.choix ?? []) {
                        if (choix.id)
                            await optionQuestionOrganisateurService.modifyChoix(
                                choix.id,
                                { texte_option: choix.texte_option },
                            );
                        else
                            await optionQuestionOrganisateurService.createChoix(
                                question.id,
                                { texte_option: choix.texte_option },
                            );
                    }
                }
                const questionsNouvelles = this.courseData.questions.filter(
                    (q) => !q.id,
                );
                const nouvellesAvecId = [];
                for (const question of questionsNouvelles) {
                    const questionResponse =
                        await questionOrganisateurService.createQuestion({
                            enonce: question.enonce,
                            modele: false,
                            ids_courses: [courseId],
                        });
                    const questionId = questionResponse.data.question.id;
                    nouvellesAvecId.push({ ...question, id: questionId });
                    for (const choix of question.choix ?? [])
                        await optionQuestionOrganisateurService.createChoix(
                            questionId,
                            { texte_option: choix.texte_option },
                        );
                }
                const toutesLesQuestions = [
                    ...this.courseData.questions.filter((q) => q.id),
                    ...nouvellesAvecId,
                ];
                if (toutesLesQuestions.length > 0) {
                    await courseQuestionOrganisateurService.reordonnerQuestions(
                        courseId,
                        {
                            questions: toutesLesQuestions.map((q, index) => ({
                                id_question: q.id,
                                ordre: index + 1,
                            })),
                        },
                    );
                }

                this.confirmPopup();
            } catch (e) {
                console.log("Erreur:", e.response?.data);
            }
        },

        /**
         * Affiche le message de succès puis redirige en mode édition si nécessaire.
         * @author Guillermet Jean-Daniel
         * @returns {void}
         */
        confirmPopup() {
            this.confirmationPopup = false;
            this.dataInserted = true;
            setTimeout(() => {
                this.dataInserted = false;
                if (this.courseData.event.id)
                    this.$router.push(
                        `/organisateur/evenements/${this.courseData.event.id}/courses`,
                    );
                else this.$router.push(`/organisateur/evenements`);
            }, 2000);
        },

        /**
         * Ajoute une organisation challenge locale en évitant les doublons.
         * @author Guillermet Jean-Daniel
         * @returns {void}
         */
        ajouterOrganisation() {
            if (!this.nouvelleOrg.nom.trim()) return;
            const doublon = this.courseData.challengeOrganisations.some(
                (o) =>
                    o.nom.trim().toLowerCase() ===
                        this.nouvelleOrg.nom.trim().toLowerCase() &&
                    o.type === this.nouvelleOrg.type,
            );
            if (doublon) return;
            this.courseData.challengeOrganisations.push({
                nom: this.nouvelleOrg.nom.trim(),
                type: this.nouvelleOrg.type,
            });
            this.nouvelleOrg.nom = "";
        },

        /**
         * Supprime une organisation challenge du formulaire et de l'API si besoin.
         * @author Guillermet Jean-Daniel
         * @param {number} index
         * @param {object} org
         * @returns {Promise<void>}
         */
        async supprimerOrganisation(index, org) {
            if (org.id && this.isEditMode)
                await challengeOrganisationService.deleteOrganisation(org.id);
            this.courseData.challengeOrganisations.splice(index, 1);
        },
    },

    async mounted() {
        initDropdowns();
        try {
            const response =
                await evenementOrganisateurService.getAllEvenements();
            this.evenements = response.data;
            // Pré-remplir le champ événement si l'ID est présent dans l'URL
            if (this.eventIdFromUrl && !this.isEditMode) {
                const evenementSelectionne = this.evenements.find(
                    (e) => e.id === Number.parseInt(this.eventIdFromUrl),
                );
                if (evenementSelectionne) {
                    this.courseData.event = evenementSelectionne;
                }
            }
        } catch (e) {
            console.log("Erreur lors de la récupération de l'évènement ", e);
        }
        try {
            const response = await optionOrganisateurService.getAllOptions();
            this.optionModels = response.data;
        } catch (e) {
            console.error("Erreur lors de la récupération des options: ", e);
        }
        try {
            const response =
                await questionOrganisateurService.getAllQuestions();
            this.questionModels = response.data;
        } catch (e) {
            console.error("Erreur lors de la récupération des questions: ", e);
        }
        try {
            const response =
                await categorieOrganisateurService.getAllCategorie();
            this.categories = response.data;
        } catch (e) {
            console.error("Erreur lors de la récupération des categories: ", e);
        }
        try {
            const response =
                await sousCategorieOrganisateurService.getAllSousCategorie();
            this.subCategories = response.data;
        } catch (e) {
            console.error(
                "Erreur lors de la récupération des sous categories: ",
                e,
            );
        }
        try {
            const response =
                await avertissementOrganisateurService.getAllAvertissement();
            this.avertissementModels = response.data;
        } catch (e) {
            console.error(
                "Erreur lors de la récupération des avertissements: ",
                e,
            );
        }
        if (this.isEditMode) await this.chargerDonneesCourse();
    },
};
</script>
