<template>
    <div class="p-4" :class="border ? 'border border-gray-300 rounded-base' : ''">
        <div class="mb-4">
            <label class="block mb-2.5 text-sm font-medium text-heading">Type d'option</label>
            <div class="flex gap-4">
                <button 
                type="button" 
                @click="optionModel.type = 'Quantifiable'"
                :class="optionModel.type === 'Quantifiable' ? 'bg-primary text-white' : 'bg-neutral-secondary-medium text-heading border border-default-medium'"
                class="px-4 py-2 text-sm rounded-base transition-colors"
                >
                Quantité (Quantifiable)
            </button>
            <button 
                type="button" 
                @click="optionModel.type = 'Cochable'"
                :class="optionModel.type === 'Cochable' ? 'bg-primary text-white' : 'bg-neutral-secondary-medium text-heading border border-default-medium'"
                class="px-4 py-2 text-sm rounded-base transition-colors"
            >
                Simple (Cochable)
            </button>
            </div>
        </div>

        <div class="flex flex-row justify-between items-center">
            <label class="block mb-2.5 text-sm font-medium text-heading">Nom de l'option</label>
            <button v-if="!removeButton" type="button" @click="removeOption" class="mb-2.5 text-primary-900 hover:text-accent">
                <Icon icon="mdi:close-circle" width="20" height="20" />
            </button>
        </div>
        <input type="text" v-model="optionModel.nom" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base w-full px-2.5 py-2" required />

        <div class="my-4">
            <label class="block mb-2.5 text-sm font-medium text-heading">Description</label>
            <textarea v-model="optionModel.description" class="resize-none bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base w-full px-2.5 py-2" rows="3" required />
        </div>

        <div class="flex flex-row justify-between gap-4 my-4">
            <label class="block mb-2.5 text-sm font-medium text-heading">Tarif (CHF)</label>
            <input type="number" v-model="optionModel.tarif" class="basis-1/4 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base px-2.5 py-2" required />
        </div>

        <div v-if="optionModel.type === 'Quantifiable'" class="flex col-2 gap-4 pt-4 border-t border-dashed border-default-medium">
            <div class="basis-1/2">
                <label class="block mb-2.5 text-sm font-medium text-heading">Qté min</label>
                <input type="number" v-model="optionModel.quantifiable.quantiteMin" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base w-full px-2.5 py-2" />
            </div>
            <div class="basis-1/2">
                <label class="block mb-2.5 text-sm font-medium text-heading">Qté max</label>
                <input type="number" v-model="optionModel.quantifiable.quantiteMax" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base w-full px-2.5 py-2" />
            </div>
        </div>
    </div>
</template>

<script>
/**
 * @fileoverview Composant OptionTemplate.
 * @description Bloc d'édition et d'affichage d'une option de course (nom, type, tarif, quantités).
 * @remarks Ce composant sert de brique de formulaire réutilisable pour saisir ou modifier
 * une option, avec bascule entre mode cochable et quantifiable.
 */
import { Icon } from "@iconify/vue";
export default {
    name: 'OptionTemplate',
    components: { Icon },
    props: {
        optionModel: {
            type: Object,
            required: false,
            default: () => ({})
        },
        border: {
            type: Boolean,
            required: false,
            default: true
        },
        removeButton: {
            type: Boolean,
            required: false,
            default: false
        },
    },
    methods: {
        /**
         * Demande au parent de retirer l'option courante.
         * @returns {void}
         */
        removeOption() {
            this.$emit('remove-option', this.optionModel);
        }
    }
}
</script>