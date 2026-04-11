import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

const routerMock = {
  push: vi.fn(),
}

const authStoreMock = {
  register: vi.fn(),
}

const fetchMock = vi.fn()

class MockFileReader {
  constructor() {
    this.onload = null
  }

  readAsDataURL(file) {
    if (this.onload) {
      this.onload({ target: { result: `data:${file.type};base64,preview` } })
    }
  }
}

vi.mock('vue-router', () => ({
  useRouter: () => routerMock,
}))

vi.mock('../../stores/auth', () => ({
  useAuthStore: () => authStoreMock,
}))

vi.mock('../../components/IndicateurEtapes.vue', () => ({
  default: {
    name: 'IndicateurEtapes',
    props: ['steps', 'currentStep'],
    template: '<div data-test="indicateur-etapes"></div>',
  },
}))

import CreationCompte from '../../views/CreationCompte.vue'

function mountComponent() {
  return mount(CreationCompte)
}

describe('CreationCompte', () => {
  let wrapper

  beforeEach(() => {
    vi.clearAllMocks()
    authStoreMock.register.mockResolvedValue({})
    vi.stubGlobal('fetch', fetchMock)
    vi.stubGlobal('FileReader', MockFileReader)
  })

  afterEach(() => {
    wrapper?.unmount()
    vi.useRealTimers()
  })

  // Reste sur la premiere etape et affiche les erreurs de validation
  test('reste sur la premiere etape et affiche les erreurs de validation', async () => {
    wrapper = mountComponent()

    expect(wrapper.text()).toContain('Prénom :')
    expect(wrapper.text()).toContain('Nom :')

    const nextButton = wrapper.findAll('button').find((button) => button.text() === 'Suivant')
    await nextButton.trigger('click')

    expect(wrapper.text()).toContain('Le prénom est requis.')
    expect(wrapper.text()).toContain('Le nom est requis.')
    expect(wrapper.text()).toContain("L'email est requis.")
    expect(wrapper.text()).toContain('Le mot de passe est requis.')
    expect(wrapper.text()).toContain('Veuillez répéter le mot de passe.')
    expect(wrapper.text()).not.toContain('Genre :')
  })

  // Termine la creation du compte et redirige vers la connexion
  test('termine la creation du compte et redirige vers la connexion', async () => {
    wrapper = mountComponent()

    await wrapper.findAll('input').at(0).setValue('Alice')
    await wrapper.findAll('input').at(1).setValue('Martin')
    await wrapper.findAll('input').at(2).setValue('alice@example.com')
    await wrapper.findAll('input').at(3).setValue('Aa1!aaaa')
    await wrapper.findAll('input').at(4).setValue('Aa1!aaaa')

    await wrapper.findAll('button').find((button) => button.text() === 'Suivant').trigger('click')

    expect(wrapper.text()).toContain('Genre :')

    await wrapper.findAll('button').find((button) => button.text() === 'Femme').trigger('click')
    await wrapper.find('input[placeholder="JJ/MM/AAAA"]').setValue('01011990')
    await wrapper.find('input[type="tel"]').setValue('12345678')

    const step2TextInputs = wrapper.findAll('input[type="text"]')
    await step2TextInputs.at(1).setValue('ACME Running')
    await step2TextInputs.at(2).setValue('Sui')

    await flushPromises()

    const switzerlandOption = wrapper.findAll('button').find((button) => button.text() === 'Suisse')
    await switzerlandOption.trigger('mousedown')

    await wrapper.findAll('button').find((button) => button.text() === 'Suivant').trigger('click')

    expect(wrapper.text()).toContain('Adresse :')

    await wrapper.find('input[placeholder="Ex: Rue du Rhône"]').setValue('Rue du Rhône')

    const step3TextInputs = wrapper.findAll('input[type="text"]')
    await step3TextInputs.at(1).setValue('12')
    await step3TextInputs.at(2).setValue('1204')
    await step3TextInputs.at(3).setValue('Genève')
    await wrapper.find('select').setValue('M')

    await wrapper.findAll('button').find((button) => button.text() === 'Créer mon compte').trigger('click')
    await flushPromises()

    expect(authStoreMock.register).toHaveBeenCalledTimes(1)
    expect(authStoreMock.register).toHaveBeenCalledWith(
      expect.objectContaining({
        email: 'alice@example.com',
        password: 'Aa1!aaaa',
        password_confirmation: 'Aa1!aaaa',
        prenom: 'Alice',
        nom: 'Martin',
        date_naissance: '1990-01-01',
        telephone: '123 456 78',
        nationalite: 'Suisse',
        adresse: 'Rue du Rhône 12',
        code_postal: '1204',
        ville: 'Genève',
        pays: 'Suisse',
        taille_tshirt: 'M',
        sexe: 'Femme',
        equipe_nom: 'ACME Running',
      }),
    )
    expect(routerMock.push).toHaveBeenCalledWith('/login')
  })

  // Valide les champs de la premiere etape
  test('validateStep1 retourne false quand les identifiants sont incomplets', async () => {
    wrapper = mountComponent()

    const valide = await wrapper.vm.validateStep1()

    expect(valide).toBe(false)
    expect(wrapper.vm.errors.prenom).toBe('Le prénom est requis.')
    expect(wrapper.vm.errors.nom).toBe('Le nom est requis.')
    expect(wrapper.vm.errors.email).toBe("L'email est requis.")
    expect(wrapper.vm.errors.password).toBe('Le mot de passe est requis.')
    expect(wrapper.vm.errors.passwordConfirm).toBe('Veuillez répéter le mot de passe.')
  })

  // Valide les champs de la deuxieme etape
  test('validateStep2 retourne false quand le profil est incomplet', async () => {
    wrapper = mountComponent()
    wrapper.vm.currentStep = 2

    const valide = await wrapper.vm.validateStep2()

    expect(valide).toBe(false)
    expect(wrapper.vm.errors.genre).toBe('Veuillez sélectionner un genre.')
    expect(wrapper.vm.errors.dateNaissance).toBe('La date de naissance est requise.')
    expect(wrapper.vm.errors.telephone).toBe('Le numéro de téléphone est requis.')
    expect(wrapper.vm.errors.nationalite).toBe('Veuillez sélectionner une nationalité.')
  })

  // Valide les champs de la troisieme etape
  test('validateStep3 retourne false quand ladresse est incomplete', async () => {
    wrapper = mountComponent()
    wrapper.vm.currentStep = 3

    const valide = await wrapper.vm.validateStep3()

    expect(valide).toBe(false)
    expect(wrapper.vm.errors.adresse).toBe("L'adresse est requise.")
    expect(wrapper.vm.errors.numeroRue).toBe('Le numéro est requis.')
    expect(wrapper.vm.errors.npa).toBe('Le NPA est requis.')
    expect(wrapper.vm.errors.commune).toBe('La commune est requise.')
  })

  // Formate le numero de telephone
  test('formaterTelephone reformate le numero saisi', () => {
    wrapper = mountComponent()

    wrapper.vm.formaterTelephone({ target: { value: '12345678' } })

    expect(wrapper.vm.form.telephone).toBe('123 456 78')
  })

  // Formate la date de naissance
  test('formaterDate reformate la date saisie', () => {
    wrapper = mountComponent()

    wrapper.vm.formaterDate({ target: { value: '01011990' } })

    expect(wrapper.vm.form.dateNaissance).toBe('01/01/1990')
  })

  // Ouvre le calendrier natif et formate la date choisie
  test('dateDepuisCalendrier met a jour la date et ouvre le picker', () => {
    wrapper = mountComponent()
    const showPicker = vi.fn()
    wrapper.vm.datePickerRef = { showPicker }

    wrapper.vm.dateDepuisCalendrier({ target: { value: '1990-01-01' } })

    expect(wrapper.vm.form.dateNaissance).toBe('01/01/1990')
    expect(showPicker).toHaveBeenCalledTimes(1)
  })

  // Selectionne un pays et ferme la liste
  test('selectCountry renseigne la nationalite et ferme le menu', () => {
    wrapper = mountComponent()
    wrapper.vm.showCountryDropdown = true

    wrapper.vm.selectCountry('Suisse')

    expect(wrapper.vm.form.nationalite).toBe('Suisse')
    expect(wrapper.vm.nationaliteSearch).toBe('Suisse')
    expect(wrapper.vm.showCountryDropdown).toBe(false)
  })

  // Ferme la liste des pays si le clic est dehors
  test('handleClickOutside ferme la liste des pays', () => {
    wrapper = mountComponent()
    wrapper.vm.showCountryDropdown = true
    wrapper.vm.nationaliteRef = { contains: vi.fn(() => false) }

    wrapper.vm.handleClickOutside({ target: document.createElement('div') })

    expect(wrapper.vm.showCountryDropdown).toBe(false)
  })

  // Ferme la liste des adresses si le clic est dehors
  test('handleAdresseClickOutside ferme la liste des adresses', () => {
    wrapper = mountComponent()
    wrapper.vm.showAdresseDropdown = true
    wrapper.vm.adresseRef = { contains: vi.fn(() => false) }

    wrapper.vm.handleAdresseClickOutside({ target: document.createElement('div') })

    expect(wrapper.vm.showAdresseDropdown).toBe(false)
  })

  // Avance d une etape a l autre selon la validation
  test('nextStep avance de l etape 1 a 3 quand le formulaire est valide', async () => {
    wrapper = mountComponent()

    wrapper.vm.form.prenom = 'Alice'
    wrapper.vm.form.nom = 'Martin'
    wrapper.vm.form.email = 'alice@example.com'
    wrapper.vm.form.password = 'Aa1!aaaa'
    wrapper.vm.form.passwordConfirm = 'Aa1!aaaa'

    wrapper.vm.nextStep()
    expect(wrapper.vm.currentStep).toBe(2)

    wrapper.vm.form.genre = 'Femme'
    wrapper.vm.form.dateNaissance = '01/01/1990'
    wrapper.vm.form.telephone = '123 456 78'
    wrapper.vm.form.nationalite = 'Suisse'

    wrapper.vm.nextStep()
    expect(wrapper.vm.currentStep).toBe(3)
  })

  // Repart en arriere ou revient a la connexion
  test('previousStep revient en arriere puis redirige si on est a la premiere etape', () => {
    wrapper = mountComponent()

    wrapper.vm.currentStep = 2
    wrapper.vm.previousStep()
    expect(wrapper.vm.currentStep).toBe(1)

    wrapper.vm.previousStep()
    expect(routerMock.push).toHaveBeenCalledWith('/login')
  })

  // Declenche le clic sur le champ fichier cache
  test('triggerFileInput clique sur le champ fichier', () => {
    wrapper = mountComponent()
    const click = vi.fn()
    wrapper.vm.fileInput = { click }

    wrapper.vm.triggerFileInput()

    expect(click).toHaveBeenCalledTimes(1)
  })

  // Charge une photo et genere un apercu
  test('handlePhotoChange enregistre le fichier et le preview', () => {
    wrapper = mountComponent()
    const file = new File(['image'], 'avatar.png', { type: 'image/png' })

    wrapper.vm.handlePhotoChange({ target: { files: [file] } })

    expect(wrapper.vm.form.photo.name).toBe('avatar.png')
    expect(wrapper.vm.form.photo.type).toBe('image/png')
    expect(wrapper.vm.photoPreview).toBe('data:image/png;base64,preview')
  })

  // Recherche une adresse et remplit les suggestions
  test('rechercherAdresse vide ou charge les suggestions selon la saisie', async () => {
    wrapper = mountComponent()

    await wrapper.vm.rechercherAdresse('ab')
    expect(wrapper.vm.adresseSuggestions).toEqual([])
    expect(wrapper.vm.showAdresseDropdown).toBe(false)

    vi.useFakeTimers()
    fetchMock.mockResolvedValueOnce({
      json: vi.fn().mockResolvedValue({
        results: [
          {
            attrs: {
              label: 'Rue du Rhône 12 <b>1204 Genève</b>',
              num: '12',
            },
          },
        ],
      }),
    })

    const promise = wrapper.vm.rechercherAdresse('Rue')
    await vi.advanceTimersByTimeAsync(300)
    await promise
    await flushPromises()

    expect(fetchMock).toHaveBeenCalledTimes(1)
    expect(wrapper.vm.showAdresseDropdown).toBe(true)
    expect(wrapper.vm.adresseSuggestions).toHaveLength(1)
  })

  // Selectionne une adresse proposee et remplit les champs
  test('selectionnerAdresse remplit les champs de coordonnees', () => {
    wrapper = mountComponent()

    wrapper.vm.selectionnerAdresse({
      attrs: {
        label: 'Rue du Rhône 12 1204 Genève',
        num: '12',
      },
    })

    expect(wrapper.vm.form.adresse).toBe('Rue du Rhône')
    expect(wrapper.vm.form.numeroRue).toBe('12')
    expect(wrapper.vm.form.npa).toBe('1204')
    expect(wrapper.vm.form.commune).toBe('Genève')
    expect(wrapper.vm.showAdresseDropdown).toBe(false)
  })

  // Le chargement monte et demonte les ecouteurs globaux
  test('enregistre et retire les ecouteurs globaux au montage', () => {
    const addSpy = vi.spyOn(document, 'addEventListener')
    const removeSpy = vi.spyOn(document, 'removeEventListener')

    wrapper = mountComponent()
    expect(addSpy).toHaveBeenCalledWith('mousedown', expect.any(Function))

    wrapper.unmount()

    expect(removeSpy).toHaveBeenCalledWith('mousedown', expect.any(Function))

    addSpy.mockRestore()
    removeSpy.mockRestore()
  })

  // Soumet le formulaire complet et redirige
  test('handleRegister envoie les donnees et redirige', async () => {
    wrapper = mountComponent()

    wrapper.vm.currentStep = 3
    wrapper.vm.form.prenom = 'Alice'
    wrapper.vm.form.nom = 'Martin'
    wrapper.vm.form.email = 'alice@example.com'
    wrapper.vm.form.password = 'Aa1!aaaa'
    wrapper.vm.form.passwordConfirm = 'Aa1!aaaa'
    wrapper.vm.form.dateNaissance = '01/01/1990'
    wrapper.vm.form.telephone = '123 456 78'
    wrapper.vm.form.nationalite = 'Suisse'
    wrapper.vm.form.adresse = 'Rue du Rhône'
    wrapper.vm.form.numeroRue = '12'
    wrapper.vm.form.npa = '1204'
    wrapper.vm.form.commune = 'Genève'
    wrapper.vm.form.genre = 'Femme'
    wrapper.vm.form.club = 'ACME Running'
    wrapper.vm.form.tailleTshirt = 'M'

    await wrapper.vm.handleRegister()

    expect(authStoreMock.register).toHaveBeenCalledTimes(1)
    expect(routerMock.push).toHaveBeenCalledWith('/login')
  })

  // Garde le message backend si l envoi echoue
  test('handleRegister affiche une erreur backend', async () => {
    authStoreMock.register.mockRejectedValue({
      response: {
        data: {
          errors: {
            email: ['Adresse email déjà utilisée.'],
          },
        },
      },
    })
    wrapper = mountComponent()

    wrapper.vm.currentStep = 3
    wrapper.vm.form.prenom = 'Alice'
    wrapper.vm.form.nom = 'Martin'
    wrapper.vm.form.email = 'alice@example.com'
    wrapper.vm.form.password = 'Aa1!aaaa'
    wrapper.vm.form.passwordConfirm = 'Aa1!aaaa'
    wrapper.vm.form.dateNaissance = '01/01/1990'
    wrapper.vm.form.telephone = '123 456 78'
    wrapper.vm.form.nationalite = 'Suisse'
    wrapper.vm.form.adresse = 'Rue du Rhône'
    wrapper.vm.form.numeroRue = '12'
    wrapper.vm.form.npa = '1204'
    wrapper.vm.form.commune = 'Genève'
    wrapper.vm.form.genre = 'Femme'

    await wrapper.vm.handleRegister()

    expect(wrapper.vm.errors.email).toBe('Adresse email déjà utilisée.')
    expect(wrapper.vm.currentStep).toBe(1)
  })
})