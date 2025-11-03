import { App } from 'vue';
import { route } from '../helpers/route';

export default {
    install: (app: App) => {
        // Add route function to global properties
        app.config.globalProperties.$route = route;

        // Also add to window object for backward compatibility
        if (typeof window !== 'undefined') {
            (window as any).route = route;
        }

        // Provide it for injection
        app.provide('route', route);
    }
};