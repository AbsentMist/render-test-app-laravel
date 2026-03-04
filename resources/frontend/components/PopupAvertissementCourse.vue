<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">

            <!-- Contenu -->
            <div class="px-6 py-6">
                <div class="flex gap-4">
                    <div class="shrink-0 mt-1">
                        <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
                            <Icon icon="mdi:alert-circle-outline" class="w-6 h-6 text-amber-500" />
                        </div>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-800 mb-2">Avant de continuer</h2>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ texte }}</p>
                    </div>
                </div>

                <div class="mt-6 p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-center gap-2">
                    <Icon icon="mdi:information-outline" class="w-4 h-4 text-amber-500 shrink-0" />
                    <p class="text-xs text-amber-700">En continuant, vous reconnaissez avoir pris connaissance de cet avertissement.</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-between items-center px-6 py-4 border-t border-gray-100 gap-3">
                <button
                    @click="$emit('close')"
                    class="btn-accent-300"
                >
                    Quitter
                </button>
                <button
                    @click="$emit('confirmer')"
                    class="btn-tertiary"
                >
                    J'ai compris, continuer
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { Icon } from '@iconify/vue';

export default {
    components: { Icon },
    emits: ['close', 'confirmer'],
    props: {
        texte: {
            type: String,
            required: false,
        }
    }
}
</script>