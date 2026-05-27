<template>
    <div
        class="auth-bg min-h-screen flex items-center justify-center px-4 py-8"
    >
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-8">
            <!-- Logo de l'application -->
            <div class="flex justify-center mb-4">
                <img
                    src="/images/rgva-logo.png"
                    alt="Running Geneva"
                    class="h-20 object-contain"
                />
            </div>

            <!-- Titre de bienvenue et message contextuel selon l'étape courante -->
            <p class="text-center text-body mb-6">
                <strong>Bienvenue!</strong><br />
                Veuillez entrer ces informations :
                <span v-if="currentStep === 3"
                    ><br /><strong>Vous y êtes presque !</strong></span
                >
            </p>

            <!-- Indicateur visuel de progression entre les 3 étapes -->
            <IndicateurEtapes :steps="steps" :currentStep="currentStep" />

            <!-- ======================================== -->
            <!-- ÉTAPE 1 : Identifiants                  -->
            <!-- Prénom, nom, email, mot de passe        -->
            <!-- ======================================== -->
            <div v-if="currentStep === 1" class="space-y-4">
                <!-- Ligne Prénom / Nom -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-label block mb-1"
                            >Prénom : <span class="text-accent">*</span></label
                        >
                        <input
                            v-model="form.prenom"
                            type="text"
                            class="input-field w-full"
                            :class="{ 'border-accent': errors.prenom }"
                        />
                        <p
                            v-if="errors.prenom"
                            class="text-accent text-label mt-1"
                        >
                            {{ errors.prenom }}
                        </p>
                    </div>
                    <div>
                        <label class="text-label block mb-1"
                            >Nom : <span class="text-accent">*</span></label
                        >
                        <input
                            v-model="form.nom"
                            type="text"
                            class="input-field w-full"
                            :class="{ 'border-accent': errors.nom }"
                        />
                        <p
                            v-if="errors.nom"
                            class="text-accent text-label mt-1"
                        >
                            {{ errors.nom }}
                        </p>
                    </div>
                </div>

                <!-- Champ email avec icône décorative à gauche -->
                <div>
                    <label class="text-label block mb-1"
                        >Votre adresse mail :
                        <span class="text-accent">*</span></label
                    >
                    <div class="relative">
                        <span
                            class="absolute inset-y-0 left-3 flex items-center text-primary-300"
                        >
                            <!-- Icône enveloppe -->
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                />
                            </svg>
                        </span>
                        <input
                            v-model="form.email"
                            type="email"
                            class="input-field w-full pl-10"
                            :class="{ 'border-accent': errors.email }"
                        />
                    </div>
                    <p v-if="errors.email" class="text-accent text-label mt-1">
                        {{ errors.email }}
                    </p>
                </div>

                <!-- Champ mot de passe avec bascule visibilité -->
                <div>
                    <label class="text-label block mb-1"
                        >Votre mot de passe :
                        <span class="text-accent">*</span></label
                    >
                    <div class="relative">
                        <span
                            class="absolute inset-y-0 left-3 flex items-center text-primary-300"
                        >
                            <!-- Icône cadenas -->
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                />
                            </svg>
                        </span>
                        <!-- Le type bascule entre 'text' et 'password' selon showPassword -->
                        <input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            class="input-field w-full pl-10 pr-10"
                            :class="{ 'border-accent': errors.password }"
                        />
                        <!-- Bouton œil pour afficher/masquer le mot de passe -->
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-3 flex items-center text-primary-300 hover:text-primary"
                        >
                            <!-- Icône œil ouvert (mot de passe masqué) -->
                            <svg
                                v-if="!showPassword"
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                />
                            </svg>
                            <!-- Icône œil barré (mot de passe visible) -->
                            <svg
                                v-else
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21"
                                />
                            </svg>
                        </button>
                    </div>
                    <p
                        v-if="errors.password"
                        class="text-accent text-label mt-1"
                    >
                        {{ errors.password }}
                    </p>
                </div>

                <!-- Champ confirmation du mot de passe avec bascule visibilité indépendante -->
                <div>
                    <label class="text-label block mb-1"
                        >Répétez votre mot de passe :
                        <span class="text-accent">*</span></label
                    >
                    <div class="relative">
                        <span
                            class="absolute inset-y-0 left-3 flex items-center text-primary-300"
                        >
                            <!-- Icône cadenas -->
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                />
                            </svg>
                        </span>
                        <!-- Type géré indépendamment de showPassword -->
                        <input
                            v-model="form.passwordConfirm"
                            :type="showPasswordConfirm ? 'text' : 'password'"
                            class="input-field w-full pl-10 pr-10"
                            :class="{ 'border-accent': errors.passwordConfirm }"
                        />
                        <!-- Bouton œil indépendant pour la confirmation -->
                        <button
                            type="button"
                            @click="showPasswordConfirm = !showPasswordConfirm"
                            class="absolute inset-y-0 right-3 flex items-center text-primary-300 hover:text-primary"
                        >
                            <!-- Icône œil ouvert -->
                            <svg
                                v-if="!showPasswordConfirm"
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                />
                            </svg>
                            <!-- Icône œil barré -->
                            <svg
                                v-else
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21"
                                />
                            </svg>
                        </button>
                    </div>
                    <p
                        v-if="errors.passwordConfirm"
                        class="text-accent text-label mt-1"
                    >
                        {{ errors.passwordConfirm }}
                    </p>
                </div>
            </div>

            <!-- ============================================= -->
            <!-- ÉTAPE 2 : Profil                             -->
            <!-- Genre, date de naissance, téléphone, club,  -->
            <!-- nationalité                                  -->
            <!-- ============================================= -->
            <div v-if="currentStep === 2" class="space-y-4">
                <!-- Sélection du genre par boutons toggles -->
                <div>
                    <label class="text-label block mb-1"
                        >Genre : <span class="text-accent">*</span></label
                    >
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Bouton Femme : actif si form.genre === 'Femme' -->
                        <button
                            @click="form.genre = 'Femme'"
                            class="py-3 rounded-xl border-2 text-body font-medium transition-all"
                            :class="
                                form.genre === 'Femme'
                                    ? 'border-primary bg-primary text-white'
                                    : 'border-gray-300 bg-white text-primary hover:border-primary'
                            "
                        >
                            Femme
                        </button>
                        <!-- Bouton Homme : actif si form.genre === 'Homme' -->
                        <button
                            @click="form.genre = 'Homme'"
                            class="py-3 rounded-xl border-2 text-body font-medium transition-all"
                            :class="
                                form.genre === 'Homme'
                                    ? 'border-primary bg-primary text-white'
                                    : 'border-gray-300 bg-white text-primary hover:border-primary'
                            "
                        >
                            Homme
                        </button>
                    </div>
                    <p v-if="errors.genre" class="text-accent text-label mt-1">
                        {{ errors.genre }}
                    </p>
                </div>

                <!-- Ligne Date de naissance / Téléphone -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Champ date avec saisie manuelle (JJ/MM/AAAA) et calendrier natif caché -->
                    <div>
                        <label
                            class="block mb-1 text-label text-primary font-normal"
                            >Date de naissance :
                            <span class="text-accent">*</span></label
                        >
                        <div class="relative">
                            <!-- Saisie textuelle avec auto-formatage via formaterDate() -->
                            <input
                                v-model="form.dateNaissance"
                                type="text"
                                placeholder="JJ/MM/AAAA"
                                maxlength="10"
                                class="input-field w-full pr-10"
                                :class="{
                                    'border-accent': errors.dateNaissance,
                                }"
                                @input="formaterDate"
                            />
                            <!-- Bouton calendrier : ouvre le date picker natif du navigateur -->
                            <button
                                type="button"
                                @click="datePickerRef.showPicker()"
                                class="absolute inset-y-0 right-3 flex items-center text-primary-300 hover:text-primary"
                            >
                                <!-- Icône calendrier -->
                                <svg
                                    class="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                            </button>

                            <!--
                                Input date natif rendu invisible (opacity-0, taille zéro).
                                Déclenché programmatiquement via datePickerRef.showPicker().
                                Sa valeur est convertie au format JJ/MM/AAAA par dateDepuisCalendrier().
                                L'attribut max empêche de sélectionner une date future.
                            -->
                            <input
                                ref="datePickerRef"
                                type="date"
                                class="absolute opacity-0 w-0 h-0 top-0 right-0"
                                :max="new Date().toISOString().split('T')[0]"
                                @change="dateDepuisCalendrier"
                            />
                        </div>
                        <p
                            v-if="errors.dateNaissance"
                            class="text-accent text-label mt-1"
                        >
                            {{ errors.dateNaissance }}
                        </p>
                    </div>

                    <!-- Champ téléphone avec auto-formatage via formaterTelephone() -->
                    <div>
                        <label class="text-label block mb-1"
                            >N° téléphone :
                            <span class="text-accent">*</span></label
                        >
                        <input
                            v-model="form.telephone"
                            type="tel"
                            class="input-field w-full"
                            :class="{ 'border-accent': errors.telephone }"
                            @input="formaterTelephone"
                        />
                        <p
                            v-if="errors.telephone"
                            class="text-accent text-label mt-1"
                        >
                            {{ errors.telephone }}
                        </p>
                    </div>
                </div>

                <!-- Champ optionnel : club, équipe ou entreprise du participant -->
                <div>
                    <label class="text-label block mb-1"
                        >Équipe, Club, Entreprise :</label
                    >
                    <input
                        v-model="form.club"
                        type="text"
                        class="input-field w-full"
                    />
                </div>

                <!-- Combobox nationalité via composant dédié SelectNationalite -->
                <div>
                    <label class="text-label block mb-1"
                        >Votre nationalité :
                        <span class="text-accent">*</span></label
                    >
                    <!--
                        SelectNationalite gère en interne la liste des pays et la recherche.
                        La classe border-accent est ajoutée dynamiquement en cas d'erreur.
                    -->
                    <SelectNationalite
                        v-model="form.nationalite"
                        :inputClass="
                            'input-field w-full pr-8' +
                            (errors.nationalite ? ' border-accent' : '')
                        "
                    />
                    <p
                        v-if="errors.nationalite"
                        class="text-accent text-label mt-1"
                    >
                        {{ errors.nationalite }}
                    </p>
                </div>
            </div>

            <!-- ================================================= -->
            <!-- ÉTAPE 3 : Coordonnées                             -->
            <!-- Adresse (autocomplétion), NPA, commune,          -->
            <!-- taille T-shirt, photo de profil                  -->
            <!-- ================================================= -->
            <div v-if="currentStep === 3" class="space-y-4">
                <!-- Ligne Adresse (2/3) + Numéro de rue (1/3) -->
                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-2">
                        <label class="text-label block mb-1"
                            >Adresse : <span class="text-accent">*</span></label
                        >
                        <!--
                            ref="adresseRef" utilisé par handleAdresseClickOutside()
                            pour détecter les clics en dehors et fermer le dropdown.
                        -->
                        <div class="relative" ref="adresseRef">
                            <!--
                                @input déclenche rechercherAdresse() après un délai (debounce 300 ms)
                                pour interroger l'API geo.admin.ch à partir de 3 caractères.
                            -->
                            <input
                                v-model="form.adresse"
                                type="text"
                                class="input-field w-full"
                                :class="{ 'border-accent': errors.adresse }"
                                @input="rechercherAdresse(form.adresse)"
                                placeholder="Ex: Rue du Rhône"
                            />
                            <!-- Dropdown de suggestions d'adresses (visible si résultats disponibles) -->
                            <div
                                v-if="
                                    showAdresseDropdown &&
                                    adresseSuggestions.length > 0
                                "
                                class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto"
                            >
                                <!--
                                    @mousedown.prevent évite que le blur sur le champ input
                                    ne ferme le dropdown avant que le clic soit traité.
                                -->
                                <button
                                    v-for="(
                                        suggestion, index
                                    ) in adresseSuggestions"
                                    :key="index"
                                    @mousedown.prevent="
                                        selectionnerAdresse(suggestion)
                                    "
                                    class="w-full text-left px-4 py-2 text-body hover:bg-secondary-600 transition-colors"
                                >
                                    <!--
                                        Les labels retournés par l'API contiennent du HTML (balises <b>).
                                        On les nettoie avec replace(/<[^>]*>/g, '') avant affichage.
                                    -->
                                    {{
                                        suggestion.attrs.label.replace(
                                            /<[^>]*>/g,
                                            "",
                                        )
                                    }}
                                </button>
                            </div>
                        </div>
                        <p
                            v-if="errors.adresse"
                            class="text-accent text-label mt-1"
                        >
                            {{ errors.adresse }}
                        </p>
                    </div>

                    <!-- Numéro de rue (pré-rempli automatiquement par selectionnerAdresse()) -->
                    <div>
                        <label class="text-label block mb-1"
                            >N° <span class="text-accent">*</span></label
                        >
                        <input
                            v-model="form.numeroRue"
                            type="text"
                            class="input-field w-full"
                            :class="{ 'border-accent': errors.numeroRue }"
                        />
                        <p
                            v-if="errors.numeroRue"
                            class="text-accent text-label mt-1"
                        >
                            {{ errors.numeroRue }}
                        </p>
                    </div>
                </div>

                <!-- Ligne NPA / Commune (pré-remplis par selectionnerAdresse()) -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-label block mb-1"
                            >NPA : <span class="text-accent">*</span></label
                        >
                        <input
                            v-model="form.npa"
                            type="text"
                            class="input-field w-full"
                            :class="{ 'border-accent': errors.npa }"
                        />
                        <p
                            v-if="errors.npa"
                            class="text-accent text-label mt-1"
                        >
                            {{ errors.npa }}
                        </p>
                    </div>
                    <div>
                        <label class="text-label block mb-1"
                            >Commune : <span class="text-accent">*</span></label
                        >
                        <input
                            v-model="form.commune"
                            type="text"
                            class="input-field w-full"
                            :class="{ 'border-accent': errors.commune }"
                        />
                        <p
                            v-if="errors.commune"
                            class="text-accent text-label mt-1"
                        >
                            {{ errors.commune }}
                        </p>
                    </div>
                </div>

                <!-- Ligne Taille T-shirt / Photo de profil -->
                <div class="grid grid-cols-2 gap-4 items-start">
                    <!-- Sélect taille T-shirt, valeur par défaut L -->
                    <div>
                        <label class="text-label block mb-1"
                            >Taille T-Shirt :
                            <span class="text-accent">*</span></label
                        >
                        <select
                            v-model="form.tailleTshirt"
                            class="input-field w-full pr-8 appearance-none cursor-pointer"
                        >
                            <option value="XS">XS</option>
                            <option value="S">S</option>
                            <option value="M">M</option>
                            <option value="L">L</option>
                            <option value="XL">XL</option>
                            <option value="XXL">XXL</option>
                        </select>
                    </div>

                    <!-- Zone photo de profil : cercle cliquable + input file caché -->
                    <div>
                        <label class="text-label block mb-1"
                            >Photo de profil :</label
                        >
                        <div class="relative w-16 h-16">
                            <!--
                                Clic sur le cercle déclenche triggerFileInput()
                                qui transmet le clic à l'input file caché.
                            -->
                            <div
                                class="w-16 h-16 rounded-full bg-tertiary flex items-center justify-center cursor-pointer overflow-hidden"
                                @click="triggerFileInput"
                            >
                                <!-- Aperçu de la photo sélectionnée -->
                                <img
                                    v-if="photoPreview"
                                    :src="photoPreview"
                                    class="w-full h-full object-cover"
                                    alt="Photo profil"
                                />
                                <!-- Icône silhouette par défaut (aucune photo choisie) -->
                                <svg
                                    v-else
                                    class="w-8 h-8 text-primary"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    />
                                </svg>
                            </div>

                            <!-- Bouton de suppression de la photo (affiché uniquement si une photo est sélectionnée) -->
                            <button
                                v-if="photoPreview"
                                @click="
                                    photoPreview = null;
                                    form.photo = null;
                                "
                                class="absolute -top-1 -right-1 w-5 h-5 bg-white rounded-full flex items-center justify-center shadow text-primary-300 hover:text-accent"
                            >
                                <!-- Icône croix de suppression -->
                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>

                            <!-- Input file masqué, déclenché programmatiquement par triggerFileInput() -->
                            <input
                                ref="fileInput"
                                type="file"
                                accept="image/*"
                                class="hidden"
                                @change="handlePhotoChange"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================================================= -->
            <!-- Barre de navigation entre étapes                  -->
            <!-- ================================================= -->
            <div class="flex justify-between mt-8">
                <!-- Précédent : retour à l'étape n-1, ou redirection vers /login à l'étape 1 -->
                <button
                    @click="previousStep"
                    class="btn-accent-300 px-8 py-3 rounded-xl"
                >
                    Précédent
                </button>

                <!-- Suivant : visible aux étapes 1 et 2, déclenche la validation avant progression -->
                <button
                    v-if="currentStep < 3"
                    @click="nextStep"
                    class="btn-tertiary px-8 py-3 rounded-xl"
                >
                    Suivant
                </button>

                <!-- Soumettre : visible uniquement à l'étape 3, désactivé pendant le chargement -->
                <button
                    v-else
                    @click="handleRegister"
                    :disabled="chargement"
                    class="btn-tertiary px-8 py-3 rounded-xl"
                    :class="{ 'opacity-50 cursor-not-allowed': chargement }"
                >
                    {{
                        chargement ? "Création en cours..." : "Créer mon compte"
                    }}
                </button>
            </div>

            <!-- Message d'erreur global affiché sous la navigation (erreurs non liées à un champ précis) -->
            <p
                v-if="erreurGlobale"
                class="text-accent text-label text-center mt-2"
            >
                {{ erreurGlobale }}
            </p>
        </div>
    </div>
</template>

<script setup>
/**
 * @fileoverview Vue CreationCompte.
 * @description Parcours de création de compte participant en plusieurs étapes.
 * @remarks Cette vue orchestre la validation progressive des champs, l'autocomplétion
 * des coordonnées et la soumission finale du profil utilisateur.
 *
 * Flux général :
 *   Étape 1 (Identifiants) → validateStep1() → nextStep()
 *   Étape 2 (Profil)       → validateStep2() → nextStep()
 *   Étape 3 (Coordonnées)  → validateStep3() → handleRegister()
 *
 * Autocomplétion adresse :
 *   rechercherAdresse() → API geo.admin.ch → adresseSuggestions[]
 *   selectionnerAdresse() remplit form.adresse, form.numeroRue, form.npa, form.commune
 *
 * Gestion des erreurs backend :
 *   handleRegister() mappe les erreurs de l'API vers les champs du formulaire
 *   et repositionne automatiquement l'utilisateur sur l'étape concernée.
 */
import { ref, reactive, computed, onMounted, onBeforeUnmount } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import IndicateurEtapes from "../components/IndicateurEtapes.vue";
import SelectNationalite from "../components/SelectNationalite.vue";

const router = useRouter();
const authStore = useAuthStore();

const currentStep = ref(1);
const steps = ["Identifiants", "Profil", "Coordonnées"];
const fileInput = ref(null);
const photoPreview = ref(null);
const errors = reactive({});
const showCalendrier = ref(false);
const datePickerRef = ref(null);
const showCountryDropdown = ref(false);
const nationaliteSearch = ref("");
const nationaliteRef = ref(null);
const adresseRef = ref(null);
const adresseSuggestions = ref([]);
const showAdresseDropdown = ref(false);
let adresseTimeout = null;
const erreurGlobale = ref("");
const chargement = ref(false);
const showPassword = ref(false);
const showPasswordConfirm = ref(false);

const form = reactive({
    prenom: "",
    nom: "",
    email: "",
    password: "",
    passwordConfirm: "",
    genre: "",
    dateNaissance: "",
    telephone: "",
    club: "",
    nationalite: "",
    adresse: "",
    numeroRue: "",
    npa: "",
    commune: "",
    tailleTshirt: "L",
    photo: null,
});

/**
 * Formate le numéro de téléphone à mesure que l'utilisateur tape.
 * Supprime tous les caractères non numériques puis insère des espaces
 * selon le gabarit suisse : XXX XXX XX XX.
 * @author Guillermet Jean-Daniel
 * @param {Event} event - L'événement input du champ téléphone
 * @returns {void}
 * @example
 * // Saisie : "0791234567"  →  Résultat affiché : "079 123 45 67"
 */
function formaterTelephone(event) {
    // Supprime tout ce qui n'est pas un chiffre
    let valeur = event.target.value.replace(/\D/g, "");
    if (valeur.length <= 3) {
        // Ex: "079"
        form.telephone = valeur;
    } else if (valeur.length <= 6) {
        // Ex: "079 123"
        form.telephone = valeur.slice(0, 3) + " " + valeur.slice(3);
    } else if (valeur.length <= 8) {
        // Ex: "079 123 45"
        form.telephone =
            valeur.slice(0, 3) +
            " " +
            valeur.slice(3, 6) +
            " " +
            valeur.slice(6);
    } else {
        // Ex: "079 123 45 67" (format complet à 10 chiffres)
        form.telephone =
            valeur.slice(0, 3) +
            " " +
            valeur.slice(3, 6) +
            " " +
            valeur.slice(6, 8) +
            " " +
            valeur.slice(8, 10);
    }
}

/**
 * Compatibilité de test : sélectionne une nationalité et ferme le menu local.
 * Utilisé lors des tests unitaires pour simuler la sélection sans passer
 * par le composant SelectNationalite.
 * @author Guillermet Jean-Daniel
 * @param {string} value - La valeur de nationalité à affecter à form.nationalite
 * @returns {void}
 */
function selectCountry(value) {
    form.nationalite = value;
    nationaliteSearch.value = value;
    showCountryDropdown.value = false;
}

/**
 * Compatibilité de test : ferme le dropdown de nationalité si le clic est hors du composant.
 * Appelée dans les tests pour simuler un clic extérieur.
 * @author Guillermet Jean-Daniel
 * @param {MouseEvent} e - L'événement mousedown capturé
 * @returns {void}
 */
function handleClickOutside(e) {
    if (nationaliteRef.value && !nationaliteRef.value.contains(e.target)) {
        showCountryDropdown.value = false;
    }
}

// Enregistre le listener de clic extérieur pour le dropdown d'adresse au montage
onMounted(() => {
    document.addEventListener("mousedown", handleAdresseClickOutside);
});

// Supprime le listener au démontage pour éviter les fuites mémoire
onBeforeUnmount(() => {
    document.removeEventListener("mousedown", handleAdresseClickOutside);
});

/**
 * Expose les propriétés et méthodes nécessaires aux tests unitaires.
 * Seules les entités listées ici sont accessibles depuis l'extérieur du composant.
 */
defineExpose({
    currentStep,
    errors,
    form,
    photoPreview,
    showCountryDropdown,
    nationaliteSearch,
    nationaliteRef,
    showAdresseDropdown,
    adresseRef,
    datePickerRef,
    fileInput,
    selectCountry,
    handleClickOutside,
    handleAdresseClickOutside,
    formaterTelephone,
    formaterDate,
    dateDepuisCalendrier,
    triggerFileInput,
    validateStep1,
    validateStep2,
    validateStep3,
    nextStep,
    previousStep,
    handlePhotoChange,
    rechercherAdresse,
});

/**
 * Recherche les suggestions d'adresses via l'API geo.admin.ch de la Confédération Suisse.
 * La requête est déclenchée après un délai de 300 ms (debounce) dès que
 * la saisie atteint 3 caractères minimum.
 * @author Guillermet Jean-Daniel
 * @param {string} valeur - La valeur saisie dans le champ adresse
 * @returns {Promise<void>}
 * @see https://api3.geo.admin.ch/services/sdiservices.html#search
 */
async function rechercherAdresse(valeur) {
    // Annule la requête précédente si l'utilisateur continue de taper
    clearTimeout(adresseTimeout);

    // Ne pas lancer de requête pour une saisie trop courte
    if (!valeur || valeur.length < 3) {
        adresseSuggestions.value = [];
        showAdresseDropdown.value = false;
        return;
    }

    // Debounce : attend 300 ms avant d'envoyer la requête
    adresseTimeout = setTimeout(async () => {
        try {
            const response = await fetch(
                `https://api3.geo.admin.ch/rest/services/api/SearchServer?searchText=${encodeURIComponent(valeur)}&type=locations&lang=fr&limit=6&origins=address`,
            );
            const data = await response.json();
            adresseSuggestions.value = data.results || [];
            showAdresseDropdown.value = adresseSuggestions.value.length > 0;
        } catch (e) {
            // En cas d'erreur réseau, on vide silencieusement les suggestions
            adresseSuggestions.value = [];
        }
    }, 300);
}

/**
 * Traite la sélection d'une suggestion et remplit les champs adresse,
 * numéro de rue, NPA et commune à partir de l'objet retourné par l'API.
 *
 * Le label brut de l'API contient du HTML (ex: "Rue ROTHSCHILD 11 <b>1202 Genève</b>").
 * L'algorithme de décomposition fonctionne comme suit :
 *   1. Nettoyage des balises HTML
 *   2. Découpage en tokens séparés par des espaces
 *   3. Détection du NPA (4 chiffres consécutifs)
 *   4. Nom de rue = tokens précédant le premier token numérique
 *   5. Numéro = champ `num` de l'API (plus fiable que le parsing du label)
 *   6. Commune = tokens suivant le NPA
 * @author Guillermet Jean-Daniel
 * @param {{ attrs: { label: string, num?: number } }} suggestion - Objet suggestion de l'API geo.admin.ch
 * @returns {void}
 */
function selectionnerAdresse(suggestion) {
    const attrs = suggestion.attrs;

    // Étape 1 : nettoyage du HTML et découpage en tokens
    const labelPropre = attrs.label.replace(/<[^>]*>/g, "").trim();
    const parties = labelPropre.split(" ");

    // Étape 2 : recherche de l'index du NPA (4 chiffres consécutifs)
    const indexNpa = parties.findIndex((p) => /^\d{4}$/.test(p));

    // Étape 3 : nom de rue = tout ce qui précède le premier token numérique
    const indexPremierChiffre = parties.findIndex((p) => /^\d/.test(p));
    form.adresse =
        indexPremierChiffre > 0
            ? parties.slice(0, indexPremierChiffre).join(" ")
            : parties[0];

    // Étape 4 : numéro de rue issu directement du champ `num` de l'API
    form.numeroRue = attrs.num ? String(attrs.num) : "";

    // Étape 5 : NPA (4 chiffres)
    form.npa = indexNpa >= 0 ? parties[indexNpa] : "";

    // Étape 6 : commune = tokens après le NPA
    form.commune = indexNpa >= 0 ? parties.slice(indexNpa + 1).join(" ") : "";

    // Fermeture du dropdown après sélection
    adresseSuggestions.value = [];
    showAdresseDropdown.value = false;
}

/**
 * Ferme le dropdown des suggestions d'adresses lors d'un clic hors du champ.
 * Attachée à l'événement mousedown du document via onMounted / onBeforeUnmount.
 * @author Guillermet Jean-Daniel
 * @param {MouseEvent} e - L'événement mousedown capturé sur le document
 * @returns {void}
 */
function handleAdresseClickOutside(e) {
    if (adresseRef.value && !adresseRef.value.contains(e.target)) {
        showAdresseDropdown.value = false;
    }
}

/**
 * Formate la date de naissance au format JJ/MM/AAAA à mesure que l'utilisateur tape.
 * Les séparateurs "/" sont insérés automatiquement après le jour (position 2)
 * et après le mois (position 4). Les caractères non numériques sont ignorés.
 * @author Guillermet Jean-Daniel
 * @param {Event} event - L'événement input du champ de date
 * @returns {void}
 * @example
 * // Saisie : "01012000"  →  Résultat affiché : "01/01/2000"
 */
function formaterDate(event) {
    // Supprime tout caractère non numérique
    let valeur = event.target.value.replace(/\D/g, "");
    if (valeur.length >= 3 && valeur.length <= 4) {
        // Insertion du "/" après le jour
        valeur = valeur.slice(0, 2) + "/" + valeur.slice(2);
    } else if (valeur.length >= 5) {
        // Insertion des "/" après le jour et après le mois
        valeur =
            valeur.slice(0, 2) +
            "/" +
            valeur.slice(2, 4) +
            "/" +
            valeur.slice(4, 8);
    }
    form.dateNaissance = valeur;
}

/**
 * Convertit la date sélectionnée via le calendrier natif au format JJ/MM/AAAA
 * et l'affecte à form.dateNaissance.
 * Le calendrier natif retourne une date au format ISO (AAAA-MM-JJ).
 * @author Guillermet Jean-Daniel
 * @param {Event} event - L'événement change de l'input[type=date]
 * @returns {void}
 */
function dateDepuisCalendrier(event) {
    const date = new Date(event.target.value);
    if (!isNaN(date)) {
        const jour = String(date.getDate()).padStart(2, "0");
        const mois = String(date.getMonth() + 1).padStart(2, "0");
        const annee = date.getFullYear();
        form.dateNaissance = `${jour}/${mois}/${annee}`;
    }
    showCalendrier.value = false;
    // Note : showPicker() n'est appelé qu'à l'ouverture du picker, pas à sa fermeture
    datePickerRef.value.showPicker?.();
}

/**
 * Valide tous les champs de l'étape 1 (identifiants, email, mot de passe).
 * Efface les erreurs précédentes avant chaque validation.
 * Règles appliquées :
 *   - Prénom et nom non vides
 *   - Email au format valide (regex RFC-like)
 *   - Mot de passe : ≥ 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial
 *   - Confirmation identique au mot de passe
 * @author Guillermet Jean-Daniel
 * @returns {boolean} true si tous les champs sont valides, false en cas d'erreur
 */
function validateStep1() {
    // Nettoyage des erreurs de l'étape 1
    ["prenom", "nom", "email", "password", "passwordConfirm"].forEach(
        (k) => delete errors[k],
    );
    let valid = true;

    if (!form.prenom.trim()) {
        errors.prenom = "Le prénom est requis.";
        valid = false;
    }

    if (!form.nom.trim()) {
        errors.nom = "Le nom est requis.";
        valid = false;
    }

    // Validation de l'adresse e-mail avec une regex simplifiée
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!form.email.trim()) {
        errors.email = "L'email est requis.";
        valid = false;
    } else if (!emailRegex.test(form.email)) {
        errors.email = "L'email n'est pas valide.";
        valid = false;
    }

    if (!form.password) {
        errors.password = "Le mot de passe est requis.";
        valid = false;
    } else {
        // Vérification des critères de complexité du mot de passe
        const majuscule = /[A-Z]/.test(form.password);
        const minuscule = /[a-z]/.test(form.password);
        const chiffre = /[0-9]/.test(form.password);
        const special = /[^A-Za-z0-9]/.test(form.password);
        const longueur = form.password.length >= 8;

        if (!longueur || !majuscule || !minuscule || !chiffre || !special) {
            errors.password =
                "Le mot de passe doit contenir minimum 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.";
            valid = false;
        }
    }

    if (!form.passwordConfirm) {
        errors.passwordConfirm = "Veuillez répéter le mot de passe.";
        valid = false;
    } else if (form.password !== form.passwordConfirm) {
        errors.passwordConfirm = "Les mots de passe ne correspondent pas.";
        valid = false;
    }

    return valid;
}

/**
 * Valide tous les champs de l'étape 2 (genre, date de naissance, téléphone, nationalité).
 * Efface les erreurs précédentes avant chaque validation.
 * Règles appliquées :
 *   - Genre sélectionné (non vide)
 *   - Date de naissance non vide et correspondant au format JJ/MM/AAAA
 *   - Téléphone non vide
 *   - Nationalité sélectionnée (non vide)
 * @author Guillermet Jean-Daniel
 * @returns {boolean} true si tous les champs sont valides, false en cas d'erreur
 */
function validateStep2() {
    // Nettoyage des erreurs de l'étape 2
    ["genre", "dateNaissance", "telephone", "nationalite"].forEach(
        (k) => delete errors[k],
    );
    let valid = true;

    if (!form.genre) {
        errors.genre = "Veuillez sélectionner un genre.";
        valid = false;
    }

    // Validation du format de date JJ/MM/AAAA
    const dateRegex = /^(0[1-9]|[12]\d|3[01])\/(0[1-9]|1[0-2])\/\d{4}$/;
    if (!form.dateNaissance.trim()) {
        errors.dateNaissance = "La date de naissance est requise.";
        valid = false;
    } else if (!dateRegex.test(form.dateNaissance)) {
        errors.dateNaissance = "Format attendu : JJ/MM/AAAA.";
        valid = false;
    }

    if (!form.telephone.trim()) {
        errors.telephone = "Le numéro de téléphone est requis.";
        valid = false;
    }

    if (!form.nationalite) {
        errors.nationalite = "Veuillez sélectionner une nationalité.";
        valid = false;
    }

    return valid;
}

/**
 * Valide tous les champs de l'étape 3 (adresse, numéro de rue, NPA, commune).
 * Efface les erreurs précédentes avant chaque validation.
 * Règles appliquées :
 *   - Adresse, numéro, NPA et commune non vides
 * @author Guillermet Jean-Daniel
 * @returns {boolean} true si tous les champs sont valides, false en cas d'erreur
 */
function validateStep3() {
    // Nettoyage des erreurs de l'étape 3
    ["adresse", "npa", "commune"].forEach((k) => delete errors[k]);
    delete errors.numeroRue;
    let valid = true;

    if (!form.adresse.trim()) {
        errors.adresse = "L'adresse est requise.";
        valid = false;
    }

    if (!form.numeroRue.trim()) {
        errors.numeroRue = "Le numéro est requis.";
        valid = false;
    }

    if (!form.npa.trim()) {
        errors.npa = "Le NPA est requis.";
        valid = false;
    }

    if (!form.commune.trim()) {
        errors.commune = "La commune est requise.";
        valid = false;
    }

    return valid;
}

/**
 * Valide l'étape courante et avance à l'étape suivante si la validation réussit.
 * Utilise un dictionnaire de fonctions pour associer chaque étape à son validateur.
 * @author Guillermet Jean-Daniel
 * @returns {void}
 */
function nextStep() {
    // Association étape → fonction de validation
    const validators = { 1: validateStep1, 2: validateStep2 };
    if (validators[currentStep.value]()) currentStep.value++;
}

/**
 * Recule à l'étape précédente ou redirige vers /login si l'utilisateur est déjà à l'étape 1.
 * @author Guillermet Jean-Daniel
 * @returns {void}
 */
function previousStep() {
    if (currentStep.value > 1) currentStep.value--;
    else router.push("/login");
}

/**
 * Transmet programmatiquement le clic vers l'input file caché pour ouvrir
 * la boîte de dialogue de sélection de fichier.
 * @author Guillermet Jean-Daniel
 * @returns {void}
 */
function triggerFileInput() {
    fileInput.value?.click();
}

/**
 * Traite le changement de fichier lors de la sélection d'une photo de profil.
 * Lit le fichier sélectionné via FileReader et génère une URL base64
 * pour l'aperçu immédiat dans le template.
 * @author Guillermet Jean-Daniel
 * @param {Event} event - L'événement change de l'input[type=file]
 * @returns {void}
 */
function handlePhotoChange(event) {
    const file = event.target.files[0];
    if (file) {
        form.photo = file;
        const reader = new FileReader();
        // Affecte l'URL base64 à photoPreview une fois la lecture terminée
        reader.onload = (e) => {
            photoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

/**
 * Valide l'étape 3 puis soumet le formulaire d'inscription au backend via authStore.register().
 *
 * Construction du payload :
 *   - La date est convertie de JJ/MM/AAAA vers AAAA-MM-JJ (format ISO attendu par l'API)
 *   - L'adresse est concaténée avec le numéro de rue
 *   - Le pays est renseigné avec la nationalité (simplification métier)
 *
 * Gestion des erreurs backend :
 *   - Les erreurs de validation (e.response.data.errors) sont mappées
 *     vers les champs correspondants du formulaire via fieldMap
 *   - L'utilisateur est automatiquement repositionné sur l'étape contenant le champ en erreur
 *   - Si aucune erreur ne correspond à un champ connu, un message global est affiché
 *   - Les erreurs réseau ou serveur génériques sont capturées et affichées globalement
 *
 * @author Guillermet Jean-Daniel
 * @returns {Promise<void>}
 */
async function handleRegister() {
    if (!validateStep3()) return;

    erreurGlobale.value = "";
    chargement.value = true;

    try {
        // Construction du payload au format attendu par l'API backend
        const payload = {
            email: form.email,
            password: form.password,
            password_confirmation: form.passwordConfirm,
            nom: form.nom,
            prenom: form.prenom,
            // Conversion JJ/MM/AAAA → AAAA-MM-JJ
            date_naissance: form.dateNaissance.split("/").reverse().join("-"),
            telephone: form.telephone,
            nationalite: form.nationalite,
            adresse: `${form.adresse} ${form.numeroRue}`.trim(),
            code_postal: form.npa,
            ville: form.commune,
            pays: form.nationalite,
            taille_tshirt: form.tailleTshirt,
            sexe: form.genre,
            equipe_nom: form.club,
        };

        await authStore.register(payload);
        router.push("/login");
    } catch (e) {
        if (e.response?.data?.errors) {
            const apiErrors = e.response.data.errors;

            // Effacement des erreurs affichées précédemment
            Object.keys(errors).forEach((k) => delete errors[k]);

            /**
             * Table de correspondance entre les noms de champs du backend
             * et les clés du formulaire frontend.
             * @type {Record<string, string>}
             */
            const fieldMap = {
                email: "email",
                password: "password",
                password_confirmation: "passwordConfirm",
                nom: "nom",
                prenom: "prenom",
                date_naissance: "dateNaissance",
                telephone: "telephone",
                nationalite: "nationalite",
                adresse: "adresse",
                code_postal: "npa",
                ville: "commune",
                sexe: "genre",
                taille_tshirt: "tailleTshirt",
            };

            // Affectation des messages d'erreur sur les champs correspondants
            Object.entries(apiErrors).forEach(([apiField, messages]) => {
                const frontField = fieldMap[apiField];
                if (
                    frontField &&
                    Array.isArray(messages) &&
                    messages.length > 0
                ) {
                    errors[frontField] = messages[0];
                }
            });

            // Repositionnement automatique sur l'étape concernée par l'erreur
            const step1Fields = [
                "prenom",
                "nom",
                "email",
                "password",
                "passwordConfirm",
            ];
            const step2Fields = [
                "genre",
                "dateNaissance",
                "telephone",
                "nationalite",
            ];

            if (step1Fields.some((f) => errors[f])) {
                currentStep.value = 1;
            } else if (step2Fields.some((f) => errors[f])) {
                currentStep.value = 2;
            } else {
                currentStep.value = 3;
            }

            // Fallback : message global si aucune erreur n'a pu être mappée sur un champ UI
            if (Object.keys(errors).length === 0) {
                const premierMessage = Object.values(apiErrors)?.[0]?.[0];
                erreurGlobale.value =
                    premierMessage || "Certaines informations sont invalides.";
            }
        } else if (e.response?.data?.message) {
            // Message d'erreur générique retourné par le backend
            erreurGlobale.value = e.response.data.message;
        } else {
            // Erreur réseau ou serveur non structurée
            erreurGlobale.value =
                "Une erreur est survenue, veuillez réessayer.";
        }
    } finally {
        // Toujours désactiver l'état de chargement, succès ou échec
        chargement.value = false;
    }
}
</script>

<style scoped>
/* Image de fond de l'écran d'authentification */
.auth-bg {
    background-image: url("/images/login-bg.png");
    background-size: cover;
    background-position: center;
}
</style>
