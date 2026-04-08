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

    store.ajouterInscription({ nom: 'A', tarif: '12.5' }, { id: 1, nom: 'Course 1' });
    store.ajouterInscription({ nom: 'B', tarif: '7.5' }, { id: 2, nom: 'Course 2' });

    expect(store.cartCount).toBe(2);
    expect(store.cartTotal).toBe(20);
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
