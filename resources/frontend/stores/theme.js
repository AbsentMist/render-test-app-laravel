import { defineStore } from 'pinia';

export const useThemeStore = defineStore('theme', {
  state: () => ({
    primaryColor: null, 
    secondaryColor: null,
    logo: null
  }),
  actions: {
    setTheme(primary, secondary, logo) {
      this.primaryColor = primary;
      this.secondaryColor = secondary;
      this.logo = logo;
    },
    resetTheme() {
      this.primaryColor = null;
      this.secondaryColor = null;
      this.logo = null;
    }
  }
});