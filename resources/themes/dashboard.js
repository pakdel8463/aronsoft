import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Vazirmatn', 'sans-serif'],
            },
            colors: {
                'darkbg': { 950: '#09090b', 900: '#18181b', 800: '#27272a' },
                'tech': { cyan: '#06b6d4', blue: '#3b82f6', purple: '#8b5cf6', green: '#10b981', red: '#ef4444', orange: '#f97316' }
            }
        },
    },

    plugins: [forms],
};
