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
        component: () => import('./views/CategoriesView.vue'),
        meta: { label: 'Katalog', nav: true, navSection: 'categories' },
    },
    {
        path: '/search',
        name: 'search',
        component: () => import('./views/ProductsView.vue'),
        meta: { label: 'Pencarian', nav: true, navSection: 'categories' },
    },
    {
        path: '/c/:slug',
        name: 'category-products',
        component: () => import('./views/ProductsView.vue'),
        meta: { label: 'Kategori', nav: true, navSection: 'categories' },
    },
    {
        path: '/products/:slug',
        name: 'product',
        component: () => import('./views/ProductDetailView.vue'),
        meta: { label: 'Detail Produk', nav: true, navSection: 'categories' },
    },
    {
        path: '/cart',
        name: 'cart',
        component: () => import('./views/CartView.vue'),
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
        path: '/wishlist',
        name: 'wishlist',
        component: () => import('./views/WishlistView.vue'),
        meta: { label: 'Wishlist', nav: true, requiresAuth: true, navSection: 'account' },
    },
    {
        path: '/auth/login',
        name: 'login',
        component: () => import('./views/LoginView.vue'),
        meta: { hideNavigation: true },
    },
    {
        path: '/auth/register',
        name: 'register',
        component: () => import('./views/RegisterView.vue'),
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
            if (!savedPosition) return { top: 0 };

            return new Promise((resolve) => {
                window.setTimeout(() => resolve(savedPosition), 300);
            });
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
