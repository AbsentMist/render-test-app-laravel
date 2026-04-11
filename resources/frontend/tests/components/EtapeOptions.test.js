import { describe, test, expect } from 'vitest'
import { mount } from '@vue/test-utils'

import EtapeOptions from '../../components/EtapeOptions.vue'

const optionsFixtures = [
  {
    id: 1,
    nom: 'Repas',
    description: 'Menu apres course',
    type: 'Cochable',
    tarif: 10,
  },
  {
    id: 2,
    nom: 'Photo pack',
    description: 'Pack photos officiel',
    type: 'Quantifiable',
    tarif: 5,
    quantifiable: {
      quantite_min: 2,
      quantite_max: 4,
    },
  },
]

function mountComponent(customProps = {}) {
  return mount(EtapeOptions, {
    props: {
      options: optionsFixtures,
      modelValue: {},
      ...customProps,
    },
  })
}

describe('EtapeOptions', () => {
  // Initialise les options locales via le watcher immediate
  test('watch options initialise optionsSelectionnees', () => {
    const wrapper = mountComponent()

    expect(wrapper.vm.optionsSelectionnees[1]).toMatchObject({
      option: optionsFixtures[0],
      selectionne: false,
      quantite: 1,
    })
    expect(wrapper.vm.optionsSelectionnees[2]).toMatchObject({
      option: optionsFixtures[1],
      selectionne: false,
      quantite: 2,
    })
  })

  // Reinitialise l etat local quand la prop options change
  test('watch options reinitialise etat lors dun changement de props', async () => {
    const wrapper = mountComponent()
    wrapper.vm.optionsSelectionnees[1].selectionne = true

    await wrapper.setProps({
      options: [
        {
          id: 7,
          nom: 'Navette',
          type: 'Cochable',
          tarif: 3,
        },
      ],
    })

    expect(Object.keys(wrapper.vm.optionsSelectionnees)).toEqual(['7'])
    expect(wrapper.vm.optionsSelectionnees[7].selectionne).toBe(false)
  })

  // Genere les quantites disponibles selon min max
  test('quantitesDisponibles retourne la plage attendue', () => {
    const wrapper = mountComponent()

    expect(wrapper.vm.quantitesDisponibles(optionsFixtures[1])).toEqual([2, 3, 4])
    expect(wrapper.vm.quantitesDisponibles({})).toEqual([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
  })

  // Ajoute une option cochable et emet modelValue
  test('ajouterCochable active option et emet update:modelValue', () => {
    const wrapper = mountComponent()

    wrapper.vm.ajouterCochable(optionsFixtures[0])

    expect(wrapper.vm.optionsSelectionnees[1].selectionne).toBe(true)
    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    expect(wrapper.emitted('update:modelValue').at(-1)[0]).toEqual({
      1: {
        option: optionsFixtures[0],
        quantite: 1,
      },
    })
  })

  // Retire une option et met a jour le payload emis
  test('retirerCochable desactive option et met a jour emission', () => {
    const wrapper = mountComponent()
    wrapper.vm.ajouterCochable(optionsFixtures[0])

    wrapper.vm.retirerCochable(optionsFixtures[0])

    expect(wrapper.vm.optionsSelectionnees[1].selectionne).toBe(false)
    expect(wrapper.emitted('update:modelValue').at(-1)[0]).toEqual({})
  })

  // Ajoute une option quantifiable avec la quantite courante
  test('ajouterQuantifiable active option quantifiable et emet', () => {
    const wrapper = mountComponent()
    wrapper.vm.optionsSelectionnees[2].quantite = 4

    wrapper.vm.ajouterQuantifiable(optionsFixtures[1])

    expect(wrapper.vm.optionsSelectionnees[2].selectionne).toBe(true)
    expect(wrapper.emitted('update:modelValue').at(-1)[0]).toEqual({
      2: {
        option: optionsFixtures[1],
        quantite: 4,
      },
    })
  })

  // Met a jour le payload quand plusieurs options sont selectionnees
  test('mettreAJour emet toutes les options selectionnees', () => {
    const wrapper = mountComponent()

    wrapper.vm.optionsSelectionnees[1].selectionne = true
    wrapper.vm.optionsSelectionnees[2].selectionne = true
    wrapper.vm.optionsSelectionnees[2].quantite = 3

    wrapper.vm.mettreAJour()

    expect(wrapper.emitted('update:modelValue').at(-1)[0]).toEqual({
      1: {
        option: optionsFixtures[0],
        quantite: 1,
      },
      2: {
        option: optionsFixtures[1],
        quantite: 3,
      },
    })
  })

  // Calcule correctement le total options cochable + quantifiable
  test('totalOptions calcule le montant cumule', () => {
    const wrapper = mountComponent()

    wrapper.vm.optionsSelectionnees[1].selectionne = true
    wrapper.vm.optionsSelectionnees[2].selectionne = true
    wrapper.vm.optionsSelectionnees[2].quantite = 3

    expect(wrapper.vm.totalOptions).toBe(25)
  })

  // Retourne zero si aucune option selectionnee
  test('totalOptions vaut 0 sans selection', () => {
    const wrapper = mountComponent()

    expect(wrapper.vm.totalOptions).toBe(0)
  })

  // Affiche un message vide quand aucune option n est disponible
  test('affiche etat vide si options absentes', () => {
    const wrapper = mountComponent({ options: [] })

    expect(wrapper.text()).toContain('Aucune option disponible pour cette course.')
  })
})
