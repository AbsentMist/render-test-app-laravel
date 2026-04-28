import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

const routerMock = {
  push: vi.fn(),
}

const authStoreMock = {
  login: vi.fn(),
  isAdmin: false,
}

vi.mock('vue-router', () => ({
  useRouter: () => routerMock,
}))

vi.mock('../../stores/auth', () => ({
  useAuthStore: () => authStoreMock,
}))

import Connexion from '../../views/Connexion.vue'

function mountComponent() {
  return mount(Connexion, {
    global: {
      stubs: {
        RouterLink: {
          name: 'RouterLink',
          props: ['to'],
          template: '<a :href="to"><slot /></a>',
        },
      },
    },
  })
}

describe('Connexion', () => {
  let wrapper

  beforeEach(() => {
    vi.clearAllMocks()
    authStoreMock.isAdmin = false
  })

  afterEach(() => {
    wrapper?.unmount()
  })

  // Affiche le formulaire et permet de masquer ou afficher le mot de passe
  test('affiche le formulaire et permet de basculer la visibilité du mot de passe', async () => {
    wrapper = mountComponent()

    expect(wrapper.text()).toContain('Bienvenue!')
    expect(wrapper.find('input[type="password"]').exists()).toBe(true)

    const toggleButton = wrapper.find('button[type="button"]')
    await toggleButton.trigger('click')

    expect(wrapper.find('input[type="text"]').exists()).toBe(true)
    expect(wrapper.find('input[type="password"]').exists()).toBe(false)
  })

  // Connecte le participant et redirige vers le tableau de bord
  test('connecte le participant et redirige vers le tableau de bord', async () => {
    authStoreMock.login.mockResolvedValue({})
    wrapper = mountComponent()

    await wrapper.get('input[type="email"]').setValue('alice@example.com')
    await wrapper.get('input[type="password"]').setValue('Aa1!aaaa')

    await wrapper.get('button:not([type="button"])').trigger('click')
    await flushPromises()

    expect(authStoreMock.login).toHaveBeenCalledWith('alice@example.com', 'Aa1!aaaa')
    expect(routerMock.push).toHaveBeenCalledWith('/accueil')
    expect(wrapper.text()).not.toContain('Identifiants incorrects')
  })

  // Connecte un organisateur et redirige vers la gestion des evenements
  test('connecte l organisateur et redirige vers organisateur evenements', async () => {
    authStoreMock.login.mockResolvedValue({})
    authStoreMock.isAdmin = true
    wrapper = mountComponent()

    await wrapper.get('input[type="email"]').setValue('admin@example.com')
    await wrapper.get('input[type="password"]').setValue('Aa1!aaaa')

    await wrapper.get('button:not([type="button"])').trigger('click')
    await flushPromises()

    expect(authStoreMock.login).toHaveBeenCalledWith('admin@example.com', 'Aa1!aaaa')
    expect(routerMock.push).toHaveBeenCalledWith('/organisateur/evenements')
  })

  // Affiche l etat de chargement pendant la connexion
  test('affiche letat de chargement pendant la connexion', async () => {
    authStoreMock.login.mockImplementation(() => new Promise(() => {}))
    wrapper = mountComponent()

    await wrapper.get('input[type="email"]').setValue('alice@example.com')
    await wrapper.get('input[type="password"]').setValue('Aa1!aaaa')
    await wrapper.get('button:not([type="button"])').trigger('click')

    expect(wrapper.get('button:not([type="button"])').text()).toContain('Connexion en cours...')
    expect(wrapper.get('button:not([type="button"])').attributes('disabled')).toBeDefined()
  })

  // Affiche le message de validation du backend en cas d erreur
  test('affiche le message de validation du backend en cas derreur', async () => {
    authStoreMock.login.mockRejectedValue({
      response: {
        data: {
          errors: {
            email: ['Adresse email invalide.'],
          },
        },
      },
    })
    wrapper = mountComponent()

    await wrapper.get('input[type="email"]').setValue('bad-email')
    await wrapper.get('input[type="password"]').setValue('secret')
    await wrapper.get('button:not([type="button"])').trigger('click')
    await flushPromises()

    expect(wrapper.text()).toContain('Adresse email invalide.')
    expect(routerMock.push).not.toHaveBeenCalled()
  })

  // Affiche un message generique si la connexion echoue sans detail backend
  test('affiche un message generique en cas derreur inattendue', async () => {
    authStoreMock.login.mockRejectedValue(new Error('Erreur reseau'))
    wrapper = mountComponent()

    await wrapper.get('input[type="email"]').setValue('alice@example.com')
    await wrapper.get('input[type="password"]').setValue('Aa1!aaaa')
    await wrapper.get('button:not([type="button"])').trigger('click')
    await flushPromises()

    expect(wrapper.text()).toContain('Identifiants incorrects, veuillez réessayer.')
    expect(routerMock.push).not.toHaveBeenCalled()
  })
})