import { describe, test, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: vi.fn(),
  }),
}))

vi.mock('../../services/evenementParticipantService', () => ({
  default: {
    getAllEvenements: vi.fn(),
  },
}))

vi.mock('../../components/Title.vue', () => ({
  default: {
    name: 'Title',
    props: ['texte'],
    template: '<h1>{{ texte }}</h1>',
  },
}))

vi.mock('../../components/MiniatureEvenement.vue', () => ({
  default: {
    name: 'MiniatureEvenement',
    props: ['evenements'],
    template: '<div data-test="miniature-evenement">{{ evenements.length }}</div>',
  },
}))

import Evenements from '../../views/Evenements.vue'
import evenementParticipantService from '../../services/evenementParticipantService'

const mockEvenements = [
  {
    id: 1,
    nom: 'Nocturne des Evaux',
    couleur_primaire: '#123456',
    couleur_secondaire: '#abcdef',
    logo_base64: 'data:image/png;base64,QUJD',
  },
  {
    id: 2,
    nom: 'Course de Printemps',
    couleur_primaire: '#654321',
    couleur_secondaire: '#fedcba',
    logo_base64: null,
  },
]

function mountComponent() {
  return mount(Evenements)
}

describe('Evenements', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  // Affiche le chargement puis la liste des évènements
  test('charge les evenements et affiche la grille', async () => {
    evenementParticipantService.getAllEvenements.mockResolvedValue({
      data: { data: mockEvenements },
    })

    const wrapper = mountComponent()

    expect(wrapper.text()).toContain('Chargement des évènements...')

    await flushPromises()

    expect(wrapper.text()).toContain('Liste des évènements')
    expect(wrapper.find('[data-test="miniature-evenement"]').exists()).toBe(true)

    const miniature = wrapper.findComponent({ name: 'MiniatureEvenement' })
    expect(miniature.props('evenements')).toHaveLength(2)
    expect(miniature.props('evenements')[0].logo_base64).toBe('ABC')
  })

  // Garde la page stable si le chargement echoue
  test('affiche une grille vide si le chargement echoue', async () => {
    evenementParticipantService.getAllEvenements.mockRejectedValue(new Error('Erreur reseau'))
    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})

    const wrapper = mountComponent()
    await flushPromises()

    expect(wrapper.text()).not.toContain('Chargement des évènements...')
    expect(wrapper.find('[data-test="miniature-evenement"]').exists()).toBe(true)
    expect(wrapper.findComponent({ name: 'MiniatureEvenement' }).props('evenements')).toHaveLength(0)
    expect(consoleSpy).toHaveBeenCalled()

    consoleSpy.mockRestore()
  })
})