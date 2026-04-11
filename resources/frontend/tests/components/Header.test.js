import { describe, test, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { reactive } from 'vue'

const routerPushMock = vi.fn()
const alertMock = vi.fn()

const authStoreMock = reactive({
  isAdmin: false,
  showAdminLayout: false,
  user: {
    participant: {
      prenom: 'Alice',
      nom: 'Dupont',
    },
  },
  toggleAdminMode: vi.fn(() => {
    authStoreMock.showAdminLayout = !authStoreMock.showAdminLayout
  }),
})

const themeStoreMock = reactive({
  primaryColor: null,
  secondaryColor: null,
})

const cartStoreMock = reactive({
  inscriptions: [],
  cartCount: 0,
  cartTotal: 0,
  isDropdownOpen: false,
  fermerDropdown: vi.fn(() => {
    cartStoreMock.isDropdownOpen = false
  }),
  toggleDropdown: vi.fn(() => {
    cartStoreMock.isDropdownOpen = !cartStoreMock.isDropdownOpen
  }),
})

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    props: ['icon'],
    template: '<span data-test="icon"></span>',
  },
}))

vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: routerPushMock,
  }),
}))

vi.mock('../../stores/auth', () => ({
  useAuthStore: () => authStoreMock,
}))

vi.mock('../../stores/theme', () => ({
  useThemeStore: () => themeStoreMock,
}))

vi.mock('../../stores/cart', () => ({
  useCartStore: () => cartStoreMock,
}))

vi.mock('../../services/groupeService', () => ({
  default: {
    getMesInvitations: vi.fn(),
    accepterInvitation: vi.fn(),
    refuserInvitation: vi.fn(),
  },
}))

vi.mock('../../services/api', () => ({
  default: {
    get: vi.fn(),
  },
}))

import Header from '../../components/Header.vue'
import groupeService from '../../services/groupeService'
import api from '../../services/api'

function mountComponent() {
  return mount(Header, {
    global: {
      stubs: {
        RouterLink: {
          props: ['to'],
          template: '<a><slot /></a>',
        },
      },
    },
  })
}

describe('Header', () => {
  beforeEach(() => {
    vi.clearAllMocks()

    authStoreMock.isAdmin = false
    authStoreMock.showAdminLayout = false
    authStoreMock.user = {
      participant: {
        prenom: 'Alice',
        nom: 'Dupont',
      },
    }

    themeStoreMock.primaryColor = null
    themeStoreMock.secondaryColor = null

    cartStoreMock.inscriptions = []
    cartStoreMock.cartCount = 0
    cartStoreMock.cartTotal = 0
    cartStoreMock.isDropdownOpen = false

    groupeService.getMesInvitations.mockResolvedValue({ data: [] })
    groupeService.accepterInvitation.mockResolvedValue({})
    groupeService.refuserInvitation.mockResolvedValue({})
    api.get.mockResolvedValue({ data: { tarif: 0 } })

    vi.stubGlobal('alert', alertMock)
  })

  // Charge les invitations au montage pour un participant
  test('charge les invitations au montage', async () => {
    groupeService.getMesInvitations.mockResolvedValue({
      data: [{ id: 1, nom: 'Team Flash', type: 'Groupe', course: { fin_inscription: '2099-01-01' } }],
    })

    const wrapper = mountComponent()
    await flushPromises()

    expect(groupeService.getMesInvitations).toHaveBeenCalledTimes(1)
    expect(wrapper.text()).toContain('Alice')
    expect(wrapper.text()).toContain('DUPONT')
  })

  // Change de mode admin et navigue vers la bonne page
  test('handleToggleMode bascule et redirige', async () => {
    authStoreMock.isAdmin = true
    authStoreMock.showAdminLayout = false

    const wrapper = mountComponent()
    await flushPromises()

    const toggleButton = wrapper.find('button:has([data-test="icon"])')
    const adminButton = wrapper.findAll('button').find((b) => b.text().includes('Vue Organisateur') || b.text().includes('Vue Participant'))
    expect(adminButton).toBeTruthy()

    await adminButton.trigger('click')

    expect(authStoreMock.toggleAdminMode).toHaveBeenCalled()
    expect(routerPushMock).toHaveBeenCalledWith('/organisateur/evenements')

    authStoreMock.showAdminLayout = true
    await adminButton.trigger('click')
    expect(routerPushMock).toHaveBeenCalledWith('/accueil')

    expect(toggleButton.exists()).toBe(true)
  })

  // Ouvre le mini-panier et affiche le total avec deduction changement
  test('calcule total mini panier avec deduction changement', async () => {
    cartStoreMock.inscriptions = [
      { ancienneInscriptionId: 10, tarif: 70, courseDetails: { nom_course: 'Trail', evenement: {} } },
      { ancienneInscriptionId: 11, tarif: 50, courseDetails: { nom_course: 'Semi', evenement: {} } },
    ]
    cartStoreMock.cartCount = 2
    cartStoreMock.cartTotal = 120
    cartStoreMock.isDropdownOpen = true

    api.get.mockImplementation((url) => {
      if (url.includes('/10')) return Promise.resolve({ data: { tarif: 20 } })
      if (url.includes('/11')) return Promise.resolve({ data: { tarif: 15 } })
      return Promise.resolve({ data: { tarif: 0 } })
    })

    const wrapper = mountComponent()
    await flushPromises()

    expect(api.get).toHaveBeenCalledWith('/participant/inscriptions/10')
    expect(api.get).toHaveBeenCalledWith('/participant/inscriptions/11')
    expect(wrapper.text()).toContain('Déduction (Changement de course)')
    expect(wrapper.text()).toContain('- 35.00.-')
    expect(wrapper.text()).toContain('85.00.-')
  })

  // Aller au panier ferme le dropdown et redirige
  test('allerAuPanier ferme dropdown et navigue', async () => {
    cartStoreMock.cartCount = 1
    cartStoreMock.isDropdownOpen = true

    const wrapper = mountComponent()
    await flushPromises()

    const btnPanier = wrapper.findAll('button').find((b) => b.text().includes('Panier'))
    expect(btnPanier).toBeTruthy()

    await btnPanier.trigger('click')

    expect(cartStoreMock.fermerDropdown).toHaveBeenCalled()
    expect(routerPushMock).toHaveBeenCalledWith('/panier')
  })

  // Accepte une invitation et la retire de la liste locale
  test('accepterInvitation met a jour la liste et affiche une alerte', async () => {
    groupeService.getMesInvitations.mockResolvedValue({
      data: [{ id: 5, nom: 'Team A', type: 'Groupe', course: { fin_inscription: '2099-01-01' } }],
    })

    const wrapper = mountComponent()
    await flushPromises()

    const profileButton = wrapper.find('button.w-11.h-11.rounded-full')
    await profileButton.trigger('click')
    await flushPromises()

    const accepterBtn = wrapper.findAll('button').find((b) => b.text().includes('Accepter'))
    expect(accepterBtn).toBeTruthy()

    await accepterBtn.trigger('click')
    await flushPromises()

    expect(groupeService.accepterInvitation).toHaveBeenCalledWith(5)
    expect(alertMock).toHaveBeenCalled()
    expect(wrapper.text()).toContain("Vous n'avez aucune invitation en attente.")
  })

  // Refuse une invitation expiree
  test('refuserInvitation fonctionne aussi pour une invitation expiree', async () => {
    groupeService.getMesInvitations.mockResolvedValue({
      data: [{ id: 8, nom: 'Team B', type: 'Groupe', course: { fin_inscription: '2000-01-01' } }],
    })

    const wrapper = mountComponent()
    await flushPromises()

    const profileButton = wrapper.find('button.w-11.h-11.rounded-full')
    await profileButton.trigger('click')
    await flushPromises()

    expect(wrapper.text()).toContain('Expirée')

    const refuserBtn = wrapper.findAll('button').find((b) => b.text().includes('Supprimer l\'invitation'))
    expect(refuserBtn).toBeTruthy()

    await refuserBtn.trigger('click')
    await flushPromises()

    expect(groupeService.refuserInvitation).toHaveBeenCalledWith(8)
    expect(alertMock).toHaveBeenCalled()
  })
})
