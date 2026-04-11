import { describe, test, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

vi.mock('../../services/groupeService', () => ({
  default: {
    updateGroupe: vi.fn(),
    removeParticipant: vi.fn(),
    addParticipant: vi.fn(),
    getGroupe: vi.fn(),
  },
}))

vi.mock('../../services/api', () => ({
  default: {
    get: vi.fn(),
  },
}))

vi.mock('../../stores/auth', () => ({
  useAuthStore: () => ({
    user: { participant: { id: 10 } },
  }),
}))

vi.mock('../../components/PopupConfirmation.vue', () => ({
  default: {
    name: 'PopupConfirmation',
    props: ['message', 'icon'],
    emits: ['confirm', 'cancel'],
    template: '<div data-test="popup-confirmation"></div>',
  },
}))

import PopupGestionGroupe from '../../components/PopupGestionGroupe.vue'
import groupeService from '../../services/groupeService'
import api from '../../services/api'

const baseGroupe = {
  id: 1,
  nom: 'Team Flash',
  type: 'Groupe',
  course: {
    nom: 'Trail 10K',
    type: 'Groupe',
    fin_inscription: '2099-01-01',
    max_nb_personne: 4,
  },
  participants: [
    { id: 10, prenom: 'Alice', nom: 'Dupont', pivot: { statut: 'Fondateur' } },
    { id: 11, prenom: 'Bob', nom: 'Martin', pivot: { statut: 'Membre' } },
  ],
}

const mesParticipants = [
  { id: 10, prenom: 'Alice', nom: 'Dupont' },
  { id: 11, prenom: 'Bob', nom: 'Martin' },
  { id: 12, prenom: 'Nina', nom: 'Ray' },
]

function mountComponent(customProps = {}) {
  return mount(PopupGestionGroupe, {
    props: {
      groupe: baseGroupe,
      mesParticipants,
      ...customProps,
    },
  })
}

describe('PopupGestionGroupe', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    groupeService.updateGroupe.mockResolvedValue({})
    groupeService.removeParticipant.mockResolvedValue({})
    groupeService.addParticipant.mockResolvedValue({})
    groupeService.getGroupe.mockResolvedValue({
      data: {
        ...baseGroupe,
        participants: [
          { id: 10, prenom: 'Alice', nom: 'Dupont', pivot: { statut: 'Fondateur' } },
          { id: 12, prenom: 'Nina', nom: 'Ray', pivot: { statut: 'Membre' } },
        ],
      },
    })
    api.get.mockResolvedValue({ data: { id: 12, prenom: 'Nina', nom: 'Ray' } })
  })

  // Evalue correctement les drapeaux de fermeture et de type
  test('calcule les etats derives du groupe', () => {
    const wrapper = mountComponent()

    expect(wrapper.vm.inscriptionsFermees).toBe(false)
    expect(wrapper.vm.estGroupe).toBe(true)
    expect(wrapper.vm.estRelais).toBe(false)
    expect(wrapper.vm.estChallenge).toBe(false)
    expect(wrapper.vm.peutModifierNom).toBe(true)
    expect(wrapper.vm.peutAjouter).toBe(true)
  })

  // Detecte les inscriptions fermees quand la date est depassee
  test('inscriptionsFermees passe a true apres la date limite', () => {
    const wrapper = mountComponent({
      groupe: {
        ...baseGroupe,
        course: { ...baseGroupe.course, fin_inscription: '2000-01-01' },
      },
    })

    expect(wrapper.vm.inscriptionsFermees).toBe(true)
  })

  // Expose les valeurs derivees typeCourse, typeLabel et maxMembres
  test('typeCourse typeLabel et maxMembres sont coherents', () => {
    const wrapper = mountComponent()

    expect(wrapper.vm.typeCourse).toBe('Groupe')
    expect(wrapper.vm.typeLabel).toBe('Groupe')
    expect(wrapper.vm.maxMembres).toBe(4)
  })

  // Derive le label challenge si le groupe est de type entreprise
  test('typeLabel devient Challenge pour un groupe entreprise', () => {
    const wrapper = mountComponent({
      groupe: {
        ...baseGroupe,
        type: 'Entreprise',
      },
    })

    expect(wrapper.vm.estChallenge).toBe(true)
    expect(wrapper.vm.typeLabel).toBe('Challenge')
    expect(wrapper.vm.peutModifierNom).toBe(false)
  })

  // Filtre les participants deja presents dans le groupe
  test('participantsDisponibles exclut les membres deja dans le groupe', () => {
    const wrapper = mountComponent()

    expect(wrapper.vm.participantsDisponibles).toEqual([
      { id: 12, prenom: 'Nina', nom: 'Ray' },
    ])
  })

  // Retourne l identifiant du participant authentifie
  test('monId lit l id depuis le store auth', () => {
    const wrapper = mountComponent()

    expect(wrapper.vm.monId()).toBe(10)
  })

  // Traduit correctement les roles des membres
  test('estFondateur et roleLabel gerent les statuts', () => {
    const wrapper = mountComponent()

    expect(wrapper.vm.estFondateur(baseGroupe.participants[0])).toBe(true)
    expect(wrapper.vm.roleLabel(baseGroupe.participants[0])).toBe('Fondateur')
    expect(wrapper.vm.roleLabel({ pivot: { statut: 'En attente' } })).toBe('En attente')
    expect(wrapper.vm.roleLabel({ pivot: { statut: 'Membre' } })).toBe('Membre')
  })

  // Sauvegarde le nom et emet la mise a jour
  test('sauvegarderNom met a jour le groupe', async () => {
    const wrapper = mountComponent()
    wrapper.vm.nomGroupe = 'Team Nova'

    await wrapper.vm.sauvegarderNom()

    expect(groupeService.updateGroupe).toHaveBeenCalledWith(1, { nom: 'Team Nova' })
    expect(wrapper.vm.groupeLocal.nom).toBe('Team Nova')
    expect(wrapper.vm.messageNom.type).toBe('ok')
    expect(wrapper.emitted('mis-a-jour')).toBeTruthy()
  })

  // Affiche un message d erreur si la sauvegarde echoue
  test('sauvegarderNom gere les erreurs', async () => {
    groupeService.updateGroupe.mockRejectedValue(new Error('Erreur reseau'))
    const wrapper = mountComponent()
    wrapper.vm.nomGroupe = 'Team Nova'

    await wrapper.vm.sauvegarderNom()

    expect(wrapper.vm.messageNom.type).toBe('erreur')
  })

  // Ignore la sauvegarde si le nom est vide
  test('sauvegarderNom ignore un nom vide', async () => {
    const wrapper = mountComponent()
    wrapper.vm.nomGroupe = '   '

    await wrapper.vm.sauvegarderNom()

    expect(groupeService.updateGroupe).not.toHaveBeenCalled()
  })

  // Ouvre puis reinitialise le formulaire de remplacement
  test('ouvrirRemplacement puis annulerFormulaire reinitialisent letat', () => {
    const wrapper = mountComponent()

    wrapper.vm.ouvrirRemplacement(baseGroupe.participants[1])
    expect(wrapper.vm.modeFormulaire).toBe(true)
    expect(wrapper.vm.membreARemplacer.id).toBe(11)

    wrapper.vm.annulerFormulaire()
    expect(wrapper.vm.modeFormulaire).toBe(false)
    expect(wrapper.vm.membreARemplacer).toBeNull()
    expect(wrapper.vm.nouveauMembre).toBeNull()
  })

  // Ouvre explicitement le formulaire dajout
  test('ouvrirAjout initialise le formulaire sans membre a remplacer', () => {
    const wrapper = mountComponent()
    wrapper.vm.membreARemplacer = baseGroupe.participants[1]

    wrapper.vm.ouvrirAjout()

    expect(wrapper.vm.modeFormulaire).toBe(true)
    expect(wrapper.vm.membreARemplacer).toBeNull()
    expect(wrapper.vm.nouveauMembre).toBeNull()
  })

  // Recherche un participant par email
  test('rechercherParEmail remplit participantTrouve', async () => {
    const wrapper = mountComponent()
    wrapper.vm.emailRecherche = 'nina@test.ch'

    await wrapper.vm.rechercherParEmail()

    expect(api.get).toHaveBeenCalledWith('/participant/rechercher-participant', {
      params: { email: 'nina@test.ch' },
    })
    expect(wrapper.vm.participantTrouve.id).toBe(12)
    expect(wrapper.vm.erreurRecherche).toBeNull()
  })

  // Remonte une erreur de recherche si aucun participant n est trouve
  test('rechercherParEmail remonte une erreur en cas dechec', async () => {
    api.get.mockRejectedValue(new Error('Not found'))
    const wrapper = mountComponent()
    wrapper.vm.emailRecherche = 'inconnu@test.ch'

    await wrapper.vm.rechercherParEmail()

    expect(wrapper.vm.participantTrouve).toBeNull()
    expect(wrapper.vm.erreurRecherche).toContain('Aucun participant trouvé')
  })

  // Ignore la recherche si lemail est vide
  test('rechercherParEmail ignore un email vide', async () => {
    const wrapper = mountComponent()
    wrapper.vm.emailRecherche = ''

    await wrapper.vm.rechercherParEmail()

    expect(api.get).not.toHaveBeenCalled()
  })

  // Remplace un membre puis recharge le groupe depuis l API
  test('confirmerAction remplace un membre puis emet mis-a-jour', async () => {
    const wrapper = mountComponent()
    wrapper.vm.ouvrirRemplacement(baseGroupe.participants[1])
    wrapper.vm.nouveauMembre = { id: 12, prenom: 'Nina', nom: 'Ray' }

    await wrapper.vm.confirmerAction()
    await flushPromises()

    expect(groupeService.removeParticipant).toHaveBeenCalledWith(1, 11)
    expect(groupeService.addParticipant).toHaveBeenCalledWith(1, 12)
    expect(groupeService.getGroupe).toHaveBeenCalledWith(1)
    expect(wrapper.vm.modeFormulaire).toBe(false)
    expect(wrapper.emitted('mis-a-jour')).toBeTruthy()
  })

  // Ajoute un membre sans remplacement
  test('confirmerAction ajoute un membre quand aucun remplacement', async () => {
    const wrapper = mountComponent()
    wrapper.vm.ouvrirAjout()
    wrapper.vm.nouveauMembre = { id: 12, prenom: 'Nina', nom: 'Ray' }

    await wrapper.vm.confirmerAction()

    expect(groupeService.removeParticipant).not.toHaveBeenCalled()
    expect(groupeService.addParticipant).toHaveBeenCalledWith(1, 12)
    expect(wrapper.vm.messageAction.type).toBe('ok')
  })

  // N effectue aucune action si aucun nouveau membre n est selectionne
  test('confirmerAction quitte immediatement sans nouveau membre', async () => {
    const wrapper = mountComponent()

    await wrapper.vm.confirmerAction()

    expect(groupeService.addParticipant).not.toHaveBeenCalled()
    expect(groupeService.removeParticipant).not.toHaveBeenCalled()
  })

  // Remonte un message d erreur si l ajout echoue
  test('confirmerAction gere une erreur service', async () => {
    groupeService.addParticipant.mockRejectedValue(new Error('Erreur API'))
    const wrapper = mountComponent()
    wrapper.vm.ouvrirAjout()
    wrapper.vm.nouveauMembre = { id: 12, prenom: 'Nina', nom: 'Ray' }

    await wrapper.vm.confirmerAction()

    expect(wrapper.vm.messageAction.type).toBe('erreur')
    expect(wrapper.vm.enCours).toBe(false)
  })

  // Ouvre la confirmation de retrait puis retire le membre
  test('retirerMembre et confirmerRetraitMembre mettent a jour le groupe', async () => {
    const wrapper = mountComponent()

    await wrapper.vm.retirerMembre(baseGroupe.participants[1])
    expect(wrapper.vm.membreARetirer.id).toBe(11)
    expect(wrapper.find('[data-test="popup-confirmation"]').exists()).toBe(true)

    await wrapper.vm.confirmerRetraitMembre()

    expect(groupeService.removeParticipant).toHaveBeenCalledWith(1, 11)
    expect(groupeService.getGroupe).toHaveBeenCalledWith(1)
    expect(wrapper.vm.membreARetirer).toBeNull()
    expect(wrapper.emitted('mis-a-jour')).toBeTruthy()
  })

  // Ne retire rien si aucun membre n est selectionne
  test('confirmerRetraitMembre quitte immediatement sans cible', async () => {
    const wrapper = mountComponent()

    await wrapper.vm.confirmerRetraitMembre()

    expect(groupeService.removeParticipant).not.toHaveBeenCalled()
  })
})