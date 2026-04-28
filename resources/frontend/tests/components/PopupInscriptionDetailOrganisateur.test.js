import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    template: '<span data-test="icon"></span>',
  },
}))

vi.mock('../../components/PopupChangementCourseOrganisateur.vue', () => ({
  default: {
    name: 'PopupChangementCourseOrganisateur',
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

vi.mock('../../services/courseOrganisateurService', () => ({
  default: {
    getCourse: vi.fn(),
  },
}))

vi.mock('../../services/documentService', () => ({
  default: {
    uploadDocumentAdmin: vi.fn(),
    deleteDocumentAdmin: vi.fn(),
    downloadDocument: vi.fn(),
  },
}))

vi.mock('../../services/inscriptionService', () => ({
  default: {
    updateInscriptionAdmin: vi.fn(),
  },
}))

vi.mock('../../services/choixOptionOrganisateurService', () => ({
  default: {
    saveChoix: vi.fn(),
    modifyChoix: vi.fn(),
    deleteChoix: vi.fn(),
  },
}))

vi.mock('../../services/reponseQuestionOrganisateurService', () => ({
  default: {
    saveReponses: vi.fn(),
  },
}))

import PopupInscriptionDetailOrganisateur from '../../components/PopupInscriptionDetailOrganisateur.vue'
import courseOrganisateurService from '../../services/courseOrganisateurService'
import documentService from '../../services/documentService'
import inscriptionService from '../../services/inscriptionService'
import choixOptionOrganisateurService from '../../services/choixOptionOrganisateurService'
import reponseQuestionOrganisateurService from '../../services/reponseQuestionOrganisateurService'

const baseInscription = {
  id: 501,
  id_course: 77,
  tarif: 40,
  status_paiement: 'Validé',
  montant_rabais: 2,
  code_participant: 'CODE-X',
  date_paiement: '2026-01-10T10:00',
  avertissement_valide: true,
  numero_inscription: 'N-123',
  ref_groupage: 'RG-1',
  participe_challenge: false,
  type_challenge: null,
  documents_fournis: [
    { id: 201, url: 'https://cdn/doc-a.pdf' },
    { id: 202, url: 'https://cdn/doc-b.pdf' },
  ],
  choix_options: [
    { id_option: 5, id_inscription: 501, quantite: 1 },
    { id_option: 6, id_inscription: 501, quantite: 3 },
  ],
  reponses_questions: [
    { id_question: 9, id_option_choisie: 3 },
  ],
  participant: {
    id: 1,
    prenom: 'Alice',
    nom: 'Dupont',
    date_naissance: '1990-06-12',
    sexe: 'F',
    nationalite: 'CH',
    telephone: '0790000000',
    taille_tshirt: 'M',
    adresse: 'Rue 1',
    code_postal: '1200',
    ville: 'Geneve',
    pays: 'Suisse',
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
    document_description: 'Certificat medical',
    evenement: {
      couleur_primaire: '#111111',
      couleur_secondaire: '#eeeeee',
    },
  },
}

const baseParticipants = [{ id: 1, prenom: 'Alice', nom: 'Dupont' }]

function mountComponent(customProps = {}) {
  return mount(PopupInscriptionDetailOrganisateur, {
    props: {
      inscription: JSON.parse(JSON.stringify(baseInscription)),
      participants: baseParticipants,
      ...customProps,
    },
  })
}

describe('PopupInscriptionDetailOrganisateur', () => {
  beforeEach(() => {
    vi.clearAllMocks()

    courseOrganisateurService.getCourse.mockResolvedValue({
      data: {
        id: 77,
        options: [
          { id: 5, nom: 'Repas', description: 'Menu', type: 'Quantifiable', tarif: 10 },
        ],
        questionnaire: [
          {
            id: 9,
            question: 'Taille T-shirt ?',
            answers: [{ id: 3, option: 'M' }, { id: 4, option: 'L' }],
          },
        ],
      },
    })

    inscriptionService.updateInscriptionAdmin.mockResolvedValue({})
    choixOptionOrganisateurService.saveChoix.mockResolvedValue({})
    choixOptionOrganisateurService.modifyChoix.mockResolvedValue({})
    choixOptionOrganisateurService.deleteChoix.mockResolvedValue({})
    reponseQuestionOrganisateurService.saveReponses.mockResolvedValue({})

    documentService.uploadDocumentAdmin.mockResolvedValue({
      data: { document: { id: 999, url: 'https://cdn/uploaded.pdf' } },
    })
    documentService.downloadDocument.mockResolvedValue({
      data: new Uint8Array([1, 2, 3]),
    })
    documentService.deleteDocumentAdmin.mockResolvedValue({})
  })

  afterEach(() => {
    vi.restoreAllMocks()
  })

  // Charge la course complete au montage
  test('charge la course complete au montage', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    expect(courseOrganisateurService.getCourse).toHaveBeenCalledWith(77)
    expect(wrapper.vm.coursComplet?.id).toBe(77)
  })

  // Formate une date et gere la valeur vide
  test('formatDate retourne JJ.MM.AAAA ou tiret', () => {
    const wrapper = mountComponent()

    expect(wrapper.vm.formatDate('2026-12-31')).toBe('31.12.2026')
    expect(wrapper.vm.formatDate(null)).toBe('—')
  })

  // Active et annule le mode edition
  test('activerEdition et annulerEdition gerent etat local', () => {
    const wrapper = mountComponent()

    wrapper.vm.activerEdition()
    expect(wrapper.vm.isEdit).toBe(true)
    expect(wrapper.vm.inscriptionEdit.tarif).toBe(40)
    expect(wrapper.vm.inscriptionEdit.choix_options).toHaveLength(2)

    wrapper.vm.annulerEdition()
    expect(wrapper.vm.isEdit).toBe(false)
    expect(wrapper.vm.inscriptionEdit).toBeNull()
  })

  // Sauvegarde inscription options et reponses puis emet la maj
  test('sauvegarderEdition orchestre update inscription choix et reponses', async () => {
    const wrapper = mountComponent()
    wrapper.vm.activerEdition()
    wrapper.vm.inscriptionEdit.tarif = 55
    wrapper.vm.inscriptionEdit.status_paiement = 'En attente'
    wrapper.vm.inscriptionEdit.choix_options = [
      { id_option: 5, quantite: 2 },
      { id_option: 7, quantite: 1 },
    ]
    wrapper.vm.inscriptionEdit.reponses_questions = [
      { id_question: 9, id_option_choisie: 4 },
    ]

    await wrapper.vm.sauvegarderEdition()

    expect(inscriptionService.updateInscriptionAdmin).toHaveBeenCalledWith(501, {
      tarif: 55,
      status_paiement: 'En attente',
      montant_rabais: 2,
      code_participant: 'CODE-X',
      date_paiement: '2026-01-10T10:00',
      avertissement_valide: true,
    })

    expect(choixOptionOrganisateurService.modifyChoix).toHaveBeenCalledWith(501, 5, { quantite: 2 })
    expect(choixOptionOrganisateurService.saveChoix).toHaveBeenCalledWith({
      choix: [{ id_inscription: 501, id_option: 7, quantite: 1 }],
    })
    expect(choixOptionOrganisateurService.deleteChoix).toHaveBeenCalledWith(501, 6)
    expect(reponseQuestionOrganisateurService.saveReponses).toHaveBeenCalledWith({
      reponses: [{ id_inscription: 501, id_question: 9, id_option_choisie: 4 }],
    })

    expect(wrapper.vm.isEdit).toBe(false)
    expect(wrapper.emitted('modifier-inscription')).toBeTruthy()
  })

  // N envoie pas les reponses si liste vide
  test('sauvegarderEdition saute saveReponses si aucune reponse', async () => {
    const wrapper = mountComponent()
    wrapper.vm.activerEdition()
    wrapper.vm.inscriptionEdit.reponses_questions = []

    await wrapper.vm.sauvegarderEdition()

    expect(reponseQuestionOrganisateurService.saveReponses).not.toHaveBeenCalled()
  })

  // Lit et affiche les options selon mode lecture ou edition
  test('optionSelectionnee et optionSelectionneePourAffichage respectent le mode', () => {
    const wrapper = mountComponent()

    expect(wrapper.vm.optionSelectionnee(5)).toEqual({ id_option: 5, id_inscription: 501, quantite: 1 })
    expect(wrapper.vm.optionSelectionnee(404)).toBeNull()
    expect(wrapper.vm.optionSelectionneePourAffichage(5)).toEqual({ id_option: 5, id_inscription: 501, quantite: 1 })

    wrapper.vm.activerEdition()
    wrapper.vm.inscriptionEdit.choix_options = [{ id_option: 5, quantite: 9 }]

    expect(wrapper.vm.optionSelectionneePourAffichage(5)).toEqual({ id_option: 5, quantite: 9 })
  })

  // Gere quantite ajout et retrait d options
  test('getQuantiteOption toggleOption et mettreAJourQuantite', () => {
    const wrapper = mountComponent()
    wrapper.vm.activerEdition()

    expect(wrapper.vm.getQuantiteOption(5)).toBe(1)

    wrapper.vm.mettreAJourQuantite(5, '6')
    expect(wrapper.vm.getQuantiteOption(5)).toBe(6)

    wrapper.vm.toggleOption({ id: 88, type: 'Cochable' })
    expect(wrapper.vm.inscriptionEdit.choix_options.some((o) => o.id_option === 88)).toBe(true)

    wrapper.vm.toggleOption({ id: 88, type: 'Cochable' })
    expect(wrapper.vm.inscriptionEdit.choix_options.some((o) => o.id_option === 88)).toBe(false)
  })

  // Lit selection de reponse et la modifie en edition
  test('reponseQuestion reponseQuestionEdit et selectionnerReponse', () => {
    const wrapper = mountComponent()

    expect(wrapper.vm.reponseQuestion(9)).toBe(3)
    expect(wrapper.vm.reponseQuestionEdit(9, 3)).toBe(true)
    expect(wrapper.vm.reponseQuestionEdit(9, 4)).toBe(false)

    wrapper.vm.activerEdition()
    wrapper.vm.selectionnerReponse(9, 4)
    expect(wrapper.vm.reponseQuestionEdit(9, 4)).toBe(true)

    wrapper.vm.selectionnerReponse(10, 8)
    expect(wrapper.vm.inscriptionEdit.reponses_questions.find((r) => r.id_question === 10)?.id_option_choisie).toBe(8)
  })

  // Prepare la suppression puis supprime le document cible
  test('supprimerDocument et confirmerSuppressionDocument mettent a jour la liste', async () => {
    const wrapper = mountComponent()

    await wrapper.vm.supprimerDocument(201)
    expect(wrapper.vm.documentASupprimer).toBe(201)

    await wrapper.vm.confirmerSuppressionDocument()
    expect(documentService.deleteDocumentAdmin).toHaveBeenCalledWith(201)
    expect(wrapper.vm.inscription.documents_fournis.find((d) => d.id === 201)).toBeUndefined()
    expect(wrapper.vm.documentASupprimer).toBeNull()
  })

  // Ignore suppression si aucune cible selectionnee
  test('confirmerSuppressionDocument quitte si aucune cible', async () => {
    const wrapper = mountComponent()

    await wrapper.vm.confirmerSuppressionDocument()

    expect(documentService.deleteDocumentAdmin).not.toHaveBeenCalled()
  })

  // Gere selection input et depot drag and drop
  test('selectionnerDocument et deposerDocument deleguent a uploadDocument', async () => {
    const wrapper = mountComponent()
    const fichier = new File(['abc'], 'piece.pdf', { type: 'application/pdf' })
    const uploadSpy = vi.spyOn(wrapper.vm, 'uploadDocument').mockResolvedValue()

    const event = {
      target: {
        files: [fichier],
        value: 'tmp',
      },
    }

    await wrapper.vm.selectionnerDocument(event)
    expect(uploadSpy).toHaveBeenCalledWith(fichier)
    expect(event.target.value).toBe('')

    wrapper.vm.glisserDocument = true
    wrapper.vm.deposerDocument({ dataTransfer: { files: [fichier] } })
    expect(wrapper.vm.glisserDocument).toBe(false)
    expect(uploadSpy).toHaveBeenCalledWith(fichier)
  })

  // Autorise aussi les SVG
  test('selectionnerDocument et deposerDocument acceptent les fichiers svg', async () => {
    const wrapper = mountComponent()
    const fichier = new File(['<svg></svg>'], 'piece.svg', { type: 'image/svg+xml' })
    const uploadSpy = vi.spyOn(wrapper.vm, 'uploadDocument').mockResolvedValue()

    const event = {
      target: {
        files: [fichier],
        value: 'tmp',
      },
    }

    await wrapper.vm.selectionnerDocument(event)
    expect(uploadSpy).toHaveBeenCalledWith(fichier)
    expect(event.target.value).toBe('')

    wrapper.vm.glisserDocument = true
    wrapper.vm.deposerDocument({ dataTransfer: { files: [fichier] } })
    expect(wrapper.vm.glisserDocument).toBe(false)
    expect(uploadSpy).toHaveBeenCalledWith(fichier)
  })

  // Refuse un format non autorise avant l upload
  test('selectionnerDocument et deposerDocument refusent les fichiers non autorises', async () => {
    const wrapper = mountComponent()
    const fichier = new File(['abc'], 'piece.txt', { type: 'text/plain' })
    const uploadSpy = vi.spyOn(wrapper.vm, 'uploadDocument').mockResolvedValue()
    const errorSpy = vi.spyOn(console, 'error').mockImplementation(() => {})

    const event = {
      target: {
        files: [fichier],
        value: 'tmp',
      },
    }

    await wrapper.vm.selectionnerDocument(event)
    expect(uploadSpy).not.toHaveBeenCalled()
    expect(errorSpy).toHaveBeenCalledWith('Type de fichier non autorisé : seuls les PDF, SVG, PNG et JPG sont acceptés.')
    expect(event.target.value).toBe('')

    wrapper.vm.glisserDocument = true
    wrapper.vm.deposerDocument({ dataTransfer: { files: [fichier] } })
    expect(wrapper.vm.glisserDocument).toBe(false)
    expect(uploadSpy).not.toHaveBeenCalled()

    errorSpy.mockRestore()
  })

  // Upload ajoute un document et libere l etat de chargement
  test('uploadDocument ajoute le document et termine le chargement', async () => {
    const wrapper = mountComponent()
    const fichier = new File(['abc'], 'up.pdf', { type: 'application/pdf' })

    await wrapper.vm.uploadDocument(fichier)

    expect(documentService.uploadDocumentAdmin).toHaveBeenCalledWith(501, expect.any(FormData))
    expect(wrapper.vm.inscription.documents_fournis.some((d) => d.id === 999)).toBe(true)
    expect(wrapper.vm.chargementDocument).toBe(false)
  })

  // Ouvre un document dans un nouvel onglet
  test('ouvrirDocument utilise window.open avec une blob url', async () => {
    const wrapper = mountComponent()
    const createObjectURLSpy = vi.spyOn(window.URL, 'createObjectURL').mockReturnValue('blob:test-open')
    const openSpy = vi.spyOn(window, 'open').mockImplementation(() => null)

    await wrapper.vm.ouvrirDocument({ id: 201, url: 'https://cdn/doc-a.pdf' })

    expect(documentService.downloadDocument).toHaveBeenCalledWith(201)
    expect(createObjectURLSpy).toHaveBeenCalled()
    expect(openSpy).toHaveBeenCalledWith('blob:test-open', '_blank')
  })

  // Construit un lien temporaire pour telecharger un document
  test('telechargerDocument cree un lien temporaire et le declenche', async () => {
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

  // Ouvre et ferme le changement de course puis emet les evenements
  test('ouvrirChangementCourse onChangementConfirme et fermerAvecPopup', () => {
    const wrapper = mountComponent()

    wrapper.vm.ouvrirChangementCourse()
    expect(wrapper.vm.showChangementCourse).toBe(true)

    wrapper.vm.onChangementConfirme({ id_inscription: 501, id_course: 88 })
    expect(wrapper.vm.showChangementCourse).toBe(false)
    expect(wrapper.emitted('ajouter-panier')).toBeTruthy()

    wrapper.vm.fermerAvecPopup()
    expect(wrapper.emitted('close')).toBeTruthy()
  })
})
