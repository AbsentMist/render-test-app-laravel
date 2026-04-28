import { describe, test, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'

const initModalsMock = vi.fn()

vi.stubGlobal('initModals', initModalsMock)

vi.mock('../../components/Title.vue', () => ({
  default: {
    name: 'Title',
    props: ['texte'],
    template: '<h1>{{ texte }}</h1>',
  },
}))

vi.mock('../../components/FormulaireOnglet.vue', () => ({
  default: {
    name: 'FormulaireOnglet',
    props: ['formulaires', 'modelValue'],
    emits: ['update:modelValue'],
    template: `
      <div data-test="formulaire-onglet">
        <button
          v-for="formulaire in formulaires"
          :key="formulaire"
          :data-active="modelValue === formulaire"
          @click="$emit('update:modelValue', formulaire)"
        >
          {{ formulaire }}
        </button>
      </div>
    `,
  },
}))

vi.mock('../../components/FormulaireCourse.vue', () => ({
  default: {
    name: 'FormulaireCourse',
    template: '<div data-test="formulaire-course"></div>',
  },
}))

vi.mock('../../components/FormulaireOption.vue', () => ({
  default: {
    name: 'FormulaireOption',
    template: '<div data-test="formulaire-option"></div>',
  },
}))

vi.mock('../../components/FormulaireQuestion.vue', () => ({
  default: {
    name: 'FormulaireQuestion',
    template: '<div data-test="formulaire-question"></div>',
  },
}))

vi.mock('../../components/FormulaireEvenement.vue', () => ({
  default: {
    name: 'FormulaireEvenement',
    template: '<div data-test="formulaire-evenement"></div>',
  },
}))

vi.mock('../../components/FormulaireCategorie.vue', () => ({
  default: {
    name: 'FormulaireCategorie',
    template: '<div data-test="formulaire-categorie"></div>',
  },
}))

vi.mock('../../components/FormulaireAvertissement.vue', () => ({
  default: {
    name: 'FormulaireAvertissement',
    template: '<div data-test="formulaire-avertissement"></div>',
  },
}))

import OrganisateurFormulaires from '../../views/OrganisateurFormulaires.vue'

function mountComponent(routeQuery = {}) {
  return mount(OrganisateurFormulaires, {
    global: {
      mocks: {
        $route: { query: routeQuery },
      },
    },
  })
}

describe('OrganisateurFormulaires', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  // Charge l onglet Course par defaut et initialise les modales
  test('affiche le formulaire course par defaut', () => {
    const wrapper = mountComponent()

    expect(wrapper.text()).toContain('Formulaires')
    expect(wrapper.find('[data-test="formulaire-course"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="formulaire-option"]').exists()).toBe(false)
    expect(initModalsMock).toHaveBeenCalledTimes(1)
  })

  // Ouvre directement l onglet demande par l URL
  test('selectionne un onglet depuis la query', () => {
    const wrapper = mountComponent({ onglet: 'Evènement' })

    expect(wrapper.find('[data-test="formulaire-evenement"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="formulaire-course"]').exists()).toBe(false)
  })

  // Change le formulaire actif via le v-model de FormulaireOnglet
  test('change de formulaire via FormulaireOnglet', async () => {
    const wrapper = mountComponent()

    await wrapper.findAll('[data-test="formulaire-onglet"] button').find((button) => button.text() === 'Options').trigger('click')

    expect(wrapper.find('[data-test="formulaire-option"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="formulaire-course"]').exists()).toBe(false)

    await wrapper.findAll('[data-test="formulaire-onglet"] button').find((button) => button.text() === 'Catégorie').trigger('click')

    expect(wrapper.find('[data-test="formulaire-categorie"]').exists()).toBe(true)

    await wrapper.findAll('[data-test="formulaire-onglet"] button').find((button) => button.text() === 'Questionnaire').trigger('click')

    expect(wrapper.find('[data-test="formulaire-question"]').exists()).toBe(true)
  })

  // Maintient les autres sous-formulaires inactifs tant que leur onglet n est pas selectionne
  test('n affiche qu un sous formulaire a la fois', async () => {
    const wrapper = mountComponent()

    expect(wrapper.find('[data-test="formulaire-course"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="formulaire-option"]').exists()).toBe(false)
    expect(wrapper.find('[data-test="formulaire-question"]').exists()).toBe(false)
    expect(wrapper.find('[data-test="formulaire-evenement"]').exists()).toBe(false)
    expect(wrapper.find('[data-test="formulaire-categorie"]').exists()).toBe(false)
    expect(wrapper.find('[data-test="formulaire-avertissement"]').exists()).toBe(false)

    await wrapper.findAll('[data-test="formulaire-onglet"] button').find((button) => button.text() === 'Avertissement').trigger('click')

    expect(wrapper.find('[data-test="formulaire-avertissement"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="formulaire-course"]').exists()).toBe(false)
  })
})