<template>
  <div class="flex w-full mb-8 px-4">
    <div v-for="(step, index) in steps" :key="index" class="relative flex flex-col items-center flex-1">
      
      <div class="flex items-center w-full mb-2">
        <div class="flex-1 h-0.5" 
          :class="[index === 0 ? 'invisible' : '', currentStep > index ? 'bg-primary' : 'bg-gray-200']">
        </div>

        <div class="z-10 w-8 h-8 rounded-full flex items-center justify-center border-2 flex-shrink-0 transition-colors duration-300"
          :class="{
            'bg-primary border-primary text-white': currentStep > index + 1,
            'border-accent bg-white shadow-sm': currentStep === index + 1,
            'border-gray-300 bg-white': currentStep < index + 1
          }">
          <svg v-if="currentStep > index + 1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
          </svg>
          <div v-else-if="currentStep === index + 1" class="w-3 h-3 rounded-full bg-accent"></div>
          <span v-else class="text-xs font-bold text-gray-400">{{ index + 1 }}</span>
        </div>

        <div class="flex-1 h-0.5"
          :class="[index === steps.length - 1 ? 'invisible' : '', currentStep > index + 1 ? 'bg-primary' : 'bg-gray-200']">
        </div>
      </div>

      <div class="absolute top-10 text-center w-full">
        <span class="text-xs font-medium uppercase tracking-wider"
          :class="currentStep >= index + 1 ? 'text-primary font-bold' : 'text-gray-400'">
          {{ step }}
        </span>
      </div>
      
      <div class="h-8"></div>
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