<template>
    <div class="bg-secondary rounded-b-base rounded-tr-base p-6">
        <h1 class="text-subtitle my-4">Créer une course</h1>
        
        <div class="flex justify-between items-center">
            <label for="dropdown" class="text-sm font-medium text-heading">Evènement</label>
            <div class="relative">
                <button @click="open = !open" class="inline-flex items-center justify-center border hover:bg-primary-300 text-white bg-primary-900 shadow-xs font-medium rounded-base text-sm px-4 py-2.5" type="button">
                    {{courseData.event.name || "Sélectionner un évènement"}}
                    <Icon icon="mdi:chevron-down" class="ml-2 w-6 h-6" />
                </button>
                <div v-if="open" class="absolute left-0 mt-1 z-10 bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44">
                    <ul class="p-2 text-sm text-body font-medium">
                        <li v-for="evenement in evenements" :key="evenement.id">
                            <button type="button" @click="courseData.event = evenement; open = false" 
                                class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">
                                {{ evenement.name }}
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="flex flex-col-2 gap-4">
            <div class="w-full">
                <label for="year" class="block mb-2.5 text-sm font-medium text-heading">Année</label>
                <input type="text" id="year" v-model="courseData.year" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="" required />
            </div>
            <div class="w-full">
                <label for="format" class="block mb-2.5 text-sm font-medium text-heading">Format</label>
                <input type="text" id="format" v-model="courseData.format" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" required />
            </div>
        </div>

        <hr class="border-t border-gray-200 mt-6 mb-4 mx-4" />

        <div id="date-range-picker" date-rangepicker class="flex items-center gap-4">
            <div class="w-full">
                <label for="datepicker-range-start" class="block mb-2.5 text-sm font-medium text-heading">Date de début</label>
                <div class="relative">
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                        <Icon icon="mdi:calendar" class="w-4 h-4 text-body" />
                    </div>
                    <input id="datepicker-range-start" name="start" type="text" class="block w-full ps-9 pe-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 py-2.5 shadow-xs placeholder:text-body" placeholder="Select date start">
                </div>
            </div>
            <div class="w-full">
                <label for="datepicker-range-end" class="block mb-2.5 text-sm font-medium text-heading">Date de fin</label>
                <div class="relative">
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                        <Icon icon="mdi:calendar" class="w-4 h-4 text-body" />
                    </div>
                    <input id="datepicker-range-end" name="end" type="text" class="block w-full ps-9 pe-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 py-2.5 shadow-xs placeholder:text-body" placeholder="Select date end">
                </div>
            </div>
        </div>
        <div id="date-range-picker" date-rangepicker class="flex justify-between items-center gap-4 my-4">
            <label for="datepicker-range-start" class="block mb-2.5 text-sm font-medium text-heading">Interval d'inscription</label>
            <div class="flex row gap-4 basis-1/2">
                <div class="relative">
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                        <Icon icon="mdi:calendar" class="w-4 h-4 text-body" />
                    </div>
                    <input id="datepicker-range-start" name="start" type="text" class="block w-full ps-9 pe-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 py-2.5 shadow-xs placeholder:text-body" placeholder="Select date start">
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                        <Icon icon="mdi:calendar" class="w-4 h-4 text-body" />
                    </div>
                    <input id="datepicker-range-end" name="end" type="text" class="block w-full ps-9 pe-3 py-2.5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand px-3 py-2.5 shadow-xs placeholder:text-body" placeholder="Select date end">
                </div>
            </div>
        </div>
        <div class="flex flex-col-3 gap-4">
            <div class="w-full">
                <label for="year" class="block mb-2.5 text-sm font-medium text-heading">Distance</label>
                <input type="text" id="year" v-model="courseData.year" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="" required />
            </div>
            <div class="w-full">
                <label for="format" class="block mb-2.5 text-sm font-medium text-heading">Tarif</label>
                <input type="text" id="format" v-model="courseData.format" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" required />
            </div>
            <div class="w-full">
                <label for="format" class="block mb-2.5 text-sm font-medium text-heading">Information tarif</label>
                <input type="text" id="format" v-model="courseData.format" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" required />
            </div>
        </div>
        <div>
                <label for="year" class="block mb-2.5 text-sm font-medium text-heading">Distance</label>
                <textarea id="year" v-model="courseData.year" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" required></textarea>
        </div>
        <div class="flex justify-between items-center gap-4 my-4">
            <label for="dropdown" class="text-sm font-medium text-heading">Type de course</label>
            <div class="relative">
                <button @click="open = !open" class="inline-flex items-center justify-center border hover:bg-primary-300 text-white bg-primary-900 shadow-xs font-medium rounded-base text-sm px-4 py-2.5" type="button">
                    {{courseData.event.name || "Sélectionner un évènement"}}
                    <Icon icon="mdi:chevron-down" class="ml-2 w-6 h-6" />
                </button>
                <div v-if="open" class="absolute left-0 mt-1 z-10 bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44">
                    <ul class="p-2 text-sm text-body font-medium">
                        <li v-for="evenement in evenements" :key="evenement.id">
                            <button type="button" @click="courseData.event = evenement; open = false" 
                                class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">
                                {{ evenement.name }}
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="flex justify-between items-center gap-4 my-4">
            <label for="year" class="basis-1/3 block mb-2.5 text-sm font-medium text-heading">Nombre de coureur maximum</label>
            <input type="text" id="year" v-model="courseData.year" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" required />
        </div>
        <div class="flex flex-col-2 gap-4">
            <div class="w-full">
                <label for="year" class="block mb-2.5 text-sm font-medium text-heading">Premier dossard</label>
                <input type="text" id="year" v-model="courseData.year" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="" required />
            </div>
            <div class="w-full">
                <label for="format" class="block mb-2.5 text-sm font-medium text-heading">Dernier dossard</label>
                <input type="text" id="format" v-model="courseData.format" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" required />
            </div>
        </div>
        
        <hr class="border-t border-gray-200 mt-6 mb-4 mx-4" />

        <div class="flex flex-row justify-between items-center">
            <label class="text-sm font-medium text-heading">Dossard personnalisé</label>
            <label class="inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="courseData.dossardPersonalise" value="" class="sr-only peer">
                <div class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"></div>
            </label>
        </div>
        <div class="flex flex-row justify-between items-center my-4">
            <label class="text-sm font-medium text-heading">Challenge</label>
            <label class="inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="courseData.challenge" value="" class="sr-only peer">
                <div class="relative w-9 h-5 bg-neutral-quaternary peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-soft rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-buffer after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-tertiary"></div>
            </label>
        </div>

        <hr class="border-t border-gray-200 mt-6 mb-4 mx-4" />

        <div class="flex flex-col-2 gap-4">
            <div class="flex justify-between items-center gap-4 my-4 w-full">
                <label for="dropdown" class="text-sm font-medium text-heading">Catégorie</label>
                <div class="relative">
                    <button @click="open = !open" class="inline-flex items-center justify-center border hover:bg-primary-300 text-white bg-primary-900 shadow-xs font-medium rounded-base text-sm px-4 py-2.5" type="button">
                        {{courseData.event.name || "Sélectionner un évènement"}}
                        <Icon icon="mdi:chevron-down" class="ml-2 w-6 h-6" />
                    </button>
                    <div v-if="open" class="absolute left-0 mt-1 z-10 bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44">
                        <ul class="p-2 text-sm text-body font-medium">
                            <li v-for="evenement in evenements" :key="evenement.id">
                                <button type="button" @click="courseData.event = evenement; open = false" 
                                    class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">
                                    {{ evenement.name }}
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="flex justify-between items-center gap-4 my-4 w-full">
                <label for="dropdown" class="text-sm font-medium text-heading">Sous-catégorie</label>
                <div class="relative">
                    <button @click="open = !open" class="inline-flex items-center justify-center border hover:bg-primary-300 text-white bg-primary-900 shadow-xs font-medium rounded-base text-sm px-4 py-2.5" type="button">
                        {{courseData.event.name || "Sélectionner un évènement"}}
                        <Icon icon="mdi:chevron-down" class="ml-2 w-6 h-6" />
                    </button>
                    <div v-if="open" class="absolute left-0 mt-1 z-10 bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44">
                        <ul class="p-2 text-sm text-body font-medium">
                            <li v-for="evenement in evenements" :key="evenement.id">
                                <button type="button" @click="courseData.event = evenement; open = false" 
                                    class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">
                                    {{ evenement.name }}
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex justify-end items-center gap-4 my-4">
            <button class="btn-tertiary">Créer la course</button>
        </div>
    </div>
</template>

<script>
import { Icon } from '@iconify/vue';
import { initDatepickers } from 'flowbite';

export default {
    components: {
        Icon
    },
    data() {
        return {
            open: false,
            evenements: [
                {
                    name: "Evènement 1",
                    id: 1,
                },
                {
                    name: "Evènement 2",
                    id: 2,
                },
            ],
            courseData:{
                year: "",
                format: "",
                event: "",
            },
        };
    },
    mounted() {
        initDatepickers();
    }
}
</script>