/**
 * Tests frontend du projet.
 *
 * @author Ngozoo
 * @returns {void}
 */

import { describe, test, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

const routerPushMock = vi.fn()

const cartStoreMock = {
  inscriptions: [],
  cartTotal: 0,
  cartCount: 1,
  fermerDropdown: vi.fn(),
  viderPanier: vi.fn(),
}

const authStoreMock = {
  isAdmin: false,
}

vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: routerPushMock,
  }),
}))

vi.mock('../../stores/cart', () => ({
  useCartStore: () => cartStoreMock,
}))

vi.mock('../../stores/auth', () => ({
  useAuthStore: () => authStoreMock,
}))

vi.mock('../../stores/theme', () => ({
  useThemeStore: () => ({ theme: 'light' }),
}))

vi.mock('../../services/membershipService', () => ({
  default: {
    checkoutMembership: vi.fn(),
  },
}))

vi.mock('../../services/inscriptionService', () => ({
  default: {
    createInscription: vi.fn(),
    updateInscriptionAdmin: vi.fn(),
    updateInscription: vi.fn(),
  },
}))

vi.mock('../../services/groupeService', () => ({
  default: {
    createGroupe: vi.fn(),
    addParticipant: vi.fn(),
  },
}))

vi.mock('../../services/choixOptionParticipantService', () => ({
  default: {
    saveChoix: vi.fn(),
  },
}))

vi.mock('../../services/reponseQuestionParticipantService', () => ({
  default: {
    saveReponses: vi.fn(),
  },
}))

vi.mock('../../services/documentService', () => ({
  default: {
    uploadDocument: vi.fn(),
  },
}))

vi.mock('../../services/api', () => ({
  default: {
    post: vi.fn(),
    get: vi.fn(),
  },
}))

vi.mock('../../services/prixEvolutifService', () => ({
  default: {
    getTarifActuel: vi.fn(),
  },
}))

vi.mock('../../components/PopupConfirmation.vue', () => ({
  default: {
    name: 'PopupConfirmation',
    props: ['message', 'icon'],
    emits: ['confirm', 'cancel'],
    template: '<div data-test="popup-confirmation"></div>',
  },
}))

import Panier from '../../views/Panier.vue'
import membershipService from '../../services/membershipService'
import inscriptionService from '../../services/inscriptionService'
import api from '../../services/api'

function mountComponent() {
  return mount(Panier, {
    global: {
      stubs: {
        'router-link': {
          template: '<a><slot /></a>',
        },
      },
    },
  })
}

describe('Panier.vue - membership', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    cartStoreMock.inscriptions = [
      {
        type_article: 'membership',
        tarif: 0,
        tarif_base: 25,
        formulaire_membership: {
          id_invitation: 77,
          nom: 'Dupont',
          prenom: 'Alice',
        },
        participant: [{ id: 12, prenom: 'Alice', nom: 'Dupont' }],
        courseDetails: {
          id: 0,
          nom_course: 'Inscription à Membership',
          evenement: { nom: 'Membership', couleur_primaire: '#0e0f54' },
          tarif: 0,
        },
      },
    ]
  })

  test('procederPaiement appelle checkoutMembership pour un article membership', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    wrapper.vm.accepteConditions = true

    await wrapper.vm.procederPaiement()

    expect(membershipService.checkoutMembership).toHaveBeenCalledTimes(1)
    expect(membershipService.checkoutMembership).toHaveBeenCalledWith({
      formulaire: {
        id_invitation: 77,
        nom: 'Dupont',
        prenom: 'Alice',
      },
      invitation_id: 77,
      prix: 25,
    })
    expect(inscriptionService.createInscription).not.toHaveBeenCalled()
    expect(cartStoreMock.viderPanier).toHaveBeenCalled()
    expect(api.post).not.toHaveBeenCalled()
    expect(wrapper.vm.isProcessing).toBe(false)
  })
})