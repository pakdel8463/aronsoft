// پیکربندی Tailwind v3 مخصوص قالب عمومی سایت (front.css از طریق @config به آن اشاره می‌کند).
// مستقل از متغیر THEME است تا بیلد فرانت همیشه توکن‌های رنگی خودش را داشته باشد.
import forms from '@tailwindcss/forms';

export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/front.js',
        './app/View/**/*.php',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                darkbg: { 950: '#09090b', 900: '#18181b', 800: '#27272a' },
                tech: { cyan: '#0891b2', blue: '#2f4ff5', purple: '#7c3aed', green: '#059669', red: '#dc2626' },
                brand: {
                    50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 500: '#4f6bff', 600: '#2f4ff5', 700: '#2540d6', 900: '#1e2a78',
                },
                azure: { light: '#0078D4', bg: '#F0F6FF' },
                'ai-dark': '#0f172a', 'ai-gold': '#f59e0b', 'ai-blue': '#3b82f6', 'ai-red': '#ef4444',
            },
            fontFamily: {
                sans: ['Vazirmatn', 'Tahoma', 'sans-serif'],
                mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Consolas', 'monospace'],
            },
            boxShadow: {
                soft: '0 1px 2px rgba(15,23,42,.04), 0 8px 24px -8px rgba(15,23,42,.08)',
                card: '0 1px 2px rgba(15,23,42,.04), 0 12px 32px -12px rgba(15,23,42,.12)',
            },
            animation: { float: 'float 6s ease-in-out infinite' },
            keyframes: { float: { '0%,100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-6px)' } } },
        },
    },
    plugins: [forms],
};
