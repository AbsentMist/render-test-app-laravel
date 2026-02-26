<template>
    <div class="fixed inset-0 flex items-center justify-center z-50">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="$emit('cancel')"></div>
        <div class="relative bg-secondary rounded-base shadow-xl p-8 w-96 flex flex-col items-center gap-4">
            <div class="w-14 h-14 rounded-full border-2 border-primary flex items-center justify-center text-primary">
                <Icon :icon="icon" width="28" height="28" />
            </div>
            <p class="text-heading text-center text-sm">{{ message }}</p>
            <div v-if="showButtons" class="flex flex-row gap-4 mt-2 w-full">
                <button type="button" @click="$emit('confirm')" class="btn-tertiary flex-1">
                    Continuer
                </button>
                <button type="button" @click="$emit('cancel')" class="btn-accent-300 flex-1">
                    Annuler
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { Icon } from "@iconify/vue";
export default {
    components: { Icon },
    emits: ['confirm', 'cancel'],
    props: {
        message: {
            type: String,
            default: "Êtes-vous sûr de vouloir créer cet évènement ?"
        },
        showButtons: {
            type: Boolean,
            default: true
        },
        icon: {
            type: String,
            default: "mdi:alert-outline"
        }
    }
}
</script>