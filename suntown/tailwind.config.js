import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                ink: {
                    DEFAULT: '#12203A',
                    2: '#1B2E4D',
                },
                turf: {
                    DEFAULT: '#1F5E40',
                    light: '#2F7A54',
                },
                gold: {
                    DEFAULT: '#F2B134',
                    dim: '#C98A1F',
                },
                chalk: {
                    DEFAULT: '#F6F2E7',
                    white: '#FFFDF8',
                },
                endzone: '#B23A2E',
            },
            fontFamily: {
                display: ['"Bebas Neue"', ...defaultTheme.fontFamily.sans],
                sans: ['"Libre Franklin"', ...defaultTheme.fontFamily.sans],
                mono: ['"JetBrains Mono"', ...defaultTheme.fontFamily.mono],
            },
            boxShadow: {
                glow: '0 0 6px rgba(242, 177, 52, 0.65), 0 0 1px rgba(242, 177, 52, 0.9)',
                panel: 'inset 0 1px 0 rgba(255,255,255,0.06), 0 8px 24px -12px rgba(18, 32, 58, 0.45)',
            },
        },
    },

    plugins: [forms],
};
