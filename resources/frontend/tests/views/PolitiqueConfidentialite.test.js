import { describe, test, expect } from 'vitest'
import { mount } from '@vue/test-utils'

import PolitiqueConfidentialite from '../../views/PolitiqueConfidentialite.vue'

describe('PolitiqueConfidentialite', () => {
  // Affiche le contenu principal de la politique de confidentialité
  test('rend le titre et les sections principales', () => {
    const wrapper = mount(PolitiqueConfidentialite)

    expect(wrapper.text()).toContain('Politique de confidentialité')
    expect(wrapper.text()).toContain('Responsable du traitement')
    expect(wrapper.text()).toContain('Les données que nous collectons')
    expect(wrapper.text()).toContain('Pourquoi nous traitons vos données ?')
    expect(wrapper.text()).toContain('Partage et transferts des données')
    expect(wrapper.find('a[href="mailto:inscription@runningeneva.ch"]').exists()).toBe(true)
  })
})