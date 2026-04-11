import { describe, test, expect } from 'vitest'
import { mount } from '@vue/test-utils'

import IndicateurEtapes from '../../components/IndicateurEtapes.vue'

const baseSteps = ['Participant', 'Options', 'Documents', 'Validation']

function mountComponent(customProps = {}) {
  return mount(IndicateurEtapes, {
    props: {
      steps: baseSteps,
      currentStep: 2,
      ...customProps,
    },
  })
}

describe('IndicateurEtapes', () => {
  // Affiche un jalon par etape
  test('rend le bon nombre de jalons', () => {
    const wrapper = mountComponent()

    const stepItems = wrapper.findAll('.relative.flex.flex-col.items-center.flex-1')
    expect(stepItems).toHaveLength(4)
  })

  // Affiche les labels des etapes
  test('affiche tous les labels fournis', () => {
    const wrapper = mountComponent()

    for (const step of baseSteps) {
      expect(wrapper.text()).toContain(step)
    }
  })

  // Marque letape precedente comme terminee (icone check)
  test('affiche une coche pour les etapes terminees', () => {
    const wrapper = mountComponent({ currentStep: 3 })

    const checks = wrapper.findAll('svg')
    expect(checks.length).toBeGreaterThanOrEqual(2)
  })

  // Marque letape active avec le point accent
  test('affiche le point accent pour letape en cours', () => {
    const wrapper = mountComponent({ currentStep: 2 })

    const activeDot = wrapper.find('.w-3.h-3.rounded-full.bg-accent')
    expect(activeDot.exists()).toBe(true)
  })

  // Affiche le numero pour les etapes futures
  test('affiche les numeros pour les etapes futures', () => {
    const wrapper = mountComponent({ currentStep: 1 })

    expect(wrapper.text()).toContain('2')
    expect(wrapper.text()).toContain('3')
    expect(wrapper.text()).toContain('4')
  })

  // Colore les labels actifs en primaire
  test('applique la classe text-primary aux etapes atteintes', () => {
    const wrapper = mountComponent({ currentStep: 3 })

    const labels = wrapper.findAll('span.text-xs.font-medium.uppercase.tracking-wider')
    expect(labels[0].classes()).toContain('text-primary')
    expect(labels[1].classes()).toContain('text-primary')
    expect(labels[2].classes()).toContain('text-primary')
    expect(labels[3].classes()).toContain('text-gray-400')
  })
})
