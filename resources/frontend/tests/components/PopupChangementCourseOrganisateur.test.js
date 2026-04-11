import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'

const mockAjouterInscription = vi.fn()

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    template: '<span data-test="icon"></span>',
  },
}))

vi.mock('../../components/ChangementEvenement.vue', () => ({
  default: {
    name: 'ChangementEvenement',
    emits: ['selectionner'],
    template: '<div data-test="changement-evenement"></div>',
  },
}))

vi.mock('../../components/ChangementCourse.vue', () => ({
  default: {
    name: 'ChangementCourse',
    props: ['evenement'],
    emits: ['selectionner'],
    template: '<div data-test="changement-course"></div>',
  },
}))

vi.mock('../../components/PopupInscriptionCourse.vue', () => ({
  default: {
    name: 'PopupInscriptionCourse',
    props: ['course', 'participants', 'inline'],
    emits: ['close', 'ajouter-panier'],
    template: '<div data-test="popup-inscription-course"></div>',
  },
}))

vi.mock('../../components/PopupConfirmation.vue', () => ({
  default: {
    name: 'PopupConfirmation',
    props: ['message', 'showButtons'],
    emits: ['confirm', 'cancel'],
    template: '<div data-test="popup-confirmation"></div>',
  },
}))

vi.mock('../../stores/cart', () => ({
  useCartStore: () => ({
    ajouterInscription: mockAjouterInscription,
  }),
}))

import PopupChangementCourseOrganisateur from '../../components/PopupChangementCourseOrganisateur.vue'

const baseInscription = {
  id: 1001,
  tarif: 42,
  participant: {
    id: 1,
    prenom: 'Alice',
    nom: 'Dupont',
  },
  groupe: null,
  course: {
    id: 77,
    nom: 'Trail 10K',
    date_debut: '2026-06-10',
    date_fin: '2026-06-10',
    evenement: {
      id: 9,
      nom: 'Run Geneva',
      couleur_primaire: '#111111',
      couleur_secondaire: '#eeeeee',
      logo_base64: null,
    },
  },
}

const baseParticipants = [{ id: 1, prenom: 'Alice', nom: 'Dupont' }]

function mountComponent(customProps = {}) {
  return mount(PopupChangementCourseOrganisateur, {
    props: {
      inscription: JSON.parse(JSON.stringify(baseInscription)),
      participants: baseParticipants,
      ...customProps,
    },
  })
}

describe('PopupChangementCourseOrganisateur', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.useFakeTimers()
  })

  afterEach(() => {
    vi.runOnlyPendingTimers()
    vi.useRealTimers()
    vi.restoreAllMocks()
  })

  // Initialise le store panier au montage
  test('mounted initialise cartStore', () => {
    const wrapper = mountComponent()

    expect(wrapper.vm.cartStore).toBeTruthy()
    expect(typeof wrapper.vm.cartStore.ajouterInscription).toBe('function')
  })

  // Passe de l etape evenement a course
  test('choisirEvenement stocke evenement et avance', () => {
    const wrapper = mountComponent()

    wrapper.vm.choisirEvenement({ id: 12, nom: 'Event X' })

    expect(wrapper.vm.nouvelleInscription.evenement).toEqual({ id: 12, nom: 'Event X' })
    expect(wrapper.vm.etape).toBe(wrapper.vm.ETAPES.COURSE)
  })

  // Passe de l etape course a inscription
  test('choisirCourse stocke course et avance', () => {
    const wrapper = mountComponent()

    wrapper.vm.choisirCourse({ id: 99, nom_course: 'Course Y' })

    expect(wrapper.vm.nouvelleInscription.course).toEqual({ id: 99, nom_course: 'Course Y' })
    expect(wrapper.vm.etape).toBe(wrapper.vm.ETAPES.INSCRIPTION)
  })

  // Retour complet au choix evenement
  test('retourEvenements reinitialise evenement course et etape', () => {
    const wrapper = mountComponent()
    wrapper.vm.nouvelleInscription.evenement = { id: 12 }
    wrapper.vm.nouvelleInscription.course = { id: 99 }
    wrapper.vm.etape = wrapper.vm.ETAPES.INSCRIPTION

    wrapper.vm.retourEvenements()

    expect(wrapper.vm.nouvelleInscription.evenement).toBeNull()
    expect(wrapper.vm.nouvelleInscription.course).toBeNull()
    expect(wrapper.vm.etape).toBe(wrapper.vm.ETAPES.EVENEMENT)
  })

  // Retour au choix course en conservant evenement
  test('retourCourses efface seulement la course', () => {
    const wrapper = mountComponent()
    wrapper.vm.nouvelleInscription.evenement = { id: 12 }
    wrapper.vm.nouvelleInscription.course = { id: 99 }
    wrapper.vm.etape = wrapper.vm.ETAPES.INSCRIPTION

    wrapper.vm.retourCourses()

    expect(wrapper.vm.nouvelleInscription.evenement).toEqual({ id: 12 })
    expect(wrapper.vm.nouvelleInscription.course).toBeNull()
    expect(wrapper.vm.etape).toBe(wrapper.vm.ETAPES.COURSE)
  })

  // Ouvre la confirmation avec les donnees d inscription
  test('confirmerChangement stocke les donnees et ouvre confirmation', () => {
    const wrapper = mountComponent()
    const payload = { id_course: 88, tarif: 55 }

    wrapper.vm.confirmerChangement(payload)

    expect(wrapper.vm.nouvelleInscriptionData).toEqual(payload)
    expect(wrapper.vm.confirmation).toBe(true)
  })

  // Rejette la meme course et referme l alerte auto
  test('confirmPopup bloque la meme course et ferme le message apres delai', async () => {
    const wrapper = mountComponent()
    wrapper.vm.confirmation = true
    wrapper.vm.nouvelleInscription.evenement = { id: 9 }
    wrapper.vm.nouvelleInscription.course = { id: 77 }

    await wrapper.vm.confirmPopup()

    expect(wrapper.vm.confirmation).toBe(false)
    expect(wrapper.vm.dataInserted).toBe(true)
    expect(wrapper.vm.messageConfirmation).toContain('même course')

    vi.advanceTimersByTime(2000)
    expect(wrapper.vm.dataInserted).toBe(false)
  })

  // Ajoute au panier puis emet close apres delai
  test('confirmPopup ajoute au panier et emet close en cas de succes', async () => {
    const wrapper = mountComponent()
    wrapper.vm.nouvelleInscription.evenement = { id: 12, nom: 'Event X' }
    wrapper.vm.nouvelleInscription.course = { id: 88, nom_course: 'Course Y' }
    wrapper.vm.nouvelleInscriptionData = { id_course: 88, tarif: 55 }
    wrapper.vm.confirmation = true

    await wrapper.vm.confirmPopup()

    expect(mockAjouterInscription).toHaveBeenCalledWith(
      { id_course: 88, tarif: 55, ancienneInscriptionId: 1001 },
      { id: 88, nom_course: 'Course Y' }
    )
    expect(wrapper.vm.confirmation).toBe(false)
    expect(wrapper.vm.dataInserted).toBe(true)
    expect(wrapper.vm.messageConfirmation).toContain('pris en compte')

    vi.advanceTimersByTime(1500)
    expect(wrapper.emitted('close')).toBeTruthy()
  })

  // Intercepte les erreurs du store panier sans crasher
  test('confirmPopup capture les erreurs du panier', async () => {
    mockAjouterInscription.mockImplementationOnce(() => {
      throw new Error('Panier KO')
    })
    const errorSpy = vi.spyOn(console, 'error').mockImplementation(() => {})

    const wrapper = mountComponent()
    wrapper.vm.nouvelleInscription.evenement = { id: 12, nom: 'Event X' }
    wrapper.vm.nouvelleInscription.course = { id: 88, nom_course: 'Course Y' }
    wrapper.vm.nouvelleInscriptionData = { id_course: 88, tarif: 55 }

    await wrapper.vm.confirmPopup()

    expect(errorSpy).toHaveBeenCalled()
  })

  // Recolorise un logo base64 via canvas
  test('coloriserLogo retourne une image recolorisee', async () => {
    const wrapper = mountComponent()

    const drawImage = vi.fn()
    const fillRect = vi.fn()
    const fakeContext = {
      drawImage,
      fillRect,
      globalCompositeOperation: '',
      fillStyle: '',
    }

    const originalCreateElement = document.createElement.bind(document)
    vi.spyOn(document, 'createElement').mockImplementation((tagName) => {
      if (tagName === 'canvas') {
        return {
          width: 0,
          height: 0,
          getContext: () => fakeContext,
          toDataURL: () => 'data:image/png;base64,recolored',
        }
      }
      return originalCreateElement(tagName)
    })

    const previousImage = globalThis.Image
    class MockImage {
      constructor() {
        this.width = 20
        this.height = 10
        this.onload = null
      }

      set src(_value) {
        if (this.onload) {
          this.onload()
        }
      }
    }
    globalThis.Image = MockImage

    const result = await wrapper.vm.coloriserLogo('data:image/png;base64,abc', '#ffffff')

    expect(result).toBe('data:image/png;base64,recolored')
    expect(drawImage).toHaveBeenCalled()
    expect(fillRect).toHaveBeenCalled()

    globalThis.Image = previousImage
  })
})
