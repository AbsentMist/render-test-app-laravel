import { describe, test, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { reactive } from 'vue'

const routerPushMock = vi.fn()
const logoutMock = vi.fn().mockResolvedValue()

const authStoreMock = reactive({
  logout: logoutMock,
})

const themeStoreMock = reactive({
  primaryColor: null,
  secondaryColor: null,
})

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    template: '<span data-test="icon"></span>',
  },
}))

vi.mock('vue-router', () => ({
  useRouter: () => ({ push: routerPushMock }),
}))

vi.mock('../../stores/auth', () => ({
  useAuthStore: () => authStoreMock,
}))

vi.mock('../../stores/theme', () => ({
  useThemeStore: () => themeStoreMock,
}))

import SideBarUser from '../../components/SideBarUser.vue'

function mountComponent() {
  return mount(SideBarUser, {
    global: {
      stubs: {
        RouterLink: {
          props: ['to'],
          template: '<a :data-to="to"><slot /></a>',
        },
      },
    },
  })
}

describe('SideBarUser', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    themeStoreMock.primaryColor = null
    themeStoreMock.secondaryColor = null
  })

  // Rend les liens de navigation participant attendus
  test('affiche les routes principales participant', () => {
    const wrapper = mountComponent()

    const links = wrapper.findAll('a[data-to]').map((a) => a.attributes('data-to'))
    expect(links).toEqual([
      '/accueil',
      '/evenements',
      '/inscriptions',
      '/resultats',
      '/membership',
      '/course-collecte',
      '/echange-dossard',
    ])

    expect(wrapper.text()).toContain('Tableau de bord')
    expect(wrapper.text()).toContain('Liste des évènements')
    expect(wrapper.text()).toContain('Mes inscriptions')
    expect(wrapper.text()).toContain('Mes résultats')
  })

  // Applique les classes par defaut sans theme personnalise
  test('utilise le style par defaut quand primaryColor est null', () => {
    const wrapper = mountComponent()
    const aside = wrapper.find('aside')

    expect(aside.classes()).toContain('bg-primary-300')
    expect(aside.classes()).toContain('text-secondary')
  })

  // Applique un style inline quand un theme est present
  test('applique un style inline quand primaryColor est defini', () => {
    themeStoreMock.primaryColor = '#445566'

    const wrapper = mountComponent()
    const aside = wrapper.find('aside')

    expect(aside.attributes('style')).toContain('background-color: #4455661A')
    expect(aside.attributes('style')).toContain('border-color: #44556633')
  })

  // Deconnecte puis redirige vers login
  test('handleLogout deconnecte et redirige', async () => {
    const wrapper = mountComponent()

    const logoutButton = wrapper.findAll('button').find((b) => b.text().includes('Se déconnecter'))
    expect(logoutButton).toBeTruthy()

    await logoutButton.trigger('click')
    await flushPromises()

    expect(logoutMock).toHaveBeenCalledTimes(1)
    expect(routerPushMock).toHaveBeenCalledWith('/login')
  })
})
