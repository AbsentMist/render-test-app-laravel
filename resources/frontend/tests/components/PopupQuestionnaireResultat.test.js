import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    template: '<span data-test="icon"></span>',
  },
}))

vi.mock('../../services/reponseQuestionOrganisateurService', () => ({
  default: {
    getReponsesQuestion: vi.fn(),
  },
}))

vi.mock('../../services/courseOrganisateurService', () => ({
  default: {
    getCourse: vi.fn(),
  },
}))

import PopupQuestionnaireResultat from '../../components/PopupQuestionnaireResultat.vue'
import courseOrganisateurService from '../../services/courseOrganisateurService'
import reponseQuestionOrganisateurService from '../../services/reponseQuestionOrganisateurService'

const course = {
  id: 2,
  nom: '5km Populaire',
}

const courseComplete = {
  id: 2,
  nom: '5km Populaire',
  questionnaire: [
    {
      id: 9,
      question: 'Pourquoi participez-vous ?',
      answers: [
        { id: 31, texte: 'Sport' },
        { id: 32, texte: 'Ambiance' },
      ],
    },
    {
      id: 10,
      question: 'Comment nous avez-vous connus ?',
      answers: [
        { id: 41, texte: 'Réseaux sociaux' },
      ],
    },
  ],
}

function mountComponent(customProps = {}) {
  return mount(PopupQuestionnaireResultat, {
    props: {
      course,
      ...customProps,
    },
  })
}

describe('PopupQuestionnaireResultat', () => {
  beforeEach(() => {
    vi.clearAllMocks()

    vi.spyOn(window.URL, 'createObjectURL').mockReturnValue('blob:questionnaire')
    vi.spyOn(window.URL, 'revokeObjectURL').mockImplementation(() => {})

    courseOrganisateurService.getCourse.mockResolvedValue({
      data: courseComplete,
    })

    reponseQuestionOrganisateurService.getReponsesQuestion.mockImplementation(async (idQuestion) => {
      if (idQuestion === 9) {
        return {
          data: [
            { id_option_choisie: 31 },
            { id_option_choisie: 31 },
            { id_option_choisie: 32 },
            { option: { id: 32 } },
          ],
        }
      }

      if (idQuestion === 10) {
        return {
          data: [
            { id_option_choisie: 41 },
          ],
        }
      }

      return { data: [] }
    })
  })

  afterEach(() => {
    vi.restoreAllMocks()
  })

  // Charge les statistiques et affiche les totaux par réponse
  test('affiche les totaux de sélection par réponse', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    expect(courseOrganisateurService.getCourse).toHaveBeenCalledWith(2)
    expect(reponseQuestionOrganisateurService.getReponsesQuestion).toHaveBeenCalledWith(9)
    expect(reponseQuestionOrganisateurService.getReponsesQuestion).toHaveBeenCalledWith(10)
    expect(wrapper.text()).toContain('Résultats du questionnaire')
    expect(wrapper.text()).toContain('4 sélection')
    expect(wrapper.text()).toContain('2')
    expect(wrapper.text()).toContain('1')
    expect(wrapper.vm.getAnswerCount(9, 31)).toBe(2)
    expect(wrapper.vm.getAnswerCount(9, 32)).toBe(2)
    expect(wrapper.vm.getAnswerCount(10, 41)).toBe(1)
  })

  // Retourne zéro quand aucune reponse ne correspond
  test('getAnswerCount retourne zéro par défaut', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    expect(wrapper.vm.getAnswerCount(9, 999)).toBe(0)
    expect(wrapper.vm.getTotalSelections(10)).toBe(1)
  })

  // Exporte les resultats en CSV pour Excel
  test('exporterCsv telecharge un fichier csv', async () => {
    const clickSpy = vi.fn()
    const removeSpy = vi.fn()
    const originalCreateElement = document.createElement.bind(document)

    vi.spyOn(document, 'createElement').mockImplementation((tagName) => {
      const element = originalCreateElement(tagName)

      if (tagName === 'a') {
        element.click = clickSpy
        element.remove = removeSpy
      }

      return element
    })

    const wrapper = mountComponent()
    await flushPromises()

    const exportButton = wrapper.findAll('button').find((button) => button.text().includes('Export CSV'))
    expect(exportButton.exists()).toBe(true)

    await exportButton.trigger('click')

    expect(window.URL.createObjectURL).toHaveBeenCalledTimes(1)
    expect(clickSpy).toHaveBeenCalledTimes(1)
    expect(removeSpy).toHaveBeenCalledTimes(1)
    expect(window.URL.revokeObjectURL).toHaveBeenCalledWith('blob:questionnaire')
  })
})