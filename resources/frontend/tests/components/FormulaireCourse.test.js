import { describe, test, expect, vi, beforeEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import FormulaireCourse from '../../components/FormulaireCourse.vue';

import evenementOrganisateurService from '../../services/evenementOrganisateurService';
import optionOrganisateurService from '../../services/optionOrganisateurService';
import categorieOrganisateurService from '../../services/categorieOrganisateurService';
import sousCategorieOrganisateurService from '../../services/sousCategorieOrganisateurService';
import avertissementOrganisateurService from '../../services/avertissementOrganisateurService';

vi.mock('flowbite', () => ({
  initDropdowns: vi.fn(),
}));

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    template: '<span data-test="icon"></span>',
  },
}));

vi.mock('../../services/evenementOrganisateurService', () => ({
  default: { getAllEvenements: vi.fn() },
}));
vi.mock('../../services/optionOrganisateurService', () => ({
  default: { getAllOptions: vi.fn() },
}));
vi.mock('../../services/categorieOrganisateurService', () => ({
  default: { getAllCategorie: vi.fn() },
}));
vi.mock('../../services/sousCategorieOrganisateurService', () => ({
  default: { getAllSousCategorie: vi.fn() },
}));
vi.mock('../../services/avertissementOrganisateurService', () => ({
  default: { getAllAvertissement: vi.fn() },
}));

vi.mock('../../services/courseOrganisateurService', () => ({
  default: {
    getCourse: vi.fn(),
    createCourse: vi.fn(),
    modifyCourse: vi.fn(),
  },
}));

vi.mock('../../services/optionCourseService', () => ({
  default: {
    createOptionCourse: vi.fn(),
    deleteOptionCourse: vi.fn(),
  },
}));
vi.mock('../../services/questionOrganisateurService', () => ({
  default: {
    createQuestion: vi.fn(),
    modifyQuestion: vi.fn(),
  },
}));
vi.mock('../../services/optionQuestionOrganisateurService', () => ({
  default: {
    createChoix: vi.fn(),
    modifyChoix: vi.fn(),
  },
}));
vi.mock('../../services/courseQuestionOrganisateurService', () => ({
  default: {
    reordonnerQuestions: vi.fn(),
  },
}));
vi.mock('../../services/challengeOrganisationService', () => ({
  default: {
    getOrganisations: vi.fn(),
    createOrganisation: vi.fn(),
    deleteOrganisation: vi.fn(),
  },
}));
vi.mock('../../services/prixEvolutifService', () => ({
  default: {
    getPaliers: vi.fn().mockResolvedValue({ data: [] }),
    createPalier: vi.fn(),
    deletePalier: vi.fn(),
    deleteAllPaliers: vi.fn(),
  },
}));

const PopupConfirmationStub = {
  template: '<div data-test="popup-confirmation"><button data-test="confirm" @click="$emit(\'confirm\')">Confirmer</button></div>',
};

const OptionListStub = {
  props: ['elements'],
  template: `
    <div data-test="option-list">
      <button
        v-for="item in elements"
        :key="item"
        class="option-choice"
        @click="$emit('select-item', item)"
      >
        {{ item }}
      </button>
    </div>
  `,
};

function mountComponent(routeQuery = {}) {
  return mount(FormulaireCourse, {
    global: {
      stubs: {
        PopupConfirmation: PopupConfirmationStub,
        OptionList: OptionListStub,
        OptionTemplate: true,
        QuestionTemplate: true,
        IndicateurEtapes: true,
      },
      mocks: {
        $route: { query: routeQuery },
        $router: { push: vi.fn() },
      },
    },
  });
}

describe('FormulaireCourse', () => {
  beforeEach(() => {
    vi.clearAllMocks();

    evenementOrganisateurService.getAllEvenements.mockResolvedValue({ data: [{ id: 1, nom: 'Event 1' }] });
    optionOrganisateurService.getAllOptions.mockResolvedValue({ data: [{ id: 1, nom: 'Pack VIP' }] });
    categorieOrganisateurService.getAllCategorie.mockResolvedValue({ data: [{ id: 11, nom: 'Trail' }] });
    sousCategorieOrganisateurService.getAllSousCategorie.mockResolvedValue({ data: [{ id: 21, nom: '10 km' }] });
    avertissementOrganisateurService.getAllAvertissement.mockResolvedValue({ data: [{ id: 31, titre: 'Météo', contenu: 'Vent' }] });
  });

  test('affiche le mode création et charge les données initiales', async () => {
    const wrapper = mountComponent();
    await flushPromises();

    expect(wrapper.text()).toContain('Créer une course');
    expect(evenementOrganisateurService.getAllEvenements).toHaveBeenCalledTimes(1);
    expect(optionOrganisateurService.getAllOptions).toHaveBeenCalledTimes(1);
  });

  test('permet de naviguer de l’étape générale à l’étape options', async () => {
    const wrapper = mountComponent();
    await flushPromises();

    const nextButton = wrapper.findAll('button').find((b) => b.text().includes('Etape suivante'));
    await nextButton.trigger('click');

    expect(wrapper.text()).toContain('Options supplémentaires');
    expect(wrapper.text()).toContain('Etape précédente');
  });

  test('ouvre la sélection d’option puis ajoute une nouvelle option', async () => {
    const wrapper = mountComponent();
    await flushPromises();

    const nextButton = wrapper.findAll('button').find((b) => b.text().includes('Etape suivante'));
    await nextButton.trigger('click');

    const plusButtons = wrapper.findAll('button').filter((b) => b.classes().includes('rounded-full'));
    await plusButtons[0].trigger('click');

    expect(wrapper.find('[data-test="option-list"]').exists()).toBe(true);

    const newOptionChoice = wrapper.findAll('.option-choice').find((b) => b.text() === 'Nouveau');
    await newOptionChoice.trigger('click');

    expect(wrapper.findAllComponents({ name: 'OptionTemplate' }).length).toBe(1);
  });
});
