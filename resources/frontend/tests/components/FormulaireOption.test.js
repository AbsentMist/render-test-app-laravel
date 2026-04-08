import { describe, test, expect, vi, beforeEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import FormulaireOption from '../../components/FormulaireOption.vue';
import optionOrganisateurService from '../../services/optionOrganisateurService';

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    template: '<button data-test="icon" @click="$emit(\'click\', $event)"></button>',
  },
}));

vi.mock('../../services/optionOrganisateurService', () => ({
  default: {
    getAllOptions: vi.fn(),
    createOption: vi.fn(),
    deleteOption: vi.fn(),
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

const OptionTemplateStub = {
  props: ['optionModel'],
  template: '<div data-test="option-template">{{ optionModel?.nom || "" }}</div>',
};

function mountComponent() {
  return mount(FormulaireOption, {
    global: {
      stubs: {
        PopupConfirmation: PopupConfirmationStub,
        OptionTemplate: OptionTemplateStub,
      },
    },
  });
}

describe('FormulaireOption', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    optionOrganisateurService.getAllOptions.mockResolvedValue({
      data: [
        { id: 1, nom: 'Option A', description: 'Desc A', tarif: '10', type: 'Quantifiable', quantifiable: { quantiteMin: 1, quantiteMax: 3 } },
        { id: 2, nom: 'Option B', description: 'Desc B', tarif: '20', type: 'Cochable', quantifiable: { quantiteMin: 0, quantiteMax: 0 } },
      ],
    });
    optionOrganisateurService.createOption.mockResolvedValue({ status: 201, data: { option: { id: 99 } } });
    optionOrganisateurService.deleteOption.mockResolvedValue({ status: 200 });
  });

  test('charge et affiche les modèles au montage', async () => {
    const wrapper = mountComponent();
    await flushPromises();

    expect(optionOrganisateurService.getAllOptions).toHaveBeenCalledTimes(1);
    expect(wrapper.text()).toContain('Option A');
    expect(wrapper.text()).toContain('Option B');
  });

  test('copie les données du modèle sélectionné', async () => {
    const wrapper = mountComponent();
    await flushPromises();

    const modelButtons = wrapper.findAll('button').filter((b) => b.text().includes('Option A'));
    await modelButtons[0].trigger('click');

    expect(wrapper.get('[data-test="option-template"]').text()).toContain('Option A');
  });

  test('soumet une nouvelle option et rafraîchit la liste', async () => {
    const wrapper = mountComponent();
    await flushPromises();

    const addButton = wrapper.findAll('button').find((b) => b.text().includes('Ajouter une option'));
    await addButton.trigger('click');
    await flushPromises();

    expect(optionOrganisateurService.createOption).toHaveBeenCalledTimes(1);
    expect(optionOrganisateurService.getAllOptions).toHaveBeenCalledTimes(2);
  });

  test('ouvre la confirmation et supprime un modèle', async () => {
    const wrapper = mountComponent();
    await flushPromises();

    const trashIcon = wrapper.get('[data-test="icon"]');
    await trashIcon.trigger('click');

    expect(wrapper.find('[data-test="popup-confirmation"]').exists()).toBe(true);

    await wrapper.get('[data-test="confirm"]').trigger('click');
    await flushPromises();

    expect(optionOrganisateurService.deleteOption).toHaveBeenCalledWith(1);
    const modelLabels = wrapper.findAll('button').map((b) => b.text().trim());
    expect(modelLabels.includes('Option A')).toBe(false);
  });
});
