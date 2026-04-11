import { describe, test, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    template: '<span data-test="icon"></span>',
  },
}))

vi.mock('../../services/inscriptionService.js', () => ({
  default: {
    getMesInscriptions: vi.fn(),
  },
}))
vi.mock('../../components/Title.vue', () => ({
  default: {
    name: 'Title',
    props: ['texte'],
    template: '<h1>{{ texte }}</h1>',
  },
}))
vi.mock('../../components/PopupAvertissementCourse.vue', () => ({
  default: {
    name: 'PopupAvertissementCourse',
    props: ['texte'],
    template: '<div data-test="popup-avertissement"></div>',
  },
}))
vi.mock('../../components/PopupChangementCourseParticipant.vue', () => ({
  default: {
    name: 'PopupChangementCourseParticipant',
    props: ['inscription', 'participants'],
    template: '<div data-test="popup-changement"></div>',
  },
}))
vi.mock('../../components/PopupInscriptionDetailParticipant.vue', () => ({
  default: {
    name: 'PopupInscriptionDetailParticipant',
    props: ['inscription', 'participants'],
    template: '<div data-test="popup-detail"></div>',
  },
}))

import ParticipantInscriptions from '../../views/ParticipantInscriptions.vue'
import inscriptionService from '../../services/inscriptionService.js'

const mockInscriptions = [
  {
    id: 99,
    course: {
      nom: '5 km',
      distance: 5,
      type: 'Trail',
      date_debut: '2026-04-01',
      status: 'actif',
      tarif: 35,
      evenement: { nom: 'Run 2026' },
      document_description: 'Documents à fournir',
    },
    groupe: { nom: 'Team A' },
    equipe: 'Team A',
    tarif: 35,
    status_paiement: 'Validé',
    dossard: { numero: 123 },
    participant: { id: 10, nom: 'Dupont', prenom: 'Alice', equipe_nom: 'Team A' },
    montant_rabais: 5,
    date_paiement: '2026-04-01',
    ref_groupage: 'RG-1',
    participe_challenge: true,
    type_challenge: 'Groupe',
    code_participant: 'ABC123',
    avertissement_valide: true,
    documents_fournis: [],
  },
]

function mountComponent() {
  return mount(ParticipantInscriptions)
}

describe('ParticipantInscriptions', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    inscriptionService.getMesInscriptions.mockResolvedValue({
      data: mockInscriptions,
    })
  })

  // Charge les inscriptions et affiche les informations principales
  test('charge les inscriptions du participant', async () => {
    const wrapper = mountComponent()

    expect(wrapper.text()).toContain('Chargement des inscriptions...')

    await flushPromises()

    expect(wrapper.text()).toContain('Mes inscriptions')
    expect(wrapper.text()).toContain('Run 2026 - 5 km')
    expect(wrapper.text()).toContain('Dupont Alice')
    expect(wrapper.text()).toContain('Validé')
    expect(wrapper.text()).toContain('Team A')
  })

  // Ouvre la popup de détail au clic sur le bouton detail
  test('ouvre la popup de detail', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    await wrapper.findAll('button').find((button) => button.text().includes('Détail')).trigger('click')

    expect(wrapper.find('[data-test="popup-detail"]').exists()).toBe(true)
    const popup = wrapper.findComponent({ name: 'PopupInscriptionDetailParticipant' })
    expect(popup.props('inscription').id).toBe(99)
  })

  // Ouvre et ferme les popups de changement selon la methode appelee
  test('controle les popups de changement de course', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    wrapper.vm.changerInscription(mockInscriptions[0])
    expect(wrapper.vm.popupAvertissement).toBe(true)
    expect(wrapper.vm.inscription.actuel.id).toBe(99)

    wrapper.vm.afficherPopupChangement()
    expect(wrapper.vm.popupAvertissement).toBe(false)
    expect(wrapper.vm.popupChangement).toBe(true)

    await wrapper.vm.fermerPopupChangement()
    await flushPromises()

    expect(wrapper.vm.popupChangement).toBe(false)
    expect(inscriptionService.getMesInscriptions).toHaveBeenCalledTimes(2)
  })

  // Ouvre ou ferme le detail et relaie les donnees vers le parent
  test('toggle les donnees de detail et emet ajouter-panier', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    wrapper.vm.detailInscription(mockInscriptions[0])
    expect(wrapper.vm.popupDetail).toBe(true)
    expect(wrapper.vm.inscription.actuel.id).toBe(99)

    wrapper.vm.toggleExpand(99)
    expect(wrapper.vm.expandedRows).toContain(99)
    wrapper.vm.toggleExpand(99)
    expect(wrapper.vm.expandedRows).not.toContain(99)

    const warnSpy = vi.spyOn(console, 'warn').mockImplementation(() => {})
    wrapper.vm.onChangementConfirme({ panier: true })
    warnSpy.mockRestore()
    expect(wrapper.vm.popupDetail).toBe(false)
    expect(wrapper.emitted('ajouter-panier')[0]).toEqual([{ panier: true }])
  })

  // Affiche un message en cas d erreur de chargement
  test('affiche un message derreur si le chargement echoue', async () => {
    inscriptionService.getMesInscriptions.mockRejectedValue(new Error('Erreur reseau'))
    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})

    const wrapper = mountComponent()
    await flushPromises()

    expect(wrapper.text()).toContain('Impossible de charger les inscriptions.')
    expect(consoleSpy).toHaveBeenCalled()

    consoleSpy.mockRestore()
  })
})