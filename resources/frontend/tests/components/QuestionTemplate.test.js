import { describe, test, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    props: ['icon', 'width', 'height'],
    template: '<span data-test="icon"></span>',
  },
}))

import QuestionTemplate from '../../components/QuestionTemplate.vue'

const baseQuestionModel = {
  enonce: 'Quelle est votre taille de t-shirt ?',
  choix: [
    { texte_option: 'S' },
    { texte_option: 'M' },
  ],
}

function mountComponent(customProps = {}) {
  return mount(QuestionTemplate, {
    props: {
      questionModel: JSON.parse(JSON.stringify(baseQuestionModel)),
      ...customProps,
    },
  })
}

describe('QuestionTemplate', () => {
  // Rend l enonce et les reponses existantes
  test('affiche enonce et liste des choix', () => {
    const wrapper = mountComponent()

    expect(wrapper.text()).toContain('Question')
    expect(wrapper.text()).toContain('Réponses')

    const radios = wrapper.findAll('input[type="radio"]')
    const reponseInputs = wrapper.findAll('input[placeholder="Réponse..."]')

    expect(radios).toHaveLength(2)
    expect(reponseInputs).toHaveLength(2)
  })

  // Emet remove-question au clic suppression question
  test('removeQuestion emet remove-question avec questionModel', async () => {
    const wrapper = mountComponent()

    const removeQuestionBtn = wrapper.findAll('button').find((b) => b.find('[data-test="icon"]').exists())
    expect(removeQuestionBtn).toBeTruthy()

    await removeQuestionBtn.trigger('click')

    expect(wrapper.emitted('remove-question')).toBeTruthy()
    expect(wrapper.emitted('remove-question')[0][0]).toEqual(wrapper.props('questionModel'))
  })

  // Ajoute une reponse vide au clic du bouton
  test('ajoute une reponse avec le bouton Ajouter une réponse', async () => {
    const wrapper = mountComponent()

    const addBtn = wrapper.findAll('button').find((b) => b.text().includes('Ajouter une réponse'))
    await addBtn.trigger('click')

    expect(wrapper.props('questionModel').choix).toHaveLength(3)
    expect(wrapper.props('questionModel').choix[2]).toEqual({ texte_option: '' })
  })

  // Supprime une reponse via le bouton remove de ligne
  test('supprime une reponse avec le bouton de ligne', async () => {
    const wrapper = mountComponent()

    const removeRowButtons = wrapper.findAll('button').filter((b) => b.find('[data-test="icon"]').exists())
    const removeFirstChoiceBtn = removeRowButtons[1]
    await removeFirstChoiceBtn.trigger('click')

    expect(wrapper.props('questionModel').choix).toHaveLength(1)
    expect(wrapper.props('questionModel').choix[0].texte_option).toBe('M')
  })

  // Met a jour enonce et texte des choix via v-model
  test('met a jour enonce et choix via v-model', async () => {
    const wrapper = mountComponent()

    const enonceInput = wrapper.find('input[placeholder="Énoncé de la question..."]')
    await enonceInput.setValue('Nouvelle question ?')

    const reponseInputs = wrapper.findAll('input[placeholder="Réponse..."]')
    await reponseInputs[0].setValue('Oui')
    await reponseInputs[1].setValue('Non')

    const model = wrapper.props('questionModel')
    expect(model.enonce).toBe('Nouvelle question ?')
    expect(model.choix[0].texte_option).toBe('Oui')
    expect(model.choix[1].texte_option).toBe('Non')
  })
})
