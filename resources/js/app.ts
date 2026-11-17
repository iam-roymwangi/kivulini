import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h } from 'vue';
import PagePreloader from '@/components/PagePreloader.vue';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = 'Kivulini Adventures';

// Initialize theme before mounting so onMounted hooks read the correct dark class
initializeTheme();

createInertiaApp({
    // Public pages send complete, already-branded titles from the server (see App\Support\Seo).
    title: (title) => (!title ? appName : title.includes(appName) ? title : `${title} - ${appName}`),
    // Keep the server-built SEO tags (page prop "head") in sync on client-side navigation.
    serverHead: true,
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name.startsWith('events/'):
            case name.startsWith('merchandise/'):
            case name.startsWith('legal/'):
                return PublicLayout;
            case name.startsWith('admin/'):
                return AppLayout;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        app.use(plugin);

        if (el) {
            app.mount(el);
        }

        // Dismiss the initial HTML preloader once Vue has mounted (client-only)
        if (typeof document !== 'undefined') {
            const preloader = document.getElementById('app-preloader');

            if (preloader) {
                preloader.classList.add('fade-out');
                preloader.addEventListener('transitionend', () => preloader.remove(), { once: true });
            }
        }
    },
    progress: {
        color: '#4B5563',
    },
});

// This will listen for flash toast data from the server...
initializeFlashToast();

// Mount the page preloader as a standalone app so it works across all layouts (client-only)
if (typeof document !== 'undefined') {
    const preloaderEl = document.createElement('div');
    document.body.appendChild(preloaderEl);
    createApp(PagePreloader).mount(preloaderEl);
}
