import { describe, test, expect } from 'vitest'
import { mount } from '@vue/test-utils'

import Footer from '../../components/Footer.vue'

const RouterLinkStub = {
  name: 'RouterLink',
  props: ['to'],
  template: '<a :data-to="to"><slot /></a>',
}

function mountComponent() {
  return mount(Footer, {
    global: {
      stubs: {
        RouterLink: RouterLinkStub,
      },
    },
  })
}

describe('Footer', () => {
  // Affiche la mention de copyright
  test('affiche le texte principal', () => {
    const wrapper = mountComponent()

    expect(wrapper.text()).toContain('2025 @ Runningeneva Association')
  })

  // Rend les trois liens de navigation attendus
  test('rend trois liens de navigation', () => {
    const wrapper = mountComponent()

    const links = wrapper.findAll('a')
    expect(links).toHaveLength(3)
    expect(links[0].attributes('data-to')).toBe('/conditions-utilisation')
    expect(links[1].attributes('data-to')).toBe('/politique-confidentialite')
    expect(links[2].attributes('data-to')).toBe('/protection-donnees')
  })

  // Affiche les libelles des trois liens
  test('affiche les libelles attendus', () => {
    const wrapper = mountComponent()

    expect(wrapper.text()).toContain("Condition d'utilisation")
    expect(wrapper.text()).toContain('Politique de confidentialité')
    expect(wrapper.text()).toContain('Politique de protection des données')
  })

  // Conserve la structure de footer
  test('rend un element footer racine', () => {
    const wrapper = mountComponent()

    expect(wrapper.find('footer').exists()).toBe(true)
    expect(wrapper.find('footer').classes()).toContain('w-full')
  })
})
