<template>
  <div class="flex justify-center p-10">
    <div 
      class="z-10 bg-white divide-y divide-gray-200 rounded-2xl shadow-xl overflow-hidden border border-gray-300"
    >
      <ul class="text-sm text-gray-700" aria-labelledby="dropdownDefaultButton">
        <li>
          <a href="#" class="block px-4 py-4 hover:bg-gray-200 text-center border-b border-gray-300 text-body font-medium">
            Existant
          </a>
        </li>
        <li>
          <a href="#" class="block px-4 py-4 hover:bg-gray-200 text-center text-body font-medium">
            Nouveau
          </a>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { initDropdowns } from 'flowbite'

// Initialise les composants interactifs de Flowbite
onMounted(() => {
    initDropdowns();
})
</script>