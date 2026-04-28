<template>
  <div :class="getWrapperClass()">
    <div 
      class="z-10 bg-white divide-y divide-gray-200 rounded-2xl shadow-xl overflow-hidden border border-gray-300"
    >
      <ul class="text-sm text-gray-700">
        <li v-for="element in elements" :key="element">
          <button v-if="element=='Supprimer'" type="button" @click="selectionnerElement(element)" class="block w-full px-4 py-4 hover:bg-accent-300 text-center border-b border-gray-300 text-accent font-medium">
            <Icon icon="lucide:trash" class="inline-block h-5 w-5 mr-2" />
            {{ element }}
          </button>
          <button v-if="element=='Dupliquer'" type="button" @click="selectionnerElement(element)" class="block w-full px-4 py-4 hover:bg-gray-200 text-center border-b border-gray-300 text-body font-medium">
            <Icon icon="lucide:copy" class="inline-block h-5 w-5 mr-2" />
            {{ element }}
          </button>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import { Icon } from '@iconify/vue';

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
    },
    placement: {
      type: String,
      default: 'center'
    }
  },
  emits: ['select-item'],
  components: {
    Icon
  },
  methods: {
    /**
     * Retourne les classes CSS du wrapper en fonction du placement.
     * @returns {string}
     */
    getWrapperClass() {
      if (this.placement === 'right') {
        return 'absolute left-full top-1/2 z-50 ml-3 -translate-y-1/2';
      } else if (this.placement === 'none') {
        return '';
      } else {
        return 'flex justify-center p-2';
      }
    },
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
