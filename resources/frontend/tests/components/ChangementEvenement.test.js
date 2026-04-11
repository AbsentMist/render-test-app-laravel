import { describe, test, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

vi.mock('../../services/evenementParticipantService', () => ({
  default: {
    getAllEvenements: vi.fn(),
  },
}))

vi.mock('../../components/MiniatureEvenement.vue', () => ({
  default: {
    name: 'MiniatureEvenement',
    props: ['evenements', 'mode'],
    emits: ['selectionner'],
    template: `
      <div data-test="miniature-evenement">
        <button data-test="emit-selection" @click="$emit('selectionner', { id: 12, nom: 'Run Geneva' })">Emit</button>
      </div>
    `,
  },
}))

import ChangementEvenement from '../../components/ChangementEvenement.vue'
import evenementParticipantService from '../../services/evenementParticipantService'

const evenementsFixtures = [
  {
    id: 1,
    nom: 'Run Geneva',
    logo_base64: 'data:image/png;base64,QQ==',
  },
  {
    id: 2,
    nom: 'Trail Lausanne',
    logo_base64: null,
  },
]

function mountComponent() {
  return mount(ChangementEvenement)
}

describe('ChangementEvenement', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    evenementParticipantService.getAllEvenements.mockResolvedValue({
      data: evenementsFixtures,
    })
  })

  // Charge et normalise les evenements au montage
  test('charge les evenements au montage', async () => {
    const wrapper = mountComponent()

    expect(wrapper.text()).toContain('Chargement des évènements...')

    await flushPromises()

    expect(evenementParticipantService.getAllEvenements).toHaveBeenCalledTimes(1)

    const miniature = wrapper.findComponent({ name: 'MiniatureEvenement' })
    expect(miniature.exists()).toBe(true)
    expect(miniature.props('mode')).toBe('selection')
    expect(miniature.props('evenements')).toEqual([
      {
        id: 1,
        nom: 'Run Geneva',
        logo_base64: 'A',
      },
      {
        id: 2,
        nom: 'Trail Lausanne',
        logo_base64: null,
      },
    ])
  })

  // Propage selectionner emis par MiniatureEvenement
  test('reemet selectionner depuis MiniatureEvenement', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    await wrapper.find('[data-test="emit-selection"]').trigger('click')

    expect(wrapper.emitted('selectionner')).toBeTruthy()
    expect(wrapper.emitted('selectionner')[0][0]).toEqual({ id: 12, nom: 'Run Geneva' })
  })

  // Garde un rendu stable en cas derreur API
  test('gere erreur API et sort du mode chargement', async () => {
    const errorSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
    evenementParticipantService.getAllEvenements.mockRejectedValueOnce(new Error('API down'))

    const wrapper = mountComponent()
    await flushPromises()

    expect(errorSpy).toHaveBeenCalled()
    expect(wrapper.text()).not.toContain('Chargement des évènements...')

    const miniature = wrapper.findComponent({ name: 'MiniatureEvenement' })
    expect(miniature.exists()).toBe(true)
    expect(miniature.props('evenements')).toEqual([])
  })
})
