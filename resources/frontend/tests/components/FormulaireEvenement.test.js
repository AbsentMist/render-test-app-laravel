import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import FormulaireEvenement from '../../components/FormulaireEvenement.vue';
import evenementOrganisateurService from '../../services/evenementOrganisateurService';

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    template: '<span data-test="icon"></span>',
  },
}));

vi.mock('../../services/evenementOrganisateurService', () => ({
  default: {
    getEvenement: vi.fn(),
    createEvenement: vi.fn(),
    modifyEvenement: vi.fn(),
  },
}));

const PopupConfirmationStub = {
  template: `
    <div data-test="popup-confirmation">
      <button data-test="confirm" @click="$emit('confirm')">Confirmer</button>
      <button data-test="cancel" @click="$emit('cancel')">Annuler</button>
    </div>
  `,
};

function mountComponent(routeQuery = {}) {
  const push = vi.fn();
  const wrapper = mount(FormulaireEvenement, {
    global: {
      stubs: {
        PopupConfirmation: PopupConfirmationStub,
        OptionList: true,
        OptionTemplate: true,
      },
      mocks: {
        $route: { query: routeQuery },
        $router: { push },
      },
    },
  });

  return { wrapper, push };
}

describe('FormulaireEvenement', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    evenementOrganisateurService.getEvenement.mockResolvedValue({
      data: {
        nom: 'Marathon 2026',
        site: 'https://event.test',
        couleur_primaire: '#010203',
        couleur_secondaire: '#aabbcc',
        logo_base64: null,
        is_actif: 1,
        is_interne: 0,
        is_rabais: 1,
      },
    });
    evenementOrganisateurService.createEvenement.mockResolvedValue({ status: 201, data: { id: 1 } });
    evenementOrganisateurService.modifyEvenement.mockResolvedValue({ status: 200, data: { id: 1 } });
    vi.useFakeTimers();
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  test('affiche le mode création par défaut', () => {
    const { wrapper } = mountComponent();

    expect(wrapper.text()).toContain('Créer un évènement');
    expect(wrapper.get('#name').element.value).toBe('');
  });

  test('charge les données en mode édition', async () => {
    const { wrapper } = mountComponent({ id: '42' });
    await flushPromises();

    expect(evenementOrganisateurService.getEvenement).toHaveBeenCalledWith('42');
    expect(wrapper.get('#name').element.value).toBe('Marathon 2026');
    expect(wrapper.get('#url').element.value).toBe('https://event.test');
  });

  test('soumet une création depuis l’UI', async () => {
    const { wrapper } = mountComponent();

    await wrapper.get('#name').setValue('Nouveau Run');
    await wrapper.get('#url').setValue('https://new.run');

    const submitButton = wrapper.findAll('button').find((b) => b.text().includes("Créer l'évènement"));
    await submitButton.trigger('click');
    await wrapper.get('[data-test="confirm"]').trigger('click');
    await flushPromises();

    expect(evenementOrganisateurService.createEvenement).toHaveBeenCalledTimes(1);
  });

  test('soumet une modification et redirige après succès', async () => {
    const { wrapper, push } = mountComponent({ id: '42' });
    await flushPromises();

    const submitButton = wrapper.findAll('button').find((b) => b.text().includes('Enregistrer les modifications'));
    await submitButton.trigger('click');
    await wrapper.get('[data-test="confirm"]').trigger('click');
    await flushPromises();

    expect(evenementOrganisateurService.modifyEvenement).toHaveBeenCalledTimes(1);

    vi.runAllTimers();
    expect(push).toHaveBeenCalledWith('/organisateur/evenements');
  });
});
