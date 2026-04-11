import { describe, test, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    props: ['icon', 'width', 'height'],
    template: '<span data-test="icon"></span>',
  },
}))

import OptionTemplate from '../../components/OptionTemplate.vue'

const baseOptionModel = {
  type: 'Cochable',
  nom: 'Repas',
  description: 'Menu coureur',
  tarif: 10,
  quantifiable: {
    quantiteMin: 1,
    quantiteMax: 3,
  },
}

function mountComponent(customProps = {}) {
  return mount(OptionTemplate, {
    props: {
      optionModel: JSON.parse(JSON.stringify(baseOptionModel)),
      border: true,
      removeButton: false,
      ...customProps,
    },
  })
}

describe('OptionTemplate', () => {
  // Rend les champs principaux et le style de bordure
  test('affiche les champs de base avec bordure', () => {
    const wrapper = mountComponent()

    expect(wrapper.text()).toContain("Type d'option")
    expect(wrapper.text()).toContain("Nom de l'option")
    expect(wrapper.text()).toContain('Description')
    expect(wrapper.text()).toContain('Tarif (CHF)')
    expect(wrapper.classes()).toContain('border')
  })

  // Cache la bordure quand la prop border est false
  test('retire la bordure si border est false', () => {
    const wrapper = mountComponent({ border: false })

    expect(wrapper.classes()).not.toContain('border')
  })

  // Emet remove-option au clic sur le bouton de suppression
  test('removeOption emet remove-option avec optionModel', async () => {
    const wrapper = mountComponent({ removeButton: false })

    const removeBtn = wrapper.findAll('button').find((b) => b.find('[data-test="icon"]').exists())
    expect(removeBtn).toBeTruthy()

    await removeBtn.trigger('click')

    expect(wrapper.emitted('remove-option')).toBeTruthy()
    expect(wrapper.emitted('remove-option')[0][0]).toEqual(wrapper.props('optionModel'))
  })

  // Masque le bouton de suppression quand removeButton est true
  test('n affiche pas le bouton remove quand removeButton est true', () => {
    const wrapper = mountComponent({ removeButton: true })

    const buttons = wrapper.findAll('button')
    expect(buttons).toHaveLength(2)
    expect(buttons[0].text()).toContain('Quantité (Quantifiable)')
    expect(buttons[1].text()).toContain('Simple (Cochable)')
  })

  // Passe en mode Quantifiable et affiche les quantites
  test('selection du type Quantifiable affiche qte min/max', async () => {
    const wrapper = mountComponent()

    const quantifiableBtn = wrapper.findAll('button').find((b) => b.text().includes('Quantité (Quantifiable)'))
    await quantifiableBtn.trigger('click')

    expect(wrapper.props('optionModel').type).toBe('Quantifiable')
    expect(wrapper.text()).toContain('Qté min')
    expect(wrapper.text()).toContain('Qté max')
  })

  // Met a jour les champs via v-model
  test('met a jour nom description tarif et quantites via v-model', async () => {
    const wrapper = mountComponent({
      optionModel: {
        ...baseOptionModel,
        type: 'Quantifiable',
      },
    })

    const inputs = wrapper.findAll('input')
    await inputs[0].setValue('Option VIP')
    await wrapper.find('textarea').setValue('Description VIP')
    await inputs[1].setValue('25')
    await inputs[2].setValue('2')
    await inputs[3].setValue('8')

    const model = wrapper.props('optionModel')
    expect(model.nom).toBe('Option VIP')
    expect(model.description).toBe('Description VIP')
    expect(model.tarif).toBe(25)
    expect(model.quantifiable.quantiteMin).toBe(2)
    expect(model.quantifiable.quantiteMax).toBe(8)
  })
})
