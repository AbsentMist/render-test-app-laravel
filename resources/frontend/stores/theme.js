import { defineStore } from 'pinia';

export const useThemeStore = defineStore('theme', {
  state: () => ({
    primaryColor: null, 
    secondaryColor: null,
  }),
  actions: {
    setTheme(primary, secondary) {
      this.primaryColor = primary;
      this.secondaryColor = secondary;
    },
    resetTheme() {
      this.primaryColor = null;
      this.secondaryColor = null;
    }
  }
});