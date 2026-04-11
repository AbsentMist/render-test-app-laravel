import { describe, test, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

const routerMock = {
  push: vi.fn(),
}

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    template: '<span data-test="icon"></span>',
  },
}))

vi.mock('../../services/courseOrganisateurService', () => ({
  default: {
    getAllCourses: vi.fn(),
    deleteCourse: vi.fn(),
  },
}))

vi.mock('../../services/evenementOrganisateurService', () => ({
  default: {
    getEvenement: vi.fn(),
  },
}))

vi.mock('../../components/Title.vue', () => ({
  default: {
    name: 'Title',
    props: ['texte'],
    template: '<h1>{{ texte }}</h1>',
  },
}))

vi.mock('../../components/PopupConfirmation.vue', () => ({
  default: {
    name: 'PopupConfirmation',
    props: ['message', 'icon'],
    template: `
      <div data-test="popup-confirmation">
        <span>{{ message }}</span>
        <button data-test="confirm" @click="$emit('confirm')">Confirmer</button>
        <button data-test="cancel" @click="$emit('cancel')">Annuler</button>
      </div>
    `,
  },
}))

import OrganisateurCourses from '../../views/OrganisateurCourses.vue'
import courseOrganisateurService from '../../services/courseOrganisateurService'
import evenementOrganisateurService from '../../services/evenementOrganisateurService'

const mockCourses = [
  {
    id: 1,
    nom: '10 km',
    debut_inscription: '2026-05-01',
    fin_inscription: '2026-05-20',
    is_actif: true,
    is_interne: false,
  },
  {
    id: 2,
    nom: '5 km',
    debut_inscription: null,
    fin_inscription: null,
    is_actif: false,
    is_interne: true,
  },
]

function mountComponent(routeParams = { idEvenement: '42' }) {
  return mount(OrganisateurCourses, {
    global: {
      mocks: {
        $route: { params: routeParams },
        $router: routerMock,
      },
    },
  })
}

describe('OrganisateurCourses', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    courseOrganisateurService.getAllCourses.mockResolvedValue({
      data: { courses: mockCourses },
    })
    courseOrganisateurService.deleteCourse.mockResolvedValue({})
    evenementOrganisateurService.getEvenement.mockResolvedValue({
      data: { nom: 'Marathon 2026' },
    })
  })

  // Charge les courses et le nom de l evenement au montage
  test('charge les courses et le nom de l evenement', async () => {
    const wrapper = mountComponent()

    expect(wrapper.text()).toContain('Chargement des courses...')

    await flushPromises()

    expect(wrapper.text()).toContain('Tableau de bord : courses Marathon 2026')
    expect(wrapper.text()).toContain('10 km')
    expect(wrapper.text()).toContain('5 km')
    expect(courseOrganisateurService.getAllCourses).toHaveBeenCalledWith('42')
    expect(evenementOrganisateurService.getEvenement).toHaveBeenCalledWith('42')
  })

  // Formate une date brute en date suisse
  test('formaterDate retourne une date suisse', () => {
    const wrapper = mountComponent()

    expect(wrapper.vm.formaterDate('2026-05-01')).toBe('01.05.2026')
    expect(wrapper.vm.formaterDate(null)).toBe('—')
  })

  // Redirige vers le formulaire de creation de course
  test('bouton Nouveau ouvre le formulaire course', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    await wrapper.findAll('button').find((button) => button.text().includes('Nouveau')).trigger('click')

    expect(routerMock.push).toHaveBeenCalledWith('/organisateur/formulaires?onglet=Course')
  })

  // Redirige vers le formulaire de modification avec les bons ids
  test('modifierCourse ouvre le formulaire en edition', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    wrapper.vm.modifierCourse(mockCourses[0])

    expect(routerMock.push).toHaveBeenCalledWith('/organisateur/formulaires?onglet=Course&id=1&idEvenement=42')
  })

  // Ouvre la confirmation de suppression
  test('confirmerSuppression selectionne la course', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    wrapper.vm.confirmerSuppression(mockCourses[0])
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.courseASupprimer.id).toBe(1)
    expect(wrapper.find('[data-test="popup-confirmation"]').exists()).toBe(true)
  })

  // Supprime la course puis retire la ligne du tableau
  test('supprimerCourse retire la course supprimee', async () => {
    const wrapper = mountComponent()
    await flushPromises()

    wrapper.vm.confirmerSuppression(mockCourses[0])
    await wrapper.vm.$nextTick()
    await wrapper.vm.supprimerCourse()

    expect(courseOrganisateurService.deleteCourse).toHaveBeenCalledWith(1)
    expect(wrapper.vm.courses).toHaveLength(1)
    expect(wrapper.vm.courses[0].id).toBe(2)
    expect(wrapper.vm.courseASupprimer).toBeNull()
  })

  // Affiche une erreur si la suppression echoue
  test('supprimerCourse affiche une erreur en cas d echec', async () => {
    courseOrganisateurService.deleteCourse.mockRejectedValue(new Error('Erreur reseau'))
    const wrapper = mountComponent()
    await flushPromises()

    wrapper.vm.confirmerSuppression(mockCourses[0])
    await wrapper.vm.$nextTick()
    await wrapper.vm.supprimerCourse()

    expect(wrapper.vm.erreur).toBe('Impossible de supprimer cette course.')
    expect(wrapper.vm.courseASupprimer).toBeNull()
  })

  // Affiche une erreur si le chargement echoue
  test('chargerCourses affiche un message derreur', async () => {
    courseOrganisateurService.getAllCourses.mockRejectedValue(new Error('Erreur reseau'))
    const wrapper = mountComponent()
    await flushPromises()

    expect(wrapper.text()).toContain('Impossible de charger les courses.')
  })

  // Affiche le message quand il n y a aucune course
  test('affiche un message si la liste des courses est vide', async () => {
    courseOrganisateurService.getAllCourses.mockResolvedValue({
      data: { courses: [] },
    })
    const wrapper = mountComponent()
    await flushPromises()

    expect(wrapper.text()).toContain('Aucune course trouvée.')
  })
})