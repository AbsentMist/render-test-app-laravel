import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { reactive } from 'vue'

const routerPushMock = vi.fn()
const routerBackMock = vi.fn()

const authStoreMock = reactive({
  showAdminLayout: false,
  toggleAdminMode: vi.fn(),
  fetchUser: vi.fn().mockResolvedValue(undefined),
})

const fetchMock = vi.fn()
const createObjectURLMock = vi.fn(() => 'blob:preview')

vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: routerPushMock,
    back: routerBackMock,
  }),
}))

vi.mock('../../stores/auth', () => ({
  useAuthStore: () => authStoreMock,
}))

vi.mock('../../services/profilService', () => ({
  default: {
    getProfil: vi.fn(),
    updateProfil: vi.fn(),
    updateAuthPassword: vi.fn(),
  },
}))

import ProfilUser from '../../views/ProfilUser.vue'
import profilService from '../../services/profilService'

const profilePayload = {
  nom: 'Dupont',
  prenom: 'Alice',
  email: 'alice.dupont@test.ch',
  dateNaissance: '01/02/2000',
  adresse: 'Rue de Carouge',
  numero: '14',
  club: 'Team A',
  npa: '1227',
  commune: 'Carouge',
  nationalite: 'Suisse',
  telephone: '0787774758',
  tailleTshirt: 'M',
  photo: null,
}

function mountComponent() {
  return mount(ProfilUser)
}

function findButtonByText(wrapper, text) {
  return wrapper.findAll('button').find((b) => b.text().includes(text))
}

describe('ProfilUser', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.useRealTimers()
    vi.stubGlobal('fetch', fetchMock)
    vi.stubGlobal('URL', { createObjectURL: createObjectURLMock })

    authStoreMock.showAdminLayout = false
    authStoreMock.toggleAdminMode = vi.fn()
    authStoreMock.fetchUser = vi.fn().mockResolvedValue(undefined)

    profilService.getProfil.mockResolvedValue({ data: profilePayload })
    profilService.updateProfil.mockImplementation(async (payload) => ({ data: payload }))
    profilService.updateAuthPassword.mockResolvedValue({ data: { message: 'ok' } })
    fetchMock.mockReset()
    createObjectURLMock.mockReset()
    createObjectURLMock.mockReturnValue('blob:preview')
  })

  afterEach(() => {
    vi.useRealTimers()
    vi.unstubAllGlobals()
  })

  test('charge le profil au montage et hydrate le formulaire', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    expect(profilService.getProfil).toHaveBeenCalledTimes(1)
    expect(wrapper.find('input[type="email"]').element.value).toBe('alice.dupont@test.ch')
    expect(wrapper.find('input[type="tel"]').element.value).toBe('078 777 47 58')
    expect(wrapper.find('select').element.value).toBe('M')
  })

  test('soumet le formulaire profil et affiche la modal de succes', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    expect(profilService.updateProfil).toHaveBeenCalledTimes(1)
    expect(authStoreMock.fetchUser).toHaveBeenCalledTimes(1)
    expect(wrapper.text()).toContain('Profil mis a jour avec succès')
  })

  test('soumet le formulaire profil et applique les erreurs API', async () => {
    profilService.updateProfil.mockRejectedValue({
      response: {
        data: {
          errors: {
            email: ['Email deja utilise'],
          },
        },
      },
    })

    const wrapper = mountComponent()
    await flushPromises()

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    expect(wrapper.text()).toContain('Certaines informations sont invalides.')
    expect(wrapper.text()).toContain('Email deja utilise')
  })

  test('soumet le formulaire profil et affiche une erreur generique si echec', async () => {
    profilService.updateProfil.mockRejectedValue(new Error('boom'))

    const wrapper = mountComponent()
    await flushPromises()

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    expect(wrapper.text()).toContain('La mise a jour du profil a échouée.')
  })

  test('bloque la soumission si le formulaire est invalide', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    await wrapper.find('input[type="email"]').setValue('email-invalide')
    await wrapper.find('input[placeholder="JJ/MM/AAAA"]').setValue('99/99/9999')
    await wrapper.find('input[type="tel"]').setValue('078123')
    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    expect(profilService.updateProfil).not.toHaveBeenCalled()
    expect(wrapper.text()).toContain('Veuillez corriger les erreurs du formulaire.')
    expect(wrapper.text()).toContain('L\'email est invalide')
    expect(wrapper.text()).toContain('La date est invalide (format: JJ/MM/AAAA)')
    expect(wrapper.text()).toContain('Le numéro de téléphone est invalide')
  })

  test('redirige vers les inscriptions et quitte le mode admin si actif', async () => {
    authStoreMock.showAdminLayout = true
    const wrapper = mountComponent()
    await flushPromises()

    const button = wrapper.findAll('button').find((b) => b.text().includes('Voir mes inscriptions'))
    await button.trigger('click')

    expect(authStoreMock.toggleAdminMode).toHaveBeenCalledTimes(1)
    expect(routerPushMock).toHaveBeenCalledWith('/inscriptions')
  })

  test('redirige vers les inscriptions sans basculer si deja en mode participant', async () => {
    authStoreMock.showAdminLayout = false
    const wrapper = mountComponent()
    await flushPromises()

    const button = findButtonByText(wrapper, 'Voir mes inscriptions')
    await button.trigger('click')

    expect(authStoreMock.toggleAdminMode).not.toHaveBeenCalled()
    expect(routerPushMock).toHaveBeenCalledWith('/inscriptions')
  })

  test('retour appelle router.back', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    const button = findButtonByText(wrapper, 'Retour')
    await button.trigger('click')

    expect(routerBackMock).toHaveBeenCalledTimes(1)
  })

  test('formate la date via l input date de naissance', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    const inputDate = wrapper.find('input[placeholder="JJ/MM/AAAA"]')
    await inputDate.setValue('01022000')

    expect(inputDate.element.value).toBe('01/02/2000')
  })

  test('formate le numero de telephone via l input telephone', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    const inputTelephone = wrapper.find('input[type="tel"]')
    await inputTelephone.setValue('0787774758')

    expect(inputTelephone.element.value).toBe('078 777 47 58')
  })

  test('recherche et selectionne une adresse depuis les suggestions', async () => {
    vi.useFakeTimers()
    fetchMock.mockResolvedValue({
      json: async () => ({
        results: [
          {
            attrs: {
              label: 'Avenue de la <b>Gare</b> 99 1000 Lausanne',
              num: 99,
            },
          },
        ],
      }),
    })

    const wrapper = mountComponent()
    await flushPromises()

    const inputAdresse = wrapper.find('input[placeholder="Ex: Rue du Rhône"]')
    await inputAdresse.setValue('Avenue de la Gare')

    vi.advanceTimersByTime(350)
    await flushPromises()

    expect(fetchMock).toHaveBeenCalledTimes(1)

    const suggestion = wrapper.findAll('button').find((b) => b.text().includes('Avenue de la Gare'))
    expect(suggestion).toBeTruthy()
    await suggestion.trigger('mousedown')

    expect(wrapper.find('input[placeholder="Ex: Rue du Rhône"]').element.value).toBe('Avenue de la Gare')
    expect(wrapper.find('input[placeholder="14"]').element.value).toBe('99')
    expect(wrapper.find('input[placeholder="1227"]').element.value).toBe('1000')
    expect(wrapper.find('input[placeholder="Carouge"]').element.value).toBe('Lausanne')
  })

  test('ferme les suggestions adresse au clic exterieur', async () => {
    vi.useFakeTimers()
    fetchMock.mockResolvedValue({
      json: async () => ({
        results: [
          {
            attrs: {
              label: 'Rue du Rhone 10 1204 Geneve',
              num: 10,
            },
          },
        ],
      }),
    })

    const wrapper = mountComponent()
    await flushPromises()

    const inputAdresse = wrapper.find('input[placeholder="Ex: Rue du Rhône"]')
    await inputAdresse.setValue('Rue du Rhone')

    vi.advanceTimersByTime(350)
    await flushPromises()

    expect(wrapper.text()).toContain('Rue du Rhone 10 1204 Geneve')
    document.dispatchEvent(new MouseEvent('mousedown', { bubbles: true }))
    await flushPromises()

    expect(wrapper.text()).not.toContain('Rue du Rhone 10 1204 Geneve')
  })

  test('declenche le click sur input file quand on clique la zone photo', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    const fileInput = wrapper.find('input[type="file"]')
    const clickMock = vi.fn()
    Object.defineProperty(fileInput.element, 'click', {
      value: clickMock,
      configurable: true,
    })
    const photoButton = wrapper.findAll('button')[0]
    await photoButton.trigger('click')

    expect(clickMock).toHaveBeenCalledTimes(1)
  })

  test('charge une image de profil via input file', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    const file = new File(['image'], 'avatar.png', { type: 'image/png' })
    const fileInput = wrapper.find('input[type="file"]')
    Object.defineProperty(fileInput.element, 'files', {
      value: [file],
      configurable: true,
    })

    await fileInput.trigger('change')
    await flushPromises()

    expect(createObjectURLMock).toHaveBeenCalledWith(file)
    expect(wrapper.text()).toContain('avatar.png')
    const image = wrapper.find('img[alt="Photo de profil"]')
    expect(image.exists()).toBe(true)
    expect(image.attributes('src')).toBe('blob:preview')
  })

  test('ignore les fichiers non image lors d un drop et desactive l etat drag actif', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    const photoButton = wrapper.findAll('button')[0]
    await photoButton.trigger('dragover')
    expect(photoButton.classes()).toContain('border-tertiary')

    const invalidFile = new File(['not-image'], 'notes.txt', { type: 'text/plain' })
    await photoButton.trigger('drop', {
      dataTransfer: {
        files: [invalidFile],
      },
    })

    expect(photoButton.classes()).toContain('border-gray-300')
    expect(wrapper.find('img[alt="Photo de profil"]').exists()).toBe(false)
  })

  test('met a jour le mot de passe depuis la modal', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    const openButton = wrapper.findAll('button').find((b) => b.text().includes('Modifier mon mot de passe'))
    await openButton.trigger('click')

    const passwordInputs = wrapper.findAll('input[type="password"]')
    await passwordInputs[0].setValue('CurrentPwd!123')
    await passwordInputs[1].setValue('NewStrongPwd!456')
    await passwordInputs[2].setValue('NewStrongPwd!456')

    const savePasswordButton = wrapper.findAll('button').find((b) => b.text().includes('Enregistrer le mot de passe'))
    await savePasswordButton.trigger('click')
    await flushPromises()

    expect(profilService.updateAuthPassword).toHaveBeenCalledWith({
      currentPassword: 'CurrentPwd!123',
      newPassword: 'NewStrongPwd!456',
      newPassword_confirmation: 'NewStrongPwd!456',
    })
    expect(wrapper.text()).toContain('Mot de passe modifie avec succes')
  })

  test('affiche une erreur si le mot de passe actuel est vide', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    await findButtonByText(wrapper, 'Modifier mon mot de passe').trigger('click')
    await findButtonByText(wrapper, 'Enregistrer le mot de passe').trigger('click')

    expect(profilService.updateAuthPassword).not.toHaveBeenCalled()
    expect(wrapper.text()).toContain('Le mot de passe actuel est requis.')
  })

  test('affiche une erreur si le nouveau mot de passe est trop court', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    await findButtonByText(wrapper, 'Modifier mon mot de passe').trigger('click')
    const passwordInputs = wrapper.findAll('input[type="password"]')
    await passwordInputs[0].setValue('CurrentPwd!123')
    await passwordInputs[1].setValue('short')
    await passwordInputs[2].setValue('short')

    await findButtonByText(wrapper, 'Enregistrer le mot de passe').trigger('click')

    expect(profilService.updateAuthPassword).not.toHaveBeenCalled()
    expect(wrapper.text()).toContain('Le mot de passe doit contenir au moins 8 caracteres.')
  })

  test('affiche une erreur si la confirmation du mot de passe differe', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    await findButtonByText(wrapper, 'Modifier mon mot de passe').trigger('click')
    const passwordInputs = wrapper.findAll('input[type="password"]')
    await passwordInputs[0].setValue('CurrentPwd!123')
    await passwordInputs[1].setValue('NewStrongPwd!456')
    await passwordInputs[2].setValue('MismatchPwd!789')

    await findButtonByText(wrapper, 'Enregistrer le mot de passe').trigger('click')

    expect(profilService.updateAuthPassword).not.toHaveBeenCalled()
    expect(wrapper.text()).toContain('La confirmation ne correspond pas.')
  })

  test('affiche les erreurs API lors de la mise a jour du mot de passe', async () => {
    profilService.updateAuthPassword.mockRejectedValue({
      response: {
        data: {
          message: 'Erreur de mot de passe',
          errors: {
            currentPassword: ['Mot de passe actuel invalide'],
            newPassword: ['Mot de passe trop faible'],
          },
        },
      },
    })

    const wrapper = mountComponent()
    await flushPromises()

    await findButtonByText(wrapper, 'Modifier mon mot de passe').trigger('click')
    const passwordInputs = wrapper.findAll('input[type="password"]')
    await passwordInputs[0].setValue('CurrentPwd!123')
    await passwordInputs[1].setValue('NewStrongPwd!456')
    await passwordInputs[2].setValue('NewStrongPwd!456')

    await findButtonByText(wrapper, 'Enregistrer le mot de passe').trigger('click')
    await flushPromises()

    expect(wrapper.text()).toContain('Mot de passe actuel invalide')
    expect(wrapper.text()).toContain('Mot de passe trop faible')
    expect(wrapper.text()).toContain('Erreur de mot de passe')
  })

  test('annuler dans la modal reinitialise le formulaire mot de passe', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    await findButtonByText(wrapper, 'Modifier mon mot de passe').trigger('click')

    let passwordInputs = wrapper.findAll('input[type="password"]')
    await passwordInputs[0].setValue('CurrentPwd!123')
    await passwordInputs[1].setValue('NewStrongPwd!456')
    await passwordInputs[2].setValue('NewStrongPwd!456')

    await findButtonByText(wrapper, 'Annuler').trigger('click')
    await findButtonByText(wrapper, 'Modifier mon mot de passe').trigger('click')

    passwordInputs = wrapper.findAll('input[type="password"]')
    expect(passwordInputs[0].element.value).toBe('')
    expect(passwordInputs[1].element.value).toBe('')
    expect(passwordInputs[2].element.value).toBe('')
  })

  test('inscrit puis desinscrit le listener document sur cycle de vie', async () => {
    const addSpy = vi.spyOn(document, 'addEventListener')
    const removeSpy = vi.spyOn(document, 'removeEventListener')

    const wrapper = mountComponent()
    await flushPromises()
    wrapper.unmount()

    expect(addSpy).toHaveBeenCalledWith('mousedown', expect.any(Function))
    expect(removeSpy).toHaveBeenCalledWith('mousedown', expect.any(Function))

    addSpy.mockRestore()
    removeSpy.mockRestore()
  })

  test('affiche une erreur quand le chargement du profil echoue', async () => {
    profilService.getProfil.mockRejectedValue(new Error('API down'))
    const errorSpy = vi.spyOn(console, 'error').mockImplementation(() => {})

    const wrapper = mountComponent()
    await flushPromises()

    expect(wrapper.text()).toContain('Impossible de charger le profil.')
    expect(errorSpy).toHaveBeenCalled()

    errorSpy.mockRestore()
  })
})
