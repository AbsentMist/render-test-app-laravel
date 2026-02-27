<script setup>
import { ref } from 'vue';

// Données fictives, facilement remplaçables par un appel API
// Les couleurs sont intégrées dans l'objet pour être dynamiques selon la course
const courses = ref([
  {
    id: 1,
    eventName: 'Nocturne des\nEvaux',
    date: '10.04.2026',
    title: 'Course 20 km',
    price: '35.00',
    validity: '1 janvier 2026',
    extraInfo: '',
    // Couleurs dynamiques (pourront venir de la BDD)
    cardBg: 'bg-[#cbf0f4]',        // Fond bleu clair de la carte entière
    leftBg: 'bg-[#5e7082]',        // Fond bleu foncé du bloc de gauche
    datePillBg: 'bg-[#3b5266]',    // Fond de la pilule de date
    textColor: 'text-[#5e7082]',   // Couleur du texte à droite
  },
  {
    id: 2,
    eventName: 'Nocturne des\nEvaux',
    date: '10.04.2026',
    title: 'Course 20 km',
    price: '35.00',
    validity: '1 janvier 2026',
    extraInfo: 'Groupe (2)',
    cardBg: 'bg-[#cbf0f4]',
    leftBg: 'bg-[#5e7082]',
    datePillBg: 'bg-[#3b5266]',
    textColor: 'text-[#5e7082]',
  },
  {
    id: 3,
    eventName: 'Nocturne des\nEvaux',
    date: '10.04.2026',
    title: 'Course 20 km',
    price: '35.00',
    validity: '1 janvier 2026',
    extraInfo: '',
    cardBg: 'bg-[#cbf0f4]',
    leftBg: 'bg-[#5e7082]',
    datePillBg: 'bg-[#3b5266]',
    textColor: 'text-[#5e7082]',
  },
  {
    id: 4,
    eventName: 'Nocturne des\nEvaux',
    date: '10.04.2026',
    title: 'Course 20 km',
    price: '35.00',
    validity: '1 janvier 2026',
    extraInfo: '',
    cardBg: 'bg-[#cbf0f4]',
    leftBg: 'bg-[#5e7082]',
    datePillBg: 'bg-[#3b5266]',
    textColor: 'text-[#5e7082]',
  }
]);
</script>

<template>
  <div class="p-6">

    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
      <div>
        <h2 class="text-2xl font-normal text-gray-900">Nocturne des Evaux</h2>
        <div class="h-1 w-20 bg-pink-200 mt-2 rounded-full"></div>
      </div>

      <div class="relative w-full md:w-72">
        <input
          type="text"
          placeholder="Rechercher"
          class="w-full pl-4 pr-10 py-2.5 rounded-full bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-200 text-sm font-medium text-gray-700 placeholder-gray-800"
        />
        <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
          <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
      </div>
    </div>

    <div class="flex flex-col gap-4">

      <div
        v-for="course in courses"
        :key="course.id"
        :class="['p-1.5 rounded-2xl flex items-stretch shadow-sm border border-gray-100 cursor-pointer transition-transform hover:-translate-y-0.5', course.cardBg]"
      >
        <div :class="['w-48 md:w-56 rounded-xl border-2 border-white flex flex-col items-center justify-center p-4 text-center text-white shrink-0', course.leftBg]">
          <h3 class="text-xl font-medium leading-tight whitespace-pre-line">{{ course.eventName }}</h3>
          <div :class="['mt-3 px-4 py-1 rounded-full text-sm font-semibold tracking-wide', course.datePillBg]">
            {{ course.date }}
          </div>
        </div>

        <div class="flex-1 flex flex-col md:flex-row md:items-center justify-between p-4 pl-6">

          <div :class="['flex flex-col', course.textColor]">
            <h4 class="text-xl font-semibold mb-2">{{ course.title }}</h4>

            <div class="text-sm font-semibold">
              Tarif CHF {{ course.price }}
            </div>
            <div class="text-xs opacity-80">
              + Frais de plateforme et frais bancaire
            </div>

            <div class="text-xs italic mt-3 font-medium opacity-80">
              Tarif valable jusqu'au {{ course.validity }}
            </div>

            <div class="text-sm font-bold mt-2">
              Dossards restants
            </div>
          </div>

          <div class="flex items-center gap-8 mt-4 md:mt-0">
            <span v-if="course.extraInfo" :class="['text-sm font-semibold', course.textColor]">
              {{ course.extraInfo }}
            </span>

            <svg :class="['w-6 h-6', course.textColor]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
            </svg>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>
