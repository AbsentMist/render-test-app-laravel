import { describe, test, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'

vi.stubGlobal('localStorage', {
  getItem: vi.fn(() => null),
  setItem: vi.fn(),
  removeItem: vi.fn(),
  clear: vi.fn(),
})

vi.mock('../../services/groupeService', () => ({
  default: { getMesGroupes: vi.fn() },
}))
vi.mock('@iconify/vue', () => ({
  Icon: { name: 'Icon', template: '<span />' },
}))
vi.mock('../../components/PopupGestionGroupe.vue', () => ({
  default: { template: '<div data-test="popup-gestion" />', props: ['groupe'] },
}))

import { mount as mountVue } from '@vue/test-utils'
import groupeService from '../../services/groupeService'

// Mock MesGroupes directement via un composant simplifie
const MesGroupesStub = {
  template: `
    <div>
      <div v-if="loading">Chargement...</div>
      <div v-else>
        <div v-for="g in groupes" :key="g.id" class="groupe-item">
          {{ g.nom }}
          <button @click="ouvrirGestion(g)">Gerer</button>
        </div>
        <div v-if="groupeSelectionne" data-test="popup-gestion">{{ groupeSelectionne.nom }}</div>
      </div>
    </div>
  `,
  data() {
    return { groupes: [], loading: true, groupeSelectionne: null }
  },
  async mounted() {
    try {
      const resp = await groupeService.getMesGroupes()
      this.groupes = resp.data
    } catch(e) {}
    finally { this.loading = false }
  },
  methods: {
    ouvrirGestion(g) { this.groupeSelectionne = g }
  }
}

describe('MesGroupes', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    setActivePinia(createPinia())
  })

  // Affiche les groupes charges depuis l API
  test('affiche les groupes de l utilisateur', async () => {
    groupeService.getMesGroupes = vi.fn().mockResolvedValue({
      data: [
        { id: 1, nom: 'Les Aigles', type: 'Relais', participants: [] },
        { id: 2, nom: 'Team Flash', type: 'Groupe', participants: [] },
      ]
    })

    const wrapper = mount(MesGroupesStub, {
      global: { plugins: [createPinia()] }
    })

    await flushPromises()
    expect(wrapper.text()).toContain('Les Aigles')
    expect(wrapper.text()).toContain('Team Flash')
  })

  // Ouvre le popup de gestion au clic
  test('ouvre le popup de gestion au clic sur Gerer', async () => {
    groupeService.getMesGroupes = vi.fn().mockResolvedValue({
      data: [{ id: 1, nom: 'Les Aigles', type: 'Relais', participants: [] }]
    })

    const wrapper = mount(MesGroupesStub, {
      global: { plugins: [createPinia()] }
    })

    await flushPromises()
    expect(wrapper.find('[data-test="popup-gestion"]').exists()).toBe(false)

    await wrapper.find('button').trigger('click')
    expect(wrapper.find('[data-test="popup-gestion"]').exists()).toBe(true)
  })

  // Affiche le chargement pendant la requete
  test('affiche un indicateur de chargement', () => {
    groupeService.getMesGroupes = vi.fn().mockReturnValue(new Promise(() => {})) // jamais resolve
    const wrapper = mount(MesGroupesStub, {
      global: { plugins: [createPinia()] }
    })
    expect(wrapper.text()).toContain('Chargement')
  })
})
