import { describe, test, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

const routerMock = {
  push: vi.fn(),
}

vi.mock('vue-router', () => ({
  useRouter: () => routerMock,
}))

vi.mock('../../services/api.js', () => ({
  default: {
    get: vi.fn(),
    delete: vi.fn(),
  },
}))

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    template: '<span data-test="icon"></span>',
  },
}))

vi.mock('../../components/Title.vue', () => ({
  default: {
    name: 'Title',
    props: ['texte'],
    template: '<h1>{{ texte }}</h1>',
  },
}))

vi.mock('../../components/PopupConfirmation.vue', () => ({
  default: {
    name: 'PopupConfirmation',
    props: ['message', 'icon'],
    template: `
      <div data-test="popup-confirmation">
        <span>{{ message }}</span>
        <button data-test="confirm" @click="$emit('confirm')">Confirmer</button>
        <button data-test="cancel" @click="$emit('cancel')">Annuler</button>
      </div>
    `,
  },
}))

import OrganisateurEvenements from '../../views/OrganisateurEvenements.vue'
import api from '../../services/api.js'

const mockEvenements = [
  {
    id: 1,
    nom: 'Run 1',
    is_actif: true,
    is_interne: false,
    courses: [
      { debut_inscription: '2026-05-10', fin_inscription: '2026-05-20' },
      { debut_inscription: '2026-05-12', fin_inscription: '2026-05-25' },
    ],
  },
  {
    id: 2,
    nom: 'Run 2',
    is_actif: false,
    is_interne: true,
    courses: [],
  },
]

function mountComponent() {
  return mount(OrganisateurEvenements, {
    global: {
      mocks: {
        $router: routerMock,
      },
    },
  })
}

describe('OrganisateurEvenements', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    api.get.mockResolvedValue({ data: mockEvenements })
    api.delete.mockResolvedValue({})
  })

  // Charge les evenements et affiche le tableau
  test('charge les evenements organisateur', async () => {
    const wrapper = mountComponent()

    expect(wrapper.text()).toContain('Chargement des évènements...')

    await flushPromises()

    expect(wrapper.text()).toContain('Run 1')
    expect(wrapper.text()).toContain('Run 2')
    expect(api.get).toHaveBeenCalledWith('/organisateur/evenements')
  })

  // Formate correctement une date brute
  test('formaterDate retourne une date suisse', () => {
    const wrapper = mountComponent()

    expect(wrapper.vm.formaterDate('2026-05-10')).toBe('10.05.2026')
    expect(wrapper.vm.formaterDate(null)).toBe('—')
  })

  // Calcule la premiere date d inscription d un evenement
  test('getDateDebutEvenement calcule la plus petite date', () => {
    const wrapper = mountComponent()

    expect(wrapper.vm.getDateDebutEvenement(mockEvenements[0])).toBe('10.05.2026')
    expect(wrapper.vm.getDateDebutEvenement(mockEvenements[1])).toBe('—')
  })

  // Calcule la derniere date d inscription d un evenement
  test('getDateFinEvenement calcule la plus grande date', () => {
    const wrapper = mountComponent()

    expect(wrapper.vm.getDateFinEvenement(mockEvenements[0])).toBe('25.05.2026')
    expect(wrapper.vm.getDateFinEvenement(mockEvenements[1])).toBe('—')
  })

  // Redirige vers le formulaire de modification
  test('modifierEvenement ouvre le bon formulaire', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    wrapper.vm.modifierEvenement(mockEvenements[0])

    expect(routerMock.push).toHaveBeenCalledWith('/organisateur/formulaires?onglet=Evènement&id=1')
  })

  // Ouvre la confirmation de suppression
  test('confirmerSuppression selectionne un evenement', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    wrapper.vm.confirmerSuppression(mockEvenements[0])
    await flushPromises()

    expect(wrapper.vm.evenementASupprimer.id).toBe(1)
    expect(wrapper.find('[data-test="popup-confirmation"]').exists()).toBe(true)
  })

  // Supprime un evenement puis retire la ligne du tableau
  test('supprimerEvenement supprime l evenement confirme', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    wrapper.vm.confirmerSuppression(mockEvenements[0])
    await wrapper.vm.supprimerEvenement()

    expect(api.delete).toHaveBeenCalledWith('/organisateur/evenements/1')
    expect(wrapper.vm.evenements).toHaveLength(1)
    expect(wrapper.vm.evenements[0].id).toBe(2)
    expect(wrapper.vm.evenementASupprimer).toBeNull()
  })

  // Affiche un message si la suppression echoue
  test('supprimerEvenement affiche une erreur en cas d echec', async () => {
    api.delete.mockRejectedValue(new Error('Erreur reseau'))
    const wrapper = mountComponent()
    await flushPromises()

    wrapper.vm.confirmerSuppression(mockEvenements[0])
    await wrapper.vm.supprimerEvenement()

    expect(wrapper.vm.erreur).toBe('Impossible de supprimer cet évènement.')
    expect(wrapper.vm.evenementASupprimer).toBeNull()
  })

  // Affiche un message si le chargement echoue
  test('chargerEvenements affiche une erreur de chargement', async () => {
    api.get.mockRejectedValue(new Error('Erreur reseau'))
    const wrapper = mountComponent()
    await flushPromises()

    expect(wrapper.text()).toContain('Impossible de charger les évènements.')
  })

  // Clique sur une ligne et ouvre la page des courses de l evenement
  test('ouvre les courses de l evenement au clic sur une ligne', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    await wrapper.findAll('tr').at(1).trigger('click')

    expect(routerMock.push).toHaveBeenCalledWith('/organisateur/evenements/1/courses')
  })
})