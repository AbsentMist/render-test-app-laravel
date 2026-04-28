import { describe, test, expect, vi, beforeEach } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import { useAuthStore } from '../../stores/auth';
import { useCartStore } from '../../stores/cart';
import { useThemeStore } from '../../stores/theme';
import api from '../../services/api';

vi.mock('../../services/api', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
  },
}));

describe('Logique applicative des stores', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    localStorage.clear();
    setActivePinia(createPinia());
  });

  test('auth: calcule le statut admin et bascule le mode admin', async () => {
    const store = useAuthStore();

    api.post.mockResolvedValue({
      data: {
        token: 'jwt',
        user: {
          id: 1,
          roles: [{ type: 'Administrateur' }],
        },
      },
    });

    await store.login('admin@test.com', 'secret');

    expect(store.isAdmin).toBe(true);
    expect(store.showAdminLayout).toBe(true);

    store.toggleAdminMode();
    expect(store.showAdminLayout).toBe(false);
  });

  test('cart: ajoute et totalise correctement les inscriptions', () => {
    const store = useCartStore();

    store.setOwner(1);

    store.ajouterInscription({ nom: 'A', tarif: '12.5' }, { id: 1, nom: 'Course 1' });
    store.ajouterInscription({ nom: 'B', tarif: '7.5' }, { id: 2, nom: 'Course 2' });

    expect(store.cartCount).toBe(2);
    expect(store.cartTotal).toBe(20);
  });

  test('cart: charge un panier distinct selon le compte connecté', () => {
    localStorage.setItem(
      'running_cart_user_2',
      JSON.stringify([{ nom: 'Compte 2', tarif: '12' }]),
    );

    const store = useCartStore();

    expect(store.inscriptions).toHaveLength(0);

    store.setOwner(2);

    expect(store.inscriptions).toHaveLength(1);
    expect(store.inscriptions[0].nom).toBe('Compte 2');

    store.ajouterInscription({ nom: 'C', tarif: '3' }, { id: 3, nom: 'Course 3' });

    expect(JSON.parse(localStorage.getItem('running_cart_user_2'))).toHaveLength(2);

    store.setOwner(null);

    expect(store.inscriptions).toHaveLength(0);
  });

  test('theme: applique et réinitialise le thème', () => {
    const store = useThemeStore();

    store.setTheme('#111111', '#eeeeee');
    expect(store.primaryColor).toBe('#111111');
    expect(store.secondaryColor).toBe('#eeeeee');

    store.resetTheme();
    expect(store.primaryColor).toBeNull();
    expect(store.secondaryColor).toBeNull();
  });
});
