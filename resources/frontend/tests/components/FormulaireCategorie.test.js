import { describe, test, expect, vi, beforeEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import FormulaireCategorie from '../../components/FormulaireCategorie.vue';
import categorieOrganisateurService from '../../services/categorieOrganisateurService';
import sousCategorieOrganisateurService from '../../services/sousCategorieOrganisateurService';

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    template: '<button data-test="icon" @click="$emit(\'click\', $event)"></button>',
  },
}));

vi.mock('../../services/categorieOrganisateurService', () => ({
  default: {
    getAllCategorie: vi.fn(),
    createCategorie: vi.fn(),
    modifyCategorie: vi.fn(),
    deleteCategorie: vi.fn(),
  },
}));

vi.mock('../../services/sousCategorieOrganisateurService', () => ({
  default: {
    getAllSousCategorie: vi.fn(),
    createSousCategorie: vi.fn(),
    modifySousCategorie: vi.fn(),
    deleteSousCategorie: vi.fn(),
  },
}));

const PopupConfirmationStub = {
  props: ['message'],
  template: `
    <div data-test="popup-confirmation">
      <p>{{ message }}</p>
      <button data-test="confirm" @click="$emit('confirm')">Confirmer</button>
      <button data-test="cancel" @click="$emit('cancel')">Annuler</button>
    </div>
  `,
};

function mountComponent() {
  return mount(FormulaireCategorie, {
    global: {
      stubs: {
        PopupConfirmation: PopupConfirmationStub,
      },
    },
  });
}

describe('FormulaireCategorie', () => {
  beforeEach(() => {
    vi.clearAllMocks();

    categorieOrganisateurService.getAllCategorie.mockResolvedValue({
      data: [
        { id: 1, nom: 'Trail' },
        { id: 2, nom: 'Route' },
      ],
    });
    sousCategorieOrganisateurService.getAllSousCategorie.mockResolvedValue({
      data: [
        { id: 10, nom: '10 km' },
      ],
    });

    categorieOrganisateurService.createCategorie.mockResolvedValue({
      data: { categorie: { id: 3, nom: 'Montagne' } },
    });
    categorieOrganisateurService.deleteCategorie.mockResolvedValue({ status: 200 });
  });

  test('charge et affiche catégories et sous-catégories', async () => {
    const wrapper = mountComponent();
    await flushPromises();

    expect(categorieOrganisateurService.getAllCategorie).toHaveBeenCalledTimes(1);
    expect(sousCategorieOrganisateurService.getAllSousCategorie).toHaveBeenCalledTimes(1);
    expect(wrapper.text()).toContain('Trail');
    expect(wrapper.text()).toContain('10 km');
  });

  test('crée une catégorie depuis la modale', async () => {
    const wrapper = mountComponent();
    await flushPromises();

    const createButtons = wrapper.findAll('button').filter((b) => b.text().trim() === 'Créer');
    await createButtons[0].trigger('click');

    const input = wrapper.get('input[placeholder="Nom de la catégorie..."]');
    await input.setValue('Montagne');
    await input.trigger('keyup.enter');
    await flushPromises();

    expect(categorieOrganisateurService.createCategorie).toHaveBeenCalledWith({ nom: 'Montagne', modele: true });
    expect(wrapper.text()).toContain('Montagne');
  });

  test('supprime une catégorie après confirmation', async () => {
    const wrapper = mountComponent();
    await flushPromises();

    const deleteButton = wrapper.find('.btn-model .text-accent');
    await deleteButton.trigger('click');
    await flushPromises();

    expect(wrapper.find('[data-test="popup-confirmation"]').exists()).toBe(true);

    await wrapper.get('[data-test="confirm"]').trigger('click');
    await flushPromises();

    expect(categorieOrganisateurService.deleteCategorie).toHaveBeenCalledWith(1);
    expect(wrapper.text()).not.toContain('Trail');
  });
});
