import { describe, test, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'

vi.mock('@iconify/vue', () => ({
  Icon: {
    name: 'Icon',
    template: '<span data-test="icon"></span>',
  },
}))

import EtapeDocument from '../../components/EtapeDocument.vue'

function mountComponent(customProps = {}) {
  return mount(EtapeDocument, {
    props: {
      modelValue: [],
      description_document: 'Certificat medical',
      ...customProps,
    },
  })
}

describe('EtapeDocument', () => {
  // Initialise fichiers depuis modelValue via watcher immediate
  test('watch modelValue initialise fichiers', () => {
    const f1 = new File(['a'], 'doc-a.pdf', { type: 'application/pdf' })
    const wrapper = mountComponent({ modelValue: [f1] })

    expect(wrapper.vm.fichiers).toHaveLength(1)
    expect(wrapper.vm.fichiers[0].name).toBe('doc-a.pdf')
  })

  // Reagit au changement de modelValue parent
  test('watch modelValue met a jour fichiers sur setProps', async () => {
    const wrapper = mountComponent()
    const f1 = new File(['a'], 'doc-a.pdf', { type: 'application/pdf' })
    const f2 = new File(['b'], 'doc-b.jpg', { type: 'image/jpeg' })

    await wrapper.setProps({ modelValue: [f1, f2] })

    expect(wrapper.vm.fichiers).toHaveLength(2)
    expect(wrapper.vm.fichiers.map((f) => f.name)).toEqual(['doc-a.pdf', 'doc-b.jpg'])
  })

  // Selection input ajoute les fichiers puis reset input
  test('selectionnerFichier ajoute fichiers et vide input', () => {
    const wrapper = mountComponent()
    const f1 = new File(['a'], 'doc-a.pdf', { type: 'application/pdf' })
    const f2 = new File(['b'], 'doc-b.jpg', { type: 'image/jpeg' })
    const event = {
      target: {
        files: [f1, f2],
        value: 'tmp',
      },
    }

    wrapper.vm.selectionnerFichier(event)

    expect(wrapper.vm.fichiers).toHaveLength(2)
    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    expect(wrapper.emitted('update:modelValue').at(-1)[0]).toHaveLength(2)
    expect(event.target.value).toBe('')
  })

  // Drag and drop desactive glisser et ajoute fichiers
  test('deposerFichier gere drop et etat glisser', () => {
    const wrapper = mountComponent()
    const f1 = new File(['a'], 'doc-a.pdf', { type: 'application/pdf' })

    wrapper.vm.glisser = true
    wrapper.vm.deposerFichier({
      dataTransfer: { files: [f1] },
    })

    expect(wrapper.vm.glisser).toBe(false)
    expect(wrapper.vm.fichiers).toHaveLength(1)
    expect(wrapper.vm.fichiers[0].name).toBe('doc-a.pdf')
  })

  // ajouterFichiers concatene proprement et emet
  test('ajouterFichiers concatene et emet update:modelValue', () => {
    const wrapper = mountComponent()
    const f1 = new File(['a'], 'doc-a.pdf', { type: 'application/pdf' })
    const f2 = new File(['b'], 'doc-b.jpg', { type: 'image/jpeg' })

    wrapper.vm.ajouterFichiers([f1])
    wrapper.vm.ajouterFichiers([f2])

    expect(wrapper.vm.fichiers.map((f) => f.name)).toEqual(['doc-a.pdf', 'doc-b.jpg'])
    expect(wrapper.emitted('update:modelValue').at(-1)[0]).toHaveLength(2)
  })

  // retirerFichier supprime par index et emet nouvel etat
  test('retirerFichier retire et emet la nouvelle liste', () => {
    const f1 = new File(['a'], 'doc-a.pdf', { type: 'application/pdf' })
    const f2 = new File(['b'], 'doc-b.jpg', { type: 'image/jpeg' })
    const wrapper = mountComponent({ modelValue: [f1, f2] })

    wrapper.vm.retirerFichier(0)

    expect(wrapper.vm.fichiers).toHaveLength(1)
    expect(wrapper.vm.fichiers[0].name).toBe('doc-b.jpg')
    expect(wrapper.emitted('update:modelValue').at(-1)[0]).toHaveLength(1)
  })

  // Formate tailles en o Ko Mo
  test('formaterTaille gere octets kilo et mega', () => {
    const wrapper = mountComponent()

    expect(wrapper.vm.formaterTaille(512)).toBe('512 o')
    expect(wrapper.vm.formaterTaille(2048)).toBe('2.0 Ko')
    expect(wrapper.vm.formaterTaille(3 * 1024 * 1024)).toBe('3.0 Mo')
  })

  // Affiche le texte descriptif passe en prop
  test('rend description_document', () => {
    const wrapper = mountComponent({ description_document: 'Joindre une piece d identite' })

    expect(wrapper.text()).toContain('Joindre une piece d identite')
  })
})
