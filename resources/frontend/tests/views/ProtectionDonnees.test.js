import { describe, test, expect } from 'vitest'
import { mount } from '@vue/test-utils'

import ProtectionDonnees from '../../views/ProtectionDonnees.vue'

describe('ProtectionDonnees', () => {
  // Affiche le contenu principal de la politique de protection des données
  test('rend le titre et les blocs informatifs', () => {
    const wrapper = mount(ProtectionDonnees)

    expect(wrapper.text()).toContain('Politique de protection des données')
    expect(wrapper.text()).toContain('Sécurité et conservation des données')
    expect(wrapper.text()).toContain('Vos droits')
    expect(wrapper.find('a[href="mailto:inscription@runningeneva.ch"]').exists()).toBe(true)
  })
})