import { describe, test, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    template: '<span data-test="icon"></span>',
  },
}))

import MiniatureCourse from '../../components/MiniatureCourse.vue'

const courses = [
  { id: 1, nom_course: 'Trail 10K', tarif: 40, dossards_restants: 120 },
  { id: 2, nom_course: 'Semi Marathon', tarif: 55, dossards_restants: 80 },
]

const evenement = {
  nom: 'Run Geneva',
  date: '2026-04-11',
  couleur_primaire: '#123456',
  couleur_secondaire: '#abcdef',
}

function mountComponent(customProps = {}) {
  return mount(MiniatureCourse, {
    props: {
      courses,
      evenement,
      mode: 'selection',
      ...customProps,
    },
  })
}

describe('MiniatureCourse', () => {
  // Rend les informations principales des courses
  test('affiche les cartes de courses avec infos tarif et dossards', () => {
    const wrapper = mountComponent()

    expect(wrapper.text()).toContain('Trail 10K')
    expect(wrapper.text()).toContain('Semi Marathon')
    expect(wrapper.text()).toContain('Tarif CHF 40')
    expect(wrapper.text()).toContain('Dossards restants : 120')
  })

  // Relaye la selection de course au parent
  test('emet selectionner au clic sur une carte', async () => {
    const wrapper = mountComponent()

    const cards = wrapper.findAll('div.cursor-pointer')
    expect(cards.length).toBeGreaterThan(0)
    await cards[0].trigger('click')

    expect(wrapper.emitted('selectionner')).toBeTruthy()
    expect(wrapper.emitted('selectionner')[0][0]).toEqual(courses[0])
  })

  // Formate la date evenement en fr-CH dans le badge
  test('affiche la date evenement formatee', () => {
    const wrapper = mountComponent()

    expect(wrapper.text()).toContain('2026')
  })

  // Gere evenement null sans date invalide ni crash
  test('gere evenement absent avec styles fallback', () => {
    const wrapper = mountComponent({ evenement: null })

    const firstCard = wrapper.find('div.cursor-pointer')
    expect(firstCard.exists()).toBe(true)
    expect(firstCard.attributes('style')).toContain('background-color: #ffffff')
    expect(wrapper.text()).not.toContain('Invalid Date')
  })
})
