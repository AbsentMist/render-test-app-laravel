<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

function goToListeCourses() {
  router.push({ name: 'ListeCourses' });
}

// Données fictives des évènements avec les couleurs correspondantes au design
const evenements = ref([
  {
    id: 1,
    title: 'Nocturne des Evaux',
    logoText: 'n<span class="text-white">o</span>cturne<br/><span class="text-3xl">EVAUX</span>',
    logoSub: 'BB Switzerland',
    bgColor: 'bg-[#53687e] shadow-sm',
    titleColor: 'text-[#8cc0d4]',
    logoColor: 'text-[#44c8eb]',
    infoBorder: 'border-[#44c8eb]',
    infoText: 'text-[#44c8eb]'
  },
  {
    id: 2,
    title: 'Antigel Night Run',
    logoText: 'ANTIGEL',
    logoSub: '',
    bgColor: 'bg-[#a3cde6] shadow-sm',
    titleColor: 'text-[#ee528b]',
    logoColor: 'text-[#ee528b]',
    infoBorder: 'border-[#ee528b]',
    infoText: 'text-[#ee528b]'
  },
  {
    id: 3,
    title: 'Course des Ponts de genève',
    logoText: 'Course<br/>des Ponts',
    logoSub: 'de Genève',
    bgColor: 'bg-[#8dc095] shadow-sm',
    titleColor: 'text-[#eaddb4]',
    logoColor: 'text-[#eaddb4]',
    infoBorder: 'border-[#eaddb4]',
    infoText: 'text-[#eaddb4]'
  },
  {
    id: 4,
    title: 'Geneva Urban Trail',
    logoText: 'GUT',
    logoSub: 'Geneva Urban Trail',
    bgColor: 'bg-white shadow-md',
    titleColor: 'text-[#f48d31]',
    logoColor: 'text-[#f48d31]',
    infoBorder: 'border-[#f48d31]',
    infoText: 'text-[#f48d31]'
  }
]);
</script>

<template>
  <div class="p-6">

    <div class="mb-8">
      <h2 class="text-2xl font-normal text-gray-900">Liste des évènements</h2>
      <div class="h-1 w-20 bg-pink-200 mt-2 rounded-full"></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

      <div
        v-for="evt in evenements"
        :key="evt.id"
        @click="goToListeCourses"
        :class="['relative h-48 rounded-xl p-4 flex flex-col justify-end cursor-pointer transition-transform hover:-translate-y-1', evt.bgColor]"
      >
        <div :class="['absolute top-4 right-4 w-6 h-6 rounded-full border flex items-center justify-center text-xs font-medium', evt.infoBorder, evt.infoText]">
          i
        </div>

        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
          <span :class="['text-xl font-black tracking-widest text-center leading-tight', evt.logoColor]" v-html="evt.logoText"></span>
          <span v-if="evt.logoSub" :class="['text-[10px] font-bold mt-1 tracking-wider', evt.logoColor]">{{ evt.logoSub }}</span>
        </div>

        <div :class="['relative z-10 font-normal text-base', evt.titleColor]">
          {{ evt.title }}
        </div>

      </div>

    </div>

  </div>
</template>
