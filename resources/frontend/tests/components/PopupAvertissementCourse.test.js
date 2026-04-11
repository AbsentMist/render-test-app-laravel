import { describe, test, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    props: ['icon'],
    template: '<span data-test="icon" :data-icon="icon"></span>',
  },
}))

import PopupAvertissementCourse from '../../components/PopupAvertissementCourse.vue'

function mountComponent(customProps = {}) {
  return mount(PopupAvertissementCourse, {
    props: {
      ...customProps,
    },
  })
}

describe('PopupAvertissementCourse', () => {
  // Affiche le texte davertissement recu
  test('affiche le texte fourni', () => {
    const wrapper = mountComponent({ texte: 'Hydratation obligatoire avant depart.' })

    expect(wrapper.text()).toContain('Hydratation obligatoire avant depart.')
  })

  // Affiche les infos statiques de la popup
  test('affiche le titre et le message informatif', () => {
    const wrapper = mountComponent({ texte: 'Message test' })

    expect(wrapper.text()).toContain('Avant de continuer')
    expect(wrapper.text()).toContain('En continuant, vous reconnaissez avoir pris connaissance de cet avertissement.')
  })

  // Rend les deux icones attendues
  test('rend les icones dalerte et dinformation', () => {
    const wrapper = mountComponent({ texte: 'Message test' })

    const icons = wrapper.findAll('[data-test="icon"]')
    expect(icons).toHaveLength(2)
    expect(icons[0].attributes('data-icon')).toBe('mdi:alert-circle-outline')
    expect(icons[1].attributes('data-icon')).toBe('mdi:information-outline')
  })

  // Emet close au clic sur Quitter
  test('emet close au clic sur Quitter', async () => {
    const wrapper = mountComponent({ texte: 'Message test' })

    await wrapper.findAll('button')[0].trigger('click')

    expect(wrapper.emitted('close')).toBeTruthy()
    expect(wrapper.emitted('close')).toHaveLength(1)
  })

  // Emet confirmer au clic sur bouton continuer
  test('emet confirmer au clic sur J ai compris, continuer', async () => {
    const wrapper = mountComponent({ texte: 'Message test' })

    await wrapper.findAll('button')[1].trigger('click')

    expect(wrapper.emitted('confirmer')).toBeTruthy()
    expect(wrapper.emitted('confirmer')).toHaveLength(1)
  })

  // Gere labsence de texte sans erreur
  test('rend la popup meme sans texte', () => {
    const wrapper = mountComponent()

    expect(wrapper.exists()).toBe(true)
    expect(wrapper.text()).toContain('Avant de continuer')
  })
})
