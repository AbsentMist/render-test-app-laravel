import { describe, test, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

const routerMock = {
  push: vi.fn(),
}

vi.mock('vue-router', () => ({
  useRouter: () => routerMock,
}))

vi.mock('../../services/evenementParticipantService', () => ({
  default: { getAllEvenements: vi.fn() },
}))
vi.mock('../../services/participantService', () => ({
  default: { getMesParticipants: vi.fn() },
}))
vi.mock('../../services/groupeService', () => ({
  default: { getGroupes: vi.fn() },
}))
vi.mock('../../components/Title.vue', () => ({
  default: {
    name: 'Title',
    props: ['texte', 'couleur'],
    template: '<h1>{{ texte }}</h1>',
  },
}))
vi.mock('../../components/MiniatureEvenement.vue', () => ({
  default: {
    name: 'MiniatureEvenement',
    props: ['evenements'],
    template: '<div data-test="miniature-evenement"></div>',
  },
}))
vi.mock('../../components/PopupGestionGroupe.vue', () => ({
  default: {
    name: 'PopupGestionGroupe',
    props: ['groupe', 'mesParticipants'],
    template: '<div data-test="popup-gestion-groupe"></div>',
  },
}))

import ParticipantTableauDeBord from '../../views/ParticipantTableauDeBord.vue'
import evenementParticipantService from '../../services/evenementParticipantService'
import participantService from '../../services/participantService'
import groupeService from '../../services/groupeService'

const mockEvenements = [
  {
    id: 1,
    nom: 'Marathon de Printemps',
    couleur_primaire: '#111111',
    couleur_secondaire: '#eeeeee',
    logo_base64: null,
  },
  {
    id: 2,
    nom: 'Course d été',
    couleur_primaire: '#222222',
    couleur_secondaire: '#dddddd',
    logo_base64: null,
  },
]

const mockParticipants = [
  { id: 10, prenom: 'Alice', nom: 'Dupont' },
]

const mockGroupes = [
  {
    id: 21,
    nom: 'Team Flash',
    course: { type: 'Groupe' },
    participants: [{ id: 1 }, { id: 2 }],
  },
  {
    id: 22,
    nom: 'Challenge Rouge',
    course: { type: 'Challenge' },
    participants: [],
  },
]

function mountComponent() {
  return mount(ParticipantTableauDeBord)
}

describe('ParticipantTableauDeBord', () => {
  beforeEach(() => {
    vi.clearAllMocks()

    evenementParticipantService.getAllEvenements.mockResolvedValue({
      data: { data: mockEvenements },
    })
    participantService.getMesParticipants.mockResolvedValue({
      data: mockParticipants,
    })
    groupeService.getGroupes.mockResolvedValue({
      data: mockGroupes,
    })
  })

  // Charge les donnees et affiche les elements principaux du tableau de bord
  test('charge les donnees du tableau de bord', async () => {
    const wrapper = mountComponent()

    expect(wrapper.text()).toContain('Chargement des évènements...')

    await flushPromises()

    expect(wrapper.text()).toContain('Tableau de bord')
    expect(wrapper.text()).toContain('Alice Dupont')
    expect(wrapper.text()).toContain('Team Flash')
    expect(wrapper.text()).not.toContain('Challenge Rouge')
    expect(wrapper.find('[data-test="miniature-evenement"]').exists()).toBe(true)

    const miniature = wrapper.findComponent({ name: 'MiniatureEvenement' })
    expect(miniature.props('evenements')).toHaveLength(2)
  })

  // Permet de naviguer vers les pages principales
  test('navigue vers inscriptions et evenements', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    await wrapper.findAll('button').find((button) => button.text().includes('Mes inscriptions')).trigger('click')
    expect(routerMock.push).toHaveBeenCalledWith('/inscriptions')

    await wrapper.findAll('button').find((button) => button.text().includes('Mes résultats')).trigger('click')
    expect(routerMock.push).toHaveBeenCalledWith('/resultats')

    await wrapper.findAll('button').find((button) => button.text().includes('Voir plus')).trigger('click')
    expect(routerMock.push).toHaveBeenCalledWith('/evenements')
  })

  // Redirige vers la liste des courses de l evenement
  test('goToListeCourses envoie vers la route ListeCourses', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    wrapper.vm.goToListeCourses(42)

    expect(routerMock.push).toHaveBeenCalledWith({
      name: 'ListeCourses',
      params: { idEvenement: 42 },
    })
  })

  // Redirige vers les pages du participant selon l action demandee
  test('goToAllEvenements goToInscriptions et goToResultats utilisent les bonnes routes', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    wrapper.vm.goToAllEvenements()
    wrapper.vm.goToInscriptions()
    wrapper.vm.goToResultats()

    expect(routerMock.push).toHaveBeenCalledWith('/evenements')
    expect(routerMock.push).toHaveBeenCalledWith('/inscriptions')
    expect(routerMock.push).toHaveBeenCalledWith('/resultats')
  })

  // Ouvre la popup de gestion au clic sur un groupe
  test('ouvre la popup de gestion du groupe', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    await wrapper.findAll('button').find((button) => button.text().includes('Team Flash')).trigger('click')

    expect(wrapper.find('[data-test="popup-gestion-groupe"]').exists()).toBe(true)
    const popup = wrapper.findComponent({ name: 'PopupGestionGroupe' })
    expect(popup.props('groupe').id).toBe(21)
  })
})