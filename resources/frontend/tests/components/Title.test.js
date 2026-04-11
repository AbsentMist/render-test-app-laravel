import { describe, test, expect } from 'vitest'
import { mount } from '@vue/test-utils'

import Title from '../../components/Title.vue'

function mountComponent(customProps = {}) {
  return mount(Title, {
    props: {
      texte: 'Inscription',
      ...customProps,
    },
  })
}

describe('Title', () => {
  // Rend le texte principal dans le h1
  test('affiche le texte passe en prop', () => {
    const wrapper = mountComponent({ texte: 'Mon titre' })

    expect(wrapper.find('h1').text()).toContain('Mon titre')
  })

  // Applique la couleur personnalisee a la barre
  test('applique la couleur personnalisee', () => {
    const wrapper = mountComponent({ couleur: '#123456' })

    const bar = wrapper.find('span')
    expect(bar.attributes('style')).toContain('background-color: #123456')
  })

  // Utilise la couleur par defaut si couleur non fournie
  test('utilise la couleur par defaut', () => {
    const wrapper = mountComponent()

    const bar = wrapper.find('span')
    expect(bar.attributes('style')).toContain('background-color: #fca5a5')
  })

  // Conserve les classes attendues de structure
  test('garde les classes de style attendues', () => {
    const wrapper = mountComponent()

    expect(wrapper.find('h1').classes()).toContain('text-title')
    expect(wrapper.find('h1').classes()).toContain('mb-6')
    expect(wrapper.find('span').classes()).toContain('w-32')
  })
})
