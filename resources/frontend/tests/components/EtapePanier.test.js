/**
 * Tests frontend du projet.
 *
 * @author Ngozoo
 * @returns {void}
 */

import { describe, test, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { defineComponent } from 'vue'

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    template: '<span data-test="icon"></span>',
  },
}))

vi.mock('../../services/codeDossardService', () => ({
  default: {
    validerCode: vi.fn(),
  },
}))

vi.mock('../../services/codeRabaisService', () => ({
  default: {
    validerCode: vi.fn(),
  },
}))

import EtapePanier from '../../components/EtapePanier.vue'
import codeDossardService from '../../services/codeDossardService'

function mountComponent(customProps = {}) {
  return mount(EtapePanier, {
    props: {
      codeParticipation: '',
      ...customProps,
    },
  })
}

describe('EtapePanier', () => {
  // Initialise codeInterne depuis la prop
  test('initialise codeInterne avec codeParticipation', () => {
    const wrapper = mountComponent({ codeParticipation: 'ABC123' })

    expect(wrapper.vm.codeUnique).toBe('ABC123')
  })

  // Synchronise codeInterne quand la prop change
  test('watch codeParticipation met a jour codeInterne', async () => {
    const wrapper = mountComponent({ codeParticipation: 'OLD' })

    await wrapper.setProps({ codeParticipation: 'NEW' })
    expect(wrapper.vm.codeUnique).toBe('NEW')

    await wrapper.setProps({ codeParticipation: null })
    expect(wrapper.vm.codeUnique).toBe('')
  })

  // Emet update:codeParticipation sur input
  test('appliquerCode emet la valeur courante quand code dossard valide', async () => {
    const wrapper = mountComponent({ idCourse: 1 })
    wrapper.vm.codeUnique = 'CODE-77'

    // mock validation as dossard valide
    const codeDossardService = (await import('../../services/codeDossardService')).default
    codeDossardService.validerCode.mockResolvedValue({ data: { valide: true, code: 'CODE-77' } })

    await wrapper.vm.appliquerCode()
    // wait microtasks
    await Promise.resolve()

    expect(wrapper.emitted('update:codeParticipation')).toBeTruthy()
    expect(wrapper.emitted('update:codeParticipation')[0][0]).toBe('CODE-77')
  })

  // Trigger input appelle l emission
  test('interaction input declenche update:codeParticipation', async () => {
    const wrapper = mountComponent({ idCourse: 1 })
    const input = wrapper.find('input[placeholder="Code dossard ou code rabais"]')

    // mock as dossard valid to trigger emission on enter
    const codeDossardService = (await import('../../services/codeDossardService')).default
    codeDossardService.validerCode.mockResolvedValue({ data: { valide: true, code: 'ENTREPRISE' } })

    await input.setValue('ENTREPRISE')
    await input.trigger('keyup.enter')
    await Promise.resolve()

    expect(wrapper.emitted('update:codeParticipation')).toBeTruthy()
    expect(wrapper.emitted('update:codeParticipation').at(-1)[0]).toBe('ENTREPRISE')
  })

  // Blur appelle verifierCodeEntreprise du parent si present
  test('verifierCodeEntrepriseParent appelle la methode du parent', async () => {
    const verifierSpy = vi.fn()
    codeDossardService.validerCode.mockRejectedValue(new Error('API down'))
    const Host = defineComponent({
      components: { EtapePanier },
      data() {
        return { code: 'ABC123' }
      },
      methods: {
        verifierCodeEntreprise: verifierSpy,
      },
      template: '<EtapePanier v-model:codeParticipation="code" :idCourse="1" />',
    })

    const wrapper = mount(Host)
    const input = wrapper.find('input[placeholder="Code dossard ou code rabais"]')

    await input.trigger('keyup.enter')
    await Promise.resolve()
    expect(verifierSpy).toHaveBeenCalledTimes(1)
  })

  // Ne casse pas si parent absent
  test('verifierCodeEntrepriseParent est safe sans parent', async () => {
    codeDossardService.validerCode.mockRejectedValue(new Error('API down'))
    const wrapper = mountComponent({ idCourse: 1, codeParticipation: 'ABC123' })

    await expect(wrapper.vm.appliquerCode()).resolves.not.toThrow()
  })
})
