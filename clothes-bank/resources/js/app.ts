import '../css/app.css';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { initializeTheme } from './composables/useAppearance';
import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';
import { createPinia } from 'pinia'
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import { useVolunteerStore } from '@/stores/useVolunteerStore';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

axios.defaults.withCredentials = true; // Send cookies
axios.defaults.baseURL = import.meta.env.VITE_APP_URL || 'http://192.168.1.213:8000';
console.log(axios.defaults.baseURL)
const pinia = createPinia()



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
                .use(pinia)
                .mount(el);
        },
        progress: {
            color: '#4B5563',
        },
    });

    // Set light/dark mode
    initializeTheme();
});

router.on('before', (event) => {
    const volunteerStore = useVolunteerStore();
    
    if (volunteerStore.activeVolunteer?.id) {
        event.detail.visit.headers = {
            ...event.detail.visit.headers,
            'X-Volunteer-ID': String(volunteerStore.activeVolunteer.id),
        };
    }
});
axios.interceptors.request.use((config) => {
    const volunteerStore = useVolunteerStore();
    
    if (volunteerStore.activeVolunteer?.id) {
        config.headers['X-Volunteer-ID'] = volunteerStore.activeVolunteer.id;
    }
    
    return config;
});

