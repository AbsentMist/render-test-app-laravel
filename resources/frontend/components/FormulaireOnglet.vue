<template>
  <div class="flex">
    <button v-for="(formulaire, index) in formulaires" :key="index" @click="$emit('update:modelValue', formulaire)"
    :class="['px-4 py-2 text-sm font-medium border -mb-px transition-colors rounded-t-base',
        modelValue === formulaire 
          ? 'border-default-medium border-b-secondary bg-secondary text-accent' 
          : 'border-transparent text-body hover:text-heading'
      ]">
      {{ formulaire }}
    </button>
  </div>
</template>

<script>

export default {
  props: {
    formulaires: {
      type: Array,
      default: () => formulaires
    },
    modelValue: {
      type: String,
      default: ''
    }
  },
  emits: ['update:modelValue']
};
</script>