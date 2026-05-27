/**
 * Tests frontend du projet.
 *
 * @author Ngozoo
 * @returns {void}
 */

import { describe, test, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

const cartStoreMock = {
  inscriptions: [],
  ajouterInscription: vi.fn(),
}

vi.mock('../../services/membershipService', () => ({
  default: {
    accesMembershipParticipant: vi.fn(),
    profilParticipant: vi.fn(),
    maDemande: vi.fn(),
  },
}))

vi.mock('../../stores/cart', () => ({
  useCartStore: vi.fn(() => cartStoreMock),
}))

import membershipService from '../../services/membershipService'
import Membership from '../../views/Membership.vue'

function mountComponent() {
  return mount(Membership, {
    global: {
      stubs: {
        'router-link': {
          template: '<a><slot /></a>',
        },
      },
      mocks: {
        $router: {
          push: vi.fn(),
        },
      },
    },
  })
}

describe('Membership.vue', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    cartStoreMock.inscriptions = []
    membershipService.accesMembershipParticipant.mockResolvedValue({ data: { has_access: true, invitation: null, formulaire: null } })
    membershipService.profilParticipant.mockResolvedValue({ data: {} })
    membershipService.maDemande.mockResolvedValue({ data: null })
  })

  test('initialiserVue pré-remplit depuis profil et charge demande', async () => {
    membershipService.profilParticipant.mockResolvedValue({
      data: {
        nom: 'Dupont',
        prenom: 'Jean',
        email: 'jean@test.ch',
        adresse: 'Rue 1',
        numero: '5',
        npa: '1200',
        commune: 'Geneve',
        nationalite: 'Suisse',
        telephone: '0791234567',
        dateNaissance: '01/02/1990',
      },
    })

    const wrapper = mountComponent()
    await flushPromises()

    expect(membershipService.accesMembershipParticipant).toHaveBeenCalled()
    expect(membershipService.profilParticipant).toHaveBeenCalled()
    expect(wrapper.vm.formulaire.nom).toBe('Dupont')
    expect(wrapper.vm.formulaire.prenom).toBe('Jean')
    expect(wrapper.vm.formulaire.email).toBe('jean@test.ch')
    expect(wrapper.vm.formulaire.code_postal).toBe('1200')
    expect(wrapper.vm.formulaire.ville).toBe('Geneve')
    expect(wrapper.vm.formulaire.pays).toBe('Suisse')
    expect(wrapper.vm.formulaire.telephone).toBe('079 123 45 67')
    expect(wrapper.vm.formulaire.date_naissance).toBe('1990-02-01')
    expect(wrapper.vm.chargementInitial).toBe(false)
  })

  test('prefillDepuisDemande prend les valeurs de la demande à compléter', async () => {
    const demande = {
      nom: 'Martin',
      prenom: 'Alice',
      email: 'alice@test.ch',
      adresse: 'Rue X 10',
      code_postal: '1111',
      ville: 'Lausanne',
      pays: 'Suisse',
      telephone: '0791234567',
      description: 'Motivation longue',
      date_naissance: '1990-03-04',
      status: 'À compléter',
    }

    membershipService.maDemande.mockResolvedValue({ data: demande })

    const wrapper = mountComponent()
    await flushPromises()

    expect(wrapper.vm.demandeExistante.status).toBe('À compléter')
    expect(wrapper.vm.formulaire.nom).toBe('Martin')
    expect(wrapper.vm.formulaire.telephone).toBe('079 123 45 67')
    expect(wrapper.vm.formulaire.description).toBe('Motivation longue')
  })

  test('normaliserTelephone et formaterTelephone fonctionnent', async () => {
    const wrapper = mountComponent()
    const vm = wrapper.vm

    expect(vm.normaliserTelephone('0791234567')).toBe('079 123 45 67')
    expect(vm.normaliserTelephone('0791234')).toBe('079 123 4')
    expect(vm.normaliserTelephone('')).toBe('')

    vm.formulaire.telephone = ''
    vm.formaterTelephone({ target: { value: '0791234567' } })
    expect(vm.formulaire.telephone).toBe('079 123 45 67')
  })

  test('rechercherAdresse remplit les suggestions via fetch', async () => {
    const wrapper = mountComponent()
    const vm = wrapper.vm

    const fakeResults = { results: [{ attrs: { label: 'Rue Test 5 1200 Genève' } }] }
    vi.stubGlobal('fetch', vi.fn(() => Promise.resolve({ json: () => Promise.resolve(fakeResults) })))

    vi.useFakeTimers()
    vm.rechercherAdresse('Rue')
    vi.advanceTimersByTime(300)
    await flushPromises()

    expect(fetch).toHaveBeenCalled()
    expect(vm.adresseSuggestions.length).toBeGreaterThan(0)
    expect(vm.showAdresseDropdown).toBe(true)

    vi.useRealTimers()
    vi.unstubAllGlobals()
  })

  test('selectionnerAdresse met a jour adresse, code_postal et ville et cache dropdown', async () => {
    const wrapper = mountComponent()
    const vm = wrapper.vm

    const suggestion = { attrs: { label: 'Route de Test 1200 Ville' } }
    vm.adresseSuggestions = [suggestion]
    vm.showAdresseDropdown = true

    vm.selectionnerAdresse(suggestion)

    expect(vm.formulaire.adresse).toContain('Route de Test')
    expect(vm.formulaire.code_postal).toBe('1200')
    expect(vm.showAdresseDropdown).toBe(false)
  })

  test('validerFormulaire detecte champs manquants et valide correctement', async () => {
    const wrapper = mountComponent()
    const vm = wrapper.vm

    vm.formulaire = {
      nom: '', prenom: '', email: '', adresse: '', code_postal: '', ville: '', pays: '', telephone: '', date_naissance: '', description: '',
    }
    const ok1 = vm.validerFormulaire()
    expect(ok1).toBe(false)
    expect(Object.keys(vm.errors).length).toBeGreaterThan(0)

    vm.formulaire = {
      nom: 'N', prenom: 'P', email: 'a@b.c', adresse: 'Rue 1', code_postal: '1200', ville: 'Geneve', pays: 'Suisse', telephone: '0791234567', date_naissance: '1990-01-01', description: '0123456789',
    }
    vm.formulaire.telephone = vm.normaliserTelephone(vm.formulaire.telephone)
    const ok2 = vm.validerFormulaire()
    expect(ok2).toBe(true)
  })

  test('soumettreDemande ajoute le membership au panier et redirige', async () => {
    const wrapper = mountComponent()
    const vm = wrapper.vm

    vm.formulaire = {
      nom: 'N', prenom: 'P', email: 'a@b.c', adresse: 'Rue 1', code_postal: '1200', ville: 'Geneve', pays: 'Suisse', telephone: '079 123 45 67', date_naissance: '1990-01-01', description: '0123456789',
    }

    await vm.soumettreDemande()

    expect(cartStoreMock.ajouterInscription).toHaveBeenCalledTimes(1)
    expect(wrapper.vm.$router.push).toHaveBeenCalledWith({ name: 'Panier' })
  })

  test('soumettreDemande bloque si le membership est déjà dans le panier', async () => {
    cartStoreMock.inscriptions = [{ type_article: 'membership' }]

    const wrapper = mountComponent()
    const vm = wrapper.vm

    vm.formulaire = {
      nom: 'N', prenom: 'P', email: 'a@b.c', adresse: 'Rue 1', code_postal: '1200', ville: 'Geneve', pays: 'Suisse', telephone: '079 123 45 67', date_naissance: '1990-01-01', description: '0123456789',
    }

    await vm.soumettreDemande()

    expect(cartStoreMock.ajouterInscription).not.toHaveBeenCalled()
    expect(vm.messageErreur).toContain('déjà dans le panier')
  })
})
