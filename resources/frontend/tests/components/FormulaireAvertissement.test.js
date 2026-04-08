import { describe, test, expect, vi, beforeEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import FormulaireAvertissement from '../../components/FormulaireAvertissement.vue';
import avertissementOrganisateurService from '../../services/avertissementOrganisateurService';

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    template: '<button data-test="icon" @click="$emit(\'click\', $event)"></button>',
  },
}));

vi.mock('../../services/avertissementOrganisateurService', () => ({
  default: {
    getAllAvertissement: vi.fn(),
    createAvertissement: vi.fn(),
    deleteAvertissement: vi.fn(),
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
  return mount(FormulaireAvertissement, {
    global: {
      stubs: {
        PopupConfirmation: PopupConfirmationStub,
      },
    },
  });
}

describe('FormulaireAvertissement', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    avertissementOrganisateurService.getAllAvertissement.mockResolvedValue({
      data: [
        { id: 10, titre: 'Météo', contenu: 'Prévoir coupe-vent' },
        { id: 11, titre: 'Santé', contenu: 'Hydratation recommandée' },
      ],
    });
    avertissementOrganisateurService.createAvertissement.mockResolvedValue({ status: 201, data: {} });
    avertissementOrganisateurService.deleteAvertissement.mockResolvedValue({ status: 200 });
  });

  test('affiche les modèles existants au chargement', async () => {
    const wrapper = mountComponent();
    await flushPromises();

    expect(avertissementOrganisateurService.getAllAvertissement).toHaveBeenCalledTimes(1);
    expect(wrapper.text()).toContain('Météo');
    expect(wrapper.text()).toContain('Santé');
  });

  test('soumet un avertissement via le bouton utilisateur', async () => {
    const wrapper = mountComponent();
    await flushPromises();

    await wrapper.get('#titre').setValue('Alerte terrain');
    await wrapper.get('#avertissement').setValue('Sol glissant possible.');

    const addButton = wrapper.findAll('button').find((b) => b.text().includes("Ajouter l'avertissement"));
    await addButton.trigger('click');
    await flushPromises();

    expect(avertissementOrganisateurService.createAvertissement).toHaveBeenCalledTimes(1);
    expect(avertissementOrganisateurService.getAllAvertissement).toHaveBeenCalledTimes(2);
  });

  test('supprime un modèle après confirmation', async () => {
    const wrapper = mountComponent();
    await flushPromises();

    await wrapper.get('[data-test="icon"]').trigger('click');
    expect(wrapper.find('[data-test="popup-confirmation"]').exists()).toBe(true);

    await wrapper.get('[data-test="confirm"]').trigger('click');
    await flushPromises();

    expect(avertissementOrganisateurService.deleteAvertissement).toHaveBeenCalledWith(10);
    expect(wrapper.text()).not.toContain('Météo');
  });
});
