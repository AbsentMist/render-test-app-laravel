import { describe, test, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import FormulaireOnglet from '../../components/FormulaireOnglet.vue';

describe('FormulaireOnglet', () => {
  test('affiche tous les onglets reçus en props', () => {
    const wrapper = mount(FormulaireOnglet, {
      props: {
        formulaires: ['Evenement', 'Course', 'Categorie'],
        modelValue: 'Evenement',
      },
    });

    const buttons = wrapper.findAll('button');
    expect(buttons).toHaveLength(3);
    expect(buttons.map((b) => b.text())).toEqual(['Evenement', 'Course', 'Categorie']);
  });

  test('émet update:modelValue au clic sur un onglet', async () => {
    const wrapper = mount(FormulaireOnglet, {
      props: {
        formulaires: ['Evenement', 'Course'],
        modelValue: 'Evenement',
      },
    });

    await wrapper.findAll('button')[1].trigger('click');

    expect(wrapper.emitted('update:modelValue')).toBeTruthy();
    expect(wrapper.emitted('update:modelValue')[0]).toEqual(['Course']);
  });

  test('applique un style actif à l’onglet sélectionné', () => {
    const wrapper = mount(FormulaireOnglet, {
      props: {
        formulaires: ['Evenement', 'Course'],
        modelValue: 'Course',
      },
    });

    const buttons = wrapper.findAll('button');
    expect(buttons[1].classes().join(' ')).toContain('bg-secondary');
    expect(buttons[0].classes().join(' ')).not.toContain('bg-secondary');
  });
});
