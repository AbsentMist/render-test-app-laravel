import { describe, test, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import FiltreInscriptions from '../../components/FiltreInscriptions.vue'

function mountComponent(props = {}) {
  return mount(FiltreInscriptions, {
    props: {
      nbResultats: 3,
      ...props,
    },
  })
}

describe('FiltreInscriptions', () => {

  // Affiche le nombre de resultats recu en prop
  test('affiche le nombre de resultats', () => {
    const wrapper = mountComponent({ nbResultats: 7 })
    expect(wrapper.text()).toContain('7 résultat(s)')
  })

  // Affiche 0 resultats si prop = 0
  test('affiche 0 resultats', () => {
    const wrapper = mountComponent({ nbResultats: 0 })
    expect(wrapper.text()).toContain('0 résultat(s)')
  })

  // Emettre update:filtres quand on tape dans la recherche
  test('emet update:filtres quand on tape dans la recherche', async () => {
    const wrapper = mountComponent()
    await wrapper.find('input[type="text"]').setValue('Dupont')
    const emitted = wrapper.emitted('update:filtres')
    expect(emitted).toBeTruthy()
    expect(emitted[emitted.length - 1][0]).toMatchObject({ recherche: 'Dupont' })
  })

  // Emettre update:filtres quand on change le statut
  test('emet update:filtres quand on change le statut', async () => {
    const wrapper = mountComponent()
    const selects = wrapper.findAll('select')
    await selects[0].setValue('Validé')
    const emitted = wrapper.emitted('update:filtres')
    expect(emitted).toBeTruthy()
    expect(emitted[emitted.length - 1][0]).toMatchObject({ status: 'Validé' })
  })

  // Emettre update:filtres quand on change le type
  test('emet update:filtres quand on change le type', async () => {
    const wrapper = mountComponent()
    const selects = wrapper.findAll('select')
    await selects[1].setValue('Relais')
    const emitted = wrapper.emitted('update:filtres')
    expect(emitted).toBeTruthy()
    expect(emitted[emitted.length - 1][0]).toMatchObject({ type: 'Relais' })
  })

  // Le bouton reinitialiser n'est pas visible si aucun filtre actif
  test('bouton reinitialiser absent si aucun filtre', () => {
    const wrapper = mountComponent()
    const btn = wrapper.findAll('button').find(b => b.text().includes('Réinitialiser'))
    expect(btn).toBeUndefined()
  })

  // Le bouton reinitialiser apparait quand un filtre est actif
  test('bouton reinitialiser visible si filtre actif', async () => {
    const wrapper = mountComponent()
    await wrapper.find('input[type="text"]').setValue('test')
    const btn = wrapper.findAll('button').find(b => b.text().includes('Réinitialiser'))
    expect(btn).toBeDefined()
  })

  // Reinitialiser vide les filtres et emet filtres vides
  test('reinitialiser remet les filtres a zero', async () => {
    const wrapper = mountComponent()
    await wrapper.find('input[type="text"]').setValue('Dupont')
    const btn = wrapper.findAll('button').find(b => b.text().includes('Réinitialiser'))
    await btn.trigger('click')
    expect(wrapper.find('input[type="text"]').element.value).toBe('')
    const emitted = wrapper.emitted('update:filtres')
    const dernierEmit = emitted[emitted.length - 1][0]
    expect(dernierEmit).toEqual({ recherche: '', status: '', type: '' })
  })

  // Bouton Excel emet exporter avec xlsx
  test('bouton Excel emet exporter xlsx', async () => {
    const wrapper = mountComponent()
    const btnExcel = wrapper.findAll('button').find(b => b.text().includes('Excel'))
    await btnExcel.trigger('click')
    expect(wrapper.emitted('exporter')).toBeTruthy()
    expect(wrapper.emitted('exporter')[0]).toEqual(['xlsx'])
  })

  // Bouton CSV emet exporter avec csv
  test('bouton CSV emet exporter csv', async () => {
    const wrapper = mountComponent()
    const btnCsv = wrapper.findAll('button').find(b => b.text().includes('CSV'))
    await btnCsv.trigger('click')
    expect(wrapper.emitted('exporter')).toBeTruthy()
    expect(wrapper.emitted('exporter')[0]).toEqual(['csv'])
  })
})