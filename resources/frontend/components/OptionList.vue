<template>
  <div class="flex justify-center p-2">
    <div 
      class="z-10 bg-white divide-y divide-gray-200 rounded-2xl shadow-xl overflow-hidden border border-gray-300"
    >
      <ul class="text-sm text-gray-700">
        <li v-for="element in elements">
          <a @click="selectionnerElement(element)" class="block px-4 py-4 hover:bg-gray-200 text-center border-b border-gray-300 text-body font-medium">
            {{ element }}
          </a>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
/**
 * @fileoverview Composant OptionList.
 * @description Liste d'options cliquables qui émet le choix sélectionné au composant parent.
 * @remarks Ce composant est volontairement stateless: il ne stocke aucun état local
 * et se contente de relayer la sélection au parent.
 */
export default {
  name: 'OptionList',
  props: {
    elements: {
      type: Array,
      required: true
    }
  },
  emits: ['select-item'],
  methods: {
    /**
     * Émet l'élément choisi vers le parent.
     * @param {string} element Valeur sélectionnée dans la liste.
     * @returns {void}
     */
    selectionnerElement(element) {
      this.$emit('select-item', element);
    },
  },
}
</script>
