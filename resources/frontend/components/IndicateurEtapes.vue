<template>
  <div class="flex items-center justify-between mb-8 px-4">
    <div v-for="(step, index) in steps" :key="index" class="flex flex-col items-center flex-1">
      <div class="flex items-center w-full">
        <div v-if="index > 0" class="flex-1 h-0.5"
          :class="currentStep > index ? 'bg-primary' : 'bg-gray-200'"></div>

        <div class="w-7 h-7 rounded-full flex items-center justify-center border-2 flex-shrink-0"
          :class="{
            'bg-primary border-primary text-white': currentStep > index + 1,
            'border-accent bg-white': currentStep === index + 1,
            'border-gray-300 bg-white': currentStep < index + 1
          }">
          <svg v-if="currentStep > index + 1" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
          </svg>
          <div v-else-if="currentStep === index + 1" class="w-2.5 h-2.5 rounded-full bg-accent"></div>
        </div>

        <div v-if="index < steps.length - 1" class="flex-1 h-0.5"
          :class="currentStep > index + 1 ? 'bg-primary' : 'bg-gray-200'"></div>
      </div>
      <span class="text-label mt-1 text-center text-primary-300">{{ step }}</span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  steps: {
    type: Array,
    required: true
    // Exemple : ['Identifiants', 'Profil', 'Coordonnées']
  },
  currentStep: {
    type: Number,
    required: true
    // Commence à 1
  }
})
</script>