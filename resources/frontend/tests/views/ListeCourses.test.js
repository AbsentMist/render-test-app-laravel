import { vi } from 'vitest'

// Mock localStorage AVANT tout import
vi.stubGlobal('localStorage', {
  getItem: vi.fn(() => null),
  setItem: vi.fn(),
  removeItem: vi.fn(),
  clear: vi.fn(),
})

// Mock des modules AVANT les imports
vi.mock('../../services/courseParticipantService', () => ({
  default: { getAllCourses: vi.fn() }
}))
vi.mock('../../components/MiniatureCourse.vue', () => ({
  default: { template: '<div data-testid="miniature-course" />', props: ['courses', 'evenement'], name: 'MiniatureCourse' }
}))
vi.mock('../../components/PopupInscriptionCourse.vue', () => ({
  default: { template: '<div data-testid="popup-inscription" />', props: ['course', 'participants', 'couleurPrimaire'] }
}))
vi.mock('../../components/Title.vue', () => ({
  default: { template: '<h1>{{ texte }}</h1>', props: ['texte', 'couleur'] }
}))

import { describe, test, expect, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import ListeCourses from '../../views/ListeCourses.vue'
import courseParticipantService from '../../services/courseParticipantService'

const mockEvenement = {
  nom: 'Nocturne des Evaux',
  couleur_primaire: '#123456',
  couleur_secondaire: '#654321',
}

const mockCourses = [
  { id: 1, nom_course: '5km Populaire' },
  { id: 2, nom_course: '10km Élite' },
]

async function createWrapper() {
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [{ path: '/evenements/:idEvenement', component: ListeCourses }]
  })
  await router.push('/evenements/42')
  await router.isReady()

  return mount(ListeCourses, {
    global: {
      plugins: [createPinia(), router],
    }
  })
}

describe('VueEvenement', () => {

  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  test('affiche le chargement puis les courses', async () => {
    courseParticipantService.getAllCourses.mockResolvedValue({
      data: { evenement: mockEvenement, courses: mockCourses }
    })
    const wrapper = await createWrapper()
    expect(wrapper.text()).toContain('Chargement des courses...')
    await flushPromises()
    expect(wrapper.text()).not.toContain('Chargement des courses...')
    expect(wrapper.find('[data-testid="miniature-course"]').exists()).toBe(true)
  })

  test('filtre les courses selon la recherche', async () => {
    courseParticipantService.getAllCourses.mockResolvedValue({
      data: { evenement: mockEvenement, courses: mockCourses }
    })
    const wrapper = await createWrapper()
    await flushPromises()
    await wrapper.find('input').setValue('5km')
    const miniature = wrapper.findComponent({ name: 'MiniatureCourse' })
    expect(miniature.props('courses')).toHaveLength(1)
    expect(miniature.props('courses')[0].nom_course).toBe('5km Populaire')
  })

  test('ouvre la popup au clic sur une course', async () => {
    courseParticipantService.getAllCourses.mockResolvedValue({
      data: { evenement: mockEvenement, courses: mockCourses }
    })
    const wrapper = await createWrapper()
    await flushPromises()
    expect(wrapper.find('[data-testid="popup-inscription"]').exists()).toBe(false)
    await wrapper.findComponent({ name: 'MiniatureCourse' }).vm.$emit('selectionner', mockCourses[0])
    expect(wrapper.find('[data-testid="popup-inscription"]').exists()).toBe(true)
  })

  test('gère une erreur de chargement sans crash', async () => {
    courseParticipantService.getAllCourses.mockRejectedValue(new Error('Erreur réseau'))
    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
    const wrapper = await createWrapper()
    await flushPromises()
    expect(wrapper.text()).not.toContain('Chargement des courses...')
    expect(consoleSpy).toHaveBeenCalled()
  })

})