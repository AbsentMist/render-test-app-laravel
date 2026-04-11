import { describe, test, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

vi.mock('../../services/courseParticipantService', () => ({
  default: {
    getAllCourses: vi.fn(),
  },
}))

vi.mock('../../components/MiniatureCourse.vue', () => ({
  default: {
    name: 'MiniatureCourse',
    props: ['courses', 'evenement', 'mode'],
    emits: ['selectionner'],
    template: `
      <div data-test="miniature-course">
        <button data-test="emit-selection" @click="$emit('selectionner', { id: 999, nom_course: 'Choisie' })">Emit</button>
      </div>
    `,
  },
}))

import ChangementCourse from '../../components/ChangementCourse.vue'
import courseParticipantService from '../../services/courseParticipantService'

const baseEvenement = {
  id: 77,
  nom: 'Run Geneva',
}

const coursesFixtures = [
  { id: 1, nom_course: 'Trail 10K' },
  { id: 2, nom_course: 'Semi Marathon' },
  { id: 3, nom_course: 'Course enfants' },
]

function mountComponent(customProps = {}) {
  return mount(ChangementCourse, {
    props: {
      evenement: baseEvenement,
      ...customProps,
    },
  })
}

describe('ChangementCourse', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    courseParticipantService.getAllCourses.mockResolvedValue({
      data: { courses: coursesFixtures },
    })
  })

  // Charge les courses au montage puis alimente MiniatureCourse
  test('charge les courses au montage', async () => {
    const wrapper = mountComponent()

    expect(wrapper.text()).toContain('Chargement des courses...')

    await flushPromises()

    expect(courseParticipantService.getAllCourses).toHaveBeenCalledWith(77)
    const miniature = wrapper.findComponent({ name: 'MiniatureCourse' })
    expect(miniature.exists()).toBe(true)
    expect(miniature.props('courses')).toEqual(coursesFixtures)
    expect(miniature.props('evenement')).toEqual(baseEvenement)
    expect(miniature.props('mode')).toBe('selection')
  })

  // Filtre les courses selon la recherche utilisateur
  test('filtre les courses avec le champ recherche', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    const input = wrapper.find('input[placeholder="Rechercher une course"]')
    await input.setValue('semi')

    const miniature = wrapper.findComponent({ name: 'MiniatureCourse' })
    expect(miniature.props('courses')).toEqual([{ id: 2, nom_course: 'Semi Marathon' }])
  })

  // Propage levenement selectionner emis par MiniatureCourse
  test('reemet selectionner depuis MiniatureCourse', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    await wrapper.find('[data-test="emit-selection"]').trigger('click')

    expect(wrapper.emitted('selectionner')).toBeTruthy()
    expect(wrapper.emitted('selectionner')[0][0]).toEqual({ id: 999, nom_course: 'Choisie' })
  })

  // Termine le chargement meme en cas derreur API
  test('gere une erreur de chargement et masque letat loading', async () => {
    const errorSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
    courseParticipantService.getAllCourses.mockRejectedValueOnce(new Error('API down'))

    const wrapper = mountComponent()
    await flushPromises()

    expect(errorSpy).toHaveBeenCalled()
    expect(wrapper.text()).not.toContain('Chargement des courses...')

    const miniature = wrapper.findComponent({ name: 'MiniatureCourse' })
    expect(miniature.props('courses')).toEqual([])
  })
})
