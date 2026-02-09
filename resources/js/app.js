import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

const applyTheme = (theme) => {
    const root = document.documentElement;
    const resolved = theme === 'auto'
        ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
        : theme;

    root.classList.toggle('dark', resolved === 'dark');
    root.setAttribute('data-theme', theme);
};

window.setTheme = (theme) => {
    localStorage.setItem('kanai-theme', theme);
    applyTheme(theme);
};

const initialTheme = localStorage.getItem('kanai-theme') ?? 'auto';
applyTheme(initialTheme);

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if ((localStorage.getItem('kanai-theme') ?? 'auto') === 'auto') {
        applyTheme('auto');
    }
});

Alpine.start();
