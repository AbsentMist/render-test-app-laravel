import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

const mockAjouterInscription = vi.fn()

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    template: '<span data-test="icon"></span>',
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

vi.mock('../../components/ChangementEvenement.vue', () => ({
  default: {
    name: 'ChangementEvenement',
    emits: ['selectionner'],
    template: '<div data-test="changement-evenement"></div>',
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

import PopupChangementCourseParticipant from '../../components/PopupChangementCourseParticipant.vue'

const baseInscription = {
  id: 2001,
  tarif: 38,
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
  return mount(PopupChangementCourseParticipant, {
    props: {
      inscription: JSON.parse(JSON.stringify(baseInscription)),
      participants: baseParticipants,
      ...customProps,
    },
  })
}

describe('PopupChangementCourseParticipant', () => {
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

  // Selectionne une course et ouvre letape inscription
  test('choisirCourse met a jour la course et letape', () => {
    const wrapper = mountComponent()

    wrapper.vm.choisirCourse({ id: 88, nom_course: 'Semi' })

    expect(wrapper.vm.nouvelleInscription.course).toEqual({ id: 88, nom_course: 'Semi' })
    expect(wrapper.vm.etape).toBe(wrapper.vm.ETAPES.INSCRIPTION)
  })

  // Retourne a la liste des courses
  test('retourCourses reinitialise la course et letape', () => {
    const wrapper = mountComponent()
    wrapper.vm.nouvelleInscription.course = { id: 88 }
    wrapper.vm.etape = wrapper.vm.ETAPES.INSCRIPTION

    wrapper.vm.retourCourses()

    expect(wrapper.vm.nouvelleInscription.course).toBeNull()
    expect(wrapper.vm.etape).toBe(wrapper.vm.ETAPES.COURSE)
  })

  // Ouvre la popup de confirmation avec les donnees du parcours
  test('confirmerChangement stocke les donnees et active confirmation', () => {
    const wrapper = mountComponent()
    const payload = { id_course: 88, tarif: 55 }

    wrapper.vm.confirmerChangement(payload)

    expect(wrapper.vm.nouvelleInscriptionData).toEqual(payload)
    expect(wrapper.vm.confirmation).toBe(true)
  })

  // Interdit le choix de la meme course
  test('confirmPopup bloque la meme course et masque l info apres delai', async () => {
    const wrapper = mountComponent()
    wrapper.vm.confirmation = true
    wrapper.vm.nouvelleInscription.course = { id: 77 }

    await wrapper.vm.confirmPopup()

    expect(wrapper.vm.confirmation).toBe(false)
    expect(wrapper.vm.dataInserted).toBe(true)
    expect(wrapper.vm.messageConfirmation).toContain('même course')

    vi.advanceTimersByTime(2000)
    expect(wrapper.vm.dataInserted).toBe(false)
  })

  // Ajoute au panier puis ferme la popup apres delai
  test('confirmPopup ajoute au panier et emet close', async () => {
    const wrapper = mountComponent()
    wrapper.vm.confirmation = true
    wrapper.vm.nouvelleInscription.course = { id: 88, nom_course: 'Semi' }
    wrapper.vm.nouvelleInscriptionData = { id_course: 88, tarif: 55 }

    await wrapper.vm.confirmPopup()

    expect(mockAjouterInscription).toHaveBeenCalledWith(
      { id_course: 88, tarif: 55, ancienneInscriptionId: 2001 },
      { id: 88, nom_course: 'Semi' }
    )
    expect(wrapper.vm.dataInserted).toBe(true)
    expect(wrapper.vm.messageConfirmation).toContain('pris en compte')

    vi.advanceTimersByTime(1500)
    expect(wrapper.emitted('close')).toBeTruthy()
  })

  // Capture les erreurs panier sans casser le flux
  test('confirmPopup capture les erreurs du store panier', async () => {
    mockAjouterInscription.mockImplementationOnce(() => {
      throw new Error('Panier KO')
    })
    const errorSpy = vi.spyOn(console, 'error').mockImplementation(() => {})

    const wrapper = mountComponent()
    wrapper.vm.nouvelleInscription.course = { id: 88, nom_course: 'Semi' }
    wrapper.vm.nouvelleInscriptionData = { id_course: 88, tarif: 55 }

    await wrapper.vm.confirmPopup()

    expect(errorSpy).toHaveBeenCalled()
  })

  // Recolorise un logo avec canvas
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

  // Monte avec logo et calcule logoPreview
  test('mounted calcule logoPreview si logo_base64 est present', async () => {
    const coloriserSpy = vi.spyOn(PopupChangementCourseParticipant.methods, 'coloriserLogo').mockResolvedValue('data:image/png;base64,final')

    const wrapper = mountComponent({
      inscription: {
        ...JSON.parse(JSON.stringify(baseInscription)),
        course: {
          ...baseInscription.course,
          evenement: {
            ...baseInscription.course.evenement,
            logo_base64: 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAAB',
          },
        },
      },
    })

    await flushPromises()

    expect(coloriserSpy).toHaveBeenCalled()
    expect(wrapper.vm.logoPreview).toBe('data:image/png;base64,final')
  })
})
