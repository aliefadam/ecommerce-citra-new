import Alpine from 'alpinejs';
import * as lucide from 'lucide';
import './pwa';

const createLucideIcons = (options = {}) => {
    lucide.createIcons({
        icons: lucide.icons,
        ...options,
    });
};

window.Alpine = Alpine;
window.lucide = {
    ...lucide,
    createIcons: createLucideIcons,
};

Alpine.start();
createLucideIcons();
