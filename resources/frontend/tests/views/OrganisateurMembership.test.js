/**
 * Tests frontend du projet.
 *
 * @author Ngozoo
 * @returns {void}
 */

import { describe, test, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    template: '<span data-test="icon"></span>',
  },
}))

vi.mock('../../components/Title.vue', () => ({
  default: {
    name: 'Title',
    props: ['texte'],
    template: '<h1>{{ texte }}</h1>',
  },
}))

vi.mock('../../services/membershipService', () => ({
  default: {
    listerDemandes: vi.fn(),
    approuverDemande: vi.fn(),
    remettreACompleterDemande: vi.fn(),
    annulerInvitation: vi.fn(),
    rechercherParticipantParEmail: vi.fn(),
    inviterParticipant: vi.fn(),
  },
}))

import OrganisateurMembership from '../../views/OrganisateurMembership.vue'
import membershipService from '../../services/membershipService'

const demandesFixture = [
  {
    id: 1,
    prenom: 'Alice',
    nom: 'Dupont',
    email: 'alice@test.ch',
    telephone: '079 123 45 67',
    date_naissance: '1990-01-01',
    date_creation: '2026-05-01 10:00:00',
    status: 'En attente de validation',
    description: 'Motivation A',
    id_invitation: 10,
    invitation_status: 'En cours',
  },
  {
    id: 2,
    prenom: 'Bob',
    nom: 'Martin',
    email: 'bob@test.ch',
    telephone: '078 111 22 33',
    date_naissance: '1992-03-04',
    date_creation: '2026-05-02 11:00:00',
    status: 'Approuvée',
    description: 'Motivation B',
    id_invitation: 11,
    invitation_status: 'Complété',
  },
  {
    id: 3,
    prenom: 'Chloe',
    nom: 'Rossi',
    email: 'chloe@test.ch',
    telephone: '077 000 11 22',
    date_naissance: '1995-07-09',
    date_creation: '2026-05-03 12:00:00',
    status: 'À compléter',
    description: 'Motivation C',
    id_invitation: 12,
    invitation_status: 'En cours',
  },
]

function mountComponent() {
  return mount(OrganisateurMembership)
}

describe('OrganisateurMembership', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    membershipService.listerDemandes.mockResolvedValue({ data: demandesFixture })
    membershipService.approuverDemande.mockResolvedValue({ data: { message: 'ok' } })
    membershipService.remettreACompleterDemande.mockResolvedValue({ data: { message: 'ok' } })
    membershipService.annulerInvitation.mockResolvedValue({ data: { message: 'ok' } })
    membershipService.rechercherParticipantParEmail.mockResolvedValue({ data: { email: 'alice@test.ch', prenom: 'Alice', nom: 'Dupont' } })
    membershipService.inviterParticipant.mockResolvedValue({ data: { message: 'ok' } })
  })

  test('chargerDemandes est appelé au montage et hydrate la liste', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    expect(membershipService.listerDemandes).toHaveBeenCalledTimes(1)
    expect(wrapper.vm.demandes).toHaveLength(3)
    expect(wrapper.vm.chargement).toBe(false)
    expect(wrapper.text()).toContain('Alice Dupont')
  })

  test('demandesFiltrees filtre par statut et recherche', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    expect(wrapper.vm.demandesFiltrees).toHaveLength(3)

    wrapper.vm.filtreStatus = 'En attente de validation'
    wrapper.vm.recherche = 'alice'
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.demandesFiltrees).toHaveLength(1)
    expect(wrapper.vm.demandesFiltrees[0].id).toBe(1)

    wrapper.vm.recherche = 'inexistant'
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.demandesFiltrees).toHaveLength(0)
  })

  test('ouvrirDetail ouvre la modal avec une copie de la demande', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    const source = wrapper.vm.demandes[0]
    wrapper.vm.ouvrirDetail(source)

    expect(wrapper.vm.modalDetail).toBe(true)
    expect(wrapper.vm.demandeSelectionnee).toEqual(source)
    expect(wrapper.vm.demandeSelectionnee).not.toBe(source)
  })

  test('ouvrirApprouve positionne la demande et ouvre la modal', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    const source = wrapper.vm.demandes[0]
    wrapper.vm.ouvrirApprouve(source)

    expect(wrapper.vm.demandeSelectionnee).toBe(source)
    expect(wrapper.vm.modalApprouver).toBe(true)
  })

  test('ouvrirACompleter reset le commentaire et ouvre la modal', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    wrapper.vm.commentaireACompleter = 'Ancienne raison'
    const source = wrapper.vm.demandes[0]
    wrapper.vm.ouvrirACompleter(source)

    expect(wrapper.vm.demandeSelectionnee).toBe(source)
    expect(wrapper.vm.commentaireACompleter).toBe('')
    expect(wrapper.vm.modalACompleter).toBe(true)
  })

  test('approuverDemande appelle le service, ferme les modals et dispatch un event', async () => {
    const dispatchSpy = vi.spyOn(window, 'dispatchEvent')

    const wrapper = mountComponent()
    await flushPromises()

    wrapper.vm.demandeSelectionnee = { id: 1 }
    wrapper.vm.modalApprouver = true
    wrapper.vm.modalDetail = true

    await wrapper.vm.approuverDemande()

    expect(membershipService.approuverDemande).toHaveBeenCalledWith(1)
    expect(wrapper.vm.modalApprouver).toBe(false)
    expect(wrapper.vm.modalDetail).toBe(false)
    expect(membershipService.listerDemandes).toHaveBeenCalledTimes(2)
    expect(dispatchSpy).toHaveBeenCalledTimes(1)
    expect(dispatchSpy.mock.calls[0][0].type).toBe('membership-notifications-updated')

    dispatchSpy.mockRestore()
  })

  test('remettreACompleterDemande ne fait rien si commentaire vide', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    wrapper.vm.demandeSelectionnee = { id: 1 }
    wrapper.vm.commentaireACompleter = '   '

    await wrapper.vm.remettreACompleterDemande()

    expect(membershipService.remettreACompleterDemande).not.toHaveBeenCalled()
  })

  test('remettreACompleterDemande appelle le service et ferme les modals', async () => {
    const dispatchSpy = vi.spyOn(window, 'dispatchEvent')

    const wrapper = mountComponent()
    await flushPromises()

    wrapper.vm.demandeSelectionnee = { id: 1 }
    wrapper.vm.commentaireACompleter = 'Dossier incomplet'
    wrapper.vm.modalACompleter = true
    wrapper.vm.modalDetail = true

    await wrapper.vm.remettreACompleterDemande()

    expect(membershipService.remettreACompleterDemande).toHaveBeenCalledWith(1, 'Dossier incomplet')
    expect(wrapper.vm.modalACompleter).toBe(false)
    expect(wrapper.vm.modalDetail).toBe(false)
    expect(membershipService.listerDemandes).toHaveBeenCalledTimes(2)
    expect(dispatchSpy.mock.calls[0][0].type).toBe('membership-notifications-updated')

    dispatchSpy.mockRestore()
  })

  test('ouvrirAnnulerInvitation positionne la demande et ouvre la modale', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    const source = wrapper.vm.demandes[0]
    wrapper.vm.ouvrirAnnulerInvitation(source)

    expect(wrapper.vm.demandeSelectionnee).toBe(source)
    expect(wrapper.vm.commentaireAnnulation).toBe('')
    expect(wrapper.vm.modalAnnulerInvitation).toBe(true)
  })

  test('annulerInvitation appelle le service et ferme les modals', async () => {
    const dispatchSpy = vi.spyOn(window, 'dispatchEvent')

    const wrapper = mountComponent()
    await flushPromises()

    wrapper.vm.demandeSelectionnee = { id_invitation: 12 }
    wrapper.vm.commentaireAnnulation = 'Invitation fermée'
    wrapper.vm.modalAnnulerInvitation = true
    wrapper.vm.modalDetail = true

    await wrapper.vm.annulerInvitation()

    expect(membershipService.annulerInvitation).toHaveBeenCalledWith(12, 'Invitation fermée')
    expect(wrapper.vm.modalAnnulerInvitation).toBe(false)
    expect(wrapper.vm.modalDetail).toBe(false)
    expect(membershipService.listerDemandes).toHaveBeenCalledTimes(2)
    expect(dispatchSpy.mock.calls[0][0].type).toBe('membership-notifications-updated')

    dispatchSpy.mockRestore()
  })

  test('formatDate retourne un tiret si date vide', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    expect(wrapper.vm.formatDate(null)).toBe('—')
  })
})
