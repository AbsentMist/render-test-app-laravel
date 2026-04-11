import { describe, test, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    props: ['icon'],
    template: '<span data-test="icon"></span>',
  },
}))

import EtapeParametre from '../../components/EtapeParametre.vue'

const baseCourse = {
  type: 'Individuel',
  is_challenge: false,
}

function mountComponent(customProps = {}) {
  return mount(EtapeParametre, {
    props: {
      course: baseCourse,
      modelValue: null,
      ...customProps,
    },
  })
}

describe('EtapeParametre', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  // Calcule les types pour une course individuelle simple
  test('typesCourse retourne individuel par defaut', () => {
    const wrapper = mountComponent({
      course: { type: 'Individuel', is_challenge: false },
    })

    expect(wrapper.vm.typesCourse.map((t) => t.id)).toEqual(['individuel'])
  })

  // Ajoute challenge quand la course le permet
  test('typesCourse inclut challenge si is_challenge', () => {
    const wrapper = mountComponent({
      course: { type: 'Individuel', is_challenge: true },
    })

    expect(wrapper.vm.typesCourse.map((t) => t.id)).toEqual(['individuel', 'challenge'])
  })

  // Type relais uniquement si course relais
  test('typesCourse retourne relais pour une course relais', () => {
    const wrapper = mountComponent({
      course: { type: 'Relais', is_challenge: false },
    })

    expect(wrapper.vm.typesCourse.map((t) => t.id)).toEqual(['relais'])
  })

  // Type groupe uniquement si course groupe
  test('typesCourse retourne groupe pour une course groupe', () => {
    const wrapper = mountComponent({
      course: { type: 'Groupe', is_challenge: false },
    })

    expect(wrapper.vm.typesCourse.map((t) => t.id)).toEqual(['groupe'])
  })

  // Getter/setter proxy typeSelectionne sur modelValue
  test('typeSelectionne proxy getter/setter emet update:modelValue', () => {
    const wrapper = mountComponent({
      modelValue: { id: 'individuel', nom: 'Individuel', icone: 'mdi:account-outline' },
    })

    expect(wrapper.vm.typeSelectionne.id).toBe('individuel')

    wrapper.vm.typeSelectionne = { id: 'challenge', nom: 'Challenge', icone: 'mdi:trophy-outline' }
    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    expect(wrapper.emitted('update:modelValue').at(-1)[0]).toEqual({
      id: 'challenge',
      nom: 'Challenge',
      icone: 'mdi:trophy-outline',
    })
  })

  // selectionner delegue au setter typeSelectionne
  test('selectionner emet update:modelValue avec le type choisi', () => {
    const wrapper = mountComponent()
    const type = { id: 'individuel', nom: 'Individuel', icone: 'mdi:account-outline' }

    wrapper.vm.selectionner(type)

    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    expect(wrapper.emitted('update:modelValue').at(-1)[0]).toEqual(type)
  })

  // Monte avec un seul type => preselection auto
  test('mounted preselectionne le type unique', () => {
    const wrapper = mountComponent({
      course: { type: 'Relais', is_challenge: false },
      modelValue: null,
    })

    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    expect(wrapper.emitted('update:modelValue')[0][0]).toEqual({
      id: 'relais',
      nom: 'Relais',
      icone: 'mdi:account-group-outline',
    })
  })

  // Monte sans selection => prend individuel comme defaut si present
  test('mounted choisit individuel par defaut quand disponible', () => {
    const wrapper = mountComponent({
      course: { type: 'Individuel', is_challenge: true },
      modelValue: null,
    })

    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    expect(wrapper.emitted('update:modelValue')[0][0].id).toBe('individuel')
  })

  // Affiche le texte d aide selon la selection
  test('rend le texte explicatif selon le typeSelectionne', async () => {
    const wrapper = mountComponent({
      modelValue: { id: 'challenge', nom: 'Challenge', icone: 'mdi:trophy-outline' },
    })

    expect(wrapper.text()).toContain('Le Challenge permet')

    await wrapper.setProps({ modelValue: { id: 'relais', nom: 'Relais', icone: 'mdi:account-group-outline' } })
    expect(wrapper.text()).toContain('Le Relais permet')

    await wrapper.setProps({ modelValue: { id: 'groupe', nom: 'Groupe', icone: 'mdi:account-group' } })
    expect(wrapper.text()).toContain('L\'inscription en Groupe')

    await wrapper.setProps({ modelValue: { id: 'individuel', nom: 'Individuel', icone: 'mdi:account-outline' } })
    expect(wrapper.text()).toContain('L\'inscription Individuelle')
  })
})
