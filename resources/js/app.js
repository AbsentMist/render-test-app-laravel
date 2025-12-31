import './bootstrap';
import 'flowbite'; 
import { createApp } from 'vue';
import { createPinia } from 'pinia';


const app = createApp({});

const pinia = createPinia();
app.use(pinia);

app.mount('#app');