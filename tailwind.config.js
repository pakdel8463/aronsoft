import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

// همه تم‌ها
import adminTheme from './resources/themes/admin.js';
import frontTheme from './resources/themes/front.js';
import dashboardTheme from './resources/themes/dashboard.js';

const themes = {
    admin: adminTheme,
    front: frontTheme,
    dashboard: dashboardTheme,
};

// Admin panel (/panel) design tokens — namespaced (aron / coral / mint / adm shadows) so they never clash with the front theme.
const admColors = {
    aron: { 50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc', 400: '#7b8dfa', 500: '#4f6bf8', 600: '#2f4ff5', 700: '#2340d6', 800: '#1e36ab', 900: '#1b3086', 950: '#121d52' },
    coral: { 50: '#fff1ef', 100: '#ffe0db', 200: '#ffc2b9', 400: '#ff8a7a', 500: '#ff6b5b', 600: '#f04d3c', 700: '#c93a2b' },
    mint: { 50: '#e8fbf4', 100: '#c9f5e3', 200: '#9aebcd', 400: '#3dd9a4', 500: '#12c48b', 600: '#0ba673', 700: '#0a8860' },
    canvas: '#f4f6fb',
    line: '#e7eaf4',
    ink: '#141a3d',
    rail: '#0e1330',
};
const activeTheme = themes[process.env.THEME] || {};

export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './resources/css/**/*.css',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            ...activeTheme,   // ← تم فعال
            colors: { ...(activeTheme.colors || {}), ...admColors },
            boxShadow: {
                adm: '0 12px 32px -16px rgb(47 79 245 / 0.2), 0 2px 6px -2px rgb(20 26 61 / 0.06)',
                'adm-lift': '0 22px 44px -18px rgb(47 79 245 / 0.35), 0 4px 10px -4px rgb(20 26 61 / 0.08)',
                'adm-brand': '0 12px 24px -10px rgb(47 79 245 / 0.65)',
                'adm-pop': '0 24px 60px -20px rgb(20 26 61 / 0.35)',
            },
            fontFamily: {
                sans: ['Vazirmatn', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
