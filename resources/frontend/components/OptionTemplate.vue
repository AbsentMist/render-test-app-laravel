<template>
    <div class="border border-gray-300 rounded-base p-4">
            <div>
                <div class="flex flex-row justify-between items-center">
                    <label class="block mb-2.5 text-sm font-medium text-heading">Nom de l'option</label>
                    <button type="button" @click="removeOption" class="mb-2.5 text-primary-900 hover:text-accent">
                        <Icon icon="mdi:close-circle" width="20" height="20" />
                    </button>
                </div>
                <input type="text" id="name" v-model="optionModel.name" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="" required />
            </div>
            <div>
            </div>
        <div class="my-4">
            <label class="block mb-2.5 text-sm font-medium text-heading">Description</label>
            <input type="text" id="description" v-model="optionModel.description" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="" required />
        </div>
        <div class="flex flex-row justify-between gap-4 my-4">
            <label class="block mb-2.5 text-sm font-medium text-heading">Prix</label>
            <input type="text" id="prix" v-model="optionModel.prix" class="basis-1/5 bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="" required />
        </div>
        <div class="flex col-2 gap-4">
            <div class="basis-1/2">
                <label class="block mb-2.5 text-sm font-medium text-heading">Qté min</label>
                <input type="text" id="quantiteMin" v-model="optionModel.quantiteMin" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="" required />
            </div>
            <div class="basis-1/2">
                <label class="block mb-2.5 text-sm font-medium text-heading">Qté max</label>
                <input type="text" id="quantiteMax" v-model="optionModel.quantiteMax" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-2.5 py-2 shadow-xs placeholder:text-body" placeholder="" required />
            </div>
        </div>
    </div>
</template>

<script>
import { Icon } from "@iconify/vue";
export default {
    components: { Icon },
    props: {
        optionModel: {
            type: Object,
            required: false,
            default: () => ({})
        },
    },
    methods: {
        removeOption() {
            this.$emit('remove-option', this.optionModel);
        }
    }
}
</script>