import '../css/app.css';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { initializeTheme } from './composables/useAppearance';
import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';
import axios from 'axios';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

axios.defaults.withCredentials = true; // Send cookies
axios.defaults.baseURL = import.meta.env.VITE_APP_URL || 'http://192.168.1.184:8000';
console.log(axios.defaults.baseURL)

// Get CSRF cookie first
axios.get('/sanctum/csrf-cookie').then(() => {
    createInertiaApp({
        title: (title) => (title ? `${title} - ${appName}` : appName),
        resolve: (name) =>
            resolvePageComponent(
                `./pages/${name}.vue`,
                import.meta.glob<DefineComponent>('./pages/**/*.vue')
            ),
        setup({ el, App, props, plugin }) {
            createApp({ render: () => h(App, props) })
                .use(ElementPlus)
                .use(plugin)
                .mount(el);
        },
        progress: {
            color: '#4B5563',
        },
    });

    // Set light/dark mode
    initializeTheme();
});