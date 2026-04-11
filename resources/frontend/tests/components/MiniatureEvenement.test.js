import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

const routerPushMock = vi.fn()

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    template: '<span data-test="icon"></span>',
  },
}))

vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: routerPushMock,
  }),
}))

import MiniatureEvenement from '../../components/MiniatureEvenement.vue'

const evenements = [
  {
    id: 1,
    nom: 'Run Geneva',
    couleur_primaire: '#111111',
    couleur_secondaire: '#eeeeee',
    logo_base64: 'data:image/png;base64,QQ==',
  },
  {
    id: 2,
    nom: 'Trail Lausanne',
    couleur_primaire: '#222222',
    couleur_secondaire: '#ffffff',
    logo_base64: null,
  },
]

function mountComponent(customProps = {}) {
  return mount(MiniatureEvenement, {
    props: {
      evenements,
      mode: 'selection',
      ...customProps,
    },
  })
}

const evenementsSansLogo = evenements.map((e) => ({ ...e, logo_base64: null }))

describe('MiniatureEvenement', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  afterEach(() => {
    vi.restoreAllMocks()
  })

  // Prepare les evenements colorises au montage
  test('colorise les logos et rend les cartes evenement', async () => {
    const originalCreateElement = document.createElement.bind(document)
    vi.spyOn(document, 'createElement').mockImplementation((tagName) => {
      if (tagName === 'canvas') {
        return {
          width: 0,
          height: 0,
          getContext: () => ({
            drawImage: vi.fn(),
            globalCompositeOperation: '',
            fillStyle: '',
            fillRect: vi.fn(),
          }),
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
        if (this.onload) this.onload()
      }
    }
    globalThis.Image = MockImage

    const wrapper = mountComponent()
    await flushPromises()

    const cards = wrapper.findAll('div.cursor-pointer')
    expect(cards).toHaveLength(2)

    const images = wrapper.findAll('img[alt="Logo évènement"]')
    expect(images).toHaveLength(1)
    expect(images[0].attributes('src')).toBe('data:image/png;base64,recolored')

    expect(wrapper.text()).toContain('Run Geneva')
    expect(wrapper.text()).toContain('Trail Lausanne')

    globalThis.Image = previousImage
  })

  // En mode selection emet levenement choisi
  test('handleClick emet selectionner en mode selection', async () => {
    const wrapper = mountComponent({ mode: 'selection', evenements: evenementsSansLogo })
    await flushPromises()

    const firstCard = wrapper.findAll('div.cursor-pointer')[0]
    expect(firstCard).toBeTruthy()
    await firstCard.trigger('click')

    expect(wrapper.emitted('selectionner')).toBeTruthy()
    expect(wrapper.emitted('selectionner')[0][0]).toMatchObject({ id: 1, nom: 'Run Geneva' })
    expect(routerPushMock).not.toHaveBeenCalled()
  })

  // En mode navigation redirige vers la liste des courses
  test('handleClick navigue vers ListeCourses hors mode selection', async () => {
    const wrapper = mountComponent({ mode: 'navigation', evenements: evenementsSansLogo })
    await flushPromises()

    const secondCard = wrapper.findAll('div.cursor-pointer')[1]
    expect(secondCard).toBeTruthy()
    await secondCard.trigger('click')

    expect(routerPushMock).toHaveBeenCalledWith({
      name: 'ListeCourses',
      params: { idEvenement: 2 },
    })
    expect(wrapper.emitted('selectionner')).toBeFalsy()
  })
})
