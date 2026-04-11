import { describe, test, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    props: ['icon', 'width', 'height'],
    template: '<span data-test="icon" :data-icon="icon"></span>',
  },
}))

import PopupConfirmation from '../../components/PopupConfirmation.vue'

function mountComponent(customProps = {}) {
  return mount(PopupConfirmation, {
    props: {
      ...customProps,
    },
  })
}

describe('PopupConfirmation', () => {
  // Affiche le message par defaut
  test('affiche le message par defaut', () => {
    const wrapper = mountComponent()

    expect(wrapper.text()).toContain('Êtes-vous sûr de vouloir créer cet évènement ?')
  })

  // Affiche le message passe en props
  test('affiche le message personnalise', () => {
    const wrapper = mountComponent({ message: 'Confirmer la suppression ?' })

    expect(wrapper.text()).toContain('Confirmer la suppression ?')
  })

  // Affiche l icone si fournie
  test('rend licone si la prop icon est definie', () => {
    const wrapper = mountComponent({ icon: 'mdi:alert' })

    const icon = wrapper.find('[data-test="icon"]')
    expect(icon.exists()).toBe(true)
    expect(icon.attributes('data-icon')).toBe('mdi:alert')
  })

  // Cache l icone si non fournie
  test('ne rend pas licone si la prop icon est absente', () => {
    const wrapper = mountComponent()

    expect(wrapper.find('[data-test="icon"]').exists()).toBe(false)
  })

  // Affiche les boutons de confirmation par defaut
  test('affiche les boutons quand showButtons est true', () => {
    const wrapper = mountComponent()

    const buttons = wrapper.findAll('button')
    expect(buttons).toHaveLength(2)
    expect(wrapper.text()).toContain('Continuer')
    expect(wrapper.text()).toContain('Annuler')
  })

  // Cache les boutons si showButtons vaut false
  test('cache les boutons quand showButtons est false', () => {
    const wrapper = mountComponent({ showButtons: false })

    expect(wrapper.findAll('button')).toHaveLength(0)
  })

  // Emet confirm sur clic continuer
  test('emet confirm au clic sur Continuer', async () => {
    const wrapper = mountComponent()

    await wrapper.findAll('button')[0].trigger('click')

    expect(wrapper.emitted('confirm')).toBeTruthy()
    expect(wrapper.emitted('confirm')).toHaveLength(1)
  })

  // Emet cancel sur clic annuler
  test('emet cancel au clic sur Annuler', async () => {
    const wrapper = mountComponent()

    await wrapper.findAll('button')[1].trigger('click')

    expect(wrapper.emitted('cancel')).toBeTruthy()
    expect(wrapper.emitted('cancel')).toHaveLength(1)
  })

  // Emet cancel au clic sur le backdrop
  test('emet cancel au clic sur le fond', async () => {
    const wrapper = mountComponent()

    const backdrop = wrapper
      .findAll('div')
      .find((node) => (node.attributes('class') || '').includes('bg-black/40'))

    expect(backdrop).toBeTruthy()
    await backdrop.trigger('click')

    expect(wrapper.emitted('cancel')).toBeTruthy()
    expect(wrapper.emitted('cancel')).toHaveLength(1)
  })
})
