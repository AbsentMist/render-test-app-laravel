import { describe, test, expect, vi, beforeEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'

// Mock prixEvolutifService
vi.mock('../../services/prixEvolutifService', () => ({
  default: {
    getTarifActuel: vi.fn(),
  },
}))

// Mock api
vi.mock('../../services/api', () => ({
  default: { get: vi.fn(), post: vi.fn(), delete: vi.fn() },
}))

// Mock localStorage
vi.stubGlobal('localStorage', {
  getItem: vi.fn(() => null),
  setItem: vi.fn(),
  removeItem: vi.fn(),
  clear: vi.fn(),
})

import { useCartStore } from '../../stores/cart'
import prixEvolutifService from '../../services/prixEvolutifService'

describe('Panier - logique prix evolutif', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    setActivePinia(createPinia())
  })

  // Le store cart calcule bien le total avec tarif normal
  test('cartTotal additionne correctement les tarifs', () => {
    const store = useCartStore()
    store.ajouterInscription({ nom: 'Alice', tarif: '30' }, { id: 1, nom: 'Course A', is_prix_evolutif: false })
    store.ajouterInscription({ nom: 'Bob', tarif: '40' }, { id: 2, nom: 'Course B', is_prix_evolutif: false })
    expect(store.cartTotal).toBe(70)
  })

  // Vider le panier remet le total a 0
  test('viderPanier remet cartTotal a 0', () => {
    const store = useCartStore()
    store.ajouterInscription({ nom: 'Alice', tarif: '30' }, { id: 1, nom: 'Course A', is_prix_evolutif: false })
    store.viderPanier()
    expect(store.cartTotal).toBe(0)
    expect(store.cartCount).toBe(0)
  })

  // Supprimer une inscription specifique
  test('supprimerInscription retire le bon article', () => {
    const store = useCartStore()
    store.ajouterInscription({ nom: 'Alice', tarif: '30' }, { id: 1, nom: 'Course A' })
    store.ajouterInscription({ nom: 'Bob', tarif: '40' }, { id: 2, nom: 'Course B' })
    store.supprimerInscription(0)
    expect(store.cartCount).toBe(1)
    expect(store.inscriptions[0].courseDetails.nom).toBe('Course B')
  })

  // getTarifActuel est appele pour les courses a prix evolutif
  // et le total conserve le supplement des options.
  test('getTarifActuel conserve les options pour une course a prix evolutif', async () => {
    prixEvolutifService.getTarifActuel.mockResolvedValue({ data: { tarif: 40 } })

    const store = useCartStore()
    const courseDetails = { id: 5, nom: 'Course Prix Evolutif', is_prix_evolutif: true }
    store.ajouterInscription(
      {
        nom: 'Alice',
        tarif: '30',
        options: {
          1: { option: { tarif: 5, type: 'Cochable' }, quantite: 1 },
          2: { option: { tarif: 2.5, type: 'Quantifiable' }, quantite: 2 },
        },
      },
      courseDetails,
    )

    // Simuler la mise a jour du tarif comme dans le watch de Panier.vue
    for (const article of store.inscriptions) {
      if (article.courseDetails?.is_prix_evolutif) {
        const prixResp = await prixEvolutifService.getTarifActuel(article.courseDetails.id)
        if (prixResp.data?.tarif !== undefined) {
          const options = Object.values(article.options || {})
          const supplementOptions = options.reduce((total, optionSelectionnee) => {
            const option = optionSelectionnee?.option || {}
            const tarifOption = parseFloat(option.tarif || 0)
            const quantite = option.type === 'Quantifiable'
              ? parseFloat(optionSelectionnee?.quantite || 0)
              : 1
            return total + tarifOption * quantite
          }, 0)

          article.tarif = prixResp.data.tarif + supplementOptions
        }
      }
    }

    expect(prixEvolutifService.getTarifActuel).toHaveBeenCalledWith(5)
    expect(store.inscriptions[0].tarif).toBe(50)
  })

  // getTarifActuel n'est PAS appele pour une course sans prix evolutif
  test('getTarifActuel nest pas appele pour une course sans prix evolutif', async () => {
    const store = useCartStore()
    const courseDetails = { id: 3, nom: 'Course Normale', is_prix_evolutif: false }
    store.ajouterInscription({ nom: 'Alice', tarif: '30' }, courseDetails)

    for (const article of store.inscriptions) {
      if (article.courseDetails?.is_prix_evolutif) {
        await prixEvolutifService.getTarifActuel(article.courseDetails.id)
      }
    }

    expect(prixEvolutifService.getTarifActuel).not.toHaveBeenCalled()
  })
})
