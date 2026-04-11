import { describe, test, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    template: '<span data-test="icon"></span>',
  },
}))

vi.mock('../../services/groupeService', () => ({
  default: {
    createGroupe: vi.fn(),
    addParticipant: vi.fn(),
    verifierCode: vi.fn(),
  },
}))

vi.mock('../../components/IndicateurEtapes.vue', () => ({
  default: {
    name: 'IndicateurEtapes',
    props: ['steps', 'currentStep'],
    template: '<div data-test="indicateur-etapes"></div>',
  },
}))
vi.mock('../../components/PopupAvertissementCourse.vue', () => ({
  default: {
    name: 'PopupAvertissementCourse',
    props: ['texte'],
    emits: ['confirmer', 'close'],
    template: '<div data-test="popup-avertissement"></div>',
  },
}))
vi.mock('../../components/EtapeParametre.vue', () => ({
  default: { name: 'EtapeParametre', template: '<div data-test="etape-parametre"></div>', props: ['course', 'modelValue'] },
}))
vi.mock('../../components/EtapeParticipant.vue', () => ({
  default: {
    name: 'EtapeParticipant',
    template: '<div data-test="etape-participant"></div>',
    props: ['participants', 'chargement', 'typeSelectionne', 'courseId', 'modelValue', 'groupeValue'],
  },
}))
vi.mock('../../components/EtapeOptions.vue', () => ({
  default: { name: 'EtapeOptions', template: '<div data-test="etape-options"></div>', props: ['options', 'modelValue'] },
}))
vi.mock('../../components/EtapeDocument.vue', () => ({
  default: { name: 'EtapeDocument', template: '<div data-test="etape-document"></div>', props: ['description_document', 'modelValue'] },
}))
vi.mock('../../components/EtapeQuestionnaire.vue', () => ({
  default: { name: 'EtapeQuestionnaire', template: '<div data-test="etape-questionnaire"></div>', props: ['questions', 'modelValue'] },
}))
vi.mock('../../components/EtapePanier.vue', () => ({
  default: { name: 'EtapePanier', template: '<div data-test="etape-panier"></div>', props: ['codeParticipation'] },
}))

import PopupInscriptionCourse from '../../components/PopupInscriptionCourse.vue'
import groupeService from '../../services/groupeService'

const baseCourse = {
  id: 77,
  nom_course: 'Trail 10K',
  tarif: '40',
  type: 'Individuel',
  categorie: 'Running',
  sous_categorie: '10 km',
  options: [],
  document: false,
  document_description: '',
  questionnaire: [],
  avertissement: null,
  evenement: {
    nom: 'Run Geneva',
    couleur_primaire: '#111111',
    couleur_secondaire: '#eeeeee',
  },
}

const baseParticipants = [
  { id: 1, prenom: 'Alice', nom: 'Dupont' },
  { id: 2, prenom: 'Bob', nom: 'Martin' },
]

function mountComponent(customProps = {}) {
  return mount(PopupInscriptionCourse, {
    props: {
      course: baseCourse,
      participants: baseParticipants,
      chargementParticipants: false,
      ...customProps,
    },
  })
}

describe('PopupInscriptionCourse', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    groupeService.createGroupe.mockResolvedValue({ data: { id: 999 } })
    groupeService.addParticipant.mockResolvedValue({})
    groupeService.verifierCode.mockResolvedValue({ data: { groupe: { id: 42, nom: 'Entreprise X' } } })
  })

  // Initialise la modal directement sur inscription sans avertissement
  test('ouvre la modal inscription si aucun avertissement', () => {
    const wrapper = mountComponent()

    expect(wrapper.vm.modalAffichage).toBe(wrapper.vm.modals.INSCRIPTION)
    expect(wrapper.find('[data-test="popup-avertissement"]').exists()).toBe(false)
  })

  // Ouvre l avertissement si la course en contient un
  test('ouvre l avertissement quand la course en contient un', async () => {
    const wrapper = mountComponent({
      course: {
        ...baseCourse,
        avertissement: { contenu: 'Attention' },
      },
    })
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.modalAffichage).toBe(wrapper.vm.modals.AVERTISSEMENT)
    expect(wrapper.find('[data-test="popup-avertissement"]').exists()).toBe(true)
  })

  // Derive les participants a afficher en dedoublonnant les membres temporaires
  test('tousLesParticipants fusionne participants et groupeEphemere sans doublon', () => {
    const wrapper = mountComponent()
    wrapper.vm.inscription.groupeEphemere = {
      participants: [
        { id: 2, prenom: 'Bob', nom: 'Martin' },
        { id: 3, prenom: 'Nina', nom: 'Ray' },
      ],
    }

    expect(wrapper.vm.tousLesParticipants.map((p) => p.id)).toEqual([1, 2, 3])
  })

  // Signale correctement une course de type groupe
  test('estCourseGroupe est vrai si la course est de type Groupe', () => {
    const wrapper = mountComponent({
      course: {
        ...baseCourse,
        type: 'Groupe',
      },
    })

    expect(wrapper.vm.estCourseGroupe).toBe(true)
  })

  // Construit les etapes actives selon options documents et questionnaire
  test('etapesActives inclut toutes les sections configurees', () => {
    const wrapper = mountComponent({
      course: {
        ...baseCourse,
        type: 'Groupe',
        options: [{ id: 1, nom: 'Repas' }],
        document: true,
        questionnaire: [{ id: 9, question: 'Q1' }],
      },
    })

    expect(wrapper.vm.etapesActives).toEqual([
      wrapper.vm.formulaireEtape.PARAMETRE,
      wrapper.vm.formulaireEtape.PARTICIPANTS,
      wrapper.vm.formulaireEtape.OPTIONS,
      wrapper.vm.formulaireEtape.DOCUMENT,
      wrapper.vm.formulaireEtape.QUESTIONNAIRE,
      wrapper.vm.formulaireEtape.CONFIRMATION,
    ])
    expect(wrapper.vm.formulaireEtapesLabels).toEqual(['Type', 'Groupe', 'Options', 'Documents', 'Questionnaire', 'Panier'])
  })

  // Detecte la derniere etape du tunnel
  test('estDerniereEtape devient vrai sur letape finale', () => {
    const wrapper = mountComponent()
    wrapper.vm.etape = wrapper.vm.formulaireEtape.CONFIRMATION

    expect(wrapper.vm.estDerniereEtape).toBe(true)
  })

  // Valide la continuation selon les regles des etapes
  test('peutContinuer applique les regles solo groupe relais challenge', () => {
    const wrapper = mountComponent({
      course: {
        ...baseCourse,
        type: 'Groupe',
      },
    })

    wrapper.vm.etape = wrapper.vm.formulaireEtape.PARAMETRE
    expect(wrapper.vm.peutContinuer).toBe(false)
    wrapper.vm.inscription.type = { id: 'solo', nom: 'Solo' }
    expect(wrapper.vm.peutContinuer).toBe(true)

    wrapper.vm.etape = wrapper.vm.formulaireEtape.PARTICIPANTS
    wrapper.vm.inscription.participant = []
    expect(wrapper.vm.peutContinuer).toBe(false)
    wrapper.vm.inscription.participant = [{ id: 1 }]
    expect(wrapper.vm.peutContinuer).toBe(true)

    wrapper.vm.inscription.type = { id: 'groupe', nom: 'Groupe (2-4)' }
    wrapper.vm.inscription.groupeEphemere = { nom: 'Team', participants: [{ id: 1 }] }
    expect(wrapper.vm.peutContinuer).toBe(false)
    wrapper.vm.inscription.groupeEphemere.participants.push({ id: 2 })
    expect(wrapper.vm.peutContinuer).toBe(true)

    wrapper.vm.inscription.type = { id: 'relais', nom: 'Relais' }
    wrapper.vm.inscription.groupeEphemere = { nom: 'Relay', participants: [{ id: 1 }, { id: 2 }] }
    expect(wrapper.vm.peutContinuer).toBe(true)
    wrapper.vm.inscription.groupeEphemere.participants.push({ id: 3 })
    expect(wrapper.vm.peutContinuer).toBe(false)

    wrapper.vm.inscription.type = { id: 'challenge', nom: 'Challenge' }
    wrapper.vm.inscription.groupeEphemere = { nom: 'Org' }
    wrapper.vm.inscription.participant = [{ id: 1 }]
    expect(wrapper.vm.peutContinuer).toBe(true)
  })

  // Formate les options et calcule le total
  test('optionsSelectionnees totalInscription choixOptionsPourPanier et reponsesPourPanier', () => {
    const wrapper = mountComponent()
    wrapper.vm.inscription.options = {
      5: { option: { id: 5, type: 'Quantifiable', tarif: 10 }, quantite: 3 },
      6: { option: { id: 6, type: 'Cochable', tarif: 5 }, quantite: 1 },
      7: { option: { id: 7, type: 'Question', tarif: 2 }, quantite: 1 },
    }
    wrapper.vm.inscription.reponses = {
      8: { reponse: { id: 88 } },
    }

    expect(wrapper.vm.optionsSelectionnees).toHaveLength(3)
    expect(wrapper.vm.totalInscription).toBe(77)
    expect(wrapper.vm.choixOptionsPourPanier).toEqual([
      { id_option: 5, quantite: 3 },
      { id_option: 6, quantite: 1 },
      { id_option: 7, quantite: 0 },
    ])
    expect(wrapper.vm.reponsesPourPanier).toEqual([{ id_question: 8, id_option_choisie: 88 }])
  })

  // Bloque la progression tant qu un code entreprise n est pas valide
  test('codeBloquant depend du code saisi et de la validation entreprise', () => {
    const wrapper = mountComponent()

    wrapper.vm.inscription.codeParticipation = ''
    expect(wrapper.vm.codeBloquant).toBe(false)

    wrapper.vm.inscription.codeParticipation = 'ABC123'
    expect(wrapper.vm.codeBloquant).toBe(true)

    wrapper.vm.entrepriseValidee = { id: 42, nom: 'Entreprise X' }
    expect(wrapper.vm.codeBloquant).toBe(false)
  })

  // Reinitialise les etats code via le watcher
  test('watch inscription.codeParticipation reinitialise entrepriseValidee et erreurCode', async () => {
    const wrapper = mountComponent()
    wrapper.vm.entrepriseValidee = { id: 42 }
    wrapper.vm.erreurCode = 'ancienne erreur'

    wrapper.vm.inscription.codeParticipation = 'NOUVEAU'
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.entrepriseValidee).toBeNull()
    expect(wrapper.vm.erreurCode).toBeNull()
  })

  // Ajoute et dedoublonne les participants supplementaires
  test('ajouterParticipantSupplementaire dedoublonne les ajouts', () => {
    const wrapper = mountComponent()

    wrapper.vm.ajouterParticipantSupplementaire({ id: 33, prenom: 'Nina', nom: 'Ray' })
    wrapper.vm.ajouterParticipantSupplementaire({ id: 33, prenom: 'Nina', nom: 'Ray' })

    expect(wrapper.vm.participantsSupplementaires).toHaveLength(1)
  })

  // Vérifie un code entreprise valide
  test('verifierCodeEntreprise valide un code correct', async () => {
    const wrapper = mountComponent()
    wrapper.vm.inscription.codeParticipation = 'ABC123'

    await wrapper.vm.verifierCodeEntreprise()

    expect(groupeService.verifierCode).toHaveBeenCalledWith('ABC123')
    expect(wrapper.vm.entrepriseValidee.nom).toBe('Entreprise X')
    expect(wrapper.vm.erreurCode).toBeNull()
  })

  // Retourne une erreur si le code entreprise est invalide
  test('verifierCodeEntreprise renseigne une erreur si code invalide', async () => {
    groupeService.verifierCode.mockRejectedValue({ response: { data: { message: 'Code invalide.' } } })
    const wrapper = mountComponent()
    wrapper.vm.inscription.codeParticipation = 'BAD'

    await wrapper.vm.verifierCodeEntreprise()

    expect(wrapper.vm.entrepriseValidee).toBeNull()
    expect(wrapper.vm.erreurCode).toBe('Code invalide.')
  })

  // Ignore la verification quand aucun code nest renseigne
  test('verifierCodeEntreprise nettoie sans appel service si code vide', async () => {
    const wrapper = mountComponent()
    wrapper.vm.entrepriseValidee = { id: 42 }
    wrapper.vm.erreurCode = 'Erreur precedente'
    wrapper.vm.inscription.codeParticipation = '   '

    await wrapper.vm.verifierCodeEntreprise()

    expect(groupeService.verifierCode).not.toHaveBeenCalled()
    expect(wrapper.vm.entrepriseValidee).toBeNull()
    expect(wrapper.vm.erreurCode).toBeNull()
  })

  // Passe à l étape suivante lorsque la validation est satisfaite
  test('etapeSuivante avance dans le tunnel', async () => {
    const wrapper = mountComponent()
    wrapper.vm.inscription.type = { id: 'solo', nom: 'Solo' }

    expect(wrapper.vm.etape).toBe(wrapper.vm.formulaireEtape.PARAMETRE)
    await wrapper.vm.etapeSuivante()

    expect(wrapper.vm.etape).toBe(wrapper.vm.formulaireEtape.PARTICIPANTS)
  })

  // Ne progresse pas quand la validation de letape courante est invalide
  test('etapeSuivante est bloquee si peutContinuer est faux', async () => {
    const wrapper = mountComponent()

    wrapper.vm.etape = wrapper.vm.formulaireEtape.PARAMETRE
    wrapper.vm.inscription.type = null
    await wrapper.vm.etapeSuivante()

    expect(wrapper.vm.etape).toBe(wrapper.vm.formulaireEtape.PARAMETRE)
    expect(wrapper.emitted('ajouter-panier')).toBeFalsy()
  })

  // Revient à l étape précédente
  test('etapePrecedente recule dune etape', () => {
    const wrapper = mountComponent()
    wrapper.vm.etape = wrapper.vm.formulaireEtape.PARTICIPANTS

    wrapper.vm.etapePrecedente()

    expect(wrapper.vm.etape).toBe(wrapper.vm.formulaireEtape.PARAMETRE)
  })

  // Émet l inscription vers le panier en fin de parcours simple
  test('etapeSuivante emet ajouter-panier en fin de parcours', async () => {
    const wrapper = mountComponent()
    wrapper.vm.etape = wrapper.vm.formulaireEtape.CONFIRMATION
    wrapper.vm.inscription.type = { id: 'solo', nom: 'Solo' }
    wrapper.vm.inscription.participant = [{ id: 1, prenom: 'Alice', nom: 'Dupont' }]
    wrapper.vm.inscription.options = {
      5: { option: { id: 5, nom: 'Repas', type: 'Cochable', tarif: 10 }, quantite: 1 },
    }
    wrapper.vm.inscription.reponses = {
      7: { reponse: { id: 88 } },
    }

    await wrapper.vm.etapeSuivante()

    const emitted = wrapper.emitted('ajouter-panier')
    expect(emitted).toBeTruthy()
    expect(emitted[0][0]).toMatchObject({
      tarif: 50,
      id_groupe: null,
    })
    expect(emitted[0][0].choix_options).toEqual([{ id_option: 5, quantite: 1 }])
    expect(emitted[0][0].reponses_questions).toEqual([{ id_question: 7, id_option_choisie: 88 }])
  })

  // Crée un groupe en mode groupe puis émet les données panier
  test('etapeSuivante cree un groupe et rattache les membres', async () => {
    const wrapper = mountComponent()
    wrapper.vm.etape = wrapper.vm.formulaireEtape.CONFIRMATION
    wrapper.vm.inscription.type = { id: 'groupe', nom: 'Groupe (2-4)' }
    wrapper.vm.inscription.groupeEphemere = {
      nom: 'Team Flash',
      participants: [
        { id: 1, prenom: 'Alice', nom: 'Dupont' },
        { id: 1000000000001, prenom: 'Temp', nom: 'Local' },
      ],
    }

    await wrapper.vm.etapeSuivante()
    await flushPromises()

    expect(groupeService.createGroupe).toHaveBeenCalledWith({
      nom: 'Team Flash',
      type: 'Groupe',
      id_course: 77,
    })
    expect(groupeService.addParticipant).toHaveBeenCalledTimes(1)
    expect(groupeService.addParticipant).toHaveBeenCalledWith(999, 1)

    const emitted = wrapper.emitted('ajouter-panier')
    expect(emitted).toBeTruthy()
    expect(emitted[0][0].id_groupe).toBe(999)
    expect(emitted[0][0].nom_equipe).toBe('Team Flash')
  })

  // Cree un groupe challenge en utilisant le type fourni
  test('etapeSuivante cree un groupe challenge avec type_groupe', async () => {
    const wrapper = mountComponent()
    wrapper.vm.etape = wrapper.vm.formulaireEtape.CONFIRMATION
    wrapper.vm.inscription.type = { id: 'challenge', nom: 'Challenge' }
    wrapper.vm.inscription.groupeEphemere = {
      nom: 'Entreprise Y',
      type_groupe: 'Entreprise',
      participants: [{ id: 1, prenom: 'Alice', nom: 'Dupont' }],
    }
    wrapper.vm.inscription.participant = [{ id: 1, prenom: 'Alice', nom: 'Dupont' }]

    await wrapper.vm.etapeSuivante()

    expect(groupeService.createGroupe).toHaveBeenCalledWith({
      nom: 'Entreprise Y',
      type: 'Entreprise',
      id_course: 77,
    })
    expect(wrapper.emitted('ajouter-panier')).toBeTruthy()
  })

  // Priorise le groupe valide par code entreprise dans le payload final
  test('etapeSuivante utilise entrepriseValidee comme id_groupe final', async () => {
    const wrapper = mountComponent()
    wrapper.vm.etape = wrapper.vm.formulaireEtape.CONFIRMATION
    wrapper.vm.inscription.type = { id: 'solo', nom: 'Solo' }
    wrapper.vm.inscription.participant = [{ id: 1, prenom: 'Alice', nom: 'Dupont' }]
    wrapper.vm.entrepriseValidee = { id: 42, nom: 'Entreprise X' }

    await wrapper.vm.etapeSuivante()

    const emitted = wrapper.emitted('ajouter-panier')
    expect(emitted[0][0].id_groupe).toBe(42)
    expect(emitted[0][0].tarif).toBe(0)
  })

  // Affiche une erreur utilisateur quand la création du groupe échoue par contrainte unique
  test('etapeSuivante affiche une erreur explicite si nom de groupe deja pris', async () => {
    groupeService.createGroupe.mockRejectedValue({
      response: {
        status: 500,
        data: { message: 'UNIQUE constraint failed' },
      },
    })
    const wrapper = mountComponent()
    wrapper.vm.etape = wrapper.vm.formulaireEtape.CONFIRMATION
    wrapper.vm.inscription.type = { id: 'groupe', nom: 'Groupe (2-4)' }
    wrapper.vm.inscription.groupeEphemere = {
      nom: 'Team Flash',
      participants: [{ id: 1, prenom: 'Alice', nom: 'Dupont' }],
    }

    await wrapper.vm.etapeSuivante()

    expect(wrapper.vm.erreurGroupe).toContain('Un groupe avec ce nom existe déjà')
    expect(wrapper.emitted('ajouter-panier')).toBeFalsy()
  })
})