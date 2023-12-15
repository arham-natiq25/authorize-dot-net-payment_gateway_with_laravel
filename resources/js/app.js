import './bootstrap';

import { createApp } from 'vue';

import authorize from './components/authorize.vue'
const app = createApp({});

app.component("authorize",authorize)

app.mount("#app");

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
