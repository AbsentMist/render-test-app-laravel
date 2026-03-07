<template>
    <div class="flex flex-col gap-5 mt-4">
        <h2 class="text-base font-semibold text-heading">Ajoutez votre inscription dans le panier</h2>

        <!-- Code de participation -->
        <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700">Code de participation</label>
            <input
                v-model="codeInterne"
                type="text"
                placeholder="Code de participation"
                @input="$emit('update:codeParticipation', codeInterne)"
                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/40 bg-white"
            />
        </div>

        <!-- Message d'info -->
        <div class="flex flex-col gap-3 text-sm text-gray-600">
            <p>
                Pour valider le processus d'inscription, vous devez cliquer sur le bouton
                <strong>"ajouter au panier"</strong>.
            </p>

            <div class="flex items-start gap-3 bg-orange-50 border border-orange-200 rounded-xl p-4">
                <Icon icon="mdi:alert-outline" class="w-5 h-5 text-orange-400 shrink-0 mt-0.5" />
                <p class="text-orange-700 text-xs leading-relaxed">
                    Veuillez noter que vous ne serez pas officiellement inscrit tant que vous n'aurez pas
                    payé votre panier.
                </p>
            </div>
        </div>
    </div>
</template>

<script>
import { Icon } from '@iconify/vue';

export default {
    name: 'EtapePanier',
    components: { Icon },
    props: {
        codeParticipation: {
            type: String,
            default: '',
        },
    },
    emits: ['update:codeParticipation'],
    data() {
        return {
            codeInterne: this.codeParticipation || '',
        };
    },
}
</script>
