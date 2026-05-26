<template>
    <div class="w-full max-w-5xl mx-auto">
        <!-- Titre -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-primary mb-2">
                Modifier mon profil
            </h1>
            <div class="h-1 w-16 bg-accent rounded"></div>
        </div>

        <!-- Section Photo et actions profil -->
        <div class="bg-white rounded-2xl shadow-lg mb-8 overflow-hidden">
            <div class="p-8">
                <label class="block text-2xl font-semibold text-primary mb-4"
                    >Photo<span class="text-accent">*</span></label
                >

                <button
                    type="button"
                    @click="triggerProfileImageInput"
                    @dragover.prevent="isDropzoneActive = true"
                    @dragleave.prevent="isDropzoneActive = false"
                    @drop.prevent="handleDropProfileImage"
                    class="group relative h-64 w-full overflow-hidden text-left border-2 transition-colors"
                    :class="
                        isDropzoneActive ? 'border-tertiary' : 'border-gray-300'
                    "
                >
                    <div v-if="userProfilePicture" class="absolute inset-0">
                        <img
                            :src="userProfilePicture"
                            alt="Photo de profil"
                            class="h-full w-full object-cover"
                        />
                    </div>

                    <div v-else class="absolute inset-0 bg-gray-100"></div>

                    <div
                        class="absolute inset-0 bg-black/55 transition-opacity duration-200"
                        :class="
                            isDropzoneActive
                                ? 'opacity-100'
                                : 'opacity-0 group-hover:opacity-100'
                        "
                    ></div>

                    <div
                        class="absolute inset-0 flex items-center justify-center transition-opacity duration-200"
                        :class="
                            isDropzoneActive
                                ? 'opacity-0'
                                : 'opacity-100 group-hover:opacity-0'
                        "
                    >
                        <svg
                            class="w-28 h-28 text-black"
                            fill="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5z"
                            ></path>
                            <path
                                d="M12 14c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5z"
                            ></path>
                        </svg>
                    </div>

                    <div
                        class="absolute inset-0 flex flex-col items-center justify-center px-6 text-center text-white transition-opacity duration-200"
                        :class="
                            isDropzoneActive
                                ? 'opacity-100'
                                : 'opacity-0 group-hover:opacity-100'
                        "
                    >
                        <svg
                            class="w-24 h-24 mb-4"
                            fill="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5z"
                            ></path>
                            <path
                                d="M12 14c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5z"
                            ></path>
                        </svg>
                        <p
                            v-if="profileImageName"
                            class="text-2xl font-semibold mb-2 break-all"
                        >
                            {{ profileImageName }}
                        </p>
                        <p class="text-lg font-medium text-white/85">
                            Glisser déposer un fichier ici ou cliquer pour
                            remplacer
                        </p>
                    </div>

                    <input
                        ref="profileImageInput"
                        type="file"
                        accept="image/*"
                        class="hidden"
                        @change="handleProfileImageChange"
                    />
                </button>

                <p
                    class="mt-2 text-sm font-medium text-accent/85 tracking-wide"
                >
                    Taille recommandée: 225 x 225 px, 1 Mo maximum.
                </p>
            </div>

            <!-- Section Profil -->
            <div class="px-8 pb-8 pt-6">
                <div class="flex justify-end">
                    <div class="flex flex-col items-end gap-2">
                        <button
                            type="button"
                            @click="goToMesInscriptions"
                            class="bg-tertiary text-primary px-6 py-2 rounded-2xl font-medium hover:bg-secondary-600 transition-colors"
                        >
                            Voir mes inscriptions
                        </button>
                        <button
                            type="button"
                            @click="openPasswordModal"
                            class="bg-white text-primary border border-primary-300 px-5 py-1.5 rounded-xl font-medium hover:bg-secondary-600 transition-colors"
                        >
                            Modifier mon mot de passe
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulaire -->
        <form
            @submit.prevent="submitForm"
            class="bg-white rounded-2xl shadow-lg p-8 space-y-6"
        >
            <p
                v-if="formFeedback.message && formFeedback.isError"
                class="text-accent text-sm"
            >
                {{ formFeedback.message }}
            </p>

            <!-- Ligne 1: Nom et Prénom -->
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label
                        class="block text-label font-medium text-primary mb-2"
                    >
                        Nom <span class="text-accent">*</span>
                    </label>
                    <input
                        v-model="form.nom"
                        type="text"
                        class="input-field w-full"
                        :class="{ 'border-accent': errors.nom }"
                        placeholder="Benoit"
                    />
                    <p v-if="errors.nom" class="text-accent text-label mt-1">
                        {{ errors.nom }}
                    </p>
                </div>
                <div>
                    <label
                        class="block text-label font-medium text-primary mb-2"
                    >
                        Prénom <span class="text-accent">*</span>
                    </label>
                    <input
                        v-model="form.prenom"
                        type="text"
                        class="input-field w-full"
                        :class="{ 'border-accent': errors.prenom }"
                        placeholder="Patrice"
                    />
                    <p v-if="errors.prenom" class="text-accent text-label mt-1">
                        {{ errors.prenom }}
                    </p>
                </div>
            </div>

            <!-- Ligne 2: Email et Date de naissance -->
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label
                        class="block text-label font-medium text-primary mb-2"
                    >
                        Email <span class="text-accent">*</span>
                    </label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="input-field w-full"
                        :class="{ 'border-accent': errors.email }"
                        placeholder="name@company.com"
                    />
                    <p v-if="errors.email" class="text-accent text-label mt-1">
                        {{ errors.email }}
                    </p>
                </div>
                <div>
                    <label
                        class="block text-label font-medium text-primary mb-2"
                    >
                        Date de naissance <span class="text-accent">*</span>
                    </label>
                    <input
                        v-model="form.dateNaissance"
                        type="text"
                        placeholder="JJ/MM/AAAA"
                        maxlength="10"
                        class="input-field w-full"
                        :class="{ 'border-accent': errors.dateNaissance }"
                        @input="formaterDate"
                    />
                    <p
                        v-if="errors.dateNaissance"
                        class="text-accent text-label mt-1"
                    >
                        {{ errors.dateNaissance }}
                    </p>
                </div>
            </div>

            <!-- Ligne 3: Adresse et N° -->
            <div class="grid grid-cols-3 gap-6">
                <div class="col-span-2">
                    <label
                        class="block text-label font-medium text-primary mb-2"
                    >
                        Adresse <span class="text-accent">*</span>
                    </label>
                    <div class="relative" ref="adresseRef">
                        <input
                            v-model="form.adresse"
                            type="text"
                            class="input-field w-full"
                            :class="{ 'border-accent': errors.adresse }"
                            @input="rechercherAdresse(form.adresse)"
                            placeholder="Ex: Rue du Rhône"
                        />

                        <div
                            v-if="
                                showAdresseDropdown &&
                                adresseSuggestions.length > 0
                            "
                            class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto"
                        >
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
                <div>
                    <label
                        class="block text-label font-medium text-primary mb-2"
                    >
                        N° <span class="text-accent">*</span>
                    </label>
                    <input
                        v-model="form.numero"
                        type="text"
                        class="input-field w-full"
                        :class="{ 'border-accent': errors.numero }"
                        placeholder="14"
                    />
                    <p v-if="errors.numero" class="text-accent text-label mt-1">
                        {{ errors.numero }}
                    </p>
                </div>
            </div>

            <!-- Ligne 4: Équipe/Club/Entreprise -->
            <div>
                <label class="block text-label font-medium text-primary mb-2">
                    Équipe, Club, Entreprise
                </label>
                <input
                    v-model="form.club"
                    type="text"
                    class="input-field w-full"
                    placeholder="Équipe, Club, Entreprise"
                />
            </div>

            <!-- Ligne 5: NPA, Commune, Nationalité -->
            <div class="grid grid-cols-3 gap-6">
                <div>
                    <label
                        class="block text-label font-medium text-primary mb-2"
                    >
                        NPA <span class="text-accent">*</span>
                    </label>
                    <input
                        v-model="form.npa"
                        type="text"
                        class="input-field w-full"
                        :class="{ 'border-accent': errors.npa }"
                        placeholder="1227"
                    />
                    <p v-if="errors.npa" class="text-accent text-label mt-1">
                        {{ errors.npa }}
                    </p>
                </div>
                <div>
                    <label
                        class="block text-label font-medium text-primary mb-2"
                    >
                        Commune <span class="text-accent">*</span>
                    </label>
                    <input
                        v-model="form.commune"
                        type="text"
                        class="input-field w-full"
                        :class="{ 'border-accent': errors.commune }"
                        placeholder="Carouge"
                    />
                    <p
                        v-if="errors.commune"
                        class="text-accent text-label mt-1"
                    >
                        {{ errors.commune }}
                    </p>
                </div>
                <div>
                    <label
                        class="block text-label font-medium text-primary mb-2"
                    >
                        Nationalité <span class="text-accent">*</span>
                    </label>
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

            <!-- Ligne 6: N° téléphone et Taille T-Shirt -->
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label
                        class="block text-label font-medium text-primary mb-2"
                    >
                        N° téléphone <span class="text-accent">*</span>
                    </label>
                    <input
                        v-model="form.telephone"
                        type="tel"
                        class="input-field w-full"
                        :class="{ 'border-accent': errors.telephone }"
                        placeholder="076 766 76 76"
                        @input="formaterTelephone"
                    />
                    <p
                        v-if="errors.telephone"
                        class="text-accent text-label mt-1"
                    >
                        {{ errors.telephone }}
                    </p>
                </div>
                <div>
                    <label
                        class="block text-label font-medium text-primary mb-2"
                    >
                        Taille T-Shirt <span class="text-accent">*</span>
                    </label>
                    <select
                        v-model="form.tailleTshirt"
                        class="input-field w-full pr-8 appearance-none cursor-pointer"
                        :class="{ 'border-accent': errors.tailleTshirt }"
                    >
                        <option value="">Selectionner une taille</option>
                        <option value="XS">XS</option>
                        <option value="S">S</option>
                        <option value="M">M</option>
                        <option value="L">L</option>
                        <option value="XL">XL</option>
                        <option value="XXL">XXL</option>
                    </select>
                    <p
                        v-if="errors.tailleTshirt"
                        class="text-accent text-label mt-1"
                    >
                        {{ errors.tailleTshirt }}
                    </p>
                </div>
            </div>

            <!-- Boutons -->
            <div
                class="flex justify-between gap-4 mt-8 pt-6 border-t border-gray-200"
            >
                <button
                    type="button"
                    @click="handleRetour"
                    class="btn-accent-300 px-8 py-3 rounded-xl font-medium transition-all hover:opacity-80"
                >
                    Retour
                </button>
                <button
                    type="submit"
                    :disabled="isSavingProfile"
                    class="btn-tertiary px-8 py-3 rounded-xl font-medium transition-all hover:opacity-80"
                >
                    {{ isSavingProfile ? "Enregistrement..." : "Enregistrer" }}
                </button>
            </div>
        </form>

        <div
            v-if="showSuccessModal"
            class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 px-4"
        >
            <div
                class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl border border-gray-100"
            >
                <h3 class="text-xl font-bold text-primary mb-2">
                    {{ successModalTitle }}
                </h3>
                <p class="text-sm text-gray-600 mb-5">
                    {{ successModalMessage }}
                </p>
                <div class="flex justify-end">
                    <button
                        type="button"
                        @click="showSuccessModal = false"
                        class="btn-tertiary px-5 py-2 rounded-xl font-medium"
                    >
                        OK
                    </button>
                </div>
            </div>
        </div>

        <div
            v-if="showPasswordForm"
            @click.self="closePasswordModal"
            class="fixed inset-0 z-[70] flex items-center justify-center bg-black/45 px-4"
        >
            <div
                class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl border border-gray-100"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-primary">
                        Modification du mot de passe
                    </h3>
                    <button
                        type="button"
                        @click="closePasswordModal"
                        class="text-gray-500 hover:text-gray-800 text-2xl leading-none"
                    >
                        &times;
                    </button>
                </div>

                <div class="space-y-3">
                    <div>
                        <label
                            class="block text-label font-medium text-primary mb-1"
                            >Mot de passe actuel</label
                        >
                        <input
                            v-model="passwordForm.currentPassword"
                            type="password"
                            class="input-field w-full"
                            :class="{
                                'border-accent': passwordErrors.currentPassword,
                            }"
                        />
                        <p
                            v-if="passwordErrors.currentPassword"
                            class="text-accent text-label mt-1"
                        >
                            {{ passwordErrors.currentPassword }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-label font-medium text-primary mb-1"
                            >Nouveau mot de passe</label
                        >
                        <input
                            v-model="passwordForm.newPassword"
                            type="password"
                            class="input-field w-full"
                            :class="{
                                'border-accent': passwordErrors.newPassword,
                            }"
                        />
                        <p
                            v-if="passwordErrors.newPassword"
                            class="text-accent text-label mt-1"
                        >
                            {{ passwordErrors.newPassword }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-label font-medium text-primary mb-1"
                            >Confirmer le nouveau mot de passe</label
                        >
                        <input
                            v-model="passwordForm.newPasswordConfirmation"
                            type="password"
                            class="input-field w-full"
                            :class="{
                                'border-accent':
                                    passwordErrors.newPasswordConfirmation,
                            }"
                        />
                        <p
                            v-if="passwordErrors.newPasswordConfirmation"
                            class="text-accent text-label mt-1"
                        >
                            {{ passwordErrors.newPasswordConfirmation }}
                        </p>
                    </div>

                    <p
                        v-if="passwordFeedback.message"
                        :class="
                            passwordFeedback.isError
                                ? 'text-accent text-sm'
                                : 'text-green-600 text-sm'
                        "
                    >
                        {{ passwordFeedback.message }}
                    </p>
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <button
                        type="button"
                        @click="closePasswordModal"
                        class="bg-white text-primary border border-primary-300 px-4 py-2 rounded-xl font-medium hover:bg-secondary-600 transition-colors"
                    >
                        Annuler
                    </button>
                    <button
                        type="button"
                        @click="submitPasswordForm"
                        :disabled="isSavingPassword"
                        class="btn-tertiary px-4 py-2 rounded-xl font-medium disabled:opacity-60"
                    >
                        {{
                            isSavingPassword
                                ? "Enregistrement..."
                                : "Enregistrer le mot de passe"
                        }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Section Mes participants (sous-profils mineurs) -->
        <div
            id="mes-participants"
            class="bg-white rounded-2xl shadow-lg mb-8 overflow-hidden mt-10"
        >
            <div
                class="h-1 w-full bg-gradient-to-r from-transparent via-gray-200 to-transparent mb-0"
            ></div>
            <div class="p-8">
                <label class="block text-2xl font-semibold text-primary mb-1"
                    >Mes participants</label
                >
                <p class="text-sm text-gray-500 mb-6">
                    Les profils mineurs rattachés à votre compte.
                </p>

                <div
                    v-if="chargementParticipants"
                    class="text-sm text-gray-400"
                >
                    Chargement...
                </div>

                <div v-else class="flex flex-col gap-3">
                    <!-- Sous-profils -->
                    <div
                        v-for="p in sousProfilsParticipants"
                        :key="p.id"
                        class="flex items-center justify-between px-4 py-3 border border-gray-200 rounded-xl bg-gray-50"
                    >
                        <div class="flex flex-col">
                            <span class="font-semibold text-primary text-sm"
                                >{{ p.prenom }} {{ p.nom }}</span
                            >
                            <span class="text-xs text-gray-400">{{
                                p.date_naissance
                                    ? new Date(
                                          p.date_naissance,
                                      ).toLocaleDateString("fr-CH")
                                    : "—"
                            }}</span>
                        </div>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                @click="ouvrirEditionParticipant(p)"
                                class="text-xs px-3 py-1.5 rounded-lg border border-gray-300 text-gray-600 hover:border-primary hover:text-primary transition-colors"
                            >
                                Modifier
                            </button>
                            <button
                                type="button"
                                @click="confirmerSuppressionParticipant(p)"
                                class="text-xs px-3 py-1.5 rounded-lg border border-red-200 text-red-400 hover:border-red-400 hover:text-red-600 transition-colors"
                            >
                                Supprimer
                            </button>
                        </div>
                    </div>

                    <p
                        v-if="sousProfilsParticipants.length === 0"
                        class="text-sm text-gray-400 italic"
                    >
                        Aucun sous-profil rattaché à votre compte.
                    </p>
                </div>
            </div>
        </div>

        <!-- Popup édition participant -->
        <div
            v-if="participantEdite"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/30"
            @click.self="participantEdite = null"
        >
            <div
                class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6 flex flex-col gap-4"
            >
                <h3 class="text-lg font-semibold text-primary">
                    Modifier le participant
                </h3>

                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-600"
                            >Nom <span class="text-red-400">*</span></label
                        >
                        <input
                            v-model="formEdition.nom"
                            type="text"
                            class="input-field w-full"
                        />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-600"
                            >Prénom <span class="text-red-400">*</span></label
                        >
                        <input
                            v-model="formEdition.prenom"
                            type="text"
                            class="input-field w-full"
                        />
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-600"
                        >Date de naissance</label
                    >
                    <input
                        v-model="formEdition.date_naissance"
                        type="date"
                        :max="new Date().toISOString().split('T')[0]"
                        class="input-field w-full"
                    />
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-600"
                        >Téléphone</label
                    >
                    <input
                        v-model="formEdition.telephone"
                        type="text"
                        placeholder="+41 79 000 00 00"
                        class="input-field w-full"
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-600"
                            >Taille t-shirt</label
                        >
                        <select
                            v-model="formEdition.taille_tshirt"
                            class="input-field w-full"
                        >
                            <option value="XS">XS</option>
                            <option value="S">S</option>
                            <option value="M">M</option>
                            <option value="L">L</option>
                            <option value="XL">XL</option>
                            <option value="XXL">XXL</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-gray-600"
                            >Genre</label
                        >
                        <select
                            v-model="formEdition.sexe"
                            class="input-field w-full"
                        >
                            <option value="M">Homme</option>
                            <option value="F">Femme</option>
                            <option value="A">Autre</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-600"
                        >Nationalité</label
                    >
                    <SelectNationalite
                        v-model="formEdition.nationalite"
                        inputClass="input-field w-full pr-8"
                    />
                </div>

                <p
                    v-if="erreurEdition"
                    class="text-xs text-red-500 bg-red-50 border border-red-100 rounded-lg px-3 py-2"
                >
                    {{ erreurEdition }}
                </p>

                <div class="flex justify-end gap-2 pt-2">
                    <button
                        type="button"
                        @click="participantEdite = null"
                        class="px-4 py-2 text-sm rounded-xl border border-gray-300 text-gray-600 hover:bg-gray-50"
                    >
                        Annuler
                    </button>
                    <button
                        type="button"
                        @click="sauvegarderEditionParticipant"
                        :disabled="isSavingEdition"
                        class="btn-tertiary px-4 py-2 rounded-xl text-sm disabled:opacity-60"
                    >
                        {{
                            isSavingEdition
                                ? "Enregistrement..."
                                : "Enregistrer"
                        }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Popup confirmation suppression -->
        <div
            v-if="participantASupprimer"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/30"
            @click.self="participantASupprimer = null"
        >
            <div
                class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6 flex flex-col gap-4"
            >
                <h3 class="text-lg font-semibold text-primary">
                    Supprimer ce participant ?
                </h3>
                <p class="text-sm text-gray-600">
                    Vous êtes sur le point de supprimer
                    <strong
                        >{{ participantASupprimer.prenom }}
                        {{ participantASupprimer.nom }}</strong
                    >. Cette action est irréversible.
                </p>
                <p
                    v-if="erreurSuppression"
                    class="text-xs text-red-500 bg-red-50 border border-red-100 rounded-lg px-3 py-2"
                >
                    {{ erreurSuppression }}
                </p>
                <div class="flex justify-end gap-2">
                    <button
                        type="button"
                        @click="participantASupprimer = null"
                        class="px-4 py-2 text-sm rounded-xl border border-gray-300 text-gray-600 hover:bg-gray-50"
                    >
                        Annuler
                    </button>
                    <button
                        type="button"
                        @click="supprimerParticipantConfirme"
                        :disabled="isSuppression"
                        class="px-4 py-2 text-sm rounded-xl bg-red-500 text-white hover:bg-red-600 disabled:opacity-60"
                    >
                        {{ isSuppression ? "Suppression..." : "Supprimer" }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, reactive, ref, computed } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import profilService from "../services/profilService";
import SelectNationalite from "../components/SelectNationalite.vue";

const router = useRouter();
const authStore = useAuthStore();

// État du formulaire
const form = reactive({
    nom: "",
    prenom: "",
    email: "",
    dateNaissance: "",
    adresse: "",
    numero: "",
    club: "",
    npa: "",
    commune: "",
    nationalite: "Suisse",
    telephone: "",
    tailleTshirt: "",
});

// Messages d'erreur
const errors = reactive({
    nom: "",
    prenom: "",
    email: "",
    dateNaissance: "",
    adresse: "",
    numero: "",
    npa: "",
    commune: "",
    nationalite: "",
    telephone: "",
    tailleTshirt: "",
});

// Références pour les inputs de fichiers
const profileImageInput = ref(null);
const userProfilePicture = ref(null);
const profileImageName = ref("");
const profileImageFile = ref(null);
const isDropzoneActive = ref(false);
const isSavingProfile = ref(false);
const isSavingPassword = ref(false);
const showPasswordForm = ref(false);
const showSuccessModal = ref(false);
const successModalTitle = ref("");
const successModalMessage = ref("");
const adresseRef = ref(null);
const adresseSuggestions = ref([]);
const showAdresseDropdown = ref(false);
let adresseTimeout = null;

const formFeedback = reactive({
    message: "",
    isError: false,
});

const passwordFeedback = reactive({
    message: "",
    isError: false,
});

const passwordForm = reactive({
    currentPassword: "",
    newPassword: "",
    newPasswordConfirmation: "",
});

const passwordErrors = reactive({
    currentPassword: "",
    newPassword: "",
    newPasswordConfirmation: "",
});

/**
 * Réinitialise tous les messages d'erreur du formulaire de mot de passe.
 * @author Ngoie Steven
 * @returns {void}
 */
const resetPasswordErrors = () => {
    passwordErrors.currentPassword = "";
    passwordErrors.newPassword = "";
    passwordErrors.newPasswordConfirmation = "";
};

/**
 * Réinitialise l'état complet du formulaire et des erreurs de mot de passe.
 * @author Ngoie Steven
 * @returns {void}
 */
const resetPasswordFormState = () => {
    resetPasswordErrors();
    passwordFeedback.message = "";
    passwordFeedback.isError = false;
    passwordForm.currentPassword = "";
    passwordForm.newPassword = "";
    passwordForm.newPasswordConfirmation = "";
};

/**
 * Ouvre le modal de modification du mot de passe.
 * @author Ngoie Steven
 * @returns {void}
 */
const openPasswordModal = () => {
    resetPasswordFormState();
    showPasswordForm.value = true;
};

/**
 * Ferme le modal de modification du mot de passe et réinitialise le formulaire.
 * @author Ngoie Steven
 * @returns {void}
 */
const closePasswordModal = () => {
    showPasswordForm.value = false;
    resetPasswordFormState();
};

/**
 * Applique les erreurs retournées par l'API aux champs du formulaire.
 * @author Ngoie Steven
 * @param {Object} apiErrors - Les erreurs retournées par l'API
 * @returns {void}
 */
const applyApiErrors = (apiErrors) => {
    const fieldMap = {
        dateNaissance: "dateNaissance",
        tailleTshirt: "tailleTshirt",
        nom: "nom",
        prenom: "prenom",
        email: "email",
        adresse: "adresse",
        numero: "numero",
        club: "club",
        npa: "npa",
        commune: "commune",
        nationalite: "nationalite",
        telephone: "telephone",
    };

    Object.keys(apiErrors || {}).forEach((apiField) => {
        const localField = fieldMap[apiField];
        if (localField && errors[localField] !== undefined) {
            errors[localField] = apiErrors[apiField][0];
        }
    });
};

/**
 * Applique les données du profil au formulaire.
 * @author Ngoie Steven
 * @param {Object} data - Les données du profil
 * @returns {void}
 */
const applyProfileData = (data) => {
    form.nom = data.nom || "";
    form.prenom = data.prenom || "";
    form.email = data.email || "";
    form.dateNaissance = data.dateNaissance || "";
    form.adresse = data.adresse || "";
    form.numero = data.numero || "";
    form.club = data.club || "";
    form.npa = data.npa || "";
    form.commune = data.commune || "";
    form.nationalite = data.nationalite || "";
    form.telephone = formaterTelephoneValeur(data.telephone || "");
    form.tailleTshirt = data.tailleTshirt || "";

    userProfilePicture.value = data.photo || null;
    if (!data.photo) {
        profileImageName.value = "";
    }
};

/**
 * Charge les données du profil utilisateur depuis l'API.
 * @author Ngoie Steven
 * @async
 * @returns {Promise<void>}
 */
const loadProfile = async () => {
    try {
        const response = await profilService.getProfil();
        applyProfileData(response.data);
    } catch (error) {
        formFeedback.message = "Impossible de charger le profil.";
        formFeedback.isError = true;
        console.error("Erreur chargement profil:", error);
    }
};

/**
 * Formate la date au format JJ/MM/AAAA au fur et à mesure de la saisie.
 * @author Ngoie Steven
 * @param {Event} event - L'événement d'input
 * @returns {void}
 */
const formaterDate = (event) => {
    let value = event.target.value.replace(/\D/g, "");

    if (value.length >= 2) {
        value = value.slice(0, 2) + "/" + value.slice(2);
    }
    if (value.length >= 5) {
        value = value.slice(0, 5) + "/" + value.slice(5, 9);
    }

    form.dateNaissance = value;
};

/**
 * Formate le numéro de téléphone au fur et à mesure de la saisie.
 * @author Ngoie Steven
 * @param {Event} event - L'événement d'input
 * @returns {void}
 */
const formaterTelephone = (event) => {
    form.telephone = formaterTelephoneValeur(event.target.value);
};

/**
 * Formate une valeur de téléphone au format XX XXX XX XX.
 * @author Ngoie Steven
 * @param {string} valeur - La valeur du téléphone à formater
 * @returns {string} Le téléphone formaté
 */
const formaterTelephoneValeur = (valeur) => {
    const chiffres = valeur.replace(/\D/g, "");

    if (chiffres.length <= 3) {
        return chiffres;
    }
    if (chiffres.length <= 6) {
        return chiffres.slice(0, 3) + " " + chiffres.slice(3);
    }
    if (chiffres.length <= 8) {
        return (
            chiffres.slice(0, 3) +
            " " +
            chiffres.slice(3, 6) +
            " " +
            chiffres.slice(6)
        );
    }

    return (
        chiffres.slice(0, 3) +
        " " +
        chiffres.slice(3, 6) +
        " " +
        chiffres.slice(6, 8) +
        " " +
        chiffres.slice(8, 10)
    );
};

/**
 * Recherche des adresses via l'API geo.admin.ch avec débounce.
 * @author Ngoie Steven
 * @async
 * @param {string} valeur - Le texte d'adresse à rechercher
 * @returns {Promise<void>}
 */
const rechercherAdresse = async (valeur) => {
    clearTimeout(adresseTimeout);

    if (!valeur || valeur.length < 3) {
        adresseSuggestions.value = [];
        showAdresseDropdown.value = false;
        return;
    }

    adresseTimeout = setTimeout(async () => {
        try {
            const response = await fetch(
                `https://api3.geo.admin.ch/rest/services/api/SearchServer?searchText=${encodeURIComponent(valeur)}&type=locations&lang=fr&limit=6&origins=address`,
            );

            const data = await response.json();
            adresseSuggestions.value = data.results || [];
            showAdresseDropdown.value = adresseSuggestions.value.length > 0;
        } catch (_error) {
            adresseSuggestions.value = [];
            showAdresseDropdown.value = false;
        }
    }, 300);
};

/**
 * Sélectionne une adresse des suggestions et remplissage automatique des champs adresse, numéro, NPA, commune.
 * @author Ngoie Steven
 * @param {Object} suggestion - La suggestion d'adresse sélectionnée
 * @returns {void}
 */
const selectionnerAdresse = (suggestion) => {
    const attrs = suggestion.attrs;
    const labelPropre = attrs.label.replace(/<[^>]*>/g, "").trim();
    const parties = labelPropre.split(" ");
    const indexNpa = parties.findIndex((p) => /^\d{4}$/.test(p));
    const indexPremierChiffre = parties.findIndex((p) => /^\d/.test(p));

    form.adresse =
        indexPremierChiffre > 0
            ? parties.slice(0, indexPremierChiffre).join(" ")
            : parties[0];

    form.numero = attrs.num ? String(attrs.num) : "";
    form.npa = indexNpa >= 0 ? parties[indexNpa] : "";
    form.commune = indexNpa >= 0 ? parties.slice(indexNpa + 1).join(" ") : "";

    adresseSuggestions.value = [];
    showAdresseDropdown.value = false;
};

/**
 * Gère le clic extérieur au dropdown d'adresses pour le fermer.
 * @author Ngoie Steven
 * @param {MouseEvent} event - L'événement de clic
 * @returns {void}
 */
const handleAdresseClickOutside = (event) => {
    if (adresseRef.value && !adresseRef.value.contains(event.target)) {
        showAdresseDropdown.value = false;
    }
};

/**
 * Valide tous les champs du formulaire de profil.
 * @author Ngoie Steven
 * @returns {boolean} True si le formulaire est valide, false sinon
 */
const validerFormulaire = () => {
    // Réinitialiser les erreurs
    Object.keys(errors).forEach((key) => {
        errors[key] = "";
    });

    let isValid = true;

    // Validation des champs requis
    if (!form.nom.trim()) {
        errors.nom = "Le nom est requis";
        isValid = false;
    }

    if (!form.prenom.trim()) {
        errors.prenom = "Le prénom est requis";
        isValid = false;
    }

    if (!form.email.trim()) {
        errors.email = "L'email est requis";
        isValid = false;
    } else if (!isValidEmail(form.email)) {
        errors.email = "L'email est invalide";
        isValid = false;
    }

    if (!form.dateNaissance.trim()) {
        errors.dateNaissance = "La date de naissance est requise";
        isValid = false;
    } else if (!isValidDate(form.dateNaissance)) {
        errors.dateNaissance = "La date est invalide (format: JJ/MM/AAAA)";
        isValid = false;
    }

    if (!form.adresse.trim()) {
        errors.adresse = "L'adresse est requise";
        isValid = false;
    }

    if (!form.numero.trim()) {
        errors.numero = "Le numéro est requis";
        isValid = false;
    }

    if (!form.npa.trim()) {
        errors.npa = "Le NPA est requis";
        isValid = false;
    }

    if (!form.commune.trim()) {
        errors.commune = "La commune est requise";
        isValid = false;
    }

    if (!form.nationalite.trim()) {
        errors.nationalite = "La nationalité est requise";
        isValid = false;
    }

    if (!form.telephone.trim()) {
        errors.telephone = "Le numéro de téléphone est requis";
        isValid = false;
    } else if (!isValidPhone(form.telephone)) {
        errors.telephone = "Le numéro de téléphone est invalide";
        isValid = false;
    }

    if (!form.tailleTshirt) {
        errors.tailleTshirt = "La taille T-Shirt est requise";
        isValid = false;
    }

    return isValid;
};

/**
 * Valide le format d'une adresse email.
 * @author Ngoie Steven
 * @param {string} email - L'email à valider
 * @returns {boolean} True si l'email est valide
 */
const isValidEmail = (email) => {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
};

/**
 * Valide le format d'une date au format JJ/MM/AAAA.
 * @author Ngoie Steven
 * @param {string} dateStr - La date à valider
 * @returns {boolean} True si la date est valide
 */
const isValidDate = (dateStr) => {
    const regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
    const match = dateStr.match(regex);

    if (!match) return false;

    const day = parseInt(match[1], 10);
    const month = parseInt(match[2], 10);
    const year = parseInt(match[3], 10);

    if (month < 1 || month > 12) return false;
    if (day < 1 || day > 31) return false;
    if (year < 1900 || year > new Date().getFullYear()) return false;

    return true;
};

/**
 * Valide le format d'un numéro de téléphone (XX XXX XX XX).
 * @author Ngoie Steven
 * @param {string} phone - Le numéro de téléphone à valider
 * @returns {boolean} True si le téléphone est valide
 */
const isValidPhone = (phone) => {
    const regex = /^(\d{3})\s(\d{3})\s(\d{2})\s(\d{2})$/;
    return regex.test(phone);
};

/**
 * Soumet le formulaire de profil à l'API après validation.
 * @author Ngoie Steven
 * @async
 * @returns {Promise<void>}
 */
const submitForm = async () => {
    formFeedback.message = "";
    formFeedback.isError = false;

    if (!validerFormulaire()) {
        formFeedback.message = "Veuillez corriger les erreurs du formulaire.";
        formFeedback.isError = true;
        return;
    }

    isSavingProfile.value = true;
    try {
        const payload = {
            ...form,
            photo: profileImageFile.value,
        };

        const response = await profilService.updateProfil(payload);
        applyProfileData(response.data);
        profileImageFile.value = null;
        successModalTitle.value = "Profil mis a jour avec succès";
        successModalMessage.value =
            "Vos informations ont bien été enregistrées.";
        showSuccessModal.value = true;
        await authStore.fetchUser();
    } catch (error) {
        const apiErrors = error?.response?.data?.errors;
        if (apiErrors) {
            applyApiErrors(apiErrors);
            formFeedback.message = "Certaines informations sont invalides.";
        } else {
            formFeedback.message = "La mise a jour du profil a échouée.";
        }
        formFeedback.isError = true;
    } finally {
        isSavingProfile.value = false;
    }
};

/**
 * Retourne à la page précédente du navigateur.
 * @author Ngoie Steven
 * @returns {void}
 */
const handleRetour = () => {
    router.back();
};

/**
 * Navigue vers la page des inscriptions en s'assurant de revenir à la vue participant.
 * @author Ngoie Steven
 * @returns {Promise<void>}
 */
const goToMesInscriptions = () => {
    // Si l'utilisateur est admin et actuellement en vue organisateur,
    // on repasse explicitement en vue participant avant la navigation.
    if (authStore.showAdminLayout) {
        authStore.toggleAdminMode();
    }

    router.push("/inscriptions");
};

/**
 * Déclenche l'input de fichier pour changer la photo de profil.
 * @author Ngoie Steven
 * @returns {void}
 */
const triggerProfileImageInput = () => {
    profileImageInput.value.click();
};

/**
 * Définit la photo de profil et crée un URL d'aperçu.
 * @author Ngoie Steven
 * @param {File} file - Le fichier image sélectionné
 * @returns {void}
 */
const setProfileImage = (file) => {
    if (!file || !file.type.startsWith("image/")) {
        return;
    }

    profileImageFile.value = file;
    userProfilePicture.value = URL.createObjectURL(file);
    profileImageName.value = file.name;
};

/**
 * Gère le changement de photo de profil via l'input de fichier.
 * @author Ngoie Steven
 * @param {Event} event - L'événement de changement de fichier
 * @returns {void}
 */
const handleProfileImageChange = (event) => {
    const file = event.target.files[0];
    setProfileImage(file);
};

/**
 * Gère le drop de fichier photo sur la zone de dépôt.
 * @author Ngoie Steven
 * @param {DragEvent} event - L'événement de dépôt
 * @returns {void}
 */
const handleDropProfileImage = (event) => {
    isDropzoneActive.value = false;
    const file = event.dataTransfer?.files?.[0];
    setProfileImage(file);
};

/**
 * Soumet le formulaire de modification du mot de passe à l'API.
 * @author Ngoie Steven
 * @async
 * @returns {Promise<void>}
 */
const submitPasswordForm = async () => {
    resetPasswordErrors();
    passwordFeedback.message = "";
    passwordFeedback.isError = false;

    if (!passwordForm.currentPassword) {
        passwordErrors.currentPassword = "Le mot de passe actuel est requis.";
        return;
    }

    if (!passwordForm.newPassword) {
        passwordErrors.newPassword = "Le nouveau mot de passe est requis.";
        return;
    }

    if (passwordForm.newPassword.length < 8) {
        passwordErrors.newPassword =
            "Le mot de passe doit contenir au moins 8 caracteres.";
        return;
    }

    if (passwordForm.newPassword !== passwordForm.newPasswordConfirmation) {
        passwordErrors.newPasswordConfirmation =
            "La confirmation ne correspond pas.";
        return;
    }

    isSavingPassword.value = true;
    try {
        await profilService.updateAuthPassword({
            currentPassword: passwordForm.currentPassword,
            newPassword: passwordForm.newPassword,
            newPassword_confirmation: passwordForm.newPasswordConfirmation,
        });

        closePasswordModal();
        successModalTitle.value = "Mot de passe modifié avec succès";
        successModalMessage.value = "Votre mot de passe a bien été mis à jour.";
        showSuccessModal.value = true;
    } catch (error) {
        const apiErrors = error?.response?.data?.errors || {};
        if (apiErrors.currentPassword) {
            passwordErrors.currentPassword = apiErrors.currentPassword[0];
        }
        if (apiErrors.newPassword) {
            passwordErrors.newPassword = apiErrors.newPassword[0];
        }

        passwordFeedback.message =
            error?.response?.data?.message ||
            "La modification du mot de passe a échoué.";
        passwordFeedback.isError = true;
    } finally {
        isSavingPassword.value = false;
    }
};

import participantService from "../services/participantService";

// ===== Sous-profils participants =====
const chargementParticipants = ref(false);
const tousParticipants = ref([]);
const participantEdite = ref(null);
const participantASupprimer = ref(null);
const isSavingEdition = ref(false);
const isSuppression = ref(false);
const erreurEdition = ref(null);
const erreurSuppression = ref(null);
const formEdition = reactive({
    nom: "",
    prenom: "",
    date_naissance: "",
    telephone: "",
    taille_tshirt: "M",
    sexe: "M",
    nationalite: "Suisse",
});

/**
 * Filtre et retourne les sous-profils de participants (autres que l'utilisateur courant).
 * @author Ngoie Steven
 * @returns {Array} Liste des sous-profils participants
 */
const sousProfilsParticipants = computed(() => {
    const moi = authStore.user?.participant?.id;
    return tousParticipants.value.filter((p) => p.id !== moi);
});

/**
 * Charge la liste de tous les participants liés au compte utilisateur.
 * @author Ngoie Steven
 * @async
 * @returns {Promise<void>}
 */
async function chargerParticipants() {
    chargementParticipants.value = true;
    try {
        const res = await participantService.getMesParticipants();
        tousParticipants.value = res.data;
    } catch (e) {
        console.error("Erreur chargement participants:", e);
    } finally {
        chargementParticipants.value = false;
    }
}

/**
 * Ouvre le modal d'édition pour un participant sélectionné.
 * @author Ngoie Steven
 * @param {Object} p - Le participant à éditer
 * @returns {void}
 */
function ouvrirEditionParticipant(p) {
    participantEdite.value = p;
    erreurEdition.value = null;
    Object.assign(formEdition, {
        nom: p.nom,
        prenom: p.prenom,
        date_naissance: p.date_naissance ?? "",
        telephone: p.telephone ?? "",
        taille_tshirt: p.taille_tshirt ?? "M",
        sexe: p.sexe ?? "M",
        nationalite: p.nationalite ?? "Suisse",
    });
}

/**
 * Enregistre les modifications apportées à un participant.
 * @author Ngoie Steven
 * @async
 * @returns {Promise<void>}
 */
async function sauvegarderEditionParticipant() {
    if (!formEdition.nom.trim() || !formEdition.prenom.trim()) return;
    isSavingEdition.value = true;
    erreurEdition.value = null;
    try {
        await participantService.majParticipant(
            participantEdite.value.id,
            formEdition,
        );
        await chargerParticipants();
        participantEdite.value = null;
    } catch (e) {
        erreurEdition.value =
            e.response?.data?.message ?? "Une erreur est survenue.";
    } finally {
        isSavingEdition.value = false;
    }
}

/**
 * Prépare la suppression d'un participant en ouvrant le modal de confirmation.
 * @author Ngoie Steven
 * @param {Object} p - Le participant à supprimer
 * @returns {void}
 */
function confirmerSuppressionParticipant(p) {
    participantASupprimer.value = p;
    erreurSuppression.value = null;
}

/**
 * Supprime le participant confirmé via l'API.
 * @author Ngoie Steven
 * @async
 * @returns {Promise<void>}
 */
async function supprimerParticipantConfirme() {
    isSuppression.value = true;
    erreurSuppression.value = null;
    try {
        await participantService.supprimerParticipant(
            participantASupprimer.value.id,
        );
        await chargerParticipants();
        participantASupprimer.value = null;
    } catch (e) {
        erreurSuppression.value =
            e.response?.data?.message ?? "Une erreur est survenue.";
    } finally {
        isSuppression.value = false;
    }
}

onMounted(() => {
    document.addEventListener("mousedown", handleAdresseClickOutside);
    loadProfile();
    chargerParticipants();
});

onBeforeUnmount(() => {
    document.removeEventListener("mousedown", handleAdresseClickOutside);
});
</script>
