import { describe, test, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import DropdownList from '../../components/OptionList.vue'

describe('OptionList', () => {

  const elements = ['Option A', 'Option B', 'Option C']

  test('affiche tous les éléments', () => {
    const wrapper = mount(DropdownList, {
      props: { elements }
    })

    const items = wrapper.findAll('li')
    expect(items).toHaveLength(3)
    expect(items[0].text()).toBe('Option A')
    expect(items[1].text()).toBe('Option B')
    expect(items[2].text()).toBe('Option C')
  })

  test('émet "select-item" avec le bon élément au clic', async () => {
    const wrapper = mount(DropdownList, {
      props: { elements }
    })

    await wrapper.findAll('a')[1].trigger('click')

    expect(wrapper.emitted('select-item')).toBeTruthy()
    expect(wrapper.emitted('select-item')[0]).toEqual(['Option B'])
  })

  test('émet "select-item" pour chaque élément cliqué', async () => {
    const wrapper = mount(DropdownList, {
      props: { elements }
    })

    for (const [index, element] of elements.entries()) {
      await wrapper.findAll('a')[index].trigger('click')
      expect(wrapper.emitted('select-item')[index]).toEqual([element])
    }
  })

  test('ne rend rien si elements est vide', () => {
    const wrapper = mount(DropdownList, {
      props: { elements: [] }
    })

    expect(wrapper.findAll('li')).toHaveLength(0)
  })

})