import { describe, test, expect, vi, beforeEach } from 'vitest'

vi.mock('../../services/api', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
  },
}))

import api from '../../services/api'
import membershipService from '../../services/membershipService'

describe('membershipService', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    api.get.mockResolvedValue({ data: {} })
    api.post.mockResolvedValue({ data: {} })
  })

  test('accesMembershipParticipant appelle GET /participant/membership/acces', async () => {
    await membershipService.accesMembershipParticipant()

    expect(api.get).toHaveBeenCalledTimes(1)
    expect(api.get).toHaveBeenCalledWith('/participant/membership/acces')
  })

  test('soumettreDemande appelle POST /participant/membership/demander avec le payload', async () => {
    const payload = {
      nom: 'Dupont',
      prenom: 'Alice',
      email: 'alice@test.ch',
    }

    await membershipService.soumettreDemande(payload)

    expect(api.post).toHaveBeenCalledTimes(1)
    expect(api.post).toHaveBeenCalledWith('/participant/membership/demander', payload)
  })

  test('maDemande appelle GET /participant/membership/ma-demande', async () => {
    await membershipService.maDemande()

    expect(api.get).toHaveBeenCalledTimes(1)
    expect(api.get).toHaveBeenCalledWith('/participant/membership/ma-demande')
  })

  test('profilParticipant appelle GET /participant/profil', async () => {
    await membershipService.profilParticipant()

    expect(api.get).toHaveBeenCalledTimes(1)
    expect(api.get).toHaveBeenCalledWith('/participant/profil')
  })

  test('rechercherParticipantParEmail appelle GET /organisateur/membership/participants/rechercher avec params', async () => {
    await membershipService.rechercherParticipantParEmail('alice@test.ch')

    expect(api.get).toHaveBeenCalledTimes(1)
    expect(api.get).toHaveBeenCalledWith('/organisateur/membership/participants/rechercher', {
      params: { email: 'alice@test.ch' },
    })
  })

  test('inviterParticipant appelle POST /organisateur/membership/invitations avec le payload', async () => {
    const payload = { email: 'alice@test.ch', commentaire_admin: 'Bienvenue' }

    await membershipService.inviterParticipant(payload)

    expect(api.post).toHaveBeenCalledTimes(1)
    expect(api.post).toHaveBeenCalledWith('/organisateur/membership/invitations', payload)
  })

  test('listerDemandes appelle GET /organisateur/membership/demandes', async () => {
    await membershipService.listerDemandes()

    expect(api.get).toHaveBeenCalledTimes(1)
    expect(api.get).toHaveBeenCalledWith('/organisateur/membership/demandes')
  })

  test('listerDemandesEnAttente appelle GET /organisateur/membership/demandes/en-attente', async () => {
    await membershipService.listerDemandesEnAttente()

    expect(api.get).toHaveBeenCalledTimes(1)
    expect(api.get).toHaveBeenCalledWith('/organisateur/membership/demandes/en-attente')
  })

  test('approuverDemande appelle POST /organisateur/membership/demandes/{id}/approuver', async () => {
    await membershipService.approuverDemande(42)

    expect(api.post).toHaveBeenCalledTimes(1)
    expect(api.post).toHaveBeenCalledWith('/organisateur/membership/demandes/42/approuver')
  })

  test('remettreACompleterDemande appelle POST /organisateur/membership/demandes/{id}/a-completer avec commentaire', async () => {
    await membershipService.remettreACompleterDemande(42, 'Dossier incomplet')

    expect(api.post).toHaveBeenCalledTimes(1)
    expect(api.post).toHaveBeenCalledWith('/organisateur/membership/demandes/42/a-completer', {
      commentaire_admin: 'Dossier incomplet',
    })
  })

  test('annulerInvitation appelle POST /organisateur/membership/invitations/{id}/annuler avec commentaire', async () => {
    await membershipService.annulerInvitation(42, 'Annulation')

    expect(api.post).toHaveBeenCalledTimes(1)
    expect(api.post).toHaveBeenCalledWith('/organisateur/membership/invitations/42/annuler', {
      commentaire_annulation: 'Annulation',
    })
  })

  test('checkoutMembership appelle POST /participant/membership/checkout avec le payload', async () => {
    const payload = { formulaire: { nom: 'Dupont' }, prix: 25 }

    await membershipService.checkoutMembership(payload)

    expect(api.post).toHaveBeenCalledTimes(1)
    expect(api.post).toHaveBeenCalledWith('/participant/membership/checkout', payload)
  })
})
