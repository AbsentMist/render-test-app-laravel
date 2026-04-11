import { describe, test, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    template: '<span data-test="icon"></span>',
  },
}))

vi.mock('../../services/api', () => ({
  default: {
    get: vi.fn(),
  },
}))

vi.mock('../../services/participantService', () => ({
  default: {
    creerParticipant: vi.fn(),
  },
}))

vi.mock('../../services/challengeOrganisationService', () => ({
  default: {
    getOrganisations: vi.fn(),
  },
}))

import EtapeParticipant from '../../components/EtapeParticipant.vue'
import api from '../../services/api'
import participantService from '../../services/participantService'
import challengeOrganisationService from '../../services/challengeOrganisationService'

const baseParticipants = [
  { id: 1, prenom: 'Alice', nom: 'Dupont' },
  { id: 2, prenom: 'Bob', nom: 'Martin' },
  { id: 3, prenom: 'Nina', nom: 'Ray' },
]

function mountComponent(customProps = {}) {
  return mount(EtapeParticipant, {
    props: {
      participants: baseParticipants,
      chargement: false,
      typeSelectionne: { id: 'solo', nom: 'Solo' },
      modelValue: [],
      groupeValue: null,
      courseId: 77,
      ...customProps,
    },
  })
}

describe('EtapeParticipant', () => {
  beforeEach(() => {
    vi.clearAllMocks()

    api.get.mockResolvedValue({
      data: { id: 9, prenom: 'Rita', nom: 'Lopez' },
    })

    participantService.creerParticipant.mockResolvedValue({
      data: { id: 99, prenom: 'New', nom: 'Person' },
    })

    challengeOrganisationService.getOrganisations.mockResolvedValue({
      data: [
        { id: 1, nom: 'UNIGE', type: 'Groupe' },
        { id: 2, nom: 'CERN', type: 'Entreprise' },
      ],
    })
  })

  // Derive correctement les drapeaux de type et la liste des participants
  test('computed de type et tousLesParticipants', () => {
    const wrapper = mountComponent({ typeSelectionne: { id: 'relais', nom: 'Relais' } })

    expect(wrapper.vm.estRelais).toBe(true)
    expect(wrapper.vm.estGroupe).toBe(false)
    expect(wrapper.vm.estChallenge).toBe(false)
    expect(wrapper.vm.tousLesParticipants).toEqual(baseParticipants)
  })

  // Filtre les organisations selon le type challenge et calcule le nom final
  test('organisationsFiltrees et nomOrganisationChallenge', async () => {
    const wrapper = mountComponent({ typeSelectionne: { id: 'challenge', nom: 'Challenge' } })
    wrapper.vm.organisationsChallenge = [
      { id: 1, nom: 'UNIGE', type: 'Groupe' },
      { id: 2, nom: 'CERN', type: 'Entreprise' },
    ]

    wrapper.vm.challengeData.typeOrganisation = 'Groupe'
    expect(wrapper.vm.organisationsFiltrees).toEqual([{ id: 1, nom: 'UNIGE', type: 'Groupe' }])

    wrapper.vm.selectionnerOrg({ id: 1, nom: 'UNIGE', type: 'Groupe' })
    expect(wrapper.vm.nomOrganisationChallenge).toBe('UNIGE')

    wrapper.vm.challengeData.orgSelectionnee = null
    wrapper.vm.challengeData.modeLibre = true
    wrapper.vm.challengeData.orgLibre = 'HEG'
    await wrapper.vm.$nextTick()
    expect(wrapper.vm.nomOrganisationChallenge).toBe('HEG')
  })

  // Calcule limite et message statut groupe
  test('limiteGroupe et messageStatutGroupe', () => {
    const wrapper = mountComponent({ typeSelectionne: { id: 'groupe', nom: 'Groupe (2-4)' } })
    wrapper.vm.groupeData.participants = [{ id: 1 }]

    expect(wrapper.vm.limiteGroupe).toEqual({ min: 2, max: 4 })
    expect(wrapper.vm.messageStatutGroupe.type).toBe('erreur')

    wrapper.vm.groupeData.participants = [{ id: 1 }, { id: 2 }]
    expect(wrapper.vm.messageStatutGroupe.type).toBe('ok')
  })

  // Emet la mise a jour modelValue via le setter selectionnes
  test('selectionnes setter emet update:modelValue', () => {
    const wrapper = mountComponent()

    wrapper.vm.selectionnes = [{ id: 1 }]

    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    expect(wrapper.emitted('update:modelValue')[0][0]).toEqual([{ id: 1 }])
  })

  // Verifie la validation minimale du formulaire
  test('formulaireValide depend du nom et prenom', () => {
    const wrapper = mountComponent()

    wrapper.vm.form.nom = ''
    wrapper.vm.form.prenom = 'Alice'
    expect(wrapper.vm.formulaireValide).toBe(false)

    wrapper.vm.form.nom = 'Dupont'
    wrapper.vm.form.prenom = 'Alice'
    expect(wrapper.vm.formulaireValide).toBe(true)
  })

  // Emet la structure groupe avec copie participants
  test('emitGroupe emet update:groupeValue', () => {
    const wrapper = mountComponent({ typeSelectionne: { id: 'groupe', nom: 'Groupe (2-4)' } })
    wrapper.vm.groupeData = { nom: 'Team A', participants: [{ id: 1 }] }

    wrapper.vm.emitGroupe()

    expect(wrapper.emitted('update:groupeValue')).toBeTruthy()
    expect(wrapper.emitted('update:groupeValue').at(-1)[0]).toEqual({ nom: 'Team A', participants: [{ id: 1 }] })
  })

  // Emet la structure challenge seulement quand type et nom sont valides
  test('emitChallenge emet update:groupeValue quand structure complete', () => {
    const wrapper = mountComponent({
      typeSelectionne: { id: 'challenge', nom: 'Challenge' },
      modelValue: [{ id: 2, prenom: 'Bob', nom: 'Martin' }],
    })

    wrapper.vm.challengeData.typeOrganisation = 'Entreprise'
    wrapper.vm.challengeData.orgLibre = 'CERN'
    wrapper.vm.challengeData.modeLibre = true

    wrapper.vm.emitChallenge()

    expect(wrapper.emitted('update:groupeValue')).toBeTruthy()
    expect(wrapper.emitted('update:groupeValue').at(-1)[0]).toEqual({
      nom: 'CERN',
      type_groupe: 'Entreprise',
      participants: [{ id: 2, prenom: 'Bob', nom: 'Martin' }],
    })
  })

  // Selectionne une org challenge et remet le mode libre
  test('selectionnerOrg met a jour orgSelectionnee et emet', () => {
    const wrapper = mountComponent({ typeSelectionne: { id: 'challenge', nom: 'Challenge' } })
    wrapper.vm.challengeData.typeOrganisation = 'Groupe'

    wrapper.vm.selectionnerOrg({ id: 1, nom: 'UNIGE', type: 'Groupe' })

    expect(wrapper.vm.challengeData.orgSelectionnee?.id).toBe(1)
    expect(wrapper.vm.challengeData.modeLibre).toBe(false)
    expect(wrapper.emitted('update:groupeValue')).toBeTruthy()
  })

  // Selection challenge force un seul participant
  test('toggleSelectionnerChallenge remplace la selection par un seul participant', () => {
    const wrapper = mountComponent({ typeSelectionne: { id: 'challenge', nom: 'Challenge' } })
    wrapper.vm.challengeData.typeOrganisation = 'Entreprise'
    wrapper.vm.challengeData.modeLibre = true
    wrapper.vm.challengeData.orgLibre = 'CERN'

    wrapper.vm.toggleSelectionnerChallenge(baseParticipants[1])

    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    expect(wrapper.emitted('update:modelValue').at(-1)[0]).toEqual([baseParticipants[1]])
  })

  // Charge les organisations challenge (success + garde courseId)
  test('chargerOrganisations remplit organisations et gere courseId absent', async () => {
    const wrapper = mountComponent({ typeSelectionne: { id: 'challenge', nom: 'Challenge' } })

    await wrapper.vm.chargerOrganisations()
    expect(challengeOrganisationService.getOrganisations).toHaveBeenCalledWith(77)
    expect(wrapper.vm.organisationsChallenge).toHaveLength(2)
    expect(wrapper.vm.challengeData.chargementOrgs).toBe(false)

    const wrapperSansCourse = mountComponent({ courseId: null, typeSelectionne: { id: 'challenge', nom: 'Challenge' } })
    await wrapperSansCourse.vm.chargerOrganisations()
    expect(challengeOrganisationService.getOrganisations).toHaveBeenCalledTimes(1)
  })

  // Gere ajout/retrait en groupe avec limite
  test('estDansGroupe numeroMembre toggleMembreGroupe respectent la limite', () => {
    const wrapper = mountComponent({ typeSelectionne: { id: 'relais', nom: 'Relais' } })
    wrapper.vm.groupeData = { nom: '', participants: [] }

    wrapper.vm.toggleMembreGroupe(baseParticipants[0])
    wrapper.vm.toggleMembreGroupe(baseParticipants[1])
    wrapper.vm.toggleMembreGroupe(baseParticipants[2])

    expect(wrapper.vm.groupeData.participants).toHaveLength(2)
    expect(wrapper.vm.estDansGroupe(1)).toBe(true)
    expect(wrapper.vm.numeroMembre(1)).toBe(1)

    wrapper.vm.toggleMembreGroupe(baseParticipants[0])
    expect(wrapper.vm.estDansGroupe(1)).toBe(false)
  })

  // Selection simple: max un participant a la fois
  test('estSelectionne et toggleSelectionner en mode individuel', async () => {
    const wrapper = mountComponent()

    wrapper.vm.toggleSelectionner(baseParticipants[0])
    expect(wrapper.emitted('update:modelValue').at(-1)[0]).toEqual([baseParticipants[0]])
    await wrapper.setProps({ modelValue: [baseParticipants[0]] })
    expect(wrapper.vm.estSelectionne(1)).toBe(true)

    wrapper.vm.toggleSelectionner(baseParticipants[1])
    expect(wrapper.emitted('update:modelValue').at(-1)[0]).toEqual([baseParticipants[0]])
    await wrapper.setProps({ modelValue: [baseParticipants[0]] })

    wrapper.vm.toggleSelectionner(baseParticipants[0])
    expect(wrapper.emitted('update:modelValue').at(-1)[0]).toEqual([])
    await wrapper.setProps({ modelValue: [] })

    expect(wrapper.vm.estSelectionne(1)).toBe(false)
    wrapper.vm.toggleSelectionner(baseParticipants[0])
    expect(wrapper.emitted('update:modelValue').at(-1)[0]).toEqual([baseParticipants[0]])
  })

  // Ouvre et ferme la popup formulaire avec reset
  test('ouvrirFormulaire et fermerFormulaire pilotent etat et reset', () => {
    const wrapper = mountComponent()
    wrapper.vm.emailRecherche = 'a@b.ch'
    wrapper.vm.participantTrouve = { id: 9 }
    wrapper.vm.erreurRecherche = 'Erreur'
    wrapper.vm.form.nom = 'X'

    wrapper.vm.ouvrirFormulaire()
    expect(wrapper.vm.formulaireOuvert).toBe(true)

    wrapper.vm.fermerFormulaire()
    expect(wrapper.vm.formulaireOuvert).toBe(false)
    expect(wrapper.vm.emailRecherche).toBe('')
    expect(wrapper.vm.participantTrouve).toBeNull()
    expect(wrapper.vm.erreurRecherche).toBeNull()
    expect(wrapper.vm.form.nom).toBe('')
  })

  // Recherche participant par email
  test('lancerRecherche gere succes erreur et email vide', async () => {
    const wrapper = mountComponent()

    wrapper.vm.emailRecherche = ''
    await wrapper.vm.lancerRecherche()
    expect(api.get).not.toHaveBeenCalled()

    wrapper.vm.emailRecherche = 'rita@test.ch'
    await wrapper.vm.lancerRecherche()
    expect(api.get).toHaveBeenCalledWith('/participant/rechercher-participant', {
      params: { email: 'rita@test.ch' },
    })
    expect(wrapper.vm.participantTrouve?.id).toBe(9)

    api.get.mockRejectedValueOnce(new Error('Not found'))
    wrapper.vm.emailRecherche = 'x@test.ch'
    await wrapper.vm.lancerRecherche()
    expect(wrapper.vm.erreurRecherche).toContain('Aucun participant')
  })

  // Integre participant trouve en groupe
  test('selectionnerTrouve ajoute au groupe puis ferme le formulaire', () => {
    const wrapper = mountComponent({ typeSelectionne: { id: 'groupe', nom: 'Groupe (2-4)' } })
    wrapper.vm.formulaireOuvert = true
    wrapper.vm.participantTrouve = { id: 9, prenom: 'Rita', nom: 'Lopez' }

    wrapper.vm.selectionnerTrouve()

    expect(wrapper.vm.groupeData.participants.some((p) => p.id === 9)).toBe(true)
    expect(wrapper.vm.formulaireOuvert).toBe(false)
  })

  // Integre participant trouve en mode individuel
  test('selectionnerTrouve emet creer-participant en mode individuel', () => {
    const wrapper = mountComponent()
    wrapper.vm.formulaireOuvert = true
    wrapper.vm.participantTrouve = { id: 9, prenom: 'Rita', nom: 'Lopez' }

    wrapper.vm.selectionnerTrouve()

    expect(wrapper.emitted('creer-participant')).toBeTruthy()
    expect(wrapper.vm.formulaireOuvert).toBe(false)
  })

  // Cree un participant via service puis l ajoute selon le mode
  test('valider cree participant en DB puis met a jour selection', async () => {
    const wrapper = mountComponent()
    wrapper.vm.formulaireOuvert = true
    wrapper.vm.form.nom = 'New'
    wrapper.vm.form.prenom = 'Person'

    await wrapper.vm.valider()

    expect(participantService.creerParticipant).toHaveBeenCalled()
    expect(wrapper.emitted('creer-participant')).toBeTruthy()
    expect(wrapper.vm.formulaireOuvert).toBe(false)
  })

  // Fallback local si creation DB echoue
  test('valider cree localement en fallback si erreur API', async () => {
    vi.spyOn(Date, 'now').mockReturnValue(12345)
    participantService.creerParticipant.mockRejectedValueOnce(new Error('API down'))

    const wrapper = mountComponent({ typeSelectionne: { id: 'groupe', nom: 'Groupe (2-4)' } })
    wrapper.vm.form.nom = 'Local'
    wrapper.vm.form.prenom = 'Only'

    await wrapper.vm.valider()

    expect(wrapper.vm.groupeData.participants.some((p) => p.id === 12345)).toBe(true)
  })

  // Ignore valider si formulaire invalide
  test('valider quitte immediatement si formulaire invalide', async () => {
    const wrapper = mountComponent()
    wrapper.vm.form.nom = ''
    wrapper.vm.form.prenom = ''

    await wrapper.vm.valider()

    expect(participantService.creerParticipant).not.toHaveBeenCalled()
  })

  // Watcher typeSelectionne gere transitions groupe/challenge/autres
  test('watch typeSelectionne initialise et reset les etats selon le type', async () => {
    const wrapper = mountComponent({ typeSelectionne: { id: 'solo', nom: 'Solo' } })

    await wrapper.setProps({ typeSelectionne: { id: 'groupe', nom: 'Groupe (2-4)' } })
    expect(wrapper.vm.groupeData.participants).toEqual(baseParticipants)
    expect(wrapper.emitted('update:groupeValue')).toBeTruthy()

    await wrapper.setProps({ typeSelectionne: { id: 'challenge', nom: 'Challenge' } })
    expect(wrapper.vm.groupeData.participants).toEqual([])
    expect(wrapper.vm.challengeData.typeOrganisation).toBeNull()
    expect(wrapper.emitted('update:groupeValue').at(-1)[0]).toBeNull()
  })
})
