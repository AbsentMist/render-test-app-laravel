import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    template: '<span data-test="icon"></span>',
  },
}))

vi.mock('../../components/PopupChangementCourseParticipant.vue', () => ({
  default: {
    name: 'PopupChangementCourseParticipant',
    props: ['inscription', 'participants'],
    emits: ['close', 'confirmer'],
    template: '<div data-test="popup-changement-course"></div>',
  },
}))

vi.mock('../../components/PopupConfirmation.vue', () => ({
  default: {
    name: 'PopupConfirmation',
    props: ['message', 'icon'],
    emits: ['confirm', 'cancel'],
    template: '<div data-test="popup-confirmation"></div>',
  },
}))

vi.mock('../../services/courseParticipantService', () => ({
  default: {
    getCourse: vi.fn(),
  },
}))

vi.mock('../../services/documentService', () => ({
  default: {
    getDocumentsByInscription: vi.fn(),
    uploadDocument: vi.fn(),
    downloadDocument: vi.fn(),
    deleteDocument: vi.fn(),
  },
}))

vi.mock('../../services/inscriptionService', () => ({
  default: {
    updateInscription: vi.fn(),
  },
}))

import PopupInscriptionDetailParticipant from '../../components/PopupInscriptionDetailParticipant.vue'
import courseParticipantService from '../../services/courseParticipantService'
import documentService from '../../services/documentService'
import inscriptionService from '../../services/inscriptionService'

const baseInscription = {
  id: 101,
  id_course: 77,
  status_paiement: 'Validé',
  tarif: 40,
  date_paiement: '2026-01-10',
  montant_rabais: 0,
  ref_groupage: null,
  participe_challenge: false,
  type_challenge: null,
  code_participant: 'ABC-123',
  avertissement_valide: true,
  choix_options: [
    { id_option: 5, id_inscription: 101, quantite: 2 },
  ],
  reponses_questions: [
    { id_question: 9, id_option_choisie: 3 },
  ],
  documents_fournis: [
    { id: 201, url: 'https://cdn/doc-a.pdf' },
    { id: 202, url: 'https://cdn/doc-b.pdf' },
  ],
  participant: {
    id: 1,
    prenom: 'Alice',
    nom: 'Dupont',
    equipe_nom: null,
  },
  groupe: null,
  course: {
    id: 77,
    nom: 'Trail 10K',
    distance: 10,
    type: 'Individuel',
    date_debut: '2026-06-10',
    status: 'actif',
    tarif: 40,
    fin_inscription: '2099-01-01',
    document_description: 'Certificat medical',
    evenement: {
      couleur_secondaire: '#dddddd',
    },
  },
}

const baseParticipants = [
  { id: 1, prenom: 'Alice', nom: 'Dupont' },
]

function mountComponent(customProps = {}) {
  return mount(PopupInscriptionDetailParticipant, {
    props: {
      inscription: JSON.parse(JSON.stringify(baseInscription)),
      participants: baseParticipants,
      ...customProps,
    },
  })
}

describe('PopupInscriptionDetailParticipant', () => {
  beforeEach(() => {
    vi.clearAllMocks()

    courseParticipantService.getCourse.mockResolvedValue({
      data: {
        id: 77,
        options: [
          { id: 5, nom: 'Repas', description: 'Menu', type: 'Quantifiable', tarif: 10 },
        ],
        questionnaire: [
          {
            id: 9,
            question: 'Taille T-shirt ?',
            answers: [{ id: 3, option: 'M' }],
          },
        ],
      },
    })

    documentService.getDocumentsByInscription.mockResolvedValue({
      data: [{ id: 300, url: 'https://cdn/new.pdf' }],
    })

    documentService.uploadDocument.mockResolvedValue({
      data: { document: { id: 999, url: 'https://cdn/uploaded.pdf' } },
    })

    documentService.downloadDocument.mockResolvedValue({
      data: new Uint8Array([1, 2, 3]),
    })

    documentService.deleteDocument.mockResolvedValue({})
    inscriptionService.updateInscription.mockResolvedValue({})
  })

  afterEach(() => {
    vi.restoreAllMocks()
  })

  // Charge la course complete et les documents au montage
  test('charge les donnees initiales au montage', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    expect(courseParticipantService.getCourse).toHaveBeenCalledWith(77)
    expect(documentService.getDocumentsByInscription).toHaveBeenCalledWith(101)
    expect(wrapper.vm.coursComplet?.id).toBe(77)
    expect(wrapper.vm.inscription.documents_fournis).toHaveLength(1)
  })

  // Calcule correctement la fermeture des inscriptions
  test('inscriptionsFermees passe a true quand la date est depassee', () => {
    const wrapper = mountComponent({
      inscription: {
        ...JSON.parse(JSON.stringify(baseInscription)),
        course: {
          ...baseInscription.course,
          fin_inscription: '2000-01-01',
        },
      },
    })

    expect(wrapper.vm.inscriptionsFermees).toBe(true)
  })

  // Formate correctement une date et gere la valeur vide
  test('formatDate retourne JJ.MM.AAAA ou tiret', () => {
    const wrapper = mountComponent()

    expect(wrapper.vm.formatDate('2026-12-31')).toBe('31.12.2026')
    expect(wrapper.vm.formatDate(null)).toBe('—')
  })

  // Active et annule le mode edition local
  test('activerEdition et annulerEdition gerent l etat d edition', () => {
    const wrapper = mountComponent()

    wrapper.vm.activerEdition()
    expect(wrapper.vm.isEdit).toBe(true)
    expect(wrapper.vm.inscriptionEdit.choix_options).toEqual(baseInscription.choix_options)

    wrapper.vm.annulerEdition()
    expect(wrapper.vm.isEdit).toBe(false)
    expect(wrapper.vm.inscriptionEdit).toBeNull()
  })

  // Met a jour une inscription et emet la modification
  test('sauvegarderEdition normalise les quantites et emet modifier-inscription', async () => {
    const wrapper = mountComponent()
    wrapper.vm.activerEdition()
    wrapper.vm.inscriptionEdit.choix_options = [
      { id_option: 5, quantite: '4' },
      { id_option: 7, quantite: null },
    ]

    await wrapper.vm.sauvegarderEdition()

    expect(inscriptionService.updateInscription).toHaveBeenCalledWith(101, {
      choix_options: [
        { id_option: 5, quantite: 4 },
        { id_option: 7, quantite: null },
      ],
    })
    expect(wrapper.vm.isEdit).toBe(false)
    expect(wrapper.emitted('modifier-inscription')).toBeTruthy()
  })

  // Ajoute et retire des options en mode edition
  test('toggleOption ajoute puis retire une option', () => {
    const wrapper = mountComponent()
    wrapper.vm.activerEdition()

    wrapper.vm.toggleOption({ id: 99 })
    expect(wrapper.vm.inscriptionEdit.choix_options.some((o) => o.id_option === 99)).toBe(true)

    wrapper.vm.toggleOption({ id: 99 })
    expect(wrapper.vm.inscriptionEdit.choix_options.some((o) => o.id_option === 99)).toBe(false)
  })

  // Lit une option selectionnee depuis l inscription d origine
  test('optionSelectionnee retourne l option attendue', () => {
    const wrapper = mountComponent()

    expect(wrapper.vm.optionSelectionnee(5)).toEqual({ id_option: 5, id_inscription: 101, quantite: 2 })
    expect(wrapper.vm.optionSelectionnee(404)).toBeNull()
  })

  // Priorise l etat d edition pour l affichage des options
  test('optionSelectionneePourAffichage bascule entre source et edition', () => {
    const wrapper = mountComponent()

    expect(wrapper.vm.optionSelectionneePourAffichage(5)).toEqual({ id_option: 5, id_inscription: 101, quantite: 2 })

    wrapper.vm.activerEdition()
    wrapper.vm.inscriptionEdit.choix_options = [{ id_option: 5, quantite: 7 }]

    expect(wrapper.vm.optionSelectionneePourAffichage(5)).toEqual({ id_option: 5, quantite: 7 })
    expect(wrapper.vm.optionSelectionneePourAffichage(404)).toBeNull()
  })

  // Met a jour la quantite d une option existante
  test('mettreAJourQuantite modifie la quantite dans inscriptionEdit', () => {
    const wrapper = mountComponent()
    wrapper.vm.activerEdition()

    wrapper.vm.mettreAJourQuantite(5, '6')

    expect(wrapper.vm.getQuantiteOption(5)).toBe(6)
  })

  // Confirme la suppression d un document et met a jour la liste locale
  test('confirmerSuppressionDocument supprime le document localement', async () => {
    const wrapper = mountComponent()
    wrapper.vm.documentASupprimer = 201

    await wrapper.vm.confirmerSuppressionDocument()

    expect(documentService.deleteDocument).toHaveBeenCalledWith(201)
    expect(wrapper.vm.inscription.documents_fournis.find((d) => d.id === 201)).toBeUndefined()
    expect(wrapper.vm.documentASupprimer).toBeNull()
  })

  // Prepare la suppression en stockant l identifiant cible
  test('supprimerDocument assigne documentASupprimer', async () => {
    const wrapper = mountComponent()

    await wrapper.vm.supprimerDocument(202)

    expect(wrapper.vm.documentASupprimer).toBe(202)
  })

  // Traite la selection fichier puis reset l input
  test('selectionnerDocument delegue a uploadDocument et vide l input', async () => {
    const wrapper = mountComponent()
    const fichier = new File(['abc'], 'piece.pdf', { type: 'application/pdf' })
    const uploadSpy = vi.spyOn(wrapper.vm, 'uploadDocument').mockResolvedValue()
    const event = {
      target: {
        files: [fichier],
        value: 'temp',
      },
    }

    await wrapper.vm.selectionnerDocument(event)

    expect(uploadSpy).toHaveBeenCalledWith(fichier)
    expect(event.target.value).toBe('')
  })

  // Autorise aussi les SVG
  test('selectionnerDocument accepte les fichiers svg', async () => {
    const wrapper = mountComponent()
    const fichier = new File(['<svg></svg>'], 'piece.svg', { type: 'image/svg+xml' })
    const uploadSpy = vi.spyOn(wrapper.vm, 'uploadDocument').mockResolvedValue()
    const event = {
      target: {
        files: [fichier],
        value: 'temp',
      },
    }

    await wrapper.vm.selectionnerDocument(event)

    expect(uploadSpy).toHaveBeenCalledWith(fichier)
    expect(event.target.value).toBe('')
  })

  // Refuse un format non autorise et ne lance pas l upload
  test('selectionnerDocument refuse les fichiers non autorises', async () => {
    const wrapper = mountComponent()
    const fichier = new File(['abc'], 'piece.txt', { type: 'text/plain' })
    const uploadSpy = vi.spyOn(wrapper.vm, 'uploadDocument').mockResolvedValue()
    const errorSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
    const event = {
      target: {
        files: [fichier],
        value: 'temp',
      },
    }

    await wrapper.vm.selectionnerDocument(event)

    expect(uploadSpy).not.toHaveBeenCalled()
    expect(errorSpy).toHaveBeenCalledWith('Type de fichier non autorisé : seuls les PDF, SVG, PNG et JPG sont acceptés.')
    expect(event.target.value).toBe('')
    errorSpy.mockRestore()
  })

  // Traite un depot drag and drop
  test('deposerDocument remet glisserDocument a false et lance upload', async () => {
    const wrapper = mountComponent()
    const fichier = new File(['abc'], 'drag.pdf', { type: 'application/pdf' })
    const uploadSpy = vi.spyOn(wrapper.vm, 'uploadDocument').mockResolvedValue()

    wrapper.vm.glisserDocument = true
    wrapper.vm.deposerDocument({
      dataTransfer: { files: [fichier] },
    })

    expect(wrapper.vm.glisserDocument).toBe(false)
    expect(uploadSpy).toHaveBeenCalledWith(fichier)
  })

  // Refuse un depot de fichier non autorise
  test('deposerDocument refuse les fichiers non autorises', async () => {
    const wrapper = mountComponent()
    const fichier = new File(['abc'], 'drag.txt', { type: 'text/plain' })
    const uploadSpy = vi.spyOn(wrapper.vm, 'uploadDocument').mockResolvedValue()
    const errorSpy = vi.spyOn(console, 'error').mockImplementation(() => {})

    wrapper.vm.glisserDocument = true
    wrapper.vm.deposerDocument({
      dataTransfer: { files: [fichier] },
    })

    expect(wrapper.vm.glisserDocument).toBe(false)
    expect(uploadSpy).not.toHaveBeenCalled()
    expect(errorSpy).toHaveBeenCalledWith('Type de fichier non autorisé : seuls les PDF, SVG, PNG et JPG sont acceptés.')
    errorSpy.mockRestore()
  })

  // Upload un document et ajoute le resultat a la liste
  test('uploadDocument ajoute le document et termine le chargement', async () => {
    const wrapper = mountComponent()
    const fichier = new File(['abc'], 'up.pdf', { type: 'application/pdf' })

    await wrapper.vm.uploadDocument(fichier)

    expect(documentService.uploadDocument).toHaveBeenCalledWith(101, expect.any(FormData))
    expect(wrapper.vm.inscription.documents_fournis.some((d) => d.id === 999)).toBe(true)
    expect(wrapper.vm.chargementDocument).toBe(false)
  })

  // Ouvre un document telecharge dans un nouvel onglet
  test('ouvrirDocument appelle window.open avec un blob url', async () => {
    const wrapper = mountComponent()
    const createObjectURLSpy = vi.spyOn(window.URL, 'createObjectURL').mockReturnValue('blob:test-open')
    const openSpy = vi.spyOn(window, 'open').mockImplementation(() => null)

    await wrapper.vm.ouvrirDocument({ id: 201, url: 'https://cdn/doc-a.pdf' })

    expect(documentService.downloadDocument).toHaveBeenCalledWith(201)
    expect(createObjectURLSpy).toHaveBeenCalled()
    expect(openSpy).toHaveBeenCalledWith('blob:test-open', '_blank')
  })

  // Construit un lien de telechargement et telecharge le fichier
  test('telechargerDocument cree un lien temporaire et declenche click', async () => {
    const wrapper = mountComponent()
    const createObjectURLSpy = vi.spyOn(window.URL, 'createObjectURL').mockReturnValue('blob:test-download')
    const revokeSpy = vi.spyOn(window.URL, 'revokeObjectURL').mockImplementation(() => {})

    const appendSpy = vi.spyOn(document.body, 'appendChild')
    const removeSpy = vi.spyOn(document.body, 'removeChild')
    const originalCreateElement = document.createElement.bind(document)

    vi.spyOn(document, 'createElement').mockImplementation((tagName) => {
      const el = originalCreateElement(tagName)
      if (tagName === 'a') {
        el.click = vi.fn()
      }
      return el
    })

    await wrapper.vm.telechargerDocument({ id: 202, url: 'https://cdn/doc-b.pdf' })

    expect(documentService.downloadDocument).toHaveBeenCalledWith(202)
    expect(createObjectURLSpy).toHaveBeenCalled()
    expect(appendSpy).toHaveBeenCalled()
    expect(removeSpy).toHaveBeenCalled()
    expect(revokeSpy).toHaveBeenCalledWith('blob:test-download')
  })

  // Affiche la popup changement puis emet les actions de fermeture/confirmation
  test('gere les emits du changement de course', () => {
    const wrapper = mountComponent()

    wrapper.vm.ouvrirChangementCourse()
    expect(wrapper.vm.showChangementCourse).toBe(true)

    wrapper.vm.onChangementConfirme({ id_inscription: 101, id_course: 88 })
    expect(wrapper.vm.showChangementCourse).toBe(false)
    expect(wrapper.emitted('ajouter-panier')).toBeTruthy()

    wrapper.vm.fermerAvecPopup()
    expect(wrapper.emitted('close')).toBeTruthy()
  })

  // Evalue la reponse d affichage d une question
  test('reponseQuestionAffichage retourne true uniquement pour la bonne option', () => {
    const wrapper = mountComponent()

    expect(wrapper.vm.reponseQuestionAffichage(9, 3)).toBe(true)
    expect(wrapper.vm.reponseQuestionAffichage(9, 999)).toBe(false)
    expect(wrapper.vm.reponseQuestionAffichage(99, 3)).toBeUndefined()
  })
})
