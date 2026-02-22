<template>
  <div class="flex justify-center p-2">
    <div 
      class="z-10 bg-white divide-y divide-gray-200 rounded-2xl shadow-xl overflow-hidden border border-gray-300"
    >
      <ul class="text-sm text-gray-700">
        <li v-for="element in elements">
          <a @click="$emit('select-item', element)" class="block px-4 py-4 hover:bg-gray-200 text-center border-b border-gray-300 text-body font-medium">
            {{ element }}
          </a>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    elements: {
      type: Array,
      required: true
    }
  },
  emits: ['select-item'],
}
</script>
