export default {
    colors: {
        darkbg: { 950: '#09090b', 900: '#18181b', 800: '#27272a' },
        tech: { cyan: '#06b6d4', blue: '#3b82f6', purple: '#8b5cf6', green: '#10b981', red: '#ef4444' },
        brand: {
            50: '#eff6ff',
            100: '#dbeafe',
            500: '#3b82f6', // آبی اصلی برند
            600: '#2563eb',
            900: '#1e3a8a',
        },
        azure: {
            light: '#0078D4',
            bg: '#F0F6FF'
        }
        ,
        'ai-dark': '#0f172a',
        'ai-gold': '#f59e0b',
        'ai-blue': '#3b82f6',
        'ai-red': '#ef4444'
    },

    fontFamily: {
        sans: ['Vazirmatn', 'sans-serif'],
    },
    animation: {
        'float': 'float 6s ease-in-out infinite',
        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
    },
    keyframes: {
        float: {
            '0%, 100%': { transform: 'translateY(0)' },
            '50%': { transform: 'translateY(-10px)' },
        }
    },
    typography: (theme) => ({
        DEFAULT: {
            css: {
                color: theme('colors.gray.700'),
                h1: { color: theme('colors.gray.900') },
                h2: { color: theme('colors.gray.900') },
                code: { color: theme('colors.tech.blue') },
            },
        },
        dark: {
            css: {
                color: theme('colors.gray.300'),
                h1: { color: theme('colors.white') },
                h2: { color: theme('colors.white') },
                code: { color: theme('colors.tech.cyan') },
            },
        },
    }),
};
