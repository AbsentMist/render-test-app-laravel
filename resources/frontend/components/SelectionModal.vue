<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="relative mx-4 flex max-h-[80vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
      <div class="flex items-center justify-between border-b border-gray-100 bg-tertiary-900 px-6 py-5">
        <div>
          <span class="text-subtitle font-medium text-secondary">{{ titre }}</span>
          <p v-if="sousTitre" class="mt-1 text-sm text-secondary/80">{{ sousTitre }}</p>
        </div>
        <button type="button" @click="$emit('cancel')" class="text-secondary transition-colors hover:text-gray-600">
          <Icon icon="mdi:close" class="h-5 w-5" />
        </button>
      </div>

      <div class="flex-1 overflow-y-auto p-6">
        <div v-if="elements.length === 0" class="py-14 text-center text-sm text-gray-400">
          Aucun élément disponible.
        </div>

        <div v-else class="space-y-2">
          <button
            v-for="element in elements"
            :key="elementKey(element)"
            type="button"
            @click="$emit('select-item', element)"
            class="flex w-full items-center justify-between gap-4 rounded-xl border border-gray-200 bg-white px-4 py-3 text-left transition-colors hover:border-tertiary hover:bg-gray-50"
          >
            <div class="min-w-0 flex-1">
              <p class="truncate font-medium text-gray-800">{{ elementLabel(element) }}</p>
              <p v-if="elementDescription(element)" class="mt-1 text-xs text-gray-500">
                {{ elementDescription(element) }}
              </p>
            </div>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Icon } from "@iconify/vue";

export default {
  name: "SelectionModal",
  components: {
    Icon,
  },
  props: {
    titre: {
      type: String,
      required: true,
    },
    sousTitre: {
      type: String,
      default: "",
    },
    elements: {
      type: Array,
      default: () => [],
    },
  },
  emits: ["select-item", "cancel"],
  methods: {
    elementKey(element) {
      return element && typeof element === "object"
        ? element.id ?? element.label ?? element.name ?? element.enonce
        : element;
    },
    elementLabel(element) {
      if (element && typeof element === "object") {
        return element.label ?? element.name ?? element.enonce ?? String(this.elementKey(element));
      }
      return String(element);
    },
    elementDescription(element) {
      if (element && typeof element === "object") {
        return element.description ?? "";
      }
      return "";
    },
  },
};
</script>