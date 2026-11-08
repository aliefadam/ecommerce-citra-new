import { createRouter, createWebHistory } from 'vue-router';
import { useSessionStore } from './stores/session';

const routes = [
    {
        path: '/',
        name: 'home',
        component: () => import('./views/HomeView.vue'),
        meta: { label: 'Home', nav: true },
    },
    {
        path: '/categories',
        name: 'categories',
        component: () => import('./views/FoundationView.vue'),
        props: { title: 'Katalog', message: 'Katalog mobile akan tersedia pada Fase 2.' },
        meta: { label: 'Katalog', nav: true },
    },
    {
        path: '/cart',
        name: 'cart',
        component: () => import('./views/FoundationView.vue'),
        props: { title: 'Keranjang', message: 'Keranjang mobile akan tersedia pada Fase 3.' },
        meta: { label: 'Keranjang', nav: true, requiresAuth: true },
    },
    {
        path: '/orders',
        name: 'orders',
        component: () => import('./views/FoundationView.vue'),
        props: { title: 'Transaksi', message: 'Daftar transaksi mobile akan tersedia pada Fase 5.' },
        meta: { label: 'Transaksi', nav: true, requiresAuth: true },
    },
    {
        path: '/account',
        name: 'account',
        component: () => import('./views/AccountView.vue'),
        meta: { label: 'Profil', nav: true, requiresAuth: true },
    },
    {
        path: '/auth/login',
        name: 'login',
        component: () => import('./views/LoginView.vue'),
        meta: { hideNavigation: true },
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: () => import('./views/FoundationView.vue'),
        props: { title: 'Halaman tidak ditemukan', message: 'Periksa kembali tautan yang Anda buka.' },
        meta: { hideNavigation: true },
    },
];

export const createMobileRouter = () => {
    const router = createRouter({
        history: createWebHistory('/app/'),
        routes,
        scrollBehavior(to, from, savedPosition) {
            return savedPosition || { top: 0 };
        },
    });

    router.beforeEach(async (to) => {
        const session = useSessionStore();

        try {
            await session.bootstrap();
        } catch {
            return true;
        }

        if (to.meta.requiresAuth && !session.user) {
            return {
                name: 'login',
                query: { return: to.fullPath },
            };
        }

        if (to.name === 'login' && session.user) {
            return { name: 'home' };
        }

        return true;
    });

    router.afterEach((to) => {
        document.title = to.meta.label
            ? `${to.meta.label} · Mobile`
            : 'Mobile Storefront';
    });

    return router;
};
