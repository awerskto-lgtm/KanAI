import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

const getPreferredTheme = () => (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

const applyTheme = (theme) => {
    const root = document.documentElement;
    const resolved = theme === 'auto' ? getPreferredTheme() : theme;
    root.classList.toggle('dark', resolved === 'dark');
    root.setAttribute('data-theme', theme);
};

window.setTheme = (theme) => {
    localStorage.setItem('kanai-theme', theme);
    applyTheme(theme);
};

window.copyToClipboard = async (value) => {
    try {
        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(value);
            return true;
        }
    } catch (_) {
        // fallback below
    }

    const input = document.createElement('input');
    input.value = value;
    document.body.appendChild(input);
    input.select();
    const ok = document.execCommand('copy');
    document.body.removeChild(input);
    return ok;
};

const initialTheme = localStorage.getItem('kanai-theme') ?? 'auto';
applyTheme(initialTheme);

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if ((localStorage.getItem('kanai-theme') ?? 'auto') === 'auto') {
        applyTheme('auto');
    }
});

Alpine.start();
