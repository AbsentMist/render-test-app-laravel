import { describe, test, expect } from 'vitest'
import { mount } from '@vue/test-utils'

import EtapeQuestionnaire from '../../components/EtapeQuestionnaire.vue'

const questionsFixtures = [
  {
    id: 1,
    question: 'Taille T-shirt ? ',
    answers: [
      { id: 11, texte: 'S' },
      { id: 12, texte: 'M' },
    ],
  },
  {
    id: 2,
    question: 'Repas vegetarien ? ',
    answers: [
      { id: 21, texte: 'Oui' },
      { id: 22, texte: 'Non' },
    ],
  },
]

function mountComponent(customProps = {}) {
  return mount(EtapeQuestionnaire, {
    props: {
      questions: questionsFixtures,
      modelValue: {},
      ...customProps,
    },
  })
}

describe('EtapeQuestionnaire', () => {
  // Initialise la structure des reponses via watcher immediate
  test('watch questions initialise reponses a null', () => {
    const wrapper = mountComponent()

    expect(wrapper.vm.reponses).toEqual({
      1: null,
      2: null,
    })
  })

  // Reinitialise la structure quand les questions changent
  test('watch questions met a jour reponses apres setProps', async () => {
    const wrapper = mountComponent()

    await wrapper.setProps({
      questions: [
        {
          id: 9,
          question: 'Nouvelle question',
          answers: [{ id: 90, texte: 'A' }],
        },
      ],
    })

    expect(wrapper.vm.reponses).toEqual({ 9: null })
  })

  // Emet toutes les reponses structurees
  test('mettreAJour emet resultat structure pour chaque question', () => {
    const wrapper = mountComponent()

    wrapper.vm.reponses[1] = { id: 12, texte: 'M' }
    wrapper.vm.reponses[2] = { id: 21, texte: 'Oui' }

    wrapper.vm.mettreAJour()

    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    expect(wrapper.emitted('update:modelValue').at(-1)[0]).toEqual({
      1: {
        question: 'Taille T-shirt ? ',
        reponse: { id: 12, texte: 'M' },
      },
      2: {
        question: 'Repas vegetarien ? ',
        reponse: { id: 21, texte: 'Oui' },
      },
    })
  })

  // Rend l etat vide si aucune question
  test('affiche etat vide si questions absentes', () => {
    const wrapper = mountComponent({ questions: [] })

    expect(wrapper.text()).toContain('Aucune question pour cette course.')
  })

  // Changement radio declenche emission
  test('interaction radio declenche mettreAJour et emission', async () => {
    const wrapper = mountComponent()

    const radios = wrapper.findAll('input[type="radio"]')
    expect(radios.length).toBeGreaterThan(0)

    await radios[1].setValue()

    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    const payload = wrapper.emitted('update:modelValue').at(-1)[0]
    expect(payload[1].reponse).toEqual({ id: 12, texte: 'M' })
  })
})
