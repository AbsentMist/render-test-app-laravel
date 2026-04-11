import { describe, test, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

vi.stubGlobal('localStorage', {
  getItem: vi.fn(() => null),
  setItem: vi.fn(),
  removeItem: vi.fn(),
  clear: vi.fn(),
})

const createObjectURL = vi.fn(() => 'blob:download-url')
const revokeObjectURL = vi.fn()

vi.stubGlobal('URL', {
  createObjectURL,
  revokeObjectURL,
})

const routerMock = {
  push: vi.fn(),
}

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    template: '<span data-test="icon"></span>',
  },
}))

vi.mock('../../services/inscriptionService.js', () => ({
  default: {
    getAllInscriptionsAdmin: vi.fn(),
    updateInscriptionAdmin: vi.fn(),
    exportInscriptionsAdmin: vi.fn(),
  },
}))

vi.mock('../../components/Title.vue', () => ({
  default: {
    name: 'Title',
    props: ['texte'],
    template: '<h1>{{ texte }}</h1>',
  },
}))

vi.mock('../../components/FiltreInscriptions.vue', () => ({
  default: {
    name: 'FiltreInscriptions',
    props: ['nbResultats'],
    emits: ['update:filtres', 'exporter'],
    template: `
      <div data-test="filtre-inscriptions">
        <span>{{ nbResultats }}</span>
        <button data-test="emit-filters" @click="$emit('update:filtres', { recherche: 'Alice', status: '', type: '' })">Filter</button>
        <button data-test="emit-csv" @click="$emit('exporter', 'csv')">CSV</button>
        <button data-test="emit-xlsx" @click="$emit('exporter', 'xlsx')">XLSX</button>
      </div>
    `,
  },
}))

vi.mock('../../components/PopupAvertissementCourse.vue', () => ({
  default: {
    name: 'PopupAvertissementCourse',
    props: ['texte'],
    template: '<div data-test="popup-avertissement"><button data-test="confirm" @click="$emit(\'confirmer\')">Confirmer</button><button data-test="close" @click="$emit(\'close\')">Close</button></div>',
  },
}))

vi.mock('../../components/PopupChangementCourseOrganisateur.vue', () => ({
  default: {
    name: 'PopupChangementCourseOrganisateur',
    props: ['inscription', 'participants'],
    template: '<div data-test="popup-changement"></div>',
  },
}))

vi.mock('../../components/PopupInscriptionDetailOrganisateur.vue', () => ({
  default: {
    name: 'PopupInscriptionDetailOrganisateur',
    props: ['inscription'],
    emits: ['modifier-inscription', 'close'],
    template: '<div data-test="popup-detail"></div>',
  },
}))

vi.mock('../../components/PopupConfirmation.vue', () => ({
  default: {
    name: 'PopupConfirmation',
    props: ['message', 'icon'],
    template: '<div data-test="popup-confirmation"><button data-test="confirm-delete" @click="$emit(\'confirm\')">Confirmer</button><button data-test="cancel-delete" @click="$emit(\'cancel\')">Annuler</button></div>',
  },
}))

import OrganisateurInscriptions from '../../views/OrganisateurInscriptions.vue'
import inscriptionService from '../../services/inscriptionService.js'

const mockInscriptions = [
  {
    id: 1,
    dossard: { numero: 101 },
    participant: { id: 10, nom: 'Dupont', prenom: 'Alice' },
    course: { nom: '5 km', type: 'Trail', evenement: { nom: 'Run A' } },
    date_paiement: '2026-04-10T10:00:00Z',
    tarif: 35,
    status_paiement: 'Validé',
  },
  {
    id: 2,
    dossard: { numero: 55 },
    participant: { id: 11, nom: 'Martin', prenom: 'Bob' },
    course: { nom: '10 km', type: 'Route', evenement: { nom: 'Run B' } },
    date_paiement: '2026-04-11T10:00:00Z',
    tarif: 45,
    status_paiement: 'Annulé',
  },
]

function mountComponent() {
  return mount(OrganisateurInscriptions, {
    global: {
      mocks: {
        $route: { params: {} },
        $router: routerMock,
      },
    },
  })
}

describe('OrganisateurInscriptions', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    inscriptionService.getAllInscriptionsAdmin.mockResolvedValue({
      data: mockInscriptions,
    })
    inscriptionService.updateInscriptionAdmin.mockResolvedValue({})
    inscriptionService.exportInscriptionsAdmin.mockResolvedValue({ data: new Blob(['csv']) })
  })

  // Charge les inscriptions et deduit la liste des participants
  test('charge les inscriptions administrateur', async () => {
    const wrapper = mountComponent()

    expect(wrapper.text()).toContain('Chargement des inscriptions...')

    await flushPromises()

    expect(wrapper.text()).toContain('Inscriptions')
    expect(wrapper.text()).toContain('Dupont')
    expect(wrapper.text()).toContain('Martin')
    expect(wrapper.vm.inscriptions).toHaveLength(2)
    expect(wrapper.vm.participants).toHaveLength(2)
    expect(inscriptionService.getAllInscriptionsAdmin).toHaveBeenCalledTimes(1)
  })

  // Met a jour les filtres et le resultat affiché
  test('onFiltresChange met a jour les filtres', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    wrapper.vm.onFiltresChange({ recherche: 'Alice', status: '', type: '' })
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.filtres).toEqual({ recherche: 'Alice', status: '', type: '' })
    expect(wrapper.vm.inscriptionsFiltrees).toHaveLength(1)
    expect(wrapper.vm.inscriptionsFiltrees[0].participant.nom).toBe('Dupont')
  })

  // Trie par colonne et inverse la direction si on reclique
  test('changerTri inverse la direction ou change la colonne', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    expect(wrapper.vm.tri.colonne).toBe('date')
    expect(wrapper.vm.tri.direction).toBe('desc')

    wrapper.vm.changerTri('nom')
    expect(wrapper.vm.tri.colonne).toBe('nom')
    expect(wrapper.vm.tri.direction).toBe('asc')

    wrapper.vm.changerTri('nom')
    expect(wrapper.vm.tri.direction).toBe('desc')
  })

  // Ouvre le detail d une inscription
  test('afficherDetail ouvre la popup detail', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    wrapper.vm.afficherDetail(mockInscriptions[0])
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.popupDetail).toBe(true)
    expect(wrapper.vm.inscription.actuel.id).toBe(1)
    expect(wrapper.find('[data-test="popup-detail"]').exists()).toBe(true)
  })

  // Met a jour localement une inscription modifiee
  test('onModifierInscription met a jour la ligne locale', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    wrapper.vm.onModifierInscription({ id: 1, tarif: 99, status_paiement: 'Validé' })
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.inscriptions.find((i) => i.id === 1).tarif).toBe(99)
    expect(wrapper.vm.inscription.actuel.tarif).toBe(99)
  })

  // Prepare et confirme l annulation d une inscription
  test('suppression puis confirmerSuppression annulent une inscription', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    await wrapper.vm.suppression(mockInscriptions[0])
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.inscriptionASupprimer.id).toBe(1)
    expect(wrapper.find('[data-test="popup-confirmation"]').exists()).toBe(true)

    await wrapper.vm.confirmerSuppression()

    expect(inscriptionService.updateInscriptionAdmin).toHaveBeenCalledWith(1, { status_paiement: 'Annulé' })
    expect(wrapper.vm.inscriptionASupprimer).toBeNull()
    expect(inscriptionService.getAllInscriptionsAdmin).toHaveBeenCalledTimes(2)
  })

  // Affiche une erreur si l annulation echoue
  test('confirmerSuppression conserve l erreur en cas d echec', async () => {
    inscriptionService.updateInscriptionAdmin.mockRejectedValue(new Error('Erreur reseau'))
    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
    const wrapper = mountComponent()
    await flushPromises()

    await wrapper.vm.suppression(mockInscriptions[0])
    await wrapper.vm.$nextTick()
    await wrapper.vm.confirmerSuppression()

    expect(consoleSpy).toHaveBeenCalled()
    consoleSpy.mockRestore()
  })

  // Ouvre et ferme les popups de changement de course
  test('afficherPopupChangement et fermerPopupChangement fonctionnent', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    wrapper.vm.popupAvertissement = true
    wrapper.vm.afficherPopupChangement()
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.popupAvertissement).toBe(false)
    expect(wrapper.vm.popupChangement).toBe(true)

    await wrapper.vm.fermerPopupChangement()

    expect(wrapper.vm.popupChangement).toBe(false)
    expect(inscriptionService.getAllInscriptionsAdmin).toHaveBeenCalledTimes(2)
  })

  // Exporte les inscriptions au format demande
  test('exporter genere un fichier csv', async () => {
    const clickSpy = vi.fn()
    const originalCreateElement = document.createElement.bind(document)

    vi.spyOn(document, 'createElement').mockImplementation((tagName) => {
      const element = originalCreateElement(tagName)
      if (tagName === 'a') {
        element.click = clickSpy
      }
      return element
    })

    const wrapper = mountComponent()
    await flushPromises()

    await wrapper.vm.exporter('csv')

    expect(inscriptionService.exportInscriptionsAdmin).toHaveBeenCalledWith('csv')
    expect(createObjectURL).toHaveBeenCalledTimes(1)
    expect(clickSpy).toHaveBeenCalledTimes(1)
    expect(wrapper.vm.erreur).toBe('')
  })

  // Garde un message lisible si l export echoue
  test('exporter affiche une erreur si lexport echoue', async () => {
    inscriptionService.exportInscriptionsAdmin.mockRejectedValue(new Error('Erreur reseau'))
    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
    const wrapper = mountComponent()
    await flushPromises()

    await wrapper.vm.exporter('xlsx')

    expect(wrapper.vm.erreur).toBe("Impossible d'exporter les inscriptions.")
    expect(consoleSpy).toHaveBeenCalled()
    consoleSpy.mockRestore()
  })

  // Affiche une erreur si le chargement echoue
  test('chargerInscriptions affiche un message derreur', async () => {
    inscriptionService.getAllInscriptionsAdmin.mockRejectedValue(new Error('Erreur reseau'))
    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
    const wrapper = mountComponent()
    await flushPromises()

    expect(wrapper.text()).toContain('Impossible de charger les inscriptions.')
    expect(consoleSpy).toHaveBeenCalled()
    consoleSpy.mockRestore()
  })
})