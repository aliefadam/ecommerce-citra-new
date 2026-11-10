import { defineStore } from 'pinia';
import { inject, ref } from 'vue';
import { prepareCsrf, request } from '../services/api';

export const useSessionStore = defineStore('mobile-session', () => {
    const config = inject('mobileConfig');
    const user = ref(null);
    const loading = ref(false);
    const ready = ref(false);
    const error = ref('');

    const bootstrap = async () => {
        if (ready.value || loading.value) return;

        loading.value = true;
        error.value = '';
        try {
            const response = await request(`${config.apiBase}/bootstrap`);
            user.value = response?.data?.user || null;
            ready.value = true;
        } catch (exception) {
            error.value = exception.message || 'Aplikasi tidak dapat dimuat.';
            throw exception;
        } finally {
            loading.value = false;
        }
    };

    const login = async (credentials) => {
        loading.value = true;
        error.value = '';
        try {
            await prepareCsrf();
            const response = await request(`${config.apiBase}/auth/login`, {
                method: 'POST',
                body: credentials,
            });
            user.value = response.data.user;

            return response.data.return_url || '/app';
        } catch (exception) {
            error.value = exception.message || 'Tidak dapat masuk.';
            throw exception;
        } finally {
            loading.value = false;
        }
    };

    const logout = async () => {
        loading.value = true;
        try {
            await prepareCsrf();
            await request(`${config.apiBase}/auth/logout`, { method: 'POST' });
            user.value = null;
        } finally {
            loading.value = false;
        }
    };

    const register = async (details) => {
        loading.value = true;
        error.value = '';
        try {
            await prepareCsrf();
            const response = await request(`${config.apiBase}/auth/register`, {
                method: 'POST',
                body: details,
            });
            user.value = response.data.user;

            return response.data.return_url || '/app';
        } catch (exception) {
            error.value = exception.message || 'Akun belum dapat dibuat.';
            throw exception;
        } finally {
            loading.value = false;
        }
    };

    const expire = () => {
        user.value = null;
        ready.value = true;
    };

    return { user, loading, ready, error, bootstrap, login, logout, register, expire };
});
